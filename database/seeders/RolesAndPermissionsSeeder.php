<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia caché de permisos para evitar errores
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permisos administrativos
        |--------------------------------------------------------------------------
        */
        $permissions = [
            'manage users',
            'manage roles',
            'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles del sistema
        |--------------------------------------------------------------------------
        */
        $roles = [
            'admin',
            'unidad_solicitante',
            'planeacion',
            'hacienda',
            'juridica',
            'secop',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        /*
        |--------------------------------------------------------------------------
        | Asignar permisos administrativos al admin
        |--------------------------------------------------------------------------
        */
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }
}
