<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * DashboardTemplatesProductionSeeder
 * ==================================
 *
 * FASE 3 - ORGANIZACIÓN DE DATOS
 *
 * Crea templates de dashboard específicos por rol para ambiente de producción:
 * - Template Ejecutivo (Gobernador): Vista estratégica 4 columnas
 * - Template Secretarial (Secretarios): Vista operativa 3 columnas
 * - Template de Gestión (Jefes Unidad): Vista gestión 4 columnas compacto
 *
 * Cada template incluye:
 * ✅ Layout específico (columnas, espaciado, tema)
 * ✅ Widgets predeterminados por rol
 * ✅ Configuración responsiva
 * ✅ Temas institucionales
 */
class DashboardTemplatesProductionSeeder extends Seeder
{
    /**
     * Seed de templates de dashboard para producción
     */
    public function run(): void
    {
        $this->command->info('📊 Creando templates de dashboard para producción...');

        // Limpiar datos existentes
        $this->cleanExistingData();

        // Crear templates de dashboard
        $templates = $this->createDashboardTemplates();

        // Crear asignaciones por rol
        $this->createRoleAssignments($templates);

        // Crear widgets predeterminados
        $this->createDefaultWidgets($templates);

        $this->command->info('');
        $this->command->info('✅ Templates de dashboard creados exitosamente!');
        $this->command->info('📊 Templates disponibles:');
        $this->command->info('   👑 Template Ejecutivo (Gobernador)');
        $this->command->info('   📋 Template Secretarial (Secretarios)');
        $this->command->info('   ⚙️  Template de Gestión (Jefes de Unidad)');
        $this->command->info('   🎨 3 temas institucionales configurados');
    }

    /**
     * Limpiar datos existentes de templates de prueba
     */
    private function cleanExistingData(): void
    {
        $this->command->info('   🧹 Limpiando templates de prueba existentes...');

        // Eliminar asignaciones de roles existentes de prueba
        if (DB::getSchemaBuilder()->hasTable('dashboard_rol_asignaciones')) {
            DB::table('dashboard_rol_asignaciones')->where('activo', false)->delete();
        }

        // Eliminar templates de prueba
        if (DB::getSchemaBuilder()->hasTable('dashboard_plantillas')) {
            $testTemplates = DB::table('dashboard_plantillas')
                ->where(function($query) {
                    $query->where('nombre', 'like', '%test%')
                          ->orWhere('nombre', 'like', '%prueba%')
                          ->orWhere('nombre', 'like', '%demo%');
                })
                ->delete();

            if ($testTemplates > 0) {
                $this->command->info("     ✅ Eliminados {$testTemplates} templates de prueba");
            }
        }
    }

