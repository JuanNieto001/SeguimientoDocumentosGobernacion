<?php
/**
 * Archivo: backend/database/migrations/2026_02_24_135821_add_recibido_fisico_to_proceso_etapa_checks_table.php
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
        Schema::table('proceso_etapa_checks', function (Blueprint $table) {
            $table->boolean('recibido_fisico')->default(false)->after('checked');
            $table->timestamp('recibido_fisico_at')->nullable()->after('recibido_fisico');
            $table->unsignedBigInteger('recibido_fisico_por')->nullable()->after('recibido_fisico_at');
            $table->string('archivo_path')->nullable()->after('recibido_fisico_por');
            $table->string('archivo_nombre')->nullable()->after('archivo_path');
            $table->timestamp('archivo_subido_at')->nullable()->after('archivo_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proceso_etapa_checks', function (Blueprint $table) {
            $table->dropColumn(['recibido_fisico', 'recibido_fisico_at', 'recibido_fisico_por', 'archivo_path', 'archivo_nombre', 'archivo_subido_at']);
        });
    }
};

