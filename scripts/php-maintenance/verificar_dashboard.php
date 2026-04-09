<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN DASHBOARD BUILDER ===\n\n";

// Verificar permiso
$permission = Spatie\Permission\Models\Permission::where('name', 'dashboard.builder.access')->first();
if ($permission) {
    echo "✅ Permiso encontrado: {$permission->name}\n\n";
    
    $roles = $permission->roles()->get();
    echo "Roles con este permiso (" . count($roles) . "):\n";
    foreach ($roles as $role) {
        echo "- {$role->name} (scope_level: " . ($role->scope_level ?? 'no definido') . ")\n";
        $users = $role->users()->get(['name', 'email']);
        if (count($users) > 0) {
            foreach ($users as $user) {
                echo "  * {$user->name} ({$user->email})\n";
            }
        } else {
            echo "  (sin usuarios)\n";
        }
    }
} else {
    echo "❌ Permiso dashboard.builder.access no encontrado\n";
}

echo "\n=== DATOS DE PRUEBA ===\n\n";

// Mostrar algunos procesos para verificar que hay datos
$procesoCount = App\Models\Proceso::count();
echo "Procesos en BD: $procesoCount\n";

$userCount = App\Models\User::count();
echo "Usuarios en BD: $userCount\n";

$secretariaCount = App\Models\Secretaria::count();
echo "Secretarías en BD: $secretariaCount\n";

// Crear un usuario de prueba si es necesario
$testUser = App\Models\User::where('email', 'test@dashboard.com')->first();
if (!$testUser) {
    echo "\nCreando usuario de prueba...\n";
    
    $secretaria = App\Models\Secretaria::first();
    $unidad = $secretaria ? $secretaria->unidades()->first() : null;
    
    $testUser = App\Models\User::create([
        'name' => 'Usuario Test Dashboard',
        'email' => 'test@dashboard.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'secretaria_id' => $secretaria?->id,
        'unidad_id' => $unidad?->id
    ]);
    
    $role = Spatie\Permission\Models\Role::where('name', 'admin')->first();
    if ($role) {
        $testUser->assignRole($role);
        echo "✅ Usuario test creado y asignado rol admin\n";
    }
}

echo "\nUsuario de prueba:\n";
echo "Email: {$testUser->email}\n";
echo "Password: password\n";
echo "Tiene permiso: " . ($testUser->hasPermissionTo('dashboard.builder.access') ? 'Sí' : 'No') . "\n";

echo "\n=== RUTAS DISPONIBLES ===\n";
echo "Frontend: /dashboard/builder\n";
echo "API Entities: /api/dashboard-builder/entities\n";
echo "API Query: /api/dashboard-builder/widget/query\n";