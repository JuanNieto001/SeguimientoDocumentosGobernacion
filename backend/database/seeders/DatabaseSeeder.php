<?php
/**
 * Archivo: backend/database/seeders/DatabaseSeeder.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

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
        MotorFlujosBootstrapSeeder::class,
        RolesAndPermissionsSeeder::class,
        DashboardRolesSeeder::class,
        AdminUserSeeder::class,
        AreaUsersSeeder::class,
        UsuariosPruebaSeeder::class,
        WorkflowSeeder::class,
        PAASeeder::class,
        TiposArchivoSeeder::class,
        EstivenGuidesSeeder::class,
    ]);
}

}

