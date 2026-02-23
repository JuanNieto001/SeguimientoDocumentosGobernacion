<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_processes', function (Blueprint $table) {
            $table->id();
            
            // Información básica del proceso
            $table->string('process_type')->default('cd_pn'); // cd_pn, cd_pj, lp, etc.
            $table->string('status')->index(); // Estado actual del workflow
            $table->integer('current_step')->default(0); // Etapa actual (0-9)
            
            // Números de identificación
            $table->string('process_number')->unique()->nullable(); // Ej: CD-PN-001-2026
            $table->string('contract_number')->unique()->nullable(); // Asignado en etapa 8
            $table->string('secop_id')->nullable(); // ID en SECOP II
            $table->string('rpc_number')->nullable(); // Número de RPC
            
            // Datos del contrato
            $table->text('object'); // Objeto del contrato
            $table->decimal('estimated_value', 15, 2); // Valor estimado
            $table->integer('term_days'); // Plazo en días
            $table->date('expected_start_date')->nullable();
            $table->date('actual_start_date')->nullable();
            
            // Relaciones con personas
            $table->foreignId('contractor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contractor_name')->nullable(); // Por si aún no está en BD
            $table->string('contractor_document_type')->nullable(); // CC, NIT
            $table->string('contractor_document_number')->nullable();
            $table->string('contractor_email')->nullable();
            $table->string('contractor_phone')->nullable();
            
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ordering_officer_id')->nullable()->constrained('users')->nullOnDelete(); // Ordenador del gasto
            $table->foreignId('unit_head_id')->nullable()->constrained('users')->nullOnDelete(); // Jefe de unidad
            $table->foreignId('unit_lawyer_id')->nullable()->constrained('users')->nullOnDelete(); // Abogado de unidad
            $table->foreignId('link_lawyer_id')->nullable()->constrained('users')->nullOnDelete(); // Abogado enlace jurídica
            
            // Relaciones organizacionales
            $table->foreignId('secretaria_id')->nullable()->constrained('secretarias')->nullOnDelete();
            $table->foreignId('unidad_id')->nullable()->constrained('unidades')->nullOnDelete();
            
            // Campos de auditoría y seguimiento
            $table->text('observations')->nullable();
            $table->json('metadata')->nullable(); // Para datos adicionales flexibles
            $table->timestamp('submitted_to_legal_at')->nullable(); // Fecha radicación jurídica
            $table->timestamp('signed_at')->nullable(); // Fecha firma contrato
            $table->timestamp('published_secop_at')->nullable();
            $table->timestamp('rpc_issued_at')->nullable();
            $table->timestamp('started_at')->nullable(); // Fecha acta de inicio
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para búsquedas comunes
            $table->index(['status', 'current_step']);
            $table->index('contractor_document_number');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_processes');
    }
};
