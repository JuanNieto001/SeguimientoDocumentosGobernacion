<?php
/**
 * Archivo: backend/database/migrations/2026_04_07_091227_add_dashboard_scope_to_roles_table.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Alcance de vista del dashboard:
     * - 'global': Ve todos los procesos del sistema
     * - 'secretaria': Ve solo procesos de su secretaría
     * - 'unidad': Ve solo procesos de su unidad
     * - 'propios': Ve solo sus propios procesos
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('dashboard_scope', ['global', 'secretaria', 'unidad', 'propios'])
                  ->default('propios')
                  ->after('guard_name')
                  ->comment('Alcance de datos visibles en el dashboard');
        });

        // Asignar alcance por defecto según el rol
        DB::table('roles')->where('name', 'admin')->update(['dashboard_scope' => 'global']);
        DB::table('roles')->where('name', 'admin_general')->update(['dashboard_scope' => 'global']);
        DB::table('roles')->where('name', 'gobernador')->update(['dashboard_scope' => 'global']);
        DB::table('roles')->where('name', 'secretario')->update(['dashboard_scope' => 'secretaria']);
        DB::table('roles')->where('name', 'admin_secretaria')->update(['dashboard_scope' => 'secretaria']);
        DB::table('roles')->where('name', 'jefe_unidad')->update(['dashboard_scope' => 'unidad']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('dashboard_scope');
        });
    }
};

