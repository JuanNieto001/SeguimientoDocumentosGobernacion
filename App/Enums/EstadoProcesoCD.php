<?php

namespace App\Enums;

/**
 * Estados del proceso de Contratación Directa – Persona Natural
 * Implementa máquina de estados con transiciones y validaciones por rol.
 */
enum EstadoProcesoCD: string
{
    // ── ETAPA 1: Estudios Previos ──
    case BORRADOR = 'borrador';
    case ESTUDIO_PREVIO_CARGADO = 'estudio_previo_cargado';

    // ── ETAPA 2: Validaciones Presupuestales ──
    case EN_VALIDACION_PLANEACION = 'en_validacion_planeacion';
    case COMPATIBILIDAD_APROBADA = 'compatibilidad_aprobada';
    case CDP_SOLICITADO = 'cdp_solicitado';
    case CDP_APROBADO = 'cdp_aprobado';
    case CDP_BLOQUEADO = 'cdp_bloqueado';

    // ── ETAPA 3: Validación Hoja de Vida ──
    case DOCUMENTACION_INCOMPLETA = 'documentacion_incompleta';
    case DOCUMENTACION_VALIDADA = 'documentacion_validada';
    case EN_REVISION_JURIDICA = 'en_revision_juridica';

    // ── ETAPA 4: Revisión Jurídica ──
    case PROCESO_NUMERO_GENERADO = 'proceso_numero_generado';
    case GENERACION_CONTRATO = 'generacion_contrato';

    // ── ETAPA 5: Generación y Firma de Contrato ──
    case CONTRATO_GENERADO = 'contrato_generado';
    case CONTRATO_FIRMADO_PARCIAL = 'contrato_firmado_parcial';
    case CONTRATO_FIRMADO_TOTAL = 'contrato_firmado_total';
    case CONTRATO_DEVUELTO = 'contrato_devuelto';

    // ── ETAPA 6: RPC ──
    case RPC_SOLICITADO = 'rpc_solicitado';
    case RPC_FIRMADO = 'rpc_firmado';
    case EXPEDIENTE_RADICADO = 'expediente_radicado';

    // ── ETAPA 7: Inicio de Ejecución ──
    case EN_EJECUCION = 'en_ejecucion';

    // ── Estados especiales ──
    case CANCELADO = 'cancelado';
    case SUSPENDIDO = 'suspendido';

    // ═══════════════════════════════════════════════
    //  ETAPA A LA QUE PERTENECE CADA ESTADO
    // ═══════════════════════════════════════════════
    public function etapa(): int
    {
        return match ($this) {
            self::BORRADOR, self::ESTUDIO_PREVIO_CARGADO => 1,

            self::EN_VALIDACION_PLANEACION,
            self::COMPATIBILIDAD_APROBADA,
            self::CDP_SOLICITADO,
            self::CDP_APROBADO,
            self::CDP_BLOQUEADO => 2,

            self::DOCUMENTACION_INCOMPLETA,
            self::DOCUMENTACION_VALIDADA,
            self::EN_REVISION_JURIDICA => 3,

            self::PROCESO_NUMERO_GENERADO,
            self::GENERACION_CONTRATO => 4,

            self::CONTRATO_GENERADO,
            self::CONTRATO_FIRMADO_PARCIAL,
            self::CONTRATO_FIRMADO_TOTAL,
            self::CONTRATO_DEVUELTO => 5,

            self::RPC_SOLICITADO,
            self::RPC_FIRMADO,
            self::EXPEDIENTE_RADICADO => 6,

            self::EN_EJECUCION => 7,

            self::CANCELADO, self::SUSPENDIDO => -1,
        };
    }

    // ═══════════════════════════════════════════════
    //  LABEL LEGIBLE
    // ═══════════════════════════════════════════════
    public function label(): string
    {
        return match ($this) {
            self::BORRADOR                    => 'Borrador',
            self::ESTUDIO_PREVIO_CARGADO      => 'Estudio Previo Cargado',
            self::EN_VALIDACION_PLANEACION    => 'En Validación – Planeación',
            self::COMPATIBILIDAD_APROBADA     => 'Compatibilidad del Gasto Aprobada',
            self::CDP_SOLICITADO              => 'CDP Solicitado',
            self::CDP_APROBADO                => 'CDP Aprobado',
            self::CDP_BLOQUEADO               => 'CDP Bloqueado (sin compatibilidad)',
            self::DOCUMENTACION_INCOMPLETA    => 'Documentación Incompleta',
            self::DOCUMENTACION_VALIDADA      => 'Documentación Validada',
            self::EN_REVISION_JURIDICA        => 'En Revisión Jurídica',
            self::PROCESO_NUMERO_GENERADO     => 'Número de Proceso Generado',
            self::GENERACION_CONTRATO         => 'Generación de Contrato',
            self::CONTRATO_GENERADO           => 'Contrato Generado',
            self::CONTRATO_FIRMADO_PARCIAL    => 'Contrato Firmado Parcialmente',
            self::CONTRATO_FIRMADO_TOTAL      => 'Contrato Firmado Totalmente',
            self::CONTRATO_DEVUELTO           => 'Contrato Devuelto',
            self::RPC_SOLICITADO              => 'RPC Solicitado',
            self::RPC_FIRMADO                 => 'RPC Firmado',
            self::EXPEDIENTE_RADICADO         => 'Expediente Final Radicado',
            self::EN_EJECUCION                => 'En Ejecución',
            self::CANCELADO                   => 'Cancelado',
            self::SUSPENDIDO                  => 'Suspendido',
        };
    }

