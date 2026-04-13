<?php

namespace App\Console\Commands;

use App\Models\SystemHealthCheck;
use Illuminate\Console\Command;

class SystemAvailabilityReportCommand extends Command
{
    protected $signature = 'system:availability-report {--days=30 : Ventana de días para el cálculo}';

    protected $description = 'Calcula disponibilidad histórica del sistema con base en health checks.';

    public function handle(): int
    {
        $days = max((int) $this->option('days'), 1);
        $target = (float) config('operations.availability.target_percentage', 95);

        $query = SystemHealthCheck::where('checked_at', '>=', now()->subDays($days));
        $total = (int) (clone $query)->count();

        if ($total === 0) {
            $this->warn('No hay health checks registrados para el período indicado.');
            return Command::FAILURE;
        }

        $ok = (int) (clone $query)->where('status', 'ok')->count();
        $degraded = (int) (clone $query)->where('status', 'degraded')->count();
        $down = (int) (clone $query)->where('status', 'down')->count();

        $available = $ok + $degraded;
        $availability = round(($available / $total) * 100, 2);

        $this->info("Disponibilidad últimos {$days} día(s): {$availability}%");
        $this->line("Checks: total={$total}, ok={$ok}, degradado={$degraded}, caído={$down}");
        $this->line("Objetivo configurado: {$target}%");

        if ($availability < $target) {
            $this->error('La disponibilidad está por debajo del objetivo configurado.');
            return Command::FAILURE;
        }

        $this->info('Disponibilidad dentro del objetivo.');
        return Command::SUCCESS;
    }
}
