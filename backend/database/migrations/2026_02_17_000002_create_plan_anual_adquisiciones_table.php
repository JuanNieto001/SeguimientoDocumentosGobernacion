<?php
/**
 * Archivo: backend/database/migrations/2026_02_17_000002_create_plan_anual_adquisiciones_table.php
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
        // Plan Anual de Adquisiciones (PAA)
        Schema::create('plan_anual_adquisiciones', function (Blueprint $table) {
            $table->id();
            $table->year('anio');
            $table->string('codigo_necesidad')->unique();
            $table->text('descripcion');
            $table->decimal('valor_estimado', 15, 2);
            $table->string('modalidad_contratacion'); // CD_PN, MC, SA, LP, CM
            $table->integer('trimestre_estimado');
            $table->string('dependencia_solicitante');
            $table->enum('estado', ['vigente', 'modificado', 'ejecutado', 'cancelado'])->default('vigente');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['anio', 'activo']);
            $table->index('modalidad_contratacion');
        });

        // Agregar relación de procesos con PAA
        Schema::table('procesos', function (Blueprint $table) {
            $table->foreignId('paa_item_id')->nullable()->constrained('plan_anual_adquisiciones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $table->dropForeign(['paa_item_id']);
            $table->dropColumn('paa_item_id');
        });
        
        Schema::dropIfExists('plan_anual_adquisiciones');
    }
};


