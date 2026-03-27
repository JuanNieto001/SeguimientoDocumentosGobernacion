<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use App\Models\ContractProcess;
use App\Policies\ContractProcessPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        ContractProcess::class => ContractProcessPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar policies
        Gate::policy(ContractProcess::class, ContractProcessPolicy::class);

        if (!app()->runningInConsole()) {
            $requestHost = strtolower((string) request()->getHost());
            $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

            $isLocalRequest = in_array($requestHost, ['localhost', '127.0.0.1', '::1'], true);
            $isAppHostRequest = $appHost !== '' && $requestHost === $appHost;

            // Cuando se accede por ngrok/u otro dominio remoto, evita usar public/hot
            // para no apuntar a localhost:<vite-port> que no existe fuera del equipo local.
            if (!$isLocalRequest && !$isAppHostRequest) {
                Vite::useHotFile(public_path('hot-disabled'));
            }
        }
    }
}

