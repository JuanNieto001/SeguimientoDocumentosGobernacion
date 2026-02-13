<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * =========================
         * 1) PROCESOS (expedientes)
         * =========================
         */
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();          // ej: CD-2026-0001
            $table->string('objeto');                    // nombre / objeto del proceso
            $table->text('descripcion')->nullable();

            // Estado "global" del expediente
            $table->string('estado')->default('EN_CURSO'); // EN_CURSO | FINALIZADO | ANULADO

            // En qué etapa / secretaría está actualmente (FK se agrega después)
            $table->unsignedBigInteger('etapa_actual_id')->nullable();
            $table->string('area_actual_role')->nullable(); // 'planeacion', 'juridica', etc.

            // Quién creó el proceso
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        /**
         * ==================================
         * 2) ETAPAS (plantilla del flujo)
         * ==================================
         */
        Schema::create('etapas', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('orden'); // 1..N
            $table->string('nombre');         // "Solicitante / Estudios previos", etc.
            $table->string('area_role');      // 'unidad_solicitante', 'planeacion', 'hacienda', 'juridica', 'secop'

            // Siguiente etapa (self FK: está ok porque 'etapas' ya existe al final del create)
            $table->foreignId('next_etapa_id')->nullable()->constrained('etapas')->nullOnDelete();

            $table->boolean('activa')->default(true);

            $table->timestamps();

            $table->unique(['orden']);
        });

        /**
         * ✅ Ahora sí: FK de procesos.etapa_actual_id (etapas ya existe)
         */
        Schema::table('procesos', function (Blueprint $table) {
            $table->foreign('etapa_actual_id')
                ->references('id')
                ->on('etapas')
                ->nullOnDelete();
        });

        /**
         * ==========================================
         * 3) ITEMS DE CHECKLIST (plantilla por etapa)
         * ==========================================
         */
        Schema::create('etapa_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();
            $table->unsignedInteger('orden')->default(1);

            $table->string('label');              // texto del checkbox
            $table->boolean('requerido')->default(true);

            $table->timestamps();
        });

        /**
         * ===================================================
         * 4) PROCESO_ETAPAS (instancia por proceso y por etapa)
         * ===================================================
         */
        Schema::create('proceso_etapas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();

            $table->boolean('recibido')->default(false);
            $table->foreignId('recibido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recibido_at')->nullable();

            $table->boolean('enviado')->default(false);
            $table->foreignId('enviado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enviado_at')->nullable();

            $table->timestamps();

            $table->unique(['proceso_id', 'etapa_id']);
        });

        /**
         * ==========================================================
         * 5) PROCESO_ETAPA_CHECKS (respuesta a checklist por proceso)
         * ==========================================================
         */
        Schema::create('proceso_etapa_checks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proceso_etapa_id')->constrained('proceso_etapas')->cascadeOnDelete();
            $table->foreignId('etapa_item_id')->constrained('etapa_items')->cascadeOnDelete();

            $table->boolean('checked')->default(false);
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();

            $table->timestamps();

            $table->unique(['proceso_etapa_id', 'etapa_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_etapa_checks');
        Schema::dropIfExists('proceso_etapas');
        Schema::dropIfExists('etapa_items');

        // 🔻 Primero elimina la FK antes de borrar tablas
        Schema::table('procesos', function (Blueprint $table) {
            // por si quedó creada
            try {
                $table->dropForeign(['etapa_actual_id']);
            } catch (\Throwable $e) {
                // no hacer nada si no existe
            }
        });

        Schema::dropIfExists('etapas');
        Schema::dropIfExists('procesos');
    }
};
