<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = DB::table('users')->where('email', 'jefe.sistemas@demo.com')->first();

if (!$user) {
    echo "Usuario no encontrado\n";
    exit(1);
}

$roleId = DB::table('roles')->where('name', 'planeacion')->value('id');

if (!$roleId) {
    echo "Rol 'planeacion' no encontrado\n";
    exit(1);
}

// Asignar rol
DB::table('model_has_roles')->updateOrInsert(
    [
        'role_id' => $roleId,
        'model_type' => 'App\Models\User',
        'model_id' => $user->id
    ]
);

echo "✅ Rol 'planeacion' asignado correctamente a jefe.sistemas@demo.com\n";
