<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class SystemBackupCommand extends Command
{
    protected $signature = 'system:backup {--only= : db|files}';

    protected $description = 'Genera respaldo de base de datos y archivos documentales.';

    public function handle(): int
    {
        $only = $this->option('only');
        if ($only !== null && !in_array($only, ['db', 'files'], true)) {
            $this->error('La opción --only solo permite: db o files');
            return Command::FAILURE;
        }

        $basePath = (string) config('operations.backup.path');
        $backupDir = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'backup_' . now()->format('Ymd_His');
        File::ensureDirectoryExists($backupDir);

        $manifest = [
            'created_at' => now()->toDateTimeString(),
            'app_env' => config('app.env'),
            'backup_dir' => $backupDir,
            'db' => null,
            'files' => null,
        ];

        $dbOk = true;
        $filesOk = true;

        if ($only !== 'files') {
            $manifest['db'] = $this->backupDatabase($backupDir);
            $dbOk = (bool) ($manifest['db']['ok'] ?? false);
        }

        if ($only !== 'db') {
            $manifest['files'] = $this->backupFiles($backupDir);
            $filesOk = (bool) ($manifest['files']['ok'] ?? false);
        }

        File::put($backupDir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (($only === null && (!$dbOk || !$filesOk)) || ($only === 'db' && !$dbOk) || ($only === 'files' && !$filesOk)) {
            $this->error('Respaldo completado con errores. Revisa el manifest del backup.');
            return Command::FAILURE;
        }

        $this->info('Respaldo generado correctamente en: ' . $backupDir);
        return Command::SUCCESS;
    }

    private function backupDatabase(string $backupDir): array
    {
        $driver = (string) config('database.default');

        if ($driver === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');
            if (!str_starts_with($sqlitePath, DIRECTORY_SEPARATOR) && !preg_match('/^[A-Za-z]:\\\\/', $sqlitePath)) {
                $sqlitePath = database_path($sqlitePath);
            }

            if (!File::exists($sqlitePath)) {
                return ['ok' => false, 'message' => 'No se encontró la base SQLite: ' . $sqlitePath];
            }

            $target = $backupDir . DIRECTORY_SEPARATOR . 'database.sqlite';
            File::copy($sqlitePath, $target);
            return ['ok' => true, 'driver' => 'sqlite', 'file' => $target];
        }

        if ($driver !== 'mysql') {
            return ['ok' => false, 'message' => 'Driver no soportado para backup automático: ' . $driver];
        }

        $conn = config('database.connections.mysql');
        $dumpFile = $backupDir . DIRECTORY_SEPARATOR . 'database.sql';
        $binary = (string) config('operations.backup.mysql_dump_binary', 'mysqldump');

        $cmd = [
            escapeshellarg($binary),
            '--host=' . escapeshellarg((string) ($conn['host'] ?? '127.0.0.1')),
            '--port=' . escapeshellarg((string) ($conn['port'] ?? '3306')),
            '--user=' . escapeshellarg((string) ($conn['username'] ?? 'root')),
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            '--result-file=' . escapeshellarg($dumpFile),
        ];

        $password = (string) ($conn['password'] ?? '');
        if ($password !== '') {
            $cmd[] = '--password=' . escapeshellarg($password);
        }

        $cmd[] = escapeshellarg((string) ($conn['database'] ?? ''));

        $result = Process::run(implode(' ', $cmd));
        if (!$result->successful()) {
            return [
                'ok' => false,
                'message' => 'Falló dump MySQL: ' . $result->errorOutput(),
            ];
        }

        return ['ok' => true, 'driver' => 'mysql', 'file' => $dumpFile];
    }

    private function backupFiles(string $backupDir): array
    {
        $sourcePath = (string) config('operations.backup.files_source_path');
        if (!File::isDirectory($sourcePath)) {
            File::ensureDirectoryExists($sourcePath);
        }

        $zipPath = $backupDir . DIRECTORY_SEPARATOR . 'files.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return ['ok' => false, 'message' => 'No fue posible crear el ZIP de archivos.'];
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            $absolutePath = $file->getRealPath();
            $relativePath = ltrim(str_replace($sourcePath, '', $absolutePath), '\\/');
            $zip->addFile($absolutePath, $relativePath);
        }

        $zip->close();

        return ['ok' => true, 'file' => $zipPath];
    }
}
