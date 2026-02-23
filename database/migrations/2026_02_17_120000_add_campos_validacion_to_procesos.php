<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            // Validaciones legales
            $table->boolean('requiere_secop')->default(true);
            $table->boolean('requiere_rup')->default(false);
            $table->integer('plazo_minimo_dias')->nullable();
            
            // Cuantías y valores
            $table->decimal('cuantia_smmlv', 12, 2)->nullable();
            $table->decimal('valor_modificaciones', 15, 2)->default(0);
            $table->decimal('porcentaje_modificaciones', 5, 2)->default(0);
            
            // Garantías
            $table->boolean('garantias_presentadas')->default(false);
            $table->json('garantias_detalle')->nullable();
            
            // Requisitos habilitantes
            $table->json('requisitos_habilitantes')->nullable();
            $table->boolean('requisitos_verificados')->default(false);
            
            // Validaciones por modalidad
            $table->json('validaciones_modalidad')->nullable();
            $table->boolean('modalidad_validada')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $table->dropColumn([
                'requiere_secop',
                'requiere_rup',
                'plazo_minimo_dias',
                'cuantia_smmlv',
                'valor_modificaciones',
                'porcentaje_modificaciones',
                'garantias_presentadas',
                'garantias_detalle',
                'requisitos_habilitantes',
                'requisitos_verificados',
                'validaciones_modalidad',
                'modalidad_validada',
            ]);
        });
    }
};

