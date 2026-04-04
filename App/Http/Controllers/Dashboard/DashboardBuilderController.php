<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Dashboard\EntityRegistry;
use App\Models\Dashboard;
use App\Models\DashboardAssignment;
use App\Models\DashboardRoleAssignment;
use App\Models\DashboardSecretariaAssignment;
use App\Models\User;
use App\Models\Secretaria;
use Spatie\Permission\Models\Role;

/**
 * Controlador principal del Dashboard Builder - SOLO ADMIN
 */
class DashboardBuilderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.builder.access');
    }

    /**
     * Muestra la vista del Dashboard Builder (SOLO Admin)
     */
    public function index()
    {
        $user = Auth::user();
        
        return view('dashboard.builder', [
            'user' => $user,
            'userRole' => $user->roles()->first(),
            'entities' => EntityRegistry::all(),
            'csrf_token' => csrf_token(),
            'savedDashboards' => Dashboard::where('active', true)->with('creator')->get()
        ]);
    }

    /**
     * Guarda un dashboard creado por Admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|string',
            'widgets.*.type' => 'required|string',
            'widgets.*.entity' => 'required|string',
            'widgets.*.title' => 'required|string',
            'widgets.*.config' => 'required|array'
        ]);

        $dashboard = Dashboard::create([
            'name' => $request->name,
            'description' => $request->description,
            'widgets' => $request->widgets,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard guardado exitosamente',
            'dashboard' => [
                'id' => $dashboard->id,
                'name' => $dashboard->name,
                'created_at' => $dashboard->created_at->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Lista dashboards guardados
     */
    public function list()
    {
        $dashboards = Dashboard::where('active', true)
            ->with(['creator', 'userAssignments.user', 'roleAssignments', 'secretariaAssignments.secretaria'])
            ->get();

        return response()->json([
            'dashboards' => $dashboards->map(function($dashboard) {
                return [
                    'id' => $dashboard->id,
                    'name' => $dashboard->name,
                    'description' => $dashboard->description,
                    'created_by' => $dashboard->creator->name,
                    'created_at' => $dashboard->created_at->format('Y-m-d H:i:s'),
                    'widget_count' => count($dashboard->widgets),
                    'assignments' => [
                        'users' => $dashboard->userAssignments->where('active', true)->count(),
                        'roles' => $dashboard->roleAssignments->where('active', true)->count(),
                        'secretarias' => $dashboard->secretariaAssignments->where('active', true)->count()
                    ]
                ];
            })
        ]);
    }

    /**
     * Carga un dashboard específico
     */
    public function show($id)
    {
        $dashboard = Dashboard::with('creator')->find($id);

        if (!$dashboard || !$dashboard->active) {
            return response()->json([
                'error' => 'Dashboard no encontrado'
            ], 404);
        }

        return response()->json([
            'dashboard' => [
                'id' => $dashboard->id,
                'name' => $dashboard->name,
                'description' => $dashboard->description,
                'widgets' => $dashboard->widgets,
                'created_by' => $dashboard->creator->name,
                'created_at' => $dashboard->created_at->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Asignar dashboard a usuarios específicos
     */
    public function assignToUsers(Request $request, $dashboardId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $dashboard = Dashboard::findOrFail($dashboardId);
        $assignedBy = Auth::id();
        $assignedAt = now();

        foreach ($request->user_ids as $userId) {
            DashboardAssignment::updateOrCreate(
                ['dashboard_id' => $dashboardId, 'user_id' => $userId],
                [
                    'assigned_by' => $assignedBy,
                    'assigned_at' => $assignedAt,
                    'active' => true
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard asignado a ' . count($request->user_ids) . ' usuario(s)'
        ]);
    }

    /**
     * Asignar dashboard a roles
     */
    public function assignToRoles(Request $request, $dashboardId)
    {
        $request->validate([
            'role_names' => 'required|array',
            'role_names.*' => 'string'
        ]);

        $dashboard = Dashboard::findOrFail($dashboardId);
        $assignedBy = Auth::id();
        $assignedAt = now();

        foreach ($request->role_names as $roleName) {
            DashboardRoleAssignment::updateOrCreate(
                ['dashboard_id' => $dashboardId, 'role_name' => $roleName],
                [
                    'assigned_by' => $assignedBy,
                    'assigned_at' => $assignedAt,
                    'active' => true
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard asignado a ' . count($request->role_names) . ' rol(es)'
        ]);
    }

    /**
     * Asignar dashboard a secretarías
     */
    public function assignToSecretarias(Request $request, $dashboardId)
    {
        $request->validate([
            'secretaria_ids' => 'required|array',
            'secretaria_ids.*' => 'exists:secretarias,id'
        ]);

        $dashboard = Dashboard::findOrFail($dashboardId);
        $assignedBy = Auth::id();
        $assignedAt = now();

        foreach ($request->secretaria_ids as $secretariaId) {
            DashboardSecretariaAssignment::updateOrCreate(
                ['dashboard_id' => $dashboardId, 'secretaria_id' => $secretariaId],
                [
                    'assigned_by' => $assignedBy,
                    'assigned_at' => $assignedAt,
                    'active' => true
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard asignado a ' . count($request->secretaria_ids) . ' secretaría(s)'
        ]);
    }

    /**
     * Obtener usuarios, roles y secretarías para asignación
     */
    public function assignmentData()
    {
        $users = User::select('id', 'name', 'email', 'secretaria_id')
            ->with('secretaria:id,nombre')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'secretaria' => $user->secretaria?->nombre
                ];
            });

        $roles = Role::select('name')->get()->pluck('name');
        $secretarias = Secretaria::select('id', 'nombre', 'sigla')->get();

        return response()->json([
            'users' => $users,
            'roles' => $roles,
            'secretarias' => $secretarias
        ]);
    }
}