<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class SystemRestoreCommand extends Command
{
    protected $signature = 'system:restore {backup_dir : Carpeta del backup a restaurar} {--force : Ejecuta la restauración real}';

    protected $description = 'Restaura base de datos y archivos desde un backup generado por system:backup.';

    public function handle(): int
    {
        $backupDirArg = (string) $this->argument('backup_dir');
        $backupDir = $this->resolveBackupPath($backupDirArg);

        if (!File::isDirectory($backupDir)) {
            $this->error('Directorio de backup no encontrado: ' . $backupDir);
            return Command::FAILURE;
        }

        $manifestPath = $backupDir . DIRECTORY_SEPARATOR . 'manifest.json';
        if (!File::exists($manifestPath)) {
            $this->error('No se encontró manifest.json en el backup.');
            return Command::FAILURE;
        }

        if (!$this->option('force')) {
            $this->warn('Modo simulación: no se aplicaron cambios.');
            $this->line('Backup objetivo: ' . $backupDir);
            $this->line('Ejecuta de nuevo con --force para restaurar.');
            return Command::SUCCESS;
        }

        $db = $this->restoreDatabase($backupDir);
        $files = $this->restoreFiles($backupDir);

        if (!$db['ok'] || !$files['ok']) {
            $this->error('Restauración finalizada con errores.');
            $this->line('DB: ' . ($db['message'] ?? 'sin detalle'));
            $this->line('FILES: ' . ($files['message'] ?? 'sin detalle'));
            return Command::FAILURE;
        }

        $this->info('Restauración completada correctamente.');
        return Command::SUCCESS;
    }

    private function resolveBackupPath(string $arg): string
    {
        if (File::isDirectory($arg)) {
            return $arg;
        }

        return rtrim((string) config('operations.backup.path'), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . ltrim($arg, '\\/');
    }

    private function restoreDatabase(string $backupDir): array
    {
        $driver = (string) config('database.default');

        if ($driver === 'sqlite') {
            $source = $backupDir . DIRECTORY_SEPARATOR . 'database.sqlite';
            if (!File::exists($source)) {
                return ['ok' => false, 'message' => 'No existe database.sqlite en el backup.'];
            }

            $target = (string) config('database.connections.sqlite.database');
            if (!str_starts_with($target, DIRECTORY_SEPARATOR) && !preg_match('/^[A-Za-z]:\\\\/', $target)) {
                $target = database_path($target);
            }

            File::ensureDirectoryExists(dirname($target));
            File::copy($source, $target);
            return ['ok' => true, 'message' => 'Base SQLite restaurada.'];
        }

        if ($driver !== 'mysql') {
            return ['ok' => false, 'message' => 'Driver no soportado para restore automático: ' . $driver];
        }

        $dumpFile = $backupDir . DIRECTORY_SEPARATOR . 'database.sql';
        if (!File::exists($dumpFile)) {
            return ['ok' => false, 'message' => 'No existe database.sql en el backup.'];
        }

        $conn = config('database.connections.mysql');
        $binary = (string) config('operations.backup.mysql_binary', 'mysql');

        $cmd = [
            escapeshellarg($binary),
            '--host=' . escapeshellarg((string) ($conn['host'] ?? '127.0.0.1')),
            '--port=' . escapeshellarg((string) ($conn['port'] ?? '3306')),
            '--user=' . escapeshellarg((string) ($conn['username'] ?? 'root')),
            escapeshellarg((string) ($conn['database'] ?? '')),
            '--execute=' . escapeshellarg('source ' . $dumpFile),
        ];

        $password = (string) ($conn['password'] ?? '');
        if ($password !== '') {
            $cmd[] = '--password=' . escapeshellarg($password);
        }

        $result = Process::run(implode(' ', $cmd));
        if (!$result->successful()) {
            return ['ok' => false, 'message' => 'Error restaurando MySQL: ' . $result->errorOutput()];
        }

        return ['ok' => true, 'message' => 'Base MySQL restaurada.'];
    }

    private function restoreFiles(string $backupDir): array
    {
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . 'files.zip';
        if (!File::exists($zipPath)) {
            return ['ok' => false, 'message' => 'No existe files.zip en el backup.'];
        }

        $targetPath = (string) config('operations.backup.files_source_path');
        File::ensureDirectoryExists($targetPath);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['ok' => false, 'message' => 'No fue posible abrir files.zip.'];
        }

        $ok = $zip->extractTo($targetPath);
        $zip->close();

        if (!$ok) {
            return ['ok' => false, 'message' => 'No fue posible extraer files.zip'];
        }

        return ['ok' => true, 'message' => 'Archivos restaurados.'];
    }
}
