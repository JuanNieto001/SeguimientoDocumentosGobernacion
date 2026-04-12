<?php
/**
 * Archivo: backend/App/Console/Commands/SyncContratosAplicacionesSecopCommand.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Console\Commands;

use App\Models\ContratoAplicacion;
use App\Services\ContratoAplicacionSecopSyncService;
use Illuminate\Console\Command;

class SyncContratosAplicacionesSecopCommand extends Command
{
    protected $signature = 'contratos-aplicaciones:sync-secop {--all : Incluye contratos inactivos}';

    protected $description = 'Sincroniza contratos de aplicaciones contra SECOP usando secop_proceso_id.';

    public function handle(ContratoAplicacionSecopSyncService $syncService): int
    {
        $query = ContratoAplicacion::query()
            ->whereNotNull('secop_proceso_id')
            ->where('secop_proceso_id', '!=', '');

        if (!$this->option('all')) {
            $query->where('activo', true);
        }

        $contratos = $query->orderBy('id')->get();

        if ($contratos->isEmpty()) {
            $this->info('No hay contratos con ID de SECOP para sincronizar.');
            return Command::SUCCESS;
        }

        $this->info('Iniciando sincronizacion de contratos de aplicaciones con SECOP...');
        $summary = $syncService->syncCollection($contratos);

        $this->line('Resultado:');
        $this->line('  - Actualizados: ' . (int) $summary['updated']);
        $this->line('  - No encontrados: ' . (int) $summary['not_found']);
        $this->line('  - Omitidos: ' . (int) $summary['skipped']);
        $this->line('  - Errores: ' . (int) $summary['errors']);

        return Command::SUCCESS;
    }
}
