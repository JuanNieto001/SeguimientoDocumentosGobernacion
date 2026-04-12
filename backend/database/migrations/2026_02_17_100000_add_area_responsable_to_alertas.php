<?php
/**
 * Archivo: backend/database/migrations/2026_02_17_100000_add_area_responsable_to_alertas.php
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
        // Agregar campo area_responsable a alertas si no existe
        if (!Schema::hasColumn('alertas', 'area_responsable')) {
            Schema::table('alertas', function (Blueprint $table) {
                $table->string('area_responsable')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('alertas', 'area_responsable')) {
            Schema::table('alertas', function (Blueprint $table) {
                $table->dropColumn('area_responsable');
            });
        }
    }
};


