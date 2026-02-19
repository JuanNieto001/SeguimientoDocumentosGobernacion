<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentType;
use App\Enums\ProcessStatus;
use App\Models\ContractProcess;
use App\Models\ProcessAuditLog;
use App\Models\ProcessDocument;
use App\Models\ProcessNotification;
use App\Models\ProcessStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowEngine
{
    /**
     * Inicializa el workflow para un nuevo proceso
     */
    public function initializeWorkflow(ContractProcess $process): void
    {
        DB::transaction(function () use ($process) {
            // Crear todas las etapas del proceso (0-9)
            $steps = $this->getStepDefinitions();

            foreach ($steps as $stepNumber => $stepDefinition) {
                ProcessStep::create([
                    'process_id' => $process->id,
                    'step_number' => $stepNumber,
                    'step_name' => $stepDefinition['name'],
                    'status' => $stepNumber === 0 ? 'in_progress' : 'pending',
                    'requirements' => $stepDefinition['requirements'] ?? [],
                    'started_at' => $stepNumber === 0 ? now() : null,
                ]);
            }

            // Registrar inicio en auditoría
            ProcessAuditLog::logCustomAction(
                $process,
                'workflow_initialized',
                'Workflow inicializado con ' . count($steps) . ' etapas'
            );
        });
    }

    /**
     * Valida si el proceso puede avanzar a la siguiente etapa
     */
    public function canAdvance(ContractProcess $process): array
    {
        $errors = [];
        $currentStep = $process->current_step;

        // Validar documentos requeridos
        $missingDocs = $this->getMissingRequiredDocuments($process, $currentStep);
        if (count($missingDocs) > 0) {
            $errors[] = 'Faltan documentos requeridos: ' . implode(', ', array_map(
                fn($doc) => $doc->getLabel(),
                $missingDocs
            ));
        }

        // Validar documentos expirados
        $expiredDocs = $this->getExpiredDocuments($process, $currentStep);
        if (count($expiredDocs) > 0) {
            $errors[] = 'Hay documentos expirados que deben renovarse';
        }

        // Validar aprobaciones pendientes
        $pendingApprovals = $process->getPendingApprovals();
        if ($pendingApprovals->count() > 0) {
            $errors[] = 'Hay ' . $pendingApprovals->count() . ' aprobaciones pendientes';
        }

        // Validaciones específicas por etapa
        $stepValidations = $this->validateStepSpecificRules($process, $currentStep);
        if (count($stepValidations) > 0) {
            $errors = array_merge($errors, $stepValidations);
        }

        return $errors;
    }

    /**
     * Avanza el proceso a la siguiente etapa
     */
    public function advance(ContractProcess $process, User $user): void
    {
        // Validar que se puede avanzar
        $errors = $this->canAdvance($process);
        if (count($errors) > 0) {
            throw ValidationException::withMessages([
                'workflow' => $errors
            ]);
        }

        DB::transaction(function () use ($process, $user) {
            $currentStep = $process->current_step;
            $currentStatus = $process->status;

            // Determinar siguiente estado
            $nextStatus = $this->getNextStatus($process);

            if (!$currentStatus->canTransitionTo($nextStatus)) {
                throw ValidationException::withMessages([
                    'workflow' => ['Transición de estado no permitida']
                ]);
            }

            // Marcar etapa actual como completada
            $stepRecord = $process->steps()->where('step_number', $currentStep)->first();
            if ($stepRecord) {
                $stepRecord->markAsCompleted($user);
            }

            // Actualizar proceso
            $oldStatus = $process->status;
            $process->update([
                'status' => $nextStatus,
                'current_step' => $nextStatus->getStepNumber(),
                'updated_by' => $user->id,
            ]);

            // Marcar siguiente etapa como iniciada
            $nextStep = $process->steps()->where('step_number', $nextStatus->getStepNumber())->first();
            if ($nextStep) {
                $nextStep->markAsStarted();
            }

            // Registrar en auditoría
            ProcessAuditLog::logStateChange($process, $oldStatus->value, $nextStatus->value, $user);

            // Enviar notificaciones
            $this->sendAdvanceNotifications($process, $nextStatus);
        });
    }

    /**
     * Devuelve el proceso a una etapa anterior (ej: desde Jurídica)
     */
    public function returnToStep(ContractProcess $process, int $targetStep, string $reason, User $user): void
    {
        DB::transaction(function () use ($process, $targetStep, $reason, $user) {
            // Obtener el estado correspondiente a la etapa objetivo
            $targetStatus = $this->getStatusForStep($targetStep);

            // Actualizar proceso
            $oldStatus = $process->status;
            $process->update([
                'status' => $targetStatus,
                'current_step' => $targetStep,
                'observations' => $reason,
                'updated_by' => $user->id,
            ]);

            // Actualizar step
            $step = $process->steps()->where('step_number', $targetStep)->first();
            if ($step) {
                $step->update([
                    'status' => 'in_progress',
                    'notes' => "Devuelto desde etapa {$process->current_step}: {$reason}",
                ]);
            }

            // Registrar en auditoría
            ProcessAuditLog::logCustomAction(
                $process,
                'returned_to_step',
                "Proceso devuelto a etapa {$targetStep}: {$reason}",
                [
                    'from_step' => $oldStatus->getStepNumber(),
                    'to_step' => $targetStep,
                    'reason' => $reason,
                ],
                $user
            );

            // Notificar al responsable
            if ($process->unit_head_id) {
                ProcessNotification::notifyLegalReturn(
                    $process,
                    $process->unitHead,
                    $reason
                );
            }
        });
    }

    /**
     * Valida reglas específicas por etapa
     */
    protected function validateStepSpecificRules(ContractProcess $process, int $step): array
    {
        $errors = [];

        switch ($step) {
            case 0: // Definición de necesidad
                if (!$process->object || !$process->estimated_value || !$process->term_days) {
                    $errors[] = 'Faltan datos básicos del contrato (objeto, valor, plazo)';
                }
                break;

            case 1: // Solicitud documentos iniciales
                // REGLA CRÍTICA: CDP requiere Compatibilidad del Gasto
                $hasCompatibilidad = $process->documents()
                    ->where('document_type', DocumentType::COMPATIBILIDAD_GASTO->value)
                    ->where('approval_status', ApprovalStatus::APPROVED->value)
                    ->exists();

                $hasCDP = $process->documents()
                    ->where('document_type', DocumentType::CDP->value)
                    ->exists();

                if ($hasCDP && !$hasCompatibilidad) {
                    $errors[] = 'No se puede tener CDP sin Compatibilidad del Gasto aprobada';
                }

                // Validar datos del contratista para paz y salvos
                if (!$process->contractor_name || !$process->contractor_document_number) {
                    $hasPazSalvo = $process->documents()
                        ->whereIn('document_type', [
                            DocumentType::PAZ_SALVO_RENTAS->value,
                            DocumentType::PAZ_SALVO_CONTABILIDAD->value
                        ])
                        ->exists();

                    if ($hasPazSalvo) {
                        $errors[] = 'Se requiere nombre completo y documento del contratista para paz y salvos';
                    }
                }
                break;

            case 2: // Validación del contratista
                // Verificar checklists
                $hasChecklistJuridica = $process->documents()
                    ->where('document_type', DocumentType::CHECKLIST_JURIDICA->value)
                    ->where('approval_status', ApprovalStatus::APPROVED->value)
                    ->exists();

                $hasChecklistAbogado = $process->documents()
                    ->where('document_type', DocumentType::CHECKLIST_ABOGADO_UNIDAD->value)
                    ->where('approval_status', ApprovalStatus::APPROVED->value)
                    ->exists();

                if (!$hasChecklistJuridica) {
                    $errors[] = 'Falta checklist de Secretaría Jurídica';
                }

                if (!$hasChecklistAbogado) {
                    $errors[] = 'Falta checklist de Abogado de Unidad';
                }
                break;

            case 3: // Elaboración documentos contractuales
                // Verificar firmas requeridas
                $docsWithSignatures = [
                    DocumentType::INVITACION_OFERTA,
                    DocumentType::SOLICITUD_CONTRATACION,
                    DocumentType::ACEPTACION_OFERTA_CONTRATISTA,
                ];

                foreach ($docsWithSignatures as $docType) {
                    $doc = $process->documents()
                        ->where('document_type', $docType->value)
                        ->first();

                    if ($doc && $doc->getAllSignaturesPending()) {
                        $errors[] = "Faltan firmas en {$docType->getLabel()}";
                    }
                }
                break;

            case 5: // Radicación en Jurídica
                if (!$process->submitted_to_legal_at) {
                    $errors[] = 'Falta registrar fecha de radicación en Jurídica';
                }

                // Verificar Ajustado a Derecho
                $hasAjustado = $process->documents()
                    ->where('document_type', DocumentType::AJUSTADO_DERECHO->value)
                    ->where('approval_status', ApprovalStatus::APPROVED->value)
                    ->exists();

                if (!$hasAjustado) {
                    $errors[] = 'Falta concepto "Ajustado a Derecho"';
                }
                break;

            case 6: // SECOP II
                if (!$process->secop_id) {
                    $errors[] = 'Falta ID del proceso en SECOP II';
                }

                // Verificar contrato electrónico
                $hasContratoElectronico = $process->documents()
                    ->where('document_type', DocumentType::CONTRATO_ELECTRONICO->value)
                    ->exists();

                if (!$hasContratoElectronico) {
                    $errors[] = 'Falta descargar contrato electrónico de SECOP II';
                }
                break;

            case 7: // RPC
                if (!$process->rpc_number) {
                    $errors[] = 'Falta número de RPC';
                }
                break;

            case 8: // Número de contrato
                if (!$process->contract_number) {
                    $errors[] = 'Falta asignar número de contrato';
                }
                break;
        }

        return $errors;
    }

    /**
     * Obtiene documentos requeridos faltantes
     */
    protected function getMissingRequiredDocuments(ContractProcess $process, int $step): array
    {
        $required = DocumentType::getRequiredByStep($step);
        $existing = $process->documents()
            ->where('step_number', $step)
            ->pluck('document_type')
            ->map(fn($type) => $type->value)
            ->toArray();

        return array_filter($required, function($docType) use ($existing) {
            return !in_array($docType->value, $existing);
        });
    }

    /**
     * Obtiene documentos expirados
     */
    protected function getExpiredDocuments(ContractProcess $process, int $step): array
    {
        return $process->documents()
            ->where('step_number', $step)
            ->where('is_expired', true)
            ->get()
            ->toArray();
    }

    /**
     * Determina el siguiente estado basado en el estado actual
     */
    protected function getNextStatus(ContractProcess $process): ProcessStatus
    {
        $currentStatus = $process->status;
        $allowedTransitions = $currentStatus->allowedTransitions();

        if (count($allowedTransitions) === 0) {
            throw new \Exception('No hay transiciones disponibles desde el estado actual');
        }

        // Para la mayoría de casos, solo hay una transición posible
        if (count($allowedTransitions) === 1) {
            return $allowedTransitions[0];
        }

        // Para etapa 5 (Jurídica), por defecto avanzar a LEGAL_REVIEW_PENDING
        if ($currentStatus === ProcessStatus::PRECONTRACT_FILE_READY) {
            return ProcessStatus::LEGAL_REVIEW_PENDING;
        }

        // Si hay múltiples opciones, retornar la primera (puede personalizarse)
        return $allowedTransitions[0];
    }

    /**
     * Obtiene el estado correspondiente a una etapa
     */
    protected function getStatusForStep(int $step): ProcessStatus
    {
        return match($step) {
            0 => ProcessStatus::NEED_DEFINED,
            1 => ProcessStatus::INITIAL_DOCS_PENDING,
            2 => ProcessStatus::CONTRACTOR_VALIDATION,
            3 => ProcessStatus::CONTRACT_DOCS_DRAFTED,
            4 => ProcessStatus::PRECONTRACT_FILE_READY,
            5 => ProcessStatus::LEGAL_REVIEW_PENDING,
            6 => ProcessStatus::SECOP_PUBLISHED_AND_SIGNED,
            7 => ProcessStatus::RPC_REQUESTED,
            8 => ProcessStatus::CONTRACT_NUMBER_ASSIGNED,
            9 => ProcessStatus::STARTED,
            default => throw new \Exception("Etapa {$step} no válida"),
        };
    }

    /**
     * Envía notificaciones al avanzar de etapa
     */
    protected function sendAdvanceNotifications(ContractProcess $process, ProcessStatus $newStatus): void
    {
        $step = $newStatus->getStepNumber();

        // Notificar según la etapa
        match($step) {
            1 => $this->notifyForInitialDocs($process),
            2 => $this->notifyForContractorValidation($process),
            5 => $this->notifyForLegalReview($process),
            6 => $this->notifyForSecopPublication($process),
            7 => $this->notifyForRpc($process),
            9 => $this->notifyForActaInicio($process),
            default => null,
        };
    }

    protected function notifyForInitialDocs(ContractProcess $process): void
    {
        if ($process->unit_head_id) {
            ProcessNotification::create([
                'process_id' => $process->id,
                'user_id' => $process->unit_head_id,
                'type' => 'step_advanced',
                'title' => 'Solicitud de Documentos Iniciales',
                'message' => "El proceso {$process->process_number} requiere documentos iniciales (PAA, CDP, etc.)",
            ]);
        }
    }

    protected function notifyForContractorValidation(ContractProcess $process): void
    {
        if ($process->unit_lawyer_id) {
            ProcessNotification::create([
                'process_id' => $process->id,
                'user_id' => $process->unit_lawyer_id,
                'type' => 'step_advanced',
                'title' => 'Validación de Contratista',
                'message' => "El proceso {$process->process_number} requiere validación de documentos del contratista",
            ]);
        }
    }

    protected function notifyForLegalReview(ContractProcess $process): void
    {
        if ($process->link_lawyer_id) {
            ProcessNotification::create([
                'process_id' => $process->id,
                'user_id' => $process->link_lawyer_id,
                'type' => 'step_advanced',
                'title' => 'Revisión Jurídica Pendiente',
                'message' => "El proceso {$process->process_number} ha sido radicado en Secretaría Jurídica para revisión",
            ]);
        }
    }

    protected function notifyForSecopPublication(ContractProcess $process): void
    {
        // Notificar a múltiples usuarios para SECOP
        $users = collect([
            $process->link_lawyer_id,
            $process->contractor_id,
            $process->ordering_officer_id
        ])->filter()->unique();

        foreach ($users as $userId) {
            ProcessNotification::notifySecopSignatureReady($process, User::find($userId));
        }
    }

    protected function notifyForRpc(ContractProcess $process): void
    {
        if ($process->unit_head_id) {
            ProcessNotification::create([
                'process_id' => $process->id,
                'user_id' => $process->unit_head_id,
                'type' => 'step_advanced',
                'title' => 'Solicitar RPC',
                'message' => "El proceso {$process->process_number} está listo para solicitar RPC",
            ]);
        }
    }

    protected function notifyForActaInicio(ContractProcess $process): void
    {
        $users = collect([
            $process->supervisor_id,
            $process->contractor_id
        ])->filter()->unique();

        foreach ($users as $userId) {
            ProcessNotification::notifyReadyForActaInicio($process, User::find($userId));
        }
    }

    /**
     * Obtiene definiciones de todas las etapas
     */
    protected function getStepDefinitions(): array
    {
        return [
            0 => [
                'name' => 'Definición de Necesidad',
                'requirements' => [
                    'Crear Estudios Previos',
                    'Definir objeto, valor estimado y plazo',
                    'Enviar a Unidad de Descentralización/Planeación',
                ],
            ],
            1 => [
                'name' => 'Solicitud de Documentos Iniciales',
                'requirements' => [
                    'PAA',
                    'No Planta',
                    'Paz y Salvo Rentas',
                    'Paz y Salvo Contabilidad',
                    'Compatibilidad del Gasto',
                    'CDP (solo después de Compatibilidad)',
                ],
            ],
            2 => [
                'name' => 'Validación del Contratista',
                'requirements' => [
                    'Documentos del contratista',
                    'Checklist Secretaría Jurídica',
                    'Checklist Abogado de Unidad',
                ],
            ],
            3 => [
                'name' => 'Elaboración Documentos Contractuales',
                'requirements' => [
                    'Invitación a presentar oferta',
                    'Solicitud de contratación',
                    'Designación de supervisor',
                    'Certificado de idoneidad',
                    'Estudios previos',
                    'Análisis del sector',
                ],
            ],
            4 => [
                'name' => 'Consolidación Expediente Precontractual',
                'requirements' => [
                    'Agrupar toda la documentación',
                    'Validar vigencias',
                    'Validar firmas completas',
                ],
            ],
            5 => [
                'name' => 'Radicación en Secretaría Jurídica',
                'requirements' => [
                    'Registro en SharePoint',
                    'Revisión abogado enlace',
                    'Ajustado a Derecho',
                    'Firma de contrato',
                ],
            ],
            6 => [
                'name' => 'Publicación y Firma en SECOP II',
                'requirements' => [
                    'Cargar proceso en SECOP II',
                    'Aprobación creación',
                    'Firma contratista',
                    'Firma Secretario Privado',
                    'Descargar contrato electrónico',
                ],
            ],
            7 => [
                'name' => 'Solicitud RPC',
                'requirements' => [
                    'Imprimir contrato electrónico',
                    'Adjuntar Ajustado a Derecho',
                    'Solicitud firmada por Secretario de Planeación',
                    'Radicar en Hacienda',
                    'Esperar expedición RPC',
                ],
            ],
            8 => [
                'name' => 'Radicación Final y Número de Contrato',
                'requirements' => [
                    'Radicar expediente físico final',
                    'Obtener número de contrato',
                ],
            ],
            9 => [
                'name' => 'Afiliaciones y Acta de Inicio',
                'requirements' => [
                    'Solicitar ARL',
                    'Elaborar Acta de Inicio',
                    'Registrar inicio en SECOP II',
                ],
            ],
        ];
    }
}
