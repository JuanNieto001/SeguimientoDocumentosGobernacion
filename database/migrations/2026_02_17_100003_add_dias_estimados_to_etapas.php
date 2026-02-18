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
        // Agregar días estimados a etapas
        Schema::table('etapas', function (Blueprint $table) {
            if (!Schema::hasColumn('etapas', 'dias_estimados')) {
                $table->integer('dias_estimados')->nullable()->after('area_responsable')
                    ->comment('Días estimados para completar esta etapa');
            }
        });

        // Actualizar con valores por defecto (7 días por defecto)
        DB::table('etapas')->whereNull('dias_estimados')->update(['dias_estimados' => 7]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etapas', function (Blueprint $table) {
            if (Schema::hasColumn('etapas', 'dias_estimados')) {
                $table->dropColumn('dias_estimados');
            }
        });
    }
};
