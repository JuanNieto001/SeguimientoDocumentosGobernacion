<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$doc = DB::table('proceso_etapa_archivos')
    ->where('tipo_archivo', 'estudios_previos')
    ->first();

if ($doc) {
    echo "✅ Documento encontrado:\n";
    echo "   ID: {$doc->id}\n";
    echo "   Nombre: {$doc->nombre_original}\n";
    echo "   Proceso ID: {$doc->proceso_id}\n";
} else {
    echo "❌ No se encontró documento de estudios previos\n";
}
