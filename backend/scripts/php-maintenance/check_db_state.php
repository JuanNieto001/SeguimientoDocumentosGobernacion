<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$k = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

echo "=== FLUJOS (motor) ===" . PHP_EOL;
$f = DB::table('flujos')->get();
foreach($f as $r) echo "  {$r->id} | {$r->codigo} | sec_id={$r->secretaria_id}" . PHP_EOL;

echo PHP_EOL . "=== CATALOGO PASOS ===" . PHP_EOL;
echo "  " . DB::table('catalogo_pasos')->count() . " pasos" . PHP_EOL;

echo PHP_EOL . "=== TABLAS CON PROCESOS/SOLICITUDES/CONTRATOS ===" . PHP_EOL;
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $name = array_values((array)$t)[0];
    if (str_contains($name, 'proceso') || str_contains($name, 'solicitud') || str_contains($name, 'contrat')) {
        $cnt = DB::table($name)->count();
        echo "  {$name}: {$cnt} registros" . PHP_EOL;
    }
}

echo PHP_EOL . "=== AUTH TEST (admin) ===" . PHP_EOL;
$admin = DB::table('users')->where('email', 'admin@demo.com')->first();
if ($admin) {
    echo "  id={$admin->id} sec_id=" . ($admin->secretaria_id ?? 'NULL') . PHP_EOL;
}
