<?php

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
