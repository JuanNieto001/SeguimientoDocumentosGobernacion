<?php
/**
 * Archivo: backend/database/migrations/2026_02_23_100001_create_proceso_cd_auditoria_table.php
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
        Schema::create('proceso_cd_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_cd_id')
                  ->constrained('proceso_contratacion_directa')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('accion', 100);
            $table->string('estado_anterior', 60)->nullable();
            $table->string('estado_nuevo', 60)->nullable();
            $table->text('descripcion')->nullable();
            $table->json('datos_extra')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('proceso_cd_id');
            $table->index('user_id');
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_cd_auditoria');
    }
};

