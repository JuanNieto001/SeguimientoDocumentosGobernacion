<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar supervisor a procesos
        Schema::table('procesos', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_asignado_at')->nullable();
            $table->decimal('valor_inicial', 15, 2)->nullable();
            $table->integer('plazo_dias')->nullable();
            $table->date('fecha_inicio_contrato')->nullable();
            $table->date('fecha_fin_contrato')->nullable();
        });

        // Agregar días estimados a etapas
        Schema::table('etapas', function (Blueprint $table) {
            $table->integer('dias_estimados')->nullable()->comment('Tiempo estimado en días para completar esta etapa');
        });

        // Agregar campos de seguimiento a proceso_etapas
        Schema::table('proceso_etapas', function (Blueprint $table) {
            $table->boolean('en_retraso')->default(false);
            $table->integer('dias_en_etapa')->nullable()->comment('Días transcurridos en esta etapa');
        });
    }

    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn([
                'supervisor_id', 
                'supervisor_asignado_at', 
                'valor_inicial', 
                'plazo_dias',
                'fecha_inicio_contrato',
                'fecha_fin_contrato'
            ]);
        });

        Schema::table('etapas', function (Blueprint $table) {
            $table->dropColumn('dias_estimados');
        });

        Schema::table('proceso_etapas', function (Blueprint $table) {
            $table->dropColumn(['en_retraso', 'dias_en_etapa']);
        });
    }
};

