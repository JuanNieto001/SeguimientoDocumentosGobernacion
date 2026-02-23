<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Secretaria;
use App\Models\Unidad;
use Illuminate\Http\Request;

class SecretariaController extends Controller
{
    public function index()
    {
        $secretarias = Secretaria::withCount(['unidades', 'usuarios'])
            ->orderBy('nombre')
            ->paginate(20);

        return view('admin.secretarias.index', compact('secretarias'));
    }

    public function create()
    {
        return view('admin.secretarias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:secretarias,nombre'],
        ]);

        Secretaria::create($data);

        return redirect()->route('admin.secretarias.index')
            ->with('success', 'Secretaría creada correctamente.');
    }

    public function edit(Secretaria $secretaria)
    {
        $secretaria->load('unidades');

        return view('admin.secretarias.edit', compact('secretaria'));
    }

    public function update(Request $request, Secretaria $secretaria)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:secretarias,nombre,' . $secretaria->id],
            'activo' => ['nullable', 'boolean'],
        ]);

        $secretaria->nombre = $data['nombre'];
        $secretaria->activo = $request->boolean('activo', true);
        $secretaria->save();

        return redirect()->route('admin.secretarias.index')
            ->with('success', 'Secretaría actualizada correctamente.');
    }

    public function destroy(Secretaria $secretaria)
    {
        if ($secretaria->usuarios()->count() > 0) {
            return redirect()->route('admin.secretarias.index')
                ->with('error', 'No se puede eliminar: tiene usuarios asignados.');
        }

        $secretaria->delete();

        return redirect()->route('admin.secretarias.index')
            ->with('success', 'Secretaría eliminada correctamente.');
    }
}
