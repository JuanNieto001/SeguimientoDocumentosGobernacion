<?php
/**
 * Archivo: backend/database/migrations/2026_02_18_000002_create_unidades_table.php
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
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('secretaria_id')->constrained('secretarias')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['nombre', 'secretaria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};

