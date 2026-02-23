<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposArchivoSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateIfExists('tipos_archivo_por_etapa');

        // Obtener workflows
        $workflows = DB::table('workflows')->get()->keyBy('codigo');

        // ======================================
        // TIPOS DE ARCHIVO PARA CD_PN
        // ======================================
        if (isset($workflows['CD_PN'])) {
            $this->seedTiposCD_PN($workflows['CD_PN']->id);
        }

        // ======================================
        // WORKFLOWS FUTUROS (comentados)
        // Los tipos de archivo para MC, SA, LP, CM
        // se habilitarán cuando se activen esos workflows.
        // ======================================
        // if (isset($workflows['MC'])) $this->seedTiposMC($workflows['MC']->id);
        // if (isset($workflows['SA'])) $this->seedTiposSA($workflows['SA']->id);
        // if (isset($workflows['LP'])) $this->seedTiposLP($workflows['LP']->id);
        // if (isset($workflows['CM'])) $this->seedTiposCM($workflows['CM']->id);

        $this->command->info('✅ Tipos de archivo por etapa creados correctamente.');
    }

    private function seedTiposCD_PN($workflowId)
    {
        // Helper: busca etapa por orden dentro del workflow
        $e = fn(int $orden) => DB::table('etapas')
            ->where('workflow_id', $workflowId)
            ->where('orden', $orden)
            ->first();

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 0 – Estudio Previo y Remisión  [Unidad Solicitante]
        //   Documentos que la unidad solicitante elabora y envía a Planeación.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(0)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos',      'requerido' => true,  'orden' => 1],
                ['tipo' => 'formato_necesidades',       'label' => 'Formato de Necesidades',            'requerido' => true,  'orden' => 2],
                ['tipo' => 'cotizaciones',               'label' => 'Cotizaciones (mínimo 3)',           'requerido' => true,  'orden' => 3],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 1 – Solicitud de Documentos Iniciales  [Planeación]
        //   Planeación reúne/solicita y recibe: EP Finales, Análisis del Sector,
        //   Matriz de Riesgos, CDP y Certificado de Compatibilidad con el PAA.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(1)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'estudios_previos_finales',     'label' => 'Estudios Previos Finales',                         'requerido' => true,  'orden' => 1],
                ['tipo' => 'analisis_sector',              'label' => 'Análisis del Sector',                              'requerido' => true,  'orden' => 2],
                ['tipo' => 'matriz_riesgos',               'label' => 'Matriz de Riesgos',                                'requerido' => true,  'orden' => 3],
                ['tipo' => 'cdp',                          'label' => 'Certificado de Disponibilidad Presupuestal (CDP)', 'requerido' => true,  'orden' => 4],
                ['tipo' => 'certificado_compatibilidad',   'label' => 'Certificado de Compatibilidad con el PAA',         'requerido' => true,  'orden' => 5],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 2 – Recepción y Validación de Documentos del Contratista  [Unidad Solicitante]
        //   El contratista entrega TODOS los documentos personales/legales.
        //   La Sec. Jurídica valida la HV. El abogado de la unidad revisa checklist.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(2)) {
            $this->insertTipos($etapa->id, [
                // Documentos de identidad y tributarios
                ['tipo' => 'cedula_contratista',              'label' => 'Cédula de Ciudadanía (copia legible)',                                   'requerido' => true,  'orden' => 1],
                ['tipo' => 'rut',                             'label' => 'RUT actualizado (datos y actividad económica)',                            'requerido' => true,  'orden' => 2],
                ['tipo' => 'certificado_cuenta_bancaria',     'label' => 'Certificado de Cuenta Bancaria (vigencia inferior a 30 días)',             'requerido' => true,  'orden' => 3],
                // Documentos académicos y de experiencia
                ['tipo' => 'soportes_academicos',             'label' => 'Certificados de Estudio (diplomas/actas de grado pregrado y posgrado)',   'requerido' => true,  'orden' => 4],
                ['tipo' => 'certificados_experiencia',        'label' => 'Certificados de Experiencia Laboral',                                      'requerido' => true,  'orden' => 5],
                ['tipo' => 'tarjeta_profesional',             'label' => 'Matrícula o Tarjeta Profesional (cuando aplique)',                        'requerido' => false, 'orden' => 6],
                ['tipo' => 'ausencia_sanciones_profesionales','label' => 'Constancia de Ausencia de Sanciones Disciplinarias Profesionales',        'requerido' => false, 'orden' => 7],
                // Documentos de seguridad social
                ['tipo' => 'seguridad_social_salud',          'label' => 'Constancia de Afiliación Salud (mes anterior)',                           'requerido' => true,  'orden' => 8],
                ['tipo' => 'seguridad_social_pension',        'label' => 'Constancia de Afiliación Pensión (mes anterior)',                          'requerido' => true,  'orden' => 9],
                ['tipo' => 'certificado_medico',              'label' => 'Certificado de Examen Médico de Aptitud Laboral vigente',                 'requerido' => true,  'orden' => 10],
                ['tipo' => 'situacion_militar',               'label' => 'Acreditación de la Situación Militar (cuando aplique)',                    'requerido' => false, 'orden' => 11],
                // Antecedentes (todos con vigencia ≤ 30 días)
                ['tipo' => 'antecedentes_disciplinarios',     'label' => 'Certificado Antecedentes Disciplinarios (Procuraduría)',                   'requerido' => true,  'orden' => 12],
                ['tipo' => 'antecedentes_fiscales',           'label' => 'Certificado Antecedentes Fiscales (Contraloría General)',                  'requerido' => true,  'orden' => 13],
                ['tipo' => 'antecedentes_judiciales',         'label' => 'Certificado Antecedentes Judiciales (Policía Nacional)',                   'requerido' => true,  'orden' => 14],
                ['tipo' => 'antecedentes_medidas_correctivas','label' => 'Certificado de Ausencia de Medidas Correctivas',                           'requerido' => true,  'orden' => 15],
                ['tipo' => 'delitos_sexuales',                'label' => 'Antecedentes de Delitos Sexuales',                                         'requerido' => true,  'orden' => 16],
                ['tipo' => 'redam',                           'label' => 'Registro de Deudores Alimentarios Morosos (REDAM)',                        'requerido' => true,  'orden' => 17],
                ['tipo' => 'inhabilidades_incompatibilidades','label' => 'Certificado de Inhabilidades e Incompatibilidades',                        'requerido' => true,  'orden' => 18],
                // Declaraciones SIGEP / Ley 2013
                ['tipo' => 'declaracion_bienes_rentas_sigep', 'label' => 'Declaración de Bienes y Rentas SIGEP II (descargado de plataforma)',      'requerido' => true,  'orden' => 19],
                ['tipo' => 'declaracion_conflicto_intereses', 'label' => 'Declaración Bienes/Renta y Conflicto de Intereses (Ley 2013)',            'requerido' => true,  'orden' => 20],
                // HV y validación jurídica
                ['tipo' => 'hoja_vida_sigep',                 'label' => 'Hoja de Vida SIGEP (cargada en plataforma SIGEP II)',                    'requerido' => true,  'orden' => 21],
                ['tipo' => 'hv_validada_juridica',            'label' => 'Hoja de Vida SIGEP validada por Secretaría Jurídica',                     'requerido' => true,  'orden' => 22],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 3 – Elaboración de Documentos Contractuales  [Unidad Solicitante / Abogado]
        //   El abogado de la unidad proyecta todos los documentos oficiales del
        //   proceso. Las firmas requeridas: Ordenador del Gasto, Supervisor,
        //   Contratista (acepta oferta). Honorarios según tabla de la Gobernación.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(3)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'invitacion_oferta',       'label' => 'Invitación a Presentar Oferta (Formato Almera)',                'requerido' => true,  'orden' => 1],
                ['tipo' => 'aceptacion_oferta',       'label' => 'Aceptación de la Oferta firmada por el Contratista',          'requerido' => true,  'orden' => 2],
                ['tipo' => 'solicitud_contratacion',  'label' => 'Solicitud de Contratación y Designación de Supervisor',       'requerido' => true,  'orden' => 3],
                ['tipo' => 'certificado_idoneidad',   'label' => 'Certificado de Idoneidad y Experiencia del Contratista',       'requerido' => true,  'orden' => 4],
                ['tipo' => 'estudios_previos_finales','label' => 'Estudios Previos con Anexos (versión definitiva, firmada)',   'requerido' => true,  'orden' => 5],
                ['tipo' => 'analisis_sector',         'label' => 'Análisis del Sector (incluido estudio del mercado)',          'requerido' => true,  'orden' => 6],
                ['tipo' => 'minuta_contrato',         'label' => 'Minuta del Contrato (proyección para revisión jurídica)',     'requerido' => true,  'orden' => 7],
                ['tipo' => 'ficha_bpin',              'label' => 'Ficha BPIN (si aplica para proyectos de inversión)',          'requerido' => false, 'orden' => 8],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 4 – Compilación de Carpeta Precontractual  [Unidad Solicitante]
        //   Paso de organización interna; no requiere carga de archivos nuevos
        //   (todos los documentos ya fueron cargados en etapas anteriores).
        // ─────────────────────────────────────────────────────────────────────
        // Sin tipos de archivo: es una etapa de verificación/checklist.

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 5 – Radicación en Secretaría Jurídica  [Jurídica]
        //   Jurídica recibe la carpeta y genera el radicado de recepción.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(5)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'radicado_recepcion_juridica', 'label' => 'Radicado de Recepción (Secretaría Jurídica)', 'requerido' => false, 'orden' => 1],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 6 – Revisión Jurídica y Ajustado a Derecho  [Jurídica]
        //   Jurídica emite el concepto "Ajustado a Derecho".
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(6)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'ajustado_derecho', 'label' => 'Ajustado a Derecho (Concepto Jurídico)', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 7 – Estructuración en SECOP II  [SECOP / Unidad de Compras]
        //   La Unidad de Compras estructura y publica el proceso en SECOP II.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(7)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'proceso_secop', 'label' => 'Proceso Estructurado/Publicado en SECOP II', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 8 – Aprobaciones y Firmas en SECOP II  [SECOP]
        //   El contrato es aprobado y firmado digitalmente por ambas partes.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(8)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'contrato_firmado',    'label' => 'Contrato Firmado Digitalmente',              'requerido' => true, 'orden' => 1],
                ['tipo' => 'contrato_electronico','label' => 'Contrato Electrónico registrado en SECOP II','requerido' => true, 'orden' => 2],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 9 – Solicitud de RPC  [Planeación / Unidad de Descentralización]
        //   Planeación solicita formalmente el Registro Presupuestal a Hacienda.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(9)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'solicitud_rpc', 'label' => 'Solicitud de Registro Presupuestal de Compromiso (RPC)', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 10 – Expedición del RPC  [Hacienda]
        //   Hacienda expide el Registro Presupuestal de Compromiso.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(10)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'registro_presupuestal', 'label' => 'Registro Presupuestal de Compromiso (RPC)', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 11 – Organización del Expediente Físico  [Unidad Solicitante]
        //   Paso de organización; los documentos ya existen en etapas anteriores.
        // ─────────────────────────────────────────────────────────────────────
        // Sin tipos de archivo: es una etapa de organización/checklist.

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 12 – Radicación del Expediente y Asignación Nº Contrato  [Jurídica]
        //   Jurídica recibe el expediente físico, radica y asigna número de contrato.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(12)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'comprobante_radicacion', 'label' => 'Comprobante de Radicación del Expediente', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 13 – Solicitud de ARL  [Unidad Solicitante]
        //   La unidad gestiona la afiliación a la ARL del contratista.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(13)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'poliza_arl',        'label' => 'Póliza / Certificado de ARL (Accidente Laboral)', 'requerido' => true,  'orden' => 1],
                ['tipo' => 'afiliacion_arl',    'label' => 'Constancia de Afiliación a ARL',                  'requerido' => false, 'orden' => 2],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 14 – Elaboración y Firma del Acta de Inicio  [Unidad Solicitante]
        //   La unidad elabora y hace firmar el Acta de Inicio por ambas partes.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(14)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'acta_inicio', 'label' => 'Acta de Inicio (firmada por ambas partes)', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // ORDEN 15 – Inicio de Ejecución en SECOP II  [SECOP / Unidad de Compras]
        //   La Unidad de Compras carga el Acta de Inicio en SECOP II y registra
        //   el inicio formal de la ejecución del contrato.
        // ─────────────────────────────────────────────────────────────────────
        if ($etapa = $e(15)) {
            $this->insertTipos($etapa->id, [
                ['tipo' => 'acta_inicio_secop', 'label' => 'Acta de Inicio registrada en SECOP II', 'requerido' => true, 'orden' => 1],
            ]);
        }
    }

    private function seedTiposMC($workflowId)
    {
        // Etapa 0A: Unidad Solicitante (igual que CD_PN)
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Agregar tipos específicos para MC...
        // (Similar estructura, diferentes etapas según modalidad)
    }

    private function seedTiposSA($workflowId)
    {
        // Similar a MC pero con prepliegos
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }
    }

    private function seedTiposLP($workflowId)
    {
        // Incluye documentos de audiencias exclusivas
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Etapa 10: Audiencia de Riesgos (exclusiva LP)
        $etapaRiesgos = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 11)->first();
        if ($etapaRiesgos) {
            $this->insertTipos($etapaRiesgos->id, [
                ['tipo' => 'acta_audiencia_riesgos', 'label' => 'Acta de Audiencia de Riesgos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'matriz_riesgos_ajustada', 'label' => 'Matriz de Riesgos Ajustada', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 15: Audiencia Pública de Adjudicación (exclusiva LP)
        $etapaAudiencia = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 16)->first();
        if ($etapaAudiencia) {
            $this->insertTipos($etapaAudiencia->id, [
                ['tipo' => 'acta_audiencia_adjudicacion', 'label' => 'Acta de Audiencia Pública', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'resolucion_adjudicacion', 'label' => 'Resolución de Adjudicación', 'requerido' => true, 'orden' => 2],
            ]);
        }
    }

    private function seedTiposCM($workflowId)
    {
        // CM: NO requiere Viabilidad Económica al inicio
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Etapa 11: Evaluación Técnica (exclusiva CM - sin precio)
        $etapaEvalTecnica = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 12)->first();
        if ($etapaEvalTecnica) {
            $this->insertTipos($etapaEvalTecnica->id, [
                ['tipo' => 'informe_evaluacion_tecnica', 'label' => 'Informe de Evaluación Técnica', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'puntajes_tecnicos', 'label' => 'Cuadro de Puntajes Técnicos', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 14: Negociación (exclusiva CM)
        $etapaNegociacion = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 15)->first();
        if ($etapaNegociacion) {
            $this->insertTipos($etapaNegociacion->id, [
                ['tipo' => 'acta_negociacion', 'label' => 'Acta de Negociación', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'propuesta_economica', 'label' => 'Propuesta Económica Negociada', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 15: Viabilidad Económica DESPUÉS de negociación (exclusivo CM)
        $etapaViabilidad = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 16)->first();
        if ($etapaViabilidad) {
            $this->insertTipos($etapaViabilidad->id, [
                ['tipo' => 'viabilidad_economica_negociada', 'label' => 'Viabilidad Económica del Valor Negociado', 'requerido' => true, 'orden' => 1],
            ]);
        }
    }

    private function insertTipos($etapaId, $tipos)
    {
        foreach ($tipos as $tipo) {
            DB::table('tipos_archivo_por_etapa')->insert([
                'etapa_id' => $etapaId,
                'tipo_archivo' => $tipo['tipo'],
                'label' => $tipo['label'],
                'requerido' => $tipo['requerido'],
                'orden' => $tipo['orden'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function truncateIfExists(string $table): void
    {
        try {
            DB::statement("DELETE FROM {$table}");
            if (DB::getDriverName() === 'sqlite') {
                DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
            }
        } catch (\Exception $e) {
            // Tabla no existe, ignorar
        }
    }
}
