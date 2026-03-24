<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dashboard_asignacion_auditorias')) {
            return;
        }

        Schema::create('dashboard_asignacion_auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable();
            $table->string('tipo_objetivo', 20);
            $table->string('role_name', 80)->nullable();
            $table->foreignId('target_user_id')->nullable();
            $table->string('accion', 20);
            $table->foreignId('dashboard_plantilla_anterior_id')->nullable();
            $table->foreignId('dashboard_plantilla_nueva_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('actor_user_id', 'dash_aud_actor_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('target_user_id', 'dash_aud_target_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('dashboard_plantilla_anterior_id', 'dash_aud_prev_tpl_fk')
                ->references('id')
                ->on('dashboard_plantillas')
                ->nullOnDelete();

            $table->foreign('dashboard_plantilla_nueva_id', 'dash_aud_new_tpl_fk')
                ->references('id')
                ->on('dashboard_plantillas')
                ->nullOnDelete();

            $table->index(['tipo_objetivo', 'role_name']);
            $table->index(['tipo_objetivo', 'target_user_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_asignacion_auditorias');
    }
};
