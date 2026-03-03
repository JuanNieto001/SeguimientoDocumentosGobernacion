<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ╔══════════════════════════════════════════════════════════════════════════════╗
 * ║  SEEDER – Motor de Flujos Configurable por Secretaría                     ║
 * ╠══════════════════════════════════════════════════════════════════════════════╣
 * ║  Pobla el catálogo de pasos reutilizables y crea flujos de ejemplo        ║
 * ║  para dos secretarías diferentes, demostrando que cada una puede tener    ║
 * ║  pasos diferentes, orden diferente y responsables diferentes.             ║
 * ╚══════════════════════════════════════════════════════════════════════════════╝
 */
class MotorFlujosSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ═══════════════════════════════════════════════════════════════
        // 0) LIMPIAR TABLAS DEL MOTOR (permite re-ejecutar sin error)
        // ═══════════════════════════════════════════════════════════════
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('flujo_instancia_docs')->truncate();
        DB::table('flujo_instancia_pasos')->truncate();
        DB::table('flujo_instancias')->truncate();
        DB::table('flujo_paso_responsables')->truncate();
        DB::table('flujo_paso_documentos')->truncate();
        DB::table('flujo_paso_condiciones')->truncate();
        DB::table('flujo_pasos')->truncate();
        DB::table('flujo_versiones')->truncate();
        DB::table('flujos')->truncate();
        DB::table('catalogo_pasos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info('🧹 Tablas del motor de flujos limpiadas.');

        // ═══════════════════════════════════════════════════════════════
        // 1) CATÁLOGO GENERAL DE PASOS REUTILIZABLES
        // ═══════════════════════════════════════════════════════════════
        $catalogoPasos = [
            ['codigo' => 'DEF_NECESIDAD',      'nombre' => 'Definición de la Necesidad',             'descripcion' => 'La Unidad identifica la necesidad y elabora estudios previos.',                        'icono' => 'FileText',    'color' => '#3B82F6', 'tipo' => 'secuencial'],
            ['codigo' => 'SOL_CDP',            'nombre' => 'Solicitud de CDP',                       'descripcion' => 'Se solicita el Certificado de Disponibilidad Presupuestal a Hacienda.',               'icono' => 'DollarSign',  'color' => '#10B981', 'tipo' => 'secuencial'],
            ['codigo' => 'DESC_DOCS',          'nombre' => 'Descentralización - Solicitud Documentos','descripcion' => 'Descentralización coordina solicitud de documentos a las áreas.',                   'icono' => 'Send',        'color' => '#8B5CF6', 'tipo' => 'paralelo'],
            ['codigo' => 'VAL_CONTRATISTA',    'nombre' => 'Validación del Contratista',              'descripcion' => 'Se verifican antecedentes, idoneidad y documentos del contratista.',                 'icono' => 'UserCheck',   'color' => '#F59E0B', 'tipo' => 'secuencial'],
            ['codigo' => 'ELAB_DOCS',          'nombre' => 'Elaboración Documentos Contractuales',    'descripcion' => 'Se elaboran la minuta, estudios previos definitivos y anexos.',                      'icono' => 'FileEdit',    'color' => '#EF4444', 'tipo' => 'secuencial'],
            ['codigo' => 'CONSOL_EXP',         'nombre' => 'Consolidación Expediente Precontractual', 'descripcion' => 'Se reúne toda la documentación en un expediente para revisión.',                     'icono' => 'FolderOpen',  'color' => '#06B6D4', 'tipo' => 'secuencial'],
            ['codigo' => 'RAD_JURIDICA',       'nombre' => 'Radicación en Secretaría Jurídica',       'descripcion' => 'El expediente se radica en Jurídica para verificación de ajustado a derecho.',       'icono' => 'Scale',       'color' => '#7C3AED', 'tipo' => 'secuencial'],
            ['codigo' => 'REV_JURIDICA',       'nombre' => 'Revisión Jurídica Adicional',             'descripcion' => 'Revisión jurídica especial para montos altos o contratos complejos.',                'icono' => 'ShieldCheck', 'color' => '#DC2626', 'tipo' => 'condicional'],
            ['codigo' => 'PUB_SECOP',          'nombre' => 'Publicación y Firma SECOP II',            'descripcion' => 'Se publica en SECOP II y se gestiona la firma electrónica.',                         'icono' => 'Globe',       'color' => '#059669', 'tipo' => 'secuencial'],
            ['codigo' => 'SOL_RPC',            'nombre' => 'Solicitud de RPC',                        'descripcion' => 'Se solicita el Registro Presupuestal del Compromiso a Hacienda.',                    'icono' => 'Receipt',     'color' => '#D97706', 'tipo' => 'secuencial'],
            ['codigo' => 'RAD_FINAL',          'nombre' => 'Radicación Final y Número de Contrato',   'descripcion' => 'Jurídica asigna número de contrato y radica definitivamente.',                       'icono' => 'Award',       'color' => '#7C3AED', 'tipo' => 'secuencial'],
            ['codigo' => 'ARL_INICIO',         'nombre' => 'ARL, Acta de Inicio y SECOP II',          'descripcion' => 'Se gestiona ARL, acta de inicio y activación en SECOP II.',                          'icono' => 'PlayCircle',  'color' => '#2563EB', 'tipo' => 'secuencial'],
            ['codigo' => 'COM_EVALUACION',     'nombre' => 'Comité de Evaluación',                    'descripcion' => 'El comité evalúa las propuestas recibidas (usado en licitaciones).',                  'icono' => 'Users',       'color' => '#4F46E5', 'tipo' => 'secuencial'],
            ['codigo' => 'PUB_AVISO',          'nombre' => 'Publicación Aviso de Convocatoria',       'descripcion' => 'Se publica el aviso de convocatoria en los medios requeridos.',                       'icono' => 'Megaphone',   'color' => '#CA8A04', 'tipo' => 'secuencial'],
            ['codigo' => 'RECEP_PROPUESTAS',   'nombre' => 'Recepción de Propuestas',                 'descripcion' => 'Se reciben y registran las propuestas de los oferentes.',                             'icono' => 'Inbox',       'color' => '#0891B2', 'tipo' => 'secuencial'],
            ['codigo' => 'ADJUDICACION',       'nombre' => 'Adjudicación',                            'descripcion' => 'Se adjudica el contrato al oferente seleccionado.',                                   'icono' => 'CheckCircle', 'color' => '#16A34A', 'tipo' => 'secuencial'],
            ['codigo' => 'VIAB_ECONOMICA',     'nombre' => 'Viabilidad Económica',                    'descripcion' => 'Análisis de viabilidad económica del proyecto.',                                      'icono' => 'TrendingUp', 'color' => '#EA580C', 'tipo' => 'secuencial'],
            ['codigo' => 'APROB_PLANEACION',   'nombre' => 'Aprobación Planeación',                   'descripcion' => 'La Secretaría de Planeación verifica PAA y aprueba.',                                 'icono' => 'ClipboardCheck','color' => '#9333EA','tipo' => 'secuencial'],
        ];

        foreach ($catalogoPasos as $paso) {
            DB::table('catalogo_pasos')->updateOrInsert(
                ['codigo' => $paso['codigo']],
                array_merge($paso, ['activo' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('✅ Catálogo de pasos creado (' . count($catalogoPasos) . ' pasos).');

        // Obtener IDs del catálogo
        $pasoIds = DB::table('catalogo_pasos')->pluck('id', 'codigo');

        // Obtener IDs de secretarías existentes
        $secPlaneacion = DB::table('secretarias')->where('nombre', 'like', '%Planeación%')->value('id');
        $secGobierno   = DB::table('secretarias')->where('nombre', 'like', '%Gobierno%')->value('id');

        if (!$secPlaneacion || !$secGobierno) {
            $this->command->warn('⚠️  No se encontraron las Secretarías de Planeación y Gobierno. Creando flujos de ejemplo con IDs genéricos.');
            $secPlaneacion = DB::table('secretarias')->first()?->id ?? 1;
            $secGobierno   = DB::table('secretarias')->skip(1)->first()?->id ?? 2;
        }

        // ═══════════════════════════════════════════════════════════════
        // 2) FLUJO CD-PN PARA SECRETARÍA DE PLANEACIÓN (10 pasos)
        // ═══════════════════════════════════════════════════════════════
        $flujoPlaneacionId = DB::table('flujos')->insertGetId([
            'codigo'            => 'CD_PN_PLANEACION',
            'nombre'            => 'Contratación Directa PN - Sec. Planeación',
            'descripcion'       => 'Flujo CD-PN con 10 etapas oficial de la Gobernación de Caldas para la Secretaría de Planeación.',
            'tipo_contratacion' => 'cd_pn',
            'secretaria_id'     => $secPlaneacion,
            'activo'            => true,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $versionPlaneacionId = DB::table('flujo_versiones')->insertGetId([
            'flujo_id'        => $flujoPlaneacionId,
            'numero_version'  => 1,
            'motivo_cambio'   => 'Versión inicial del flujo CD-PN para Planeación.',
            'estado'          => 'activa',
            'publicada_at'    => $now,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        DB::table('flujos')->where('id', $flujoPlaneacionId)->update(['version_activa_id' => $versionPlaneacionId]);

        // Pasos del flujo de Planeación (orden del proceso real CD-PN)
        $pasosPlaneacion = [
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

        $flujoPasoIdsPlaneacion = [];
        foreach ($pasosPlaneacion as $paso) {
            $id = DB::table('flujo_pasos')->insertGetId([
                'flujo_version_id'         => $versionPlaneacionId,
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
            $flujoPasoIdsPlaneacion[$paso['catalogo']] = $id;
        }

        // ─── Condiciones para el flujo de Planeación ───
        // Si monto > 50M → requiere revisión jurídica adicional (se agrega paso)
        DB::table('flujo_paso_condiciones')->insert([
            'flujo_paso_id' => $flujoPasoIdsPlaneacion['RAD_JURIDICA'],
            'campo'         => 'monto_estimado',
            'operador'      => '>',
            'valor'         => '50000000',
            'accion'        => 'notificar',
            'descripcion'   => 'Si el monto estimado supera $50.000.000, se notifica al Secretario Jurídico para revisión especial.',
            'prioridad'     => 1,
            'activo'        => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // ─── Documentos requeridos por paso (Planeación) ───
        $documentosPorPaso = [
            'DEF_NECESIDAD' => [
                ['nombre' => 'Estudios Previos',              'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Análisis del Sector',           'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 2],
                ['nombre' => 'Matriz de Riesgos',             'tipo' => 'xlsx', 'obligatorio' => true, 'orden' => 3],
            ],
            'VAL_CONTRATISTA' => [
                ['nombre' => 'Hoja de Vida SIGEP',            'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Certificados de Experiencia',   'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 2],
                ['nombre' => 'Antecedentes Disciplinarios',   'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 3],
                ['nombre' => 'Antecedentes Fiscales',         'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 4],
                ['nombre' => 'Antecedentes Judiciales',       'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 5],
                ['nombre' => 'RUT Actualizado',               'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 6],
            ],
            'ELAB_DOCS' => [
                ['nombre' => 'Minuta del Contrato',           'tipo' => 'docx', 'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Certificación PAA',             'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 2],
                ['nombre' => 'CDP',                           'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 3],
            ],
            'CONSOL_EXP' => [
                ['nombre' => 'Expediente Consolidado',        'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Check-list de Documentos',      'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 2],
            ],
            'RAD_JURIDICA' => [
                ['nombre' => 'Concepto Ajustado a Derecho',   'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
            ],
            'PUB_SECOP' => [
                ['nombre' => 'Comprobante Publicación SECOP', 'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Enlace SECOP II',               'tipo' => 'pdf',   'obligatorio' => false, 'orden' => 2],
            ],
            'SOL_RPC' => [
                ['nombre' => 'Solicitud de RPC',              'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'RPC Expedido',                  'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 2],
            ],
            'RAD_FINAL' => [
                ['nombre' => 'Contrato con Número',           'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
            ],
            'ARL_INICIO' => [
                ['nombre' => 'Afiliación ARL',                'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Acta de Inicio',                'tipo' => 'pdf',  'obligatorio' => true, 'orden' => 2],
                ['nombre' => 'Inicio en SECOP II',            'tipo' => 'pdf',  'obligatorio' => false, 'orden' => 3],
            ],
        ];

        foreach ($documentosPorPaso as $codigoPaso => $docs) {
            if (!isset($flujoPasoIdsPlaneacion[$codigoPaso])) continue;
            foreach ($docs as $doc) {
                DB::table('flujo_paso_documentos')->insert([
                    'flujo_paso_id'  => $flujoPasoIdsPlaneacion[$codigoPaso],
                    'nombre'         => $doc['nombre'],
                    'tipo_archivo'   => $doc['tipo'],
                    'es_obligatorio' => $doc['obligatorio'],
                    'max_archivos'   => 1,
                    'max_tamano_mb'  => 10,
                    'orden'          => $doc['orden'],
                    'activo'         => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }

        // ─── Responsables por paso (Planeación) ───
        $responsables = [
            'DEF_NECESIDAD'  => [['rol' => 'jefe_unidad',       'tipo' => 'ejecutor',  'principal' => true]],
            'DESC_DOCS'      => [['rol' => 'descentralizacion', 'tipo' => 'ejecutor',  'principal' => true]],
            'VAL_CONTRATISTA'=> [['rol' => 'abogado_unidad',    'tipo' => 'ejecutor',  'principal' => true]],
            'ELAB_DOCS'      => [['rol' => 'abogado_unidad',    'tipo' => 'ejecutor',  'principal' => true]],
            'CONSOL_EXP'     => [['rol' => 'abogado_unidad',    'tipo' => 'ejecutor',  'principal' => true]],
            'RAD_JURIDICA'   => [
                ['rol' => 'abogado_enlace',  'tipo' => 'ejecutor',  'principal' => true],
                ['rol' => 'secretario_juridico', 'tipo' => 'aprobador', 'principal' => false],
            ],
            'PUB_SECOP'      => [['rol' => 'operador_secop',    'tipo' => 'ejecutor',  'principal' => true]],
            'SOL_RPC'        => [['rol' => 'secretario_planeacion', 'tipo' => 'ejecutor', 'principal' => true]],
            'RAD_FINAL'      => [['rol' => 'abogado_enlace',    'tipo' => 'ejecutor',  'principal' => true]],
            'ARL_INICIO'     => [['rol' => 'abogado_unidad',    'tipo' => 'ejecutor',  'principal' => true]],
        ];

        foreach ($responsables as $codigoPaso => $resps) {
            if (!isset($flujoPasoIdsPlaneacion[$codigoPaso])) continue;
            foreach ($resps as $resp) {
                DB::table('flujo_paso_responsables')->insert([
                    'flujo_paso_id' => $flujoPasoIdsPlaneacion[$codigoPaso],
                    'rol'           => $resp['rol'],
                    'tipo'          => $resp['tipo'],
                    'es_principal'  => $resp['principal'],
                    'activo'        => true,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        $this->command->info('✅ Flujo CD-PN para Secretaría de Planeación creado (10 pasos, condiciones, documentos, responsables).');

        // ═══════════════════════════════════════════════════════════════
        // 3) FLUJO CD-PN PARA SECRETARÍA DE GOBIERNO (7 pasos, orden diferente)
        //    ↑ Demuestra que cada Secretaría personaliza su flujo
        // ═══════════════════════════════════════════════════════════════
        $flujoGobiernoId = DB::table('flujos')->insertGetId([
            'codigo'            => 'CD_PN_GOBIERNO',
            'nombre'            => 'Contratación Directa PN - Sec. Gobierno',
            'descripcion'       => 'Flujo CD-PN simplificado para la Secretaría de Gobierno (7 pasos, sin descentralización).',
            'tipo_contratacion' => 'cd_pn',
            'secretaria_id'     => $secGobierno,
            'activo'            => true,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $versionGobiernoId = DB::table('flujo_versiones')->insertGetId([
            'flujo_id'        => $flujoGobiernoId,
            'numero_version'  => 1,
            'motivo_cambio'   => 'Versión inicial del flujo CD-PN para Gobierno (simplificado).',
            'estado'          => 'activa',
            'publicada_at'    => $now,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        DB::table('flujos')->where('id', $flujoGobiernoId)->update(['version_activa_id' => $versionGobiernoId]);

        // Gobierno NO usa Descentralización ni Consolidación - tiene flujo más corto
        $pasosGobierno = [
            ['catalogo' => 'DEF_NECESIDAD',   'orden' => 0,  'area' => 'unidad_solicitante', 'dias' => 3],
            ['catalogo' => 'SOL_CDP',          'orden' => 1,  'area' => 'hacienda',           'dias' => 2],
            ['catalogo' => 'VAL_CONTRATISTA',  'orden' => 2,  'area' => 'unidad_solicitante', 'dias' => 2],
            ['catalogo' => 'ELAB_DOCS',        'orden' => 3,  'area' => 'unidad_solicitante', 'dias' => 4],
            ['catalogo' => 'RAD_JURIDICA',     'orden' => 4,  'area' => 'juridica',           'dias' => 5],
            ['catalogo' => 'PUB_SECOP',        'orden' => 5,  'area' => 'secop',              'dias' => 3],
            ['catalogo' => 'ARL_INICIO',       'orden' => 6,  'area' => 'unidad_solicitante', 'dias' => 2],
        ];

        $flujoPasoIdsGobierno = [];
        foreach ($pasosGobierno as $paso) {
            $id = DB::table('flujo_pasos')->insertGetId([
                'flujo_version_id'         => $versionGobiernoId,
                'catalogo_paso_id'         => $pasoIds[$paso['catalogo']],
                'orden'                    => $paso['orden'],
                'es_obligatorio'           => true,
                'es_paralelo'              => false,
                'dias_estimados'           => $paso['dias'],
                'area_responsable_default' => $paso['area'],
                'activo'                   => true,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);
            $flujoPasoIdsGobierno[$paso['catalogo']] = $id;
        }

        // Condición en Gobierno: monto > 20M omite SECOP simplificado
        DB::table('flujo_paso_condiciones')->insert([
            'flujo_paso_id' => $flujoPasoIdsGobierno['RAD_JURIDICA'],
            'campo'         => 'monto_estimado',
            'operador'      => '>',
            'valor'         => '20000000',
            'accion'        => 'requerido',
            'descripcion'   => 'Si el monto supera $20.000.000, la revisión jurídica es estrictamente requerida (doble revisión).',
            'prioridad'     => 1,
            'activo'        => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Documentos mínimos para Gobierno
        $docsGobierno = [
            'DEF_NECESIDAD' => [
                ['nombre' => 'Estudios Previos', 'tipo' => 'pdf', 'obligatorio' => true, 'orden' => 1],
            ],
            'SOL_CDP' => [
                ['nombre' => 'Solicitud CDP',    'tipo' => 'pdf', 'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'CDP Aprobado',     'tipo' => 'pdf', 'obligatorio' => true, 'orden' => 2],
            ],
            'VAL_CONTRATISTA' => [
                ['nombre' => 'Hoja de Vida SIGEP','tipo' => 'pdf', 'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Antecedentes',      'tipo' => 'pdf', 'obligatorio' => true, 'orden' => 2],
            ],
            'ELAB_DOCS' => [
                ['nombre' => 'Minuta del Contrato','tipo' => 'docx','obligatorio' => true, 'orden' => 1],
            ],
            'RAD_JURIDICA' => [
                ['nombre' => 'Concepto Ajustado a Derecho','tipo' => 'pdf','obligatorio' => true,'orden' => 1],
            ],
            'ARL_INICIO' => [
                ['nombre' => 'Afiliación ARL',    'tipo' => 'pdf', 'obligatorio' => true, 'orden' => 1],
                ['nombre' => 'Acta de Inicio',    'tipo' => 'pdf', 'obligatorio' => true, 'orden' => 2],
            ],
        ];

        foreach ($docsGobierno as $codigoPaso => $docs) {
            if (!isset($flujoPasoIdsGobierno[$codigoPaso])) continue;
            foreach ($docs as $doc) {
                DB::table('flujo_paso_documentos')->insert([
                    'flujo_paso_id'  => $flujoPasoIdsGobierno[$codigoPaso],
                    'nombre'         => $doc['nombre'],
                    'tipo_archivo'   => $doc['tipo'],
                    'es_obligatorio' => $doc['obligatorio'],
                    'max_archivos'   => 1,
                    'max_tamano_mb'  => 10,
                    'orden'          => $doc['orden'],
                    'activo'         => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }

        $this->command->info('✅ Flujo CD-PN para Secretaría de Gobierno creado (7 pasos, orden diferente).');
        $this->command->info('');
        $this->command->info('══════════════════════════════════════════════════');
        $this->command->info('  Motor de Flujos Configurables poblado.');
        $this->command->info('  • Catálogo: ' . count($catalogoPasos) . ' pasos reutilizables');
        $this->command->info('  • Flujo Planeación: 10 pasos');
        $this->command->info('  • Flujo Gobierno: 7 pasos (orden diferente)');
        $this->command->info('══════════════════════════════════════════════════');
    }
}
