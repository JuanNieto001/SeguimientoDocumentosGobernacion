<?php

namespace App\Services;

use App\Enums\EstadoProcesoCD;
use App\Models\ProcesoCDAuditoria;
use App\Models\ProcesoCDDocumento;
use App\Models\ProcesoContratacionDirecta;
use App\Models\User;
use App\Services\NotificacionCDService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Máquina de estados para el flujo de Contratación Directa – Persona Natural.
 *
 * Controla:
 *  - Validación de transiciones (no se puede saltar estados)
 *  - Validación de rol autorizado
 *  - Validación de documentos obligatorios
 *  - Registro de auditoría en cada transición
 */
class ContratoDirectoPNStateMachine
{
    // ═══════════════════════════════════════════════
    //  1. CREAR SOLICITUD (Etapa 1)
    // ═══════════════════════════════════════════════

    /**
     * Crea un nuevo proceso de contratación directa.
     * Requiere estudio previo subido ANTES de crear.
     */
    public function crearSolicitud(array $datos, User $user): ProcesoContratacionDirecta
    {
        // Validar que se suba estudio previo
        if (empty($datos['estudio_previo_path'])) {
            throw ValidationException::withMessages([
                'estudio_previo' => ['Debe subir el documento de Estudios Previos ANTES de crear la solicitud.'],
            ]);
        }

        // Validar plazo en meses (entero)
        if (!isset($datos['plazo_meses']) || !is_int((int) $datos['plazo_meses']) || (int) $datos['plazo_meses'] < 1) {
            throw ValidationException::withMessages([
                'plazo_meses' => ['El plazo solo acepta un número entero en meses.'],
            ]);
        }

        return DB::transaction(function () use ($datos, $user) {
            $proceso = ProcesoContratacionDirecta::create(array_merge($datos, [
                'estado'       => EstadoProcesoCD::ESTUDIO_PREVIO_CARGADO,
                'etapa_actual' => 1,
                'creado_por'   => $user->id,
            ]));

            // Auditoría
            ProcesoCDAuditoria::registrarCambioEstado(
                $proceso,
                EstadoProcesoCD::BORRADOR->value,
                EstadoProcesoCD::ESTUDIO_PREVIO_CARGADO->value,
                $user,
                'Solicitud creada con Estudio Previo cargado.'
            );

            // Registrar el Estudio Previo como documento del proceso
            if (!empty($datos['estudio_previo_path'])) {
                ProcesoCDDocumento::create([
                    'proceso_cd_id'     => $proceso->id,
                    'tipo_documento'    => 'estudios_previos',
                    'nombre_archivo'    => basename($datos['estudio_previo_path']),
                    'ruta_archivo'      => $datos['estudio_previo_path'],
                    'mime_type'         => 'application/pdf',
                    'tamano_bytes'      => 0,
                    'etapa'             => 1,
                    'estado_aprobacion' => 'aprobado',
                    'subido_por'        => $user->id,
                    'es_obligatorio'    => true,
                ]);
            }

            // Enviar automáticamente a Planeación (Descentralización)
            $this->transicionar(
                $proceso,
                EstadoProcesoCD::EN_VALIDACION_PLANEACION,
                $user,
                'Estudio Previo enviado automáticamente a Planeación (Descentralización).'
            );

            return $proceso->fresh();
        });
    }

    // ═══════════════════════════════════════════════
    //  2. TRANSICIÓN GENÉRICA
    // ═══════════════════════════════════════════════

    /**
     * Ejecuta una transición de estado validando:
     *  1. Que la transición sea permitida
     *  2. Que el usuario tenga rol autorizado
     *  3. Que los documentos obligatorios estén cargados
     */
    public function transicionar(
        ProcesoContratacionDirecta $proceso,
        EstadoProcesoCD $destino,
        User $user,
        ?string $comentario = null,
        array $datosExtra = []
    ): ProcesoContratacionDirecta {
        $estadoActual = $proceso->estado;

        // 1. Validar transición
        if (!$estadoActual->puedeTransicionarA($destino)) {
            throw ValidationException::withMessages([
                'transicion' => [
                    "No se puede pasar de «{$estadoActual->label()}» a «{$destino->label()}»."
                ],
            ]);
        }

        // 2. Validar rol
        $rolesPermitidos = $destino->rolesAutorizados();
        $tieneRol = false;
        foreach ($rolesPermitidos as $rol) {
            if ($user->hasRole($rol)) {
                $tieneRol = true;
                break;
            }
        }
        if (!$tieneRol) {
            throw ValidationException::withMessages([
                'permiso' => ['No tiene el rol necesario para ejecutar esta acción.'],
            ]);
        }

        // 3. Validar documentos obligatorios para el estado destino
        $faltantes = $proceso->documentosFaltantes($destino);
        if (!empty($faltantes)) {
            $labels = implode(', ', $faltantes);
            throw ValidationException::withMessages([
                'documentos' => ["Faltan documentos obligatorios: {$labels}"],
            ]);
        }

        // 4. Validaciones específicas por estado
        $this->validacionesEspecificas($proceso, $destino);

        return DB::transaction(function () use ($proceso, $destino, $user, $estadoActual, $comentario, $datosExtra) {
            $proceso->update([
                'estado'       => $destino,
                'etapa_actual' => $destino->etapa(),
            ]);

            // Aplicar cambios de campos específicos según el estado
            $this->aplicarCambiosEstado($proceso, $destino, $datosExtra);

            ProcesoCDAuditoria::registrarCambioEstado(
                $proceso,
                $estadoActual->value,
                $destino->value,
                $user,
                $comentario,
                $datosExtra
            );

            // Enviar notificaciones a los usuarios del rol correspondiente
            NotificacionCDService::notificarTransicion($proceso, $destino, $user);

            return $proceso->fresh();
        });
    }