    // ═══════════════════════════════════════════════
    //  CLASE CSS PARA BADGE
    // ═══════════════════════════════════════════════
    public function badgeClass(): string
    {
        return match ($this) {
            self::BORRADOR                    => 'bg-gray-100 text-gray-800',
            self::ESTUDIO_PREVIO_CARGADO      => 'bg-blue-100 text-blue-800',
            self::EN_VALIDACION_PLANEACION    => 'bg-yellow-100 text-yellow-800',
            self::COMPATIBILIDAD_APROBADA     => 'bg-green-100 text-green-800',
            self::CDP_SOLICITADO              => 'bg-yellow-100 text-yellow-800',
            self::CDP_APROBADO                => 'bg-green-100 text-green-800',
            self::CDP_BLOQUEADO               => 'bg-red-100 text-red-800',
            self::DOCUMENTACION_INCOMPLETA    => 'bg-orange-100 text-orange-800',
            self::DOCUMENTACION_VALIDADA      => 'bg-green-100 text-green-800',
            self::EN_REVISION_JURIDICA        => 'bg-indigo-100 text-indigo-800',
            self::PROCESO_NUMERO_GENERADO     => 'bg-purple-100 text-purple-800',
            self::GENERACION_CONTRATO         => 'bg-blue-100 text-blue-800',
            self::CONTRATO_GENERADO           => 'bg-blue-100 text-blue-800',
            self::CONTRATO_FIRMADO_PARCIAL    => 'bg-yellow-100 text-yellow-800',
            self::CONTRATO_FIRMADO_TOTAL      => 'bg-green-100 text-green-800',
            self::CONTRATO_DEVUELTO           => 'bg-red-100 text-red-800',
            self::RPC_SOLICITADO              => 'bg-yellow-100 text-yellow-800',
            self::RPC_FIRMADO                 => 'bg-green-100 text-green-800',
            self::EXPEDIENTE_RADICADO         => 'bg-green-100 text-green-800',
            self::EN_EJECUCION                => 'bg-emerald-100 text-emerald-800',
            self::CANCELADO                   => 'bg-red-100 text-red-800',
            self::SUSPENDIDO                  => 'bg-gray-200 text-gray-600',
        };
    }

    // ═══════════════════════════════════════════════
    //  TRANSICIONES PERMITIDAS
    // ═══════════════════════════════════════════════
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            // Etapa 1
            self::BORRADOR                    => [self::ESTUDIO_PREVIO_CARGADO],
            self::ESTUDIO_PREVIO_CARGADO      => [self::EN_VALIDACION_PLANEACION],

            // Etapa 2
            self::EN_VALIDACION_PLANEACION    => [self::COMPATIBILIDAD_APROBADA, self::CDP_BLOQUEADO],
            self::COMPATIBILIDAD_APROBADA     => [self::CDP_SOLICITADO],
            self::CDP_SOLICITADO              => [self::CDP_APROBADO],
            self::CDP_APROBADO                => [self::DOCUMENTACION_INCOMPLETA],
            self::CDP_BLOQUEADO               => [self::EN_VALIDACION_PLANEACION], // reintento

            // Etapa 3
            self::DOCUMENTACION_INCOMPLETA    => [self::DOCUMENTACION_VALIDADA],
            self::DOCUMENTACION_VALIDADA      => [self::EN_REVISION_JURIDICA],

            // Etapa 4
            self::EN_REVISION_JURIDICA        => [self::PROCESO_NUMERO_GENERADO, self::DOCUMENTACION_INCOMPLETA],
            self::PROCESO_NUMERO_GENERADO     => [self::GENERACION_CONTRATO],
            self::GENERACION_CONTRATO         => [self::CONTRATO_GENERADO],

            // Etapa 5
            self::CONTRATO_GENERADO           => [self::CONTRATO_FIRMADO_PARCIAL, self::CONTRATO_DEVUELTO],
            self::CONTRATO_FIRMADO_PARCIAL    => [self::CONTRATO_FIRMADO_TOTAL, self::CONTRATO_DEVUELTO],
            self::CONTRATO_FIRMADO_TOTAL      => [self::RPC_SOLICITADO],
            self::CONTRATO_DEVUELTO           => [self::CONTRATO_GENERADO],

            // Etapa 6
            self::RPC_SOLICITADO              => [self::RPC_FIRMADO],
            self::RPC_FIRMADO                 => [self::EXPEDIENTE_RADICADO],
            self::EXPEDIENTE_RADICADO         => [self::EN_EJECUCION],

