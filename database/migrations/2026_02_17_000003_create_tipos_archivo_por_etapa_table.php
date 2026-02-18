<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Define qué tipos de archivo requiere cada etapa
        Schema::create('tipos_archivo_por_etapa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();
            $table->string('tipo_archivo'); // cdp, viabilidad_economica, ajustado_derecho, etc.
            $table->string('label'); // "Certificado de Disponibilidad Presupuestal"
            $table->boolean('requerido')->default(true);
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(1);
            $table->timestamps();
            
            $table->unique(['etapa_id', 'tipo_archivo']);
            $table->index('requerido');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_archivo_por_etapa');
    }
};
