<?php

namespace App\Enums;

enum DocumentType: string
{
    // ETAPA 0 - Definición de necesidad
    case ESTUDIOS_PREVIOS = 'estudios_previos';
    case EVIDENCIA_ENVIO_UNIDAD = 'evidencia_envio_unidad';
    
    // ETAPA 1 - Documentos iniciales
    case PAA = 'paa';
    case NO_PLANTA = 'no_planta';
    case PAZ_SALVO_RENTAS = 'paz_salvo_rentas';
    case PAZ_SALVO_CONTABILIDAD = 'paz_salvo_contabilidad';
    case COMPATIBILIDAD_GASTO = 'compatibilidad_gasto';
    case CDP = 'cdp';
    
    // ETAPA 2 - Documentos del contratista
    case HOJA_VIDA_SIGEP = 'hoja_vida_sigep';
    case CERTIFICADO_ESTUDIO = 'certificado_estudio';
    case CERTIFICADO_EXPERIENCIA = 'certificado_experiencia';
    case RUT = 'rut';
    case CEDULA = 'cedula';
    case ANTECEDENTES_DISCIPLINARIOS = 'antecedentes_disciplinarios';
    case ANTECEDENTES_FISCALES = 'antecedentes_fiscales';
    case ANTECEDENTES_JUDICIALES = 'antecedentes_judiciales';
    case MEDIDAS_CORRECTIVAS = 'medidas_correctivas';
    case ANTECEDENTES_DELITOS_SEXUALES = 'antecedentes_delitos_sexuales';
    case REDAM = 'redam';
    case SEGURIDAD_SOCIAL_SALUD = 'seguridad_social_salud';
    case SEGURIDAD_SOCIAL_PENSION = 'seguridad_social_pension';
    case CERTIFICADO_CUENTA_BANCARIA = 'certificado_cuenta_bancaria';
    case CERTIFICADO_MEDICO = 'certificado_medico';
    case TARJETA_PROFESIONAL = 'tarjeta_profesional';
    
    // ETAPA 2 - Validaciones
    case CHECKLIST_JURIDICA = 'checklist_juridica';
    case CHECKLIST_ABOGADO_UNIDAD = 'checklist_abogado_unidad';
    
    // ETAPA 3 - Documentos contractuales
    case INVITACION_OFERTA = 'invitacion_oferta';
    case SOLICITUD_CONTRATACION = 'solicitud_contratacion';
    case DESIGNACION_SUPERVISOR = 'designacion_supervisor';
    case CERTIFICADO_IDONEIDAD = 'certificado_idoneidad';
    case ANALISIS_SECTOR = 'analisis_sector';
    case ACEPTACION_OFERTA_CONTRATISTA = 'aceptacion_oferta_contratista';
    
    // ETAPA 4 - Consolidación
    case EXPEDIENTE_CONSOLIDADO = 'expediente_consolidado';
    case BPIN = 'bpin'; // Si aplica
    
    // ETAPA 5 - Jurídica
    case RADICADO_JURIDICA = 'radicado_juridica';
    case OBSERVACIONES_JURIDICA = 'observaciones_juridica';
    case AJUSTADO_DERECHO = 'ajustado_derecho';
    case CONTRATO_FIRMADO = 'contrato_firmado';
    
    // ETAPA 6 - SECOP II
    case PROCESO_SECOP = 'proceso_secop';
    case CONTRATO_ELECTRONICO = 'contrato_electronico';
    
    // ETAPA 7 - RPC
    case SOLICITUD_RPC = 'solicitud_rpc';
    case RPC = 'rpc';
    
    // ETAPA 8 - Radicación final
    case EXPEDIENTE_FISICO_FINAL = 'expediente_fisico_final';
    
    // ETAPA 9 - Inicio
    case SOLICITUD_ARL = 'solicitud_arl';
    case ACTA_INICIO = 'acta_inicio';
    case REGISTRO_INICIO_SECOP = 'registro_inicio_secop';

