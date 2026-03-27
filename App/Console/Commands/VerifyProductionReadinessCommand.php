<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class VerifyProductionReadinessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:verify-readiness';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify system is ready for production deployment';

    /**
     * Lista de verificaciones a realizar
     */
    private array $verificationResults = [];
    private int $issuesFound = 0;
    private int $warningsFound = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 VERIFICACIÓN DE PREPARACIÓN PARA PRODUCCIÓN - FASE 3');
        $this->info('Sistema de Seguimiento Contractual - Gobernación de Caldas');
        $this->newLine();

        $startTime = Carbon::now();

        // Realizar todas las verificaciones
        $this->verifyTestUsers();
        $this->verifyWeakPasswords();
        $this->verifyTestData();
        $this->verifyAppConfiguration();
        $this->verifyRoleStructure();
        $this->verifyDatabaseIntegrity();
        $this->verifySecuritySettings();
        $this->verifyFilePermissions();

        // Mostrar resultados
        $this->showResults();

        $endTime = Carbon::now();
        $duration = $endTime->diffInSeconds($startTime);

        $this->newLine();
        $this->info("⏱️  Verificación completada en {$duration} segundos");

        // Retornar código de salida apropiado
        return $this->issuesFound > 0 ? 1 : 0;
    }

    /**
     * Verificar usuarios de prueba
     */
    private function verifyTestUsers(): void
    {
        $this->info('👥 Verificando usuarios de prueba...');

        // Lista de patrones de emails de prueba
        $testPatterns = ['%demo%', '%test%', '%@example.com', '%temp%'];

        $testUsersCount = 0;
        $testUsers = [];

        foreach ($testPatterns as $pattern) {
            $users = User::where('email', 'like', $pattern)->get(['email', 'name']);
            $testUsersCount += $users->count();
            $testUsers = array_merge($testUsers, $users->toArray());
        }

        // Verificar emails específicos de prueba
        $specificTestEmails = [
            'admin@demo.com', 'jesin@demo.com', 'admin@caldas.gov.co',
            'profesional1@caldas.gov.co', 'juridico1@caldas.gov.co'
        ];

        $specificTestUsers = User::whereIn('email', $specificTestEmails)->get(['email', 'name']);
        $testUsersCount += $specificTestUsers->count();
        $testUsers = array_merge($testUsers, $specificTestUsers->toArray());

        if ($testUsersCount > 0) {
            $this->addIssue("⚠️  Encontrados {$testUsersCount} usuarios de prueba", $testUsers);
        } else {
            $this->addSuccess('✅ No se encontraron usuarios de prueba');
        }
    }

    /**
     * Verificar contraseñas débiles
     */
    private function verifyWeakPasswords(): void
    {
        $this->info('🔐 Verificando contraseñas débiles...');

        $weakPasswords = ['password', '12345', '123456', 'admin', 'qwerty'];
        $weakPasswordCount = 0;
        $usersWithWeakPasswords = [];

        foreach ($weakPasswords as $weakPass) {
            $users = User::where('password', Hash::make($weakPass))->get(['email', 'name']);
            $weakPasswordCount += $users->count();
            $usersWithWeakPasswords = array_merge($usersWithWeakPasswords, $users->toArray());
        }

        // También verificar usuarios que deben cambiar contraseña hace más de 30 días
        $oldPasswordChangeUsers = User::where('debe_cambiar_password', true)
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->count();

        if ($weakPasswordCount > 0) {
            $this->addIssue("🔐 Encontrados {$weakPasswordCount} usuarios con contraseñas débiles", $usersWithWeakPasswords);
        } else {
            $this->addSuccess('✅ No se encontraron contraseñas débiles comunes');
        }

        if ($oldPasswordChangeUsers > 0) {
            $this->addWarning("⚠️  {$oldPasswordChangeUsers} usuarios deben cambiar contraseña hace más de 30 días");
        }
    }

    /**
     * Verificar datos de prueba
     */
    private function verifyTestData(): void
    {
        $this->info('📊 Verificando datos de prueba...');

        // Verificar PAA de prueba
        if (DB::getSchemaBuilder()->hasTable('plan_anual_adquisiciones')) {
            $testPAA = DB::table('plan_anual_adquisiciones')
                ->where(function($query) {
                    $query->where('codigo_necesidad', 'like', 'PAA-2026-%')
                          ->orWhere('descripcion', 'like', '%prueba%')
                          ->orWhere('descripcion', 'like', '%test%')
                          ->orWhere('descripcion', 'like', '%demo%');
                })
                ->count();

            if ($testPAA > 0) {
                $this->addIssue("📄 Encontradas {$testPAA} entradas de PAA de prueba");
            } else {
                $this->addSuccess('✅ No se encontraron PAA de prueba');
            }
        }

        // Verificar procesos de prueba
        if (DB::getSchemaBuilder()->hasTable('procesos')) {
            $testProcesses = DB::table('procesos')
                ->where(function($query) {
                    $query->where('nombre', 'like', '%prueba%')
                          ->orWhere('nombre', 'like', '%test%')
                          ->orWhere('nombre', 'like', '%demo%');
                })
                ->count();

            if ($testProcesses > 0) {
                $this->addIssue("⚙️ Encontrados {$testProcesses} procesos de prueba");
            } else {
                $this->addSuccess('✅ No se encontraron procesos de prueba');
            }
        }
    }

    /**
     * Verificar configuración de aplicación
     */
    private function verifyAppConfiguration(): void
    {
        $this->info('⚙️ Verificando configuración de aplicación...');

        // Verificar APP_DEBUG
        if (config('app.debug') === true) {
            $this->addIssue('⚠️  APP_DEBUG está habilitado (debe estar en false para producción)');
        } else {
            $this->addSuccess('✅ APP_DEBUG está correctamente deshabilitado');
        }

        // Verificar APP_ENV
        if (config('app.env') !== 'production') {
            $this->addWarning("⚠️  APP_ENV está configurado como '" . config('app.env') . "' (recomendado: 'production')");
        } else {
            $this->addSuccess('✅ APP_ENV está configurado para producción');
        }

        // Verificar APP_URL
        $appUrl = config('app.url');
        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, '.local')) {
            $this->addWarning("⚠️  APP_URL parece ser de desarrollo: {$appUrl}");
        } else {
            $this->addSuccess('✅ APP_URL configurada para producción');
        }

        // Verificar configuración de base de datos
        $dbConnection = config('database.default');
        $this->addInfo("ℹ️  Base de datos configurada: {$dbConnection}");

        // Verificar configuración de cache
        $cacheDriver = config('cache.default');
        if (in_array($cacheDriver, ['redis', 'memcached'])) {
            $this->addSuccess("✅ Cache configurado para producción: {$cacheDriver}");
        } else {
            $this->addWarning("⚠️  Cache configurado como: {$cacheDriver} (recomendado: redis/memcached para producción)");
        }
    }

    /**
     * Verificar estructura de roles
     */
    private function verifyRoleStructure(): void
    {
        $this->info('👑 Verificando estructura de roles...');

        $requiredRoles = [
            'super_admin', 'admin_sistema', 'gobernador', 'secretario', 'jefe_unidad',
            'coord_contratacion', 'prof_contratacion', 'aux_contratacion',
            'revisor_juridico', 'revisor_presupuestal', 'revisor_tecnico',
            'secop_operator', 'auditor_interno', 'consulta_ciudadana'
        ];

        $existingRoles = Role::pluck('name')->toArray();
        $missingRoles = array_diff($requiredRoles, $existingRoles);

        if (count($missingRoles) > 0) {
            $this->addIssue('👥 Roles faltantes: ' . implode(', ', $missingRoles));
        } else {
            $this->addSuccess('✅ Todos los roles requeridos están presentes (' . count($requiredRoles) . ' roles)');
        }

        // Verificar que existe al menos un super admin
        $superAdminCount = User::role('super_admin')->count();
        if ($superAdminCount === 0) {
            $this->addIssue('👑 No existe ningún usuario con rol super_admin');
        } else {
            $this->addSuccess("✅ Existe(n) {$superAdminCount} super administrador(es)");
        }
    }

    /**
     * Verificar integridad de base de datos
     */
    private function verifyDatabaseIntegrity(): void
    {
        $this->info('🏗️ Verificando integridad de base de datos...');

        try {
            // Verificar tablas principales
            $requiredTables = [
                'users', 'roles', 'permissions', 'role_has_permissions',
                'model_has_roles', 'secretarias', 'unidades'
            ];

            $existingTables = [];
            foreach ($requiredTables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $existingTables[] = $table;
                }
            }

            $missingTables = array_diff($requiredTables, $existingTables);

            if (count($missingTables) > 0) {
                $this->addIssue('🏗️ Tablas faltantes: ' . implode(', ', $missingTables));
            } else {
                $this->addSuccess('✅ Todas las tablas principales existen');
            }

            // Verificar consistencia de datos
            $orphanedRoles = DB::table('model_has_roles')
                ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
                ->whereNull('users.id')
                ->count();

            if ($orphanedRoles > 0) {
                $this->addWarning("⚠️  {$orphanedRoles} asignaciones de rol huérfanas");
            } else {
                $this->addSuccess('✅ No hay asignaciones de rol huérfanas');
            }

        } catch (\Exception $e) {
            $this->addIssue('🏗️ Error verificando base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Verificar configuraciones de seguridad
     */
    private function verifySecuritySettings(): void
    {
        $this->info('🔒 Verificando configuraciones de seguridad...');

        // Verificar APP_KEY
        if (empty(config('app.key'))) {
            $this->addIssue('🔑 APP_KEY no está configurada');
        } else {
            $this->addSuccess('✅ APP_KEY está configurada');
        }

        // Verificar configuración de sesiones
        $sessionDriver = config('session.driver');
        if (in_array($sessionDriver, ['redis', 'database'])) {
            $this->addSuccess("✅ Sesiones configuradas para producción: {$sessionDriver}");
        } else {
            $this->addWarning("⚠️  Sesiones configuradas como: {$sessionDriver}");
        }

        // Verificar HTTPS
        $httpsOnly = config('session.secure', false);
        if ($httpsOnly) {
            $this->addSuccess('✅ Sesiones configuradas para HTTPS únicamente');
        } else {
            $this->addWarning('⚠️  Sesiones no restringidas a HTTPS');
        }
    }

    /**
     * Verificar permisos de archivos
     */
    private function verifyFilePermissions(): void
    {
        $this->info('📁 Verificando permisos de archivos...');

        $criticalPaths = [
            storage_path() => 'storage',
            base_path('bootstrap/cache') => 'bootstrap/cache'
        ];

        foreach ($criticalPaths as $path => $name) {
            if (is_writable($path)) {
                $this->addSuccess("✅ {$name} tiene permisos de escritura");
            } else {
                $this->addIssue("📁 {$name} NO tiene permisos de escritura");
            }
        }

        // Verificar que .env no sea accesible públicamente
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $permissions = fileperms($envPath) & 0777;
            if ($permissions <= 0644) {
                $this->addSuccess('✅ Archivo .env tiene permisos seguros');
            } else {
                $this->addWarning('⚠️  Archivo .env puede tener permisos muy permisivos');
            }
        }
    }

    /**
     * Agregar resultado exitoso
     */
    private function addSuccess(string $message): void
    {
        $this->verificationResults[] = ['type' => 'success', 'message' => $message];
    }

    /**
     * Agregar advertencia
     */
    private function addWarning(string $message, array $details = []): void
    {
        $this->verificationResults[] = ['type' => 'warning', 'message' => $message, 'details' => $details];
        $this->warningsFound++;
    }

    /**
     * Agregar problema crítico
     */
    private function addIssue(string $message, array $details = []): void
    {
        $this->verificationResults[] = ['type' => 'error', 'message' => $message, 'details' => $details];
        $this->issuesFound++;
    }

    /**
     * Agregar información
     */
    private function addInfo(string $message): void
    {
        $this->verificationResults[] = ['type' => 'info', 'message' => $message];
    }

    /**
     * Mostrar resultados de la verificación
     */
    private function showResults(): void
    {
        $this->newLine();

        if ($this->issuesFound === 0 && $this->warningsFound === 0) {
            $this->info('🎉 ¡SISTEMA LISTO PARA PRODUCCIÓN!');
            $this->newLine();
            $this->info('📋 Checklist de producción completado exitosamente:');
        } else {
            $this->warn('⚠️  VERIFICACIÓN DE PRODUCCIÓN COMPLETADA CON OBSERVACIONES');
            $this->newLine();
            $this->info('📋 Resultados de la verificación:');
        }

        $successCount = 0;
        foreach ($this->verificationResults as $result) {
            $this->line("   {$result['message']}");

            if (isset($result['details']) && !empty($result['details'])) {
                foreach ($result['details'] as $detail) {
                    if (is_array($detail)) {
                        $this->line("     - {$detail['email']} ({$detail['name']})");
                    } else {
                        $this->line("     - {$detail}");
                    }
                }
            }

            if ($result['type'] === 'success') {
                $successCount++;
            }
        }

        $this->newLine();
        $this->info("📊 Resumen:");
        $this->info("   ✅ Verificaciones exitosas: {$successCount}");

        if ($this->warningsFound > 0) {
            $this->warn("   ⚠️  Advertencias: {$this->warningsFound}");
        }

        if ($this->issuesFound > 0) {
            $this->error("   ❌ Problemas críticos: {$this->issuesFound}");
            $this->newLine();
            $this->error('🔧 Acciones requeridas:');
            $this->error('   1. php artisan production:clean-test-data --force');
            $this->error('   2. Configurar variables de entorno para producción');
            $this->error('   3. php artisan db:seed --class=ProductionSeederStructure');
        } else {
            $this->newLine();
            $this->info('🚀 Sistema preparado para despliegue en producción!');
        }
    }
}