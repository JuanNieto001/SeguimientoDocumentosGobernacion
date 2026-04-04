<?php

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;
use Spatie\Permission\Models\Role;

// Crear usuario de prueba para Dashboard Builder
$secretaria = Secretaria::where('sigla', 'PLANEACION')->first();
$unidad = $secretaria ? $secretaria->unidades()->first() : null;

$user = User::create([
    'name' => 'Usuario Dashboard Builder',
    'email' => 'dashboard@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'secretaria_id' => $secretaria?->id,
    'unidad_id' => $unidad?->id
]);

// Asignar rol planeacion (que tiene el permiso dashboard.builder.access)
$role = Role::where('name', 'planeacion')->first();
if ($role) {
    $user->assignRole($role);
    echo "✅ Usuario '{$user->email}' creado con rol '{$role->name}'\n";
    echo "   Secretaría: {$secretaria?->nombre}\n";
    echo "   Unidad: {$unidad?->nombre}\n";
    echo "   Password: password\n";
} else {
    echo "❌ Rol 'planeacion' no encontrado\n";
}