<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Unificar flujos: eliminar el flujo duplicado de Gobierno y renombrar
 * el flujo de Planeación a un nombre genérico. Solo debe haber un flujo CD-PN.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Encontrar el flujo de Gobierno para eliminar sus datos relacionados
        $flujoGobierno = DB::table('flujos')->where('codigo', 'CD_PN_GOBIERNO')->first();

        if ($flujoGobierno) {
            $versiones = DB::table('flujo_versiones')->where('flujo_id', $flujoGobierno->id)->pluck('id');

            if ($versiones->isNotEmpty()) {
                $pasos = DB::table('flujo_pasos')->whereIn('flujo_version_id', $versiones)->pluck('id');

                if ($pasos->isNotEmpty()) {
                    DB::table('flujo_paso_documentos')->whereIn('flujo_paso_id', $pasos)->delete();
                    DB::table('flujo_paso_condiciones')->whereIn('flujo_paso_id', $pasos)->delete();
                    DB::table('flujo_paso_responsables')->whereIn('flujo_paso_id', $pasos)->delete();
                    DB::table('flujo_pasos')->whereIn('id', $pasos)->delete();
                }

                DB::table('flujo_versiones')->whereIn('id', $versiones)->delete();
            }

            DB::table('flujos')->where('id', $flujoGobierno->id)->delete();
        }

        // 2. Renombrar el flujo de Planeación a nombre genérico
        DB::table('flujos')->where('codigo', 'CD_PN_PLANEACION')->update([
            'codigo'      => 'CD_PN',
            'nombre'      => 'Contratación Directa - Persona Natural',
            'descripcion' => 'Flujo oficial de Contratación Directa Persona Natural de la Gobernación de Caldas.',
        ]);
    }

    public function down(): void
    {
        // Revertir nombre
        DB::table('flujos')->where('codigo', 'CD_PN')->update([
            'codigo'      => 'CD_PN_PLANEACION',
            'nombre'      => 'Contratación Directa PN - Sec. Planeación',
            'descripcion' => 'Flujo CD-PN con 10 etapas oficial de la Gobernación de Caldas para la Secretaría de Planeación.',
        ]);
    }
};
