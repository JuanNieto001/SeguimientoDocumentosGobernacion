<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;

echo "=== CREANDO ROLES FALTANTES PARA ETAPA 1 ===\n\n";

$rolesFaltantes = [
    'compras' => 'Unidad de Compras - Sube PAA',
    'contabilidad' => 'Contabilidad - Sube Paz y Salvo Contabilidad',
    'rentas' => 'Rentas - Sube Paz y Salvo Rentas',
    'inversiones_publicas' => 'Inversiones Públicas - Sube Compatibilidad del Gasto',
    'presupuesto' => 'Presupuesto - Sube CDP',
];

foreach ($rolesFaltantes as $roleName => $descripcion) {
    $roleExiste = Role::where('name', $roleName)->first();
    
    if ($roleExiste) {
        echo "⚠️  Rol '$roleName' ya existe\n";
        continue;
    }

    Role::create(['name' => $roleName]);
    echo "✅ Rol creado: $roleName - $descripcion\n";
}

echo "\n=== ROLES CREADOS EXITOSAMENTE ===\n\n";
