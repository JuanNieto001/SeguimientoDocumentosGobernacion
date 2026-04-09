<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Ver usuario planeacion
$u = DB::table('users')->where('email','planeacion@demo.com')->first(['id','name','email','secretaria_id','unidad_id']);
echo "=== Usuario planeacion ===\n";
echo "ID: {$u->id} | Secretaria ID: {$u->secretaria_id} | Unidad ID: {$u->unidad_id}\n\n";

// Ver todas las secretarias
echo "=== Secretarias ===\n";
$secretarias = DB::table('secretarias')->where('activo',1)->get(['id','nombre']);
foreach($secretarias as $s) {
    echo "  [{$s->id}] {$s->nombre}\n";
}

echo "\n=== Unidades de Planeación ===\n";
$sec = DB::table('secretarias')->where('nombre','like','%Planeaci%')->first();
if($sec) {
    echo "Secretaria: [{$sec->id}] {$sec->nombre}\n";
    $unis = DB::table('unidades')->where('secretaria_id',$sec->id)->where('activo',1)->get(['id','nombre']);
    foreach($unis as $u2) {
        echo "  [{$u2->id}] {$u2->nombre}\n";
    }
}
