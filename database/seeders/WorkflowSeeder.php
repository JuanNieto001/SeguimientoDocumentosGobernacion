<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $this->truncateIfExists('proceso_etapa_checks');
        $this->truncateIfExists('proceso_etapa_archivos');
        $this->truncateIfExists('proceso_etapas');
        $this->truncateIfExists('etapa_items');
        $this->truncateIfExists('etapas');
        $this->truncateIfExists('procesos');
        $this->truncateIfExists('workflows');

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $workflows = [

            /**
             * ============================================
             * 7.1 CONTRATACIÓN DIRECTA – PERSONA NATURAL
             * ============================================
             * INICIA EN: PLANEACIÓN (Etapa 0 - Verificación PAA)
             */
            [
                'codigo' => 'CD_PN',
                'nombre' => 'Contratación Directa - Persona Natural',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0: PLANEACIÓN (VERIFICACIÓN PAA) =====
                    [
                        'orden' => 0,
                        'nombre' => '0: Verificación y Carga del PAA Vigente',
                        'area_role' => 'planeacion',
                        'items' => [
                            'PAA vigente del año cargado',
                            'Verificación de inclusión de necesidad en PAA',
                            'Certificado de inclusión emitido (si aplica)',
                        ],
                    ],
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 1,
                        'nombre' => '0A: Estudio Previo Borrador y Cotizaciones',
                        'area_role' => 'unidad_solicitante',
                        'items' => [], // Sin checks - solo subida de archivos
                    ],
                    // ===== ETAPA 0B: PLANEACIÓN =====
                    [
                        'orden' => 2,
                        'nombre' => '0B: Modificación PAA y Autorización',
                        'area_role' => 'planeacion',
                        'items' => [
                            'PAA vigente del año',
                            'Certificado de inclusión en PAA',
                            'Acta de modificación del PAA (si aplica)',
                            'Acto de autorización formal de inicio',
                        ],
                    ],
                    // ===== ETAPA 1: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 3,
                        'nombre' => '1: Preparación Inicial de Estudios Previos',
                        'area_role' => 'unidad_solicitante',
                        'items' => [
                            'Estudio de Mercado con cotizaciones',
                            'Análisis del Sector',
                            'Estudios Previos (borrador)',
                            'Matriz de distribución de riesgos',
                            'Garantías contempladas',
                            'Identificación del supervisor',
                        ],
                    ],
                    // ===== ETAPA 2: PLANEACIÓN =====
                    [
                        'orden' => 4,
                        'nombre' => '2: Solicitud de Documentos Presupuestales',
                        'area_role' => 'planeacion',
                        'items' => [
                            'CDP solicitado',
                            'Certificado de compatibilidad solicitado',
                        ],
                    ],
                    // ===== ETAPA 3: HACIENDA =====
                    [
                        'orden' => 5,
                        'nombre' => '3: Viabilidad Económica',
                        'area_role' => 'hacienda',
                        'items' => [
                            'CDP emitido',
                            'PAA verificado',
                            'Certificado de compatibilidad emitido',
                            'Viabilidad Económica emitida',
                        ],
                    ],
                    // ===== ETAPA 4: JURÍDICA =====
                    [
                        'orden' => 6,
                        'nombre' => '4: Verificación del Contratista',
                        'area_role' => 'juridica',
                        'items' => [
                            'Hoja de vida y soportes (SIGEP)',
                            'Certificados de experiencia',
                            'Antecedentes Procuraduría (SIRI)',
                            'Antecedentes Policía Nacional',
                            'Antecedentes Contraloría',
                            'Verificación inhabilidades e incompatibilidades',
                            'Aportes seguridad social al día',
                            'Checklist precontractual completo',
                        ],
                    ],
                    // ===== ETAPA 5: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 7,
                        'nombre' => '5: Proyección del Contrato',
                        'area_role' => 'unidad_solicitante',
                        'items' => [
                            'Minuta del contrato (borrador)',
                            'Estudios Previos (versión definitiva)',
                            'Solicitud de contratación',
                            'Designación de supervisor',
                        ],
                    ],
                    // ===== ETAPA 6: PLANEACIÓN =====
                    [
                        'orden' => 8,
                        'nombre' => '6: Revisión y Aprobación por Secretaría',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Estudios previos revisados y aprobados',
                            'Minuta revisada y aprobada',
                            'Solicitud de contratación firmada',
                            'Designación de supervisor firmada',
                        ],
                    ],
                    // ===== ETAPA 7: JURÍDICA =====
                    [
                        'orden' => 9,
                        'nombre' => '7: Ajustado a Derecho',
                        'area_role' => 'juridica',
                        'items' => [
                            'Cotizaciones y correos',
                            'Análisis del Sector',
                            'CDP',
                            'PAA',
                            'Certificado de compatibilidad',
                            'Viabilidad económica',
                            'Estudios previos',
                            'Minuta del contrato',
                            'Solicitud y designación de supervisor',
                            'Ajustado a Derecho emitido',
                        ],
                    ],
                    // ===== ETAPA 8: SECOP =====
                    [
                        'orden' => 10,
                        'nombre' => '8: Estructuración en SECOP',
                        'area_role' => 'secop',
                        'items' => [
                            'Proceso estructurado en SECOP',
                            'Documentos cargados en SECOP',
                            'Contrato electrónico generado',
                        ],
                    ],
                    // ===== ETAPA 9: SECOP =====
                    [
                        'orden' => 11,
                        'nombre' => '9: Firma del Contrato',
                        'area_role' => 'secop',
                        'items' => [
                            'Contrato firmado por las partes',
                            'Contrato cargado en SECOP',
                        ],
                    ],
                    // ===== ETAPA 10: HACIENDA =====
                    [
                        'orden' => 12,
                        'nombre' => '10: Registro Presupuestal (RP)',
                        'area_role' => 'hacienda',
                        'items' => [
                            'Contrato firmado',
                            'Contrato electrónico de SECOP',
                            'Ajustado a Derecho',
                            'RP solicitado y emitido',
                        ],
                    ],
                    // ===== ETAPA 11: JURÍDICA =====
                    [
                        'orden' => 13,
                        'nombre' => '11: Radicación Expediente Físico',
                        'area_role' => 'juridica',
                        'items' => [
                            'Expediente físico organizado',
                            'Comprobante de radicación',
                        ],
                    ],
                    // ===== ETAPA 12: JURÍDICA =====
                    [
                        'orden' => 14,
                        'nombre' => '12: Solicitud y Aprobación de Pólizas',
                        'area_role' => 'juridica',
                        'items' => [
                            'Pólizas cargadas en SECOP por contratista',
                            'Pólizas revisadas',
                            'Pólizas aprobadas',
                        ],
                    ],
                    // ===== ETAPA 13: SECOP =====
                    [
                        'orden' => 15,
                        'nombre' => '13: Acta de Inicio del Contrato',
                        'area_role' => 'secop',
                        'items' => [
                            'Reunión de inicio programada',
                            'Acta de inicio elaborada',
                            'Acta firmada por todas las partes',
                            'Registro en sistema',
                        ],
                    ],
                    // ===== ETAPA 14: SECOP (FINAL) =====
                    [
                        'orden' => 16,
                        'nombre' => '14: Ejecución y Cierre',
                        'area_role' => 'secop',
                        'items' => [
                            'Informes periódicos de supervisión',
                            'Pagos tramitados',
                            'Modificaciones gestionadas (si aplica)',
                            'Acta de terminación',
                            'Liquidación (si aplica)',
                            'Cierre en SECOP',
                        ],
                    ],
                ],
            ],

            // ================================
            // WORKFLOW 2: MÍNIMA CUANTÍA (MC)
            // ================================
            [
                'codigo' => 'MC',
                'nombre' => 'Mínima Cuantía (MC)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0: VERIFICACIÓN PAA (PLANEACIÓN) =====
                    [
                        'orden' => 0,
                        'nombre' => '0: Verificación del PAA',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Verificar inclusión en PAA vigente',
                            'Revisar ficha técnica del proceso',
                            'Validar disponibilidad presupuestal',
                            'Aprobar inicio del proceso',
                        ],
                    ],
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 1,
                        'nombre' => '0A: Solicitud y Borrador de Estudios Previos',
                        'area_role' => 'unidad_solicitante',
                        'items' => [],
                    ],
                    // ===== ETAPA 0B: PLANEACIÓN =====
                    [
                        'orden' => 2,
                        'nombre' => '0B: CDP y Análisis del Sector',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Expedición de CDP',
                            'Análisis del sector',
                        ],
                    ],
                    // ===== ETAPA 1: PLANEACIÓN =====
                    [
                        'orden' => 3,
                        'nombre' => '1: Estudios Previos Finales',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Estudios previos completos',
                            'Aprobación de modalidad MC',
                        ],
                    ],
                    // ===== ETAPA 2: JURÍDICA =====
                    [
                        'orden' => 4,
                        'nombre' => '2: Revisión Jurídica',
                        'area_role' => 'juridica',
                        'items' => [
                            'Verificación de requisitos legales',
                            'Validación de documentos',
                            'Visto bueno jurídico',
                        ],
                    ],
                    // ===== ETAPA 3: HACIENDA =====
                    [
                        'orden' => 5,
                        'nombre' => '3: Viabilidad Económica',
                        'area_role' => 'hacienda',
                        'items' => [
                            'Verificación de disponibilidad presupuestal',
                            'Valoración económica',
                            'Viabilidad presupuestal aprobada',
                        ],
                    ],
                    // ===== ETAPA 4: PLANEACIÓN =====
                    [
                        'orden' => 6,
                        'nombre' => '4: Elaboración de Invitación Pública',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Redacción de invitación',
                            'Definición de criterios de selección',
                            'Cronograma del proceso',
                        ],
                    ],
                    // ===== ETAPA 5: SECOP =====
                    [
                        'orden' => 7,
                        'nombre' => '5: Publicación en SECOP II',
                        'area_role' => 'secop',
                        'items' => [
                            'Carga de invitación en SECOP II',
                            'Publicación oficial',
                            'Apertura de plazo para ofertas',
                        ],
                    ],
                    // ===== ETAPA 6: PLANEACIÓN =====
                    [
                        'orden' => 8,
                        'nombre' => '6: Recepción y Verificación de Ofertas',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Recepción de ofertas',
                            'Verificación de requisitos habilitantes',
                            'Registro de oferentes',
                        ],
                    ],
                    // ===== ETAPA 7: PLANEACIÓN =====
                    [
                        'orden' => 9,
                        'nombre' => '7: Evaluación de Ofertas',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Evaluación técnica',
                            'Evaluación económica',
                            'Puntaje asignado',
                        ],
                    ],
                    // ===== ETAPA 8: PLANEACIÓN =====
                    [
                        'orden' => 10,
                        'nombre' => '8: Elaboración de Informe de Evaluación',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Informe de evaluación elaborado',
                            'Recomendación de adjudicación',
                        ],
                    ],
                    // ===== ETAPA 9: JURÍDICA =====
                    [
                        'orden' => 11,
                        'nombre' => '9: Revisión Jurídica de Informe',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión del informe de evaluación',
                            'Verificación de procedimiento',
                            'Concepto jurídico favorable',
                        ],
                    ],
                    // ===== ETAPA 10: SECOP =====
                    [
                        'orden' => 12,
                        'nombre' => '10: Publicación de Informe',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación en SECOP II',
                            'Notificación a oferentes',
                            'Apertura de plazo para observaciones',
                        ],
                    ],
                    // ===== ETAPA 11: PLANEACIÓN =====
                    [
                        'orden' => 13,
                        'nombre' => '11: Respuesta a Observaciones',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Análisis de observaciones',
                            'Respuestas elaboradas',
                            'Publicación de respuestas',
                        ],
                    ],
                    // ===== ETAPA 12: JURÍDICA =====
                    [
                        'orden' => 14,
                        'nombre' => '12: Acto de Adjudicación',
                        'area_role' => 'juridica',
                        'items' => [
                            'Elaboración del acto administrativo',
                            'Firma del ordenador del gasto',
                            'Adjudicación formalizada',
                        ],
                    ],
                    // ===== ETAPA 13: SECOP =====
                    [
                        'orden' => 15,
                        'nombre' => '13: Publicación de Adjudicación',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación en SECOP II',
                            'Notificación al adjudicatario',
                        ],
                    ],
                    // ===== ETAPA 14: JURÍDICA =====
                    [
                        'orden' => 16,
                        'nombre' => '14: Elaboración y Firma del Contrato',
                        'area_role' => 'juridica',
                        'items' => [
                            'Minuta contractual elaborada',
                            'Revisión y aprobación',
                            'Firma de las partes',
                        ],
                    ],
                    // ===== ETAPA 15: JURÍDICA =====
                    [
                        'orden' => 17,
                        'nombre' => '15: Solicitud y Aprobación de Pólizas',
                        'area_role' => 'juridica',
                        'items' => [
                            'Pólizas cargadas por contratista',
                            'Revisión y aprobación',
                        ],
                    ],
                    // ===== ETAPA 16: SECOP (FINAL) =====
                    [
                        'orden' => 18,
                        'nombre' => '16: Perfeccionamiento y Ejecución',
                        'area_role' => 'secop',
                        'items' => [
                            'Acta de inicio',
                            'Supervisión',
                            'Cierre del contrato',
                        ],
                    ],
                ],
            ],

            // ================================
            // WORKFLOW 3: SELECCIÓN ABREVIADA (SA)
            // ================================
            [
                'codigo' => 'SA',
                'nombre' => 'Selección Abreviada (SA)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0: VERIFICACIÓN PAA (PLANEACIÓN) =====
                    [
                        'orden' => 0,
                        'nombre' => '0: Verificación del PAA',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Verificar inclusión en PAA vigente',
                            'Revisar ficha técnica del proceso',
                            'Validar disponibilidad presupuestal',
                            'Aprobar inicio del proceso',
                        ],
                    ],
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 1,
                        'nombre' => '0A: Solicitud y Borrador de Estudios Previos',
                        'area_role' => 'unidad_solicitante',
                        'items' => [],
                    ],
                    // ===== ETAPA 0B: PLANEACIÓN =====
                    [
                        'orden' => 2,
                        'nombre' => '0B: CDP y Análisis del Sector',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Expedición de CDP',
                            'Análisis del sector',
                            'Estudios de mercado',
                        ],
                    ],
                    // ===== ETAPA 1: PLANEACIÓN =====
                    [
                        'orden' => 3,
                        'nombre' => '1: Estudios Previos Completos',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Estudios previos finalizados',
                            'Justificación de SA',
                            'Especificaciones técnicas',
                        ],
                    ],
                    // ===== ETAPA 2: JURÍDICA =====
                    [
                        'orden' => 4,
                        'nombre' => '2: Revisión Jurídica',
                        'area_role' => 'juridica',
                        'items' => [
                            'Verificación de requisitos legales',
                            'Validación de causal de SA',
                            'Visto bueno jurídico',
                        ],
                    ],
                    // ===== ETAPA 3: HACIENDA =====
                    [
                        'orden' => 5,
                        'nombre' => '3: Viabilidad Económica',
                        'area_role' => 'hacienda',
                        'items' => [
                            'Análisis presupuestal',
                            'Verificación de disponibilidad',
                            'Viabilidad aprobada',
                        ],
                    ],
                    // ===== ETAPA 4: PLANEACIÓN =====
                    [
                        'orden' => 6,
                        'nombre' => '4: Elaboración de Pliego de Condiciones',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Redacción de pliego',
                            'Definición de requisitos habilitantes',
                            'Criterios de evaluación',
                            'Cronograma',
                        ],
                    ],
                    // ===== ETAPA 5: JURÍDICA =====
                    [
                        'orden' => 7,
                        'nombre' => '5: Revisión Jurídica del Pliego',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión de clausulado',
                            'Verificación de legalidad',
                            'Aprobación jurídica',
                        ],
                    ],
                    // ===== ETAPA 6: SECOP =====
                    [
                        'orden' => 8,
                        'nombre' => '6: Publicación del Proyecto de Pliego',
                        'area_role' => 'secop',
                        'items' => [
                            'Carga en SECOP II',
                            'Publicación proyecto de pliego',
                            'Apertura de plazo de observaciones',
                        ],
                    ],
                    // ===== ETAPA 7: PLANEACIÓN =====
                    [
                        'orden' => 9,
                        'nombre' => '7: Respuesta a Observaciones al Proyecto',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Recepción de observaciones',
                            'Análisis y respuestas',
                            'Ajustes al pliego (si aplica)',
                        ],
                    ],
                    // ===== ETAPA 8: JURÍDICA =====
                    [
                        'orden' => 10,
                        'nombre' => '8: Aprobación Pliego Definitivo',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión final',
                            'Firma del ordenador del gasto',
                            'Pliego definitivo aprobado',
                        ],
                    ],
                    // ===== ETAPA 9: SECOP =====
                    [
                        'orden' => 11,
                        'nombre' => '9: Publicación de Apertura',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación pliego definitivo',
                            'Aviso de convocatoria',
                            'Apertura oficial del proceso',
                        ],
                    ],
                    // ===== ETAPA 10: PLANEACIÓN =====
                    [
                        'orden' => 12,
                        'nombre' => '10: Audiencia de Aclaraciones',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Programación de audiencia',
                            'Realización de audiencia',
                            'Acta de audiencia',
                            'Respuestas publicadas',
                        ],
                    ],
                    // ===== ETAPA 11: PLANEACIÓN =====
                    [
                        'orden' => 13,
                        'nombre' => '11: Cierre y Verificación de Ofertas',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Cierre del proceso',
                            'Recepción de ofertas',
                            'Verificación de requisitos habilitantes',
                        ],
                    ],
                    // ===== ETAPA 12: PLANEACIÓN =====
                    [
                        'orden' => 14,
                        'nombre' => '12: Evaluación de Ofertas',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Evaluación técnica',
                            'Evaluación económica',
                            'Asignación de puntajes',
                        ],
                    ],
                    // ===== ETAPA 13: PLANEACIÓN =====
                    [
                        'orden' => 15,
                        'nombre' => '13: Informe de Evaluación',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Elaboración de informe',
                            'Cuadro comparativo',
                            'Recomendación de adjudicación',
                        ],
                    ],
                    // ===== ETAPA 14: JURÍDICA =====
                    [
                        'orden' => 16,
                        'nombre' => '14: Revisión Jurídica del Informe',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión del informe',
                            'Verificación de procedimiento',
                            'Concepto jurídico',
                        ],
                    ],
                    // ===== ETAPA 15: SECOP =====
                    [
                        'orden' => 17,
                        'nombre' => '15: Publicación de Informe',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación en SECOP II',
                            'Notificación a proponentes',
                            'Traslado para observaciones',
                        ],
                    ],
                    // ===== ETAPA 16: PLANEACIÓN =====
                    [
                        'orden' => 18,
                        'nombre' => '16: Respuesta a Observaciones al Informe',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Análisis de observaciones',
                            'Respuestas elaboradas',
                            'Publicación de respuestas',
                        ],
                    ],
                    // ===== ETAPA 17: JURÍDICA =====
                    [
                        'orden' => 19,
                        'nombre' => '17: Adjudicación',
                        'area_role' => 'juridica',
                        'items' => [
                            'Acto de adjudicación',
                            'Firma del ordenador',
                            'Publicación del acto',
                        ],
                    ],
                    // ===== ETAPA 18: JURÍDICA =====
                    [
                        'orden' => 20,
                        'nombre' => '18: Firma del Contrato y Pólizas',
                        'area_role' => 'juridica',
                        'items' => [
                            'Elaboración de minuta',
                            'Firma del contrato',
                            'Solicitud y aprobación de pólizas',
                        ],
                    ],
                    // ===== ETAPA 19: SECOP (FINAL) =====
                    [
                        'orden' => 21,
                        'nombre' => '19: Perfeccionamiento y Ejecución',
                        'area_role' => 'secop',
                        'items' => [
                            'Registro en SECOP',
                            'Acta de inicio',
                            'Supervisión y cierre',
                        ],
                    ],
                ],
            ],

            // ================================
            // WORKFLOW 4: LICITACIÓN PÚBLICA (LP)
            // ================================
            [
                'codigo' => 'LP',
                'nombre' => 'Licitación Pública (LP)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0: VERIFICACIÓN PAA (PLANEACIÓN) =====
                    [
                        'orden' => 0,
                        'nombre' => '0: Verificación del PAA',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Verificar inclusión en PAA vigente',
                            'Revisar ficha técnica del proceso',
                            'Validar disponibilidad presupuestal',
                            'Aprobar inicio del proceso',
                        ],
                    ],
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 1,
                        'nombre' => '0A: Solicitud y Borrador de Estudios Previos',
                        'area_role' => 'unidad_solicitante',
                        'items' => [],
                    ],
                    // ===== ETAPA 0B: PLANEACIÓN =====
                    [
                        'orden' => 2,
                        'nombre' => '0B: CDP y Análisis del Sector',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Expedición de CDP',
                            'Análisis del sector',
                            'Estudios de mercado',
                        ],
                    ],
                    // ===== ETAPA 1: PLANEACIÓN =====
                    [
                        'orden' => 3,
                        'nombre' => '1: Estudios Previos Completos',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Estudios previos finalizados',
                            'Justificación de LP',
                            'Especificaciones técnicas detalladas',
                        ],
                    ],
                    // ===== ETAPA 2: JURÍDICA =====
                    [
                        'orden' => 4,
                        'nombre' => '2: Revisión Jurídica',
                        'area_role' => 'juridica',
                        'items' => [
                            'Verificación de requisitos legales',
                            'Validación de modalidad LP',
                            'Visto bueno jurídico',
                        ],
                    ],
                    // ===== ETAPA 3: HACIENDA =====
                    [
                        'orden' => 5,
                        'nombre' => '3: Viabilidad Económica',
                        'area_role' => 'hacienda',
                        'items' => [
                            'Análisis presupuestal detallado',
                            'Verificación de disponibilidad',
                            'Viabilidad aprobada',
                        ],
                    ],
                    // ===== ETAPA 4: PLANEACIÓN =====
                    [
                        'orden' => 6,
                        'nombre' => '4: Elaboración de Pliego de Condiciones',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Redacción de pliego completo',
                            'Definición de requisitos habilitantes',
                            'Criterios de evaluación',
                            'Cronograma detallado',
                        ],
                    ],
                    // ===== ETAPA 5: JURÍDICA =====
                    [
                        'orden' => 7,
                        'nombre' => '5: Revisión Jurídica del Pliego',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión exhaustiva de clausulado',
                            'Verificación de legalidad',
                            'Aprobación jurídica',
                        ],
                    ],
                    // ===== ETAPA 6: SECOP =====
                    [
                        'orden' => 8,
                        'nombre' => '6: Publicación del Proyecto de Pliego',
                        'area_role' => 'secop',
                        'items' => [
                            'Carga completa en SECOP II',
                            'Publicación proyecto de pliego',
                            'Apertura de plazo de observaciones',
                        ],
                    ],
                    // ===== ETAPA 7: PLANEACIÓN =====
                    [
                        'orden' => 9,
                        'nombre' => '7: Respuesta a Observaciones al Proyecto',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Recepción de observaciones',
                            'Análisis técnico y legal',
                            'Respuestas fundamentadas',
                            'Ajustes al pliego (si aplica)',
                        ],
                    ],
                    // ===== ETAPA 8: JURÍDICA =====
                    [
                        'orden' => 10,
                        'nombre' => '8: Aprobación Pliego Definitivo',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión final del pliego',
                            'Firma del ordenador del gasto',
                            'Pliego definitivo aprobado',
                        ],
                    ],
                    // ===== ETAPA 9: SECOP =====
                    [
                        'orden' => 11,
                        'nombre' => '9: Publicación de Apertura',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación pliego definitivo',
                            'Aviso de convocatoria pública',
                            'Apertura oficial del proceso',
                        ],
                    ],
                    // ===== ETAPA 10: PLANEACIÓN (EXCLUSIVA DE LP) =====
                    [
                        'orden' => 12,
                        'nombre' => '10: Audiencia de Riesgos',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Programación de audiencia',
                            'Realización de audiencia de asignación de riesgos',
                            'Acta de audiencia',
                            'Tipificación y asignación de riesgos',
                        ],
                    ],
                    // ===== ETAPA 11: PLANEACIÓN =====
                    [
                        'orden' => 13,
                        'nombre' => '11: Audiencia de Aclaraciones',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Programación de audiencia',
                            'Realización de audiencia',
                            'Acta de audiencia',
                            'Respuestas publicadas',
                        ],
                    ],
                    // ===== ETAPA 12: PLANEACIÓN =====
                    [
                        'orden' => 14,
                        'nombre' => '12: Cierre y Verificación de Ofertas',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Cierre del proceso',
                            'Recepción de ofertas',
                            'Audiencia pública de apertura de sobres',
                            'Verificación de requisitos habilitantes',
                        ],
                    ],
                    // ===== ETAPA 13: PLANEACIÓN =====
                    [
                        'orden' => 15,
                        'nombre' => '13: Evaluación de Ofertas',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Evaluación técnica',
                            'Evaluación económica',
                            'Asignación de puntajes',
                        ],
                    ],
                    // ===== ETAPA 14: PLANEACIÓN =====
                    [
                        'orden' => 16,
                        'nombre' => '14: Informe de Evaluación',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Elaboración de informe detallado',
                            'Cuadro comparativo',
                            'Recomendación de adjudicación',
                        ],
                    ],
                    // ===== ETAPA 15: PLANEACIÓN (EXCLUSIVA DE LP) =====
                    [
                        'orden' => 17,
                        'nombre' => '15: Audiencia Pública de Adjudicación',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Convocatoria a audiencia',
                            'Presentación del informe de evaluación',
                            'Audiencia pública realizada',
                            'Acta de audiencia',
                        ],
                    ],
                    // ===== ETAPA 16: JURÍDICA =====
                    [
                        'orden' => 18,
                        'nombre' => '16: Revisión Jurídica del Informe',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión del informe',
                            'Verificación de procedimiento',
                            'Concepto jurídico',
                        ],
                    ],
                    // ===== ETAPA 17: SECOP =====
                    [
                        'orden' => 19,
                        'nombre' => '17: Publicación de Informe',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación en SECOP II',
                            'Notificación a proponentes',
                            'Traslado para observaciones',
                        ],
                    ],
                    // ===== ETAPA 18: PLANEACIÓN =====
                    [
                        'orden' => 20,
                        'nombre' => '18: Respuesta a Observaciones al Informe',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Análisis de observaciones',
                            'Respuestas fundamentadas',
                            'Publicación de respuestas',
                        ],
                    ],
                    // ===== ETAPA 19: JURÍDICA =====
                    [
                        'orden' => 21,
                        'nombre' => '19: Adjudicación',
                        'area_role' => 'juridica',
                        'items' => [
                            'Acto de adjudicación',
                            'Firma del ordenador',
                            'Publicación del acto',
                        ],
                    ],
                    // ===== ETAPA 20: JURÍDICA =====
                    [
                        'orden' => 22,
                        'nombre' => '20: Firma del Contrato y Pólizas',
                        'area_role' => 'juridica',
                        'items' => [
                            'Elaboración de minuta',
                            'Firma del contrato',
                            'Solicitud y aprobación de pólizas',
                        ],
                    ],
                    // ===== ETAPA 21: SECOP (FINAL) =====
                    [
                        'orden' => 23,
                        'nombre' => '21: Perfeccionamiento y Ejecución',
                        'area_role' => 'secop',
                        'items' => [
                            'Registro completo en SECOP',
                            'Acta de inicio',
                            'Supervisión y cierre',
                        ],
                    ],
                ],
            ],

            // ================================
            // WORKFLOW 5: CONCURSO DE MÉRITOS (CM)
            // ================================
            [
                'codigo' => 'CM',
                'nombre' => 'Concurso de Méritos (CM)',
                'activo' => true,
                'requiere_viabilidad_economica_inicial' => false, // CM NO requiere viabilidad inicial
                'requiere_estudios_previos_completos' => true,
                'observaciones' => 'Viabilidad económica solo DESPUÉS de negociación con mejor calificado técnico',
                'etapas' => [
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 0,
                        'nombre' => '0A: Solicitud y Borrador de Estudios Previos',
                        'area_role' => 'unidad_solicitante',
                        'items' => [],
                    ],
                    // ===== ETAPA 0B: PLANEACIÓN =====
                    [
                        'orden' => 1,
                        'nombre' => '0B: CDP y Análisis del Sector',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Expedición de CDP',
                            'Análisis del sector de consultoría',
                        ],
                    ],
                    // ===== ETAPA 1: PLANEACIÓN =====
                    [
                        'orden' => 2,
                        'nombre' => '1: Estudios Previos (sin Viabilidad Económica previa)',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Estudios previos técnicos',
                            'Alcance de consultoría',
                            'Perfiles profesionales requeridos',
                        ],
                    ],
                    // ===== ETAPA 2: JURÍDICA =====
                    [
                        'orden' => 3,
                        'nombre' => '2: Revisión Jurídica',
                        'area_role' => 'juridica',
                        'items' => [
                            'Verificación de requisitos CM',
                            'Validación de consultoría',
                            'Visto bueno jurídico',
                        ],
                    ],
                    // ===== ETAPA 3: PLANEACIÓN =====
                    [
                        'orden' => 4,
                        'nombre' => '3: Elaboración de Pliego',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Pliego orientado a calidad técnica',
                            'Criterios de evaluación de méritos',
                            'Ponderación técnica (sin precio inicial)',
                        ],
                    ],
                    // ===== ETAPA 4: JURÍDICA =====
                    [
                        'orden' => 5,
                        'nombre' => '4: Revisión Jurídica del Pliego',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión de clausulado',
                            'Verificación de legalidad',
                            'Aprobación jurídica',
                        ],
                    ],
                    // ===== ETAPA 5: SECOP =====
                    [
                        'orden' => 6,
                        'nombre' => '5: Publicación del Proyecto de Pliego',
                        'area_role' => 'secop',
                        'items' => [
                            'Carga en SECOP II',
                            'Publicación proyecto',
                            'Apertura de observaciones',
                        ],
                    ],
                    // ===== ETAPA 6: PLANEACIÓN =====
                    [
                        'orden' => 7,
                        'nombre' => '6: Respuesta a Observaciones',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Análisis de observaciones',
                            'Respuestas técnicas',
                            'Ajustes al pliego',
                        ],
                    ],
                    // ===== ETAPA 7: JURÍDICA =====
                    [
                        'orden' => 8,
                        'nombre' => '7: Aprobación Pliego Definitivo',
                        'area_role' => 'juridica',
                        'items' => [
                            'Revisión final',
                            'Firma del ordenador',
                            'Pliego definitivo aprobado',
                        ],
                    ],
                    // ===== ETAPA 8: SECOP =====
                    [
                        'orden' => 9,
                        'nombre' => '8: Publicación de Apertura',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación pliego definitivo',
                            'Aviso de convocatoria',
                            'Apertura oficial',
                        ],
                    ],
                    // ===== ETAPA 9: PLANEACIÓN =====
                    [
                        'orden' => 10,
                        'nombre' => '9: Audiencia de Aclaraciones',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Programación de audiencia',
                            'Realización de audiencia',
                            'Acta y respuestas',
                        ],
                    ],
                    // ===== ETAPA 10: PLANEACIÓN =====
                    [
                        'orden' => 11,
                        'nombre' => '10: Cierre y Recepción de Propuestas',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Cierre del proceso',
                            'Recepción de propuestas técnicas',
                            'Verificación habilitante',
                        ],
                    ],
                    // ===== ETAPA 11: PLANEACIÓN =====
                    [
                        'orden' => 12,
                        'nombre' => '11: Evaluación Técnica de Méritos',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Evaluación de experiencia',
                            'Evaluación de formación',
                            'Evaluación de metodología',
                            'Puntaje de calidad técnica',
                        ],
                    ],
                    // ===== ETAPA 12: PLANEACIÓN =====
                    [
                        'orden' => 13,
                        'nombre' => '12: Informe de Evaluación Técnica',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Informe de méritos',
                            'Selección del mejor puntaje técnico',
                        ],
                    ],
                    // ===== ETAPA 13: SECOP =====
                    [
                        'orden' => 14,
                        'nombre' => '13: Publicación de Informe Técnico',
                        'area_role' => 'secop',
                        'items' => [
                            'Publicación en SECOP II',
                            'Notificación a proponentes',
                        ],
                    ],
                    // ===== ETAPA 14: PLANEACIÓN =====
                    [
                        'orden' => 15,
                        'nombre' => '14: Negociación con el Mejor Calificado',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Convocatoria a negociación',
                            'Negociación de honorarios',
                            'Acta de negociación',
                        ],
                    ],
                    // ===== ETAPA 15: HACIENDA (ÚNICA vez para CM) =====
                    [
                        'orden' => 16,
                        'nombre' => '15: Viabilidad Económica del Valor Negociado',
                        'area_role' => 'hacienda',
                        'items' => [
                            'Análisis del valor negociado',
                            'Verificación presupuestal',
                            'Aprobación económica',
                        ],
                    ],
                    // ===== ETAPA 16: JURÍDICA =====
                    [
                        'orden' => 17,
                        'nombre' => '16: Adjudicación',
                        'area_role' => 'juridica',
                        'items' => [
                            'Acto de adjudicación',
                            'Firma del ordenador',
                            'Publicación del acto',
                        ],
                    ],
                    // ===== ETAPA 17: JURÍDICA =====
                    [
                        'orden' => 18,
                        'nombre' => '17: Firma del Contrato y Pólizas',
                        'area_role' => 'juridica',
                        'items' => [
                            'Elaboración de minuta',
                            'Firma del contrato',
                            'Solicitud y aprobación de pólizas',
                        ],
                    ],
                    // ===== ETAPA 18: SECOP (FINAL) =====
                    [
                        'orden' => 19,
                        'nombre' => '18: Perfeccionamiento y Ejecución',
                        'area_role' => 'secop',
                        'items' => [
                            'Registro en SECOP',
                            'Acta de inicio',
                            'Supervisión y cierre',
                        ],
                    ],
                ],
            ],

        ];

        // Insertar workflows y sus etapas
        foreach ($workflows as $workflowData) {
            $workflowId = DB::table('workflows')->insertGetId([
                'codigo' => $workflowData['codigo'],
                'nombre' => $workflowData['nombre'],
                'activo' => $workflowData['activo'],
                'requiere_viabilidad_economica_inicial' => $workflowData['requiere_viabilidad_economica_inicial'] ?? true,
                'requiere_estudios_previos_completos' => $workflowData['requiere_estudios_previos_completos'] ?? true,
                'observaciones' => $workflowData['observaciones'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $etapasIds = [];

            foreach ($workflowData['etapas'] as $etapaData) {
                $etapaId = DB::table('etapas')->insertGetId([
                    'workflow_id' => $workflowId,
                    'orden' => $etapaData['orden'],
                    'nombre' => $etapaData['nombre'],
                    'area_role' => $etapaData['area_role'],
                    'activa' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $etapasIds[$etapaData['orden']] = $etapaId;

                // Insertar items de la etapa
                if (!empty($etapaData['items'])) {
                    foreach ($etapaData['items'] as $orden => $label) {
                        DB::table('etapa_items')->insert([
                            'etapa_id' => $etapaId,
                            'orden' => $orden + 1,
                            'label' => $label,
                            'requerido' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Conectar etapas con next_etapa_id
            $ordenesOrdenados = array_keys($etapasIds);
            sort($ordenesOrdenados);

            for ($i = 0; $i < count($ordenesOrdenados) - 1; $i++) {
                $ordenActual = $ordenesOrdenados[$i];
                $ordenSiguiente = $ordenesOrdenados[$i + 1];

                DB::table('etapas')
                    ->where('id', $etapasIds[$ordenActual])
                    ->update([
                        'next_etapa_id' => $etapasIds[$ordenSiguiente],
                        'updated_at' => now(),
                    ]);
            }
        }

        $this->command->info('✅ Workflows, etapas e items creados correctamente.');
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
