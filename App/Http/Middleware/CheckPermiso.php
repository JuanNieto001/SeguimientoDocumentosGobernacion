<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermiso
 *
 * Middleware que verifica un permiso específico usando Spatie.
 * Es un wrapper más limpio que permite mensajes de error personalizados
 * y formato JSON automático para APIs.
 *
 * Uso en rutas:
 *   ->middleware('permiso:procesos.crear')
 *   ->middleware('permiso:usuarios.ver,usuarios.crear')  // requiere cualquiera
 */
class CheckPermiso
{
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->denegado($request, 'No autenticado.');
        }

        // Admin General tiene acceso total
        if ($user->hasRole(['admin_general', 'admin'])) {
            return $next($request);
        }

        // Verificar si tiene al menos uno de los permisos
        foreach ($permisos as $permiso) {
            if ($user->can($permiso)) {
                return $next($request);
            }
        }

        return $this->denegado(
            $request,
            'No tiene los permisos necesarios: ' . implode(', ', $permisos)
        );
    }

    private function denegado(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        abort(403, $message);
    }
}