    /**
     * Obtiene el label legible del tipo de documento
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ESTUDIOS_PREVIOS => 'Estudios Previos',
            self::EVIDENCIA_ENVIO_UNIDAD => 'Evidencia de Envío a Unidad',
            self::PAA => 'PAA (Plan Anual de Adquisiciones)',
            self::NO_PLANTA => 'Certificado No Planta',
            self::PAZ_SALVO_RENTAS => 'Paz y Salvo Rentas',
            self::PAZ_SALVO_CONTABILIDAD => 'Paz y Salvo Contabilidad',
            self::COMPATIBILIDAD_GASTO => 'Compatibilidad del Gasto',
            self::CDP => 'CDP (Certificado de Disponibilidad Presupuestal)',
            self::HOJA_VIDA_SIGEP => 'Hoja de Vida SIGEP',
            self::CERTIFICADO_ESTUDIO => 'Certificados de Estudio',
            self::CERTIFICADO_EXPERIENCIA => 'Certificados de Experiencia',
            self::RUT => 'RUT',
            self::CEDULA => 'Cédula de Ciudadanía',
            self::ANTECEDENTES_DISCIPLINARIOS => 'Antecedentes Disciplinarios',
            self::ANTECEDENTES_FISCALES => 'Antecedentes Fiscales',
            self::ANTECEDENTES_JUDICIALES => 'Antecedentes Judiciales',
            self::MEDIDAS_CORRECTIVAS => 'Medidas Correctivas',
            self::ANTECEDENTES_DELITOS_SEXUALES => 'Antecedentes Delitos Sexuales',
            self::REDAM => 'REDAM',
            self::SEGURIDAD_SOCIAL_SALUD => 'Seguridad Social - Salud',
            self::SEGURIDAD_SOCIAL_PENSION => 'Seguridad Social - Pensión',
            self::CERTIFICADO_CUENTA_BANCARIA => 'Certificado Cuenta Bancaria',
            self::CERTIFICADO_MEDICO => 'Certificado Médico',
            self::TARJETA_PROFESIONAL => 'Tarjeta Profesional',
            self::CHECKLIST_JURIDICA => 'Checklist Secretaría Jurídica',
            self::CHECKLIST_ABOGADO_UNIDAD => 'Checklist Abogado de Unidad',
            self::INVITACION_OFERTA => 'Invitación a Presentar Oferta',
            self::SOLICITUD_CONTRATACION => 'Solicitud de Contratación',
            self::DESIGNACION_SUPERVISOR => 'Designación de Supervisor',
            self::CERTIFICADO_IDONEIDAD => 'Certificado de Idoneidad',
            self::ANALISIS_SECTOR => 'Análisis del Sector',
            self::ACEPTACION_OFERTA_CONTRATISTA => 'Aceptación de Oferta (Contratista)',
            self::EXPEDIENTE_CONSOLIDADO => 'Expediente Precontractual Consolidado',
            self::BPIN => 'BPIN',
            self::RADICADO_JURIDICA => 'Radicado Secretaría Jurídica',
            self::OBSERVACIONES_JURIDICA => 'Observaciones Jurídica',
            self::AJUSTADO_DERECHO => 'Ajustado a Derecho',
            self::CONTRATO_FIRMADO => 'Contrato Firmado',
            self::PROCESO_SECOP => 'Proceso SECOP II',
            self::CONTRATO_ELECTRONICO => 'Contrato Electrónico',
            self::SOLICITUD_RPC => 'Solicitud RPC',
            self::RPC => 'RPC',
            self::EXPEDIENTE_FISICO_FINAL => 'Expediente Físico Final',
            self::SOLICITUD_ARL => 'Solicitud ARL',
            self::ACTA_INICIO => 'Acta de Inicio',
            self::REGISTRO_INICIO_SECOP => 'Registro Inicio en SECOP II',
        };
    }

    /**
     * Determina si el documento requiere vigencia/expiración
     */
    public function requiresExpiration(): bool
    {
        return in_array($this, [
            self::ANTECEDENTES_DISCIPLINARIOS,
            self::ANTECEDENTES_FISCALES,
            self::ANTECEDENTES_JUDICIALES,
            self::MEDIDAS_CORRECTIVAS,
            self::ANTECEDENTES_DELITOS_SEXUALES,
            self::CERTIFICADO_CUENTA_BANCARIA,
            self::CERTIFICADO_MEDICO,
            self::SEGURIDAD_SOCIAL_SALUD,
            self::SEGURIDAD_SOCIAL_PENSION,
        ]);
    }

