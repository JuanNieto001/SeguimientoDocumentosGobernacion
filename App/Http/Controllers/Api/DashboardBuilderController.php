<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DynamicQueryEngine;
use App\Services\Dashboard\ScopeFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\DashboardPlantilla;
use App\Models\DashboardWidget;

class DashboardBuilderController extends Controller
{
    protected DynamicQueryEngine $queryEngine;
    protected ScopeFilterService $scopeFilter;

    public function __construct(DynamicQueryEngine $queryEngine, ScopeFilterService $scopeFilter)
    {
        $this->queryEngine = $queryEngine;
        $this->scopeFilter = $scopeFilter;
    }

    /**
     * Obtener catálogo de entidades y campos disponibles para construcción.
     */
    public function catalog(): JsonResponse
    {
        $catalog = $this->queryEngine->getEntityCatalog();

        return response()->json([
            'success' => true,
            'data' => [
                'entities' => $catalog,
                'operators' => $this->getFilterOperators(),
                'aggregations' => $this->getAggregations(),
                'widgetTypes' => $this->getWidgetTypes(),
                'chartTypes' => $this->getChartTypes(),
            ],
        ]);
    }

    /**
     * Obtener información del scope del usuario actual.
     */
    public function userScope(): JsonResponse
    {
        $user = Auth::user();
        $scopeInfo = $this->scopeFilter->getUserScopeInfo($user);

        return response()->json([
            'success' => true,
            'data' => $scopeInfo,
        ]);
    }

