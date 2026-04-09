<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_audit_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('process_id')->constrained('contract_processes')->cascadeOnDelete();
            
            $table->string('action'); // state_changed, document_uploaded, approval_granted, etc.
            $table->string('entity_type')->nullable(); // ContractProcess, ProcessDocument, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->json('changes')->nullable(); // Detalles completos del cambio
            
            $table->text('description'); // Descripción legible del cambio
            $table->text('notes')->nullable();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['process_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_audit_logs');
    }
};
