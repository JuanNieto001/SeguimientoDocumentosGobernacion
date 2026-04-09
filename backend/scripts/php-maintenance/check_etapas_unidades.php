<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== ETAPAS DEL WORKFLOW CD_PN ===\n";
$etapas = DB::table('etapas')
    ->join('workflows','workflows.id','=','etapas.workflow_id')
    ->select('etapas.id','etapas.orden','etapas.nombre','etapas.area_role','etapas.activa')
    ->where('workflows.codigo','CD_PN')
    ->orderBy('etapas.orden')
    ->get();
foreach($etapas as $e) {
    echo "  Etapa {$e->orden} [ID:{$e->id}] - {$e->nombre} → area_role: {$e->area_role}\n";
}

echo "\n=== USUARIOS Y SUS UNIDADES ===\n";
$users = DB::table('users')
    ->select('id','name','email','secretaria_id','unidad_id')
    ->whereNotNull('email')
    ->orderBy('secretaria_id')
    ->get();
foreach($users as $u) {
    $unidad = $u->unidad_id ? DB::table('unidades')->where('id',$u->unidad_id)->value('nombre') : 'NO ASIGNADA';
    $sec = $u->secretaria_id ? DB::table('secretarias')->where('id',$u->secretaria_id)->value('nombre') : 'NO';
    $roles = DB::table('model_has_roles')
        ->join('roles','roles.id','=','model_has_roles.role_id')
        ->where('model_has_roles.model_id',$u->id)
        ->pluck('roles.name')->implode(', ');
    echo "  [{$u->id}] {$u->name} ({$roles}) | sec:{$sec} | unidad: {$unidad}\n";
}
