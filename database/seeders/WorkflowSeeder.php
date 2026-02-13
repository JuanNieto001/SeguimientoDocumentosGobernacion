<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        // Para poder limpiar sin romper llaves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpieza en orden (si ya existiera data)
        DB::table('proceso_etapa_checks')->delete();
        DB::table('proceso_etapas')->delete();
        DB::table('etapa_items')->delete();
        DB::table('etapas')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $etapas = [

            [
                'orden' => 1,
                'nombre' => 'Unidad Solicitante',
                'area_role' => 'unidad_solicitante',
                'items' => [
                    'Elaboré Estudios Previos',
                    'Elaboré Estudio de Mercado',
                    'Elaboré Análisis del Sector',
                    'Adjunté Cotizaciones',
                    'Proyecté Invitación Pública (si aplica)',
                    'Proyecté Prepliegos (si aplica)',
                    'Elaboré Solicitud de contratación',
                    'Designé supervisor',
                ]
            ],

            [
                'orden' => 2,
                'nombre' => 'Secretaría de Planeación',
                'area_role' => 'planeacion',
                'items' => [
                    'Solicité CDP',
                    'Solicité PAA',
                    'Solicité Certificado de Compatibilidad',
                    'Solicité Viabilidad Económica',
                    'Solicité Indicadores Financieros (si aplica)',
                    'Revisé documentación completa',
                ]
            ],

            [
                'orden' => 3,
                'nombre' => 'Secretaría de Hacienda',
                'area_role' => 'hacienda',
                'items' => [
                    'Emití CDP',
                    'Emití Certificado de Compatibilidad',
                    'Emití Viabilidad Económica',
                    'Emití Indicadores Financieros (si aplica)',
                    'Emití Registro Presupuestal (RP)',
                ]
            ],

            [
                'orden' => 4,
                'nombre' => 'Secretaría Jurídica',
                'area_role' => 'juridica',
                'items' => [
                    'Verifiqué Cotizaciones',
                    'Verifiqué Análisis del Sector',
                    'Verifiqué CDP',
                    'Verifiqué PAA',
                    'Verifiqué Certificado de Compatibilidad',
                    'Revisé Estudios Previos',
                    'Emití documento Ajustado a Derecho',
                    'Revisé pólizas',
                    'Radicación física completada',
                ]
            ],

            [
                'orden' => 5,
                'nombre' => 'SECOP',
                'area_role' => 'secop',
                'items' => [
                    'Estructuré proceso en SECOP',
                    'Publiqué documentos en SECOP',
                    'Registré contrato electrónico',
                    'Confirmé adjudicación',
                ]
            ],
        ];

        foreach ($etapas as $etapaData) {
            $etapaId = DB::table('etapas')->insertGetId([
                'orden' => $etapaData['orden'],
                'nombre' => $etapaData['nombre'],
                'area_role' => $etapaData['area_role'],
                'next_etapa_id' => null, // lo llenamos luego si quieres
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ordenItem = 1;
            foreach ($etapaData['items'] as $label) {
                DB::table('etapa_items')->insert([
                    'etapa_id' => $etapaId,
                    'orden' => $ordenItem++,
                    'label' => $label,
                    'requerido' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

