<?php
/**
 * Archivo: backend/App/Listeners/LogAuthEvent.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Listeners;

use App\Models\AuthEvent;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;

class LogAuthEvent
{
    /**
     * Login exitoso
     */
    public function onLogin(Login $event): void
    {
        AuthEvent::registrar(
            'login_success',
            $event->user->id,
            $event->user->email
        );
    }

    /**
     * Cierre de sesión
     */
    public function onLogout(Logout $event): void
    {
        if ($event->user) {
            AuthEvent::registrar(
                'logout',
                $event->user->id,
                $event->user->email
            );
        }
    }

    /**
     * Intento fallido de login
     */
    public function onFailed(Failed $event): void
    {
        AuthEvent::registrar(
            'login_failed',
            null,
            $event->credentials['email'] ?? null,
            ['reason' => 'Credenciales incorrectas o cuenta inactiva']
        );
    }

    /**
     * Contraseña restablecida
     */
    public function onPasswordReset(PasswordReset $event): void
    {
        AuthEvent::registrar(
            'password_reset',
            $event->user->id,
            $event->user->email
        );
    }

    /**
     * Subscribir a múltiples eventos desde un solo listener
     */
    public function subscribe($events): array
    {
        return [
            Login::class         => 'onLogin',
            Logout::class        => 'onLogout',
            Failed::class        => 'onFailed',
            PasswordReset::class => 'onPasswordReset',
        ];
    }
}

