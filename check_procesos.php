<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== TODOS LOS PROCESOS ===\n";
$procesos = DB::table('procesos')
    ->leftJoin('users','users.id','=','procesos.created_by')
    ->leftJoin('etapas','etapas.id','=','procesos.etapa_actual_id')
    ->select('procesos.id','procesos.codigo','procesos.estado','procesos.area_actual_role',
             'etapas.nombre as etapa_nombre','etapas.orden',
             'users.name as creado_por')
    ->orderByDesc('procesos.id')
    ->get();

foreach($procesos as $p) {
    echo "  [{$p->id}] {$p->codigo} | Estado:{$p->estado} | area:{$p->area_actual_role} | Etapa:{$p->orden}-{$p->etapa_nombre} | Creó:{$p->creado_por}\n";
}

echo "\n=== PROCESOS EN COLA PLANEACIÓN ===\n";
$enPlaneacion = DB::table('procesos')
    ->where('area_actual_role','planeacion')
    ->where('estado','EN_CURSO')
    ->get(['id','codigo','estado']);
if($enPlaneacion->isEmpty()) {
    echo "  (ninguno - planeacion verá bandeja vacía)\n";
} else {
    foreach($enPlaneacion as $p) echo "  [{$p->id}] {$p->codigo}\n";
}
