<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Dashboard\DashboardQueryBuilder;

/**
 * API para ejecutar queries de widgets dinámicamente
 */
class WidgetQueryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.builder.access');
    }

    /**
     * Ejecuta query para un widget específico
     */
    public function execute(Request $request)
    {
        $request->validate([
            'entity' => 'required|string',
            'widget_type' => 'required|in:bar,line,pie,area,metric,table',
            'aggregation' => 'required|array',
            'aggregation.type' => 'required|in:count,sum,avg,count_distinct',
            'aggregation.field' => 'nullable|string',
            'group_by' => 'nullable|array',
            'group_by.*' => 'string',
            'filters' => 'nullable|array',
            'filters.*.field' => 'required_with:filters|string',
            'filters.*.operator' => 'required_with:filters|in:eq,neq,gt,gte,lt,lte,like,in,between,date_range',
            'filters.*.value' => 'required_with:filters',
            'limit' => 'nullable|integer|min:1|max:10000'
        ]);

        try {
            $user = Auth::user();
            $entity = $request->input('entity');
            $widgetType = $request->input('widget_type');
            
            // Crear query builder con filtros de rol automáticos
            $queryBuilder = new DashboardQueryBuilder($entity, $user);

            // Aplicar agregación
            $aggregation = $request->input('aggregation');
            $queryBuilder->addAggregation(
                $aggregation['type'],
                $aggregation['field'] ?? null,
                'value'
            );

            // Aplicar agrupación
            if ($request->has('group_by')) {
                foreach ($request->input('group_by', []) as $field) {
                    $queryBuilder->addGroupBy($field);
                }
            }

            // Aplicar filtros de usuario
            foreach ($request->input('filters', []) as $filter) {
                $queryBuilder->addFilter(
                    $filter['field'],
                    $filter['operator'],
                    $filter['value']
                );
            }

            // Aplicar límite
            if ($request->has('limit')) {
                $queryBuilder->limit($request->input('limit'));
            }

            // Ejecutar query
            $data = $queryBuilder->execute();

            return response()->json([
                'success' => true,
                'data' => $data,
                'entity' => $entity,
                'widget_type' => $widgetType,
                'count' => count($data),
                'applied_filters' => [
                    'role_filters' => 'automatic', // Los filtros de rol se aplican automáticamente
                    'user_filters' => $request->input('filters', [])
                ]
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Configuración inválida',
                'message' => $e->getMessage()
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Widget Query Error', [
                'user_id' => Auth::id(),
                'entity' => $request->input('entity'),
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => config('app.debug') ? $e->getMessage() : 'Error al ejecutar consulta'
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas rápidas de una entidad
     */
    public function stats($entity)
    {
        try {
            $user = Auth::user();
            
            $queryBuilder = new DashboardQueryBuilder($entity, $user);
            $totalCount = $queryBuilder->addAggregation('count')->execute();

            return response()->json([
                'entity' => $entity,
                'total_records' => $totalCount[0]['value'] ?? 0,
                'user_role' => $user->roles()->first()?->name ?? 'sin_rol',
                'filters_applied' => 'role_based'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener estadísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}