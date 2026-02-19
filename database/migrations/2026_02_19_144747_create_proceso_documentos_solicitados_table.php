<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para gestionar solicitudes de documentos a otras áreas
     * 
     * CASO DE USO: Etapa 1 - Descentralización solicita documentos a:
     * - Compras (PAA)
     * - Talento Humano (No Planta)
     * - Rentas (Paz y Salvo)
     * - Contabilidad (Paz y Salvo)
     * - Inversiones Públicas (Compatibilidad del Gasto)
     * - Presupuesto (CDP) → depende de Compatibilidad
     * - Jurídica (SIGEP)
     */
    public function up(): void
    {
        Schema::create('proceso_documentos_solicitados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->onDelete('cascade');
            $table->foreignId('etapa_id')->constrained('etapas')->onDelete('cascade');
            
            // Tipo de documento solicitado
            $table->string('tipo_documento', 100);
            $table->string('nombre_documento', 255);
            
            // Área responsable de subir el documento
            $table->string('area_responsable_rol', 50); // ej: 'hacienda', 'secop', 'juridica'
            $table->string('area_responsable_nombre', 255)->nullable();
            $table->foreignId('secretaria_responsable_id')->nullable()->constrained('secretarias')->onDelete('set null');
            $table->foreignId('unidad_responsable_id')->nullable()->constrained('unidades')->onDelete('set null');
            
            // Estado de la solicitud
            $table->enum('estado', ['pendiente', 'subido', 'rechazado', 'observado'])->default('pendiente');
            
            // Dependencias (ej: CDP depende de Compatibilidad del Gasto)
            $table->foreignId('depende_de_solicitud_id')->nullable()->constrained('proceso_documentos_solicitados')->onDelete('set null');
            $table->boolean('puede_subir')->default(true)->comment('false si depende de otro documento no subido');
            
            // Auditoría
            $table->foreignId('solicitado_por')->constrained('users')->onDelete('cascade');
            $table->timestamp('solicitado_at');
            $table->foreignId('subido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('subido_at')->nullable();
            $table->foreignId('archivo_id')->nullable()->constrained('proceso_etapa_archivos')->onDelete('set null');
            
            // Observaciones
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['proceso_id', 'estado']);
            $table->index(['area_responsable_rol', 'estado']);
            $table->index('tipo_documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_documentos_solicitados');
    }
};
