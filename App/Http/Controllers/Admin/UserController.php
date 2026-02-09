<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $roles = Role::orderBy('name')->get();
        $currentRole = $usuario->roles->pluck('name')->first(); // 1 rol esperado

        return view('admin.users.edit', compact('usuario', 'roles', 'currentRole'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        // Reemplaza rol (simple)
        $usuario->syncRoles([$data['role']]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        // Evitar que borres tu propio usuario logueado por accidente
        if (auth()->id() === $usuario->id) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
