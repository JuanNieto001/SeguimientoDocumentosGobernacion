<?php
/**
 * Archivo: backend/database/migrations/2026_04_12_231000_add_image_fields_to_estiven_guide_steps_table.php
 * Proposito: Soporte de imagenes en pasos de guias de Marsetiv.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estiven_guide_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('estiven_guide_steps', 'image_path')) {
                $table->string('image_path', 500)->nullable()->after('content');
            }
            if (!Schema::hasColumn('estiven_guide_steps', 'image_caption')) {
                $table->string('image_caption', 255)->nullable()->after('image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estiven_guide_steps', function (Blueprint $table) {
            if (Schema::hasColumn('estiven_guide_steps', 'image_caption')) {
                $table->dropColumn('image_caption');
            }
            if (Schema::hasColumn('estiven_guide_steps', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