    // ═══════════════════════════════════════════════
    //  3. VALIDACIONES ESPECÍFICAS POR ESTADO
    // ═══════════════════════════════════════════════

    protected function validacionesEspecificas(ProcesoContratacionDirecta $proceso, EstadoProcesoCD $destino): void
    {
        $errores = [];

        switch ($destino) {
            // ── Etapa 2: CDP requiere compatibilidad aprobada ──
            case EstadoProcesoCD::CDP_SOLICITADO:
                if (!$proceso->compatibilidad_aprobada) {
                    $errores[] = 'No se puede solicitar CDP sin Compatibilidad del Gasto aprobada.';
                }
                if (!$proceso->validacionesParalelasCompletas()) {
                    $errores[] = 'Deben completarse todas las validaciones paralelas (PAA, No Planta, Paz y Salvos, Compatibilidad).';
                }
                break;

            case EstadoProcesoCD::CDP_BLOQUEADO:
                // Se bloquea cuando la compatibilidad NO está aprobada
                break;

            case EstadoProcesoCD::COMPATIBILIDAD_APROBADA:
                if (!$proceso->compatibilidad_gasto) {
                    $errores[] = 'La compatibilidad del gasto no ha sido verificada.';
                }
                break;

            // ── Etapa 3: Documentación del contratista ──
            case EstadoProcesoCD::DOCUMENTACION_VALIDADA:
                if (!$proceso->hoja_vida_cargada) {
                    $errores[] = 'Debe cargar la Hoja de Vida SIGEP.';
                }
                if (!$proceso->checklist_validado) {
                    $errores[] = 'El checklist debe ser validado por el Abogado de Unidad.';
                }
                break;

            // ── Etapa 4: Revisión Jurídica devuelve ──
            case EstadoProcesoCD::DOCUMENTACION_INCOMPLETA:
                // Puede venir de EN_REVISION_JURIDICA (devolución)
                break;

            case EstadoProcesoCD::PROCESO_NUMERO_GENERADO:
                if (!$proceso->numero_proceso_juridica) {
                    $errores[] = 'Secretaría Jurídica debe asignar número de proceso (CD-PS-XX-2026).';
                }
                break;

            // ── Etapa 5: Firma de contrato ──
            case EstadoProcesoCD::CONTRATO_FIRMADO_PARCIAL:
                if (!$proceso->firma_contratista && !$proceso->firma_ordenador_gasto) {
                    $errores[] = 'Al menos una firma (contratista u ordenador del gasto) debe registrarse.';
                }
                break;

            case EstadoProcesoCD::CONTRATO_FIRMADO_TOTAL:
                if (!$proceso->firma_contratista || !$proceso->firma_ordenador_gasto) {
                    $errores[] = 'Se requieren ambas firmas: Contratista y Ordenador del Gasto.';
                }
                break;

            // ── Etapa 6: RPC ──
            case EstadoProcesoCD::RPC_FIRMADO:
                if (!$proceso->numero_rpc) {
                    $errores[] = 'Debe registrar el número de RPC.';
                }
                break;

            // ── Etapa 7: Ejecución ──
            case EstadoProcesoCD::EN_EJECUCION:
                if (!$proceso->numero_contrato) {
                    $errores[] = 'Debe asignarse número de contrato.';
                }
                if (!$proceso->arl_solicitada) {
                    $errores[] = 'Debe solicitarse la ARL.';
                }
                if (!$proceso->acta_inicio_firmada) {
                    $errores[] = 'El Acta de Inicio debe estar firmada.';
                }
                break;
        }

        if (!empty($errores)) {
            throw ValidationException::withMessages(['validacion' => $errores]);
        }
    }

