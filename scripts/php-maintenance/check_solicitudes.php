<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== SOLICITUDES CREADAS ===\n";
$sols = DB::table('proceso_documentos_solicitados')
    ->where('proceso_id', 1)
    ->get();
if($sols->isEmpty()) {
    echo "  (ninguna aún)\n";
} else {
    foreach($sols as $s) {
        echo "  [{$s->id}] {$s->nombre_documento} | rol:{$s->area_responsable_rol} | estado:{$s->estado}\n";
    }
}

echo "\n=== ROLES EXISTENTES ===\n";
$roles = DB::table('roles')->orderBy('name')->get(['id','name']);
foreach($roles as $r) echo "  [{$r->id}] {$r->name}\n";
