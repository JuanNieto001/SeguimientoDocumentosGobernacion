<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Proceso;
use App\Models\ContractProcess;
use App\Models\ProcesoContratacionDirecta;
use App\Models\FlujoInstancia;
use App\Models\User;
use App\Models\Alerta;
use App\Models\PlanAnualAdquisicion;
use App\Models\TrackingEvento;
use App\Models\Secretaria;
use App\Models\Unidad;

class DynamicQueryEngine
{
    protected array $entityRegistry;
    protected ScopeFilterService $scopeFilter;

    public function __construct(ScopeFilterService $scopeFilter)
    {
        $this->scopeFilter = $scopeFilter;
        $this->registerEntities();
    }

    protected function registerEntities(): void
    {
        $this->entityRegistry = [
            'procesos' => [
                'model' => Proceso::class,
                'label' => 'Procesos',
                'icon' => 'folder',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'codigo' => ['type' => 'string', 'label' => 'Código', 'aggregatable' => false],
                    'objeto' => ['type' => 'string', 'label' => 'Objeto', 'aggregatable' => false],
                    'estado' => ['type' => 'enum', 'label' => 'Estado', 'aggregatable' => true, 'values' => ['EN_CURSO', 'FINALIZADO', 'CANCELADO', 'SUSPENDIDO']],
                    'valor_estimado' => ['type' => 'decimal', 'label' => 'Valor Estimado', 'aggregatable' => true, 'format' => 'currency'],
                    'area_actual_role' => ['type' => 'string', 'label' => 'Área Actual', 'aggregatable' => true],
                    'secretaria_origen_id' => ['type' => 'relation', 'label' => 'Secretaría', 'aggregatable' => true, 'relation' => 'secretarias'],
                    'unidad_origen_id' => ['type' => 'relation', 'label' => 'Unidad', 'aggregatable' => true, 'relation' => 'unidades'],
                    'created_by' => ['type' => 'relation', 'label' => 'Creado Por', 'aggregatable' => true, 'relation' => 'users'],
                    'created_at' => ['type' => 'datetime', 'label' => 'Fecha Creación', 'aggregatable' => true],
                    'updated_at' => ['type' => 'datetime', 'label' => 'Última Actualización', 'aggregatable' => true],
                    'plazo_ejecucion' => ['type' => 'integer', 'label' => 'Plazo (días)', 'aggregatable' => true],
                ],
                'scope_field' => 'secretaria_origen_id',
                'unit_field' => 'unidad_origen_id',
                'user_field' => 'created_by',
            ],
            'contract_processes' => [
                'model' => ContractProcess::class,
                'label' => 'Procesos Contractuales',
                'icon' => 'file-contract',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'process_number' => ['type' => 'string', 'label' => 'Número Proceso', 'aggregatable' => false],
                    'contract_number' => ['type' => 'string', 'label' => 'Número Contrato', 'aggregatable' => false],
                    'status' => ['type' => 'enum', 'label' => 'Estado', 'aggregatable' => true],
                    'process_type' => ['type' => 'enum', 'label' => 'Tipo', 'aggregatable' => true, 'values' => ['cd_pn', 'cd_pj', 'lp', 'sa', 'cm', 'mc']],
                    'current_step' => ['type' => 'integer', 'label' => 'Etapa Actual', 'aggregatable' => true],
                    'object' => ['type' => 'string', 'label' => 'Objeto', 'aggregatable' => false],
                    'estimated_value' => ['type' => 'decimal', 'label' => 'Valor Estimado', 'aggregatable' => true, 'format' => 'currency'],
                    'term_days' => ['type' => 'integer', 'label' => 'Plazo (días)', 'aggregatable' => true],
                    'secretaria_id' => ['type' => 'relation', 'label' => 'Secretaría', 'aggregatable' => true, 'relation' => 'secretarias'],
                    'unidad_id' => ['type' => 'relation', 'label' => 'Unidad', 'aggregatable' => true, 'relation' => 'unidades'],
                    'created_by' => ['type' => 'relation', 'label' => 'Creado Por', 'aggregatable' => true, 'relation' => 'users'],
                    'created_at' => ['type' => 'datetime', 'label' => 'Fecha Creación', 'aggregatable' => true],
                ],
                'scope_field' => 'secretaria_id',
                'unit_field' => 'unidad_id',
                'user_field' => 'created_by',
            ],
            'proceso_cd' => [
                'model' => ProcesoContratacionDirecta::class,
                'label' => 'Contratación Directa PN',
                'icon' => 'user-tie',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'codigo' => ['type' => 'string', 'label' => 'Código', 'aggregatable' => false],
                    'estado' => ['type' => 'enum', 'label' => 'Estado', 'aggregatable' => true],
                    'etapa_actual' => ['type' => 'integer', 'label' => 'Etapa', 'aggregatable' => true],
                    'objeto' => ['type' => 'string', 'label' => 'Objeto', 'aggregatable' => false],
                    'valor' => ['type' => 'decimal', 'label' => 'Valor', 'aggregatable' => true, 'format' => 'currency'],
                    'plazo_meses' => ['type' => 'integer', 'label' => 'Plazo (meses)', 'aggregatable' => true],
                    'secretaria_id' => ['type' => 'relation', 'label' => 'Secretaría', 'aggregatable' => true, 'relation' => 'secretarias'],
                    'unidad_id' => ['type' => 'relation', 'label' => 'Unidad', 'aggregatable' => true, 'relation' => 'unidades'],
                    'creado_por' => ['type' => 'relation', 'label' => 'Creado Por', 'aggregatable' => true, 'relation' => 'users'],
                    'created_at' => ['type' => 'datetime', 'label' => 'Fecha Creación', 'aggregatable' => true],
                ],
                'scope_field' => 'secretaria_id',
                'unit_field' => 'unidad_id',
                'user_field' => 'creado_por',
            ],
            'flujo_instancias' => [
                'model' => FlujoInstancia::class,
                'label' => 'Instancias de Flujo',
                'icon' => 'project-diagram',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'codigo_proceso' => ['type' => 'string', 'label' => 'Código', 'aggregatable' => false],
                    'estado' => ['type' => 'enum', 'label' => 'Estado', 'aggregatable' => true, 'values' => ['borrador', 'en_curso', 'completado', 'cancelado']],
                    'objeto' => ['type' => 'string', 'label' => 'Objeto', 'aggregatable' => false],
                    'monto_estimado' => ['type' => 'decimal', 'label' => 'Monto', 'aggregatable' => true, 'format' => 'currency'],
                    'plazo_dias' => ['type' => 'integer', 'label' => 'Plazo (días)', 'aggregatable' => true],
                    'secretaria_id' => ['type' => 'relation', 'label' => 'Secretaría', 'aggregatable' => true, 'relation' => 'secretarias'],
                    'unidad_id' => ['type' => 'relation', 'label' => 'Unidad', 'aggregatable' => true, 'relation' => 'unidades'],
                    'creado_por' => ['type' => 'relation', 'label' => 'Creado Por', 'aggregatable' => true, 'relation' => 'users'],
                    'created_at' => ['type' => 'datetime', 'label' => 'Fecha Creación', 'aggregatable' => true],
                ],
                'scope_field' => 'secretaria_id',
                'unit_field' => 'unidad_id',
                'user_field' => 'creado_por',
            ],
            'usuarios' => [
                'model' => User::class,
                'label' => 'Usuarios',
                'icon' => 'users',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'name' => ['type' => 'string', 'label' => 'Nombre', 'aggregatable' => false],
                    'email' => ['type' => 'string', 'label' => 'Email', 'aggregatable' => false],
                    'activo' => ['type' => 'boolean', 'label' => 'Activo', 'aggregatable' => true],
                    'secretaria_id' => ['type' => 'relation', 'label' => 'Secretaría', 'aggregatable' => true, 'relation' => 'secretarias'],
                    'unidad_id' => ['type' => 'relation', 'label' => 'Unidad', 'aggregatable' => true, 'relation' => 'unidades'],
                    'created_at' => ['type' => 'datetime', 'label' => 'Fecha Registro', 'aggregatable' => true],
                ],
                'scope_field' => 'secretaria_id',
                'unit_field' => 'unidad_id',
                'user_field' => 'id',
            ],
            'alertas' => [
                'model' => Alerta::class,
                'label' => 'Alertas',
                'icon' => 'bell',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'tipo' => ['type' => 'enum', 'label' => 'Tipo', 'aggregatable' => true, 'values' => ['tiempo_excedido', 'documento_vencido', 'sin_actividad', 'documento_pendiente']],
                    'prioridad' => ['type' => 'enum', 'label' => 'Prioridad', 'aggregatable' => true, 'values' => ['baja', 'media', 'alta', 'critica']],
                    'titulo' => ['type' => 'string', 'label' => 'Título', 'aggregatable' => false],
                    'leida' => ['type' => 'boolean', 'label' => 'Leída', 'aggregatable' => true],
                    'area_responsable' => ['type' => 'string', 'label' => 'Área', 'aggregatable' => true],
                    'user_id' => ['type' => 'relation', 'label' => 'Usuario', 'aggregatable' => true, 'relation' => 'users'],
                    'created_at' => ['type' => 'datetime', 'label' => 'Fecha', 'aggregatable' => true],
                ],
                'scope_field' => null,
                'unit_field' => null,
                'user_field' => 'user_id',
            ],
            'paa' => [
                'model' => PlanAnualAdquisicion::class,
                'label' => 'Plan Anual Adquisiciones',
                'icon' => 'calendar-alt',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'anio' => ['type' => 'integer', 'label' => 'Año', 'aggregatable' => true],
                    'codigo_necesidad' => ['type' => 'string', 'label' => 'Código', 'aggregatable' => false],
                    'descripcion' => ['type' => 'string', 'label' => 'Descripción', 'aggregatable' => false],
                    'valor_estimado' => ['type' => 'decimal', 'label' => 'Valor', 'aggregatable' => true, 'format' => 'currency'],
                    'modalidad_contratacion' => ['type' => 'enum', 'label' => 'Modalidad', 'aggregatable' => true],
                    'trimestre_estimado' => ['type' => 'integer', 'label' => 'Trimestre', 'aggregatable' => true],
                    'estado' => ['type' => 'enum', 'label' => 'Estado', 'aggregatable' => true, 'values' => ['vigente', 'modificado', 'ejecutado', 'cancelado']],
                    'dependencia_solicitante' => ['type' => 'string', 'label' => 'Dependencia', 'aggregatable' => true],
                ],
                'scope_field' => null,
                'unit_field' => null,
                'user_field' => null,
            ],
            'tracking' => [
                'model' => TrackingEvento::class,
                'label' => 'Tracking/Historial',
                'icon' => 'history',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'codigo_proceso' => ['type' => 'string', 'label' => 'Código Proceso', 'aggregatable' => false],
                    'tipo' => ['type' => 'enum', 'label' => 'Tipo Evento', 'aggregatable' => true],
                    'area_origen' => ['type' => 'string', 'label' => 'Área Origen', 'aggregatable' => true],
                    'area_destino' => ['type' => 'string', 'label' => 'Área Destino', 'aggregatable' => true],
                    'user_id' => ['type' => 'relation', 'label' => 'Usuario', 'aggregatable' => true, 'relation' => 'users'],
                    'created_at' => ['type' => 'datetime', 'label' => 'Fecha', 'aggregatable' => true],
                ],
                'scope_field' => null,
                'unit_field' => null,
                'user_field' => 'user_id',
            ],
            'secretarias' => [
                'model' => Secretaria::class,
                'label' => 'Secretarías',
                'icon' => 'building',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'nombre' => ['type' => 'string', 'label' => 'Nombre', 'aggregatable' => true],
                    'activo' => ['type' => 'boolean', 'label' => 'Activa', 'aggregatable' => true],
                ],
                'scope_field' => 'id',
                'unit_field' => null,
                'user_field' => null,
            ],
            'unidades' => [
                'model' => Unidad::class,
                'label' => 'Unidades',
                'icon' => 'sitemap',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'ID', 'aggregatable' => false],
                    'nombre' => ['type' => 'string', 'label' => 'Nombre', 'aggregatable' => true],
                    'secretaria_id' => ['type' => 'relation', 'label' => 'Secretaría', 'aggregatable' => true, 'relation' => 'secretarias'],
                    'activo' => ['type' => 'boolean', 'label' => 'Activa', 'aggregatable' => true],
                ],
                'scope_field' => 'secretaria_id',
                'unit_field' => 'id',
                'user_field' => null,
            ],
        ];
    }

    public function getEntityRegistry(): array
    {
        return $this->entityRegistry;
    }

    public function getEntityCatalog(): array
    {
        $catalog = [];
        foreach ($this->entityRegistry as $key => $config) {
            $catalog[$key] = [
                'key' => $key,
                'label' => $config['label'],
                'icon' => $config['icon'],
                'fields' => array_map(fn($f, $k) => array_merge($f, ['key' => $k]), $config['fields'], array_keys($config['fields'])),
            ];
        }
        return $catalog;
    }

    public function executeWidgetQuery(array $widgetConfig, User $user): array
    {
        $entity = $widgetConfig['entity'] ?? null;
        $metrica = $widgetConfig['metrica'] ?? 'count';
        $dimension = $widgetConfig['dimension'] ?? null;
        $filters = $widgetConfig['filters'] ?? [];
        $tipo = $widgetConfig['tipo'] ?? 'kpi';
        $limit = $widgetConfig['limit'] ?? 100;
        $orderBy = $widgetConfig['orderBy'] ?? null;
        $orderDir = $widgetConfig['orderDir'] ?? 'desc';

        if (!$entity || !isset($this->entityRegistry[$entity])) {
            return ['error' => 'Entidad no válida', 'data' => null];
        }

        $entityConfig = $this->entityRegistry[$entity];
        $modelClass = $entityConfig['model'];
        $query = $modelClass::query();

        // Aplicar filtros de scope automáticamente (OBLIGATORIO)
        $query = $this->scopeFilter->applyUserScope($query, $user, $entityConfig);

        // Aplicar filtros adicionales del widget
        $query = $this->applyFilters($query, $filters, $entityConfig);

        // Ejecutar según tipo de widget
        return match ($tipo) {
            'kpi' => $this->executeKpiQuery($query, $metrica, $entityConfig),
            'chart' => $this->executeChartQuery($query, $metrica, $dimension, $entityConfig, $limit),
            'table' => $this->executeTableQuery($query, $widgetConfig, $entityConfig, $limit, $orderBy, $orderDir),
            'timeline' => $this->executeTimelineQuery($query, $dimension, $entityConfig, $limit),
            'heatmap' => $this->executeHeatmapQuery($query, $widgetConfig, $entityConfig),
            default => ['error' => 'Tipo de widget no soportado', 'data' => null],
        };
    }

    protected function applyFilters(Builder $query, array $filters, array $entityConfig): Builder
    {
        foreach ($filters as $filter) {
            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'] ?? null;

            if (!$field || !isset($entityConfig['fields'][$field])) {
                continue;
            }

            $fieldConfig = $entityConfig['fields'][$field];

            switch ($operator) {
                case '=':
                case '!=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                    $query->where($field, $operator, $value);
                    break;
                case 'like':
                    $query->where($field, 'like', "%{$value}%");
                    break;
                case 'in':
                    $query->whereIn($field, (array)$value);
                    break;
                case 'not_in':
                    $query->whereNotIn($field, (array)$value);
                    break;
                case 'is_null':
                    $query->whereNull($field);
                    break;
                case 'is_not_null':
                    $query->whereNotNull($field);
                    break;
                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween($field, $value);
                    }
                    break;
                case 'date_range':
                    if (is_array($value)) {
                        if (!empty($value['from'])) {
                            $query->whereDate($field, '>=', $value['from']);
                        }
                        if (!empty($value['to'])) {
                            $query->whereDate($field, '<=', $value['to']);
                        }
                    }
                    break;
                case 'this_month':
                    $query->whereMonth($field, now()->month)
                          ->whereYear($field, now()->year);
                    break;
                case 'this_year':
                    $query->whereYear($field, now()->year);
                    break;
                case 'last_n_days':
                    $query->where($field, '>=', now()->subDays((int)$value));
                    break;
            }
        }

        return $query;
    }

    protected function executeKpiQuery(Builder $query, string $metrica, array $entityConfig): array
    {
        $result = match ($metrica) {
            'count' => $query->count(),
            'sum' => function() use ($query, $entityConfig) {
                $sumField = $this->findNumericField($entityConfig);
                return $sumField ? $query->sum($sumField) : 0;
            },
            'avg' => function() use ($query, $entityConfig) {
                $avgField = $this->findNumericField($entityConfig);
                return $avgField ? round($query->avg($avgField), 2) : 0;
            },
            'max' => function() use ($query, $entityConfig) {
                $maxField = $this->findNumericField($entityConfig);
                return $maxField ? $query->max($maxField) : 0;
            },
            'min' => function() use ($query, $entityConfig) {
                $minField = $this->findNumericField($entityConfig);
                return $minField ? $query->min($minField) : 0;
            },
            default => $query->count(),
        };

        $value = is_callable($result) ? $result() : $result;

        return [
            'data' => [
                'value' => $value,
                'formatted' => $this->formatValue($value, $metrica, $entityConfig),
            ],
            'meta' => [
                'metrica' => $metrica,
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    protected function executeChartQuery(Builder $query, string $metrica, ?string $dimension, array $entityConfig, int $limit): array
    {
        if (!$dimension || !isset($entityConfig['fields'][$dimension])) {
            return ['error' => 'Dimensión requerida para gráficas', 'data' => null];
        }

        $fieldConfig = $entityConfig['fields'][$dimension];
        $aggregation = $metrica === 'sum' ? 'SUM' : ($metrica === 'avg' ? 'AVG' : 'COUNT');
        $numericField = $this->findNumericField($entityConfig);

        if ($fieldConfig['type'] === 'datetime') {
            // Agrupar por fecha
            $results = $query->selectRaw("DATE({$dimension}) as label, {$aggregation}(" . ($numericField ?: '*') . ") as value")
                ->groupBy(DB::raw("DATE({$dimension})"))
                ->orderBy(DB::raw("DATE({$dimension})"))
                ->limit($limit)
                ->get();
        } else {
            // Agrupar por campo
            $selectField = $dimension;

            // Si es relación, intentar join para obtener nombre
            if ($fieldConfig['type'] === 'relation' && isset($fieldConfig['relation'])) {
                $relationEntity = $fieldConfig['relation'];
                if (isset($this->entityRegistry[$relationEntity])) {
                    $relationConfig = $this->entityRegistry[$relationEntity];
                    $relationModel = new $relationConfig['model'];
                    $relationTable = $relationModel->getTable();
                    $nameField = $this->findNameField($relationConfig);

                    $mainModel = new $entityConfig['model'];
                    $mainTable = $mainModel->getTable();

                    $query->leftJoin($relationTable, "{$mainTable}.{$dimension}", '=', "{$relationTable}.id");
                    $selectField = "{$relationTable}.{$nameField}";
                }
            }

            $results = $query->selectRaw("{$selectField} as label, {$aggregation}(" . ($numericField ? $entityConfig['model']::query()->getModel()->getTable() . ".{$numericField}" : '*') . ") as value")
                ->groupBy($selectField)
                ->orderByDesc('value')
                ->limit($limit)
                ->get();
        }

        return [
            'data' => [
                'labels' => $results->pluck('label')->map(fn($l) => $l ?? 'Sin asignar')->toArray(),
                'values' => $results->pluck('value')->map(fn($v) => (float)$v)->toArray(),
            ],
            'meta' => [
                'dimension' => $dimension,
                'metrica' => $metrica,
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    protected function executeTableQuery(Builder $query, array $widgetConfig, array $entityConfig, int $limit, ?string $orderBy, string $orderDir): array
    {
        $columns = $widgetConfig['columns'] ?? array_keys(array_slice($entityConfig['fields'], 0, 5));

        $selectColumns = [];
        foreach ($columns as $col) {
            if (isset($entityConfig['fields'][$col])) {
                $selectColumns[] = $col;
            }
        }

        if (empty($selectColumns)) {
            $selectColumns = ['id'];
        }

        if ($orderBy && isset($entityConfig['fields'][$orderBy])) {
            $query->orderBy($orderBy, $orderDir);
        } else {
            $query->latest();
        }

        $results = $query->select($selectColumns)
            ->limit($limit)
            ->get();

        return [
            'data' => [
                'columns' => array_map(fn($c) => [
                    'key' => $c,
                    'label' => $entityConfig['fields'][$c]['label'] ?? $c,
                    'type' => $entityConfig['fields'][$c]['type'] ?? 'string',
                ], $selectColumns),
                'rows' => $results->toArray(),
            ],
            'meta' => [
                'total' => $results->count(),
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    protected function executeTimelineQuery(Builder $query, ?string $dimension, array $entityConfig, int $limit): array
    {
        $dateField = $dimension ?? 'created_at';

        if (!isset($entityConfig['fields'][$dateField]) || $entityConfig['fields'][$dateField]['type'] !== 'datetime') {
            $dateField = 'created_at';
        }

        $results = $query->selectRaw("DATE({$dateField}) as date, COUNT(*) as count")
            ->groupBy(DB::raw("DATE({$dateField})"))
            ->orderBy(DB::raw("DATE({$dateField})"), 'desc')
            ->limit($limit)
            ->get();

        return [
            'data' => [
                'timeline' => $results->map(fn($r) => [
                    'date' => $r->date,
                    'count' => (int)$r->count,
                ])->toArray(),
            ],
            'meta' => [
                'field' => $dateField,
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    protected function executeHeatmapQuery(Builder $query, array $widgetConfig, array $entityConfig): array
    {
        $xDimension = $widgetConfig['xDimension'] ?? null;
        $yDimension = $widgetConfig['yDimension'] ?? null;

        if (!$xDimension || !$yDimension) {
            return ['error' => 'Heatmap requiere xDimension y yDimension', 'data' => null];
        }

        $results = $query->selectRaw("{$xDimension} as x, {$yDimension} as y, COUNT(*) as value")
            ->groupBy($xDimension, $yDimension)
            ->get();

        $xValues = $results->pluck('x')->unique()->values()->toArray();
        $yValues = $results->pluck('y')->unique()->values()->toArray();

        $matrix = [];
        foreach ($yValues as $y) {
            $row = [];
            foreach ($xValues as $x) {
                $cell = $results->first(fn($r) => $r->x === $x && $r->y === $y);
                $row[] = $cell ? (int)$cell->value : 0;
            }
            $matrix[] = $row;
        }

        return [
            'data' => [
                'xLabels' => $xValues,
                'yLabels' => $yValues,
                'matrix' => $matrix,
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    protected function findNumericField(array $entityConfig): ?string
    {
        foreach ($entityConfig['fields'] as $key => $config) {
            if (in_array($config['type'], ['decimal', 'integer']) && ($config['format'] ?? null) === 'currency') {
                return $key;
            }
        }
        foreach ($entityConfig['fields'] as $key => $config) {
            if (in_array($config['type'], ['decimal', 'integer']) && $key !== 'id') {
                return $key;
            }
        }
        return null;
    }

    protected function findNameField(array $entityConfig): string
    {
        $candidates = ['nombre', 'name', 'titulo', 'title', 'label'];
        foreach ($candidates as $candidate) {
            if (isset($entityConfig['fields'][$candidate])) {
                return $candidate;
            }
        }
        return 'id';
    }

    protected function formatValue($value, string $metrica, array $entityConfig): string
    {
        if ($metrica === 'sum' || $metrica === 'avg') {
            $numericField = $this->findNumericField($entityConfig);
            if ($numericField && ($entityConfig['fields'][$numericField]['format'] ?? null) === 'currency') {
                return '$' . number_format($value, 0, ',', '.');
            }
        }

        if (is_numeric($value)) {
            return number_format($value, $value == (int)$value ? 0 : 2, ',', '.');
        }

        return (string)$value;
    }
}
