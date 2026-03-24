<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos_aplicacion', function (Blueprint $table) {
            $table->id();

            // Información de la aplicación
            $table->string('nombre_aplicacion');
            $table->string('proveedor')->nullable();
            $table->text('descripcion')->nullable();

            // Fechas del contrato
            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            // Datos del contrato
            $table->string('numero_contrato')->nullable();
            $table->decimal('valor_contrato', 15, 2)->nullable();
            $table->string('modalidad_contratacion')->nullable();
            $table->string('estado')->default('activo'); // activo, vencido, cancelado

            // Referencia SECOP
            $table->string('secop_id')->nullable();
            $table->string('secop_url')->nullable();

            // Secretaría/unidad responsable
            $table->foreignId('secretaria_id')->nullable()->constrained('secretarias')->nullOnDelete();
            $table->foreignId('unidad_id')->nullable()->constrained('unidades')->nullOnDelete();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha_fin']);
            $table->index('secretaria_id');
            $table->index('nombre_aplicacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos_aplicacion');
    }
};
