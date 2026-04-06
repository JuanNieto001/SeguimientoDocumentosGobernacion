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
        // Cuando se accede por host publico (ngrok/IP), evitar que @vite use localhost:5173.
        if (! app()->runningInConsole()) {
            $requestHost = request()->getHost();
            $appUrlHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            $localHosts = array_filter([
                'localhost',
                '127.0.0.1',
                '::1',
                $appUrlHost,
            ]);

            if (! in_array($requestHost, $localHosts, true)) {
                Vite::useHotFile(public_path('hot-disabled'));
            }
        }

        // Registrar policies
        Gate::policy(ContractProcess::class, ContractProcessPolicy::class);
    }
}

