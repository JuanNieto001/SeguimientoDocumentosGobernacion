<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_approvals', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('process_id')->constrained('contract_processes')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('process_documents')->nullOnDelete();
            
            $table->string('approval_type'); // document_approval, step_completion, legal_review, etc.
            $table->integer('step_number'); // Etapa relacionada
            
            $table->string('status'); // pending, approved, rejected, requires_fixes
            $table->text('comments')->nullable();
            $table->json('checklist')->nullable(); // Items del checklist con estado
            
            $table->foreignId('requested_from')->constrained('users')->nullOnDelete(); // A quién se solicita
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamp('requested_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('due_date')->nullable(); // Fecha límite para respuesta
            
            $table->timestamps();
            
            // Índices
            $table->index(['process_id', 'status']);
            $table->index(['requested_from', 'status']);
            $table->index('step_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_approvals');
    }
};
