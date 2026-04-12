<?php
/**
 * Archivo: backend/database/seeders/DashboardAdminOnlySeeder.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Configuración de permisos SOLO para Admin
 * El Admin usa Dashboard Builder y asigna dashboards a otros usuarios
 */
class DashboardAdminOnlySeeder extends Seeder
{
    public function run()
    {
        // Limpiar permisos anteriores
        $builderPermission = Permission::where('name', 'dashboard.builder.access')->first();
        if ($builderPermission) {
            // Remover de todos los roles
            $builderPermission->roles()->detach();
        }

        // Crear nuevos permisos
        $permissions = [
            'dashboard.builder.access' => 'Acceso al Dashboard Builder (solo Admin)',
            'dashboard.assign' => 'Asignar dashboards a usuarios',
            'dashboard.view.assigned' => 'Ver dashboards asignados por Admin'
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // SOLO Admin puede usar Dashboard Builder
        $adminRole = Role::where('name', 'admin')->first();
        $adminGeneralRole = Role::where('name', 'admin_general')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo([
                'dashboard.builder.access',
                'dashboard.assign',
                'dashboard.view.assigned'
            ]);
        }

        if ($adminGeneralRole) {
            $adminGeneralRole->givePermissionTo([
                'dashboard.builder.access', 
                'dashboard.assign',
                'dashboard.view.assigned'
            ]);
        }

        // Resto de roles SOLO pueden ver dashboards asignados
        $viewOnlyRoles = [
            'admin_secretaria',
            'planeacion', 
            'gobernador',
            'secretario',
            'jefe_unidad',
            'secop',
            'juridica',
            'hacienda'
        ];

        foreach ($viewOnlyRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo('dashboard.view.assigned');
            }
        }

        echo "✅ Dashboard Builder - SOLO Admin\n";
        echo "   → admin: Crear/editar/asignar dashboards\n";
        echo "   → admin_general: Crear/editar/asignar dashboards\n";
        echo "   → Otros roles: Solo ver dashboards asignados\n";
    }
}
