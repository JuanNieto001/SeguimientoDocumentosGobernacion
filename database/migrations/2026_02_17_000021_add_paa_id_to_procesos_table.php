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
        Schema::table('procesos', function (Blueprint $table) {
            // Agregar relación con PAA
            $table->foreignId('paa_id')->nullable()->after('workflow_id')->constrained('plan_anual_adquisiciones')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $table->dropForeign(['paa_id']);
            $table->dropColumn('paa_id');
        });
    }
};
