<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanTestDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:clean-test-data {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all test data from database for production deployment';

    /**
     * Lista de emails de usuarios de prueba a eliminar
     */
    private array $testEmailList = [
        // Usuarios demo
        'admin@demo.com',
        'jesin@demo.com',

        // Usuarios de prueba caldas
        'admin@caldas.gov.co',
        'admin.juridica@caldas.gov.co',
        'admin.hacienda@caldas.gov.co',
        'admin.planeacion@caldas.gov.co',
        'profesional1@caldas.gov.co',
        'profesional2@caldas.gov.co',
        'profesional3@caldas.gov.co',
        'profesional4@caldas.gov.co',
        'profesional5@caldas.gov.co',
        'juridico1@caldas.gov.co',
        'juridico2@caldas.gov.co',
        'consulta1@caldas.gov.co',
        'consulta2@caldas.gov.co',
        'consulta3@caldas.gov.co'
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 LIMPIEZA DE DATOS DE PRUEBA - FASE 3');
        $this->info('Sistema de Seguimiento Contractual - Gobernación de Caldas');
        $this->newLine();

        // Confirmación de usuario si no se usa --force
        if (!$this->option('force')) {
            $this->warn('⚠️  ADVERTENCIA: Esta operación eliminará permanentemente TODOS los datos de prueba.');
            $this->warn('   Esto incluye usuarios, PAA, procesos y configuraciones de prueba.');
            $this->newLine();

            if (!$this->confirm('¿Está seguro de que desea continuar?')) {
                $this->info('❌ Operación cancelada por el usuario.');
                return 0;
            }
        }

        $this->info('🚀 Iniciando limpieza de datos de prueba...');
        $startTime = Carbon::now();

        try {
            // Crear backup de seguridad antes de la limpieza
            $this->createBackup();

            // Fase 1: Limpiar usuarios de prueba
            $this->cleanTestUsers();

            // Fase 2: Limpiar PAA de prueba
            $this->cleanTestPAA();

            // Fase 3: Limpiar procesos de prueba
            $this->cleanTestProcesses();

            // Fase 4: Limpiar configuraciones de dashboard
            $this->cleanTestDashboardConfigurations();

            // Fase 5: Limpiar archivos temporales
            $this->cleanTestFiles();

            // Final: Reportar resultados
            $endTime = Carbon::now();
            $duration = $endTime->diffInSeconds($startTime);

            $this->newLine();
            $this->info('🎉 ¡LIMPIEZA COMPLETADA EXITOSAMENTE!');
            $this->info("⏱️  Tiempo transcurrido: {$duration} segundos");
            $this->newLine();
            $this->info('📋 Próximos pasos recomendados:');
            $this->info('   1. php artisan production:verify-readiness');
            $this->info('   2. php artisan db:seed --class=ProductionSeederStructure');
            $this->info('   3. Crear usuarios reales del sistema');
            $this->info('   4. Configurar dashboards de producción');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante la limpieza: ' . $e->getMessage());

            Log::error('Error en CleanTestDataCommand', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);

            return 1;
        }
    }

    /**
     * Crear backup de seguridad antes de la limpieza
     */
    private function createBackup(): void
    {
        $this->info('💾 Creando backup de seguridad...');

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupFile = storage_path("app/backups/backup_before_cleanup_{$timestamp}.sql");

        // Asegurar que existe el directorio de backups
        if (!file_exists(dirname($backupFile))) {
            mkdir(dirname($backupFile), 0755, true);
        }

        // Comando de backup para SQLite
        if (config('database.default') === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            $backupPath = storage_path("app/backups/database_backup_{$timestamp}.sqlite");

            if (file_exists($dbPath)) {
                copy($dbPath, $backupPath);
                $this->info("   ✅ Backup creado: {$backupPath}");
            }
        }
    }

    /**
     * Limpiar usuarios de prueba
     */
    private function cleanTestUsers(): void
    {
        $this->info('👥 Limpiando usuarios de prueba...');

        // Contar usuarios a eliminar
        $testUsersCount = User::whereIn('email', $this->testEmailList)->count();

        if ($testUsersCount > 0) {
            // Eliminar usuarios de prueba
            $deleted = User::whereIn('email', $this->testEmailList)->delete();

            $this->info("   ✅ Eliminados {$deleted} usuarios de prueba");

            // Log para auditoria
            Log::info('Usuarios de prueba eliminados', [
                'count' => $deleted,
                'emails' => $this->testEmailList,
                'timestamp' => now()
            ]);
        } else {
            $this->info('   ℹ️  No se encontraron usuarios de prueba para eliminar');
        }

        // Verificar usuarios con emails sospechosos
        $suspiciousUsers = User::where('email', 'like', '%demo%')
                              ->orWhere('email', 'like', '%test%')
                              ->orWhere('email', 'like', '%@example.com')
                              ->count();

        if ($suspiciousUsers > 0) {
            $this->warn("   ⚠️  Se encontraron {$suspiciousUsers} usuarios con emails sospechosos");
            $this->warn('   Revisar manualmente y eliminar si corresponde');
        }
    }

    /**
     * Limpiar PAA de prueba
     */
    private function cleanTestPAA(): void
    {
        $this->info('📄 Limpiando PAA de prueba...');

        // Eliminar PAA con códigos de prueba
        $testPAACount = DB::table('plan_anual_adquisiciones')
            ->where('anio', 2026)
            ->where(function($query) {
                $query->where('codigo_necesidad', 'like', 'PAA-2026-%')
                      ->orWhere('descripcion', 'like', '%prueba%')
                      ->orWhere('descripcion', 'like', '%test%')
                      ->orWhere('descripcion', 'like', '%demo%');
            })
            ->count();

        if ($testPAACount > 0) {
            $deleted = DB::table('plan_anual_adquisiciones')
                ->where('anio', 2026)
                ->where(function($query) {
                    $query->where('codigo_necesidad', 'like', 'PAA-2026-%')
                          ->orWhere('descripcion', 'like', '%prueba%')
                          ->orWhere('descripcion', 'like', '%test%')
                          ->orWhere('descripcion', 'like', '%demo%');
                })
                ->delete();

            $this->info("   ✅ Eliminadas {$deleted} entradas de PAA de prueba");
        } else {
            $this->info('   ℹ️  No se encontraron PAA de prueba para eliminar');
        }
    }

    /**
     * Limpiar procesos de prueba
     */
    private function cleanTestProcesses(): void
    {
        $this->info('⚙️ Limpiando procesos de prueba...');

        // Verificar si existe la tabla procesos
        if (DB::getSchemaBuilder()->hasTable('procesos')) {
            $testProcessesCount = DB::table('procesos')
                ->where(function($query) {
                    $query->where('nombre', 'like', '%prueba%')
                          ->orWhere('nombre', 'like', '%test%')
                          ->orWhere('nombre', 'like', '%demo%')
                          ->orWhere('objeto', 'like', '%prueba%')
                          ->orWhere('objeto', 'like', '%test%');
                })
                ->count();

            if ($testProcessesCount > 0) {
                $deleted = DB::table('procesos')
                    ->where(function($query) {
                        $query->where('nombre', 'like', '%prueba%')
                              ->orWhere('nombre', 'like', '%test%')
                              ->orWhere('nombre', 'like', '%demo%')
                              ->orWhere('objeto', 'like', '%prueba%')
                              ->orWhere('objeto', 'like', '%test%');
                    })
                    ->delete();

                $this->info("   ✅ Eliminados {$deleted} procesos de prueba");
            } else {
                $this->info('   ℹ️  No se encontraron procesos de prueba para eliminar');
            }
        } else {
            $this->info('   ℹ️  Tabla procesos no existe aún');
        }
    }

    /**
     * Limpiar configuraciones de dashboard de prueba
     */
    private function cleanTestDashboardConfigurations(): void
    {
        $this->info('📊 Limpiando configuraciones de dashboard de prueba...');

        // Limpiar asignaciones de dashboard de usuarios eliminados
        if (DB::getSchemaBuilder()->hasTable('dashboard_usuario_asignaciones')) {
            $orphanedAssignments = DB::table('dashboard_usuario_asignaciones')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('users')
                          ->whereColumn('users.id', 'dashboard_usuario_asignaciones.user_id');
                })
                ->count();

            if ($orphanedAssignments > 0) {
                $deleted = DB::table('dashboard_usuario_asignaciones')
                    ->whereNotExists(function($query) {
                        $query->select(DB::raw(1))
                              ->from('users')
                              ->whereColumn('users.id', 'dashboard_usuario_asignaciones.user_id');
                    })
                    ->delete();

                $this->info("   ✅ Eliminadas {$deleted} configuraciones de dashboard huérfanas");
            } else {
                $this->info('   ℹ️  No se encontraron configuraciones huérfanas');
            }
        }

        // Limpiar widgets de prueba
        if (DB::getSchemaBuilder()->hasTable('dashboard_widgets')) {
            $testWidgets = DB::table('dashboard_widgets')
                ->where(function($query) {
                    $query->where('titulo', 'like', '%test%')
                          ->orWhere('titulo', 'like', '%prueba%')
                          ->orWhere('titulo', 'like', '%demo%');
                })
                ->count();

            if ($testWidgets > 0) {
                $deleted = DB::table('dashboard_widgets')
                    ->where(function($query) {
                        $query->where('titulo', 'like', '%test%')
                              ->orWhere('titulo', 'like', '%prueba%')
                              ->orWhere('titulo', 'like', '%demo%');
                    })
                    ->delete();

                $this->info("   ✅ Eliminados {$deleted} widgets de prueba");
            } else {
                $this->info('   ℹ️  No se encontraron widgets de prueba');
            }
        }
    }

    /**
     * Limpiar archivos temporales de prueba
     */
    private function cleanTestFiles(): void
    {
        $this->info('📁 Limpiando archivos temporales de prueba...');

        $tempPath = storage_path('app/temp');
        $testPath = storage_path('app/test');

        $deletedCount = 0;

        // Limpiar directorio temporal
        if (is_dir($tempPath)) {
            $files = glob($tempPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $deletedCount++;
                }
            }
        }

        // Limpiar directorio de pruebas
        if (is_dir($testPath)) {
            $files = glob($testPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            $this->info("   ✅ Eliminados {$deletedCount} archivos temporales");
        } else {
            $this->info('   ℹ️  No se encontraron archivos temporales para eliminar');
        }
    }
}