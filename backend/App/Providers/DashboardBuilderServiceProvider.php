<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Dashboard\DynamicQueryEngine;
use App\Services\Dashboard\ScopeFilterService;

class DashboardBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar ScopeFilterService como singleton
        $this->app->singleton(ScopeFilterService::class, function ($app) {
            return new ScopeFilterService();
        });

        // Registrar DynamicQueryEngine (depende de ScopeFilterService)
        $this->app->singleton(DynamicQueryEngine::class, function ($app) {
            return new DynamicQueryEngine(
                $app->make(ScopeFilterService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Limpiar cache de scope cuando cambian roles de usuario
        \App\Models\User::updated(function ($user) {
            if ($user->isDirty('secretaria_id') || $user->isDirty('unidad_id')) {
                app(ScopeFilterService::class)->clearUserScopeCache($user);
            }
        });
    }
}