    // ═══════════════════════════════════════════════
    //  4. ACCIONES ESPECÍFICAS AL CAMBIAR ESTADO
    // ═══════════════════════════════════════════════

    protected function aplicarCambiosEstado(ProcesoContratacionDirecta $proceso, EstadoProcesoCD $destino, array $datos): void
    {
        switch ($destino) {
            case EstadoProcesoCD::COMPATIBILIDAD_APROBADA:
                $proceso->update(['compatibilidad_aprobada' => true]);
                break;

            case EstadoProcesoCD::CDP_APROBADO:
                $proceso->update([
                    'cdp_aprobado' => true,
                    'numero_cdp'   => $datos['numero_cdp'] ?? $proceso->numero_cdp,
                    'valor_cdp'    => $datos['valor_cdp'] ?? $proceso->valor_cdp,
                ]);
                break;

            case EstadoProcesoCD::CDP_BLOQUEADO:
                $proceso->update(['cdp_aprobado' => false]);
                break;

            case EstadoProcesoCD::DOCUMENTACION_VALIDADA:
                $proceso->update([
                    'documentos_contratista_completos' => true,
                    'resultado_validacion'             => $datos['resultado'] ?? 'Documentación completa y validada.',
                ]);
                break;

            case EstadoProcesoCD::PROCESO_NUMERO_GENERADO:
                $proceso->update([
                    'numero_proceso_juridica' => $datos['numero_proceso'] ?? $proceso->numero_proceso_juridica,
                ]);
                break;

            case EstadoProcesoCD::GENERACION_CONTRATO:
                $proceso->update(['revision_juridica_aprobada' => true]);
                break;

            case EstadoProcesoCD::CONTRATO_DEVUELTO:
                $proceso->update([
                    'observaciones_devolucion' => $datos['observaciones'] ?? null,
                    'firma_contratista'        => false,
                    'firma_ordenador_gasto'    => false,
                ]);
                break;

            case EstadoProcesoCD::CONTRATO_GENERADO:
                // Reset firmas al regenerar contrato
                if ($proceso->estado === EstadoProcesoCD::CONTRATO_DEVUELTO) {
                    $proceso->update([
                        'firma_contratista'     => false,
                        'firma_ordenador_gasto' => false,
                        'observaciones_devolucion' => null,
                    ]);
                }
                break;

            case EstadoProcesoCD::RPC_FIRMADO:
                $proceso->update([
                    'rpc_firmado_flag' => true,
                    'numero_rpc'      => $datos['numero_rpc'] ?? $proceso->numero_rpc,
                ]);
                break;

            case EstadoProcesoCD::EXPEDIENTE_RADICADO:
                $proceso->update(['expediente_radicado_flag' => true]);
                break;

            case EstadoProcesoCD::EN_EJECUCION:
                $proceso->update([
                    'fecha_inicio_ejecucion' => $datos['fecha_inicio'] ?? now(),
                    'numero_contrato'        => $datos['numero_contrato'] ?? $proceso->numero_contrato,
                ]);
                break;

            case EstadoProcesoCD::DOCUMENTACION_INCOMPLETA:
                // Al devolver desde revisión jurídica
                if (isset($datos['observaciones_juridica'])) {
                    $proceso->update([
                        'observaciones_juridica' => $datos['observaciones_juridica'],
                        'checklist_validado'     => false,
                    ]);
                }
                break;
        }
    }

    // ═══════════════════════════════════════════════
    //  5. ACCIONES DE ETAPAS ESPECÍFICAS
    // ═══════════════════════════════════════════════

    /**
     * Etapa 2: Registrar validación paralela individual.
     */
    public function registrarValidacionParalela(
        ProcesoContratacionDirecta $proceso,
        string $campo,
        User $user
    ): ProcesoContratacionDirecta {
        $camposPermitidos = [
            'paa_solicitado',
            'certificado_no_planta',
            'paz_salvo_rentas',
            'paz_salvo_contabilidad',
            'compatibilidad_gasto',
        ];

        if (!in_array($campo, $camposPermitidos)) {
            throw ValidationException::withMessages([
                'campo' => ["Campo '{$campo}' no es una validación paralela válida."],
            ]);
        }

        $proceso->update([$campo => true]);

        ProcesoCDAuditoria::registrarAccion(
            $proceso,
            'validacion_paralela',
            "Validación paralela completada: {$campo}",
            ['campo' => $campo],
            $user
        );

        return $proceso->fresh();
    }

