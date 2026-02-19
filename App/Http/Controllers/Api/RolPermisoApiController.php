<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolPermisoApiController extends Controller
{
    /**
     * GET /api/roles
     *
     * Lista todos los roles con sus permisos.
     */
    public function roles(): JsonResponse
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return response()->json(['roles' => $roles]);
    }

    /**
     * GET /api/roles/{role}
     *
     * Detalle de un rol con sus permisos.
     */
    public function showRole(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json(['role' => $role]);
    }

    /**
     * GET /api/permisos
     *
     * Lista todos los permisos agrupados por recurso.
     */
    public function permisos(): JsonResponse
    {
        $permisos = Permission::orderBy('name')->get();

        $agrupados = $permisos->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'general';
        });

        return response()->json([
            'permisos'          => $permisos,
            'permisos_agrupados' => $agrupados,
        ]);
    }

    /**
     * POST /api/roles/{role}/permisos
     *
     * Asigna permisos a un rol.
     */
    public function asignarPermisos(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permisos'   => ['required', 'array'],
            'permisos.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($request->permisos);
        $role->load('permissions');

        return response()->json([
            'message' => "Permisos del rol \"{$role->name}\" actualizados.",
            'role'    => $role,
        ]);
    }

    /**
     * POST /api/usuarios/{userId}/roles
     *
     * Asigna un rol a un usuario.
     */
    public function asignarRolUsuario(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'rol' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = \App\Models\User::findOrFail($userId);
        $user->syncRoles([$request->rol]);

        return response()->json([
            'message' => "Rol \"{$request->rol}\" asignado a {$user->name}.",
            'user'    => $user->load('roles'),
        ]);
    }
}
