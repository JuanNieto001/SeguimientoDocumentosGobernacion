<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckUsuarioActivo
 *
 * Verifica que el usuario autenticado tenga la cuenta activa.
 * Si está desactivado, cierra la sesión y redirige al login.
 *
 * Uso:
 *   ->middleware('usuario.activo')
 */
class CheckUsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->activo) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Su cuenta ha sido desactivada. Contacte al administrador.',
                ], 403);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Su cuenta ha sido desactivada. Contacte al administrador.']);
        }

        return $next($request);
    }
}
