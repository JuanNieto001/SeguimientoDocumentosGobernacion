<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add flujo_id to procesos (links to Motor de Flujos)
        Schema::table('procesos', function (Blueprint $table) {
            $table->unsignedBigInteger('flujo_id')->nullable()->after('workflow_id');
            $table->foreign('flujo_id')->references('id')->on('flujos')->nullOnDelete();
        });

        // 2) Make workflow_id nullable (may not have a legacy workflow)
        Schema::table('procesos', function (Blueprint $table) {
            $table->unsignedBigInteger('workflow_id')->nullable()->change();
        });

        // 3) Make tipo_contratacion nullable in flujos (user defines it in the name)
        Schema::table('flujos', function (Blueprint $table) {
            $table->string('tipo_contratacion', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('flujos', function (Blueprint $table) {
            $table->string('tipo_contratacion', 50)->nullable(false)->change();
        });

        Schema::table('procesos', function (Blueprint $table) {
            $table->unsignedBigInteger('workflow_id')->nullable(false)->change();
        });

        Schema::table('procesos', function (Blueprint $table) {
            $table->dropForeign(['flujo_id']);
            $table->dropColumn('flujo_id');
        });
    }
};
