<?php

namespace Database\Seeders;

use App\Models\DashboardPlantilla;
use App\Models\DashboardRolAsignacion;
use App\Models\DashboardWidget;
use Illuminate\Database\Seeder;

class DashboardRolesSeeder extends Seeder
{
    public function run(): void
    {
        $plantillas = [
            [
                'slug' => 'dashboard-gobernador',
                'nombre' => 'Dashboard Gobernador',
                'descripcion' => 'Vista ejecutiva para el despacho del Gobernador.',
                'widgets' => [
                    ['titulo' => 'Procesos en curso', 'tipo' => 'kpi', 'metrica' => 'procesos_en_curso', 'orden' => 1],
                    ['titulo' => 'Finalizados del mes', 'tipo' => 'kpi', 'metrica' => 'procesos_finalizados_mes', 'orden' => 2],
                    ['titulo' => 'Alertas altas sin leer', 'tipo' => 'kpi', 'metrica' => 'alertas_altas_no_leidas', 'orden' => 3],
                    ['titulo' => 'Procesos por estado', 'tipo' => 'chart', 'metrica' => 'procesos_por_estado', 'orden' => 4],
                ],
            ],
            [
                'slug' => 'dashboard-secretario',
                'nombre' => 'Dashboard Secretario',
                'descripcion' => 'Seguimiento operativo para Secretarías.',
                'widgets' => [
                    ['titulo' => 'Procesos en curso', 'tipo' => 'kpi', 'metrica' => 'procesos_en_curso', 'orden' => 1],
                    ['titulo' => 'Procesos por área', 'tipo' => 'chart', 'metrica' => 'procesos_por_area', 'orden' => 2],
                    ['titulo' => 'Contratos por mes', 'tipo' => 'chart', 'metrica' => 'contratos_por_mes', 'orden' => 3],
                ],
            ],
            [
                'slug' => 'dashboard-jefe-unidad',
                'nombre' => 'Dashboard Jefe de Unidad',
                'descripcion' => 'Vista de control para Jefes de Unidad (no Jefe de Sistemas).',
                'widgets' => [
                    ['titulo' => 'Contratos vigentes', 'tipo' => 'kpi', 'metrica' => 'contratos_vigentes', 'orden' => 1],
                    ['titulo' => 'Por vencer (90 días)', 'tipo' => 'kpi', 'metrica' => 'contratos_por_vencer_90', 'orden' => 2],
                    ['titulo' => 'Procesos por estado', 'tipo' => 'chart', 'metrica' => 'procesos_por_estado', 'orden' => 3],
                ],
            ],
        ];

        foreach ($plantillas as $item) {
            $plantilla = DashboardPlantilla::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'nombre' => $item['nombre'],
                    'descripcion' => $item['descripcion'],
                    'activo' => true,
                ]
            );

            foreach ($item['widgets'] as $widget) {
                DashboardWidget::query()->updateOrCreate(
                    [
                        'dashboard_plantilla_id' => $plantilla->id,
                        'titulo' => $widget['titulo'],
                    ],
                    [
                        'tipo' => $widget['tipo'],
                        'metrica' => $widget['metrica'],
                        'orden' => $widget['orden'],
                        'activo' => true,
                    ]
                );
            }
        }

        $map = DashboardPlantilla::query()->pluck('id', 'slug');

        DashboardRolAsignacion::query()->updateOrCreate(
            ['role_name' => 'gobernador'],
            ['dashboard_plantilla_id' => $map['dashboard-gobernador'], 'prioridad' => 10, 'activo' => true]
        );
        DashboardRolAsignacion::query()->updateOrCreate(
            ['role_name' => 'secretario'],
            ['dashboard_plantilla_id' => $map['dashboard-secretario'], 'prioridad' => 10, 'activo' => true]
        );
        DashboardRolAsignacion::query()->updateOrCreate(
            ['role_name' => 'jefe_unidad'],
            ['dashboard_plantilla_id' => $map['dashboard-jefe-unidad'], 'prioridad' => 10, 'activo' => true]
        );
    }
}
