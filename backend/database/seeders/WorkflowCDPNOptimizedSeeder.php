<?php
/**
 * Archivo: backend/database/seeders/WorkflowCDPNOptimizedSeeder.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * WorkflowCDPNOptimizedSeeder - Flujo CD-PN Optimizado para Producción
 * ====================================================================
 *
 * FASE 3 - ORGANIZACIÓN DE DATOS
 * Flujo Contratación Directa Persona Natural optimizado con:
 * - 10 etapas detalladas y específicas
 * - Documentos requeridos y generados diferenciados
 * - Validaciones automáticas por etapa
 * - Tiempos estimados realistas
 * - Responsabilidades claramente definidas
 *
 * MEJORAS IMPLEMENTADAS:
 * ✅ Documentos específicos por etapa
 * ✅ Validaciones automáticas configurables
 * ✅ Tiempos estimados realistas
 * ✅ Responsabilidades claras por etapa
 * ✅ Documentos generados vs. requeridos
 */
class WorkflowCDPNOptimizedSeeder extends Seeder
{
    /**
     * Seed del flujo CD-PN optimizado para producción
     */
    public function run(): void
    {
        $this->command->info('⚡ Creando flujo CD-PN optimizado para producción...');

        // Definición del flujo CD-PN optimizado
        $flujoCDPNOptimizado = [
            'codigo' => 'CD_PN_OPTIMIZED',
            'nombre' => 'Contratación Directa - Persona Natural (Optimizado)',
            'descripcion' => 'Flujo optimizado para contratación directa con persona natural - 10 etapas detalladas',
            'activo' => true,
            'tipo_contratacion' => 'CD-PN',
            'valor_maximo' => 50000000, // $50 millones según normativa
            'requiere_viabilidad_economica_inicial' => false,
            'requiere_estudios_previos_completos' => true,
            'tiempo_total_estimado' => '25 días hábiles',
            'observaciones' => 'Flujo CD-PN optimizado con validaciones automáticas, documentos específicos y tiempos realistas para ambiente de producción.',

            'etapas' => [

                // ETAPA 0: IDENTIFICACIÓN DE NECESIDAD
                [
                    'orden' => 0,
                    'nombre' => 'Identificación de la Necesidad',
                    'descripcion' => 'Análisis de la necesidad contractual y verificación en PAA. Gestión inicial del proceso de contratación.',
                    'area_role' => 'unidad_solicitante',
                    'responsable_principal' => 'Jefe de Unidad',
                    'tiempo_estimado' => '3 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'solicitud_proceso' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Solicitud de inicio de proceso',
                            'descripcion' => 'Solicitud formal de inicio del proceso contractual',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf,doc,docx'
                        ],
                        'verificacion_paa' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Verificación inclusión en PAA',
                            'descripcion' => 'Documento que certifica inclusión en Plan Anual de Adquisiciones vigente',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ]
                    ],
                    'documentos_generados' => [
                        'estudio_necesidad' => [
                            'tipo' => 'generado',
                            'nombre' => 'Estudio de necesidad',
                            'descripcion' => 'Análisis técnico de la necesidad contractual',
                            'responsable' => 'unidad_solicitante'
                        ]
                    ],
                    'validaciones' => [
                        'paa_verificado' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar que la necesidad esté incluida en PAA vigente',
                            'campo_verificar' => 'codigo_paa',
                            'obligatorio' => true
                        ],
                        'presupuesto_disponible' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Confirmar disponibilidad presupuestal inicial',
                            'responsable' => 'unidad_solicitante'
                        ],
                        'modalidad_correcta' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Validar que CD-PN es la modalidad apropiada según valor',
                            'campo_verificar' => 'valor_estimado',
                            'parametros' => ['max_valor' => 50000000]
                        ]
                    ],
                    'siguiente_etapa' => 1
                ],

                // ETAPA 1: ESTUDIOS PREVIOS Y DOCUMENTOS PRECONTRACTUALES
                [
                    'orden' => 1,
                    'nombre' => 'Elaboración de Estudios Previos',
                    'descripcion' => 'Elaboración completa de estudios previos y documentos precontractuales según normativa vigente.',
                    'area_role' => 'unidad_solicitante',
                    'responsable_principal' => 'Profesional de Contratación',
                    'tiempo_estimado' => '5 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'estudio_necesidad_aprobado' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Estudio de necesidad aprobado',
                            'descripcion' => 'Estudio de necesidad aprobado en etapa anterior',
                            'obligatorio' => true,
                            'origen_etapa' => 0
                        ]
                    ],
                    'documentos_generados' => [
                        'estudios_previos' => [
                            'tipo' => 'generado',
                            'nombre' => 'Estudios previos completos',
                            'descripcion' => 'Estudios previos elaborados según Decreto 1082 de 2015',
                            'responsable' => 'prof_contratacion'
                        ],
                        'matriz_riesgos' => [
                            'tipo' => 'generado',
                            'nombre' => 'Matriz de identificación de riesgos',
                            'descripcion' => 'Identificación y evaluación de riesgos del proceso',
                            'responsable' => 'prof_contratacion'
                        ],
                        'analisis_sector' => [
                            'tipo' => 'generado',
                            'nombre' => 'Análisis del sector económico',
                            'descripcion' => 'Análisis del sector económico relacionado',
                            'responsable' => 'prof_contratacion'
                        ],
                        'especificaciones_tecnicas' => [
                            'tipo' => 'generado',
                            'nombre' => 'Especificaciones técnicas',
                            'descripcion' => 'Especificaciones técnicas detalladas del objeto contractual',
                            'responsable' => 'prof_contratacion'
                        ]
                    ],
                    'validaciones' => [
                        'estudios_completos' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar que estudios previos cumplan requisitos normativos',
                            'items' => [
                                'descripcion_necesidad_completa',
                                'objeto_contractual_definido',
                                'modalidad_justificada',
                                'valor_estimado_soportado',
                                'plazo_ejecucion_definido'
                            ]
                        ],
                        'riesgos_identificados' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Confirmar identificación completa de riesgos',
                            'responsable' => 'revisor_tecnico'
                        ],
                        'especificaciones_claras' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Validar claridad de especificaciones técnicas',
                            'responsable' => 'revisor_tecnico'
                        ]
                    ],
                    'siguiente_etapa' => 2
                ],

                // ETAPA 2: VALIDACIÓN DEL CONTRATISTA
                [
                    'orden' => 2,
                    'nombre' => 'Validación del Contratista',
                    'descripcion' => 'Verificación completa de idoneidad, capacidad y requisitos del contratista propuesto.',
                    'area_role' => 'unidad_solicitante',
                    'responsable_principal' => 'Coordinador de Contratación',
                    'tiempo_estimado' => '3 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'hoja_vida_sigep' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Hoja de vida SIGEP actualizada',
                            'descripcion' => 'Hoja de vida actualizada en el sistema SIGEP',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'cedula_ciudadania' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Cédula de ciudadanía',
                            'descripcion' => 'Cédula de ciudadanía ampliada al 150%',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'rut_actualizado' => [
                            'tipo' => 'requerido',
                            'nombre' => 'RUT actualizado',
                            'descripcion' => 'Registro Único Tributario vigente',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'certificacion_experiencia' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Certificaciones de experiencia',
                            'descripcion' => 'Certificaciones que acrediten experiencia específica',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'diplomas_acreditacion' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Diplomas y acreditaciones',
                            'descripcion' => 'Títulos académicos y acreditaciones profesionales',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'antecedentes' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Certificado de antecedentes',
                            'descripcion' => 'Antecedentes judiciales y policiales vigentes',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ]
                    ],
                    'documentos_generados' => [
                        'evaluacion_idoneidad' => [
                            'tipo' => 'generado',
                            'nombre' => 'Evaluación de idoneidad',
                            'descripcion' => 'Evaluación técnica de idoneidad del contratista',
                            'responsable' => 'coord_contratacion'
                        ],
                        'verificacion_inhabilidades' => [
                            'tipo' => 'generado',
                            'nombre' => 'Verificación de inhabilidades',
                            'descripcion' => 'Consulta y verificación en sistema de inhabilidades',
                            'responsable' => 'coord_contratacion'
                        ]
                    ],
                    'validaciones' => [
                        'sigep_actualizado' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar que SIGEP esté actualizado',
                            'endpoint' => '/api/sigep/validar',
                            'obligatorio' => true
                        ],
                        'experiencia_verificada' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Validar experiencia específica requerida',
                            'responsable' => 'coord_contratacion'
                        ],
                        'titulacion_correcta' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar títulos académicos requeridos',
                            'items' => ['titulo_pregrado', 'especializaciones', 'certificaciones']
                        ],
                        'antecedentes_limpios' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar ausencia de inhabilidades',
                            'endpoint' => '/api/inhabilidades/consultar',
                            'obligatorio' => true
                        ]
                    ],
                    'siguiente_etapa' => 3
                ],

                // ETAPA 3: REVISIÓN PRESUPUESTAL
                [
                    'orden' => 3,
                    'nombre' => 'Revisión y Aprobación Presupuestal',
                    'descripcion' => 'Verificación presupuestal completa y expedición de Certificado de Disponibilidad Presupuestal (CDP).',
                    'area_role' => 'hacienda',
                    'responsable_principal' => 'Revisor Presupuestal',
                    'colaborador' => 'Secretario de Hacienda',
                    'tiempo_estimado' => '2 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'solicitud_cdp' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Solicitud de CDP',
                            'descripcion' => 'Solicitud formal de Certificado de Disponibilidad Presupuestal',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf, doc, docx'
                        ],
                        'formato_presupuestal' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Formato presupuestal',
                            'descripcion' => 'Formato estándar de solicitud presupuestal diligenciado',
                            'obligatorio' => true,
                            'formato_permitido' => 'xlsx, xls'
                        ],
                        'justificacion_valor' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Justificación del valor',
                            'descripcion' => 'Documento técnico que justifica el valor estimado del contrato',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ]
                    ],
                    'documentos_generados' => [
                        'cdp_expedido' => [
                            'tipo' => 'generado',
                            'nombre' => 'Certificado de Disponibilidad Presupuestal',
                            'descripcion' => 'CDP expedido por la Secretaría de Hacienda',
                            'responsable' => 'revisor_presupuestal',
                            'numero_consecutivo' => true
                        ],
                        'concepto_presupuestal' => [
                            'tipo' => 'generado',
                            'nombre' => 'Concepto técnico presupuestal',
                            'descripcion' => 'Concepto técnico sobre viabilidad presupuestal',
                            'responsable' => 'revisor_presupuestal'
                        ]
                    ],
                    'validaciones' => [
                        'disponibilidad_confirmada' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar disponibilidad presupuestal en sistema',
                            'endpoint' => '/api/presupuesto/disponibilidad',
                            'obligatorio' => true
                        ],
                        'valor_justificado' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Validar justificación técnica del valor estimado',
                            'responsable' => 'revisor_presupuestal'
                        ],
                        'rubro_correcto' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar imputación al rubro presupuestal correcto',
                            'items' => ['codigo_rubro', 'disponibilidad_rubro', 'vigencia_fiscal']
                        ]
                    ],
                    'siguiente_etapa' => 4
                ],

                // ETAPA 4: CONSOLIDACIÓN EXPEDIENTE PRECONTRACTUAL
                [
                    'orden' => 4,
                    'nombre' => 'Consolidación Expediente Precontractual',
                    'descripcion' => 'Integración y organización completa de todos los documentos precontractuales en expediente único.',
                    'area_role' => 'unidad_solicitante',
                    'responsable_principal' => 'Profesional de Contratación',
                    'supervisado_por' => 'Coordinador de Contratación',
                    'tiempo_estimado' => '4 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'estudios_previos_aprobados' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Estudios previos aprobados',
                            'descripcion' => 'Estudios previos completos de la etapa 1',
                            'obligatorio' => true,
                            'origen_etapa' => 1
                        ],
                        'validacion_contratista' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Validación del contratista',
                            'descripcion' => 'Documentos de validación del contratista de la etapa 2',
                            'obligatorio' => true,
                            'origen_etapa' => 2
                        ],
                        'cdp_vigente' => [
                            'tipo' => 'requerido',
                            'nombre' => 'CDP vigente',
                            'descripcion' => 'Certificado de Disponibilidad Presupuestal vigente',
                            'obligatorio' => true,
                            'origen_etapa' => 3
                        ]
                    ],
                    'documentos_generados' => [
                        'minuta_contrato' => [
                            'tipo' => 'generado',
                            'nombre' => 'Minuta de contrato',
                            'descripcion' => 'Minuta de contrato inicial elaborada',
                            'responsable' => 'prof_contratacion',
                            'template' => 'minuta_cdpn_template'
                        ],
                        'cronograma_actividades' => [
                            'tipo' => 'generado',
                            'nombre' => 'Cronograma de actividades',
                            'descripcion' => 'Cronograma detallado de ejecución contractual',
                            'responsable' => 'prof_contratacion'
                        ],
                        'forma_pago' => [
                            'tipo' => 'generado',
                            'nombre' => 'Definición forma de pago',
                            'descripcion' => 'Documento con forma de pago y requisitos',
                            'responsable' => 'prof_contratacion'
                        ],
                        'polizas_seguros' => [
                            'tipo' => 'generado',
                            'nombre' => 'Especificación pólizas y seguros',
                            'descripcion' => 'Especificación de pólizas y seguros requeridos',
                            'responsable' => 'prof_contratacion'
                        ],
                        'expediente_consolidado' => [
                            'tipo' => 'generado',
                            'nombre' => 'Expediente precontractual consolidado',
                            'descripcion' => 'Expediente precontractual completo y organizado',
                            'responsable' => 'coord_contratacion'
                        ]
                    ],
                    'validaciones' => [
                        'minuta_elaborada' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar minuta de contrato completa',
                            'items' => [
                                'clausulas_obligatorias',
                                'objeto_definido',
                                'valor_plazo_especificado',
                                'obligaciones_partes',
                                'garantias_especificadas'
                            ]
                        ],
                        'cronograma_realista' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Validar factibilidad del cronograma propuesto',
                            'responsable' => 'coord_contratacion'
                        ],
                        'forma_pago_definida' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Confirmar forma de pago claramente establecida',
                            'responsable' => 'coord_contratacion'
                        ],
                        'garantias_especificadas' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar especificación correcta de garantías',
                            'items' => ['poliza_cumplimiento', 'poliza_responsabilidad_civil', 'seguros_obligatorios']
                        ]
                    ],
                    'siguiente_etapa' => 5
                ],

                // ETAPA 5: REVISIÓN JURÍDICA
                [
                    'orden' => 5,
                    'nombre' => 'Revisión Jurídica y Ajuste a Derecho',
                    'descripcion' => 'Revisión jurídica integral del expediente precontractual y emisión de concepto de viabilidad jurídica.',
                    'area_role' => 'juridica',
                    'responsable_principal' => 'Revisor Jurídico',
                    'supervisado_por' => 'Secretario Jurídico',
                    'tiempo_estimado' => '3 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'expediente_completo' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Expediente precontractual completo',
                            'descripcion' => 'Expediente consolidado de la etapa anterior',
                            'obligatorio' => true,
                            'origen_etapa' => 4
                        ],
                        'solicitud_revision' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Solicitud de revisión jurídica',
                            'descripcion' => 'Solicitud formal de revisión jurídica',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ]
                    ],
                    'documentos_generados' => [
                        'concepto_juridico' => [
                            'tipo' => 'generado',
                            'nombre' => 'Concepto jurídico de viabilidad',
                            'descripcion' => 'Concepto jurídico sobre viabilidad del proceso',
                            'responsable' => 'revisor_juridico',
                            'numero_consecutivo' => true
                        ],
                        'observaciones_juridicas' => [
                            'tipo' => 'generado',
                            'nombre' => 'Observaciones y recomendaciones jurídicas',
                            'descripcion' => 'Observaciones jurídicas y recomendaciones de mejora',
                            'responsable' => 'revisor_juridico'
                        ],
                        'visto_bueno_juridico' => [
                            'tipo' => 'generado',
                            'nombre' => 'Visto bueno jurídico final',
                            'descripcion' => 'Visto bueno jurídico para continuar el proceso',
                            'responsable' => 'revisor_juridico',
                            'condicional' => true
                        ],
                        'minuta_ajustada' => [
                            'tipo' => 'generado',
                            'nombre' => 'Minuta ajustada jurídicamente',
                            'descripcion' => 'Minuta de contrato con ajustes jurídicos',
                            'responsable' => 'revisor_juridico'
                        ]
                    ],
                    'validaciones' => [
                        'normativa_cumplida' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar cumplimiento de normativa de contratación',
                            'items' => [
                                'ley_1150_2007',
                                'decreto_1082_2015',
                                'ley_80_1993',
                                'circular_colombia_compra',
                                'manual_contratacion'
                            ]
                        ],
                        'documentos_conformes' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Confirmar conformidad jurídica de todos los documentos',
                            'responsable' => 'revisor_juridico'
                        ],
                        'clausulas_apropiadas' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Validar apropiada redacción de cláusulas contractuales',
                            'items' => [
                                'clausula_objeto',
                                'clausula_valor_forma_pago',
                                'clausula_plazo',
                                'clausula_obligaciones',
                                'clausula_garantias',
                                'clausula_supervision',
                                'clausula_multas_sanciones'
                            ]
                        ],
                        'riesgos_juridicos_identificados' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Identificar y evaluar riesgos jurídicos',
                            'responsable' => 'revisor_juridico'
                        ]
                    ],
                    'siguiente_etapa' => 6
                ],

                // ETAPA 6: PUBLICACIÓN SECOP II
                [
                    'orden' => 6,
                    'nombre' => 'Publicación y Gestión en SECOP II',
                    'descripcion' => 'Publicación del proceso de contratación en SECOP II y gestión de invitación directa al contratista.',
                    'area_role' => 'secop',
                    'responsable_principal' => 'Operador SECOP',
                    'colaborador' => 'Coordinador de Contratación',
                    'tiempo_estimado' => '1 día hábil',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'expediente_aprobado' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Expediente con visto bueno jurídico',
                            'descripcion' => 'Expediente aprobado jurídicamente en etapa anterior',
                            'obligatorio' => true,
                            'origen_etapa' => 5
                        ],
                        'documentos_secop' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Documentos en formato SECOP II',
                            'descripcion' => 'Documentos convertidos al formato requerido por SECOP II',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ]
                    ],
                    'documentos_generados' => [
                        'numero_proceso' => [
                            'tipo' => 'generado',
                            'nombre' => 'Número de proceso SECOP II',
                            'descripcion' => 'Número único asignado por SECOP II al proceso',
                            'responsable' => 'secop_operator',
                            'automatico' => true
                        ],
                        'publicacion_confirmada' => [
                            'tipo' => 'generado',
                            'nombre' => 'Confirmación de publicación',
                            'descripcion' => 'Certificación de publicación exitosa en SECOP II',
                            'responsable' => 'secop_operator'
                        ],
                        'invitacion_contratista' => [
                            'tipo' => 'generado',
                            'nombre' => 'Invitación directa al contratista',
                            'descripcion' => 'Invitación formal enviada al contratista a través de SECOP II',
                            'responsable' => 'secop_operator'
                        ],
                        'cronograma_secop' => [
                            'tipo' => 'generado',
                            'nombre' => 'Cronograma publicado en SECOP II',
                            'descripcion' => 'Cronograma del proceso publicado en la plataforma',
                            'responsable' => 'secop_operator'
                        ]
                    ],
                    'validaciones' => [
                        'publicacion_exitosa' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar publicación exitosa en SECOP II',
                            'endpoint' => '/api/secop/validar-publicacion',
                            'obligatorio' => true
                        ],
                        'documentos_publicos' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Confirmar que documentos están disponibles públicamente',
                            'endpoint' => '/api/secop/verificar-documentos',
                            'obligatorio' => true
                        ],
                        'invitacion_enviada' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Confirmar envío de invitación al contratista',
                            'endpoint' => '/api/secop/estado-invitacion',
                            'obligatorio' => true
                        ],
                        'transparencia_cumplida' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar cumplimiento principios transparencia',
                            'items' => [
                                'documentos_completos_publicados',
                                'cronograma_visible',
                                'contacto_entidad_disponible',
                                'proceso_busqueda_activo'
                            ]
                        ]
                    ],
                    'siguiente_etapa' => 7
                ],

                // ETAPA 7: SOLICITUD DE RPC
                [
                    'orden' => 7,
                    'nombre' => 'Solicitud y Expedición de RPC',
                    'descripcion' => 'Solicitud y expedición del Registro Presupuestal del Compromiso (RPC) por parte de Hacienda.',
                    'area_role' => 'planeacion',
                    'responsable_principal' => 'Secretario de Planeación',
                    'colaborador' => 'Revisor Presupuestal (Hacienda)',
                    'tiempo_estimado' => '2 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'proceso_publicado_secop' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Proceso publicado en SECOP II',
                            'descripcion' => 'Confirmación de proceso publicado en SECOP II',
                            'obligatorio' => true,
                            'origen_etapa' => 6
                        ],
                        'solicitud_rpc' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Solicitud formal de RPC',
                            'descripcion' => 'Solicitud formal de Registro Presupuestal del Compromiso',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'contrato_proyecto' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Proyecto de contrato',
                            'descripcion' => 'Proyecto definitivo del contrato a suscribir',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'soportes_contractuales' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Soportes contractuales completos',
                            'descripcion' => 'Todos los soportes del proceso contractual',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ]
                    ],
                    'documentos_generados' => [
                        'rpc_expedido' => [
                            'tipo' => 'generado',
                            'nombre' => 'Registro Presupuestal del Compromiso',
                            'descripcion' => 'RPC expedido por la Secretaría de Hacienda',
                            'responsable' => 'revisor_presupuestal',
                            'numero_consecutivo' => true
                        ],
                        'certificacion_rpc' => [
                            'tipo' => 'generado',
                            'nombre' => 'Certificación de expedición RPC',
                            'descripcion' => 'Certificación formal de expedición del RPC',
                            'responsable' => 'revisor_presupuestal'
                        ],
                        'compromiso_presupuestal' => [
                            'tipo' => 'generado',
                            'nombre' => 'Registro del compromiso presupuestal',
                            'descripcion' => 'Registro contable del compromiso presupuestal',
                            'responsable' => 'revisor_presupuestal'
                        ]
                    ],
                    'validaciones' => [
                        'rpc_disponible' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar disponibilidad para expedición de RPC',
                            'endpoint' => '/api/presupuesto/rpc-disponible',
                            'obligatorio' => true
                        ],
                        'compromiso_registrado' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Confirmar registro del compromiso presupuestal',
                            'endpoint' => '/api/presupuesto/compromiso-registrado',
                            'obligatorio' => true
                        ],
                        'vigencia_confirmada' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar vigencia presupuestal apropiada',
                            'items' => [
                                'vigencia_actual',
                                'vigencias_futuras_si_aplica',
                                'disponibilidad_confirmada',
                                'rubro_apropiado'
                            ]
                        ],
                        'documentos_soporte_completos' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Confirmar completitud de documentos soporte',
                            'responsable' => 'revisor_presupuestal'
                        ]
                    ],
                    'siguiente_etapa' => 8
                ],

                // ETAPA 8: SUSCRIPCIÓN DEL CONTRATO
                [
                    'orden' => 8,
                    'nombre' => 'Suscripción y Perfeccionamiento del Contrato',
                    'descripcion' => 'Firma del contrato por ambas partes, asignación de número contractual y perfeccionamiento jurídico.',
                    'area_role' => 'juridica',
                    'responsable_principal' => 'Secretario Jurídico',
                    'colaborador' => 'Revisor Jurídico',
                    'tiempo_estimado' => '2 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'rpc_expedido' => [
                            'tipo' => 'requerido',
                            'nombre' => 'RPC expedido y vigente',
                            'descripcion' => 'Registro Presupuestal del Compromiso vigente',
                            'obligatorio' => true,
                            'origen_etapa' => 7
                        ],
                        'garantias_contratista' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Garantías aportadas por el contratista',
                            'descripcion' => 'Pólizas y garantías aportadas por el contratista',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'documentos_legalizacion' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Documentos para legalización',
                            'descripcion' => 'Documentos adicionales requeridos para legalización',
                            'obligatorio' => false,
                            'formato_permitido' => 'pdf'
                        ]
                    ],
                    'documentos_generados' => [
                        'contrato_suscrito' => [
                            'tipo' => 'generado',
                            'nombre' => 'Contrato debidamente suscrito',
                            'descripcion' => 'Contrato firmado por ambas partes',
                            'responsable' => 'revisor_juridico'
                        ],
                        'numero_contractual' => [
                            'tipo' => 'generado',
                            'nombre' => 'Asignación de número de contrato',
                            'descripcion' => 'Número único asignado al contrato',
                            'responsable' => 'revisor_juridico',
                            'numero_consecutivo' => true
                        ],
                        'registro_contractual' => [
                            'tipo' => 'generado',
                            'nombre' => 'Registro en sistema contractual',
                            'descripcion' => 'Registro del contrato en el sistema de gestión',
                            'responsable' => 'revisor_juridico',
                            'automatico' => true
                        ],
                        'acta_perfeccionamiento' => [
                            'tipo' => 'generado',
                            'nombre' => 'Acta de perfeccionamiento',
                            'descripcion' => 'Acta que certifica perfeccionamiento del contrato',
                            'responsable' => 'revisor_juridico'
                        ]
                    ],
                    'validaciones' => [
                        'contrato_firmado' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar firma correcta por ambas partes',
                            'items' => [
                                'firma_contratante',
                                'firma_contratista',
                                'testigos_si_aplica',
                                'fechas_correctas'
                            ]
                        ],
                        'garantias_aprobadas' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Confirmar aprobación de garantías por la entidad',
                            'responsable' => 'revisor_juridico'
                        ],
                        'numero_asignado' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar asignación correcta de número de contrato',
                            'endpoint' => '/api/contratos/verificar-numero',
                            'obligatorio' => true
                        ],
                        'perfeccionamiento_valido' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Confirmar perfeccionamiento jurídico válido',
                            'items' => [
                                'capacidad_juridica_partes',
                                'objeto_licito',
                                'causa_licita',
                                'consentimiento_valido'
                            ]
                        ]
                    ],
                    'siguiente_etapa' => 9
                ],

                // ETAPA 9: INICIO DE EJECUCIÓN
                [
                    'orden' => 9,
                    'nombre' => 'ARL, Acta de Inicio y Activación SECOP II',
                    'descripcion' => 'Formalización del inicio de ejecución contractual: afiliación ARL, acta de inicio y activación en SECOP II.',
                    'area_role' => 'unidad_solicitante',
                    'responsable_principal' => 'Coordinador de Contratación',
                    'colaborador' => 'Jefe de Unidad',
                    'tiempo_estimado' => '3 días hábiles',
                    'es_paralelo' => false,
                    'documentos_requeridos' => [
                        'contrato_perfeccionado' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Contrato perfeccionado',
                            'descripcion' => 'Contrato perfeccionado de la etapa anterior',
                            'obligatorio' => true,
                            'origen_etapa' => 8
                        ],
                        'afiliacion_arl' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Certificación afiliación ARL contratista',
                            'descripcion' => 'Certificado vigente de afiliación a ARL del contratista',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf'
                        ],
                        'cronograma_definitivo' => [
                            'tipo' => 'requerido',
                            'nombre' => 'Cronograma definitivo de ejecución',
                            'descripcion' => 'Cronograma detallado y definitivo de ejecución contractual',
                            'obligatorio' => true,
                            'formato_permitido' => 'pdf, xlsx'
                        ]
                    ],
                    'documentos_generados' => [
                        'acta_inicio' => [
                            'tipo' => 'generado',
                            'nombre' => 'Acta de inicio de ejecución',
                            'descripcion' => 'Acta formal de inicio de ejecución del contrato',
                            'responsable' => 'coord_contratacion'
                        ],
                        'designacion_supervisor' => [
                            'tipo' => 'generado',
                            'nombre' => 'Designación de supervisor de contrato',
                            'descripcion' => 'Acto administrativo de designación de supervisor',
                            'responsable' => 'jefe_unidad'
                        ],
                        'inicio_secop_confirmado' => [
                            'tipo' => 'generado',
                            'nombre' => 'Inicio registrado en SECOP II',
                            'descripcion' => 'Confirmación de registro de inicio en SECOP II',
                            'responsable' => 'secop_operator'
                        ],
                        'contrato_legalizado' => [
                            'tipo' => 'generado',
                            'nombre' => 'Contrato perfeccionado y legalizado',
                            'descripcion' => 'Contrato completamente legalizado y listo para ejecución',
                            'responsable' => 'coord_contratacion'
                        ]
                    ],
                    'validaciones' => [
                        'arl_vigente' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar vigencia de afiliación ARL',
                            'endpoint' => '/api/arl/verificar-vigencia',
                            'obligatorio' => true
                        ],
                        'acta_firmada' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Confirmar acta de inicio debidamente firmada',
                            'items' => [
                                'firma_supervisor',
                                'firma_contratista',
                                'fecha_inicio_clara',
                                'cronograma_acordado'
                            ]
                        ],
                        'secop_actualizado' => [
                            'tipo' => 'automatica',
                            'descripcion' => 'Verificar actualización correcta en SECOP II',
                            'endpoint' => '/api/secop/verificar-inicio',
                            'obligatorio' => true
                        ],
                        'supervision_designada' => [
                            'tipo' => 'manual',
                            'descripcion' => 'Confirmar designación oficial de supervisor',
                            'responsable' => 'jefe_unidad'
                        ],
                        'ejecucion_lista' => [
                            'tipo' => 'checklist',
                            'descripcion' => 'Verificar que todo está listo para ejecución',
                            'items' => [
                                'contrato_perfeccionado',
                                'supervisor_designado',
                                'cronograma_aprobado',
                                'arl_vigente',
                                'secop_actualizado'
                            ]
                        ]
                    ],
                    'siguiente_etapa' => 'completed'
                ]
            ]
        ];

        // Insertar el workflow optimizado
        $this->insertWorkflow($flujoCDPNOptimizado);

        $this->command->info('');
        $this->command->info('✅ Flujo CD-PN optimizado creado exitosamente!');
        $this->command->info('📊 Características del flujo optimizado:');
        $this->command->info('   🎯 10 etapas detalladas (0-9)');
        $this->command->info('   📋 Documentos específicos por etapa');
        $this->command->info('   ⚡ Validaciones automáticas configurables');
        $this->command->info('   ⏱️ Tiempos estimados realistas');
        $this->command->info('   👥 Responsabilidades claras por etapa');
        $this->command->info('   🔄 Diferenciación documentos requeridos vs generados');
    }

    /**
     * Insertar workflow en la base de datos
     */
    private function insertWorkflow(array $workflowData): void
    {
        // Insertar workflow principal (solo columnas que existen)
        $workflowId = DB::table('workflows')->insertGetId([
            'codigo' => $workflowData['codigo'],
            'nombre' => $workflowData['nombre'],
            'activo' => $workflowData['activo'] ?? true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insertar etapas
        foreach ($workflowData['etapas'] as $etapa) {
            $etapaId = DB::table('etapas')->insertGetId([
                'workflow_id' => $workflowId,
                'orden' => $etapa['orden'],
                'nombre' => $etapa['nombre'],
                'area_role' => $etapa['area_role'],
                'descripcion' => $etapa['descripcion'] ?? '',
                'responsable_unidad' => $etapa['responsable_principal'] ?? null,
                'responsable_secretaria' => $etapa['colaborador'] ?? null,
                'es_paralelo' => $etapa['es_paralelo'] ?? false,
                'notas' => 'Tiempo estimado: ' . ($etapa['tiempo_estimado'] ?? 'No especificado'),
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info("   ✅ Workflow '{$workflowData['nombre']}' insertado con ID: {$workflowId}");
    }

    /**
     * Truncar tabla si existe
     */
    private function truncateIfExists(string $table): void
    {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            DB::table($table)->truncate();
        }
    }
}
