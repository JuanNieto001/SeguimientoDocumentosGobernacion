<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Secretaria;
use App\Models\Unidad;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    public function index(Request $request)
    {
        $query = Unidad::with('secretaria')->withCount('usuarios');

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }

        $unidades = $query->orderBy('nombre')->paginate(20);
        $secretarias = Secretaria::activas()->orderBy('nombre')->get();

        return view('admin.unidades.index', compact('unidades', 'secretarias'));
    }

    public function create()
    {
        $secretarias = Secretaria::activas()->orderBy('nombre')->get();

        return view('admin.unidades.create', compact('secretarias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'        => ['required', 'string', 'max:255'],
            'secretaria_id' => ['required', 'exists:secretarias,id'],
        ]);

        $exists = Unidad::where('nombre', $data['nombre'])
            ->where('secretaria_id', $data['secretaria_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['nombre' => 'Ya existe una unidad con ese nombre en esta secretaría.'])->withInput();
        }

        Unidad::create($data);

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad creada correctamente.');
    }

    public function edit(Unidad $unidade)
    {
        $secretarias = Secretaria::activas()->orderBy('nombre')->get();

        return view('admin.unidades.edit', compact('unidade', 'secretarias'));
    }

    public function update(Request $request, Unidad $unidade)
    {
        $data = $request->validate([
            'nombre'        => ['required', 'string', 'max:255'],
            'secretaria_id' => ['required', 'exists:secretarias,id'],
            'activo'        => ['nullable', 'boolean'],
        ]);

        $unidade->nombre = $data['nombre'];
        $unidade->secretaria_id = $data['secretaria_id'];
        $unidade->activo = $request->boolean('activo', true);
        $unidade->save();

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad actualizada correctamente.');
    }

    public function destroy(Unidad $unidade)
    {
        if ($unidade->usuarios()->count() > 0) {
            return redirect()->route('admin.unidades.index')
                ->with('error', 'No se puede eliminar: tiene usuarios asignados.');
        }

        $unidade->delete();

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad eliminada correctamente.');
    }

    /**
     * AJAX: Retorna unidades de una secretaría (para select dinámico).
     */
    public function porSecretaria(Secretaria $secretaria)
    {
        $unidades = $secretaria->unidades()->activas()->orderBy('nombre')->get(['id', 'nombre']);

        return response()->json($unidades);
    }
}
