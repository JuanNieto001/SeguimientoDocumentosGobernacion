<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos_aplicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('aplicacion', 160);
            $table->string('numero_contrato', 120)->nullable();
            $table->string('proveedor', 180)->nullable();
            $table->text('objeto')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('valor_total', 16, 2)->nullable();
            $table->string('estado', 40)->default('vigente');
            $table->string('secop_proceso_id', 160)->nullable();
            $table->string('secop_url', 500)->nullable();
            $table->json('secop_metadata')->nullable();
            $table->string('responsable', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['activo', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('aplicacion');
            $table->index('secop_proceso_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos_aplicaciones');
    }
};
