<?php
/**
 * Archivo: backend/database/migrations/2026_04_12_230200_create_proceso_sia_observa_accesos_table.php
 * Proposito: Asignaciones de acceso por rol o usuario para repositorio SIA Observa.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_sia_observa_accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->enum('asignacion_tipo', ['rol', 'usuario']);
            $table->string('acceso_clave', 180);
            $table->string('role_name', 100)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('puede_ver')->default(true);
            $table->boolean('puede_subir')->default(false);
            $table->boolean('activo')->default(true);
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['proceso_id', 'acceso_clave']);
            $table->index(['proceso_id', 'asignacion_tipo']);
            $table->index('role_name');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_sia_observa_accesos');
    }
};
