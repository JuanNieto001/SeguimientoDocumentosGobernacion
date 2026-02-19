<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckSecretariaAccess
 *
 * Middleware que restringe el acceso según la secretaría del usuario.
 * - Admin General y admin: acceso sin restricción.
 * - Admin Secretaría: solo accede a datos de su propia secretaría.
 * - Otros: solo acceden a datos de su unidad/secretaría.
 *
 * Uso en rutas:
 *   ->middleware('secretaria.access')
 *   ->middleware('secretaria.access:strict')  // exige secretaria_id en la ruta o request
 */
class CheckSecretariaAccess
{
    public function handle(Request $request, Closure $next, string $mode = 'default'): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->denegado($request, 'No autenticado.');
        }

        // Admin General / admin → acceso total
        if ($user->hasRole(['admin_general', 'admin'])) {
            return $next($request);
        }

        // Intentar obtener la secretaría solicitada
        $secretariaId = $request->route('secretaria')
            ?? $request->input('secretaria_id')
            ?? null;

        // Si es modo estricto y no se puede determinar la secretaría
        if ($mode === 'strict' && $secretariaId === null) {
            return $this->denegado($request, 'Se requiere especificar la secretaría.');
        }

        // Si hay una secretaría solicitada, verificar acceso
        if ($secretariaId !== null) {
            $secretariaIdInt = is_object($secretariaId) ? $secretariaId->id : (int) $secretariaId;

            if ($user->secretaria_id !== $secretariaIdInt) {
                return $this->denegado($request, 'No tiene acceso a esta secretaría.');
            }
        }

        return $next($request);
    }

    private function denegado(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        abort(403, $message);
    }
}
