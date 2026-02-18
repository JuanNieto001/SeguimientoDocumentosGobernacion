<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->nullable()->constrained('procesos')->cascadeOnDelete();
            $table->string('tipo'); // 'tiempo_excedido', 'documento_vencido', 'sin_movimiento', 'documento_rechazado', etc.
            $table->string('prioridad')->default('media'); // 'baja', 'media', 'alta', 'critica'
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Usuario que debe ver la alerta
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'leida', 'created_at']);
            $table->index('tipo');
            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
