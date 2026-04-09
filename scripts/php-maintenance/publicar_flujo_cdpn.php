<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Flujo;
use App\Models\FlujoVersion;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  PUBLICANDO VERSIÓN DEL FLUJO CD-PN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$flujo = Flujo::where('codigo', 'CD_PN')->first();

if (!$flujo) {
    echo "❌ No se encontró el flujo CD_PN\n";
    exit(1);
}

$version = $flujo->versionActiva;

if (!$version) {
    echo "❌ No hay versión activa\n";
    exit(1);
}

echo "📦 Flujo: {$flujo->nombre}\n";
echo "   Versión actual: v{$version->numero_version}\n";
echo "   Estado actual: " . ($version->es_publicada ? 'Publicada' : 'Borrador') . "\n\n";

// Publicar la versión
$version->update([
    'es_publicada' => true,
    'publicado_por' => 1, // Admin user
    'publicado_at' => now(),
]);

// Actualizar el flujo para que apunte a esta versión publicada
$flujo->update([
    'version_publicada_id' => $version->id,
]);

echo "✅ Versión publicada exitosamente\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ESTADO FINAL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$version->refresh();
$flujo->refresh();

echo "📦 Flujo: {$flujo->nombre}\n";
echo "   Versión: v{$version->numero_version}\n";
echo "   Estado: " . ($version->es_publicada ? '✅ Publicada' : 'Borrador') . "\n";
echo "   Publicada por: {$version->publicado_por}\n";
echo "   Fecha publicación: {$version->publicado_at}\n";
echo "   Total pasos: " . $version->pasos->count() . "\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ ¡Listo! El flujo ahora está visible en el Motor de Flujos\n";
echo "═══════════════════════════════════════════════════════════════\n";
