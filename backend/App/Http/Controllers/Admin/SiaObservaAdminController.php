<?php
/**
 * Archivo: backend/App/Http/Controllers/Admin/SiaObservaAdminController.php
 * Proposito: Gestion administrativa del repositorio SIA Observa.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proceso;
use App\Models\ProcesoSiaObservaAcceso;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class SiaObservaAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Proceso::query()
            ->with([
                'secretariaOrigen:id,nombre',
                'unidadOrigen:id,nombre',
                'etapaActual:id,nombre,orden',
            ])
            ->withCount([
                'siaObservaArchivos as archivos_sia_count',
                'siaObservaAccesos as accesos_sia_count',
            ])
            ->orderByDesc('id');

        $this->aplicarFiltrosProceso($query, $request);

        $procesos = $query->paginate(15)->appends($request->query());

        $secretarias = Secretaria::activas()->orderBy('nombre')->get(['id', 'nombre']);
        $unidades = collect();
        if ($request->filled('secretaria_id')) {
            $unidades = Unidad::query()
                ->activas()
                ->where('secretaria_id', $request->integer('secretaria_id'))
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        }

        return view('admin.sia-observa.index', compact('procesos', 'secretarias', 'unidades'));
    }

    public function show(Request $request, Proceso $proceso)
    {
        $proceso->load([
            'secretariaOrigen:id,nombre',
            'unidadOrigen:id,nombre',
            'etapaActual:id,nombre,orden',
            'siaObservaArchivos.subidoPor:id,name,email',
            'siaObservaAccesos.usuario:id,name,email',
            'siaObservaAccesos.asignadoPor:id,name,email',
        ]);

        $roles = Role::query()->orderBy('name')->get(['id', 'name']);

        $usuariosQuery = User::query()
            ->where('activo', true)
            ->with([
                'secretaria:id,nombre',
                'unidad:id,nombre',
            ])
            ->orderBy('name');

        if ($request->filled('user_q')) {
            $term = trim((string) $request->input('user_q'));
            $usuariosQuery->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('user_secretaria_id')) {
            $usuariosQuery->where('secretaria_id', $request->integer('user_secretaria_id'));
        }

        if ($request->filled('user_unidad_id')) {
            $usuariosQuery->where('unidad_id', $request->integer('user_unidad_id'));
        }

        $usuarios = $usuariosQuery->limit(40)->get(['id', 'name', 'email', 'secretaria_id', 'unidad_id']);

        $secretariasUsuarios = Secretaria::activas()->orderBy('nombre')->get(['id', 'nombre']);
        $unidadesUsuarios = collect();
        if ($request->filled('user_secretaria_id')) {
            $unidadesUsuarios = Unidad::query()
                ->activas()
                ->where('secretaria_id', $request->integer('user_secretaria_id'))
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        }

        return view('admin.sia-observa.show', compact(
            'proceso',
            'roles',
            'usuarios',
            'secretariasUsuarios',
            'unidadesUsuarios'
        ));
    }

    public function asignarRol(Request $request, Proceso $proceso)
    {
        $validated = $request->validate([
            'role_name' => ['required', 'string', 'exists:roles,name'],
            'puede_ver' => ['nullable', 'boolean'],
            'puede_subir' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $puedeSubir = $request->boolean('puede_subir');
        $puedeVer = $request->boolean('puede_ver', true) || $puedeSubir;

        ProcesoSiaObservaAcceso::updateOrCreate(
            [
                'proceso_id' => $proceso->id,
                'acceso_clave' => ProcesoSiaObservaAcceso::claveRol($validated['role_name']),
            ],
            [
                'asignacion_tipo' => 'rol',
                'role_name' => $validated['role_name'],
                'user_id' => null,
                'puede_ver' => $puedeVer,
                'puede_subir' => $puedeSubir,
                'activo' => $request->boolean('activo', true),
                'asignado_por' => auth()->id(),
            ]
        );

        return redirect()
            ->route('admin.sia-observa.show', ['proceso' => $proceso->id])
            ->with('success', 'Acceso por rol actualizado correctamente.');
    }

    public function asignarUsuario(Request $request, Proceso $proceso)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'puede_ver' => ['nullable', 'boolean'],
            'puede_subir' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $usuario = User::findOrFail((int) $validated['user_id']);
        $puedeSubir = $request->boolean('puede_subir');
        $puedeVer = $request->boolean('puede_ver', true) || $puedeSubir;

        ProcesoSiaObservaAcceso::updateOrCreate(
            [
                'proceso_id' => $proceso->id,
                'acceso_clave' => ProcesoSiaObservaAcceso::claveUsuario($usuario->id),
            ],
            [
                'asignacion_tipo' => 'usuario',
                'role_name' => null,
                'user_id' => $usuario->id,
                'puede_ver' => $puedeVer,
                'puede_subir' => $puedeSubir,
                'activo' => $request->boolean('activo', true),
                'asignado_por' => auth()->id(),
            ]
        );

        return redirect()
            ->route('admin.sia-observa.show', ['proceso' => $proceso->id])
            ->with('success', 'Acceso por usuario actualizado correctamente.');
    }

    public function cambiarEstado(Request $request, Proceso $proceso, ProcesoSiaObservaAcceso $acceso)
    {
        abort_if((int) $acceso->proceso_id !== (int) $proceso->id, 404);

        $validated = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        $acceso->update([
            'activo' => (bool) $validated['activo'],
            'asignado_por' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.sia-observa.show', ['proceso' => $proceso->id])
            ->with('success', 'Estado del acceso actualizado correctamente.');
    }

    private function aplicarFiltrosProceso(Builder $query, Request $request): void
    {
        if ($request->filled('cedula')) {
            $term = trim((string) $request->input('cedula'));
            $query->where('contratista_documento', 'like', "%{$term}%");
        }

        if ($request->filled('nombre')) {
            $term = trim((string) $request->input('nombre'));
            $query->where('contratista_nombre', 'like', "%{$term}%");
        }

        if ($request->filled('codigo_proceso')) {
            $term = trim((string) $request->input('codigo_proceso'));
            $query->where('codigo', 'like', "%{$term}%");
        }

        if ($request->filled('codigo_contrato')) {
            $term = trim((string) $request->input('codigo_contrato'));
            $query->where('numero_contrato', 'like', "%{$term}%");
        }

        if ($request->filled('codigo_secop')) {
            $term = trim((string) $request->input('codigo_secop'));
            $query->where(function (Builder $q) use ($term) {
                $q->where('secop_codigo', 'like', "%{$term}%")
                    ->orWhere('numero_proceso_secop', 'like', "%{$term}%");
            });
        }

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_origen_id', $request->integer('secretaria_id'));
        }

        if ($request->filled('unidad_id')) {
            $query->where('unidad_origen_id', $request->integer('unidad_id'));
        }
    }
}
