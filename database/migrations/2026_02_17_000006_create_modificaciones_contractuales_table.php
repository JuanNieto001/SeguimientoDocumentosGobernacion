<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modificaciones_contractuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->enum('tipo', ['adicion', 'prorroga', 'suspension']);
            $table->decimal('valor_modificacion', 15, 2)->nullable(); // Para adiciones
            $table->integer('dias_prorroga')->nullable();
            $table->text('justificacion');
            $table->date('fecha_inicio_suspension')->nullable();
            $table->date('fecha_fin_suspension')->nullable();
            $table->string('documento_soporte')->nullable();
            $table->foreignId('solicitado_por')->constrained('users')->cascadeOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprobado_at')->nullable();
            $table->string('estado')->default('pendiente'); // 'pendiente', 'aprobado', 'rechazado'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modificaciones_contractuales');
    }
};
