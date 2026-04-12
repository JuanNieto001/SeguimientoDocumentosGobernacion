<?php
/**
 * Archivo: backend/database/migrations/2026_02_17_210000_add_etapa_id_to_proceso_auditoria_table.php
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
        Schema::table('proceso_auditoria', function (Blueprint $table) {
            // Nullable to avoid breaking existing registros; index for faster consultas
            $table->unsignedBigInteger('etapa_id')->nullable();
            $table->index('etapa_id');
        });
    }

    public function down(): void
    {
        Schema::table('proceso_auditoria', function (Blueprint $table) {
            $table->dropIndex(['etapa_id']);
            $table->dropColumn('etapa_id');
        });
    }
};


