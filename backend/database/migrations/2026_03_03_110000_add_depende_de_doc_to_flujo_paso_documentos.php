<?php
/**
 * Archivo: backend/database/migrations/2026_03_03_110000_add_depende_de_doc_to_flujo_paso_documentos.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega columna depende_de_doc a flujo_paso_documentos.
 * Almacena la referencia posicional "pasoOrden:docOrden" que indica
 * de qué documento de un paso anterior depende este check.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flujo_paso_documentos', function (Blueprint $table) {
            $table->string('depende_de_doc', 20)->nullable()->after('orden')
                  ->comment('Dependencia posicional "pasoOrden:docOrden"');
        });
    }

    public function down(): void
    {
        Schema::table('flujo_paso_documentos', function (Blueprint $table) {
            $table->dropColumn('depende_de_doc');
        });
    }
};

