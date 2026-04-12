<?php
/**
 * Archivo: backend/bootstrap/app.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Confiar en proxy inverso para detectar correctamente HTTPS y host publico.
        $middleware->trustProxies(at: '*');

        // 🌐 Compartir sesión web con rutas API (para SPA React)
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);

        // 🔐 Alias de middlewares para Spatie (OBLIGATORIO)
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'secretaria.access'  => \App\Http\Middleware\CheckSecretariaAccess::class,
            'usuario.activo'     => \App\Http\Middleware\CheckUsuarioActivo::class,
            'permiso'            => \App\Http\Middleware\CheckPermiso::class,
            'validar.rol.proceso.cd' => \App\Http\Middleware\ValidateRolProcesoCD::class,
            'admin.unidad'       => \App\Http\Middleware\CheckAdminUnidad::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para acceder a este recurso.',
                ], 403);
            }

            return redirect()
                ->route('dashboards.mi')
                ->with('error', 'No tienes permisos para acceder a esa sección.');
        });
    })
    ->create();

