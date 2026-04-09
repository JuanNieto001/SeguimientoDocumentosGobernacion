<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlertaService;

class GenerarAlertasAutomaticas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alertas:generar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera alertas automáticas para procesos (certificados por vencer, retrasos, documentos pendientes, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 Generando alertas automáticas...');

        $resultado = AlertaService::generarAlertasAutomaticas();

        $this->info("✅ Alertas generadas:");
        $this->line("   - Alertas de tiempo: {$resultado['tiempo']}");
        $this->line("   - Alertas de documentos: {$resultado['documentos']}");
        $this->line("   - Alertas de responsabilidad: {$resultado['responsabilidad']}");
        $this->line("   - TOTAL: {$resultado['total']}");

        return Command::SUCCESS;
    }
}