    /**
     * Etapa 5: Registrar firma en contrato.
     */
    public function registrarFirma(
        ProcesoContratacionDirecta $proceso,
        string $tipoFirma,
        User $user
    ): ProcesoContratacionDirecta {
        $campo = match ($tipoFirma) {
            'contratista'      => 'firma_contratista',
            'ordenador_gasto'  => 'firma_ordenador_gasto',
            default => throw ValidationException::withMessages([
                'firma' => ["Tipo de firma '{$tipoFirma}' no válido."],
            ]),
        };

        $proceso->update([$campo => true]);

        ProcesoCDAuditoria::registrarAccion(
            $proceso,
            'firma_contrato',
            "Firma registrada: {$tipoFirma}",
            ['tipo_firma' => $tipoFirma],
            $user
        );

        // Auto-transicionar si corresponde
        if ($proceso->firma_contratista && $proceso->firma_ordenador_gasto) {
            return $this->transicionar(
                $proceso,
                EstadoProcesoCD::CONTRATO_FIRMADO_TOTAL,
                $user,
                'Ambas firmas completadas.'
            );
        } elseif ($proceso->firma_contratista || $proceso->firma_ordenador_gasto) {
            if ($proceso->estado === EstadoProcesoCD::CONTRATO_GENERADO) {
                return $this->transicionar(
                    $proceso,
                    EstadoProcesoCD::CONTRATO_FIRMADO_PARCIAL,
                    $user,
                    'Primera firma registrada.'
                );
            }
        }

        return $proceso->fresh();
    }

    /**
     * Etapa 4: Devolver con observaciones desde revisión jurídica.
     */
    public function devolverDesdeJuridica(
        ProcesoContratacionDirecta $proceso,
        string $observaciones,
        User $user
    ): ProcesoContratacionDirecta {
        return $this->transicionar(
            $proceso,
            EstadoProcesoCD::DOCUMENTACION_INCOMPLETA,
            $user,
            "Devuelto por Secretaría Jurídica: {$observaciones}",
            ['observaciones_juridica' => $observaciones]
        );
    }

    /**
     * Etapa 5: Devolver contrato con observaciones.
     */
    public function devolverContrato(
        ProcesoContratacionDirecta $proceso,
        string $observaciones,
        User $user
    ): ProcesoContratacionDirecta {
        return $this->transicionar(
            $proceso,
            EstadoProcesoCD::CONTRATO_DEVUELTO,
            $user,
            "Contrato devuelto: {$observaciones}",
            ['observaciones' => $observaciones]
        );
    }

    /**
     * Cancelar proceso.
     */
    public function cancelar(
        ProcesoContratacionDirecta $proceso,
        string $motivo,
        User $user
    ): ProcesoContratacionDirecta {
        if (!$user->hasRole('admin')) {
            throw ValidationException::withMessages([
                'permiso' => ['Solo un administrador puede cancelar un proceso.'],
            ]);
        }

        $estadoAnterior = $proceso->estado->value;

        $proceso->update(['estado' => EstadoProcesoCD::CANCELADO]);

        ProcesoCDAuditoria::registrarCambioEstado(
            $proceso,
            $estadoAnterior,
            EstadoProcesoCD::CANCELADO->value,
            $user,
            "Proceso cancelado: {$motivo}",
            ['motivo' => $motivo]
        );

        return $proceso->fresh();
    }

    // ═══════════════════════════════════════════════
    //  6. CONSULTAS / VALIDACIÓN DE AVANCE
    // ═══════════════════════════════════════════════

    /**
     * Retorna los errores que impiden avanzar al siguiente estado.
     */
    public function erroresParaAvanzar(ProcesoContratacionDirecta $proceso): array
    {
        $transiciones = $proceso->estado->transicionesPermitidas();

        if (empty($transiciones)) {
            return ['El proceso se encuentra en un estado final.'];
        }

        $errores = [];
        foreach ($transiciones as $destino) {
            $faltantes = $proceso->documentosFaltantes($destino);
            if (!empty($faltantes)) {
                $errores["documentos_{$destino->value}"] = "Para «{$destino->label()}» faltan: " . implode(', ', $faltantes);
            }
        }

        return $errores;
    }

    /**
     * ¿Puede el proceso avanzar a algún estado siguiente?
     */
    public function puedeAvanzar(ProcesoContratacionDirecta $proceso, User $user): bool
    {
        $transiciones = $proceso->estado->transicionesPermitidas();

        foreach ($transiciones as $destino) {
            $rolesPermitidos = $destino->rolesAutorizados();
            foreach ($rolesPermitidos as $rol) {
                if ($user->hasRole($rol)) {
                    $faltantes = $proceso->documentosFaltantes($destino);
                    if (empty($faltantes)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
