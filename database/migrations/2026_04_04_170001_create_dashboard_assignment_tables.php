<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabla para guardar dashboards creados por Admin
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del dashboard
            $table->text('description')->nullable();
            $table->json('widgets'); // Configuración de widgets
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Tabla para asignar dashboards a usuarios
        Schema::create('dashboard_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('dashboards')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->boolean('active')->default(true);
            
            $table->unique(['dashboard_id', 'user_id']);
        });

        // Tabla para asignar dashboards por rol
        Schema::create('dashboard_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('dashboards')->onDelete('cascade');
            $table->string('role_name');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->boolean('active')->default(true);
            
            $table->unique(['dashboard_id', 'role_name']);
        });

        // Tabla para asignar dashboards por secretaría
        Schema::create('dashboard_secretaria_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('dashboards')->onDelete('cascade');
            $table->foreignId('secretaria_id')->constrained('secretarias')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->boolean('active')->default(true);
            
            $table->unique(['dashboard_id', 'secretaria_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dashboard_secretaria_assignments');
        Schema::dropIfExists('dashboard_role_assignments');
        Schema::dropIfExists('dashboard_assignments');
        Schema::dropIfExists('dashboards');
    }
};