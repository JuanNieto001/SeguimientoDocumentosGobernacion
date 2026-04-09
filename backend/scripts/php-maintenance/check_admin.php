<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\User::where('email', 'admin@demo.com')->first();
echo "id={$u->id} sec_id=" . ($u->secretaria_id ?? 'NULL') . " roles=" . implode(',', $u->getRoleNames()->toArray()) . PHP_EOL;

echo "Secretarias activas:" . PHP_EOL;
foreach (\App\Models\Secretaria::where('activo', true)->get() as $s) {
    echo "  {$s->id}: {$s->nombre}" . PHP_EOL;
}

echo PHP_EOL . "Flujos existentes:" . PHP_EOL;
foreach (\App\Models\Flujo::with('secretaria')->get() as $f) {
    echo "  {$f->id}: {$f->codigo} - {$f->nombre} (sec_id={$f->secretaria_id} - " . ($f->secretaria->nombre ?? '?') . ")" . PHP_EOL;
}