    /**
     * Crear templates de dashboard principales
     */
    private function createDashboardTemplates(): array
    {
        $this->command->info('   📊 Creando templates principales...');

        $templates = [];

        // TEMPLATE EJECUTIVO - GOBERNADOR
        $templates['ejecutivo'] = $this->insertTemplate([
            'nombre' => 'Dashboard Ejecutivo',
            'slug' => 'dashboard-gobernador',
            'descripcion' => 'Vista estratégica para toma de decisiones ejecutivas del Gobernador',
            'tipo_dashboard' => 'ejecutivo',
            'config_json' => [
                'layout' => [
                    'tipo' => 'executive',
                    'columnas' => 4,
                    'espaciado' => 'amplio',
                    'altura_minima' => '200px',
                    'margen' => '20px'
                ],
                'tema' => [
                    'nombre' => 'verde-institucional',
                    'primary' => '#14532d',
                    'secondary' => '#16a34a',
                    'accent' => '#86efac',
                    'background' => '#f0fdf4',
                    'text' => '#1f2937'
                ],
                'responsive' => [
                    'xl' => ['columnas' => 4, 'espaciado' => 'amplio'],
                    'lg' => ['columnas' => 3, 'espaciado' => 'normal'],
                    'md' => ['columnas' => 2, 'espaciado' => 'compacto'],
                    'sm' => ['columnas' => 1, 'espaciado' => 'minimo'],
                    'xs' => ['columnas' => 1, 'espaciado' => 'minimo']
                ],
                'widgets_predeterminados' => [
                    'presupuesto_total_ejecutado',
                    'procesos_en_curso_global',
                    'eficiencia_promedio_secretarias',
                    'contratos_vigentes_valor',
                    'alertas_criticas_global',
                    'indicadores_estrategicos'
                ],
                'actualizacion' => [
                    'frecuencia' => '30_minutos',
                    'tiempo_real' => false,
                    'cache_duracion' => '15_minutos'
                ]
            ],
            'activo' => true,
            'es_publico' => false,
            'orden_prioridad' => 1
        ]);

        // TEMPLATE SECRETARIAL - SECRETARIOS
        $templates['secretarial'] = $this->insertTemplate([
            'nombre' => 'Dashboard Secretarial',
            'slug' => 'dashboard-secretario',
            'descripcion' => 'Vista operativa para gestión de secretarías de despacho',
            'tipo_dashboard' => 'secretarial',
            'config_json' => [
                'layout' => [
                    'tipo' => 'operational',
                    'columnas' => 3,
                    'espaciado' => 'normal',
                    'altura_minima' => '180px',
                    'margen' => '15px'
                ],
                'tema' => [
                    'nombre' => 'azul-operativo',
                    'primary' => '#1e40af',
                    'secondary' => '#3b82f6',
                    'accent' => '#93c5fd',
                    'background' => '#eff6ff',
                    'text' => '#1f2937'
                ],
                'responsive' => [
                    'xl' => ['columnas' => 3, 'espaciado' => 'normal'],
                    'lg' => ['columnas' => 3, 'espaciado' => 'normal'],
                    'md' => ['columnas' => 2, 'espaciado' => 'compacto'],
                    'sm' => ['columnas' => 1, 'espaciado' => 'minimo'],
                    'xs' => ['columnas' => 1, 'espaciado' => 'minimo']
                ],
                'widgets_predeterminados' => [
                    'procesos_en_curso_secretaria',
                    'pendientes_firma_secretaria',
                    'tiempo_promedio_tramite',
                    'presupuesto_secretaria',
                    'contratos_vigentes_secretaria',
                    'alertas_secretaria'
                ],
                'filtros_predeterminados' => [
                    'secretaria' => 'auto', // Se detecta automáticamente
                    'periodo' => 'mes_actual'
                ],
                'actualizacion' => [
                    'frecuencia' => '15_minutos',
                    'tiempo_real' => true,
                    'canal_websocket' => 'secretaria.{secretaria_id}',
                    'cache_duracion' => '10_minutos'
                ]
            ],
            'activo' => true,
            'es_publico' => false,
            'orden_prioridad' => 2
        ]);

        // TEMPLATE DE GESTIÓN - JEFES DE UNIDAD
        $templates['gestion'] = $this->insertTemplate([
            'nombre' => 'Dashboard de Gestión',
            'slug' => 'dashboard-jefe-unidad',
            'descripcion' => 'Vista de gestión operativa para jefes de unidad',
            'tipo_dashboard' => 'gestion',
            'config_json' => [
                'layout' => [
                    'tipo' => 'management',
                    'columnas' => 4,
                    'espaciado' => 'compacto',
                    'altura_minima' => '160px',
                    'margen' => '12px'
                ],
                'tema' => [
                    'nombre' => 'verde-gestion',
                    'primary' => '#166534',
                    'secondary' => '#22c55e',
                    'accent' => '#86efac',
                    'background' => '#f0fdf4',
                    'text' => '#1f2937'
                ],
                'responsive' => [
                    'xl' => ['columnas' => 4, 'espaciado' => 'compacto'],
                    'lg' => ['columnas' => 3, 'espaciado' => 'normal'],
                    'md' => ['columnas' => 2, 'espaciado' => 'normal'],
                    'sm' => ['columnas' => 1, 'espaciado' => 'minimo'],
                    'xs' => ['columnas' => 1, 'espaciado' => 'minimo']
                ],
                'widgets_predeterminados' => [
                    'carga_trabajo_equipo',
                    'procesos_asignados_unidad',
                    'tiempo_respuesta_promedio',
                    'rendimiento_equipo',
                    'tareas_pendientes_unidad',
                    'indicadores_productividad'
                ],
                'filtros_predeterminados' => [
                    'unidad' => 'auto', // Se detecta automáticamente
                    'periodo' => 'semana_actual',
                    'equipo' => 'todos'
                ],
                'actualizacion' => [
                    'frecuencia' => '10_minutos',
                    'tiempo_real' => true,
                    'canal_websocket' => 'unidad.{unidad_id}',
                    'notificaciones' => true,
                    'cache_duracion' => '5_minutos'
                ]
            ],
            'activo' => true,
            'es_publico' => false,
            'orden_prioridad' => 3
        ]);

        $this->command->info('     ✅ 3 templates principales creados');
        return $templates;
    }

