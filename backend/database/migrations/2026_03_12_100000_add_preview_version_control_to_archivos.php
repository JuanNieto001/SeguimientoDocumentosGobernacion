<?php
/**
 * Archivo: backend/database/migrations/2026_03_12_100000_add_preview_version_control_to_archivos.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proceso_etapa_archivos', function (Blueprint $table) {
            $table->text('motivo_reemplazo')->nullable()->after('observaciones');
            $table->boolean('es_reemplazo_admin')->default(false)->after('motivo_reemplazo');
        });
    }

    public function down(): void
    {
        Schema::table('proceso_etapa_archivos', function (Blueprint $table) {
            $table->dropColumn(['motivo_reemplazo', 'es_reemplazo_admin']);
        });
    }
};

