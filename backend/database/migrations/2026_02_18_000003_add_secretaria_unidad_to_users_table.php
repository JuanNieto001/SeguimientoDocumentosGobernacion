<?php
/**
 * Archivo: backend/database/migrations/2026_02_18_000003_add_secretaria_unidad_to_users_table.php
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('secretaria_id')->nullable()->after('password')->constrained('secretarias')->nullOnDelete();
            $table->foreignId('unidad_id')->nullable()->after('secretaria_id')->constrained('unidades')->nullOnDelete();
            $table->boolean('activo')->default(true)->after('unidad_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['secretaria_id']);
            $table->dropForeign(['unidad_id']);
            $table->dropColumn(['secretaria_id', 'unidad_id', 'activo']);
        });
    }
};

