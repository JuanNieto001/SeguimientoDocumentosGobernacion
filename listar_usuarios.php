<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                    USUARIOS DE PRUEBA DEL SISTEMA                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════════════════════════════╝\n\n";

$users = DB::table('users')->orderBy('name')->get();

echo str_pad("NOMBRE", 35) . " | " . str_pad("EMAIL", 40) . " | ROLES\n";
echo str_repeat("-", 120) . "\n";

foreach ($users as $user) {
    $roles = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_id', $user->id)
        ->where('model_type', 'App\Models\User')
        ->pluck('roles.name')
        ->toArray();
    
    $rolesStr = !empty($roles) ? implode(', ', $roles) : '(sin roles)';
    
    echo str_pad($user->name, 35) . " | " . str_pad($user->email, 40) . " | " . $rolesStr . "\n";
}

echo "\n╔════════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  CONTRASEÑA PARA TODOS: password                                                                       ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════════════════════════════╝\n\n";
