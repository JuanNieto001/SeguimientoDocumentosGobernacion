<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Dashboard\EntityRegistry;

/**
 * API para consultar entidades y sus campos
 */
class EntityMenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.builder.access');
    }

    /**
     * Lista todas las entidades disponibles
     */
    public function index()
    {
        try {
            return response()->json([
                'entities' => EntityRegistry::all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar entidades',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los campos de una entidad específica
     */
    public function fields($entity)
    {
        try {
            if (!EntityRegistry::exists($entity)) {
                return response()->json([
                    'error' => 'Entidad no encontrada'
                ], 404);
            }

            return response()->json([
                'entity' => $entity,
                'fields' => EntityRegistry::getFields($entity)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar campos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}