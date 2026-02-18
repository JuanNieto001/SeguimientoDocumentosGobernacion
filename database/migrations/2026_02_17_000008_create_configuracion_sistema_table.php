<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor');
            $table->string('tipo')->default('string'); // 'string', 'number', 'boolean', 'json'
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // Insertar configuraciones por defecto
        DB::table('configuracion_sistema')->insert([
            [
                'clave' => 'menor_cuantia_entidad',
                'valor' => '500000000',
                'tipo' => 'number',
                'descripcion' => 'Menor cuantía de la entidad en pesos colombianos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'dias_alerta_sin_movimiento',
                'valor' => '7',
                'tipo' => 'number',
                'descripcion' => 'Días sin movimiento antes de generar alerta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'dias_alerta_tiempo_excedido',
                'valor' => '5',
                'tipo' => 'number',
                'descripcion' => 'Días de exceso antes de generar alerta de tiempo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
