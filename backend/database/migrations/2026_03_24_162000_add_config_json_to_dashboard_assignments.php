<?php
/**
 * Archivo: backend/database/migrations/2026_03_24_162000_add_config_json_to_dashboard_assignments.php
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
        Schema::table('dashboard_rol_asignaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('dashboard_rol_asignaciones', 'config_json')) {
                $table->json('config_json')->nullable()->after('prioridad');
            }
        });

        Schema::table('dashboard_usuario_asignaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('dashboard_usuario_asignaciones', 'config_json')) {
                $table->json('config_json')->nullable()->after('prioridad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_rol_asignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('dashboard_rol_asignaciones', 'config_json')) {
                $table->dropColumn('config_json');
            }
        });

        Schema::table('dashboard_usuario_asignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('dashboard_usuario_asignaciones', 'config_json')) {
                $table->dropColumn('config_json');
            }
        });
    }
};

