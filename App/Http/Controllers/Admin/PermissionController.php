<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'general';
        });
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso \"{$request->name}\" creado correctamente.");
    }

    public function edit(Permission $permiso)
    {
        return view('admin.permissions.edit', compact('permiso'));
    }

    public function update(Request $request, Permission $permiso)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:permissions,name,' . $permiso->id],
        ]);

        $permiso->update(['name' => $request->name]);

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso actualizado correctamente.");
    }

    public function destroy(Permission $permiso)
    {
        $permiso->delete();
        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso eliminado correctamente.");
    }
}
