<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

// Resetear CD_PN-2026-0001 a Etapa 1 (Planeación) para probar el flujo paralelo

$procesoId = 1; // CD_PN-2026-0001
$etapa1Id  = 2; // Etapa 1: Solicitud de Documentos Iniciales (planeacion)

DB::transaction(function() use ($procesoId, $etapa1Id) {
    // 1. Limpiar solicitudes de documentos anteriores (si las hay)
    DB::table('proceso_documentos_solicitados')
        ->where('proceso_id', $procesoId)
        ->delete();
    echo "✅ Solicitudes de documentos limpiadas\n";

    // 2. Desmarcar la etapa 1 como "enviada"
    DB::table('proceso_etapas')
        ->where('proceso_id', $procesoId)
        ->where('etapa_id', $etapa1Id)
        ->update([
            'enviado' => false,
            'enviado_por' => null,
            'enviado_at' => null,
            'updated_at' => now(),
        ]);
    echo "✅ Etapa 1 desmarcada como enviada\n";

    // 3. Verificar que exista proceso_etapa para etapa 1
    $pe = DB::table('proceso_etapas')
        ->where('proceso_id', $procesoId)
        ->where('etapa_id', $etapa1Id)
        ->first();

    if (!$pe) {
        // Crear la instancia si no existe
        DB::table('proceso_etapas')->insert([
            'proceso_id' => $procesoId,
            'etapa_id'   => $etapa1Id,
            'recibido'   => true,
            'recibido_por' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✅ proceso_etapa para etapa 1 creada\n";
    } else {
        DB::table('proceso_etapas')->where('id', $pe->id)->update(['recibido' => true]);
    }

    // 4. Actualizar el proceso a etapa 1 y area planeacion
    DB::table('procesos')->where('id', $procesoId)->update([
        'etapa_actual_id' => $etapa1Id,
        'area_actual_role' => 'planeacion',
        'estado' => 'EN_CURSO',
        'updated_at' => now(),
    ]);
    echo "✅ Proceso movido a Etapa 1 (planeacion)\n";
});

// Verificar resultado
$p = DB::table('procesos')
    ->join('etapas','etapas.id','=','procesos.etapa_actual_id')
    ->where('procesos.id', $procesoId)
    ->first(['procesos.codigo','procesos.estado','procesos.area_actual_role','etapas.nombre as etapa']);

echo "\n=== Estado final ===\n";
echo "  {$p->codigo} | area: {$p->area_actual_role} | etapa: {$p->etapa} | estado: {$p->estado}\n";
echo "\n✅ ¡Listo! Ahora entra como planeacion@demo.com y verás el proceso.\n";
