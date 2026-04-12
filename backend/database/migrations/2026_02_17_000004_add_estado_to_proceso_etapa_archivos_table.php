<?php
/**
 * Archivo: backend/database/migrations/2026_02_17_000004_add_estado_to_proceso_etapa_archivos_table.php
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
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('proceso_etapa_archivos', function (Blueprint $table) {
            $table->dropForeign(['revisado_por']);
            $table->dropColumn(['estado', 'observaciones', 'revisado_por', 'revisado_at']);
        });
    }
};


