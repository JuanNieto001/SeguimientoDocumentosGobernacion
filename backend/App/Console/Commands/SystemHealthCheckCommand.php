<?php

namespace App\Console\Commands;

use App\Models\SystemHealthCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SystemHealthCheckCommand extends Command
{
    protected $signature = 'system:health-check';

    protected $description = 'Ejecuta chequeo de salud del sistema (DB, cache, storage y concurrencia).';

    public function handle(): int
    {
        $startedAt = microtime(true);
        $checks = [];
        $messages = [];
        $status = 'ok';

        // DB
        try {
            DB::select('SELECT 1');
            $checks['database'] = 'ok';
        } catch (\Throwable $e) {
            $checks['database'] = 'down';
            $messages[] = 'Base de datos no disponible: ' . $e->getMessage();
            $status = 'down';
        }

        // Cache
        try {
            $cacheKey = 'health_check_ping';
            $cacheValue = now()->timestamp;
            Cache::put($cacheKey, $cacheValue, 60);
            $readBack = Cache::get($cacheKey);
            if ((string) $readBack === (string) $cacheValue) {
                $checks['cache'] = 'ok';
            } else {
                $checks['cache'] = 'degraded';
                $messages[] = 'No fue posible validar lectura/escritura de cache.';
                $status = $this->degrade($status);
            }
        } catch (\Throwable $e) {
            $checks['cache'] = 'degraded';
            $messages[] = 'Cache degradado: ' . $e->getMessage();
            $status = $this->degrade($status);
        }

        // Storage backup path
        $backupPath = (string) config('operations.backup.path');
        try {
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            if (!is_writable($backupPath)) {
                $checks['backup_path'] = 'degraded';
                $messages[] = "Ruta de backups sin permisos de escritura: {$backupPath}";
                $status = $this->degrade($status);
            } else {
                $checks['backup_path'] = 'ok';
            }
        } catch (\Throwable $e) {
            $checks['backup_path'] = 'degraded';
            $messages[] = 'Error validando ruta de backups: ' . $e->getMessage();
            $status = $this->degrade($status);
        }

        // Concurrencia activa
        $activeSessions = 0;
        $targetConcurrentUsers = max((int) config('operations.concurrency.target_active_users', 100), 1);
        $sessionTable = (string) config('session.table', 'sessions');

        try {
            if (Schema::hasTable($sessionTable)) {
                $activeSessions = (int) DB::table($sessionTable)
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count('user_id');

                $checks['concurrency'] = $activeSessions <= $targetConcurrentUsers ? 'ok' : 'degraded';

                if ($activeSessions > $targetConcurrentUsers) {
                    $messages[] = "Concurrencia activa {$activeSessions} supera objetivo {$targetConcurrentUsers}.";
                    $status = $this->degrade($status);
                }
            } else {
                $checks['concurrency'] = 'degraded';
                $messages[] = "Tabla de sesiones '{$sessionTable}' no encontrada.";
                $status = $this->degrade($status);
            }
        } catch (\Throwable $e) {
            $checks['concurrency'] = 'degraded';
            $messages[] = 'No fue posible medir concurrencia: ' . $e->getMessage();
            $status = $this->degrade($status);
        }

        $responseMs = (int) round((microtime(true) - $startedAt) * 1000);
        if ($responseMs > 10000) {
            $status = $this->degrade($status);
            $messages[] = "Tiempo de chequeo alto: {$responseMs} ms";
        }

        $message = !empty($messages)
            ? implode(' | ', $messages)
            : 'Chequeo exitoso';

        try {
            SystemHealthCheck::create([
                'checked_at' => now(),
                'status' => $status,
                'response_ms' => $responseMs,
                'active_sessions' => $activeSessions,
                'target_concurrent_users' => $targetConcurrentUsers,
                'checks' => $checks,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            $this->warn('No se pudo persistir el health check: ' . $e->getMessage());
        }

        $statusLabel = strtoupper($status);
        $this->line("Estado: {$statusLabel}");
        $this->line("Tiempo de respuesta: {$responseMs} ms");
        $this->line("Sesiones activas: {$activeSessions} / objetivo {$targetConcurrentUsers}");
        if (!empty($messages)) {
            foreach ($messages as $line) {
                $this->warn('- ' . $line);
            }
        }

        return $status === 'down' ? Command::FAILURE : Command::SUCCESS;
    }

    private function degrade(string $status): string
    {
        return $status === 'down' ? 'down' : 'degraded';
    }
}
