<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserApiController extends Controller
{
    /**
     * GET /api/usuarios
     *
     * Lista usuarios con filtros opcionales por secretaría, unidad y rol.
     * Admin General: ve todos.
     * Admin Secretaría: ve solo los de su secretaría.
     * Otros: solo se ven a sí mismos.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = User::with(['secretaria', 'unidad', 'roles']);

        // Filtro de alcance según rol
        if ($user->hasRole('admin_general') || $user->hasRole('admin')) {
            // Ve todos
        } elseif ($user->hasRole('admin_secretaria')) {
            $query->where('secretaria_id', $user->secretaria_id);
        } else {
            $query->where('id', $user->id);
        }

        // Filtros opcionales
        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }
        if ($request->filled('rol')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->rol));
        }
        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $usuarios = $query->orderBy('name')->paginate(20);

        return response()->json($usuarios);
    }

    /**
     * POST /api/usuarios
     *
     * Crea un usuario y le asigna un rol.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
            'secretaria_id' => ['nullable', 'exists:secretarias,id'],
            'unidad_id'     => ['nullable', 'exists:unidades,id'],
            'rol'           => ['required', 'string', 'exists:roles,name'],
            'activo'        => ['nullable', 'boolean'],
        ]);

        // Validar que admin_secretaria solo cree usuarios en su secretaría
        $creator = $request->user();
        if ($creator->hasRole('admin_secretaria') && !$creator->hasRole('admin_general') && !$creator->hasRole('admin')) {
            if (isset($data['secretaria_id']) && $data['secretaria_id'] !== $creator->secretaria_id) {
                return response()->json([
                    'message' => 'Solo puede crear usuarios dentro de su secretaría.',
                ], 403);
            }
            $data['secretaria_id'] = $creator->secretaria_id;
        }

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'secretaria_id' => $data['secretaria_id'] ?? null,
            'unidad_id'     => $data['unidad_id'] ?? null,
            'activo'        => $data['activo'] ?? true,
        ]);

        $user->assignRole($data['rol']);
        $user->load(['secretaria', 'unidad', 'roles']);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'user'    => $user,
        ], 201);
    }

    /**
     * GET /api/usuarios/{user}
     */
    public function show(User $user, Request $request): JsonResponse
    {
        $authUser = $request->user();

        // Verificar acceso
        if (!$authUser->hasRole(['admin_general', 'admin'])) {
            if ($authUser->hasRole('admin_secretaria') && $user->secretaria_id !== $authUser->secretaria_id) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }
            if (!$authUser->hasRole('admin_secretaria') && $user->id !== $authUser->id) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }
        }

        $user->load(['secretaria', 'unidad', 'roles', 'permissions']);

        return response()->json(['user' => $user]);
    }

    /**
     * PUT /api/usuarios/{user}
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'      => ['nullable', 'string', 'min:8'],
            'secretaria_id' => ['nullable', 'exists:secretarias,id'],
            'unidad_id'     => ['nullable', 'exists:unidades,id'],
            'rol'           => ['sometimes', 'string', 'exists:roles,name'],
            'activo'        => ['nullable', 'boolean'],
        ]);

        $user->fill(collect($data)->except(['password', 'rol'])->toArray());

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if (!empty($data['rol'])) {
            $user->syncRoles([$data['rol']]);
        }

        $user->load(['secretaria', 'unidad', 'roles']);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'user'    => $user,
        ]);
    }

    /**
     * DELETE /api/usuarios/{user}
     */
    public function destroy(User $user, Request $request): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puede eliminar su propio usuario.',
            ], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }
}
