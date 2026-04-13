<?php
/**
 * Archivo: backend/database/migrations/2026_04_12_230100_create_proceso_sia_observa_archivos_table.php
 * Proposito: Repositorio de documentos finales para carga externa (SIA Observa).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_sia_observa_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->string('tipo_documento', 120)->default('documento_final');
            $table->string('nombre_original', 255);
            $table->string('nombre_guardado', 255);
            $table->string('ruta', 500);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('tamanio')->default(0);
            $table->unsignedSmallInteger('version')->default(1);
            $table->text('descripcion')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['proceso_id', 'tipo_documento']);
            $table->index('subido_por');
            $table->unique(['proceso_id', 'nombre_guardado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_sia_observa_archivos');
    }
};
