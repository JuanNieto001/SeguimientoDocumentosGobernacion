<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== ROLES ===\n";
$roles = DB::table('roles')->get();
foreach($roles as $r) echo $r->id.': '.$r->name.PHP_EOL;

echo "\n=== USUARIOS CON ROLES ===\n";
$users = DB::table('users')
    ->join('model_has_roles','model_has_roles.model_id','=','users.id')
    ->join('roles','roles.id','=','model_has_roles.role_id')
    ->where('model_has_roles.model_type','App\\Models\\User')
    ->select('users.name','users.email','roles.name as role')
    ->orderBy('roles.name')
    ->get();
foreach($users as $u) echo $u->role.' -> '.$u->name.' ('.$u->email.')'.PHP_EOL;

echo "\n=== SECRETARIAS ===\n";
$secs = DB::table('secretarias')->get();
foreach($secs as $s) echo $s->id.': '.$s->nombre.PHP_EOL;

echo "\n=== UNIDADES ===\n";
$uns = DB::table('unidades')->get();
foreach($uns as $u) echo $u->id.': '.$u->nombre.' (sec:'.$u->secretaria_id.')'.PHP_EOL;
