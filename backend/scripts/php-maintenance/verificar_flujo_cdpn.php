<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Workflow;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  VERIFICACIÓN FLUJO CD-PN (Contratación Directa Persona Natural)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$workflow = Workflow::where('codigo', 'CD_PN')->first();

if (!$workflow) {
    echo "❌ No se encontró el workflow CD_PN\n";
    exit(1);
}

echo "✅ Workflow: {$workflow->nombre}\n";
echo "   Código: {$workflow->codigo}\n";
if ($workflow->secretaria) {
    echo "   Secretaría: {$workflow->secretaria->nombre}\n";
}
echo "   Total Etapas: " . $workflow->etapas->count() . "\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ETAPAS DEL FLUJO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

foreach ($workflow->etapas as $etapa) {
    echo "📋 Etapa {$etapa->orden}: {$etapa->nombre}\n";
    echo "   Responsable: {$etapa->responsable_unidad} ({$etapa->responsable_secretaria})\n";
    echo "   Items: " . $etapa->items->count() . "\n";
    
    if ($etapa->es_paralelo) {
        echo "   🔄 PARALELO\n";
    }
    
    if ($etapa->items->count() > 0) {
        foreach ($etapa->items as $item) {
            echo "      • {$item->nombre}";
            if ($item->es_requerido) {
                echo " ⚠️ REQUERIDO";
            }
            if ($item->tipo_documento) {
                echo " [{$item->tipo_documento}]";
            }
            echo "\n";
        }
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Flujo cargado correctamente en la base de datos\n";
echo "═══════════════════════════════════════════════════════════════\n";
