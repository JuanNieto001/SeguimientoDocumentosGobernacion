<?php
/**
 * Archivo: backend/database/migrations/2026_03_24_160000_create_dashboard_usuario_asignaciones_table.php
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
        Schema::create('dashboard_usuario_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dashboard_plantilla_id')->constrained('dashboard_plantillas')->cascadeOnDelete();
            $table->unsignedSmallInteger('prioridad')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['activo', 'prioridad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_usuario_asignaciones');
    }
};

