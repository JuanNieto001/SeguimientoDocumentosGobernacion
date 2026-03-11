<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Verificar flujo
$flujo = DB::table('flujos')->where('codigo', 'CD_PJ')->first();
echo "=== FLUJO ===\n";
echo "ID: {$flujo->id} | Código: {$flujo->codigo} | Nombre: {$flujo->nombre}\n";
echo "Versión activa: {$flujo->version_activa_id}\n\n";

// Verificar pasos del flujo
$pasos = DB::table('flujo_pasos')
    ->join('catalogo_pasos', 'catalogo_pasos.id', '=', 'flujo_pasos.catalogo_paso_id')
    ->where('flujo_pasos.flujo_version_id', $flujo->version_activa_id)
    ->orderBy('flujo_pasos.orden')
    ->select('flujo_pasos.*', 'catalogo_pasos.nombre as paso_nombre')
    ->get();
echo "=== PASOS DEL FLUJO ({$pasos->count()}) ===\n";
foreach ($pasos as $p) {
    echo "  Paso {$p->orden}: {$p->paso_nombre} (área: {$p->area_responsable_default})\n";
}

// Verificar workflow legacy
$workflow = DB::table('workflows')->where('codigo', 'CD_PJ')->first();
echo "\n=== WORKFLOW LEGACY ===\n";
echo "ID: {$workflow->id} | Código: {$workflow->codigo}\n\n";

// Verificar etapas
$etapas = DB::table('etapas')->where('workflow_id', $workflow->id)->orderBy('orden')->get();
echo "=== ETAPAS ({$etapas->count()}) ===\n";
foreach ($etapas as $e) {
    $items = DB::table('etapa_items')->where('etapa_id', $e->id)->count();
    $next = $e->next_etapa_id ? "→ etapa_id={$e->next_etapa_id}" : "(FIN)";
    echo "  [{$e->orden}] {$e->nombre} | área: {$e->area_role} | items: {$items} | {$next}\n";
}

// Comparar con CD-PN
$wfPN = DB::table('workflows')->where('codigo', 'CD_PN')->first();
if ($wfPN) {
    $etapasPN = DB::table('etapas')->where('workflow_id', $wfPN->id)->orderBy('orden')->get();
    echo "\n=== COMPARACIÓN CD-PN vs CD-PJ ===\n";
    echo str_pad("Etapa", 6) . str_pad("CD-PN items", 15) . "CD-PJ items\n";
    echo str_repeat("-", 36) . "\n";
    foreach ($etapasPN as $epn) {
        $itemsPN = DB::table('etapa_items')->where('etapa_id', $epn->id)->count();
        $epj = $etapas->firstWhere('orden', $epn->orden);
        $itemsPJ = $epj ? DB::table('etapa_items')->where('etapa_id', $epj->id)->count() : 0;
        $diff = $itemsPJ - $itemsPN;
        $diffStr = $diff > 0 ? "+{$diff}" : ($diff < 0 ? "{$diff}" : "=");
        echo str_pad("  {$epn->orden}", 6) . str_pad("{$itemsPN}", 15) . "{$itemsPJ}  ({$diffStr})\n";
    }
}

// Comparar flujos disponibles
echo "\n=== FLUJOS DISPONIBLES ===\n";
$flujos = DB::table('flujos')->where('activo', 1)->get(['id','codigo','nombre']);
foreach ($flujos as $f) {
    echo "  [{$f->id}] {$f->codigo}: {$f->nombre}\n";
}
