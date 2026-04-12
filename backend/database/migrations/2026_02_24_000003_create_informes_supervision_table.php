<?php
/**
 * Archivo: backend/database/migrations/2026_02_24_000003_create_informes_supervision_table.php
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
        Schema::create('informes_supervision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('numero_informe'); // 1, 2, 3...
            $table->string('periodo_inicio'); // Ej: "Enero 2026"
            $table->string('periodo_fin');    // Ej: "Enero 2026"
            $table->date('fecha_informe');
            $table->enum('estado_avance', [
                'en_ejecucion',
                'con_retraso',
                'completado',
                'suspendido',
            ])->default('en_ejecucion');
            $table->tinyInteger('porcentaje_avance')->default(0); // 0-100
            $table->text('descripcion_actividades');
            $table->text('observaciones')->nullable();
            $table->string('archivo_soporte')->nullable(); // Ruta del archivo PDF
            $table->enum('estado_informe', [
                'borrador',
                'enviado',
                'aprobado',
                'devuelto',
            ])->default('enviado');
            $table->text('observaciones_revision')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamps();

            $table->unique(['proceso_id', 'numero_informe']);
            $table->index('proceso_id');
            $table->index('supervisor_id');
            $table->index('estado_informe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_supervision');
    }
};

