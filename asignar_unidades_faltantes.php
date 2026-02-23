<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

// Asignar unidad_id a usuarios que no la tienen, basado en su rol y secretaría

// ID 15 - planeacion@demo.com → Unidad de Descentralización (47)
DB::table('users')->where('id', 15)->update(['unidad_id' => 47]);
echo "✅ Planeacion Demo → Unidad de Descentralización (47)\n";

// ID 7 - Secretario de Planeación → Despacho Secretaría de Planeación (43)
DB::table('users')->where('id', 7)->update(['unidad_id' => 43]);
echo "✅ Secretario de Planeación → Despacho Secretaría de Planeación (43)\n";

// ID 20 - Admin Secretaría Planeación → Despacho Secretaría de Planeación (43)
DB::table('users')->where('id', 20)->update(['unidad_id' => 43]);
echo "✅ Admin Sec Planeación → Despacho Secretaría de Planeación (43)\n";

// ID 19 - Admin Secretaría Hacienda → buscar unidad de hacienda
$unidadHacienda = DB::table('unidades')
    ->join('secretarias','secretarias.id','=','unidades.secretaria_id')
    ->where('secretarias.nombre','like','%Hacienda%')
    ->where('unidades.activo', 1)
    ->first(['unidades.id','unidades.nombre']);
if ($unidadHacienda) {
    DB::table('users')->where('id', 19)->update(['unidad_id' => $unidadHacienda->id]);
    echo "✅ Admin Sec Hacienda → {$unidadHacienda->nombre} ({$unidadHacienda->id})\n";
}

// ID 18 - Admin Secretaría Jurídica → Unidad de Contratación
$unidadJuridica = DB::table('unidades')
    ->join('secretarias','secretarias.id','=','unidades.secretaria_id')
    ->where('secretarias.nombre','like','%Jur%')
    ->where('unidades.nombre','like','%Contrat%')
    ->where('unidades.activo', 1)
    ->first(['unidades.id','unidades.nombre']);
if ($unidadJuridica) {
    DB::table('users')->where('id', 18)->update(['unidad_id' => $unidadJuridica->id]);
    echo "✅ Admin Sec Jurídica → {$unidadJuridica->nombre} ({$unidadJuridica->id})\n";
}

echo "\n=== Verificación final ===\n";
$users = DB::table('users')
    ->join('unidades','unidades.id','=','users.unidad_id')
    ->whereIn('users.id',[7,15,18,19,20])
    ->select('users.id','users.name','unidades.nombre as unidad')
    ->get();
foreach($users as $u) {
    echo "  [{$u->id}] {$u->name} → {$u->unidad}\n";
}
