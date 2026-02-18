<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            // Columnas requeridas por el WorkflowSeeder
            $table->boolean('requiere_viabilidad_economica_inicial')->default(true)->after('activo');
            $table->boolean('requiere_estudios_previos_completos')->default(true)->after('requiere_viabilidad_economica_inicial');
            $table->text('observaciones')->nullable()->after('requiere_estudios_previos_completos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn([
                'requiere_viabilidad_economica_inicial',
                'requiere_estudios_previos_completos',
                'observaciones',
            ]);
        });
    }
};
