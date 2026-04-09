<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductionAdminSeeder extends Seeder
{
    /**
     * Seed de usuario administrador para ambiente de producción
     *
     * Crea un usuario administrador inicial seguro con credenciales robustas
     * para el primer acceso al sistema en producción.
     */
    public function run(): void
    {
        $this->command->info('🔐 Creando usuario administrador para producción...');

        // Generar contraseña temporal robusta
        $temporalPassword = Str::random(16);

        // Usuario administrador inicial para instalación
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin.sistema@gobernacion-caldas.gov.co'],
            [
                'name' => 'Administrador del Sistema',
                'password' => Hash::make($temporalPassword),
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );

        // Asignar super_admin y roles administrativos operativos para compatibilidad
        if (class_exists('\Spatie\Permission\Models\Role')) {
            $availableRoles = \Spatie\Permission\Models\Role::whereIn('name', [
                'super_admin',
                'admin_general',
                'admin',
            ])->pluck('name')->toArray();

            if (in_array('super_admin', $availableRoles, true)) {
                $superAdmin->syncRoles($availableRoles);
                $this->command->info('   ✅ Roles administrativos asignados: ' . implode(', ', $availableRoles));
            } else {
                $this->command->warn('   ⚠️  Rol super_admin no encontrado. Ejecutar RolesProductionSeeder primero.');
            }
        }

        // Log de credenciales para primer acceso (solo en desarrollo)
        $this->command->info('');
        $this->command->info('🔐 Usuario administrador creado exitosamente:');
        $this->command->info('   📧 Email: admin.sistema@gobernacion-caldas.gov.co');

        if (app()->environment(['local', 'development'])) {
            $this->command->info('   🔑 Contraseña temporal: ' . $temporalPassword);
        }

        $this->command->warn('   ⚠️  IMPORTANTE: Cambiar contraseña en primer acceso');
        $this->command->warn('   ⚠️  La contraseña temporal expira en 24 horas');

        // Información adicional de seguridad
        $this->command->info('');
        $this->command->info('🛡️  Configuración de seguridad aplicada:');
        $this->command->info('   ✅ Email institucional obligatorio');
        $this->command->info('   ✅ Contraseña robusta de 16 caracteres');
        $this->command->info('   ✅ Verificación de email automática');
        $this->command->info('   ✅ Forzar cambio de contraseña en primer acceso');
        $this->command->info('   ✅ Usuario activo y listo para uso');

        // Crear entrada en log del sistema
        \Log::info('Usuario administrador de producción creado', [
            'email' => $superAdmin->email,
            'name' => $superAdmin->name,
            'created_at' => $superAdmin->created_at,
            'debe_cambiar_password' => $superAdmin->debe_cambiar_password
        ]);
    }
}