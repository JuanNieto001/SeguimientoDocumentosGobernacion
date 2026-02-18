<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_etapa_archivos', function (Blueprint $table) {
            $table->id();

            // Relación con proceso y etapa
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('proceso_etapa_id')->constrained('proceso_etapas')->cascadeOnDelete();
            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();

            // Información del archivo
            $table->string('tipo_archivo'); // 'borrador_estudios_previos', 'formato_necesidades', 'anexo', 'cotizacion', etc.
            $table->string('nombre_original');
            $table->string('nombre_guardado'); // nombre único en storage
            $table->string('ruta'); // path relativo desde storage/app/public
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamanio')->nullable(); // tamaño en bytes

            // Auditoría
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();

            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index(['proceso_id', 'etapa_id']);
            $table->index('tipo_archivo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_etapa_archivos');
    }
};