    /**
     * Insertar un template en la base de datos
     */
    private function insertTemplate(array $templateData): int
    {
        return DB::table('dashboard_plantillas')->insertGetId([
            'nombre' => $templateData['nombre'],
            'slug' => $templateData['slug'],
            'descripcion' => $templateData['descripcion'],
            'tipo_dashboard' => $templateData['tipo_dashboard'],
            'config_json' => json_encode($templateData['config_json']),
            'activo' => $templateData['activo'],
            'es_publico' => $templateData['es_publico'],
            'orden_prioridad' => $templateData['orden_prioridad'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    /**
     * Crear asignaciones por rol
     */
    private function createRoleAssignments(array $templates): void
    {
        $this->command->info('   👥 Creando asignaciones por rol...');

        $assignments = [
            [
                'role_name' => 'gobernador',
                'dashboard_plantilla_id' => $templates['ejecutivo'],
                'prioridad' => 1,
                'activo' => true,
                'es_predeterminado' => true,
                'configuracion_adicional' => json_encode([
                    'acceso_global' => true,
                    'puede_ver_todas_secretarias' => true,
                    'alertas_criticas' => true,
                    'reportes_ejecutivos' => true
                ])
            ],
            [
                'role_name' => 'secretario',
                'dashboard_plantilla_id' => $templates['secretarial'],
                'prioridad' => 2,
                'activo' => true,
                'es_predeterminado' => true,
                'configuracion_adicional' => json_encode([
                    'acceso_secretaria' => true,
                    'puede_aprobar_procesos' => true,
                    'notificaciones_tiempo_real' => true,
                    'reportes_secretaria' => true
                ])
            ],
            [
                'role_name' => 'jefe_unidad',
                'dashboard_plantilla_id' => $templates['gestion'],
                'prioridad' => 3,
                'activo' => true,
                'es_predeterminado' => true,
                'configuracion_adicional' => json_encode([
                    'acceso_unidad' => true,
                    'puede_gestionar_equipo' => true,
                    'asignaciones_procesos' => true,
                    'seguimiento_rendimiento' => true
                ])
            ],
            [
                'role_name' => 'coord_contratacion',
                'dashboard_plantilla_id' => $templates['gestion'],
                'prioridad' => 4,
                'activo' => true,
                'es_predeterminado' => true,
                'configuracion_adicional' => json_encode([
                    'acceso_coordinacion' => true,
                    'puede_coordinar_procesos' => true,
                    'vista_carga_trabajo' => true
                ])
            ],
            [
                'role_name' => 'prof_contratacion',
                'dashboard_plantilla_id' => $templates['gestion'],
                'prioridad' => 5,
                'activo' => true,
                'es_predeterminado' => true,
                'configuracion_adicional' => json_encode([
                    'acceso_profesional' => true,
                    'procesos_asignados' => true,
                    'documentos_pendientes' => true
                ])
            ]
        ];

        foreach ($assignments as $assignment) {
            DB::table('dashboard_rol_asignaciones')->insert(array_merge($assignment, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]));
        }

        $this->command->info('     ✅ ' . count($assignments) . ' asignaciones de rol creadas');
    }

    /**
     * Crear widgets predeterminados
     */
    private function createDefaultWidgets(array $templates): void
    {
        $this->command->info('   🧩 Creando widgets predeterminados...');

        $widgets = [
            // Widgets para Template Ejecutivo
            [
                'dashboard_plantilla_id' => $templates['ejecutivo'],
                'tipo' => 'kpi',
                'titulo' => 'Presupuesto Total Ejecutado',
                'subtitulo' => 'Ejecución presupuestal global',
                'configuracion' => json_encode([
                    'metrica' => 'presupuesto_total_ejecutado',
                    'formato' => 'moneda',
                    'comparacion' => 'periodo_anterior',
                    'icono' => 'fas fa-dollar-sign',
                    'color' => 'success'
                ]),
                'posicion_x' => 0,
                'posicion_y' => 0,
                'ancho' => 1,
                'alto' => 1,
                'orden' => 1
            ],
            [
                'dashboard_plantilla_id' => $templates['ejecutivo'],
                'tipo' => 'kpi',
                'titulo' => 'Procesos en Curso',
                'subtitulo' => 'Procesos activos globalmente',
                'configuracion' => json_encode([
                    'metrica' => 'procesos_en_curso_global',
                    'formato' => 'numero',
                    'comparacion' => 'periodo_anterior',
                    'icono' => 'fas fa-cogs',
                    'color' => 'primary'
                ]),
                'posicion_x' => 1,
                'posicion_y' => 0,
                'ancho' => 1,
                'alto' => 1,
                'orden' => 2
            ],
            [
                'dashboard_plantilla_id' => $templates['ejecutivo'],
                'tipo' => 'chart',
                'titulo' => 'Eficiencia por Secretarías',
                'subtitulo' => 'Rendimiento promedio secretarías',
                'configuracion' => json_encode([
                    'tipo_grafico' => 'bar',
                    'metrica' => 'eficiencia_promedio_secretarias',
                    'agrupacion' => 'secretaria',
                    'periodo' => 'mes_actual'
                ]),
                'posicion_x' => 2,
                'posicion_y' => 0,
                'ancho' => 2,
                'alto' => 2,
                'orden' => 3
            ],

            // Widgets para Template Secretarial
            [
                'dashboard_plantilla_id' => $templates['secretarial'],
                'tipo' => 'kpi',
                'titulo' => 'Procesos Secretaría',
                'subtitulo' => 'Procesos activos en la secretaría',
                'configuracion' => json_encode([
                    'metrica' => 'procesos_en_curso_secretaria',
                    'filtro' => 'secretaria_actual',
                    'formato' => 'numero',
                    'icono' => 'fas fa-tasks',
                    'color' => 'info'
                ]),
                'posicion_x' => 0,
                'posicion_y' => 0,
                'ancho' => 1,
                'alto' => 1,
                'orden' => 1
            ],
            [
                'dashboard_plantilla_id' => $templates['secretarial'],
                'tipo' => 'kpi',
                'titulo' => 'Pendientes Firma',
                'subtitulo' => 'Documentos esperando aprobación',
                'configuracion' => json_encode([
                    'metrica' => 'pendientes_firma_secretaria',
                    'filtro' => 'secretaria_actual',
                    'formato' => 'numero',
                    'icono' => 'fas fa-pen',
                    'color' => 'warning'
                ]),
                'posicion_x' => 1,
                'posicion_y' => 0,
                'ancho' => 1,
                'alto' => 1,
                'orden' => 2
            ],
            [
                'dashboard_plantilla_id' => $templates['secretarial'],
                'tipo' => 'table',
                'titulo' => 'Procesos Prioritarios',
                'subtitulo' => 'Procesos que requieren atención',
                'configuracion' => json_encode([
                    'metrica' => 'procesos_prioritarios_secretaria',
                    'columnas' => ['proceso', 'etapa', 'responsable', 'dias_transcurridos'],
                    'filtro' => 'secretaria_actual',
                    'ordenamiento' => 'prioridad_desc'
                ]),
                'posicion_x' => 0,
                'posicion_y' => 1,
                'ancho' => 3,
                'alto' => 2,
                'orden' => 3
            ],

            // Widgets para Template de Gestión
            [
                'dashboard_plantilla_id' => $templates['gestion'],
                'tipo' => 'kpi',
                'titulo' => 'Carga de Trabajo',
                'subtitulo' => 'Procesos asignados al equipo',
                'configuracion' => json_encode([
                    'metrica' => 'carga_trabajo_equipo',
                    'filtro' => 'unidad_actual',
                    'formato' => 'numero',
                    'icono' => 'fas fa-users',
                    'color' => 'primary'
                ]),
                'posicion_x' => 0,
                'posicion_y' => 0,
                'ancho' => 1,
                'alto' => 1,
                'orden' => 1
            ],
            [
                'dashboard_plantilla_id' => $templates['gestion'],
                'tipo' => 'kpi',
                'titulo' => 'Tiempo Respuesta',
                'subtitulo' => 'Promedio días respuesta',
                'configuracion' => json_encode([
                    'metrica' => 'tiempo_respuesta_promedio',
                    'filtro' => 'unidad_actual',
                    'formato' => 'dias',
                    'icono' => 'fas fa-clock',
                    'color' => 'success'
                ]),
                'posicion_x' => 1,
                'posicion_y' => 0,
                'ancho' => 1,
                'alto' => 1,
                'orden' => 2
            ],
            [
                'dashboard_plantilla_id' => $templates['gestion'],
                'tipo' => 'chart',
                'titulo' => 'Rendimiento del Equipo',
                'subtitulo' => 'Productividad por miembro',
                'configuracion' => json_encode([
                    'tipo_grafico' => 'doughnut',
                    'metrica' => 'rendimiento_equipo',
                    'agrupacion' => 'usuario',
                    'filtro' => 'unidad_actual'
                ]),
                'posicion_x' => 2,
                'posicion_y' => 0,
                'ancho' => 2,
                'alto' => 2,
                'orden' => 3
            ]
        ];

        foreach ($widgets as $widget) {
            DB::table('dashboard_widgets')->insert(array_merge($widget, [
                'activo' => true,
                'es_predeterminado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]));
        }

        $this->command->info('     ✅ ' . count($widgets) . ' widgets predeterminados creados');
    }
}