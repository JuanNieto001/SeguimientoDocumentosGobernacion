<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$procesoId = $argv[1] ?? 1;

echo "\n=== VERIFICACIÓN DEL PROCESO #{$procesoId} ===\n\n";

$proceso = DB::table('procesos')->where('id', $procesoId)->first();

if (!$proceso) {
    echo "❌ Proceso no encontrado\n\n";
    exit(1);
}

echo "📋 Información del Proceso:\n";
echo "  - Código: {$proceso->codigo}\n";
echo "  - Estado: {$proceso->estado}\n";
echo "  - Etapa actual ID: {$proceso->etapa_actual_id}\n";
echo "  - Área actual: {$proceso->area_actual_role}\n\n";

$etapaActual = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
if ($etapaActual) {
    echo "📍 Etapa Actual:\n";
    echo "  - Nombre: {$etapaActual->nombre}\n";
    echo "  - Orden: {$etapaActual->orden}\n";
    echo "  - Área: {$etapaActual->area_role}\n\n";
}

$procesoEtapas = DB::table('proceso_etapas as pe')
    ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
    ->where('pe.proceso_id', $procesoId)
    ->select('e.nombre', 'e.orden', 'e.area_role', 'pe.recibido', 'pe.enviado', 'pe.recibido_at', 'pe.enviado_at')
    ->orderBy('e.orden')
    ->get();

echo "🔄 Historial de Etapas:\n";
foreach ($procesoEtapas as $pe) {
    $recibido = $pe->recibido ? "✅ Recibido ({$pe->recibido_at})" : "⏳ No recibido";
    $enviado = $pe->enviado ? "📤 Enviado ({$pe->enviado_at})" : "⏸ No enviado";
    echo "  [{$pe->orden}] {$pe->nombre} ({$pe->area_role}) - {$recibido} - {$enviado}\n";
}

echo "\n";
