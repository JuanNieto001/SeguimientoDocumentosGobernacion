<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== ETAPAS DEL WORKFLOW ===\n\n";
$etapas = DB::table('etapas')->orderBy('workflow_id')->orderBy('orden')->get();
foreach($etapas as $e) {
    echo "ID:{$e->id} | WF:{$e->workflow_id} | Orden:{$e->orden} | {$e->nombre} | Role:{$e->area_role} | Next:" . ($e->next_etapa_id ?? 'null') . " | Paralelo:" . ($e->es_paralelo ?? 0) . "\n";
}

echo "\n=== WORKFLOWS ===\n\n";
$workflows = DB::table('workflows')->get();
foreach($workflows as $w) {
    echo "ID:{$w->id} | Codigo:{$w->codigo} | Nombre:{$w->nombre}\n";
}

echo "\n=== PROCESOS ACTIVOS ===\n\n";
$procesos = DB::table('procesos')->where('estado', 'EN_CURSO')->get();
foreach($procesos as $p) {
    $etapa = DB::table('etapas')->where('id', $p->etapa_actual_id)->first();
    echo "ID:{$p->id} | Codigo:{$p->codigo} | Etapa:{$etapa->orden} ({$etapa->nombre}) | Area:{$p->area_actual_role}\n";
}

echo "\n=== ROLES DISPONIBLES ===\n\n";
$roles = DB::table('roles')->get();
foreach($roles as $r) {
    echo "ID:{$r->id} | {$r->name}\n";
}
