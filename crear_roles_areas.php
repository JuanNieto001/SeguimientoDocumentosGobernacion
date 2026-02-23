<?php
/**
 * Script para crear roles de áreas específicas y asignarlos a usuarios existentes.
 * Esto permite que cada área vea sus solicitudes de documentos.
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║   CREANDO ROLES DE ÁREAS Y ASIGNANDO A USUARIOS              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// 1. Crear roles que faltan
$rolesNuevos = [
    'compras'              => 'Unidad de Compras y Suministros',
    'contabilidad'         => 'Unidad de Contabilidad',
    'rentas'               => 'Unidad de Rentas',
    'inversiones_publicas' => 'Unidad de Regalías e Inversiones Públicas',
    'presupuesto'          => 'Unidad de Presupuesto',
];

foreach ($rolesNuevos as $roleName => $desc) {
    $exists = Role::where('name', $roleName)->first();
    if (!$exists) {
        Role::create(['name' => $roleName, 'guard_name' => 'web']);
        echo "✅ Rol creado: {$roleName} ({$desc})\n";
    } else {
        echo "⏩ Rol ya existe: {$roleName}\n";
    }
}

echo "\n";

// 2. Asignar roles a usuarios existentes (agregar, no reemplazar)
$asignaciones = [
    'secop@demo.com'            => 'compras',         // Coordinador Compras y SECOP
    'contabilidad@demo.com'     => 'contabilidad',     // Analista Contabilidad
    'rentas@demo.com'           => 'rentas',           // Analista Rentas
    'presupuesto@demo.com'      => 'presupuesto',      // Analista Presupuesto
    'regalias@demo.com'         => 'inversiones_publicas', // Analista Regalías e Inversiones
];

foreach ($asignaciones as $email => $role) {
    $user = User::where('email', $email)->first();
    if (!$user) {
        echo "⚠️  Usuario no encontrado: {$email}\n";
        continue;
    }
    
    if ($user->hasRole($role)) {
        echo "⏩ {$user->name} ya tiene el rol '{$role}'\n";
    } else {
        $user->assignRole($role);
        echo "✅ Rol '{$role}' asignado a {$user->name} ({$email})\n";
    }
}

echo "\n";

// 3. Verificar resultado
echo "=== VERIFICACIÓN DE USUARIOS CON ROLES DE DOCUMENTOS ===\n\n";
$rolesVerificar = ['compras', 'contabilidad', 'rentas', 'inversiones_publicas', 'presupuesto', 'talento_humano'];

foreach ($rolesVerificar as $role) {
    $users = User::role($role)->get();
    echo "📋 {$role}:\n";
    foreach ($users as $u) {
        $allRoles = $u->getRoleNames()->implode(', ');
        echo "   - {$u->name} ({$u->email}) → Roles: {$allRoles}\n";
    }
    if ($users->isEmpty()) {
        echo "   ⚠️  Sin usuarios asignados\n";
    }
    echo "\n";
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   ✅ PROCESO COMPLETADO                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
