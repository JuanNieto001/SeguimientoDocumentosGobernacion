<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->string('accion'); // 'creado', 'recibido', 'check_marcado', 'archivo_subido', 'enviado', 'documento_aprobado', etc.
            $table->text('descripcion')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha');
            $table->timestamps();
            
            $table->index(['proceso_id', 'fecha']);
            $table->index('accion');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_auditoria');
    }
};
