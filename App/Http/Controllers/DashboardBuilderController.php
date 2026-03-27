<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardBuilderController extends Controller
{
    /**
     * Vista principal del Dashboard Builder.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Cargar configuración inicial si existe
        $initialConfig = null;

        // Verificar si hay dashboard guardado para el usuario
        $asignacion = \App\Models\DashboardUsuarioAsignacion::where('user_id', $user->id)
            ->where('activo', true)
            ->with('plantilla')
            ->first();

        if ($asignacion && $asignacion->config_json) {
            $initialConfig = json_encode([
                'widgets' => $asignacion->config_json['widgets'] ?? [],
                'layout' => $asignacion->config_json['layout'] ?? [],
                'theme' => $asignacion->config_json['theme'] ?? 'default',
            ]);
        }

        // Modo solo lectura si el usuario no tiene permisos de edición
        $readOnly = !$user->hasAnyRole(['admin', 'admin_general', 'admin_secretaria', 'admin_unidad']);

        return view('dashboards.builder', [
            'initialConfig' => $initialConfig,
            'readOnly' => $readOnly ? 'true' : 'false',
        ]);
    }
}
