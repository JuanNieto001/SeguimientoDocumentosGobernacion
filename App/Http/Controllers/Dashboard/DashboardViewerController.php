<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dashboard;

/**
 * Controlador para usuarios que VEN dashboards asignados
 */
class DashboardViewerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.view.assigned');
    }

    /**
     * Lista dashboards asignados al usuario actual
     */
    public function index()
    {
        $user = Auth::user();
        $dashboards = $this->getUserAssignedDashboards($user);

        return view('dashboard.viewer', [
            'user' => $user,
            'dashboards' => $dashboards
        ]);
    }

    /**
     * Muestra un dashboard específico asignado al usuario
     */
    public function show($id)
    {
        $user = Auth::user();
        $dashboard = Dashboard::find($id);

        if (!$dashboard || !$dashboard->canBeViewedBy($user)) {
            abort(403, 'No tienes permiso para ver este dashboard');
        }

        return view('dashboard.show', [
            'user' => $user,
            'dashboard' => $dashboard,
            'widgets' => $dashboard->widgets,
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * API: Lista dashboards asignados
     */
    public function apiList()
    {
        $user = Auth::user();
        $dashboards = $this->getUserAssignedDashboards($user);

        return response()->json([
            'dashboards' => $dashboards->map(function($dashboard) {
                return [
                    'id' => $dashboard->id,
                    'name' => $dashboard->name,
                    'description' => $dashboard->description,
                    'widget_count' => count($dashboard->widgets),
                    'created_by' => $dashboard->creator->name,
                    'created_at' => $dashboard->created_at->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }

    /**
     * API: Obtiene configuración de dashboard para visualizar
     */
    public function apiShow($id)
    {
        $user = Auth::user();
        $dashboard = Dashboard::with('creator')->find($id);

        if (!$dashboard || !$dashboard->canBeViewedBy($user)) {
            return response()->json([
                'error' => 'Dashboard no encontrado o sin permisos'
            ], 403);
        }

        return response()->json([
            'dashboard' => [
                'id' => $dashboard->id,
                'name' => $dashboard->name,
                'description' => $dashboard->description,
                'widgets' => $dashboard->widgets,
                'created_by' => $dashboard->creator->name,
                'created_at' => $dashboard->created_at->format('Y-m-d H:i:s'),
                'readonly' => true // Indicar que es solo lectura
            ]
        ]);
    }

    /**
     * Obtiene dashboards asignados al usuario
     */
    private function getUserAssignedDashboards($user)
    {
        // Admin puede ver todos los dashboards
        if ($user->hasRole(['admin', 'admin_general'])) {
            return Dashboard::where('active', true)->with('creator')->get();
        }

        // Obtener IDs de dashboards asignados
        $assignedDashboardIds = collect();

        // Asignaciones directas por usuario
        $userAssignments = \App\Models\DashboardAssignment::where('user_id', $user->id)
            ->where('active', true)
            ->pluck('dashboard_id');
        $assignedDashboardIds = $assignedDashboardIds->merge($userAssignments);

        // Asignaciones por rol
        $userRoles = $user->roles->pluck('name')->toArray();
        if (!empty($userRoles)) {
            $roleAssignments = \App\Models\DashboardRoleAssignment::whereIn('role_name', $userRoles)
                ->where('active', true)
                ->pluck('dashboard_id');
            $assignedDashboardIds = $assignedDashboardIds->merge($roleAssignments);
        }

        // Asignaciones por secretaría
        if ($user->secretaria_id) {
            $secretariaAssignments = \App\Models\DashboardSecretariaAssignment::where('secretaria_id', $user->secretaria_id)
                ->where('active', true)
                ->pluck('dashboard_id');
            $assignedDashboardIds = $assignedDashboardIds->merge($secretariaAssignments);
        }

        // Obtener dashboards únicos
        $uniqueIds = $assignedDashboardIds->unique()->toArray();

        return Dashboard::whereIn('id', $uniqueIds)
            ->where('active', true)
            ->with('creator')
            ->orderBy('name')
            ->get();
    }
}