<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'general';
        });
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions(Permission::whereIn('id', $request->permissions)->get());
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol \"{$role->name}\" creado correctamente.");
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'general';
        });
        $assignedIds = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'assignedIds'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions(
            $request->filled('permissions')
                ? Permission::whereIn('id', $request->permissions)->get()
                : []
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol \"{$role->name}\" actualizado correctamente.");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['admin'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No puedes eliminar el rol administrador.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')
            ->with('success', "Rol eliminado correctamente.");
    }
}
