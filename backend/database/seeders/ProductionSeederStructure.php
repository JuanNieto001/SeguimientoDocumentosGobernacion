<?php
/**
 * Archivo: backend/database/seeders/ProductionSeederStructure.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * ProductionSeederStructure - Seeder Principal para Ambiente de Producción
 * ====================================================================
 *
 * FASE 3 - ORGANIZACIÓN DE DATOS
 *
 * Seeder maestro que orquesta la carga completa de datos para ambiente de producción.
 * Ejecuta todos los seeders necesarios en el orden correcto para tener un sistema
 * limpio y listo para uso en producción.
 *
 * SEEDERS INCLUIDOS (En orden de ejecución):
 * 1. ✅ Estructura organizacional oficial
 * 2. ✅ Sistema de roles y permisos optimizado
 * 3. ✅ Usuario administrador inicial seguro
 * 4. ✅ Workflows optimizados (CD-PN + flujos indirectos)
 * 5. ✅ Tipos documentales oficiales
 * 6. ✅ Templates de dashboard por rol
 *
 * SEEDERS EXCLUIDOS (Datos de prueba):
 * ❌ UsuariosPruebaSeeder
 * ❌ AdminUserSeeder
 * ❌ PAASeeder
 * ❌ AreaUsersSeeder
 * ❌ MotorFlujosSeeder (configuraciones de prueba)
 */
class ProductionSeederStructure extends Seeder
{
    /**
     * Orden de ejecución de seeders para producción
     */
    private array $productionSeeders = [
        // 1. ESTRUCTURA ORGANIZACIONAL (Mantener datos oficiales)
        'SecretariasUnidadesSeeder' => [
            'descripcion' => 'Estructura organizacional oficial de la Gobernación de Caldas',
            'categoria' => 'estructura',
            'critico' => true
        ],

        // 2. SISTEMA DE PERMISOS Y ROLES (Optimizado para producción)
        'RolesProductionSeeder' => [
            'descripcion' => 'Roles y permisos optimizados para contratación pública',
            'categoria' => 'seguridad',
            'critico' => true
        ],

        // 3. USUARIO ADMINISTRADOR INICIAL (Seguro)
        'ProductionAdminSeeder' => [
            'descripcion' => 'Usuario administrador inicial con credenciales seguras',
            'categoria' => 'usuarios',
            'critico' => true
        ],

        // 4. WORKFLOWS OPTIMIZADOS (CD-PN mejorado + flujos indirectos)
        'WorkflowCDPNOptimizedSeeder' => [
            'descripcion' => 'Flujo CD-PN optimizado con 10 etapas detalladas',
            'categoria' => 'workflows',
            'critico' => true
        ],

        // 5. TIPOS DOCUMENTALES OFICIALES (Mantener)
        'TiposArchivoSeeder' => [
            'descripcion' => 'Tipos documentales oficiales para contratación',
            'categoria' => 'configuracion',
            'critico' => false
        ],

        // 6. TEMPLATES DE DASHBOARD (Por rol)
        'DashboardTemplatesProductionSeeder' => [
            'descripcion' => 'Templates de dashboard específicos por rol',
            'categoria' => 'dashboard',
            'critico' => false
        ]
    ];

    /**
     * Lista de verificaciones pre-ejecución
     */
    private array $verificationChecks = [
        'database_connection',
        'production_environment',
        'test_data_cleaned',
        'required_tables_exist'
    ];

    /**
     * Estadísticas de ejecución
     */
    private array $executionStats = [];

    /**
     * Ejecutar estructura completa de seeders de producción
     */
    public function run(): void
    {
        $startTime = Carbon::now();

        $this->displayWelcome();

        // Verificaciones pre-ejecución
        if (!$this->runPreExecutionChecks()) {
            $this->command->error('❌ Verificaciones pre-ejecución fallaron. Deteniendo ejecución.');
            return;
        }

        // Confirmar ejecución en producción si es necesario
        if (!$this->confirmProductionExecution()) {
            $this->command->info('❌ Ejecución cancelada por el usuario.');
            return;
        }

        // Ejecutar seeders en orden
        $this->executeSeedersInOrder();

        // Verificaciones post-ejecución
        $this->runPostExecutionValidations();

        // Mostrar resultados finales
        $this->displayExecutionResults($startTime);

        // Log del sistema
        $this->logExecutionResults($startTime);
    }

