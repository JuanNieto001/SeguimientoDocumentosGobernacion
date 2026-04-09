<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_steps', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('process_id')->constrained('contract_processes')->cascadeOnDelete();
            $table->integer('step_number'); // 0-9
            $table->string('step_name'); // Nombre de la etapa
            $table->string('status'); // pending, in_progress, completed, blocked
            
            $table->text('requirements')->nullable(); // Requisitos de la etapa (JSON)
            $table->text('notes')->nullable();
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Índices
            $table->index(['process_id', 'step_number']);
            $table->unique(['process_id', 'step_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_steps');
    }
};