            // Etapa 7
            self::EN_EJECUCION                => [],

            // Especiales
            self::CANCELADO                   => [],
            self::SUSPENDIDO                  => [],
        };
    }

    public function puedeTransicionarA(self $destino): bool
    {
        return in_array($destino, $this->transicionesPermitidas());
    }

    public function esFinal(): bool
    {
        return in_array($this, [self::EN_EJECUCION, self::CANCELADO]);
    }

    // ═══════════════════════════════════════════════
    //  ROLES AUTORIZADOS PARA EJECUTAR TRANSICIÓN
    // ═══════════════════════════════════════════════
    public function rolesAutorizados(): array
    {
        return match ($this) {
            // Etapa 1 – Estudios Previos
            self::BORRADOR                    => ['unidad_solicitante', 'admin'],
            self::ESTUDIO_PREVIO_CARGADO      => ['unidad_solicitante', 'admin'],

            // Etapa 2 – Planeación / Hacienda
            self::EN_VALIDACION_PLANEACION    => ['planeacion', 'admin'],
            self::COMPATIBILIDAD_APROBADA     => ['planeacion', 'admin'],
            self::CDP_SOLICITADO              => ['planeacion', 'admin'],
            self::CDP_APROBADO                => ['hacienda', 'admin'],
            self::CDP_BLOQUEADO               => ['planeacion', 'admin'],

            // Etapa 3 – Contratista / Abogado Unidad
            self::DOCUMENTACION_INCOMPLETA    => ['unidad_solicitante', 'admin'],
            self::DOCUMENTACION_VALIDADA      => ['unidad_solicitante', 'admin'],

            // Etapa 4 – Secretaría Jurídica
            self::EN_REVISION_JURIDICA        => ['juridica', 'admin'],
            self::PROCESO_NUMERO_GENERADO     => ['juridica', 'admin'],
            self::GENERACION_CONTRATO         => ['juridica', 'admin'],

            // Etapa 5 – Contrato
            self::CONTRATO_GENERADO           => ['juridica', 'admin'],
            self::CONTRATO_FIRMADO_PARCIAL    => ['unidad_solicitante', 'juridica', 'admin'],
            self::CONTRATO_FIRMADO_TOTAL      => ['unidad_solicitante', 'juridica', 'admin'],
            self::CONTRATO_DEVUELTO           => ['juridica', 'admin'],

            // Etapa 6 – RPC
            self::RPC_SOLICITADO              => ['planeacion', 'admin'],
            self::RPC_FIRMADO                 => ['hacienda', 'admin'],
            self::EXPEDIENTE_RADICADO         => ['planeacion', 'hacienda', 'admin'],

            // Etapa 7 – Ejecución
            self::EN_EJECUCION                => ['unidad_solicitante', 'admin'],

            self::CANCELADO                   => ['admin'],
            self::SUSPENDIDO                  => ['admin'],
        };
    }

    // ═══════════════════════════════════════════════
    //  DOCUMENTOS OBLIGATORIOS POR ESTADO
    // ═══════════════════════════════════════════════
    public function documentosObligatorios(): array
    {
        return match ($this) {
            self::ESTUDIO_PREVIO_CARGADO => ['estudios_previos'],

            self::EN_VALIDACION_PLANEACION => ['estudios_previos'],

            self::COMPATIBILIDAD_APROBADA => [
                'paa', 'no_planta', 'paz_salvo_rentas',
                'paz_salvo_contabilidad', 'compatibilidad_gasto',
            ],

            self::CDP_APROBADO => ['cdp'],

            self::DOCUMENTACION_VALIDADA => [
                'hoja_vida_sigep', 'cedula', 'rut',
                'antecedentes_disciplinarios', 'antecedentes_fiscales',
                'antecedentes_judiciales', 'seguridad_social_salud',
                'seguridad_social_pension', 'certificado_cuenta_bancaria',
            ],

            self::PROCESO_NUMERO_GENERADO => ['checklist_juridica'],

            self::CONTRATO_GENERADO => ['contrato_electronico'],

            self::CONTRATO_FIRMADO_TOTAL => ['contrato_firmado'],

            self::RPC_FIRMADO => ['solicitud_rpc', 'rpc'],

            self::EXPEDIENTE_RADICADO => ['expediente_fisico_final'],

            self::EN_EJECUCION => ['solicitud_arl', 'acta_inicio'],

            default => [],
        };
    }

    // ═══════════════════════════════════════════════
    //  HELPERS ESTÁTICOS
    // ═══════════════════════════════════════════════
    public static function estadosActivos(): array
    {
        return array_filter(self::cases(), fn (self $e) => !in_array($e, [self::CANCELADO, self::SUSPENDIDO]));
    }

    public static function estadosPorEtapa(int $etapa): array
    {
        return array_filter(self::cases(), fn (self $e) => $e->etapa() === $etapa);
    }
}
