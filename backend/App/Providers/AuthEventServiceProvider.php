<?php
/**
 * Archivo: backend/App/Providers/AuthEventServiceProvider.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Providers;

use App\Listeners\LogAuthEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class AuthEventServiceProvider extends ServiceProvider
{
    /**
     * Subscribers registrados automáticamente.
     */
    protected $subscribe = [
        LogAuthEvent::class,
    ];
}

