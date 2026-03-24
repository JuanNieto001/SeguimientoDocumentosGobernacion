<?php

namespace App\Http\Controllers;

use App\Models\DashboardPlantilla;
use App\Models\DashboardAsignacionAuditoria;
use App\Models\DashboardRolAsignacion;
use App\Models\DashboardSecretariaAsignacion;
use App\Models\DashboardUnidadAsignacion;
use App\Models\DashboardUsuarioAsignacion;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Models\User;
use App\Support\RoleLabels;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardMotorController extends Controller
{
    private const CHART_TYPE_OPTIONS = [
        'procesos_por_area' => ['bar', 'line', 'pie', 'doughnut', 'polarArea', 'radar'],
        'procesos_por_estado' => ['doughnut', 'pie', 'bar', 'polarArea', 'radar'],
        'contratos_por_mes' => ['line', 'bar', 'area'],
    ];
    private const DATA_SCOPE_OPTIONS = ['usuario', 'unidad', 'secretaria', 'global'];
    private const WIDGET_LIBRARY = [
        ['metrica' => 'procesos_en_curso', 'titulo' => 'Procesos en curso', 'tipo' => 'kpi'],
        ['metrica' => 'procesos_finalizados_mes', 'titulo' => 'Finalizados del mes', 'tipo' => 'kpi'],
        ['metrica' => 'alertas_altas_no_leidas', 'titulo' => 'Alertas altas sin leer', 'tipo' => 'kpi'],
        ['metrica' => 'contratos_vigentes', 'titulo' => 'Contratos vigentes', 'tipo' => 'kpi'],
        ['metrica' => 'contratos_por_vencer_90', 'titulo' => 'Contratos por vencer (90 dias)', 'tipo' => 'kpi'],
        ['metrica' => 'valor_total_contratos', 'titulo' => 'Valor total contratos', 'tipo' => 'kpi'],
        ['metrica' => 'procesos_por_area', 'titulo' => 'Procesos por area', 'tipo' => 'chart'],
        ['metrica' => 'procesos_por_estado', 'titulo' => 'Procesos por estado', 'tipo' => 'chart'],
        ['metrica' => 'contratos_por_mes', 'titulo' => 'Contratos por mes', 'tipo' => 'chart'],
    ];

    public function index(Request $request): View
    {
        $plantillas = DashboardPlantilla::with(['widgets' => fn ($q) => $q->where('activo', true)->orderBy('orden')])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $asignacionesRolColeccion = DashboardRolAsignacion::query()
            ->get()
            ->keyBy('role_name');

        $usuarios = User::query()
            ->select(['id', 'name', 'email', 'secretaria_id'])
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();

        $asignacionesUsuario = DashboardUsuarioAsignacion::query()
            ->with(['plantilla:id,nombre'])
            ->get()
            ->keyBy('user_id');

        $secretarias = Secretaria::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get();

        $asignacionesSecretaria = DashboardSecretariaAsignacion::query()
            ->with(['plantilla:id,nombre'])
            ->get()
            ->keyBy('secretaria_id');

        $unidades = Unidad::query()
            ->select(['id', 'nombre', 'secretaria_id'])
            ->orderBy('nombre')
            ->get();

        $asignacionesUnidad = DashboardUnidadAsignacion::query()
            ->with(['plantilla:id,nombre'])
            ->get()
            ->keyBy('unidad_id');

        $chartTypeOptions = self::CHART_TYPE_OPTIONS;
        $widgetLibrary = self::WIDGET_LIBRARY;
        $dataScopeOptions = self::DATA_SCOPE_OPTIONS;

        $historialAsignaciones = DashboardAsignacionAuditoria::query()
            ->with([
                'actor:id,name,email',
                'targetUser:id,name,email',
                'plantillaAnterior:id,nombre',
                'plantillaNueva:id,nombre',
            ])
            ->latest('id')
            ->limit(25)
            ->get();

        $rolesMotor = $roles->map(function ($role) use ($asignacionesRolColeccion) {
            $asignacion = $asignacionesRolColeccion[$role->name] ?? null;
            $dataScope = $asignacion?->config_json['data_scope'] ?? $this->inferRoleDataScope($role->name);

            return [
                'name' => $role->name,
                'label' => RoleLabels::label($role->name),
                'templateId' => $asignacion?->dashboard_plantilla_id,
                'chartTypes' => $asignacion?->config_json['chart_types'] ?? [],
                'customWidgets' => $asignacion?->config_json['custom_widgets'] ?? [],
                'dataScope' => $dataScope,
            ];
        })->values();

        $secretariasMotor = $secretarias->map(function ($secretaria) use ($asignacionesSecretaria) {
            $asignacion = $asignacionesSecretaria[$secretaria->id] ?? null;

            return [
                'id' => $secretaria->id,
                'nombre' => $secretaria->nombre,
                'templateId' => $asignacion?->dashboard_plantilla_id,
                'chartTypes' => $asignacion?->config_json['chart_types'] ?? [],
                'customWidgets' => $asignacion?->config_json['custom_widgets'] ?? [],
                'dataScope' => $asignacion?->config_json['data_scope'] ?? 'secretaria',
            ];
        })->values();

        $unidadesMotor = $unidades->map(function ($unidad) use ($asignacionesUnidad) {
            $asignacion = $asignacionesUnidad[$unidad->id] ?? null;

            return [
                'id' => $unidad->id,
                'nombre' => $unidad->nombre,
                'secretaria_id' => $unidad->secretaria_id,
                'templateId' => $asignacion?->dashboard_plantilla_id,
                'chartTypes' => $asignacion?->config_json['chart_types'] ?? [],
                'customWidgets' => $asignacion?->config_json['custom_widgets'] ?? [],
                'dataScope' => $asignacion?->config_json['data_scope'] ?? 'unidad',
            ];
        })->values();

        $usuariosMotor = $usuarios->map(function ($usuario) use ($asignacionesUsuario) {
            $asignacion = $asignacionesUsuario[$usuario->id] ?? null;
            $rolesUsuario = $usuario->roles->pluck('name')->values()->all();

            return [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'secretaria_id' => $usuario->secretaria_id,
                'unidad_id' => $usuario->unidad_id,
                'roles' => $rolesUsuario,
                'templateId' => $asignacion?->dashboard_plantilla_id,
                'chartTypes' => $asignacion?->config_json['chart_types'] ?? [],
                'customWidgets' => $asignacion?->config_json['custom_widgets'] ?? [],
                'dataScope' => $asignacion?->config_json['data_scope'] ?? 'usuario',
            ];
        })->values();

        $plantillasMotor = $plantillas->map(function ($plantilla) {
            return [
                'id' => $plantilla->id,
                'nombre' => $plantilla->nombre,
                'descripcion' => $plantilla->descripcion,
                'widgets' => $plantilla->widgets->map(fn ($w) => [
                    'id' => $w->id,
                    'titulo' => $w->titulo,
                    'tipo' => $w->tipo,
                    'metrica' => $w->metrica,
                ])->values()->all(),
            ];
        })->values();

        return view('dashboards.motor.interactivo', compact(
            'rolesMotor',
            'secretariasMotor',
            'unidadesMotor',
            'usuariosMotor',
            'plantillasMotor',
            'historialAsignaciones',
            'chartTypeOptions',
            'widgetLibrary',
            'dataScopeOptions'
        ));
    }

    public function guardarAsignaciones(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'asignaciones' => ['required', 'array'],
            'asignaciones.*' => ['nullable', 'integer', 'exists:dashboard_plantillas,id'],
            'chart_types_role' => ['nullable', 'array'],
            'chart_types_role.*' => ['nullable', 'array'],
            'custom_widgets_role' => ['nullable', 'array'],
            'custom_widgets_role.*' => ['nullable', 'array'],
            'data_scope_role' => ['nullable', 'array'],
        ]);

        foreach ($validated['asignaciones'] as $roleName => $plantillaId) {
            $chartTypesInput = $validated['chart_types_role'][$roleName] ?? [];
            $chartTypes = $this->sanitizeChartTypes(is_array($chartTypesInput) ? $chartTypesInput : []);
            $customWidgetsInput = $validated['custom_widgets_role'][$roleName] ?? [];
            $customWidgets = $this->normalizeCustomWidgets(is_array($customWidgetsInput) ? $customWidgetsInput : []);
            $dataScopeInput = $validated['data_scope_role'][$roleName] ?? null;
            $dataScope = $this->sanitizeDataScope(is_string($dataScopeInput) ? $dataScopeInput : null, 'rol', null, $roleName);
            $asignacionActual = DashboardRolAsignacion::query()
                ->where('role_name', $roleName)
                ->first();
            $anteriorId = $asignacionActual?->dashboard_plantilla_id;

            if (!$plantillaId && empty($customWidgets)) {
                DashboardRolAsignacion::query()->where('role_name', $roleName)->delete();
                if ($anteriorId !== null) {
                    $this->registrarAuditoria(
                        tipoObjetivo: 'rol',
                        accion: 'remove',
                        roleName: $roleName,
                        targetUserId: null,
                        anteriorId: $anteriorId,
                        nuevoId: null,
                        metadata: ['origen' => 'motor_dashboard']
                    );
                }
                continue;
            }

            $resolvedTemplateId = $plantillaId ?: $this->getOrCreateCustomBaseTemplateId();

            $configJson = [
                'chart_types' => $chartTypes,
                'custom_widgets' => $customWidgets,
                'data_scope' => $dataScope,
            ];

            if ($asignacionActual
                && (int) $asignacionActual->dashboard_plantilla_id === (int) $resolvedTemplateId
                && ($asignacionActual->config_json['chart_types'] ?? []) == $chartTypes
                && ($asignacionActual->config_json['custom_widgets'] ?? []) == $customWidgets
                && ($asignacionActual->config_json['data_scope'] ?? $this->inferRoleDataScope($roleName)) === $dataScope
            ) {
                continue;
            }

            DashboardRolAsignacion::query()->updateOrCreate(
                ['role_name' => $roleName],
                [
                    'dashboard_plantilla_id' => $resolvedTemplateId,
                    'prioridad' => 100,
                    'config_json' => $configJson,
                    'activo' => true,
                ]
            );

            $this->registrarAuditoria(
                tipoObjetivo: 'rol',
                accion: $anteriorId ? 'update' : 'set',
                roleName: $roleName,
                targetUserId: null,
                anteriorId: $anteriorId,
                nuevoId: (int) $resolvedTemplateId,
                metadata: ['origen' => 'motor_dashboard']
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Asignaciones de dashboards actualizadas correctamente.',
            ]);
        }

        return back()->with('success', 'Asignaciones de dashboards actualizadas correctamente.');
    }

    public function guardarAsignacionUsuario(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'dashboard_plantilla_id' => ['nullable', 'integer', 'exists:dashboard_plantillas,id'],
            'chart_types_usuario' => ['nullable', 'array'],
            'custom_widgets_usuario' => ['nullable', 'array'],
            'data_scope_usuario' => ['nullable', 'string'],
        ]);

        $usuario = User::query()->with('roles:id,name')->findOrFail($validated['user_id']);
        $rolesUsuario = $usuario->getRoleNames()->all();

        $plantillaId = $validated['dashboard_plantilla_id'] ?? null;
        $chartTypes = $this->sanitizeChartTypes((array) ($validated['chart_types_usuario'] ?? []));
        $customWidgets = $this->normalizeCustomWidgets((array) ($validated['custom_widgets_usuario'] ?? []));
        $dataScope = $this->sanitizeDataScope($validated['data_scope_usuario'] ?? null, 'usuario', $usuario);
        $asignacionUsuarioActual = DashboardUsuarioAsignacion::query()
            ->where('user_id', $usuario->id)
            ->first();
        $anteriorId = $asignacionUsuarioActual?->dashboard_plantilla_id;

        if (!$plantillaId && empty($customWidgets)) {
            DashboardUsuarioAsignacion::query()->where('user_id', $usuario->id)->delete();

            if ($anteriorId !== null) {
                $this->registrarAuditoria(
                    tipoObjetivo: 'usuario',
                    accion: 'remove',
                    roleName: null,
                    targetUserId: (int) $usuario->id,
                    anteriorId: $anteriorId,
                    nuevoId: null,
                    metadata: [
                        'roles_usuario' => array_values($rolesUsuario),
                        'origen' => 'motor_dashboard',
                    ]
                );
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Se removio la asignacion personalizada del usuario.',
                ]);
            }

            return back()->with('success', 'Se removio la asignacion personalizada del usuario.');
        }

        $resolvedTemplateId = $plantillaId ?: $this->getOrCreateCustomBaseTemplateId();

        if ($asignacionUsuarioActual
            && (int) $asignacionUsuarioActual->dashboard_plantilla_id === (int) $resolvedTemplateId
            && ($asignacionUsuarioActual->config_json['chart_types'] ?? []) == $chartTypes
            && ($asignacionUsuarioActual->config_json['custom_widgets'] ?? []) == $customWidgets
            && ($asignacionUsuarioActual->config_json['data_scope'] ?? 'usuario') === $dataScope
        ) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Sin cambios para guardar.']);
            }
            return back()->with('success', 'Sin cambios para guardar.');
        }

        if ((int) $anteriorId !== (int) $resolvedTemplateId) {
            $this->registrarAuditoria(
                tipoObjetivo: 'usuario',
                accion: $anteriorId ? 'update' : 'set',
                roleName: null,
                targetUserId: (int) $usuario->id,
                anteriorId: $anteriorId,
                nuevoId: (int) $resolvedTemplateId,
                metadata: [
                    'roles_usuario' => array_values($rolesUsuario),
                    'origen' => 'motor_dashboard',
                ]
            );
        }

        DashboardUsuarioAsignacion::query()->updateOrCreate(
            ['user_id' => $usuario->id],
            [
                'dashboard_plantilla_id' => $resolvedTemplateId,
                'prioridad' => 1,
                'config_json' => [
                    'chart_types' => $chartTypes,
                    'custom_widgets' => $customWidgets,
                    'data_scope' => $dataScope,
                ],
                'activo' => true,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Asignacion de dashboard por usuario guardada correctamente.',
            ]);
        }

        return back()->with('success', 'Asignacion de dashboard por usuario guardada correctamente.');
    }

    public function guardarAsignacionSecretaria(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'secretaria_id' => ['required', 'integer', 'exists:secretarias,id'],
            'dashboard_plantilla_id' => ['nullable', 'integer', 'exists:dashboard_plantillas,id'],
            'chart_types_secretaria' => ['nullable', 'array'],
            'custom_widgets_secretaria' => ['nullable', 'array'],
            'data_scope_secretaria' => ['nullable', 'string'],
        ]);

        $secretaria = Secretaria::query()->findOrFail((int) $validated['secretaria_id']);
        $plantillaId = $validated['dashboard_plantilla_id'] ?? null;
        $chartTypes = $this->sanitizeChartTypes((array) ($validated['chart_types_secretaria'] ?? []));
        $customWidgets = $this->normalizeCustomWidgets((array) ($validated['custom_widgets_secretaria'] ?? []));
        $dataScope = $this->sanitizeDataScope($validated['data_scope_secretaria'] ?? null, 'secretaria');

        $asignacionActual = DashboardSecretariaAsignacion::query()
            ->where('secretaria_id', $secretaria->id)
            ->first();
        $anteriorId = $asignacionActual?->dashboard_plantilla_id;

        if (!$plantillaId && empty($customWidgets)) {
            DashboardSecretariaAsignacion::query()->where('secretaria_id', $secretaria->id)->delete();

            if ($anteriorId !== null) {
                $this->registrarAuditoria(
                    tipoObjetivo: 'secretaria',
                    accion: 'remove',
                    roleName: null,
                    targetUserId: null,
                    anteriorId: $anteriorId,
                    nuevoId: null,
                    metadata: [
                        'secretaria_id' => $secretaria->id,
                        'secretaria_nombre' => $secretaria->nombre,
                        'origen' => 'motor_dashboard',
                    ]
                );
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Se removio la asignacion de dashboard para la secretaria.',
                ]);
            }

            return back()->with('success', 'Se removio la asignacion de dashboard para la secretaria.');
        }

        $resolvedTemplateId = $plantillaId ?: $this->getOrCreateCustomBaseTemplateId();

        if ($asignacionActual
            && (int) $asignacionActual->dashboard_plantilla_id === (int) $resolvedTemplateId
            && ($asignacionActual->config_json['chart_types'] ?? []) == $chartTypes
            && ($asignacionActual->config_json['custom_widgets'] ?? []) == $customWidgets
            && ($asignacionActual->config_json['data_scope'] ?? 'secretaria') === $dataScope
        ) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Sin cambios para guardar.']);
            }
            return back()->with('success', 'Sin cambios para guardar.');
        }

        DashboardSecretariaAsignacion::query()->updateOrCreate(
            ['secretaria_id' => $secretaria->id],
            [
                'dashboard_plantilla_id' => $resolvedTemplateId,
                'prioridad' => 50,
                'config_json' => [
                    'chart_types' => $chartTypes,
                    'custom_widgets' => $customWidgets,
                    'data_scope' => $dataScope,
                ],
                'activo' => true,
            ]
        );

        if ((int) $anteriorId !== (int) $resolvedTemplateId) {
            $this->registrarAuditoria(
                tipoObjetivo: 'secretaria',
                accion: $anteriorId ? 'update' : 'set',
                roleName: null,
                targetUserId: null,
                anteriorId: $anteriorId,
                nuevoId: (int) $resolvedTemplateId,
                metadata: [
                    'secretaria_id' => $secretaria->id,
                    'secretaria_nombre' => $secretaria->nombre,
                    'origen' => 'motor_dashboard',
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Asignacion de dashboard por secretaria guardada correctamente.',
            ]);
        }

        return back()->with('success', 'Asignacion de dashboard por secretaria guardada correctamente.');
    }

    public function guardarAsignacionUnidad(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'unidad_id' => ['required', 'integer', 'exists:unidades,id'],
            'dashboard_plantilla_id' => ['nullable', 'integer', 'exists:dashboard_plantillas,id'],
            'chart_types_unidad' => ['nullable', 'array'],
            'custom_widgets_unidad' => ['nullable', 'array'],
            'data_scope_unidad' => ['nullable', 'string'],
        ]);

        $unidad = Unidad::query()->findOrFail((int) $validated['unidad_id']);
        $plantillaId = $validated['dashboard_plantilla_id'] ?? null;
        $chartTypes = $this->sanitizeChartTypes((array) ($validated['chart_types_unidad'] ?? []));
        $customWidgets = $this->normalizeCustomWidgets((array) ($validated['custom_widgets_unidad'] ?? []));
        $dataScope = $this->sanitizeDataScope($validated['data_scope_unidad'] ?? null, 'unidad');

        $asignacionActual = DashboardUnidadAsignacion::query()
            ->where('unidad_id', $unidad->id)
            ->first();
        $anteriorId = $asignacionActual?->dashboard_plantilla_id;

        if (!$plantillaId && empty($customWidgets)) {
            DashboardUnidadAsignacion::query()->where('unidad_id', $unidad->id)->delete();

            if ($anteriorId !== null) {
                $this->registrarAuditoria(
                    tipoObjetivo: 'unidad',
                    accion: 'remove',
                    roleName: null,
                    targetUserId: null,
                    anteriorId: $anteriorId,
                    nuevoId: null,
                    metadata: [
                        'unidad_id' => $unidad->id,
                        'unidad_nombre' => $unidad->nombre,
                        'origen' => 'motor_dashboard',
                    ]
                );
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Se removio la asignacion de dashboard para la unidad.',
                ]);
            }

            return back()->with('success', 'Se removio la asignacion de dashboard para la unidad.');
        }

        $resolvedTemplateId = $plantillaId ?: $this->getOrCreateCustomBaseTemplateId();

        if ($asignacionActual
            && (int) $asignacionActual->dashboard_plantilla_id === (int) $resolvedTemplateId
            && ($asignacionActual->config_json['chart_types'] ?? []) == $chartTypes
            && ($asignacionActual->config_json['custom_widgets'] ?? []) == $customWidgets
            && ($asignacionActual->config_json['data_scope'] ?? 'unidad') === $dataScope
        ) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Sin cambios para guardar.']);
            }
            return back()->with('success', 'Sin cambios para guardar.');
        }

        DashboardUnidadAsignacion::query()->updateOrCreate(
            ['unidad_id' => $unidad->id],
            [
                'dashboard_plantilla_id' => $resolvedTemplateId,
                'prioridad' => 40,
                'config_json' => [
                    'chart_types' => $chartTypes,
                    'custom_widgets' => $customWidgets,
                    'data_scope' => $dataScope,
                ],
                'activo' => true,
            ]
        );

        if ((int) $anteriorId !== (int) $resolvedTemplateId) {
            $this->registrarAuditoria(
                tipoObjetivo: 'unidad',
                accion: $anteriorId ? 'update' : 'set',
                roleName: null,
                targetUserId: null,
                anteriorId: $anteriorId,
                nuevoId: (int) $resolvedTemplateId,
                metadata: [
                    'unidad_id' => $unidad->id,
                    'unidad_nombre' => $unidad->nombre,
                    'origen' => 'motor_dashboard',
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Asignacion de dashboard por unidad guardada correctamente.',
            ]);
        }

        return back()->with('success', 'Asignacion de dashboard por unidad guardada correctamente.');
    }

    private function sanitizeChartTypes(array $chartTypes): array
    {
        $sanitized = [];

        foreach (self::CHART_TYPE_OPTIONS as $metric => $allowedTypes) {
            $value = $chartTypes[$metric] ?? null;
            if (!is_string($value) || $value === '') {
                continue;
            }

            if (in_array($value, $allowedTypes, true)) {
                $sanitized[$metric] = $value;
            }
        }

        return $sanitized;
    }

    private function normalizeCustomWidgets(array $customWidgets): array
    {
        $allowedMetrics = collect(self::WIDGET_LIBRARY)->pluck('metrica')->all();

        return collect($customWidgets)
            ->filter(fn ($item) => is_array($item))
            ->map(function ($item, $index) use ($allowedMetrics) {
                $metrica = (string) ($item['metrica'] ?? '');
                $tipo = (string) ($item['tipo'] ?? '');
                $titulo = trim((string) ($item['titulo'] ?? ''));

                if (!in_array($metrica, $allowedMetrics, true)) {
                    return null;
                }

                if (!in_array($tipo, ['kpi', 'chart'], true)) {
                    return null;
                }

                return [
                    'id' => (string) ($item['id'] ?? ($metrica . '_' . $index)),
                    'titulo' => $titulo !== '' ? $titulo : ucfirst(str_replace('_', ' ', $metrica)),
                    'tipo' => $tipo,
                    'metrica' => $metrica,
                    'orden' => (int) ($item['orden'] ?? ($index + 1)),
                ];
            })
            ->filter()
            ->sortBy('orden')
            ->values()
            ->all();
    }

    private function getOrCreateCustomBaseTemplateId(): int
    {
        $base = DashboardPlantilla::query()->firstOrCreate(
            ['slug' => 'dashboard-custom-base'],
            [
                'nombre' => 'Dashboard Personalizado',
                'descripcion' => 'Plantilla base para dashboards construidos desde cero.',
                'activo' => true,
            ]
        );

        return (int) $base->id;
    }

    private function sanitizeDataScope(?string $scope, string $targetType, ?User $targetUser = null, ?string $roleName = null): string
    {
        $scopeValue = is_string($scope) ? trim($scope) : '';
        $scopeValue = in_array($scopeValue, self::DATA_SCOPE_OPTIONS, true) ? $scopeValue : '';

        if ($scopeValue === '') {
            return match ($targetType) {
                'rol' => $this->inferRoleDataScope($roleName),
                'secretaria' => 'secretaria',
                'unidad' => 'unidad',
                default => 'usuario',
            };
        }

        if ($targetType === 'secretaria' && !in_array($scopeValue, ['secretaria', 'global'], true)) {
            return 'secretaria';
        }

        if ($targetType === 'unidad' && !in_array($scopeValue, ['unidad', 'secretaria', 'global'], true)) {
            return 'unidad';
        }

        if ($targetType === 'usuario') {
            if ($scopeValue === 'secretaria' && empty($targetUser?->secretaria_id)) {
                return 'usuario';
            }
            if ($scopeValue === 'unidad' && empty($targetUser?->unidad_id)) {
                return 'usuario';
            }
        }

        return $scopeValue;
    }

    private function inferRoleDataScope(?string $roleName): string
    {
        return match ($roleName) {
            'gobernador', 'admin', 'admin_general' => 'global',
            'secretario' => 'secretaria',
            'jefe_unidad' => 'unidad',
            default => 'usuario',
        };
    }

    private function registrarAuditoria(
        string $tipoObjetivo,
        string $accion,
        ?string $roleName,
        ?int $targetUserId,
        ?int $anteriorId,
        ?int $nuevoId,
        array $metadata = []
    ): void {
        DashboardAsignacionAuditoria::query()->create([
            'actor_user_id' => auth()->id(),
            'tipo_objetivo' => $tipoObjetivo,
            'role_name' => $roleName,
            'target_user_id' => $targetUserId,
            'accion' => $accion,
            'dashboard_plantilla_anterior_id' => $anteriorId,
            'dashboard_plantilla_nueva_id' => $nuevoId,
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }
}
