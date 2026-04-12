<?php
/**
 * Archivo: backend/database/migrations/2026_03_24_162100_create_dashboard_secretaria_asignaciones_table.php
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
        if (Schema::hasTable('dashboard_secretaria_asignaciones')) {
            return;
        }

        Schema::create('dashboard_secretaria_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secretaria_id')->constrained('secretarias')->cascadeOnDelete();
            $table->foreignId('dashboard_plantilla_id')->constrained('dashboard_plantillas')->cascadeOnDelete();
            $table->unsignedSmallInteger('prioridad')->default(50);
            $table->json('config_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('secretaria_id');
            $table->index(['activo', 'prioridad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_secretaria_asignaciones');
    }
};

