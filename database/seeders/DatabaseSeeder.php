<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // En producción/staging evita seeders que creen usuarios "demo"
        // y evita cualquier seeder que borre datos operativos.
        if (app()->environment(['production', 'staging'])) {
            $this->call([
                RolesAndPermissionsSeeder::class,
                WorkflowSeeder::class,
            ]);

            return;
        }

        // Local/Testing: se puede poblar todo para pruebas
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            AreaUsersSeeder::class,
            WorkflowSeeder::class,
        ]);
    }
}
