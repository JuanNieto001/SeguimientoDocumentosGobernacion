<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "\n=== USUARIOS DEL SISTEMA ===\n\n";

$users = User::with('roles', 'secretaria', 'unidad')->orderBy('email')->get();

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    $secretaria = $user->secretaria ? $user->secretaria->nombre : 'Sin secretaría';
    $unidad = $user->unidad ? $user->unidad->nombre : 'Sin unidad';
    
    echo "📧 {$user->email}\n";
    echo "   Nombre: {$user->name}\n";
    echo "   Rol: {$roles}\n";
    echo "   Secretaría: {$secretaria}\n";
    echo "   Unidad: {$unidad}\n";
    echo "\n";
}

echo "Total de usuarios: " . $users->count() . "\n";
