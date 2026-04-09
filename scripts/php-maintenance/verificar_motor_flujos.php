<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Flujo;
use App\Models\FlujoPaso;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  MOTOR DE FLUJOS - Contratación Directa Persona Natural\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$flujo = Flujo::where('codigo', 'CD_PN')->first();

if (!$flujo) {
    echo "❌ No se encontró el flujo CD_PN\n";
    exit(1);
}

echo "✅ Flujo: {$flujo->nombre}\n";
echo "   Código: {$flujo->codigo}\n";
if ($flujo->secretaria) {
    echo "   Secretaría: {$flujo->secretaria->nombre}\n";
}
echo "   Estado: {$flujo->estado}\n\n";

$version = $flujo->versionActiva;
if (!$version) {
    echo "⚠️  No hay versión activa\n";
    exit(0);
}

echo "📦 Versión Activa: v{$version->numero_version}\n";
echo "   Publicada: " . ($version->es_publicada ? 'Sí' : 'No') . "\n";
echo "   Total Pasos: " . $version->pasos->count() . "\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "  PASOS DEL FLUJO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

foreach ($version->pasos->sortBy('orden') as $paso) {
    echo "📋 Paso {$paso->orden}: {$paso->catalogoPaso->nombre}\n";
    echo "   Código: {$paso->catalogoPaso->codigo}\n";
    echo "   Tipo: {$paso->catalogoPaso->tipo}\n";
    
    if ($paso->responsables->count() > 0) {
        echo "   Responsables:\n";
        foreach ($paso->responsables as $resp) {
            echo "      • {$resp->area}\n";
        }
    }
    
    if ($paso->documentos->count() > 0) {
        echo "   Documentos requeridos: " . $paso->documentos->count() . "\n";
    }
    
    if ($paso->condiciones->count() > 0) {
        echo "   Condiciones: " . $paso->condiciones->count() . "\n";
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Motor de Flujos cargado correctamente\n";
echo "═══════════════════════════════════════════════════════════════\n";
