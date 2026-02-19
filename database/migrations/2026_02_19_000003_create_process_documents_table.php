<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_documents', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('process_id')->constrained('contract_processes')->cascadeOnDelete();
            $table->integer('step_number'); // Etapa a la que pertenece (0-9)
            
            $table->string('document_type'); // Enum DocumentType
            $table->string('document_name'); // Nombre descriptivo
            $table->string('file_path'); // Ruta del archivo
            $table->string('file_name'); // Nombre original del archivo
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable(); // En bytes
            
            $table->boolean('is_required')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->string('approval_status')->default('pending'); // pending, approved, rejected, requires_fixes
            
            // Vigencia del documento
            $table->date('issued_at')->nullable(); // Fecha de expedición
            $table->date('expires_at')->nullable(); // Fecha de vencimiento
            $table->boolean('is_expired')->default(false);
            
            // Firmas requeridas
            $table->boolean('requires_signature')->default(false);
            $table->json('required_signers')->nullable(); // IDs de usuarios que deben firmar
            $table->json('signatures')->nullable(); // Registro de firmas
            
            // Metadatos adicionales
            $table->json('metadata')->nullable(); // Campos adicionales flexibles
            $table->text('observations')->nullable();
            
            // Auditoría
            $table->foreignId('uploaded_by')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['process_id', 'step_number']);
            $table->index(['document_type', 'process_id']);
            $table->index('approval_status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_documents');
    }
};
