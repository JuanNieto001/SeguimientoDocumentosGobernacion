<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== ACTUALIZANDO ROLES DE USUARIOS EXISTENTES ===\n\n";

$usuariosActualizar = [
    'contabilidad@demo.com' => 'contabilidad',
    'rentas@demo.com' => 'rentas',
    'presupuesto@demo.com' => 'presupuesto',
];

foreach ($usuariosActualizar as $email => $roleName) {
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Usuario '$email' no existe\n";
        continue;
    }

    $role = Role::where('name', $roleName)->first();
    if (!$role) {
        echo "❌ Rol '$roleName' no existe\n";
        continue;
    }

    // Remover roles anteriores y asignar el nuevo
    $user->syncRoles([$roleName]);
    
    echo "✅ Usuario '$email' actualizado con rol '$roleName'\n";
}

echo "\n=== ACTUALIZACIÓN COMPLETA ===\n\n";
