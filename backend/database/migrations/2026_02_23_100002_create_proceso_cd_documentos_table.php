<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_cd_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_cd_id')
                  ->constrained('proceso_contratacion_directa')
                  ->cascadeOnDelete();

            $table->string('tipo_documento', 80)->index();
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->unsignedTinyInteger('etapa');
            $table->string('estado_aprobacion', 30)->default('pendiente'); // pendiente, aprobado, rechazado
            $table->text('observaciones')->nullable();
            $table->foreignId('subido_por')->constrained('users');
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('es_obligatorio')->default(true);
            $table->unsignedBigInteger('reemplaza_id')->nullable();
            $table->timestamps();

            $table->foreign('reemplaza_id')->references('id')->on('proceso_cd_documentos')->nullOnDelete();
            $table->index(['proceso_cd_id', 'tipo_documento']);
            $table->index(['proceso_cd_id', 'etapa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_cd_documentos');
    }
};
