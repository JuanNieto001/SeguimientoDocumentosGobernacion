<?php
/**
 * Archivo: backend/database/seeders/MotorFlujosBootstrapSeeder.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotorFlujosBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $tieneCdPn = DB::table('flujos')->where('codigo', 'CD_PN')->exists();

        if (!$tieneCdPn) {
            $this->command->warn('No existe CD_PN en motor de flujos. Se reinicia a CD_PN por defecto.');
            $this->call(MotorFlujosSeeder::class);
        }

        DB::table('flujos')->where('codigo', 'CD_PN')->update(['activo' => true]);
    }
}

