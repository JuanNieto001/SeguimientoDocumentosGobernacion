<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN DASHBOARD SYSTEM - ADMIN ONLY ===\n\n";

// Verificar permisos
$builderPermission = Spatie\Permission\Models\Permission::where('name', 'dashboard.builder.access')->first();
$assignPermission = Spatie\Permission\Models\Permission::where('name', 'dashboard.assign')->first();
$viewPermission = Spatie\Permission\Models\Permission::where('name', 'dashboard.view.assigned')->first();

echo "PERMISOS:\n";
echo "✅ dashboard.builder.access: " . ($builderPermission ? 'Existe' : 'NO EXISTE') . "\n";
echo "✅ dashboard.assign: " . ($assignPermission ? 'Existe' : 'NO EXISTE') . "\n";
echo "✅ dashboard.view.assigned: " . ($viewPermission ? 'Existe' : 'NO EXISTE') . "\n\n";

// Verificar roles con acceso al builder
if ($builderPermission) {
    $builderRoles = $builderPermission->roles()->get();
    echo "ROLES CON ACCESO AL BUILDER (" . count($builderRoles) . "):\n";
    foreach ($builderRoles as $role) {
        $userCount = $role->users()->count();
        echo "- {$role->name}: $userCount usuario(s)\n";
    }
    echo "\n";
}

// Verificar roles que pueden ver dashboards asignados
if ($viewPermission) {
    $viewRoles = $viewPermission->roles()->get();
    echo "ROLES QUE PUEDEN VER DASHBOARDS ASIGNADOS (" . count($viewRoles) . "):\n";
    foreach ($viewRoles as $role) {
        $userCount = $role->users()->count();
        echo "- {$role->name}: $userCount usuario(s)\n";
    }
    echo "\n";
}

// Verificar tablas de asignación
$dashboardsTable = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='dashboards'");
$assignmentsTable = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='dashboard_assignments'");

echo "TABLAS DE BASE DE DATOS:\n";
echo "✅ dashboards: " . (count($dashboardsTable) > 0 ? 'Existe' : 'NO EXISTE') . "\n";
echo "✅ dashboard_assignments: " . (count($assignmentsTable) > 0 ? 'Existe' : 'NO EXISTE') . "\n";
echo "✅ dashboard_role_assignments: " . (count(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='dashboard_role_assignments'")) > 0 ? 'Existe' : 'NO EXISTE') . "\n";
echo "✅ dashboard_secretaria_assignments: " . (count(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='dashboard_secretaria_assignments'")) > 0 ? 'Existe' : 'NO EXISTE') . "\n\n";

// Crear un dashboard de ejemplo si no existe
$dashboardCount = App\Models\Dashboard::count();
if ($dashboardCount == 0) {
    echo "CREANDO DASHBOARD DE EJEMPLO...\n";
    
    $adminUser = App\Models\User::where('email', 'test@dashboard.com')->first();
    if ($adminUser) {
        $dashboard = App\Models\Dashboard::create([
            'name' => 'Dashboard de Procesos',
            'description' => 'Vista general de procesos contractuales',
            'widgets' => [
                [
                    'id' => 'widget-1',
                    'type' => 'bar',
                    'title' => 'Procesos por Estado',
                    'entity' => 'procesos',
                    'x' => 0, 'y' => 0, 'w' => 6, 'h' => 4,
                    'config' => [
                        'aggregation' => ['type' => 'count'],
                        'groupBy' => ['estado'],
                        'filters' => [],
                        'limit' => 100
                    ]
                ],
                [
                    'id' => 'widget-2',
                    'type' => 'metric',
                    'title' => 'Total Procesos',
                    'entity' => 'procesos',
                    'x' => 6, 'y' => 0, 'w' => 3, 'h' => 2,
                    'config' => [
                        'aggregation' => ['type' => 'count'],
                        'groupBy' => [],
                        'filters' => [],
                        'limit' => 1
                    ]
                ]
            ],
            'created_by' => $adminUser->id
        ]);
        
        echo "✅ Dashboard creado: {$dashboard->name} (ID: {$dashboard->id})\n";
        
        // Asignar a rol planeacion
        App\Models\DashboardRoleAssignment::create([
            'dashboard_id' => $dashboard->id,
            'role_name' => 'planeacion',
            'assigned_by' => $adminUser->id,
            'assigned_at' => now()
        ]);
        
        echo "✅ Dashboard asignado al rol 'planeacion'\n";
    }
}

echo "\n=== RESUMEN SISTEMA ===\n";
echo "Dashboards creados: " . App\Models\Dashboard::count() . "\n";
echo "Asignaciones por usuario: " . App\Models\DashboardAssignment::count() . "\n";
echo "Asignaciones por rol: " . App\Models\DashboardRoleAssignment::count() . "\n";
echo "Asignaciones por secretaría: " . App\Models\DashboardSecretariaAssignment::count() . "\n";

echo "\n=== USUARIOS DE PRUEBA ===\n";
echo "ADMIN (puede crear/editar/asignar):\n";
echo "- test@dashboard.com / password\n";
echo "- admin@demo.com / Caldas2025*\n\n";

echo "USUARIOS (pueden ver dashboards asignados):\n";
echo "- planeacion@demo.com / Caldas2025*\n";
echo "- descentralizacion@demo.com / Caldas2025*\n";

echo "\n=== RUTAS DISPONIBLES ===\n";
echo "Builder (Admin): /dashboard/builder\n";
echo "Viewer (Usuarios): /dashboard\n";
echo "API Builder: /api/dashboard-builder/*\n";
echo "API Viewer: /api/dashboard-viewer/*\n";

echo "\n✅ SISTEMA CONFIGURADO - ADMIN ASIGNA DASHBOARDS A USUARIOS\n";