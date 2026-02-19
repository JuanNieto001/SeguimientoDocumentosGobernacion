<?php

namespace App\Enums;

enum ProcessStatus: string
{
    // ETAPA 0 - Definición de necesidad
    case NEED_DEFINED = 'need_defined';
    
    // ETAPA 1 - Solicitud documentos iniciales
    case INITIAL_DOCS_PENDING = 'initial_docs_pending';
    
    // ETAPA 2 - Validación del contratista
    case CONTRACTOR_VALIDATION = 'contractor_validation';
    
    // ETAPA 3 - Elaboración documentos contractuales
    case CONTRACT_DOCS_DRAFTED = 'contract_docs_drafted';
    
    // ETAPA 4 - Consolidación expediente precontractual
    case PRECONTRACT_FILE_READY = 'precontract_file_ready';
    
    // ETAPA 5 - Radicación en Secretaría Jurídica (con subestados)
    case LEGAL_REVIEW_PENDING = 'legal_review_pending';
    case RETURNED_FOR_FIXES = 'returned_for_fixes';
    case ADJUSTED_OK = 'adjusted_ok';
    case SIGNED = 'signed';
    
    // ETAPA 6 - Publicación SECOP II
    case SECOP_PUBLISHED_AND_SIGNED = 'secop_published_and_signed';
    
    // ETAPA 7 - Solicitud RPC
    case RPC_REQUESTED = 'rpc_requested';
    case RPC_ISSUED = 'rpc_issued';
    
    // ETAPA 8 - Radicación final
    case CONTRACT_NUMBER_ASSIGNED = 'contract_number_assigned';
    
    // ETAPA 9 - Afiliaciones y acta de inicio
    case STARTED = 'started';
    
    // Estados adicionales
    case CANCELLED = 'cancelled';
    case SUSPENDED = 'suspended';

    /**
     * Obtiene el número de etapa correspondiente al estado
     */
    public function getStepNumber(): int
    {
        return match($this) {
            self::NEED_DEFINED => 0,
            self::INITIAL_DOCS_PENDING => 1,
            self::CONTRACTOR_VALIDATION => 2,
            self::CONTRACT_DOCS_DRAFTED => 3,
            self::PRECONTRACT_FILE_READY => 4,
            self::LEGAL_REVIEW_PENDING, self::RETURNED_FOR_FIXES, self::ADJUSTED_OK, self::SIGNED => 5,
            self::SECOP_PUBLISHED_AND_SIGNED => 6,
            self::RPC_REQUESTED, self::RPC_ISSUED => 7,
            self::CONTRACT_NUMBER_ASSIGNED => 8,
            self::STARTED => 9,
            default => -1,
        };
    }

    /**
     * Obtiene el nombre legible del estado
     */
    public function getLabel(): string
    {
        return match($this) {
            self::NEED_DEFINED => 'Definición de Necesidad',
            self::INITIAL_DOCS_PENDING => 'Solicitud Documentos Iniciales',
            self::CONTRACTOR_VALIDATION => 'Validación del Contratista',
            self::CONTRACT_DOCS_DRAFTED => 'Elaboración Documentos Contractuales',
            self::PRECONTRACT_FILE_READY => 'Expediente Precontractual Consolidado',
            self::LEGAL_REVIEW_PENDING => 'Radicado en Jurídica - Pendiente Revisión',
            self::RETURNED_FOR_FIXES => 'Devuelto con Observaciones',
            self::ADJUSTED_OK => 'Ajustado a Derecho',
            self::SIGNED => 'Contrato Firmado',
            self::SECOP_PUBLISHED_AND_SIGNED => 'Publicado y Firmado en SECOP II',
            self::RPC_REQUESTED => 'RPC Solicitado',
            self::RPC_ISSUED => 'RPC Expedido',
            self::CONTRACT_NUMBER_ASSIGNED => 'Número de Contrato Asignado',
            self::STARTED => 'Acta de Inicio - Ejecución Iniciada',
            self::CANCELLED => 'Cancelado',
            self::SUSPENDED => 'Suspendido',
        };
    }

    /**
     * Obtiene los posibles siguientes estados
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::NEED_DEFINED => [self::INITIAL_DOCS_PENDING],
            self::INITIAL_DOCS_PENDING => [self::CONTRACTOR_VALIDATION],
            self::CONTRACTOR_VALIDATION => [self::CONTRACT_DOCS_DRAFTED],
            self::CONTRACT_DOCS_DRAFTED => [self::PRECONTRACT_FILE_READY],
            self::PRECONTRACT_FILE_READY => [self::LEGAL_REVIEW_PENDING],
            self::LEGAL_REVIEW_PENDING => [self::RETURNED_FOR_FIXES, self::ADJUSTED_OK],
            self::RETURNED_FOR_FIXES => [self::LEGAL_REVIEW_PENDING], // vuelve a revisión
            self::ADJUSTED_OK => [self::SIGNED],
            self::SIGNED => [self::SECOP_PUBLISHED_AND_SIGNED],
            self::SECOP_PUBLISHED_AND_SIGNED => [self::RPC_REQUESTED],
            self::RPC_REQUESTED => [self::RPC_ISSUED],
            self::RPC_ISSUED => [self::CONTRACT_NUMBER_ASSIGNED],
            self::CONTRACT_NUMBER_ASSIGNED => [self::STARTED],
            self::STARTED => [], // Estado final
            self::CANCELLED => [],
            self::SUSPENDED => [], // Puede reactivarse manualmente
        };
    }

    /**
     * Verifica si puede transicionar al estado dado
     */
    public function canTransitionTo(ProcessStatus $targetStatus): bool
    {
        return in_array($targetStatus, $this->allowedTransitions());
    }

    /**
     * Verifica si es un estado final
     */
    public function isFinalState(): bool
    {
        return in_array($this, [self::STARTED, self::CANCELLED]);
    }

    /**
     * Obtiene todos los estados activos (no cancelados/suspendidos)
     */
    public static function activeStatuses(): array
    {
        return array_filter(self::cases(), fn($status) => 
            !in_array($status, [self::CANCELLED, self::SUSPENDED])
        );
    }
}
