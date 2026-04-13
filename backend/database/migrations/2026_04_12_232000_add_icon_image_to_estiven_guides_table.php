<?php
/**
 * Archivo: backend/database/migrations/2026_04_12_232000_add_icon_image_to_estiven_guides_table.php
 * Proposito: Permitir icono por imagen en guias de Marsetiv.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estiven_guides', function (Blueprint $table) {
            if (!Schema::hasColumn('estiven_guides', 'icon_image_path')) {
                $table->string('icon_image_path', 500)->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estiven_guides', function (Blueprint $table) {
            if (Schema::hasColumn('estiven_guides', 'icon_image_path')) {
                $table->dropColumn('icon_image_path');
            }
        });
    }
};
