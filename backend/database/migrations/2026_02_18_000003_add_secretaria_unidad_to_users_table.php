<?php

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
