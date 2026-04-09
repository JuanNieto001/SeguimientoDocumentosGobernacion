<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'planeacion@gobernacioncaldas.gov.co')->first();
if ($user) {
    $hasPermission = $user->hasPermissionTo('dashboard.builder.access');
    $role = $user->roles()->first();
    echo "Usuario: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Rol: " . ($role ? $role->name : 'Sin rol') . "\n";
    echo "Permiso dashboard.builder.access: " . ($hasPermission ? 'Sí' : 'No') . "\n";
    echo "Secretaría ID: {$user->secretaria_id}\n";
    echo "Unidad ID: {$user->unidad_id}\n";
} else {
    echo "Usuario de planeación no encontrado\n";
}