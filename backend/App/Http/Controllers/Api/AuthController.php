<?php
/**
 * Archivo: backend/App/Http/Controllers/Api/AuthController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login
     *
     * Autentica un usuario y devuelve un token de Sanctum (si está instalado)
     * o un indicador de sesión, junto con los datos del usuario, roles y permisos.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate((string) Str::lower($request->string('email')));
        $maxAttempts = max((int) config('security.auth.max_login_attempts', 5), 1);
        $decaySeconds = max((int) config('security.auth.lockout_seconds', 300), 60);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            event(new Lockout($request));

            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => (int) ceil($seconds / 60),
                ])],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, $decaySeconds);

            if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
                event(new Lockout($request));

                $seconds = RateLimiter::availableIn($throttleKey);

                throw ValidationException::withMessages([
                    'email' => [trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => (int) ceil($seconds / 60),
                    ])],
                ]);
            }

            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (! $user->activo) {
            RateLimiter::hit($throttleKey, $decaySeconds);

            if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
                event(new Lockout($request));

                $seconds = RateLimiter::availableIn($throttleKey);

                throw ValidationException::withMessages([
                    'email' => [trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => (int) ceil($seconds / 60),
                    ])],
                ]);
            }

            throw ValidationException::withMessages([
                'email' => ['Esta cuenta está desactivada. Contacte al administrador.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Login por sesión (Laravel Breeze)
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Autenticación exitosa.',
            'user'    => $this->userData($user),
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * GET /api/me
     *
     * Retorna datos del usuario autenticado + roles + permisos.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        return response()->json([
            'user' => $this->userData($user),
        ]);
    }

    /**
     * POST /api/validar-permiso
     *
     * Verifica si el usuario autenticado tiene un permiso específico.
     */
    public function validarPermiso(Request $request): JsonResponse
    {
        $request->validate([
            'permiso' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $tienePermiso = $user->can($request->permiso);

        return response()->json([
            'permiso'       => $request->permiso,
            'autorizado'    => $tienePermiso,
            'usuario'       => $user->email,
            'roles'         => $user->getRoleNames(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function userData(User $user): array
    {
        $user->load(['secretaria', 'unidad']);

        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'activo'      => $user->activo,
            'secretaria'  => $user->secretaria ? [
                'id'     => $user->secretaria->id,
                'nombre' => $user->secretaria->nombre,
            ] : null,
            'unidad'      => $user->unidad ? [
                'id'     => $user->unidad->id,
                'nombre' => $user->unidad->nombre,
            ] : null,
            'roles'       => $user->getRoleNames(),
            'permisos'    => $user->getAllPermissions()->pluck('name'),
        ];
    }
}

