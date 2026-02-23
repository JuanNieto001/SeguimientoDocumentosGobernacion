<?php

/**
 * Script para crear usuarios de las áreas que participan en Etapa 1
 * Ejecutar: php crear_usuarios_etapa1.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== CREANDO USUARIOS PARA ETAPA 1 ===\n\n";

$usuariosACrear = [
    [
        'role' => 'compras',
        'name' => 'Usuario Compras',
        'email' => 'compras@demo.com',
        'descripcion' => 'Sube PAA en Etapa 1'
    ],
    [
        'role' => 'talento_humano',
        'name' => 'Usuario Talento Humano',
        'email' => 'talento_humano@demo.com',
        'descripcion' => 'Sube No Planta y SIGEP en Etapa 1'
    ],
    [
        'role' => 'contabilidad',
        'name' => 'Usuario Contabilidad',
        'email' => 'contabilidad@demo.com',
        'descripcion' => 'Sube Paz y Salvo Contabilidad en Etapa 1'
    ],
    [
        'role' => 'rentas',
        'name' => 'Usuario Rentas',
        'email' => 'rentas@demo.com',
        'descripcion' => 'Sube Paz y Salvo Rentas en Etapa 1'
    ],
    [
        'role' => 'inversiones_publicas',
        'name' => 'Usuario Inversiones Públicas',
        'email' => 'inversiones_publicas@demo.com',
        'descripcion' => 'Sube Compatibilidad del Gasto en Etapa 1'
    ],
    [
        'role' => 'presupuesto',
        'name' => 'Usuario Presupuesto',
        'email' => 'presupuesto@demo.com',
        'descripcion' => 'Sube CDP en Etapa 1 (solo cuando Compatibilidad está subida)'
    ],
];

foreach ($usuariosACrear as $datos) {
    $roleName = $datos['role'];
    $userName = $datos['name'];
    $userEmail = $datos['email'];
    $descripcion = $datos['descripcion'];

    // Verificar que el rol existe
    $role = Role::where('name', $roleName)->first();
    if (!$role) {
        echo "❌ ERROR: El rol '$roleName' no existe en la base de datos\n";
        continue;
    }

    // Verificar si el usuario ya existe
    $userExiste = User::where('email', $userEmail)->first();
    if ($userExiste) {
        echo "⚠️  Usuario '$userEmail' ya existe. Saltando...\n";
        continue;
    }

    // Crear usuario
    $user = User::create([
        'name' => $userName,
        'email' => $userEmail,
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]);

    // Asignar rol
    $user->assignRole($roleName);

    echo "✅ Usuario creado: $userEmail (rol: $roleName)\n";
    echo "   Descripción: $descripcion\n";
    echo "   Password: password123\n\n";
}

echo "\n=== RESUMEN DE USUARIOS PARA ETAPA 1 ===\n\n";
echo "Los siguientes usuarios pueden subir documentos cuando Descentralización los solicite:\n\n";
echo "1. compras@demo.com → PAA\n";
echo "2. talento_humano@demo.com → No Planta, SIGEP\n";
echo "3. contabilidad@demo.com → Paz y Salvo Contabilidad\n";
echo "4. rentas@demo.com → Paz y Salvo Rentas\n";
echo "5. inversiones_publicas@demo.com → Compatibilidad del Gasto\n";
echo "6. presupuesto@demo.com → CDP (bloqueado hasta que suban Compatibilidad)\n\n";
echo "Todos tienen password: password123\n\n";
echo "=== PROCESO DE PRUEBA ===\n\n";
echo "1. Login como sistemas@demo.com\n";
echo "2. Crear proceso, subir Estudios Previos, enviar\n";
echo "3. Login como descentralizacion@demo.com\n";
echo "4. Recibir proceso (auto-crea 7 solicitudes)\n";
echo "5. Login como cada área y subir su documento:\n";
echo "   - compras@demo.com sube PAA\n";
echo "   - talento_humano@demo.com sube No Planta\n";
echo "   - contabilidad@demo.com sube Paz y Salvo Contabilidad\n";
echo "   - rentas@demo.com sube Paz y Salvo Rentas\n";
echo "   - inversiones_publicas@demo.com sube Compatibilidad (desbloquea CDP)\n";
echo "   - presupuesto@demo.com sube CDP\n";
echo "   - talento_humano@demo.com sube SIGEP\n";
echo "6. Login como descentralizacion@demo.com\n";
echo "7. Verificar que muestra 7/7 documentos completos\n";
echo "8. Enviar a siguiente etapa\n\n";
