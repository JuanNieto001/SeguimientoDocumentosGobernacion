<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\ContratoAplicacion;
use App\Models\DashboardRolAsignacion;
use App\Models\DashboardSecretariaAsignacion;
use App\Models\DashboardUnidadAsignacion;
use App\Models\DashboardUsuarioAsignacion;
use App\Models\Proceso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $roles = $user->getRoleNames()->values()->all();
        $chartTypeOverrides = [];
        $asignacion = null;
        $dataScope = $this->inferDefaultScopeFromRoles($roles);

        $asignacionUsuario = DashboardUsuarioAsignacion::query()
            ->with(['plantilla.widgets' => fn ($q) => $q->where('activo', true)->orderBy('orden')])
            ->where('user_id', $user->id)
            ->where('activo', true)
            ->first();

        if ($asignacionUsuario && ($asignacionUsuario->plantilla || !empty($asignacionUsuario->config_json['custom_widgets'] ?? []))) {
            $asignacion = $asignacionUsuario;
            $chartTypeOverrides = (array) ($asignacionUsuario->config_json['chart_types'] ?? []);
            $dataScope = $this->sanitizeDataScopeForUser((string) ($asignacionUsuario->config_json['data_scope'] ?? 'usuario'), $user, $roles);
        } else {
            $asignacionUnidad = null;
            if (!empty($user->unidad_id)) {
                $asignacionUnidad = DashboardUnidadAsignacion::query()
                    ->with(['plantilla.widgets' => fn ($q) => $q->where('activo', true)->orderBy('orden')])
                    ->where('unidad_id', (int) $user->unidad_id)
                    ->where('activo', true)
                    ->first();
            }

            if ($asignacionUnidad && ($asignacionUnidad->plantilla || !empty($asignacionUnidad->config_json['custom_widgets'] ?? []))) {
                $asignacion = $asignacionUnidad;
                $chartTypeOverrides = (array) ($asignacionUnidad->config_json['chart_types'] ?? []);
                $dataScope = $this->sanitizeDataScopeForUser((string) ($asignacionUnidad->config_json['data_scope'] ?? 'unidad'), $user, $roles);
            } else {
            $asignacionSecretaria = null;
            if (!empty($user->secretaria_id)) {
                $asignacionSecretaria = DashboardSecretariaAsignacion::query()
                    ->with(['plantilla.widgets' => fn ($q) => $q->where('activo', true)->orderBy('orden')])
                    ->where('secretaria_id', (int) $user->secretaria_id)
                    ->where('activo', true)
                    ->first();
            }

            if ($asignacionSecretaria && ($asignacionSecretaria->plantilla || !empty($asignacionSecretaria->config_json['custom_widgets'] ?? []))) {
                $asignacion = $asignacionSecretaria;
                $chartTypeOverrides = (array) ($asignacionSecretaria->config_json['chart_types'] ?? []);
                $dataScope = $this->sanitizeDataScopeForUser((string) ($asignacionSecretaria->config_json['data_scope'] ?? 'secretaria'), $user, $roles);
            } else {
                $asignacionRol = DashboardRolAsignacion::query()
                    ->with(['plantilla.widgets' => fn ($q) => $q->where('activo', true)->orderBy('orden')])
                    ->where('activo', true)
                    ->whereIn('role_name', $roles)
                    ->orderBy('prioridad')
                    ->first();

                if ($asignacionRol && ($asignacionRol->plantilla || !empty($asignacionRol->config_json['custom_widgets'] ?? []))) {
                    $asignacion = $asignacionRol;
                    $chartTypeOverrides = (array) ($asignacionRol->config_json['chart_types'] ?? []);
                    $dataScope = $this->sanitizeDataScopeForUser((string) ($asignacionRol->config_json['data_scope'] ?? $this->inferDefaultScopeFromRoles($roles)), $user, $roles);
                }
            }
            }
        }

        if (!$asignacion) {
            $timeline = $this->buildTimelineProcesos($user, $roles, $dataScope);

            return view('dashboards.mi', [
                'plantilla' => null,
                'widgets' => collect(),
                'kpis' => [],
                'charts' => [],
                'timeline' => $timeline,
            ]);
        }

        $customWidgets = $this->buildCustomWidgets((array) ($asignacion->config_json['custom_widgets'] ?? []));

        if ($customWidgets->isNotEmpty()) {
            $plantilla = (object) [
                'nombre' => 'Dashboard Personalizado',
                'descripcion' => 'Construido desde cero en el Motor de Dashboards.',
            ];
            $widgets = $customWidgets;
        } elseif ($asignacion->plantilla) {
            $plantilla = $asignacion->plantilla;
            $widgets = $plantilla->widgets;
        } else {
            return view('dashboards.mi', [
                'plantilla' => null,
                'widgets' => collect(),
                'kpis' => [],
                'charts' => [],
                'timeline' => collect(),
            ]);
        }

        $kpis = [];
        $charts = [];
        $chartTypes = [];

        foreach ($widgets as $widget) {
            if ($widget->tipo === 'kpi') {
                $kpis[$widget->id] = $this->valorMetrica($widget->metrica, $user, $roles, $dataScope);
            }

            if ($widget->tipo === 'chart') {
                $charts[$widget->id] = $this->datosGrafica($widget->metrica, $user, $roles, $dataScope);
                $chartTypes[$widget->id] = $this->resolverTipoGrafica($widget->metrica, $chartTypeOverrides);
            }
        }

        $timeline = collect();
        return view('dashboards.mi', compact('plantilla', 'widgets', 'kpis', 'charts', 'chartTypes', 'timeline'));
    }

    private function buildCustomWidgets(array $customWidgets)
    {
        return collect($customWidgets)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item, int $index) {
                $metrica = (string) ($item['metrica'] ?? '');
                $tipo = (string) ($item['tipo'] ?? '');

                if (!in_array($tipo, ['kpi', 'chart'], true) || $metrica === '') {
                    return null;
                }

                return (object) [
                    'id' => (string) ($item['id'] ?? ('custom_' . $index)),
                    'tipo' => $tipo,
                    'metrica' => $metrica,
                    'titulo' => (string) ($item['titulo'] ?? ucfirst(str_replace('_', ' ', $metrica))),
                    'orden' => (int) ($item['orden'] ?? ($index + 1)),
                ];
            })
            ->filter()
            ->sortBy('orden')
            ->values();
    }

    private function valorMetrica(?string $metrica, $user, array $roles, string $dataScope): int|float
    {
        $procesosQuery = $this->procesosVisiblesQuery($user, $roles, $dataScope);
        $alertasQuery = $this->alertasVisiblesQuery($user, $roles, $dataScope);

        return match ($metrica) {
            'procesos_en_curso' => (clone $procesosQuery)
                ->where('estado', 'EN_CURSO')
                ->count(),
            'procesos_finalizados_mes' => (clone $procesosQuery)
                ->whereIn('estado', ['FINALIZADO', 'completado', 'cerrado'])
                ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'alertas_altas_no_leidas' => (clone $alertasQuery)
                ->where('prioridad', 'alta')
                ->where('leida', false)
                ->count(),
            'contratos_vigentes' => ContratoAplicacion::query()->activos()->vigentes()->count(),
            'contratos_por_vencer_90' => ContratoAplicacion::query()
                ->activos()
                ->whereNotNull('fecha_fin')
                ->whereBetween('fecha_fin', [now()->toDateString(), now()->addDays(90)->toDateString()])
                ->count(),
            'valor_total_contratos' => (float) ContratoAplicacion::query()->activos()->sum('valor_total'),
            default => 0,
        };
    }

    private function datosGrafica(?string $metrica, $user, array $roles, string $dataScope): array
    {
        return match ($metrica) {
            'procesos_por_area' => $this->chartProcesosPorArea($user, $roles, $dataScope),
            'procesos_por_estado' => $this->chartProcesosPorEstado($user, $roles, $dataScope),
            'contratos_por_mes' => $this->chartContratosPorMes(),
            default => ['labels' => [], 'datasets' => []],
        };
    }

    private function chartProcesosPorArea($user, array $roles, string $dataScope): array
    {
        $rows = $this->procesosVisiblesQuery($user, $roles, $dataScope)
            ->select('area_actual_role', DB::raw('count(*) as total'))
            ->whereNotNull('area_actual_role')
            ->groupBy('area_actual_role')
            ->orderBy('area_actual_role')
            ->get();

        return [
            'labels' => $rows->pluck('area_actual_role')->toArray(),
            'datasets' => [
                [
                    'label' => 'Procesos por área',
                    'data' => $rows->pluck('total')->toArray(),
                    'backgroundColor' => ['#2563eb', '#16a34a', '#ca8a04', '#ea580c', '#9333ea', '#0891b2'],
                ],
            ],
        ];
    }

    private function chartProcesosPorEstado($user, array $roles, string $dataScope): array
    {
        $rows = $this->procesosVisiblesQuery($user, $roles, $dataScope)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        return [
            'labels' => $rows->pluck('estado')->toArray(),
            'datasets' => [
                [
                    'label' => 'Procesos por estado',
                    'data' => $rows->pluck('total')->toArray(),
                    'backgroundColor' => ['#15803d', '#ca8a04', '#dc2626', '#64748b'],
                ],
            ],
        ];
    }

    private function chartContratosPorMes(): array
    {
        $rows = ContratoAplicacion::query()
            ->selectRaw("DATE_FORMAT(fecha_inicio, '%Y-%m') as ym, COUNT(*) as total")
            ->whereNotNull('fecha_inicio')
            ->whereYear('fecha_inicio', now()->year)
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        return [
            'labels' => $rows->pluck('ym')->toArray(),
            'datasets' => [
                [
                    'label' => 'Contratos iniciados',
                    'data' => $rows->pluck('total')->toArray(),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37,99,235,.18)',
                    'fill' => true,
                    'tension' => 0.25,
                ],
            ],
        ];
    }

    private function procesosVisiblesQuery($user, array $roles, string $dataScope): Builder
    {
        return $this->aplicarScopeProcesos(Proceso::query(), $user, $roles, $dataScope);
    }

    private function alertasVisiblesQuery($user, array $roles, string $dataScope): Builder
    {
        $query = Alerta::query();

        if ($this->tieneAlcanceGlobal($roles) || $dataScope === 'global') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user, $roles, $dataScope) {
            $q->where('user_id', $user->id)
                ->orWhereHas('proceso', function (Builder $procesoQuery) use ($user, $roles, $dataScope) {
                    $this->aplicarScopeProcesos($procesoQuery, $user, $roles, $dataScope);
                });
        });
    }

    private function aplicarScopeProcesos(Builder $query, $user, array $roles, string $dataScope): Builder
    {
        if ($this->tieneAlcanceGlobal($roles) || $dataScope === 'global') {
            return $query;
        }

        if ($dataScope === 'secretaria') {
            if (!empty($user->secretaria_id)) {
                return $query->where('secretaria_origen_id', (int) $user->secretaria_id);
            }

            return $query->where('created_by', (int) $user->id);
        }

        if ($dataScope === 'unidad') {
            if (!empty($user->unidad_id)) {
                return $query->where('unidad_origen_id', (int) $user->unidad_id);
            }

            return $query->where('created_by', (int) $user->id);
        }

        return $query->where('created_by', (int) $user->id);
    }

    private function buildTimelineProcesos($user, array $roles, string $dataScope)
    {
        $query = Proceso::query();

        if ($dataScope === 'usuario' && !$this->tieneAlcanceGlobal($roles)) {
            $query->where(function (Builder $q) use ($user) {
                $q->where('created_by', (int) $user->id)
                    ->orWhereHas('alertas', function (Builder $alertaQuery) use ($user) {
                        $alertaQuery->where('user_id', (int) $user->id);
                    });
            });
        } else {
            $this->aplicarScopeProcesos($query, $user, $roles, $dataScope);
        }

        return $query
            ->select(['id', 'codigo', 'estado', 'objeto', 'area_actual_role', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();
    }

    private function sanitizeDataScopeForUser(string $scope, $user, array $roles): string
    {
        $allowed = ['usuario', 'unidad', 'secretaria', 'global'];
        $scopeValue = in_array($scope, $allowed, true) ? $scope : $this->inferDefaultScopeFromRoles($roles);

        if ($scopeValue === 'secretaria' && empty($user->secretaria_id)) {
            $scopeValue = 'usuario';
        }

        if ($scopeValue === 'unidad' && empty($user->unidad_id)) {
            $scopeValue = 'usuario';
        }

        return $scopeValue;
    }

    private function inferDefaultScopeFromRoles(array $roles): string
    {
        if ($this->tieneAlcanceGlobal($roles)) {
            return 'global';
        }

        if (in_array('secretario', $roles, true)) {
            return 'secretaria';
        }

        if (in_array('jefe_unidad', $roles, true)) {
            return 'unidad';
        }

        return 'usuario';
    }

    private function tieneAlcanceGlobal(array $roles): bool
    {
        return !empty(array_intersect($roles, ['admin', 'admin_general', 'gobernador']));
    }

    private function resolverTipoGrafica(?string $metrica, array $overrides): string
    {
        $defaults = [
            'procesos_por_area' => 'bar',
            'procesos_por_estado' => 'doughnut',
            'contratos_por_mes' => 'line',
        ];

        $allowed = [
            'procesos_por_area' => ['bar', 'line', 'pie', 'doughnut', 'polarArea', 'radar'],
            'procesos_por_estado' => ['doughnut', 'pie', 'bar', 'polarArea', 'radar'],
            'contratos_por_mes' => ['line', 'bar', 'area'],
        ];

        $metric = (string) $metrica;
        $selected = $overrides[$metric] ?? null;

        if (is_string($selected) && isset($allowed[$metric]) && in_array($selected, $allowed[$metric], true)) {
            return $selected;
        }

        return $defaults[$metric] ?? 'bar';
    }
}
