<?php
/**
 * Archivo: backend/App/Http/Controllers/Admin/UserController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthEvent;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
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
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }
        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }
        if ($request->filled('rol')) {
            $roleFiltro = $request->rol;
            $query->whereHas('roles', fn($q) => $q->where('name', $roleFiltro));
        }

        $users = $query->paginate(15)->appends($request->query());
        $secretarias = Secretaria::activas()->orderBy('nombre')->get();
        $roles = Role::orderBy('name')->get();
        $unidades = collect();
        if ($request->filled('secretaria_id')) {
            $unidades = Unidad::where('secretaria_id', $request->secretaria_id)
                ->activas()->orderBy('nombre')->get();
        }

        return view('admin.users.index', compact('users', 'secretarias', 'roles', 'unidades'));
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
            'password'      => ['required', 'string', Password::defaults()],
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

        AuthEvent::registrar('user_created', auth()->id(), auth()->user()->email, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'role' => $data['role'],
            'activo' => true,
        ]);

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
            'password'      => ['nullable', 'string', Password::defaults()],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'secretaria_id' => ['nullable', 'exists:secretarias,id'],
            'unidad_id'     => ['nullable', 'exists:unidades,id'],
            'activo'        => ['nullable', 'boolean'],
        ]);

        $oldRoleNames = $usuario->roles->pluck('name')->values()->all();
        $oldActivo = (bool) $usuario->activo;
        $oldName = $usuario->name;
        $oldEmail = $usuario->email;

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];
        $usuario->secretaria_id = $data['secretaria_id'] ?? null;
        $usuario->unidad_id = $data['unidad_id'] ?? null;
        $usuario->activo = $request->boolean('activo', $usuario->activo);

        $passwordChanged = false;
        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
            $passwordChanged = true;
        }

        $usuario->save();
        $usuario->syncRoles([$data['role']]);

        $newRoleNames = $usuario->roles->pluck('name')->values()->all();

        AuthEvent::registrar('user_updated', auth()->id(), auth()->user()->email, [
            'target_user_id' => $usuario->id,
            'target_email' => $usuario->email,
            'before' => [
                'name' => $oldName,
                'email' => $oldEmail,
                'activo' => $oldActivo,
                'roles' => $oldRoleNames,
            ],
            'after' => [
                'name' => $usuario->name,
                'email' => $usuario->email,
                'activo' => (bool) $usuario->activo,
                'roles' => $newRoleNames,
            ],
        ]);

        if ($oldRoleNames !== $newRoleNames) {
            AuthEvent::registrar('roles_updated', auth()->id(), auth()->user()->email, [
                'target_user_id' => $usuario->id,
                'target_email' => $usuario->email,
                'before_roles' => $oldRoleNames,
                'after_roles' => $newRoleNames,
            ]);
        }

        if ($oldActivo !== (bool) $usuario->activo) {
            AuthEvent::registrar(
                $usuario->activo ? 'user_activated' : 'user_deactivated',
                auth()->id(),
                auth()->user()->email,
                [
                    'target_user_id' => $usuario->id,
                    'target_email' => $usuario->email,
                ]
            );
        }

        if ($passwordChanged) {
            AuthEvent::registrar('password_changed', auth()->id(), auth()->user()->email, [
                'target_user_id' => $usuario->id,
                'target_email' => $usuario->email,
                'trigger' => 'admin_panel',
            ]);
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if (auth()->id() === $usuario->id) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'No puedes eliminar tu propio usuario.');
        }

        $targetUserId = $usuario->id;
        $targetEmail = $usuario->email;

        if (config('session.driver') === 'database') {
            DB::table((string) config('session.table', 'sessions'))
                ->where('user_id', $targetUserId)
                ->delete();
        }

        $usuario->delete();

        AuthEvent::registrar('user_deleted', auth()->id(), auth()->user()->email, [
            'target_user_id' => $targetUserId,
            'target_email' => $targetEmail,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function cerrarSesiones(User $usuario)
    {
        if (config('session.driver') !== 'database') {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'El cierre de sesiones forzado requiere SESSION_DRIVER=database.');
        }

        $cerradas = DB::table((string) config('session.table', 'sessions'))
            ->where('user_id', $usuario->id)
            ->delete();

        AuthEvent::registrar('session_forced_logout', auth()->id(), auth()->user()->email, [
            'target_user_id' => $usuario->id,
            'target_email' => $usuario->email,
            'sessions_closed' => (int) $cerradas,
            'source' => 'admin_user_controller',
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Se cerraron {$cerradas} sesión(es) activas del usuario {$usuario->email}.");
    }
}

