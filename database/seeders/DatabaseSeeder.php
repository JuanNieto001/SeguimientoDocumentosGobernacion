<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        SecretariasUnidadesSeeder::class,
        RolesAndPermissionsSeeder::class,
        AdminUserSeeder::class,
        AreaUsersSeeder::class,
        UsuariosPruebaSeeder::class,
        WorkflowSeeder::class,
        PAASeeder::class,
        TiposArchivoSeeder::class,
    ]);
}

}
