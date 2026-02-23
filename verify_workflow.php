<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  FLUJO: CONTRATACIÓN DIRECTA - PERSONA NATURAL (CD_PN)           ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$workflow = DB::table('workflows')->where('codigo', 'CD_PN')->first();

if (!$workflow) {
    echo "❌ Workflow CD_PN no encontrado\n";
    exit(1);
}

echo "📋 Workflow: {$workflow->nombre}\n";
echo "   Código: {$workflow->codigo}\n";
echo "   Activo: " . ($workflow->activo ? 'Sí' : 'No') . "\n\n";

$etapas = DB::table('etapas')
    ->where('workflow_id', $workflow->id)
    ->orderBy('orden')
    ->get();

echo "═══════════════════════════════════════════════════════════════════\n";
echo "ETAPAS DEL FLUJO (Total: {$etapas->count()})\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

foreach ($etapas as $etapa) {
    echo "┌─────────────────────────────────────────────────────────────────┐\n";
    echo "│ ETAPA {$etapa->orden}: {$etapa->nombre}\n";
    echo "├─────────────────────────────────────────────────────────────────┤\n";
    echo "│ Responsable: {$etapa->area_role}\n";
    echo "│ Unidad: {$etapa->responsable_unidad}\n";
    echo "│ Secretaría: {$etapa->responsable_secretaria}\n";
    echo "│ Paralelo: " . ($etapa->es_paralelo ? 'Sí' : 'No') . "\n";
    echo "└─────────────────────────────────────────────────────────────────┘\n";
    
    // Items de la etapa
    $items = DB::table('etapa_items')
        ->where('etapa_id', $etapa->id)
        ->orderBy('orden')
        ->get();
    
    if ($items->count() > 0) {
        echo "   📄 Documentos/Items ({$items->count()}):\n";
        foreach ($items as $item) {
            $requerido = $item->requerido ? '✓' : '○';
            echo "      {$requerido} {$item->orden}. {$item->label}\n";
            if ($item->responsable_unidad) {
                echo "         └─ Responsable: {$item->responsable_unidad}\n";
            }
        }
        echo "\n";
    }
}

// Verificar usuarios por rol
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "USUARIOS ASIGNADOS POR ROL\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$rolesEtapas = $etapas->pluck('area_role')->unique();

foreach ($rolesEtapas as $rol) {
    $usuarios = DB::table('users')
        ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('roles.name', $rol)
        ->select('users.email', 'users.name')
        ->get();
    
    echo "👥 Rol: {$rol} ({$usuarios->count()} usuarios)\n";
    foreach ($usuarios as $user) {
        echo "   • {$user->email} ({$user->name})\n";
    }
    echo "\n";
}

echo "\n✅ Verificación completada\n\n";
