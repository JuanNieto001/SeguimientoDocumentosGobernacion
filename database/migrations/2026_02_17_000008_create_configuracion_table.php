<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor');
            $table->string('tipo')->default('string'); // string, number, boolean, json
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
        
        // Insertar configuraciones iniciales
        DB::table('configuracion')->insert([
            [
                'clave' => 'menor_cuantia_entidad',
                'valor' => '1000000000', // 1.000 millones de pesos (ejemplo)
                'tipo' => 'number',
                'descripcion' => 'Menor cuantía de la entidad en pesos colombianos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'dias_alerta_documento_vencido',
                'valor' => '5',
                'tipo' => 'number',
                'descripcion' => 'Días antes del vencimiento para generar alerta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'dias_sin_movimiento_alerta',
                'valor' => '7',
                'tipo' => 'number',
                'descripcion' => 'Días sin movimiento para generar alerta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'porcentaje_maximo_adicion',
                'valor' => '50',
                'tipo' => 'number',
                'descripcion' => 'Porcentaje máximo de adición según Ley 80 Art. 40',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
