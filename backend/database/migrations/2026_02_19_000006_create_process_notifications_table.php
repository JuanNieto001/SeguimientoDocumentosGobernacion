<?php
/**
 * Archivo: backend/database/migrations/2026_02_19_000006_create_process_notifications_table.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_notifications', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('process_id')->constrained('contract_processes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('type'); // missing_document, legal_return, approval_required, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Datos adicionales
            
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'is_read']);
            $table->index(['process_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_notifications');
    }
};

