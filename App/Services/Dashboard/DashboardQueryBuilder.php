<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Constructor de queries dinámicas para Dashboard Builder
 * CRÍTICO: Siempre aplica filtros de rol antes de filtros de usuario
 */
class DashboardQueryBuilder
{
    private string $entity;
    private array $filters;
    private array $groupBy;
    private array $aggregations;
    private ?int $limit;
    private User $user;

    public function __construct(string $entity, User $user)
    {
        $this->entity = $entity;
        $this->user = $user;
        $this->filters = [];
        $this->groupBy = [];
        $this->aggregations = [];
        $this->limit = null;
    }

    /**
     * Agrega filtros del usuario (aplicados DESPUÉS de filtros de rol)
     */
    public function addFilter(string $field, string $operator, $value): self
    {
        $this->filters[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];
        return $this;
    }

    /**
     * Agrega campos para agrupar
     */
    public function addGroupBy(string $field): self
    {
        $this->groupBy[] = $field;
        return $this;
    }

    /**
     * Agrega agregaciones (count, sum, avg, count_distinct)
     */
    public function addAggregation(string $type, ?string $field = null, string $alias = 'value'): self
    {
        $this->aggregations[] = [
            'type' => $type,
            'field' => $field,
            'alias' => $alias
        ];
        return $this;
    }

    /**
     * Establece límite de resultados
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Ejecuta la query aplicando filtros de rol PRIMERO
     */
    public function execute(): array
    {
        $modelClass = EntityRegistry::getModel($this->entity);
        if (!$modelClass) {
            throw new \InvalidArgumentException("Entidad {$this->entity} no encontrada");
        }

        $query = $modelClass::query();

        // PASO 1: Aplicar filtros de rol (CRÍTICO - no bypasseable)
        RoleFilterResolver::applyRoleFilters($query, $this->entity, $this->user);

        // PASO 2: Aplicar filtros de usuario
        $this->applyUserFilters($query);

        // PASO 3: Aplicar agrupaciones y agregaciones
        $this->applyAggregations($query);

        // PASO 4: Aplicar límite
        if ($this->limit) {
            $query->limit($this->limit);
        }

        // PASO 5: Ejecutar y retornar resultados
        try {
            return $query->get()->toArray();
        } catch (\Exception $e) {
            \Log::error('Dashboard Query Error', [
                'entity' => $this->entity,
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'query' => $query->toSql()
            ]);
            throw $e;
        }
    }

    /**
     * Aplica filtros definidos por el usuario
     */
    private function applyUserFilters(Builder $query): void
    {
        foreach ($this->filters as $filter) {
            $field = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'];

            switch ($operator) {
                case 'eq':
                    $query->where($field, '=', $value);
                    break;
                case 'neq':
                    $query->where($field, '!=', $value);
                    break;
                case 'gt':
                    $query->where($field, '>', $value);
                    break;
                case 'gte':
                    $query->where($field, '>=', $value);
                    break;
                case 'lt':
                    $query->where($field, '<', $value);
                    break;
                case 'lte':
                    $query->where($field, '<=', $value);
                    break;
                case 'like':
                    $query->where($field, 'like', "%{$value}%");
                    break;
                case 'in':
                    $query->whereIn($field, is_array($value) ? $value : [$value]);
                    break;
                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween($field, $value);
                    }
                    break;
                case 'date_range':
                    if (is_array($value) && count($value) === 2) {
                        $query->whereDate($field, '>=', $value[0])
                              ->whereDate($field, '<=', $value[1]);
                    }
                    break;
            }
        }
    }

    /**
     * Aplica agrupaciones y agregaciones
     */
    private function applyAggregations(Builder $query): void
    {
        if (empty($this->aggregations) && empty($this->groupBy)) {
            return;
        }

        $selectFields = [];

        // Agregar campos de agrupación
        foreach ($this->groupBy as $field) {
            $selectFields[] = $field;
        }

        // Agregar agregaciones
        foreach ($this->aggregations as $agg) {
            switch ($agg['type']) {
                case 'count':
                    $selectFields[] = \DB::raw("COUNT(*) as {$agg['alias']}");
                    break;
                case 'count_distinct':
                    if ($agg['field']) {
                        $selectFields[] = \DB::raw("COUNT(DISTINCT {$agg['field']}) as {$agg['alias']}");
                    }
                    break;
                case 'sum':
                    if ($agg['field']) {
                        $selectFields[] = \DB::raw("SUM({$agg['field']}) as {$agg['alias']}");
                    }
                    break;
                case 'avg':
                    if ($agg['field']) {
                        $selectFields[] = \DB::raw("AVG({$agg['field']}) as {$agg['alias']}");
                    }
                    break;
            }
        }

        if (!empty($selectFields)) {
            $query->select($selectFields);
        }

        if (!empty($this->groupBy)) {
            $query->groupBy($this->groupBy);
        }
    }
}