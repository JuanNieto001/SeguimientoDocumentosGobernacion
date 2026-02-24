<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_contrato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('informe_id')->nullable()->constrained('informes_supervision')->nullOnDelete();
            $table->unsignedSmallInteger('numero_pago'); // 1, 2, 3...
            $table->decimal('valor', 15, 2);
            $table->date('fecha_solicitud');
            $table->date('fecha_estimada_pago')->nullable(); // Alerta: 5 días antes
            $table->date('fecha_pago_efectivo')->nullable();
            $table->enum('estado', [
                'pendiente',
                'en_tramite',
                'aprobado',
                'pagado',
                'rechazado',
            ])->default('pendiente');
            $table->string('numero_referencia', 100)->nullable(); // Número de orden de pago
            $table->text('observaciones')->nullable();
            $table->string('archivo_soporte')->nullable(); // Comprobante
            $table->foreignId('registrado_por')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['proceso_id', 'numero_pago']);
            $table->index('proceso_id');
            $table->index('estado');
            $table->index('fecha_estimada_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_contrato');
    }
};
