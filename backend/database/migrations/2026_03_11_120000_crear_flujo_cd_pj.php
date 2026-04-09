<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ╔══════════════════════════════════════════════════════════════════════════════╗
 * ║  Migración – Flujo CD-PJ (Contratación Directa Persona Jurídica)         ║
 * ╠══════════════════════════════════════════════════════════════════════════════╣
 * ║  Basado en el flujo CD-PN existente, adaptado para Persona Jurídica.      ║
 * ║  Diferencias principales:                                                 ║
 * ║  ─ Etapa 1: Sin Certificado No Planta, sin Paz y Salvos individuales      ║
 * ║  ─ Etapa 2: Sin docs personales PN (SIGEP HV, diplomas, experiencia,     ║
 * ║    seguridad social individual, examen médico, matrícula profesional,     ║
 * ║    sanciones profesionales, situación militar, inhabilidades)             ║
 * ║  + Agrega: Certificado existencia y representación legal,                 ║
 * ║    Autorización órgano societario, Certificación seguridad social 6m      ║
 * ║  ─ Etapa 4: Consolidación ajustada sin los items removidos               ║
 * ║  ─ Etapas 0, 3, 5-9: Iguales a CD-PN                                     ║
 * ╚══════════════════════════════════════════════════════════════════════════════╝
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        // ═══════════════════════════════════════════════════════════════
        // 1) CREAR FLUJO EN EL MOTOR DE FLUJOS
        // ═══════════════════════════════════════════════════════════════
        $secPlaneacion = DB::table('secretarias')->where('nombre', 'like', '%Planeación%')->value('id');
        if (!$secPlaneacion) {
            $secPlaneacion = DB::table('secretarias')->first()?->id ?? 1;
        }

        $flujoId = DB::table('flujos')->insertGetId([
            'codigo'            => 'CD_PJ',
            'nombre'            => 'Contratación Directa - Persona Jurídica',
            'descripcion'       => 'Flujo oficial de Contratación Directa Persona Jurídica de la Gobernación de Caldas. Similar a CD-PN pero sin documentos personales del contratista (SIGEP HV, diplomas, experiencia, seguridad social individual) y con documentos propios de persona jurídica (existencia y representación legal, autorización órgano societario, certificación seguridad social 6 meses).',
            'tipo_contratacion' => 'cd_pj',
            'secretaria_id'     => $secPlaneacion,
            'activo'            => true,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $versionId = DB::table('flujo_versiones')->insertGetId([
            'flujo_id'        => $flujoId,
            'numero_version'  => 1,
            'motivo_cambio'   => 'Versión inicial del flujo CD-PJ.',
            'estado'          => 'activa',
            'publicada_at'    => $now,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        DB::table('flujos')->where('id', $flujoId)->update(['version_activa_id' => $versionId]);

        // Obtener IDs del catálogo de pasos (ya creado por MotorFlujosSeeder)
        $pasoIds = DB::table('catalogo_pasos')->pluck('id', 'codigo');

        // Pasos del flujo CD-PJ (misma estructura que CD-PN)
        $pasos = [
            ['catalogo' => 'DEF_NECESIDAD',   'orden' => 0,  'area' => 'unidad_solicitante', 'dias' => 5],
            ['catalogo' => 'DESC_DOCS',        'orden' => 1,  'area' => 'planeacion',         'dias' => 3],
            ['catalogo' => 'VAL_CONTRATISTA',  'orden' => 2,  'area' => 'unidad_solicitante', 'dias' => 3],
            ['catalogo' => 'ELAB_DOCS',        'orden' => 3,  'area' => 'unidad_solicitante', 'dias' => 5],
            ['catalogo' => 'CONSOL_EXP',       'orden' => 4,  'area' => 'unidad_solicitante', 'dias' => 2],
            ['catalogo' => 'RAD_JURIDICA',     'orden' => 5,  'area' => 'juridica',           'dias' => 5],
            ['catalogo' => 'PUB_SECOP',        'orden' => 6,  'area' => 'secop',              'dias' => 3],
            ['catalogo' => 'SOL_RPC',          'orden' => 7,  'area' => 'planeacion',         'dias' => 3],
            ['catalogo' => 'RAD_FINAL',        'orden' => 8,  'area' => 'juridica',           'dias' => 2],
            ['catalogo' => 'ARL_INICIO',       'orden' => 9,  'area' => 'unidad_solicitante', 'dias' => 3],
        ];

        foreach ($pasos as $paso) {
            if (!isset($pasoIds[$paso['catalogo']])) continue;

            DB::table('flujo_pasos')->insert([
                'flujo_version_id'         => $versionId,
                'catalogo_paso_id'         => $pasoIds[$paso['catalogo']],
                'orden'                    => $paso['orden'],
                'es_obligatorio'           => true,
                'es_paralelo'              => ($paso['catalogo'] === 'DESC_DOCS'),
                'dias_estimados'           => $paso['dias'],
                'area_responsable_default' => $paso['area'],
                'activo'                   => true,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // 2) CREAR WORKFLOW LEGACY CON ETAPAS DETALLADAS
        // ═══════════════════════════════════════════════════════════════
        $workflowId = DB::table('workflows')->insertGetId([
            'codigo' => 'CD_PJ',
            'nombre' => 'Contratación Directa - Persona Jurídica',
            'activo' => true,
            'requiere_viabilidad_economica_inicial' => false,
            'requiere_estudios_previos_completos'   => true,
            'observaciones' => 'Flujo CD-PJ oficial. 10 etapas (0-9). '
                . 'Basado en CD-PN pero adaptado para Persona Jurídica: '
                . 'sin docs personales PN (SIGEP HV, diplomas, seguridad social individual), '
                . 'con docs PJ (existencia y representación legal, órgano societario, cert. seg. social 6m).',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $etapas = $this->getEtapas();
        $etapasIds = [];

        foreach ($etapas as $etapaData) {
            $etapaId = DB::table('etapas')->insertGetId([
                'workflow_id'              => $workflowId,
                'orden'                    => $etapaData['orden'],
                'nombre'                   => $etapaData['nombre'],
                'descripcion'              => $etapaData['descripcion'] ?? null,
                'area_role'                => $etapaData['area_role'],
                'responsable_unidad'       => $etapaData['responsable_unidad'] ?? null,
                'responsable_secretaria'   => $etapaData['responsable_secretaria'] ?? null,
                'es_paralelo'              => $etapaData['es_paralelo'] ?? false,
                'notas'                    => $etapaData['notas'] ?? null,
                'activa'                   => true,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);

            $etapasIds[$etapaData['orden']] = $etapaId;

            if (!empty($etapaData['items'])) {
                foreach ($etapaData['items'] as $orden => $itemData) {
                    DB::table('etapa_items')->insert([
                        'etapa_id'                => $etapaId,
                        'orden'                   => $orden + 1,
                        'label'                   => $itemData['label'],
                        'requerido'               => $itemData['requerido'] ?? true,
                        'responsable_unidad'      => $itemData['responsable_unidad'] ?? null,
                        'responsable_secretaria'  => $itemData['responsable_secretaria'] ?? null,
                        'notas'                   => $itemData['notas'] ?? null,
                        'tipo_documento'          => $itemData['tipo_documento'] ?? null,
                        'created_at'              => $now,
                        'updated_at'              => $now,
                    ]);
                }
            }
        }

        // Conectar etapas con next_etapa_id (cadena secuencial)
        $ordenesOrdenados = array_keys($etapasIds);
        sort($ordenesOrdenados);

        for ($i = 0; $i < count($ordenesOrdenados) - 1; $i++) {
            $ordenActual    = $ordenesOrdenados[$i];
            $ordenSiguiente = $ordenesOrdenados[$i + 1];

            DB::table('etapas')
                ->where('id', $etapasIds[$ordenActual])
                ->update(['next_etapa_id' => $etapasIds[$ordenSiguiente], 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Eliminar flujo
        $flujo = DB::table('flujos')->where('codigo', 'CD_PJ')->first();
        if ($flujo) {
            if ($flujo->version_activa_id) {
                DB::table('flujo_pasos')->where('flujo_version_id', $flujo->version_activa_id)->delete();
                DB::table('flujo_versiones')->where('id', $flujo->version_activa_id)->delete();
            }
            DB::table('flujos')->where('id', $flujo->id)->delete();
        }

        // Eliminar workflow
        $workflow = DB::table('workflows')->where('codigo', 'CD_PJ')->first();
        if ($workflow) {
            $etapaIds = DB::table('etapas')->where('workflow_id', $workflow->id)->pluck('id');
            if ($etapaIds->isNotEmpty()) {
                DB::table('etapa_items')->whereIn('etapa_id', $etapaIds)->delete();
                DB::table('etapas')->whereIn('id', $etapaIds)->delete();
            }
            DB::table('workflows')->where('id', $workflow->id)->delete();
        }
    }

    /**
     * Definición de las 10 etapas para CD-PJ.
     * Igual que CD-PN salvo:
     *   ─ Etapa 1: Sin No Planta, sin Paz y Salvos individuales
     *   ─ Etapa 2: Sin docs personales PN + con docs PJ específicos
     *   ─ Etapa 4: Consolidación ajustada
     */
    private function getEtapas(): array
    {
        return [

            // ═══════════════════════════════════════════════════════════
            // ETAPA 0 – Definición de la Necesidad (IGUAL a CD-PN)
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 0,
                'nombre'      => '0: Definición de la Necesidad',
                'descripcion' => 'La Unidad solicitante identifica la necesidad contractual. Se elaboran los Estudios Previos definiendo objeto, valor estimado y plazo. El Jefe de Unidad envía el estudio previo a la Unidad de Descentralización – Secretaría de Planeación.',
                'area_role'   => 'unidad_solicitante',
                'responsable_unidad'      => 'Unidad Solicitante (Jefe de Unidad)',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => false,
                'notas'       => 'El estudio previo es el insumo principal. Debe definir: objeto contractual, valor estimado, plazo de ejecución y perfil requerido del contratista persona jurídica.',
                'items' => [
                    [
                        'label'                   => 'Estudios Previos elaborados y cargados en el sistema',
                        'tipo_documento'          => 'documento',
                        'responsable_unidad'      => 'Jefe de Unidad Solicitante',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'notas'                   => 'Documento obligatorio que se carga al crear la solicitud. Define objeto, valor y plazo.',
                        'requerido'               => true,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 1 – Solicitud de Documentos Iniciales
            // DIFERENCIA: Sin Certificado No Planta, sin Paz y Salvos
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 1,
                'nombre'      => '1: Solicitud de Documentos Iniciales',
                'descripcion' => 'La Unidad de Descentralización (Sec. Planeación) coordina la solicitud de documentos a múltiples dependencias. El CDP requiere primero la Compatibilidad del Gasto. No se requiere Certificado No Planta ni Paz y Salvos individuales para persona jurídica.',
                'area_role'   => 'planeacion',
                'responsable_unidad'      => 'Unidad de Descentralización',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => true,
                'notas'       => '⚠️ Para persona jurídica NO se solicita Certificado No Planta ni Paz y Salvos individuales.',
                'items' => [
                    [
                        'label'                   => 'Plan Anual de Adquisiciones (PAA) – Unidad de Compras',
                        'tipo_documento'          => 'certificado',
                        'responsable_unidad'      => 'Unidad de Compras y Suministros',
                        'responsable_secretaria'  => 'Secretaría General',
                        'notas'                   => 'La Unidad de Compras realiza el cargue en SECOP II.',
                        'requerido'               => true,
                    ],
                    [
                        'label'                   => 'Compatibilidad del Gasto – Inversiones Públicas',
                        'tipo_documento'          => 'certificado',
                        'responsable_unidad'      => 'Unidad de Regalías e Inversiones Públicas',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'notas'                   => 'PRERREQUISITO para el CDP. Debe obtenerse antes de solicitar el Certificado de Disponibilidad Presupuestal.',
                        'requerido'               => true,
                    ],
                    [
                        'label'                   => 'CDP – Certificado de Disponibilidad Presupuestal',
                        'tipo_documento'          => 'certificado',
                        'responsable_unidad'      => 'Unidad de Presupuesto',
                        'responsable_secretaria'  => 'Secretaría de Hacienda',
                        'notas'                   => '⚠️ Solo puede solicitarse DESPUÉS de tener la Compatibilidad del Gasto.',
                        'requerido'               => true,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 2 – Validación del Contratista (PERSONA JURÍDICA)
            // DIFERENCIA: Sin docs personales PN. Con docs PJ.
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 2,
                'nombre'      => '2: Validación del Contratista',
                'descripcion' => 'El Representante Legal de la persona jurídica entrega documentos legales. La Secretaría Jurídica valida la documentación. El abogado de la Unidad verifica el checklist completo. Es responsabilidad del contratista enviar los documentos correctos.',
                'area_role'   => 'unidad_solicitante',
                'responsable_unidad'      => 'Unidad Solicitante (Abogado adscrito)',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => true,
                'notas'       => 'PERSONA JURÍDICA: Se requieren documentos del Representante Legal y de la empresa. No se requieren: SIGEP HV, diplomas, experiencia laboral, seguridad social individual, examen médico, tarjeta profesional, sanciones profesionales, situación militar.',
                'items' => [
                    [
                        'label' => 'Aceptación de la oferta para la celebración del contrato suscrita por el Representante Legal',
                        'tipo_documento' => 'formato', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'Formato Almera. La firma es del Representante Legal.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Declaración de bienes y rentas del SIGEP II realizado por el Representante Legal (descargado de la plataforma)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'Descargado directamente de la plataforma SIGEP II.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Declaración de bienes y renta y conflicto de intereses del Representante Legal (Ley 2013)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'https://www1.funcionpublica.gov.co/web/sigep2/ley-2013', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de existencia y representación legal expedido por autoridad competente (inferior a 30 días)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista PJ',
                        'responsable_secretaria' => null, 'notas' => 'Expedido por Cámara de Comercio u organismo competente. Inferior a 30 días calendario.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Autorización del órgano societario cuando el Representante Legal posea limitaciones estatutarias',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista PJ',
                        'responsable_secretaria' => null, 'notas' => 'Solo cuando el Representante Legal del contratista posea limitaciones estatutarias para la celebración del contrato.', 'requerido' => false,
                    ],
                    [
                        'label' => 'Copia legible de la cédula de ciudadanía del Representante Legal',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => null, 'requerido' => true,
                    ],
                    [
                        'label' => 'Copia legible del RUT (actualizado en datos y actividad económica)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista PJ',
                        'responsable_secretaria' => null, 'notas' => 'Debe estar actualizado.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de cuenta bancaria actualizada (inferior a 30 días)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista PJ',
                        'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días calendario.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de ausencia de antecedentes disciplinarios (inferior a 30 días)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de ausencia de antecedentes fiscales (inferior a 30 días)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de ausencia de antecedentes judiciales (inferior a 30 días)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de ausencia de medidas correctivas',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => null, 'requerido' => true,
                    ],
                    [
                        'label' => 'Antecedentes de delitos sexuales del Representante Legal (inferior a 30 días)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                    ],
                    [
                        'label' => 'REDAM – Registro de Deudores Alimentarios Morosos del Representante Legal',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null, 'notas' => 'Vigencia según lo señalado en el certificado.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificación del Representante Legal o revisor fiscal sobre cumplimiento de obligaciones con el sistema integral de seguridad social (6 meses anteriores)',
                        'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Representante Legal / Revisor Fiscal',
                        'responsable_secretaria' => null, 'notas' => 'Según corresponda atendiendo su naturaleza jurídica, haciendo constar el cumplimiento durante los seis (6) meses anteriores a la celebración del contrato.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de idoneidad y experiencia del contratista emitido por el Secretario interesado',
                        'tipo_documento' => 'certificado', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => 'Emitido y firmado por el Secretario interesado.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Abogado verifica checklist completo de documentos del contratista PJ',
                        'tipo_documento' => 'checklist', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => 'Verificación interna: documentos completos, vigentes y correctos antes de elaborar documentos contractuales.', 'requerido' => true,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 3 – Elaboración de Documentos Contractuales (IGUAL)
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 3,
                'nombre'      => '3: Elaboración de Documentos Contractuales',
                'descripcion' => 'El abogado adscrito a la unidad solicitante proyecta los documentos oficiales del proceso. Estos llevan firma del ordenador del gasto y el supervisor, excepto el Análisis del Sector (solo supervisor) y la Aceptación de la Oferta (Representante Legal).',
                'area_role'   => 'unidad_solicitante',
                'responsable_unidad'      => 'Unidad Solicitante (Abogado adscrito)',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => false,
                'notas'       => 'Firmas: Ordenador del Gasto + Supervisor en la mayoría. Solo Supervisor en Análisis del Sector. Solo Representante Legal en Aceptación de Oferta.',
                'items' => [
                    [
                        'label' => 'Invitación a Presentar Oferta (firma Ordenador del Gasto + Supervisor)',
                        'tipo_documento' => 'formato', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => 'Formato Almera. Firma del Secretario interesado y del supervisor.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Solicitud de Contratación y Designación de Supervisión (firma Ordenador + Supervisor)',
                        'tipo_documento' => 'formato', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => null, 'requerido' => true,
                    ],
                    [
                        'label' => 'Certificado de Idoneidad y Experiencia del Contratista (firma Secretario interesado)',
                        'tipo_documento' => 'certificado', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => 'Emitido y firmado por el Secretario interesado.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Estudios Previos con Anexos – versión definitiva (firma Ordenador + Supervisor)',
                        'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => null, 'requerido' => true,
                    ],
                    [
                        'label' => 'Análisis del Sector con estudio de mercado (solo firma Supervisor)',
                        'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => 'SOLO lleva la firma del supervisor.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Aceptación de la Oferta firmada por el Representante Legal (Formato Almera)',
                        'tipo_documento' => 'formato', 'responsable_unidad' => 'Representante Legal',
                        'responsable_secretaria' => null,
                        'notas' => 'La firma es exclusivamente del REPRESENTANTE LEGAL.', 'requerido' => true,
                    ],
                    [
                        'label' => 'Ficha BPIN (si aplica para proyectos de inversión)',
                        'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => 'Aplica para proyectos de inversión.', 'requerido' => false,
                    ],
                    [
                        'label' => 'Excepción regla fiscal (si aplica)',
                        'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                        'responsable_secretaria' => 'Secretaría de Planeación',
                        'notas' => 'En los procesos en que el plazo de ejecución supere lo establecido en la regla fiscal, se deberá emitir justificación por parte del ordenador del gasto.', 'requerido' => false,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 4 – Consolidación del Expediente (ADAPTADA PJ)
            // DIFERENCIA: Sin docs personales PN, con docs PJ
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 4,
                'nombre'      => '4: Consolidación del Expediente Precontractual',
                'descripcion' => 'El abogado agrupa en una carpeta con el nombre del contratista TODOS los documentos precontractuales de la lista de chequeo para Persona Jurídica. Verifica fechas ajustadas, firmas completas y requisitos exigidos antes de proceder a la radicación en Jurídica.',
                'area_role'   => 'unidad_solicitante',
                'responsable_unidad'      => 'Unidad Solicitante (Abogado adscrito)',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => false,
                'notas'       => 'La carpeta lleva el nombre del contratista PJ. Todos los documentos deben tener fechas vigentes y firmas completas.',
                'items' => [
                    ['label' => 'PAA – Certificado de Plan Anual de Adquisiciones',                          'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Constancia de Compatibilidad del Gasto',                                    'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Ficha BPIN (si aplica)',                                                    'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Si aplica',    'requerido' => false],
                    ['label' => 'Análisis del Sector (incluido estudio del mercado)',                        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Invitación a Presentar Oferta (Formato Almera)',                            'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Aceptación de la Oferta suscrita por el Representante Legal',               'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Declaración de bienes y rentas SIGEP II del Representante Legal',           'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Declaración bienes, renta y conflicto de intereses del Representante Legal','tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Certificado de existencia y representación legal (< 30 días)',              'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                    ['label' => 'Autorización del órgano societario (si aplica)',                            'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Si aplica',    'requerido' => false],
                    ['label' => 'Copia de cédula del Representante Legal',                                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Copia del RUT actualizado',                                                 'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Certificado de cuenta bancaria actualizada',                                'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                    ['label' => 'Antecedentes disciplinarios',                                               'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                    ['label' => 'Antecedentes fiscales',                                                     'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                    ['label' => 'Antecedentes judiciales',                                                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                    ['label' => 'Ausencia de medidas correctivas',                                           'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Antecedentes de delitos sexuales del Representante Legal',                  'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                    ['label' => 'REDAM del Representante Legal',                                             'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Certificación seguridad social integral (6 meses anteriores)',              'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Del Representante Legal o revisor fiscal', 'requerido' => true],
                    ['label' => 'Certificado de Idoneidad y Experiencia',                                    'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'CDP – Certificado de Disponibilidad Presupuestal',                          'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Solicitud de Contratación y Designación de Supervisión',                    'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Estudios Previos con Anexos (versión definitiva)',                          'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                    ['label' => 'Excepción regla fiscal (si aplica)',                                        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Si aplica',    'requerido' => false],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 5 – Radicación en Jurídica + Ajustado a Derecho (IGUAL)
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 5,
                'nombre'      => '5: Radicación, Revisión Jurídica y Ajustado a Derecho',
                'descripcion' => 'Se diligencia el formulario en el SharePoint "Solicitud de Contratación" de la Secretaría Jurídica. Se genera número de proceso y se cargan los documentos. El abogado enlace revisa la lista de chequeo: si hay errores devuelve; si cumple emite el Ajustado a Derecho.',
                'area_role'   => 'juridica',
                'responsable_unidad'      => 'Abogado Enlace / Oficina de Radicación',
                'responsable_secretaria'  => 'Secretaría Jurídica',
                'es_paralelo' => false,
                'notas'       => 'El SharePoint asigna automáticamente el número de proceso. Si hay observaciones, el proceso se devuelve a la unidad solicitante.',
                'items' => [
                    ['label' => 'Solicitud de contratación diligenciada en SharePoint "Solicitud de Contratación"', 'tipo_documento' => 'solicitud', 'responsable_unidad' => 'Abogado de Unidad Solicitante', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'Datos: correo solicitante, secretaría, modalidad, NIT contratista, nombre contratista, tipo contrato, objeto contractual, valor.', 'requerido' => true],
                    ['label' => 'Número de proceso asignado', 'tipo_documento' => 'solicitud', 'responsable_unidad' => 'SharePoint automático', 'responsable_secretaria' => 'Secretaría Jurídica', 'notas' => 'El SharePoint asigna el número y crea la carpeta para cargar los documentos.', 'requerido' => true],
                    ['label' => 'Documentos precontractuales cargados en carpeta SharePoint', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'Se cargan en la carpeta creada automáticamente.', 'requerido' => true],
                    ['label' => 'Revisión de la lista de chequeo por abogado enlace de Jurídica', 'tipo_documento' => 'checklist', 'responsable_unidad' => 'Abogado Enlace', 'responsable_secretaria' => 'Secretaría Jurídica', 'notas' => 'Si hay observaciones se devuelve a la unidad solicitante. Si cumple, se procede a emitir el Ajustado a Derecho.', 'requerido' => true],
                    ['label' => 'Ajustado a Derecho expedido y firmado por abogado enlace', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado Enlace', 'responsable_secretaria' => 'Secretaría Jurídica', 'notas' => 'Firmado por el abogado enlace de la Secretaría Jurídica.', 'requerido' => true],
                    ['label' => 'Contrato físico firmado por Secretario Privado (Ordenador del Gasto)', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Secretaría Privada', 'responsable_secretaria' => 'Secretaría Privada', 'notas' => null, 'requerido' => true],
                    ['label' => 'Contrato físico firmado por el Representante Legal del contratista', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Representante Legal', 'responsable_secretaria' => null, 'notas' => null, 'requerido' => true],
                    ['label' => 'Contrato físico firmado por el abogado enlace de Jurídica', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado Enlace', 'responsable_secretaria' => 'Secretaría Jurídica', 'notas' => null, 'requerido' => true],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 6 – Publicación y Firma en SECOP II (IGUAL)
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 6,
                'nombre'      => '6: Publicación y Firma en SECOP II',
                'descripcion' => 'El apoyo de estructuración carga el contrato en SECOP II. Flujos de aprobación: creación aprobada por abogado enlace; contrato firmado primero por el Representante Legal y luego por el Secretario Privado. Se descarga el contrato electrónico.',
                'area_role'   => 'secop',
                'responsable_unidad'      => 'Apoyo de Estructuración SECOP',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => false,
                'notas'       => 'Secuencial: 1) Estructurar en SECOP II → 2) Abogado enlace aprueba creación → 3) Representante Legal firma → 4) Secretario Privado firma → 5) Descargar contrato electrónico.',
                'items' => [
                    ['label' => 'Contrato estructurado y documentos cargados en SECOP II', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Apoyo de Estructuración', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'Se relaciona el proceso con el Plan Anual de Adquisiciones.', 'requerido' => true],
                    ['label' => 'Creación del proceso aprobada por abogado enlace en SECOP II', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado Enlace', 'responsable_secretaria' => 'Secretaría Jurídica', 'notas' => 'Flujo de aprobación dentro de la plataforma SECOP II.', 'requerido' => true],
                    ['label' => 'Contrato firmado por el Representante Legal en SECOP II (PRIMERO)', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Representante Legal', 'responsable_secretaria' => null, 'notas' => 'El Representante Legal firma PRIMERO en la plataforma electrónica.', 'requerido' => true],
                    ['label' => 'Contrato firmado por Secretario Privado en SECOP II (DESPUÉS)', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Secretario Privado', 'responsable_secretaria' => 'Secretaría Privada', 'notas' => 'El Secretario Privado firma DESPUÉS del Representante Legal.', 'requerido' => true],
                    ['label' => 'Contrato electrónico descargado de SECOP II', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Apoyo de Estructuración', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'Se descarga una vez firmado por todas las partes.', 'requerido' => true],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 7 – Solicitud de RPC (IGUAL)
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 7,
                'nombre'      => '7: Solicitud y Expedición del RPC',
                'descripcion' => 'El abogado imprime el contrato electrónico, adjunta ajustado a derecho y contrato original, y los allega a la Unidad de Descentralización. La solicitud de RPC la firma el Secretario de Planeación y se radica en Hacienda.',
                'area_role'   => 'planeacion',
                'responsable_unidad'      => 'Unidad de Descentralización',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => false,
                'notas'       => 'Mientras se espera la expedición del RPC se puede ir organizando el expediente físico completo.',
                'items' => [
                    ['label' => 'Contrato electrónico impreso', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => null, 'requerido' => true],
                    ['label' => 'Ajustado a Derecho adjunto (original firmado)', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => null, 'requerido' => true],
                    ['label' => 'Contrato físico original firmado adjunto', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => null, 'requerido' => true],
                    ['label' => 'Solicitud de RPC firmada por el Secretario de Planeación', 'tipo_documento' => 'solicitud', 'responsable_unidad' => 'Unidad de Descentralización', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'La firma es del Secretario de Planeación.', 'requerido' => true],
                    ['label' => 'Solicitud radicada en la Secretaría de Hacienda', 'tipo_documento' => 'solicitud', 'responsable_unidad' => 'Unidad de Descentralización', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => null, 'requerido' => true],
                    ['label' => 'RPC expedido en físico por Unidad de Presupuesto – Secretaría de Hacienda', 'tipo_documento' => 'certificado', 'responsable_unidad' => 'Unidad de Presupuesto', 'responsable_secretaria' => 'Secretaría de Hacienda', 'notas' => 'Registro Presupuestal de Compromiso expedido por Hacienda.', 'requerido' => true],
                    ['label' => 'Expediente contractual físico organizado con todos los documentos', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Abogado de Unidad Solicitante', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'Incluye todos los documentos de la lista de chequeo en orden.', 'requerido' => true],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 8 – Radicación Final (IGUAL)
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 8,
                'nombre'      => '8: Radicación Final del Expediente y Número de Contrato',
                'descripcion' => 'Con el RPC listo y el expediente organizado, se radica el expediente completo en la Oficina de Radicación de la Secretaría Jurídica. Allí se asigna el número de contrato.',
                'area_role'   => 'juridica',
                'responsable_unidad'      => 'Oficina de Radicación',
                'responsable_secretaria'  => 'Secretaría Jurídica',
                'es_paralelo' => false,
                'notas'       => 'El número de contrato asignado en esta etapa es INDISPENSABLE para solicitar la ARL en la etapa siguiente.',
                'items' => [
                    ['label' => 'Expediente físico completo radicado en la Oficina de Radicación – Jurídica', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Oficina de Radicación', 'responsable_secretaria' => 'Secretaría Jurídica', 'notas' => 'El expediente debe incluir el RPC y todos los documentos de la lista de chequeo.', 'requerido' => true],
                    ['label' => 'Número de contrato asignado por la Secretaría Jurídica', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Oficina de Radicación', 'responsable_secretaria' => 'Secretaría Jurídica', 'notas' => 'Este número es necesario para solicitar la ARL y elaborar el Acta de Inicio.', 'requerido' => true],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // ETAPA 9 – ARL, Acta de Inicio e Inicio en SECOP II (IGUAL)
            // ═══════════════════════════════════════════════════════════
            [
                'orden'       => 9,
                'nombre'      => '9: ARL, Acta de Inicio e Inicio de Ejecución en SECOP II',
                'descripcion' => 'Con el número de contrato se realiza la solicitud de ARL. Con la ARL y los datos del proceso se elabora el Acta de Inicio. Finalmente se registra el inicio de ejecución en la plataforma SECOP II.',
                'area_role'   => 'unidad_solicitante',
                'responsable_unidad'      => 'Unidad Solicitante (Supervisor)',
                'responsable_secretaria'  => 'Secretaría de Planeación',
                'es_paralelo' => false,
                'notas'       => 'Paso final del flujo precontractual. A partir del inicio de ejecución en SECOP II comienza la fase de ejecución contractual.',
                'items' => [
                    ['label' => 'ARL solicitada con el número de contrato', 'tipo_documento' => 'solicitud', 'responsable_unidad' => 'Unidad Solicitante', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'La solicitud se hace con el número de contrato asignado por Jurídica.', 'requerido' => true],
                    ['label' => 'ARL expedida y confirmada', 'tipo_documento' => 'certificado', 'responsable_unidad' => 'ARL (Administradora de Riesgos Laborales)', 'responsable_secretaria' => null, 'notas' => null, 'requerido' => true],
                    ['label' => 'Acta de Inicio elaborada con todos los datos del proceso', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Supervisor designado', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => null, 'requerido' => true],
                    ['label' => 'Acta de Inicio firmada por las partes (Supervisor + Representante Legal)', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Supervisor + Representante Legal', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => 'La firman el supervisor designado y el Representante Legal del contratista.', 'requerido' => true],
                    ['label' => 'Inicio de ejecución registrado en SECOP II', 'tipo_documento' => 'documento', 'responsable_unidad' => 'Apoyo de Estructuración SECOP', 'responsable_secretaria' => 'Secretaría de Planeación', 'notas' => '✅ Paso final: inicio oficial del contrato en la plataforma electrónica.', 'requerido' => true],
                ],
            ],
        ];
    }
};
