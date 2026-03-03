<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckAdminUnidad – Middleware para verificar que el usuario es
 * administrador de su unidad solicitante.
 *
 * Solo los usuarios con rol 'admin', 'admin_general' o 'admin_unidad'
 * pueden modificar la configuración de flujos de su Secretaría.
 *
 * Uso en rutas:
 *   ->middleware('admin.unidad')
 *   ->middleware('admin.unidad:flujos.editar')  // con permiso adicional
 */
class CheckAdminUnidad
{
    public function handle(Request $request, Closure $next, ?string $permiso = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->denegado($request, 'No autenticado.');
        }

        // Admin general tiene acceso completo
        if ($user->hasRole(['admin', 'admin_general'])) {
            return $next($request);
        }

        // Verificar rol admin_unidad
        if (!$user->hasRole('admin_unidad')) {
            return $this->denegado(
                $request,
                'Solo el administrador de la unidad solicitante puede realizar esta acción.'
            );
        }

        // Si se requiere un permiso específico adicional
        if ($permiso && !$user->can($permiso)) {
            return $this->denegado(
                $request,
                "No tiene el permiso requerido: {$permiso}"
            );
        }

        return $next($request);
    }

    private function denegado(Request $request, string $mensaje): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $mensaje,
                'error'   => 'forbidden',
            ], 403);
        }

        abort(403, $mensaje);
    }
}
