<?php
/**
 * Archivo: backend/App/Http/Controllers/Api/DashboardBuilderController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardPlantilla;
use App\Models\DashboardUsuarioAsignacion;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardBuilderController extends Controller
{
    private const ENTITY_CATALOG = [
        'procesos' => ['table' => 'procesos', 'label' => 'Procesos'],
        'alertas' => ['table' => 'alertas', 'label' => 'Alertas'],
        'contratos_aplicaciones' => ['table' => 'contratos_aplicaciones', 'label' => 'Contratos'],
        'usuarios' => ['table' => 'users', 'label' => 'Usuarios'],
        'secretarias' => ['table' => 'secretarias', 'label' => 'Secretarias'],
        'unidades' => ['table' => 'unidades', 'label' => 'Unidades'],
        'plan_anual_adquisiciones' => ['table' => 'plan_anual_adquisiciones', 'label' => 'Plan Anual Adquisiciones'],
    ];

    private const ENTITY_ALIASES = [
        'contratos' => 'contratos_aplicaciones',
        'users' => 'usuarios',
        'paa' => 'plan_anual_adquisiciones',
    ];

    private const GLOBAL_ROLES = ['admin', 'admin_general', 'gobernador'];

    public function entities(): JsonResponse
    {
        $entities = [];

        foreach (self::ENTITY_CATALOG as $key => $config) {
            if (!Schema::hasTable($config['table'])) {
                continue;
            }

            $entities[] = [
                'key' => $key,
                'label' => $config['label'],
                'fields' => $this->buildFieldMap($config['table']),
            ];
        }

        return response()->json([
            'success' => true,
            'entities' => $entities,
        ]);
    }

    public function fields(string $entity): JsonResponse
    {
        $resolved = $this->resolveEntity($entity);

        if (!$resolved) {
            return response()->json([
                'success' => false,
                'message' => 'Entidad no encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'entity' => $resolved['key'],
            'fields' => $this->buildFieldMap($resolved['table']),
        ]);
    }

    public function entityStats(string $entity, Request $request): JsonResponse
    {
        $resolved = $this->resolveEntity($entity);

        if (!$resolved) {
            return response()->json([
                'success' => false,
                'message' => 'Entidad no encontrada.',
            ], 404);
        }

        $table = $resolved['table'];
        $query = DB::table($table);
        $scope = $this->applyUserScope($query, $table, $request->user());

        $total = (clone $query)->count();

        $last30Days = 0;
        if (Schema::hasColumn($table, 'created_at')) {
            $last30Days = (clone $query)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
        }

        return response()->json([
            'success' => true,
            'entity' => $resolved['key'],
            'scope' => $scope,
            'total' => $total,
            'last_30_days' => $last30Days,
            'fields_count' => count($this->buildFieldMap($table)),
        ]);
    }

    public function executeWidget(Request $request): JsonResponse
    {
        [$status, $payload] = $this->runWidgetQuery($request->all(), $request);

        return response()->json($payload, $status);
    }

    public function catalog(): JsonResponse
    {
        return $this->entities();
    }

    public function userScope(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values()->all() : [];

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'secretaria_id' => $user->secretaria_id,
                'unidad_id' => $user->unidad_id,
            ],
            'roles' => $roles,
            'scope' => $this->resolveScopeFromRoles($roles),
        ]);
    }

    public function executeDashboard(Request $request): JsonResponse
    {
        $widgets = $request->input('widgets');

        if (!is_array($widgets)) {
            return response()->json([
                'success' => false,
                'message' => 'El campo widgets debe ser un arreglo.',
            ], 422);
        }

        $results = [];

        foreach ($widgets as $widget) {
            if (!is_array($widget)) {
                continue;
            }

            $payload = [
                'entity' => $widget['entity'] ?? null,
                'widget_type' => $widget['type'] ?? ($widget['widget_type'] ?? 'table'),
                'aggregation' => $widget['config']['aggregation'] ?? ($widget['aggregation'] ?? ['type' => 'count']),
                'group_by' => $widget['config']['groupBy'] ?? ($widget['group_by'] ?? []),
                'filters' => $widget['config']['filters'] ?? ($widget['filters'] ?? []),
                'limit' => $widget['config']['limit'] ?? ($widget['limit'] ?? 100),
            ];

            [$status, $result] = $this->runWidgetQuery($payload, $request);

            $results[] = [
                'id' => $widget['id'] ?? null,
                'status' => $status,
                'result' => $result,
            ];
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function previewWidget(Request $request): JsonResponse
    {
        return $this->executeWidget($request);
    }

    public function saveDashboard(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $config = $request->input('config');
        if (!is_array($config)) {
            return response()->json([
                'success' => false,
                'message' => 'El campo config debe ser un arreglo.',
            ], 422);
        }

        $dashboardId = (int) $request->input('dashboard_id', 0);
        if ($dashboardId <= 0) {
            $dashboardId = (int) DashboardPlantilla::query()
                ->where('activo', true)
                ->orderBy('id')
                ->value('id');
        }

        if ($dashboardId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No hay plantillas disponibles para guardar la configuracion.',
            ], 422);
        }

        DashboardUsuarioAsignacion::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'dashboard_plantilla_id' => $dashboardId,
                'prioridad' => 1,
                'config_json' => $config,
                'activo' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Dashboard guardado correctamente.',
        ]);
    }

    public function loadDashboard(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $dashboardId = (int) $request->query('dashboard_id', 0);

        $query = DashboardUsuarioAsignacion::query()
            ->with(['plantilla.widgets' => fn ($q) => $q->where('activo', true)->orderBy('orden')])
            ->where('user_id', $user->id)
            ->where('activo', true);

        if ($dashboardId > 0) {
            $query->where('dashboard_plantilla_id', $dashboardId);
        }

        $assignment = $query->first();

        if (!$assignment) {
            return response()->json([
                'success' => true,
                'dashboard' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'dashboard' => [
                'id' => $assignment->dashboard_plantilla_id,
                'nombre' => $assignment->plantilla?->nombre,
                'descripcion' => $assignment->plantilla?->descripcion,
                'config' => $assignment->config_json,
                'widgets' => $assignment->plantilla?->widgets?->values() ?? [],
            ],
        ]);
    }

    public function fieldValues(Request $request): JsonResponse
    {
        $entity = (string) $request->query('entity', '');
        $field = (string) $request->query('field', '');

        $resolved = $this->resolveEntity($entity);
        if (!$resolved) {
            return response()->json([
                'success' => false,
                'message' => 'Entidad no encontrada.',
            ], 404);
        }

        $fieldMap = $this->buildFieldMap($resolved['table']);
        if (!array_key_exists($field, $fieldMap)) {
            return response()->json([
                'success' => false,
                'message' => 'Campo no valido para la entidad seleccionada.',
            ], 422);
        }

        $query = DB::table($resolved['table'])
            ->select($field)
            ->whereNotNull($field)
            ->distinct();

        $this->applyUserScope($query, $resolved['table'], $request->user());

        $values = $query
            ->orderBy($field)
            ->limit(200)
            ->pluck($field)
            ->values();

        return response()->json([
            'success' => true,
            'entity' => $resolved['key'],
            'field' => $field,
            'values' => $values,
        ]);
    }

    public function viewerList(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $dashboards = DashboardUsuarioAsignacion::query()
            ->with('plantilla:id,nombre,descripcion')
            ->where('user_id', $user->id)
            ->where('activo', true)
            ->get()
            ->map(function (DashboardUsuarioAsignacion $item): array {
                return [
                    'id' => (int) $item->dashboard_plantilla_id,
                    'nombre' => (string) ($item->plantilla?->nombre ?? ('Dashboard ' . $item->dashboard_plantilla_id)),
                    'descripcion' => $item->plantilla?->descripcion,
                    'updated_at' => $item->updated_at?->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'dashboards' => $dashboards,
        ]);
    }

    public function viewerShow(Request $request, int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $assignment = DashboardUsuarioAsignacion::query()
            ->with(['plantilla.widgets' => fn ($q) => $q->where('activo', true)->orderBy('orden')])
            ->where('user_id', $user->id)
            ->where('dashboard_plantilla_id', $id)
            ->where('activo', true)
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Dashboard no encontrado para el usuario.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'dashboard' => [
                'id' => (int) $assignment->dashboard_plantilla_id,
                'nombre' => $assignment->plantilla?->nombre,
                'descripcion' => $assignment->plantilla?->descripcion,
                'config' => $assignment->config_json,
                'widgets' => $assignment->plantilla?->widgets?->values() ?? [],
            ],
        ]);
    }

    private function runWidgetQuery(array $payload, Request $request): array
    {
        $entity = is_string($payload['entity'] ?? null) ? trim((string) $payload['entity']) : '';

        if ($entity === '') {
            return [422, [
                'success' => false,
                'message' => 'El parametro entity es obligatorio.',
            ]];
        }

        $resolved = $this->resolveEntity($entity);
        if (!$resolved) {
            return [404, [
                'success' => false,
                'message' => 'Entidad no encontrada.',
            ]];
        }

        $table = $resolved['table'];
        $fieldMap = $this->buildFieldMap($table);
        $fieldNames = array_keys($fieldMap);

        $query = DB::table($table);
        $scope = $this->applyUserScope($query, $table, $request->user());

        $filters = is_array($payload['filters'] ?? null) ? $payload['filters'] : [];
        $appliedFilters = [];

        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $field = isset($filter['field']) ? (string) $filter['field'] : '';
            if (!in_array($field, $fieldNames, true)) {
                continue;
            }

            $operator = strtolower((string) ($filter['operator'] ?? 'eq'));
            $value = $filter['value'] ?? null;

            if ($this->applyFilter($query, $field, $operator, $value)) {
                $appliedFilters[] = [
                    'field' => $field,
                    'operator' => $operator,
                    'value' => $value,
                ];
            }
        }

        $groupBy = collect(is_array($payload['group_by'] ?? null) ? $payload['group_by'] : [])
            ->map(fn ($f) => (string) $f)
            ->filter(fn ($f) => in_array($f, $fieldNames, true))
            ->values();

        $widgetType = (string) ($payload['widget_type'] ?? 'table');

        $aggregation = is_array($payload['aggregation'] ?? null) ? $payload['aggregation'] : [];
        $aggregationType = strtolower((string) ($aggregation['type'] ?? 'count'));
        $aggregationField = isset($aggregation['field']) ? (string) $aggregation['field'] : null;

        $limit = max(1, min(500, (int) ($payload['limit'] ?? 100)));

        if ($groupBy->isNotEmpty()) {
            $aggregateExpression = $this->buildAggregateExpression($aggregationType, $aggregationField, $fieldNames);

            $query->select($groupBy->all());
            $query->selectRaw($aggregateExpression . ' as value');

            foreach ($groupBy as $column) {
                $query->groupBy($column);
            }

            $data = $query
                ->limit($limit)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->values()
                ->all();
        } elseif ($widgetType === 'table') {
            $selectedColumns = array_slice($fieldNames, 0, 25);

            $data = $query
                ->select($selectedColumns)
                ->limit($limit)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->values()
                ->all();
        } else {
            $aggregateExpression = $this->buildAggregateExpression($aggregationType, $aggregationField, $fieldNames);
            $value = (clone $query)->selectRaw($aggregateExpression . ' as value')->value('value');
            $data = [['value' => $value]];
        }

        return [200, [
            'success' => true,
            'data' => $data,
            'entity' => $resolved['key'],
            'widget_type' => $widgetType,
            'count' => count($data),
            'applied_filters' => [
                'role_filters' => $scope,
                'user_filters' => $appliedFilters,
            ],
        ]];
    }

    private function resolveEntity(string $entity): ?array
    {
        $key = strtolower(trim($entity));
        $key = self::ENTITY_ALIASES[$key] ?? $key;

        if (isset(self::ENTITY_CATALOG[$key])) {
            $config = self::ENTITY_CATALOG[$key];

            if (!Schema::hasTable($config['table'])) {
                return null;
            }

            return [
                'key' => $key,
                'table' => $config['table'],
                'label' => $config['label'],
            ];
        }

        if (!Schema::hasTable($key)) {
            return null;
        }

        return [
            'key' => $key,
            'table' => $key,
            'label' => (string) Str::of($key)->replace('_', ' ')->title(),
        ];
    }

    private function buildFieldMap(string $table): array
    {
        $fields = [];

        foreach (Schema::getColumnListing($table) as $column) {
            $fields[$column] = [
                'type' => $this->normalizeFieldType($table, $column),
                'label' => (string) Str::of($column)->replace('_', ' ')->title(),
            ];
        }

        return $fields;
    }

    private function normalizeFieldType(string $table, string $column): string
    {
        $dbType = strtolower((string) Schema::getColumnType($table, $column));

        if (str_contains($dbType, 'int')
            || str_contains($dbType, 'decimal')
            || str_contains($dbType, 'float')
            || str_contains($dbType, 'double')
            || str_contains($dbType, 'numeric')
            || str_contains($dbType, 'real')) {
            return 'number';
        }

        if (str_contains($dbType, 'bool')) {
            return 'boolean';
        }

        if (str_contains($dbType, 'date')
            || str_contains($dbType, 'time')
            || str_contains($dbType, 'timestamp')
            || str_contains($dbType, 'year')) {
            return 'date';
        }

        return 'string';
    }

    private function buildAggregateExpression(string $type, ?string $field, array $allowedFields): string
    {
        $normalizedType = in_array($type, ['count', 'sum', 'avg', 'count_distinct'], true) ? $type : 'count';
        $fieldName = in_array((string) $field, $allowedFields, true) ? (string) $field : null;

        if ($normalizedType === 'sum' && $fieldName) {
            return 'SUM(' . $this->wrapColumn($fieldName) . ')';
        }

        if ($normalizedType === 'avg' && $fieldName) {
            return 'AVG(' . $this->wrapColumn($fieldName) . ')';
        }

        if ($normalizedType === 'count_distinct' && $fieldName) {
            return 'COUNT(DISTINCT ' . $this->wrapColumn($fieldName) . ')';
        }

        if ($normalizedType === 'count' && $fieldName) {
            return 'COUNT(' . $this->wrapColumn($fieldName) . ')';
        }

        return 'COUNT(*)';
    }

    private function wrapColumn(string $column): string
    {
        return DB::connection()->getQueryGrammar()->wrap($column);
    }

    private function applyFilter(Builder $query, string $field, string $operator, mixed $value): bool
    {
        switch ($operator) {
            case 'eq':
                $query->where($field, '=', $value);
                return true;

            case 'neq':
                $query->where($field, '!=', $value);
                return true;

            case 'gt':
                $query->where($field, '>', $value);
                return true;

            case 'gte':
                $query->where($field, '>=', $value);
                return true;

            case 'lt':
                $query->where($field, '<', $value);
                return true;

            case 'lte':
                $query->where($field, '<=', $value);
                return true;

            case 'like':
                $query->where($field, 'like', '%' . (string) $value . '%');
                return true;

            case 'in':
                $items = is_array($value) ? $value : array_filter(array_map('trim', explode(',', (string) $value)));
                if (empty($items)) {
                    return false;
                }
                $query->whereIn($field, $items);
                return true;

            case 'between':
            case 'date_range':
                if (!is_array($value) || count($value) < 2) {
                    return false;
                }
                $query->whereBetween($field, [$value[0], $value[1]]);
                return true;

            default:
                return false;
        }
    }

    private function applyUserScope(Builder $query, string $table, ?User $user): string
    {
        if (!$user) {
            return 'sin_autenticacion';
        }

        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values()->all() : [];

        if ($this->hasGlobalScope($roles)) {
            return 'global';
        }

        if ($table === 'users') {
            $query->where('id', $user->id);
            return 'usuario(users.id)';
        }

        if (in_array('secretario', $roles, true)
            && !empty($user->secretaria_id)
            && Schema::hasColumn($table, 'secretaria_origen_id')) {
            $query->where('secretaria_origen_id', (int) $user->secretaria_id);
            return 'secretaria(secretaria_origen_id)';
        }

        if (in_array('jefe_unidad', $roles, true)
            && !empty($user->unidad_id)
            && Schema::hasColumn($table, 'unidad_origen_id')) {
            $query->where('unidad_origen_id', (int) $user->unidad_id);
            return 'unidad(unidad_origen_id)';
        }

        if (Schema::hasColumn($table, 'created_by')) {
            $query->where('created_by', (int) $user->id);
            return 'usuario(created_by)';
        }

        if (Schema::hasColumn($table, 'user_id')) {
            $query->where('user_id', (int) $user->id);
            return 'usuario(user_id)';
        }

        return 'sin_filtro';
    }

    private function hasGlobalScope(array $roles): bool
    {
        foreach (self::GLOBAL_ROLES as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }

        return false;
    }

    private function resolveScopeFromRoles(array $roles): string
    {
        if ($this->hasGlobalScope($roles)) {
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
}