    /**
     * Ejecutar query para un widget específico.
     */
    public function executeWidget(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity' => 'required|string',
            'tipo' => 'required|string|in:kpi,chart,table,timeline,heatmap',
            'metrica' => 'nullable|string',
            'dimension' => 'nullable|string',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'limit' => 'nullable|integer|min:1|max:1000',
            'orderBy' => 'nullable|string',
            'orderDir' => 'nullable|string|in:asc,desc',
            'chartType' => 'nullable|string',
            'xDimension' => 'nullable|string',
            'yDimension' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validación fallida',
                'details' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $config = $request->all();

        // Ejecutar query con scope automático
        $result = $this->queryEngine->executeWidgetQuery($config, $user);

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => array_merge($result['meta'] ?? [], [
                'scope' => $this->scopeFilter->resolveUserScopeLevel($user),
            ]),
        ]);
    }

    /**
     * Ejecutar múltiples widgets de un dashboard completo.
     */
    public function executeDashboard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|string',
            'widgets.*.entity' => 'required|string',
            'widgets.*.tipo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validación fallida',
                'details' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $widgets = $request->input('widgets');
        $results = [];

        foreach ($widgets as $widgetConfig) {
            $widgetId = $widgetConfig['id'];
            $result = $this->queryEngine->executeWidgetQuery($widgetConfig, $user);

            $results[$widgetId] = [
                'success' => !isset($result['error']),
                'data' => $result['data'] ?? null,
                'meta' => $result['meta'] ?? null,
                'error' => $result['error'] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'widgets' => $results,
            'meta' => [
                'scope' => $this->scopeFilter->resolveUserScopeLevel($user),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Guardar configuración de dashboard del usuario.
     */
    public function saveDashboard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'widgets' => 'required|array',
            'layout' => 'nullable|array',
            'theme' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validación fallida',
                'details' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        // Generar slug único
        $slug = \Str::slug($request->input('name')) . '-' . $user->id . '-' . time();

        // Crear o actualizar plantilla
        $plantilla = DashboardPlantilla::updateOrCreate(
            [
                'slug' => $request->input('dashboard_id') ? null : $slug,
                'id' => $request->input('dashboard_id'),
            ],
            [
                'nombre' => $request->input('name'),
                'slug' => $slug,
                'descripcion' => $request->input('description'),
                'config_json' => [
                    'layout' => $request->input('layout', []),
                    'theme' => $request->input('theme', 'default'),
                    'created_by' => $user->id,
                    'widgets' => $request->input('widgets'),
                ],
                'activo' => true,
            ]
        );

        // Registrar asignación al usuario
        \App\Models\DashboardUsuarioAsignacion::updateOrCreate(
            ['user_id' => $user->id],
            [
                'dashboard_plantilla_id' => $plantilla->id,
                'prioridad' => 1,
                'config_json' => [
                    'widgets' => $request->input('widgets'),
                    'layout' => $request->input('layout'),
                ],
                'activo' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $plantilla->id,
                'slug' => $plantilla->slug,
                'name' => $plantilla->nombre,
            ],
            'message' => 'Dashboard guardado exitosamente',
        ]);
    }

    /**
     * Cargar dashboard guardado del usuario.
     */
    public function loadDashboard(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Buscar asignación del usuario
        $asignacion = \App\Models\DashboardUsuarioAsignacion::where('user_id', $user->id)
            ->where('activo', true)
            ->with('plantilla')
            ->first();

        if (!$asignacion) {
            // Buscar por unidad, secretaría o rol
            $asignacion = $this->findBestAssignment($user);
        }

        if (!$asignacion) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No hay dashboard configurado',
            ]);
        }

        $config = $asignacion->config_json ?? $asignacion->plantilla?->config_json ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $asignacion->plantilla?->id,
                'name' => $asignacion->plantilla?->nombre,
                'widgets' => $config['widgets'] ?? [],
                'layout' => $config['layout'] ?? [],
                'theme' => $config['theme'] ?? 'default',
            ],
        ]);
    }

    /**
     * Obtener valores únicos de un campo para filtros.
     */
    public function fieldValues(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity' => 'required|string',
            'field' => 'required|string',
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validación fallida',
            ], 422);
        }

        $user = Auth::user();
        $entity = $request->input('entity');
        $field = $request->input('field');
        $search = $request->input('search', '');
        $limit = $request->input('limit', 50);

        $registry = $this->queryEngine->getEntityRegistry();

        if (!isset($registry[$entity])) {
            return response()->json([
                'success' => false,
                'error' => 'Entidad no válida',
            ], 400);
        }

        $entityConfig = $registry[$entity];

        if (!isset($entityConfig['fields'][$field])) {
            return response()->json([
                'success' => false,
                'error' => 'Campo no válido',
            ], 400);
        }

        $modelClass = $entityConfig['model'];
        $query = $modelClass::query();

        // Aplicar scope del usuario
        $query = $this->scopeFilter->applyUserScope($query, $user, $entityConfig);

        // Filtrar por búsqueda si aplica
        if ($search) {
            $query->where($field, 'like', "%{$search}%");
        }

        // Obtener valores únicos
        $values = $query->select($field)
            ->distinct()
            ->whereNotNull($field)
            ->limit($limit)
            ->pluck($field)
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $values,
        ]);
    }

    /**
     * Preview de widget antes de guardar.
     */
    public function previewWidget(Request $request): JsonResponse
    {
        return $this->executeWidget($request);
    }

    /**
     * Buscar mejor asignación para el usuario.
     */
    protected function findBestAssignment($user)
    {
        // Por unidad
        if ($user->unidad_id) {
            $asignacion = \App\Models\DashboardUnidadAsignacion::where('unidad_id', $user->unidad_id)
                ->where('activo', true)
                ->with('plantilla')
                ->first();
            if ($asignacion) return $asignacion;
        }

        // Por secretaría
        if ($user->secretaria_id) {
            $asignacion = \App\Models\DashboardSecretariaAsignacion::where('secretaria_id', $user->secretaria_id)
                ->where('activo', true)
                ->with('plantilla')
                ->first();
            if ($asignacion) return $asignacion;
        }

        // Por rol
        $roles = $user->getRoleNames()->toArray();
        if (!empty($roles)) {
            $asignacion = \App\Models\DashboardRolAsignacion::whereIn('role_name', $roles)
                ->where('activo', true)
                ->orderBy('prioridad', 'asc')
                ->with('plantilla')
                ->first();
            if ($asignacion) return $asignacion;
        }

        return null;
    }

    /**
     * Operadores de filtro disponibles.
     */
    protected function getFilterOperators(): array
    {
        return [
            ['value' => '=', 'label' => 'Igual a', 'types' => ['string', 'integer', 'decimal', 'enum', 'boolean']],
            ['value' => '!=', 'label' => 'Diferente de', 'types' => ['string', 'integer', 'decimal', 'enum', 'boolean']],
            ['value' => '>', 'label' => 'Mayor que', 'types' => ['integer', 'decimal', 'datetime']],
            ['value' => '<', 'label' => 'Menor que', 'types' => ['integer', 'decimal', 'datetime']],
            ['value' => '>=', 'label' => 'Mayor o igual', 'types' => ['integer', 'decimal', 'datetime']],
            ['value' => '<=', 'label' => 'Menor o igual', 'types' => ['integer', 'decimal', 'datetime']],
            ['value' => 'like', 'label' => 'Contiene', 'types' => ['string']],
            ['value' => 'in', 'label' => 'En lista', 'types' => ['string', 'integer', 'enum']],
            ['value' => 'not_in', 'label' => 'No en lista', 'types' => ['string', 'integer', 'enum']],
            ['value' => 'is_null', 'label' => 'Es vacío', 'types' => ['string', 'integer', 'decimal', 'relation']],
            ['value' => 'is_not_null', 'label' => 'No es vacío', 'types' => ['string', 'integer', 'decimal', 'relation']],
            ['value' => 'between', 'label' => 'Entre', 'types' => ['integer', 'decimal', 'datetime']],
            ['value' => 'date_range', 'label' => 'Rango de fechas', 'types' => ['datetime']],
            ['value' => 'this_month', 'label' => 'Este mes', 'types' => ['datetime']],
            ['value' => 'this_year', 'label' => 'Este año', 'types' => ['datetime']],
            ['value' => 'last_n_days', 'label' => 'Últimos N días', 'types' => ['datetime']],
        ];
    }

    /**
     * Tipos de agregación disponibles.
     */
    protected function getAggregations(): array
    {
        return [
            ['value' => 'count', 'label' => 'Contar', 'icon' => 'hashtag'],
            ['value' => 'sum', 'label' => 'Sumar', 'icon' => 'plus'],
            ['value' => 'avg', 'label' => 'Promedio', 'icon' => 'chart-line'],
            ['value' => 'max', 'label' => 'Máximo', 'icon' => 'arrow-up'],
            ['value' => 'min', 'label' => 'Mínimo', 'icon' => 'arrow-down'],
        ];
    }

    /**
     * Tipos de widget disponibles.
     */
    protected function getWidgetTypes(): array
    {
        return [
            [
                'value' => 'kpi',
                'label' => 'KPI / Indicador',
                'icon' => 'tachometer-alt',
                'description' => 'Muestra un valor numérico destacado',
                'requiresDimension' => false,
            ],
            [
                'value' => 'chart',
                'label' => 'Gráfica',
                'icon' => 'chart-bar',
                'description' => 'Visualización gráfica de datos',
                'requiresDimension' => true,
            ],
            [
                'value' => 'table',
                'label' => 'Tabla',
                'icon' => 'table',
                'description' => 'Listado de registros',
                'requiresDimension' => false,
            ],
            [
                'value' => 'timeline',
                'label' => 'Línea de tiempo',
                'icon' => 'stream',
                'description' => 'Evolución temporal de datos',
                'requiresDimension' => true,
            ],
            [
                'value' => 'heatmap',
                'label' => 'Mapa de calor',
                'icon' => 'th',
                'description' => 'Matriz de intensidad',
                'requiresDimension' => true,
            ],
        ];
    }

    /**
     * Tipos de gráfica disponibles.
     */
    protected function getChartTypes(): array
    {
        return [
            ['value' => 'bar', 'label' => 'Barras', 'icon' => 'chart-bar'],
            ['value' => 'line', 'label' => 'Líneas', 'icon' => 'chart-line'],
            ['value' => 'pie', 'label' => 'Pastel', 'icon' => 'chart-pie'],
            ['value' => 'doughnut', 'label' => 'Dona', 'icon' => 'circle-notch'],
            ['value' => 'area', 'label' => 'Área', 'icon' => 'chart-area'],
            ['value' => 'polarArea', 'label' => 'Área polar', 'icon' => 'compass'],
            ['value' => 'radar', 'label' => 'Radar', 'icon' => 'spider'],
        ];
    }
}
