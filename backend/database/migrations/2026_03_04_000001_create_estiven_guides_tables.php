<?php
/**
 * Archivo: backend/database/migrations/2026_03_04_000001_create_estiven_guides_tables.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estiven_guides', function (Blueprint $table) {
            $table->id();
            $table->string('role', 50)->index();      // nombre interno del rol (ej: admin, unidad_solicitante, _common)
            $table->string('icon', 10)->default('📋'); // emoji
            $table->string('title');                    // título de la guía
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('estiven_guide_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estiven_guide_id')
                  ->constrained('estiven_guides')
                  ->cascadeOnDelete();
            $table->unsignedSmallInteger('step_number');
            $table->text('content'); // HTML permitido (instrucciones con <strong>, etc.)
            $table->timestamps();

            $table->unique(['estiven_guide_id', 'step_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estiven_guide_steps');
        Schema::dropIfExists('estiven_guides');
    }
};

