<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * WorkflowSeeder – Flujo de Contratación Directa Persona Natural
 * ================================================================
 *
 * FUENTE: Proceso oficial de la Gobernación de Caldas – CD-PN
 *         Documentado por el equipo de Planeación (Feb 2026).
 *
 * FLUJO CORRECTO CD-PN: 10 ETAPAS (0 – 9)
 * ─────────────────────────────────────────────────────────────
 *   Etapa 0  │ unidad_solicitante │ Definición de la Necesidad
 *   Etapa 1  │ planeacion         │ Solicitud de Documentos Iniciales
 *   Etapa 2  │ unidad_solicitante │ Validación del Contratista
 *   Etapa 3  │ unidad_solicitante │ Elaboración de Documentos Contractuales
 *   Etapa 4  │ unidad_solicitante │ Consolidación del Expediente Precontractual
 *   Etapa 5  │ juridica           │ Radicación en Sec. Jurídica + Ajustado a Derecho
 *   Etapa 6  │ secop              │ Publicación y Firma en SECOP II
 *   Etapa 7  │ planeacion         │ Solicitud de RPC (Hacienda expide)
 *   Etapa 8  │ juridica           │ Radicación Final y Número de Contrato
 *   Etapa 9  │ unidad_solicitante │ ARL, Acta de Inicio e Inicio en SECOP II
 * ─────────────────────────────────────────────────────────────
 *
 * ACTORES:
 *   sistemas@demo.com   → Etapas 0, 2, 3, 4, 9  (unidad_solicitante)
 *   planeacion@demo.com → Etapas 1, 7             (planeacion)
 *   juridica@demo.com   → Etapas 5, 8             (juridica)
 *   secop@demo.com      → Etapa 6                 (secop)
 *   hacienda participa  → sub-items de Etapa 7    (expedición RPC)
 */
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

            /*
            |==================================================================
            | CONTRATACIÓN DIRECTA – PERSONA NATURAL (CD_PN)
            |==================================================================
            | Flujo completo basado en el proceso real de la Gobernación.
            | 16 etapas secuenciales con ítems detallados por responsable.
            |==================================================================
            */
            [
                'codigo' => 'CD_PN',
                'nombre' => 'Contratación Directa - Persona Natural',
                'activo' => true,
                'requiere_viabilidad_economica_inicial' => false,
                'requiere_estudios_previos_completos'   => true,
                'observaciones' => 'Flujo CD-PN oficial de la Gobernación de Caldas. 10 etapas (0-9). '
                    . 'sistemas@demo.com gestiona Etapas 0,2,3,4,9 | planeacion@demo.com gestiona Etapas 1,7 | '
                    . 'juridica@demo.com gestiona Etapas 5,8 | secop@demo.com gestiona Etapa 6.',
                'etapas' => [

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 0 – Definición de la Necesidad
                    // Responsable: sistemas@demo.com (unidad_solicitante)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 0,
                        'nombre'      => '0: Definición de la Necesidad',
                        'descripcion' => 'La Unidad solicitante identifica la necesidad contractual. Se elaboran los Estudios Previos definiendo objeto, valor estimado y plazo. El Jefe de Unidad envía el estudio previo a la Unidad de Descentralización – Secretaría de Planeación.',
                        'area_role'   => 'unidad_solicitante',
                        'responsable_unidad'      => 'Unidad Solicitante (Jefe de Unidad)',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => false,
                        'notas'       => 'El estudio previo es el insumo principal. Debe definir: objeto contractual, valor estimado, plazo de ejecución y perfil requerido del contratista.',
                        'items' => [
                            [
                                'label'                   => 'Estudios Previos elaborados (objeto, valor y plazo definidos)',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Jefe de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'Insumo principal. Firmado por el jefe de la unidad.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Remisión del estudio previo a la Unidad de Descentralización',
                                'tipo_documento'          => 'solicitud',
                                'responsable_unidad'      => 'Jefe de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'Comunicación oficial a la Unidad de Descentralización para iniciar la solicitud de documentos.',
                                'requerido'               => true,
                            ],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 1 – Solicitud de Documentos Iniciales
                    // Responsable: planeacion@demo.com (planeacion)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 1,
                        'nombre'      => '1: Solicitud de Documentos Iniciales',
                        'descripcion' => 'La Unidad de Descentralización (Sec. Planeación) coordina la solicitud de documentos a múltiples dependencias. Pueden solicitarse simultáneamente EXCEPTO el CDP, que requiere primero la Compatibilidad del Gasto. Para paz y salvos se requiere nombre y cédula del contratista.',
                        'area_role'   => 'planeacion',
                        'responsable_unidad'      => 'Unidad de Descentralización',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => true,
                        'notas'       => '⚠️ Todos los documentos pueden solicitarse en paralelo EXCEPTO el CDP, que REQUIERE la Compatibilidad del Gasto como prerrequisito. Para Paz y Salvos se necesita nombre y cédula del contratista.',
                        'items' => [
                            [
                                'label'                   => 'Plan Anual de Adquisiciones (PAA) – Unidad de Compras',
                                'tipo_documento'          => 'certificado',
                                'responsable_unidad'      => 'Unidad de Compras y Suministros',
                                'responsable_secretaria'  => 'Secretaría General',
                                'notas'                   => 'La Unidad de Compras realiza el cargue en SECOP II para que se vea reflejado al estructurar el proceso.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Certificado No Planta – Talento Humano',
                                'tipo_documento'          => 'certificado',
                                'responsable_unidad'      => 'Jefatura de Gestión del Talento Humano',
                                'responsable_secretaria'  => 'Secretaría General',
                                'notas'                   => 'Certifica que no hay personal de planta disponible para el servicio requerido.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Paz y Salvo de Rentas (o acuerdo de pago)',
                                'tipo_documento'          => 'certificado',
                                'responsable_unidad'      => 'Unidad de Rentas',
                                'responsable_secretaria'  => 'Secretaría de Hacienda',
                                'notas'                   => 'Requiere nombre completo y documento de identificación del contratista.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Paz y Salvo de Contabilidad (o acuerdo de pago)',
                                'tipo_documento'          => 'certificado',
                                'responsable_unidad'      => 'Unidad de Contabilidad',
                                'responsable_secretaria'  => 'Secretaría de Hacienda',
                                'notas'                   => 'Requiere nombre completo y documento de identificación del contratista.',
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
                    // ETAPA 2 – Validación del Contratista
                    // Responsable: sistemas@demo.com (unidad_solicitante)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 2,
                        'nombre'      => '2: Validación del Contratista',
                        'descripcion' => 'El contratista entrega documentos personales y legales. La Secretaría Jurídica valida la hoja de vida SIGEP. El abogado de la Unidad verifica el checklist completo. Es responsabilidad del contratista enviar los documentos correctos.',
                        'area_role'   => 'unidad_solicitante',
                        'responsable_unidad'      => 'Unidad Solicitante (Abogado adscrito)',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => true,
                        'notas'       => 'IMPORTANTE: Antes de la radicación en Jurídica, la Secretaría Jurídica procede a la validación de la hoja de vida SIGEP para verificar estudios y experiencia del contratista. Honorarios según tabla de la Gobernación.',
                        'items' => [
                            [
                                'label' => 'Hoja de Vida SIGEP (cargada y actualizada en plataforma SIGEP II)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'El contratista debe registrar y actualizar su HV en SIGEP II. Insumo indispensable para la validación jurídica.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Hoja de Vida SIGEP validada por Secretaría Jurídica',
                                'tipo_documento' => 'certificado', 'responsable_unidad' => 'Abogado Enlace',
                                'responsable_secretaria' => 'Secretaría Jurídica',
                                'notas' => 'Jurídica verifica que la HV cuente con los estudios y experiencia requeridos.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificados de estudio (diplomas / actas de grado pregrado y posgrado)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Según la necesidad de la entidad. Copia legible.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificados de experiencia laboral que cumplan el perfil requerido',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => null, 'requerido' => true,
                            ],
                            [
                                'label' => 'Copia legible de la cédula de ciudadanía',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => null, 'requerido' => true,
                            ],
                            [
                                'label' => 'Copia legible del RUT (actualizado en datos y actividad económica)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Debe estar actualizado.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Declaración de bienes y rentas SIGEP II (descargado de plataforma)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Descargado directamente de la plataforma SIGEP II.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Declaración de bienes y renta y conflicto de intereses (Ley 2013)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'https://www1.funcionpublica.gov.co/web/sigep2/ley-2013', 'requerido' => true,
                            ],
                            [
                                'label' => 'Aceptación de la oferta para la celebración del contrato (Formato Almera)',
                                'tipo_documento' => 'formato', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'La firma es del contratista.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificado de cuenta bancaria actualizada',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días calendario.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificado de ausencia de antecedentes disciplinarios',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificado de ausencia de antecedentes fiscales',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificado de ausencia de antecedentes judiciales',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificado de ausencia de medidas correctivas',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => null, 'requerido' => true,
                            ],
                            [
                                'label' => 'Antecedentes de delitos sexuales',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días.', 'requerido' => true,
                            ],
                            [
                                'label' => 'REDAM – Registro de Deudores Alimentarios Morosos',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Vigencia según lo señalado en el certificado.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificado de inhabilidades e incompatibilidades',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => null, 'requerido' => true,
                            ],
                            [
                                'label' => 'Constancia de afiliación Seguridad Social – Salud',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Del mes inmediatamente anterior.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Constancia de afiliación Seguridad Social – Pensión',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Del mes inmediatamente anterior.', 'requerido' => true,
                            ],
                            [
                                'label' => 'Certificado de examen médico de aptitud laboral vigente',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => null, 'requerido' => true,
                            ],
                            [
                                'label' => 'Copia legible de matrícula o tarjeta profesional (si aplica)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Cuando el ejercicio así lo implique.', 'requerido' => false,
                            ],
                            [
                                'label' => 'Constancia de ausencia de sanciones disciplinarias profesionales (si aplica)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días. Solo para servicios profesionales que así lo impliquen.', 'requerido' => false,
                            ],
                            [
                                'label' => 'Acreditación de la situación militar (si aplica)',
                                'tipo_documento' => 'documento_contratista', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null, 'notas' => 'Inferior a 30 días. Aplica según normativa.', 'requerido' => false,
                            ],
                            [
                                'label' => 'Abogado verifica checklist completo de documentos del contratista',
                                'tipo_documento' => 'checklist', 'responsable_unidad' => 'Abogado de Unidad Solicitante',
                                'responsable_secretaria' => 'Secretaría de Planeación',
                                'notas' => 'Verificación interna: documentos completos, vigentes y correctos antes de elaborar documentos contractuales.', 'requerido' => true,
                            ],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 3 – Elaboración de Documentos Contractuales
                    // Responsable: sistemas@demo.com (unidad_solicitante)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 3,
                        'nombre'      => '3: Elaboración de Documentos Contractuales',
                        'descripcion' => 'El abogado adscrito a la unidad solicitante proyecta los documentos oficiales del proceso. Estos llevan firma del ordenador del gasto y el supervisor, excepto el Análisis del Sector (solo supervisor) y la Aceptación de la Oferta (contratista). Honorarios según tabla de la Gobernación.',
                        'area_role'   => 'unidad_solicitante',
                        'responsable_unidad'      => 'Unidad Solicitante (Abogado adscrito)',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => false,
                        'notas'       => 'Firmas: Ordenador del Gasto + Supervisor en la mayoría. Solo Supervisor en Análisis del Sector. Solo Contratista en Aceptación de Oferta.',
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
                                'label' => 'Aceptación de la Oferta firmada por el contratista (Formato Almera)',
                                'tipo_documento' => 'formato', 'responsable_unidad' => 'Contratista',
                                'responsable_secretaria' => null,
                                'notas' => 'La firma es exclusivamente del CONTRATISTA.', 'requerido' => true,
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
                                'notas' => null, 'requerido' => false,
                            ],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 4 – Consolidación del Expediente Precontractual
                    // Responsable: sistemas@demo.com (unidad_solicitante)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 4,
                        'nombre'      => '4: Consolidación del Expediente Precontractual',
                        'descripcion' => 'El abogado agrupa en una carpeta con el nombre del contratista TODOS los documentos precontractuales de la lista de chequeo. Verifica fechas ajustadas, firmas completas y requisitos exigidos antes de proceder a la radicación en Jurídica.',
                        'area_role'   => 'unidad_solicitante',
                        'responsable_unidad'      => 'Unidad Solicitante (Abogado adscrito)',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => false,
                        'notas'       => 'La carpeta lleva el nombre del contratista. Todos los documentos deben tener fechas vigentes y firmas completas.',
                        'items' => [
                            ['label' => 'PAA – Certificado de Plan Anual de Adquisiciones',        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Constancia de Compatibilidad del Gasto',                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Ficha BPIN (si aplica)',                                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Si aplica',    'requerido' => false],
                            ['label' => 'Análisis del Sector (incluido estudio del mercado)',       'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Invitación a Presentar Oferta (Formato Almera)',           'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Certificado No Planta – Talento Humano',                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Paz y Salvo de Contabilidad',                             'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Paz y Salvo de Rentas',                                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Aceptación de la Oferta (Formato Almera) firmada',        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Declaración de bienes y rentas SIGEP II',                 'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Declaración bienes, renta y conflicto de intereses',      'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Copia de cédula de ciudadanía',                           'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Copia del RUT actualizado',                               'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Certificado de cuenta bancaria actualizada',              'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                            ['label' => 'Antecedentes disciplinarios',                             'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                            ['label' => 'Antecedentes fiscales',                                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                            ['label' => 'Antecedentes judiciales',                                 'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                            ['label' => 'Ausencia de medidas correctivas',                         'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Antecedentes de delitos sexuales',                        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días',    'requerido' => true],
                            ['label' => 'REDAM',                                                   'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Inhabilidades e incompatibilidades',                      'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Afiliación Seguridad Social – Salud',                    'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Mes anterior',  'requerido' => true],
                            ['label' => 'Afiliación Seguridad Social – Pensión',                  'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Mes anterior',  'requerido' => true],
                            ['label' => 'Examen médico de aptitud laboral',                        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Matrícula o tarjeta profesional (si aplica)',             'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Si aplica',    'requerido' => false],
                            ['label' => 'Ausencia de sanciones disciplinarias profesionales',     'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días, si aplica', 'requerido' => false],
                            ['label' => 'Acreditación situación militar (si aplica)',              'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => '< 30 días, si aplica', 'requerido' => false],
                            ['label' => 'Hoja de Vida SIGEP validada por Secretaría Jurídica',    'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Certificados de estudio',                                 'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Certificados de experiencia laboral',                     'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Certificado de Idoneidad y Experiencia',                  'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'CDP – Certificado de Disponibilidad Presupuestal',        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Solicitud de Contratación y Designación de Supervisión', 'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Estudios Previos con Anexos (versión definitiva)',        'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => null,           'requerido' => true],
                            ['label' => 'Excepción regla fiscal (si aplica)',                      'tipo_documento' => 'checklist', 'responsable_unidad' => null, 'responsable_secretaria' => null, 'notas' => 'Si aplica',    'requerido' => false],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 5 – Radicación en Secretaría Jurídica + Ajustado a Derecho
                    // Responsable: juridica@demo.com (juridica)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 5,
                        'nombre'      => '5: Radicación, Revisión Jurídica y Ajustado a Derecho',
                        'descripcion' => 'Se diligencia el formulario en el SharePoint "Solicitud de Contratación" de la Secretaría Jurídica. Se genera número de proceso (CD-SP-XX-2026) y se cargan los documentos. El abogado enlace revisa la lista de chequeo: si hay errores devuelve; si cumple emite el Ajustado a Derecho. Se firman el ajustado y el contrato físico.',
                        'area_role'   => 'juridica',
                        'responsable_unidad'      => 'Abogado Enlace / Oficina de Radicación',
                        'responsable_secretaria'  => 'Secretaría Jurídica',
                        'es_paralelo' => false,
                        'notas'       => 'El SharePoint asigna automáticamente el número de proceso. Para Planeación: CD-SP-XX-2026. Si hay observaciones, el proceso se devuelve a la unidad solicitante.',
                        'items' => [
                            [
                                'label'                   => 'Solicitud de contratación diligenciada en SharePoint "Solicitud de Contratación"',
                                'tipo_documento'          => 'solicitud',
                                'responsable_unidad'      => 'Abogado de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'Datos: correo solicitante, secretaría, modalidad, NIT/cédula contratista, nombre contratista, tipo contrato, objeto contractual, valor.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Número de proceso asignado (CD-SP-XX-2026)',
                                'tipo_documento'          => 'solicitud',
                                'responsable_unidad'      => 'SharePoint automático',
                                'responsable_secretaria'  => 'Secretaría Jurídica',
                                'notas'                   => 'El SharePoint asigna el número y crea la carpeta para cargar los documentos.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Documentos precontractuales cargados en carpeta SharePoint',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'Se cargan en la carpeta creada automáticamente.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Revisión de la lista de chequeo por abogado enlace de Jurídica',
                                'tipo_documento'          => 'checklist',
                                'responsable_unidad'      => 'Abogado Enlace',
                                'responsable_secretaria'  => 'Secretaría Jurídica',
                                'notas'                   => 'Si hay observaciones se devuelve a la unidad solicitante. Si cumple, se procede a emitir el Ajustado a Derecho.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Ajustado a Derecho expedido y firmado por abogado enlace',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado Enlace',
                                'responsable_secretaria'  => 'Secretaría Jurídica',
                                'notas'                   => 'Firmado por el abogado enlace de la Secretaría Jurídica.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Contrato físico firmado por Secretario Privado (Ordenador del Gasto)',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Secretaría Privada',
                                'responsable_secretaria'  => 'Secretaría Privada',
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Contrato físico firmado por el contratista',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Contratista',
                                'responsable_secretaria'  => null,
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Contrato físico firmado por el abogado enlace de Jurídica',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado Enlace',
                                'responsable_secretaria'  => 'Secretaría Jurídica',
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 6 – Publicación y Firma en SECOP II
                    // Responsable: secop@demo.com (secop)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 6,
                        'nombre'      => '6: Publicación y Firma en SECOP II',
                        'descripcion' => 'El apoyo de estructuración (designado desde Secretaría de Planeación) carga el contrato en SECOP II. Flujos de aprobación: creación aprobada por abogado enlace; contrato firmado primero por el contratista y luego por el Secretario Privado. Se descarga el contrato electrónico.',
                        'area_role'   => 'secop',
                        'responsable_unidad'      => 'Apoyo de Estructuración SECOP',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => false,
                        'notas'       => 'Secuencial: 1) Estructurar en SECOP II → 2) Abogado enlace aprueba creación → 3) Contratista firma → 4) Secretario Privado firma → 5) Descargar contrato electrónico.',
                        'items' => [
                            [
                                'label'                   => 'Contrato estructurado y documentos cargados en SECOP II',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Apoyo de Estructuración',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'Se relaciona el proceso con el Plan Anual de Adquisiciones cargado previamente.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Creación del proceso aprobada por abogado enlace en SECOP II',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado Enlace',
                                'responsable_secretaria'  => 'Secretaría Jurídica',
                                'notas'                   => 'Flujo de aprobación dentro de la plataforma SECOP II.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Contrato firmado por el contratista en SECOP II (PRIMERO)',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Contratista',
                                'responsable_secretaria'  => null,
                                'notas'                   => 'El contratista firma PRIMERO en la plataforma electrónica.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Contrato firmado por Secretario Privado en SECOP II (DESPUÉS)',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Secretario Privado',
                                'responsable_secretaria'  => 'Secretaría Privada',
                                'notas'                   => 'El Secretario Privado firma DESPUÉS del contratista.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Contrato electrónico descargado de SECOP II',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Apoyo de Estructuración',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'Se descarga una vez firmado por todas las partes.',
                                'requerido'               => true,
                            ],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 7 – Solicitud de RPC
                    // Responsable: planeacion@demo.com (planeacion)
                    // Nota: Hacienda expide el RPC como sub-ítem de esta etapa
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 7,
                        'nombre'      => '7: Solicitud y Expedición del RPC',
                        'descripcion' => 'El abogado imprime el contrato electrónico, adjunta ajustado a derecho y contrato original, y los allega a la Unidad de Descentralización. La solicitud de RPC la firma el Secretario de Planeación y se radica en Hacienda. La Unidad de Presupuesto expide el RPC. Simultáneamente se organiza el expediente físico completo.',
                        'area_role'   => 'planeacion',
                        'responsable_unidad'      => 'Unidad de Descentralización',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => false,
                        'notas'       => 'Mientras se espera la expedición del RPC se puede ir organizando el expediente físico completo. La Unidad de Presupuesto de Hacienda expide el RPC en físico.',
                        'items' => [
                            [
                                'label'                   => 'Contrato electrónico impreso',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Ajustado a Derecho adjunto (original firmado)',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Contrato físico original firmado adjunto',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Solicitud de RPC firmada por el Secretario de Planeación',
                                'tipo_documento'          => 'solicitud',
                                'responsable_unidad'      => 'Unidad de Descentralización',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'La firma es del Secretario de Planeación.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Solicitud radicada en la Secretaría de Hacienda',
                                'tipo_documento'          => 'solicitud',
                                'responsable_unidad'      => 'Unidad de Descentralización',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'RPC expedido en físico por Unidad de Presupuesto – Secretaría de Hacienda',
                                'tipo_documento'          => 'certificado',
                                'responsable_unidad'      => 'Unidad de Presupuesto',
                                'responsable_secretaria'  => 'Secretaría de Hacienda',
                                'notas'                   => 'Registro Presupuestal de Compromiso expedido por Hacienda.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Expediente contractual físico organizado con todos los documentos',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Abogado de Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'Incluye todos los documentos de la lista de chequeo en orden. Puede avanzarse mientras se espera el RPC.',
                                'requerido'               => true,
                            ],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 8 – Radicación Final y Número de Contrato
                    // Responsable: juridica@demo.com (juridica)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 8,
                        'nombre'      => '8: Radicación Final del Expediente y Número de Contrato',
                        'descripcion' => 'Con el RPC listo y el expediente organizado, se procede a radicar el expediente completo en la Oficina de Radicación de la Secretaría Jurídica. Allí se asigna el número de contrato, indispensable para la solicitud de la ARL.',
                        'area_role'   => 'juridica',
                        'responsable_unidad'      => 'Oficina de Radicación',
                        'responsable_secretaria'  => 'Secretaría Jurídica',
                        'es_paralelo' => false,
                        'notas'       => 'El número de contrato asignado en esta etapa es INDISPENSABLE para solicitar la ARL en la etapa siguiente.',
                        'items' => [
                            [
                                'label'                   => 'Expediente físico completo radicado en la Oficina de Radicación – Jurídica',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Oficina de Radicación',
                                'responsable_secretaria'  => 'Secretaría Jurídica',
                                'notas'                   => 'El expediente debe incluir el RPC y todos los documentos de la lista de chequeo.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Número de contrato asignado por la Secretaría Jurídica',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Oficina de Radicación',
                                'responsable_secretaria'  => 'Secretaría Jurídica',
                                'notas'                   => 'Este número es necesario para solicitar la ARL y elaborar el Acta de Inicio.',
                                'requerido'               => true,
                            ],
                        ],
                    ],

                    // ═══════════════════════════════════════════════════════════
                    // ETAPA 9 – ARL, Acta de Inicio e Inicio en SECOP II
                    // Responsable: sistemas@demo.com (unidad_solicitante)
                    // ═══════════════════════════════════════════════════════════
                    [
                        'orden'       => 9,
                        'nombre'      => '9: ARL, Acta de Inicio e Inicio de Ejecución en SECOP II',
                        'descripcion' => 'Con el número de contrato se realiza la solicitud de ARL. Con la ARL y los datos del proceso se elabora el Acta de Inicio, la cual firman las partes (supervisor y contratista). Finalmente se registra el inicio de ejecución en la plataforma SECOP II. Inicio oficial del contrato.',
                        'area_role'   => 'unidad_solicitante',
                        'responsable_unidad'      => 'Unidad Solicitante (Supervisor)',
                        'responsable_secretaria'  => 'Secretaría de Planeación',
                        'es_paralelo' => false,
                        'notas'       => 'Paso final del flujo precontractual. A partir del inicio de ejecución en SECOP II comienza la fase de ejecución contractual.',
                        'items' => [
                            [
                                'label'                   => 'ARL solicitada con el número de contrato',
                                'tipo_documento'          => 'solicitud',
                                'responsable_unidad'      => 'Unidad Solicitante',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'La solicitud se hace con el número de contrato asignado por Jurídica.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'ARL expedida y confirmada',
                                'tipo_documento'          => 'certificado',
                                'responsable_unidad'      => 'ARL (Administradora de Riesgos Laborales)',
                                'responsable_secretaria'  => null,
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Acta de Inicio elaborada con todos los datos del proceso',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Supervisor designado',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => null,
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Acta de Inicio firmada por las partes (Supervisor + Contratista)',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Supervisor + Contratista',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => 'La firman el supervisor designado y el contratista.',
                                'requerido'               => true,
                            ],
                            [
                                'label'                   => 'Inicio de ejecución registrado en SECOP II',
                                'tipo_documento'          => 'documento',
                                'responsable_unidad'      => 'Apoyo de Estructuración SECOP',
                                'responsable_secretaria'  => 'Secretaría de Planeación',
                                'notas'                   => '✅ Paso final: inicio oficial del contrato en la plataforma electrónica.',
                                'requerido'               => true,
                            ],
                        ],
                    ],

                ],
            ],

            /*
            |==================================================================
            | WORKFLOWS FUTUROS (comentados para implementación progresiva)
            |==================================================================
            | Los siguientes workflows se implementarán progresivamente.
            | Las demás secretarías y sus unidades ya existen en el sistema
            | y participan gestionando documentos en el flujo CD-PN.
            | Cuando se activen estos workflows, se habilitará la creación
            | de procesos desde esas secretarías.
            |
            | PENDIENTES:
            |   - MC:    Mínima Cuantía
            |   - SA:    Selección Abreviada
            |   - LP:    Licitación Pública
            |   - CM:    Concurso de Méritos
            |   - CD_PJ: Contratación Directa - Persona Jurídica
            |            (similar a CD_PN pero al estilo de Hacienda)
            |==================================================================
            */

        ];

        // ─────────────────────────────────────────────────────────────
        // Insertar workflows y sus etapas
        // ─────────────────────────────────────────────────────────────
        foreach ($workflows as $workflowData) {
            $workflowId = DB::table('workflows')->insertGetId([
                'codigo' => $workflowData['codigo'],
                'nombre' => $workflowData['nombre'],
                'activo' => $workflowData['activo'],
                'requiere_viabilidad_economica_inicial' => $workflowData['requiere_viabilidad_economica_inicial'] ?? true,
                'requiere_estudios_previos_completos'   => $workflowData['requiere_estudios_previos_completos'] ?? true,
                'observaciones' => $workflowData['observaciones'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $etapasIds = [];

            foreach ($workflowData['etapas'] as $etapaData) {
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
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);

                $etapasIds[$etapaData['orden']] = $etapaId;

                // Insertar items de la etapa
                if (!empty($etapaData['items'])) {
                    foreach ($etapaData['items'] as $orden => $itemData) {
                        if (is_string($itemData)) {
                            $itemData = ['label' => $itemData, 'requerido' => true];
                        }

                        DB::table('etapa_items')->insert([
                            'etapa_id'                => $etapaId,
                            'orden'                   => $orden + 1,
                            'label'                   => $itemData['label'],
                            'requerido'               => $itemData['requerido'] ?? true,
                            'responsable_unidad'      => $itemData['responsable_unidad'] ?? null,
                            'responsable_secretaria'  => $itemData['responsable_secretaria'] ?? null,
                            'notas'                   => $itemData['notas'] ?? null,
                            'tipo_documento'          => $itemData['tipo_documento'] ?? null,
                            'created_at'              => now(),
                            'updated_at'              => now(),
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
                    ->update([
                        'next_etapa_id' => $etapasIds[$ordenSiguiente],
                        'updated_at'    => now(),
                    ]);
            }
        }

        $this->command->info('✅ Workflows, etapas e items creados correctamente.');
    }

    /**
     * Elimina datos de la tabla sin romper si no existe.
     */
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