    /**
     * Días de validez del documento (si aplica)
     */
    public function getValidityDays(): ?int
    {
        return match($this) {
            self::ANTECEDENTES_DISCIPLINARIOS,
            self::ANTECEDENTES_FISCALES,
            self::ANTECEDENTES_JUDICIALES,
            self::MEDIDAS_CORRECTIVAS,
            self::ANTECEDENTES_DELITOS_SEXUALES,
            self::CERTIFICADO_CUENTA_BANCARIA => 30,
            self::CERTIFICADO_MEDICO => 90,
            self::SEGURIDAD_SOCIAL_SALUD,
            self::SEGURIDAD_SOCIAL_PENSION => 30, // mes anterior
            default => null,
        };
    }

    /**
     * Obtiene los documentos obligatorios por etapa
     */
    public static function getRequiredByStep(int $step): array
    {
        return match($step) {
            0 => [self::ESTUDIOS_PREVIOS, self::EVIDENCIA_ENVIO_UNIDAD],
            1 => [
                self::PAA, 
                self::NO_PLANTA, 
                self::PAZ_SALVO_RENTAS, 
                self::PAZ_SALVO_CONTABILIDAD, 
                self::COMPATIBILIDAD_GASTO, 
                self::CDP
            ],
            2 => [
                self::HOJA_VIDA_SIGEP,
                self::CERTIFICADO_ESTUDIO,
                self::CERTIFICADO_EXPERIENCIA,
                self::RUT,
                self::CEDULA,
                self::ANTECEDENTES_DISCIPLINARIOS,
                self::ANTECEDENTES_FISCALES,
                self::ANTECEDENTES_JUDICIALES,
                self::MEDIDAS_CORRECTIVAS,
                self::ANTECEDENTES_DELITOS_SEXUALES,
                self::REDAM,
                self::SEGURIDAD_SOCIAL_SALUD,
                self::SEGURIDAD_SOCIAL_PENSION,
                self::CERTIFICADO_CUENTA_BANCARIA,
                self::CERTIFICADO_MEDICO,
                self::CHECKLIST_JURIDICA,
                self::CHECKLIST_ABOGADO_UNIDAD,
            ],
            3 => [
                self::INVITACION_OFERTA,
                self::SOLICITUD_CONTRATACION,
                self::DESIGNACION_SUPERVISOR,
                self::CERTIFICADO_IDONEIDAD,
                self::ANALISIS_SECTOR,
                self::ACEPTACION_OFERTA_CONTRATISTA,
            ],
            4 => [self::EXPEDIENTE_CONSOLIDADO],
            5 => [self::RADICADO_JURIDICA, self::AJUSTADO_DERECHO, self::CONTRATO_FIRMADO],
            6 => [self::PROCESO_SECOP, self::CONTRATO_ELECTRONICO],
            7 => [self::SOLICITUD_RPC, self::RPC],
            8 => [self::EXPEDIENTE_FISICO_FINAL],
            9 => [self::SOLICITUD_ARL, self::ACTA_INICIO, self::REGISTRO_INICIO_SECOP],
            default => [],
        };
    }

    /**
     * Verifica si el documento requiere firma
     */
    public function requiresSignature(): bool
    {
        return in_array($this, [
            self::ESTUDIOS_PREVIOS,
            self::INVITACION_OFERTA,
            self::SOLICITUD_CONTRATACION,
            self::DESIGNACION_SUPERVISOR,
            self::CERTIFICADO_IDONEIDAD,
            self::ACEPTACION_OFERTA_CONTRATISTA,
            self::AJUSTADO_DERECHO,
            self::CONTRATO_FIRMADO,
            self::CONTRATO_ELECTRONICO,
            self::SOLICITUD_RPC,
            self::ACTA_INICIO,
        ]);
    }
}
