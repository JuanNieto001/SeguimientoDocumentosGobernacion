<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$email = 'juansebastiannieto0@gmail.com';

$existe = DB::table('users')->where('email', $email)->first();
if ($existe) {
    echo "Usuario ya existe: {$existe->name} (ID: {$existe->id})" . PHP_EOL;
} else {
    $id = DB::table('users')->insertGetId([
        'name'       => 'Juan Sebastian Nieto',
        'email'      => $email,
        'password'   => Hash::make('Test1234!'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Usuario creado OK (ID: {$id})" . PHP_EOL;

    // Asignar rol básico
    $rol = DB::table('roles')->where('name', 'unidad_solicitante')->first();
    if ($rol) {
        DB::table('model_has_roles')->insert([
            'role_id'    => $rol->id,
            'model_type' => 'App\\Models\\User',
            'model_id'   => $id,
        ]);
        echo "Rol 'unidad_solicitante' asignado" . PHP_EOL;
    }
}

echo "Email: {$email}" . PHP_EOL;
echo "Contraseña temporal: Test1234!" . PHP_EOL;
