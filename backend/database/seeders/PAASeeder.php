<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PAASeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateIfExists('plan_anual_adquisiciones');

        DB::table('plan_anual_adquisiciones')->insert([
            [
                'anio' => 2026,
                'codigo_necesidad' => 'PAA-2026-001',
                'descripcion' => 'Contratación de profesional en sistemas para soporte técnico',
                'valor_estimado' => 50000000,
                'modalidad_contratacion' => 'CD_PN',
                'trimestre_estimado' => 1,
                'dependencia_solicitante' => 'Secretaría de TIC',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'anio' => 2026,
                'codigo_necesidad' => 'PAA-2026-002',
                'descripcion' => 'Adquisición de papelería y útiles de oficina',
                'valor_estimado' => 8000000,
                'modalidad_contratacion' => 'MC',
                'trimestre_estimado' => 1,
                'dependencia_solicitante' => 'Secretaría General',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'anio' => 2026,
                'codigo_necesidad' => 'PAA-2026-003',
                'descripcion' => 'Mantenimiento de vías rurales',
                'valor_estimado' => 250000000,
                'modalidad_contratacion' => 'SA',
                'trimestre_estimado' => 2,
                'dependencia_solicitante' => 'Secretaría de Infraestructura',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'anio' => 2026,
                'codigo_necesidad' => 'PAA-2026-004',
                'descripcion' => 'Construcción de puente vehicular',
                'valor_estimado' => 1500000000,
                'modalidad_contratacion' => 'LP',
                'trimestre_estimado' => 2,
                'dependencia_solicitante' => 'Secretaría de Obras Públicas',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'anio' => 2026,
                'codigo_necesidad' => 'PAA-2026-005',
                'descripcion' => 'Consultoría para estudios de factibilidad proyecto vial',
                'valor_estimado' => 180000000,
                'modalidad_contratacion' => 'CM',
                'trimestre_estimado' => 1,
                'dependencia_solicitante' => 'Secretaría de Planeación',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ PAA de ejemplo creado correctamente.');
    }

    private function truncateIfExists(string $table): void
    {
        try {
            DB::statement("DELETE FROM {$table}");
            if (DB::getDriverName() === 'sqlite') {
                DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
            }
        } catch (\Exception $e) {
            // Tabla no existe, ignorar
        }
    }
}
