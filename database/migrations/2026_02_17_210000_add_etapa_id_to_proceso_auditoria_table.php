<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proceso_auditoria', function (Blueprint $table) {
            // Nullable to avoid breaking existing registros; index for faster consultas
            $table->unsignedBigInteger('etapa_id')->nullable()->after('descripcion');
            $table->index('etapa_id');
        });
    }

    public function down(): void
    {
        Schema::table('proceso_auditoria', function (Blueprint $table) {
            $table->dropIndex(['etapa_id']);
            $table->dropColumn('etapa_id');
        });
    }
};
