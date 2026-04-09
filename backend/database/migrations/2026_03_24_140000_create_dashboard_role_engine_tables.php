<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->json('config_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_plantilla_id')->constrained('dashboard_plantillas')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('tipo', 40);
            $table->string('metrica', 80)->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->json('config_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['dashboard_plantilla_id', 'activo', 'orden']);
        });

        Schema::create('dashboard_rol_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->string('role_name', 80);
            $table->foreignId('dashboard_plantilla_id')->constrained('dashboard_plantillas')->cascadeOnDelete();
            $table->unsignedSmallInteger('prioridad')->default(100);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('role_name');
            $table->index(['activo', 'prioridad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_rol_asignaciones');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('dashboard_plantillas');
    }
};
