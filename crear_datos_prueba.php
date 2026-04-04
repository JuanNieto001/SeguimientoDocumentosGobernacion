<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Creando datos de prueba para Dashboard Builder...\n\n";

// Crear algunos procesos de ejemplo si no existen muchos
$existingCount = App\Models\Proceso::count();
if ($existingCount < 10) {
    $secretarias = App\Models\Secretaria::with('unidades')->limit(3)->get();
    
    $counter = $existingCount + 1;
    foreach ($secretarias as $secretaria) {
        $unidad = $secretaria->unidades->first();
        if (!$unidad) continue;
        
        for ($i = 1; $i <= 3; $i++) {
            $codigo = "DEMO-2026-" . str_pad($counter, 4, '0', STR_PAD_LEFT);
            
            App\Models\Proceso::create([
                'codigo' => $codigo,
                'objeto' => "Proceso de prueba #$counter para {$secretaria->nombre}",
                'descripcion' => "Descripción del proceso de prueba #$counter",
                'tipo_contratacion' => ['cd_pn', 'cd_pj', 'lp'][rand(0, 2)],
                'estado' => ['borrador', 'publicado', 'adjudicado', 'contratado'][rand(0, 3)],
                'valor_estimado' => rand(1000000, 50000000),
                'secretaria_origen_id' => $secretaria->id,
                'unidad_origen_id' => $unidad->id,
                'created_at' => now()->subDays(rand(1, 30))
            ]);
            
            $counter++;
        }
        
        echo "✅ Procesos creados para {$secretaria->nombre}\n";
    }
}

echo "✅ Total procesos en BD: " . App\Models\Proceso::count() . "\n\n";

echo "=== DATOS DISPONIBLES PARA DASHBOARD ===\n";
echo "Procesos: " . App\Models\Proceso::count() . "\n";
echo "Usuarios: " . App\Models\User::count() . "\n";
echo "Secretarías: " . App\Models\Secretaria::count() . "\n";
echo "Unidades: " . App\Models\Unidad::count() . "\n";

echo "\n✅ ¡Dashboard Builder listo para usar!\n";
echo "\nCredenciales de prueba:\n";
echo "- admin@demo.com / Caldas2025* (rol: admin, scope: global)\n";
echo "- test@dashboard.com / password (rol: admin, scope: global)\n";
echo "- planeacion@demo.com / Caldas2025* (rol: planeacion, scope: secretaria)\n";
echo "\nURL: /dashboard/builder\n";