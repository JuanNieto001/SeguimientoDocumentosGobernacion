<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_proceso', 50); // Ej: CD_PN-2026-0001
            $table->foreignId('proceso_id')->nullable()->constrained('procesos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', ['entrega', 'recepcion', 'consulta']);
            $table->string('area_origen', 150)->nullable();
            $table->string('area_destino', 150)->nullable();
            $table->string('responsable_nombre', 255)->nullable(); // Quien entrega/recibe
            $table->text('observaciones')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('codigo_proceso');
            $table->index('proceso_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_eventos');
    }
};
