<?php

namespace App\Http\Middleware;

use App\Enums\EstadoProcesoCD;
use App\Models\ProcesoContratacionDirecta;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que valida que el usuario autenticado tenga un rol
 * autorizado para operar sobre el estado actual del proceso CD-PN.
 *
 * Uso en rutas:
 *   ->middleware('validar.rol.proceso.cd')
 *
 * El middleware busca el parámetro de ruta {procesoCD} (model binding).
 * Si no lo encuentra, busca {proceso_cd} o extrae el id numérico.
 */
class ValidateRolProcesoCD
{
    public function handle(Request $request, Closure $next): Response
    {
        $proceso = $this->resolverProceso($request);

        if (!$proceso) {
            return $next($request); // No aplica, dejar pasar (p.ej. crear)
        }

        $user = $request->user();

        if (!$user) {
            abort(401, 'No autenticado.');
        }

        // Admin siempre pasa
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Verificar que el usuario tenga rol para el estado actual
        if (!$proceso->usuarioPuedeOperar($user)) {
            abort(403, sprintf(
                'No tiene permiso para operar en el estado «%s». Roles requeridos: %s',
                $proceso->estado->label(),
                implode(', ', $proceso->estado->rolesAutorizados())
            ));
        }

        return $next($request);
    }

    protected function resolverProceso(Request $request): ?ProcesoContratacionDirecta
    {
        // 1. Model binding directo
        if ($request->route('procesoCD') instanceof ProcesoContratacionDirecta) {
            return $request->route('procesoCD');
        }

        // 2. Por nombre de parámetro alternativo
        foreach (['procesoCD', 'proceso_cd', 'proceso'] as $param) {
            $valor = $request->route($param);
            if ($valor instanceof ProcesoContratacionDirecta) {
                return $valor;
            }
            if (is_numeric($valor)) {
                return ProcesoContratacionDirecta::find($valor);
            }
        }

        return null;
    }
}
