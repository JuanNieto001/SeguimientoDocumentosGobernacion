<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardBuilderPermissionSeeder extends Seeder
{
    /**
     * Crear el permiso dashboard.builder.access y asignarlo a roles autorizados.
     */
    public function run(): void
    {
        // Crear el permiso
        $permission = Permission::firstOrCreate([
            'name'       => 'dashboard.builder.access',
            'guard_name' => 'web',
        ]);

        $this->command->info('✅ Permiso dashboard.builder.access creado.');

        // Asignar a roles que deben tener acceso al builder
        $rolesConAcceso = [
            'admin',
            'admin_general',
            'admin_secretaria',
            'planeacion',
            'gobernador',
            'secretario',
        ];

        foreach ($rolesConAcceso as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permission);
                $this->command->info("  → Asignado a: {$roleName}");
            }
        }

        $this->command->info('✅ Permiso asignado a ' . count($rolesConAcceso) . ' roles.');
    }
}
