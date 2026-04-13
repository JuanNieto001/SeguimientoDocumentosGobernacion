<?php
/**
 * Archivo: backend/App/Http/Requests/Auth/LoginRequest.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Requests\Auth;

use App\Models\AuthEvent;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $decaySeconds = max((int) config('security.auth.lockout_seconds', 300), 60);
        $maxAttempts = max((int) config('security.auth.max_login_attempts', 5), 1);

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), $decaySeconds);

            if (RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
                event(new Lockout($this));

                $seconds = RateLimiter::availableIn($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if ($user && ! (bool) $user->activo) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey(), $decaySeconds);

            AuthEvent::registrar(
                'account_disabled',
                $user->id,
                $user->email,
                ['reason' => 'Intento de ingreso con cuenta inactiva']
            );

            throw ValidationException::withMessages([
                'email' => 'Su cuenta está desactivada. Contacte al administrador del sistema.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $maxAttempts = max((int) config('security.auth.max_login_attempts', 5), 1);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate((string) Str::lower($this->string('email')));
    }
}

