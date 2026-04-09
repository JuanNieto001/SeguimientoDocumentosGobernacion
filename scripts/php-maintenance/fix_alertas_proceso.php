<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Alerta;

$proceso = DB::table('procesos')->where('codigo', 'like', '%CD_PN%')->first();
if (!$proceso) { echo "No se encontró proceso\n"; exit; }

// Eliminar las 4 alertas duplicadas
$deleted = Alerta::where('proceso_id', $proceso->id)->delete();
echo "Eliminadas: {$deleted} alertas duplicadas\n";

// Crear 1 sola alerta por área
$etapa = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
Alerta::create([
    'proceso_id'       => $proceso->id,
    'tipo'             => 'proceso_recibido',
    'titulo'           => 'Nuevo proceso asignado',
    'mensaje'          => "Nuevo proceso {$proceso->codigo} recibido en " . ($etapa ? $etapa->nombre : 'etapa actual'),
    'prioridad'        => 'alta',
    'area_responsable' => $proceso->area_actual_role,
    'accion_url'       => '/procesos/' . $proceso->id,
]);

echo "Creada: 1 alerta para área '{$proceso->area_actual_role}'\n";
echo "Total alertas: " . Alerta::where('proceso_id', $proceso->id)->count() . "\n";
