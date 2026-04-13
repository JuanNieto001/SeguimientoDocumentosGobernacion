<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SystemBackupPruneCommand extends Command
{
    protected $signature = 'system:backup-prune';

    protected $description = 'Elimina respaldos antiguos según la política de retención.';

    public function handle(): int
    {
        $basePath = (string) config('operations.backup.path');
        $retentionDays = max((int) config('operations.backup.retention_days', 14), 1);
        $cutoff = now()->subDays($retentionDays);

        if (!File::isDirectory($basePath)) {
            $this->info('No existe directorio de backups, no hay nada que depurar.');
            return Command::SUCCESS;
        }

        $deleted = 0;
        foreach (File::directories($basePath) as $directory) {
            $modified = File::lastModified($directory);
            if ($modified <= $cutoff->timestamp) {
                File::deleteDirectory($directory);
                $deleted++;
            }
        }

        $this->info("Depuración completada. Backups eliminados: {$deleted}");
        return Command::SUCCESS;
    }
}
