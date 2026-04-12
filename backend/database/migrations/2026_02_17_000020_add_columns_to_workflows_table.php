<?php
/**
 * Archivo: backend/database/migrations/2026_02_17_000020_add_columns_to_workflows_table.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            // Columnas requeridas por el WorkflowSeeder
            $table->boolean('requiere_viabilidad_economica_inicial')->default(true);
            $table->boolean('requiere_estudios_previos_completos')->default(true);
            $table->text('observaciones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn([
                'requiere_viabilidad_economica_inicial',
                'requiere_estudios_previos_completos',
                'observaciones',
            ]);
        });
    }
};


