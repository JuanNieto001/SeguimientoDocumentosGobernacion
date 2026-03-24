<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_configs', function (Blueprint $table) {
            $table->id();
            $table->string('rol');
            $table->string('nombre');
            $table->json('widgets')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_configs');
    }
};
