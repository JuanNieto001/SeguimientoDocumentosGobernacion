<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICACION FLUJOS ===\n";
$flujos = \App\Models\Flujo::all();
echo "Flujos encontrados: " . $flujos->count() . "\n\n";

foreach($flujos as $flujo) {
    echo "ID: $flujo->id\n";
    echo "Nombre: $flujo->nombre\n";
    echo "Descripcion: $flujo->descripcion\n";
    echo "Estado: $flujo->estado\n";
    echo "Secretaria: $flujo->secretaria_id\n";
    echo "---\n";
}

// Verificar específicamente por nombre
$cdpn = \App\Models\Flujo::where('nombre', 'LIKE', '%Persona Natural%')->first();
$cdpj = \App\Models\Flujo::where('nombre', 'LIKE', '%Persona Jurídica%')->orWhere('nombre', 'LIKE', '%Persona Juridica%')->first();

echo "\n=== VERIFICACION FLUJOS REQUERIDOS ===\n";
echo "CD-PN (Persona Natural): " . ($cdpn ? "✅ EXISTE (ID: $cdpn->id)" : "❌ FALTA") . "\n";
echo "CD-PJ (Persona Jurídica): " . ($cdpj ? "✅ EXISTE (ID: $cdpj->id)" : "❌ FALTA") . "\n";