    /**
     * Mostrar mensaje de bienvenida
     */
    private function displayWelcome(): void
    {
        $this->command->info('🏗️  PRODUCTIONSEEDERSTRUCTURE - CARGA DE DATOS PARA PRODUCCIÓN');
        $this->command->info('Sistema de Seguimiento Contractual - Gobernación de Caldas');
        $this->command->info('FASE 3 - ORGANIZACIÓN DE DATOS');
        $this->command->newLine();

        $this->command->info('📋 Seeders a ejecutar:');
        foreach ($this->productionSeeders as $seederClass => $config) {
            $icon = $config['critico'] ? '🔴' : '🟡';
            $this->command->info("   {$icon} {$seederClass} - {$config['descripcion']}");
        }
        $this->command->newLine();
    }

    /**
     * Ejecutar verificaciones pre-ejecución
     */
    private function runPreExecutionChecks(): bool
    {
        $this->command->info('🔍 Ejecutando verificaciones pre-ejecución...');

        $allChecksPassed = true;

        foreach ($this->verificationChecks as $check) {
            $this->command->info("   Verificando: {$check}...");

            $passed = match($check) {
                'database_connection' => $this->checkDatabaseConnection(),
                'production_environment' => $this->checkProductionEnvironment(),
                'test_data_cleaned' => $this->checkTestDataCleaned(),
                'required_tables_exist' => $this->checkRequiredTablesExist(),
                default => false
            };

            if ($passed) {
                $this->command->info("     ✅ {$check} - OK");
            } else {
                $this->command->error("     ❌ {$check} - FALLÓ");
                $allChecksPassed = false;
            }
        }

        return $allChecksPassed;
    }

    /**
     * Verificar conexión a base de datos
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            $this->command->error("Error de conexión: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Verificar entorno de producción
     */
    private function checkProductionEnvironment(): bool
    {
        $env = app()->environment();

        if ($env === 'production') {
            return true;
        }

        // Permitir también en local/development para testing
        if (in_array($env, ['local', 'development'])) {
            $this->command->warn("     ⚠️  Entorno detectado: {$env} (no es producción)");
            return true;
        }

        return false;
    }

    /**
     * Verificar que datos de prueba están limpios
     */
    private function checkTestDataCleaned(): bool
    {
        // Verificar usuarios de prueba
        $testUsers = \App\Models\User::whereIn('email', [
            'admin@demo.com', 'jesin@demo.com', 'admin@caldas.gov.co'
        ])->count();

        if ($testUsers > 0) {
            $this->command->warn("     ⚠️  Se encontraron {$testUsers} usuarios de prueba");
            $this->command->warn("     Ejecutar: php artisan production:clean-test-data --force");
            return false;
        }

        return true;
    }

    /**
     * Verificar que existen tablas requeridas
     */
    private function checkRequiredTablesExist(): bool
    {
        $requiredTables = [
            'users', 'roles', 'permissions', 'secretarias', 'unidades', 'workflows'
        ];

        foreach ($requiredTables as $table) {
            if (!\Schema::hasTable($table)) {
                $this->command->error("     ❌ Tabla requerida no existe: {$table}");
                return false;
            }
        }

        return true;
    }

    /**
     * Confirmar ejecución en producción
     */
    private function confirmProductionExecution(): bool
    {
        if (app()->environment('production')) {
            $this->command->warn('⚠️  ADVERTENCIA: Está a punto de ejecutar seeders en PRODUCCIÓN.');
            $this->command->warn('   Esto puede sobrescribir datos existentes.');
            $this->command->newLine();

            return $this->command->confirm('¿Está seguro de que desea continuar?');
        }

        return true; // En desarrollo, continuar automáticamente
    }

    /**
     * Ejecutar seeders en orden específico
     */
    private function executeSeedersInOrder(): void
    {
        $this->command->info('🚀 Iniciando carga de seeders de producción...');
        $this->command->newLine();

        foreach ($this->productionSeeders as $seederClass => $config) {
            $this->executeSingleSeeder($seederClass, $config);
        }
    }

