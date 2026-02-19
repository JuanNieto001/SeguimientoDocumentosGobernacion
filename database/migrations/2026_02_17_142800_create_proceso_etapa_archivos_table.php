<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('proceso_etapa_archivos')) {
            return;
        }

        Schema::create('proceso_etapa_archivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proceso_etapa_id')
                ->constrained('proceso_etapas')
                ->cascadeOnDelete();

            // Para reglas: borrador_estudios, cotizacion, otro
            $table->string('tipo', 50);

            $table->string('nombre_original');
            $table->string('path'); // storage path
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['proceso_etapa_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_etapa_archivos');
    }
};
