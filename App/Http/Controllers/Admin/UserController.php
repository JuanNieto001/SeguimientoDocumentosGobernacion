<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['secretaria', 'unidad', 'roles'])->orderBy('id', 'desc');

        // Filtro por secretaría (admin_secretaria solo ve su secretaría)
        $authUser = $request->user();
        if ($authUser->hasRole('admin_secretaria') && !$authUser->hasRole(['admin', 'admin_general'])) {
            $query->where('secretaria_id', $authUser->secretaria_id);
        }

        // Filtros opcionales desde la UI
        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        $users = $query->paginate(15);
        $secretarias = Secretaria::activas()->orderBy('nombre')->get();

        return view('admin.users.index', compact('users', 'secretarias'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $secretarias = Secretaria::activas()->orderBy('nombre')->get();
        $unidades = Unidad::activas()->orderBy('nombre')->get();

        return view('admin.users.create', compact('roles', 'secretarias', 'unidades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:6'],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'secretaria_id' => ['nullable', 'exists:secretarias,id'],
            'unidad_id'     => ['nullable', 'exists:unidades,id'],
        ]);

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'secretaria_id' => $data['secretaria_id'] ?? null,
            'unidad_id'     => $data['unidad_id'] ?? null,
            'activo'        => true,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $roles = Role::orderBy('name')->get();
        $secretarias = Secretaria::activas()->orderBy('nombre')->get();
        $unidades = Unidad::activas()->orderBy('nombre')->get();
        $currentRole = $usuario->roles->pluck('name')->first();

        return view('admin.users.edit', compact('usuario', 'roles', 'currentRole', 'secretarias', 'unidades'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'password'      => ['nullable', 'string', 'min:6'],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'secretaria_id' => ['nullable', 'exists:secretarias,id'],
            'unidad_id'     => ['nullable', 'exists:unidades,id'],
            'activo'        => ['nullable', 'boolean'],
        ]);

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];
        $usuario->secretaria_id = $data['secretaria_id'] ?? null;
        $usuario->unidad_id = $data['unidad_id'] ?? null;
        $usuario->activo = $request->boolean('activo', $usuario->activo);

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();
        $usuario->syncRoles([$data['role']]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if (auth()->id() === $usuario->id) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
