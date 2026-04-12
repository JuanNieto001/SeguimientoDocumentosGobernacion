<?php
/**
 * Archivo: backend/App/Providers/AppServiceProvider.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

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
        $this->registerViewLocations();

        // Cuando se accede por host publico (IP o dominio), evitar que @vite use localhost:5173.
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

    /**
     * Add backend/frontend Blade locations while preserving existing view() names.
     */
    private function registerViewLocations(): void
    {
        $finder = app('view')->getFinder();

        foreach ([resource_path('views/backend'), resource_path('views/frontend')] as $path) {
            if (is_dir($path) && ! in_array($path, $finder->getPaths(), true)) {
                $finder->addLocation($path);
            }
        }
    }
}


