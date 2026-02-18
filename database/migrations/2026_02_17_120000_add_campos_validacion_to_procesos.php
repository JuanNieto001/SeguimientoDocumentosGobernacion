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
            $table->boolean('requiere_secop')->default(true)->after('publicado_secop');
            $table->boolean('requiere_rup')->default(false)->after('requiere_secop');
            $table->integer('plazo_minimo_dias')->nullable()->after('requiere_rup');
            
            // Cuantías y valores
            $table->decimal('cuantia_smmlv', 12, 2)->nullable()->after('valor_estimado');
            $table->decimal('valor_modificaciones', 15, 2)->default(0)->after('cuantia_smmlv');
            $table->decimal('porcentaje_modificaciones', 5, 2)->default(0)->after('valor_modificaciones');
            
            // Garantías
            $table->boolean('garantias_presentadas')->default(false)->after('polizas_aprobadas');
            $table->json('garantias_detalle')->nullable()->after('garantias_presentadas');
            
            // Requisitos habilitantes
            $table->json('requisitos_habilitantes')->nullable()->after('garantias_detalle');
            $table->boolean('requisitos_verificados')->default(false)->after('requisitos_habilitantes');
            
            // Validaciones por modalidad
            $table->json('validaciones_modalidad')->nullable()->after('requisitos_verificados');
            $table->boolean('modalidad_validada')->default(false)->after('validaciones_modalidad');
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
