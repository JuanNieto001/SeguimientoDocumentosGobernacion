<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnidadApiController extends Controller
{
    /**
     * GET /api/unidades
     */
    public function index(Request $request): JsonResponse
    {
        $query = Unidad::with('secretaria')->withCount('usuarios');

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }
        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $unidades = $query->orderBy('nombre')->get();

        return response()->json(['unidades' => $unidades]);
    }

    /**
     * POST /api/unidades
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'        => ['required', 'string', 'max:255'],
            'secretaria_id' => ['required', 'exists:secretarias,id'],
        ]);

        // Verificar unicidad compuesta
        $exists = Unidad::where('nombre', $data['nombre'])
            ->where('secretaria_id', $data['secretaria_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe una unidad con ese nombre en esta secretaría.',
            ], 422);
        }

        $unidad = Unidad::create($data);
        $unidad->load('secretaria');

        return response()->json([
            'message' => 'Unidad creada correctamente.',
            'unidad'  => $unidad,
        ], 201);
    }

    /**
     * GET /api/unidades/{unidad}
     */
    public function show(Unidad $unidad): JsonResponse
    {
        $unidad->load('secretaria');
        $unidad->loadCount('usuarios');

        return response()->json(['unidad' => $unidad]);
    }

    /**
     * PUT /api/unidades/{unidad}
     */
    public function update(Request $request, Unidad $unidad): JsonResponse
    {
        $data = $request->validate([
            'nombre'        => ['sometimes', 'string', 'max:255'],
            'secretaria_id' => ['sometimes', 'exists:secretarias,id'],
            'activo'        => ['sometimes', 'boolean'],
        ]);

        $unidad->update($data);
        $unidad->load('secretaria');

        return response()->json([
            'message' => 'Unidad actualizada correctamente.',
            'unidad'  => $unidad,
        ]);
    }

    /**
     * DELETE /api/unidades/{unidad}
     */
    public function destroy(Unidad $unidad): JsonResponse
    {
        if ($unidad->usuarios()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar: tiene usuarios asignados.',
            ], 422);
        }

        $unidad->delete();

        return response()->json(['message' => 'Unidad eliminada correctamente.']);
    }
}
