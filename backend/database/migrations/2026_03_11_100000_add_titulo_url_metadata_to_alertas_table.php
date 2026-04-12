<?php
/**
 * Archivo: backend/database/migrations/2026_03_11_100000_add_titulo_url_metadata_to_alertas_table.php
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
        Schema::table('alertas', function (Blueprint $table) {
            $table->string('titulo')->nullable()->after('tipo');
            $table->string('accion_url')->nullable()->after('area_responsable');
            $table->json('metadata')->nullable()->after('accion_url');
            $table->foreignId('proceso_cd_id')->nullable()->after('proceso_id')
                ->constrained('proceso_contratacion_directa')->nullOnDelete();

            $table->index(['area_responsable', 'leida', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropForeign(['proceso_cd_id']);
            $table->dropIndex(['area_responsable', 'leida', 'created_at']);
            $table->dropColumn(['titulo', 'accion_url', 'metadata', 'proceso_cd_id']);
        });
    }
};

