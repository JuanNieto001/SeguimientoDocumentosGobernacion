<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0) Workflows (tipos)
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // CD, MC, LP...
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 1) Procesos
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();

            $table->string('codigo')->unique();
            $table->string('objeto');
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('EN_CURSO');

            $table->unsignedBigInteger('etapa_actual_id')->nullable();
            $table->string('area_actual_role')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        // 2) Etapas (por workflow)
        Schema::create('etapas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();

            $table->unsignedInteger('orden');
            $table->string('nombre');
            $table->string('area_role');

            $table->foreignId('next_etapa_id')->nullable()->constrained('etapas')->nullOnDelete();

            $table->boolean('activa')->default(true);

            $table->timestamps();

            $table->unique(['workflow_id', 'orden']);
        });

        // FK procesos -> etapas
        Schema::table('procesos', function (Blueprint $table) {
            $table->foreign('etapa_actual_id')->references('id')->on('etapas')->nullOnDelete();
        });

        // 3) Items por etapa
        Schema::create('etapa_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();
            $table->unsignedInteger('orden')->default(1);

            $table->string('label');
            $table->boolean('requerido')->default(true);

            $table->timestamps();
        });

        // 4) Instancia etapa por proceso
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

        // 5) Checks por proceso_etapa
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

        Schema::table('procesos', function (Blueprint $table) {
            try { $table->dropForeign(['etapa_actual_id']); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('etapas');
        Schema::dropIfExists('procesos');
        Schema::dropIfExists('workflows');
    }
};
