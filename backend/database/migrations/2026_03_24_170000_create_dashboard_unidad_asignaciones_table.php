<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dashboard_unidad_asignaciones')) {
            return;
        }

        Schema::create('dashboard_unidad_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('dashboard_plantilla_id')->constrained('dashboard_plantillas')->cascadeOnDelete();
            $table->unsignedSmallInteger('prioridad')->default(40);
            $table->json('config_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('unidad_id');
            $table->index(['activo', 'prioridad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_unidad_asignaciones');
    }
};
