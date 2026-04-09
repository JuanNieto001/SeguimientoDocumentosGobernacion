<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Secretaria;
use App\Models\Unidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecretariaApiController extends Controller
{
    /**
     * GET /api/secretarias
     */
    public function index(Request $request): JsonResponse
    {
        $query = Secretaria::withCount('unidades', 'usuarios');

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $secretarias = $query->orderBy('nombre')->get();

        return response()->json(['secretarias' => $secretarias]);
    }

    /**
     * POST /api/secretarias
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:secretarias,nombre'],
        ]);

        $secretaria = Secretaria::create($data);

        return response()->json([
            'message'    => 'Secretaría creada correctamente.',
            'secretaria' => $secretaria,
        ], 201);
    }

    /**
     * GET /api/secretarias/{secretaria}
     */
    public function show(Secretaria $secretaria): JsonResponse
    {
        $secretaria->load('unidades');
        $secretaria->loadCount('usuarios');

        return response()->json(['secretaria' => $secretaria]);
    }

    /**
     * PUT /api/secretarias/{secretaria}
     */
    public function update(Request $request, Secretaria $secretaria): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255', 'unique:secretarias,nombre,' . $secretaria->id],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $secretaria->update($data);

        return response()->json([
            'message'    => 'Secretaría actualizada correctamente.',
            'secretaria' => $secretaria,
        ]);
    }

    /**
     * DELETE /api/secretarias/{secretaria}
     */
    public function destroy(Secretaria $secretaria): JsonResponse
    {
        if ($secretaria->usuarios()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar: tiene usuarios asignados.',
            ], 422);
        }

        $secretaria->delete();

        return response()->json(['message' => 'Secretaría eliminada correctamente.']);
    }

    /**
     * GET /api/secretarias/{secretaria}/unidades
     *
     * Lista las unidades de una secretaría específica.
     */
    public function unidades(Secretaria $secretaria): JsonResponse
    {
        $unidades = $secretaria->unidades()
            ->withCount('usuarios')
            ->orderBy('nombre')
            ->get();

        return response()->json(['unidades' => $unidades]);
    }
}