    /**
     * Ejecutar un seeder individual
     */
    private function executeSingleSeeder(string $seederClass, array $config): void
    {
        $startTime = Carbon::now();
        $this->command->info("📦 Ejecutando: {$seederClass}");
        $this->command->info("   📝 {$config['descripcion']}");

        try {
            // Verificar que la clase del seeder existe
            if (!class_exists("Database\\Seeders\\{$seederClass}")) {
                throw new \Exception("Seeder class not found: {$seederClass}");
            }

            // Ejecutar el seeder
            $this->call("Database\\Seeders\\{$seederClass}");

            $endTime = Carbon::now();
            $duration = $endTime->diffInSeconds($startTime);

            $this->command->info("   ✅ Completado en {$duration} segundos");

            // Guardar estadísticas
            $this->executionStats[$seederClass] = [
                'status' => 'success',
                'duration' => $duration,
                'categoria' => $config['categoria'],
                'critico' => $config['critico']
            ];

        } catch (\Exception $e) {
            $endTime = Carbon::now();
            $duration = $endTime->diffInSeconds($startTime);

            $this->command->error("   ❌ Error en {$duration} segundos: {$e->getMessage()}");

            // Guardar estadísticas del error
            $this->executionStats[$seederClass] = [
                'status' => 'error',
                'duration' => $duration,
                'error' => $e->getMessage(),
                'categoria' => $config['categoria'],
                'critico' => $config['critico']
            ];

            // Si es crítico, detener ejecución
            if ($config['critico']) {
                $this->command->error('❌ Seeder crítico falló. Deteniendo ejecución.');
                throw $e;
            }
        }

        $this->command->newLine();
    }

    /**
     * Ejecutar validaciones post-ejecución
     */
    private function runPostExecutionValidations(): void
    {
        $this->command->info('🔍 Ejecutando validaciones post-ejecución...');

        // Verificar que existe al menos un usuario administrador
        $adminCount = \App\Models\User::role('super_admin')->count();
        if ($adminCount > 0) {
            $this->command->info("   ✅ Usuarios administrador: {$adminCount}");
        } else {
            $this->command->error('   ❌ No se encontraron usuarios administrador');
        }

        // Verificar roles críticos
        $requiredRoles = ['super_admin', 'gobernador', 'secretario', 'jefe_unidad'];
        $existingRoles = \Spatie\Permission\Models\Role::whereIn('name', $requiredRoles)->count();
        $totalRequired = count($requiredRoles);
        if ($existingRoles === $totalRequired) {
            $this->command->info("   ✅ Roles críticos: {$existingRoles}/{$totalRequired}");
        } else {
            $this->command->warn("   ⚠️  Roles críticos: {$existingRoles}/{$totalRequired}");
        }

        // Verificar workflows
        $workflowCount = \DB::table('workflows')->where('activo', true)->count();
        $this->command->info("   ✅ Workflows activos: {$workflowCount}");

        // Verificar templates de dashboard
        if (\Schema::hasTable('dashboard_plantillas')) {
            $templateCount = \DB::table('dashboard_plantillas')->where('activo', true)->count();
            $this->command->info("   ✅ Templates dashboard: {$templateCount}");
        }
    }

    /**
     * Mostrar resultados de ejecución
     */
    private function displayExecutionResults(Carbon $startTime): void
    {
        $endTime = Carbon::now();
        $totalDuration = $endTime->diffInSeconds($startTime);

        $this->command->newLine();
        $this->command->info('🎉 ¡CARGA DE DATOS DE PRODUCCIÓN COMPLETADA!');
        $this->command->info("⏱️  Tiempo total: {$totalDuration} segundos");
        $this->command->newLine();

        // Estadísticas por categoría
        $successCount = collect($this->executionStats)->where('status', 'success')->count();
        $errorCount = collect($this->executionStats)->where('status', 'error')->count();

        $this->command->info('📊 Resumen de ejecución:');
        $this->command->info("   ✅ Seeders exitosos: {$successCount}");

        if ($errorCount > 0) {
            $this->command->error("   ❌ Seeders con errores: {$errorCount}");
        }

        // Mostrar próximos pasos
        $this->command->newLine();
        $this->command->info('📋 Próximos pasos recomendados:');
        $this->command->info('   1. php artisan production:verify-readiness');
        $this->command->info('   2. Crear usuarios reales del sistema');
        $this->command->info('   3. Configurar variables de entorno para producción');
        $this->command->info('   4. Verificar funcionamiento de dashboards');
        $this->command->info('   5. Probar flujos de contratación');
    }

    /**
     * Log de resultados para auditoria
     */
    private function logExecutionResults(Carbon $startTime): void
    {
        $endTime = Carbon::now();

        Log::info('ProductionSeederStructure ejecutado', [
            'inicio' => $startTime->toDateTimeString(),
            'fin' => $endTime->toDateTimeString(),
            'duracion_segundos' => $endTime->diffInSeconds($startTime),
            'estadisticas' => $this->executionStats,
            'entorno' => app()->environment(),
            'usuario' => auth()->user()->email ?? 'cli',
            'seeders_exitosos' => collect($this->executionStats)->where('status', 'success')->count(),
            'seeders_errores' => collect($this->executionStats)->where('status', 'error')->count()
        ]);
    }
}
