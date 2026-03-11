<?php
/**
 * Script de prueba del sistema de notificaciones para el flujo CD-PN.
 * Sigue el mismo flujo que test_flujo_cdpn.php pero también verifica
 * que las alertas se creen correctamente en cada transición.
 *
 * Ejecutar: php test_notificaciones_cdpn.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->handleRequest(\Illuminate\Http\Request::capture());

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

use App\Enums\EstadoProcesoCD;
use App\Models\Alerta;
use App\Models\ProcesoContratacionDirecta;
use App\Models\ProcesoCDDocumento;
use App\Models\User;
use App\Services\ContratoDirectoPNStateMachine;

$sm = app(ContratoDirectoPNStateMachine::class);

$admin     = User::find(1);
$unidad    = User::find(3);
$planeacion= User::find(5);
$hacienda  = User::find(8);
$juridica  = User::find(11);

$errores = [];
$ok = 0;

function paso($num, $desc) { echo "\n[Paso {$num}] {$desc}\n"; }
function exito($msg) { global $ok; $ok++; echo "  ✓ {$msg}\n"; }
function falla($msg) { global $errores; $errores[] = $msg; echo "  ✗ {$msg}\n"; }
function info($msg) { echo "  → {$msg}\n"; }

function contarAlertas($procesoId) {
    return Alerta::where('proceso_cd_id', $procesoId)->count();
}

function verificarNotificacion($procesoId, $area, $estadoEsperado, $desc) {
    $alertas = Alerta::where('proceso_cd_id', $procesoId)
        ->where('area_responsable', $area)
        ->whereJsonContains('metadata->estado_nuevo', $estadoEsperado)
        ->get();
    
    if ($alertas->count() > 0) {
        exito("{$desc}: {$alertas->count()} alerta(s) → área '{$area}'");
        foreach ($alertas as $a) {
            $u = User::find($a->user_id);
            info("  Destinatario: {$u->name} (ID:{$u->id})");
        }
        return true;
    } else {
        falla("{$desc}: Sin alertas para área '{$area}' con estado '{$estadoEsperado}'");
        return false;
    }
}

echo "═══════════════════════════════════════════════════\n";
echo " PRUEBA: NOTIFICACIONES CD-PN\n";
echo "═══════════════════════════════════════════════════\n";

// ──────────────────────────────────────────────────
// PASO 1: Crear proceso (auto-transición a EN_VALIDACION_PLANEACION)
// ──────────────────────────────────────────────────
paso(1, 'Crear proceso → auto-transiciona a EN_VALIDACION_PLANEACION');

$secretaria = \App\Models\Secretaria::first();
$unidadOrg  = \App\Models\Unidad::first();

try {
    auth()->login($unidad);
    $proceso = $sm->crearSolicitud([
        'objeto'         => 'TEST NOTIF - ' . date('Y-m-d H:i:s'),
        'valor'          => 15000000,
        'plazo_meses'    => 6,
        'estudio_previo_path' => 'test/dummy_notif_test.pdf',
        'secretaria_id'  => $secretaria->id,
        'unidad_id'      => $unidadOrg->id,
        'contratista_nombre'         => 'Juan Prueba Notif',
        'contratista_tipo_documento' => 'CC',
        'contratista_documento'      => '9876543210',
        'contratista_email'          => 'juan.notif@test.com',
    ], $unidad);
    
    $procesoId = $proceso->id;
    info("Proceso: {$proceso->codigo} (ID: {$procesoId})");
    exito('Proceso creado y enviado a Planeación');
    
    // Verificar notificación EN_VALIDACION_PLANEACION
    verificarNotificacion($procesoId, 'planeacion', 'en_validacion_planeacion', 'Notificación a Planeación');
} catch (\Throwable $e) {
    falla("Error: " . $e->getMessage());
    die("\nNo se puede continuar.\n");
}

// ──────────────────────────────────────────────────
// PASO 2: Validaciones paralelas + COMPATIBILIDAD + CDP
// ──────────────────────────────────────────────────
paso(2, 'Etapa 2: Validaciones → CDP_SOLICITADO → CDP_APROBADO');

try {
    auth()->login($planeacion);
    $campos = ['paa_solicitado', 'certificado_no_planta', 'paz_salvo_rentas', 'paz_salvo_contabilidad', 'compatibilidad_gasto'];
    foreach ($campos as $campo) {
        $proceso = $sm->registrarValidacionParalela($proceso, $campo, $planeacion);
    }
    info("Validaciones paralelas completadas");

    // Docs etapa 2
    foreach (['paa', 'no_planta', 'paz_salvo_rentas', 'paz_salvo_contabilidad', 'compatibilidad_gasto'] as $tipo) {
        ProcesoCDDocumento::create([
            'proceso_cd_id' => $procesoId, 'tipo_documento' => $tipo,
            'nombre_archivo' => "{$tipo}.pdf", 'ruta_archivo' => "test/{$tipo}.pdf",
            'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
            'etapa' => 2, 'estado_aprobacion' => 'aprobado', 'subido_por' => $planeacion->id,
        ]);
    }

    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::COMPATIBILIDAD_APROBADA, $planeacion, 'Compatibilidad OK');
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::CDP_SOLICITADO, $planeacion, 'CDP solicitado');
    verificarNotificacion($procesoId, 'hacienda', 'cdp_solicitado', 'CDP_SOLICITADO → Hacienda');

    // CDP doc
    ProcesoCDDocumento::create([
        'proceso_cd_id' => $procesoId, 'tipo_documento' => 'cdp',
        'nombre_archivo' => 'cdp.pdf', 'ruta_archivo' => 'test/cdp.pdf',
        'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
        'etapa' => 2, 'estado_aprobacion' => 'aprobado', 'subido_por' => $hacienda->id,
    ]);

    auth()->login($hacienda);
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::CDP_APROBADO, $hacienda, 'CDP aprobado', [
        'numero_cdp' => 'CDP-NOTIF-001', 'valor_cdp' => 15000000,
    ]);
    verificarNotificacion($procesoId, 'unidad_solicitante', 'cdp_aprobado', 'CDP_APROBADO → Unidad Solicitante');
    exito('Etapa 2 completada con notificaciones');
} catch (\Throwable $e) {
    falla("Etapa 2: " . $e->getMessage());
}

// ──────────────────────────────────────────────────
// PASO 3: Documentación contratista
// ──────────────────────────────────────────────────
paso(3, 'Etapa 3: Documentación → EN_REVISION_JURIDICA');

try {
    auth()->login($unidad);
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::DOCUMENTACION_INCOMPLETA, $unidad, 'Inicio docs');
    
    // Docs etapa 3
    foreach (['hoja_vida_sigep', 'cedula', 'rut', 'antecedentes_disciplinarios', 'antecedentes_fiscales',
              'antecedentes_judiciales', 'seguridad_social_salud', 'seguridad_social_pension', 'certificado_cuenta_bancaria'] as $tipo) {
        ProcesoCDDocumento::create([
            'proceso_cd_id' => $procesoId, 'tipo_documento' => $tipo,
            'nombre_archivo' => "{$tipo}.pdf", 'ruta_archivo' => "test/{$tipo}.pdf",
            'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
            'etapa' => 3, 'estado_aprobacion' => 'aprobado', 'subido_por' => $unidad->id,
        ]);
    }
    $proceso->update(['hoja_vida_cargada' => true, 'checklist_validado' => true]);

    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::DOCUMENTACION_VALIDADA, $unidad, 'Docs validados');
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::EN_REVISION_JURIDICA, $unidad, 'Enviado a jurídica');
    verificarNotificacion($procesoId, 'juridica', 'en_revision_juridica', 'EN_REVISION_JURIDICA → Jurídica');
    exito('Etapa 3 completada con notificaciones');
} catch (\Throwable $e) {
    falla("Etapa 3: " . $e->getMessage());
}

// ──────────────────────────────────────────────────
// PASO 4: Revisión Jurídica → Contrato generado
// ──────────────────────────────────────────────────
paso(4, 'Etapa 4: Jurídica → CONTRATO_GENERADO');

try {
    auth()->login($juridica);
    ProcesoCDDocumento::create([
        'proceso_cd_id' => $procesoId, 'tipo_documento' => 'checklist_juridica',
        'nombre_archivo' => 'checklist_jur.pdf', 'ruta_archivo' => 'test/checklist_jur.pdf',
        'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
        'etapa' => 4, 'estado_aprobacion' => 'aprobado', 'subido_por' => $juridica->id,
    ]);
    $proceso->update(['numero_proceso_juridica' => 'CD-NOTIF-TEST']);

    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::PROCESO_NUMERO_GENERADO, $juridica, 'Número asignado', ['numero_proceso' => 'CD-NOTIF-TEST']);
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::GENERACION_CONTRATO, $juridica, 'Generando contrato');

    ProcesoCDDocumento::create([
        'proceso_cd_id' => $procesoId, 'tipo_documento' => 'contrato_electronico',
        'nombre_archivo' => 'contrato.pdf', 'ruta_archivo' => 'test/contrato.pdf',
        'mime_type' => 'application/pdf', 'tamano_bytes' => 2048,
        'etapa' => 4, 'estado_aprobacion' => 'aprobado', 'subido_por' => $juridica->id,
    ]);

    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::CONTRATO_GENERADO, $juridica, 'Contrato generado');
    verificarNotificacion($procesoId, 'unidad_solicitante', 'contrato_generado', 'CONTRATO_GENERADO → Unidad');
    exito('Etapa 4 completada con notificaciones');
} catch (\Throwable $e) {
    falla("Etapa 4: " . $e->getMessage());
}

// ──────────────────────────────────────────────────
// PASO 5: Firmas
// ──────────────────────────────────────────────────
paso(5, 'Etapa 5: Firmas → CONTRATO_FIRMADO_TOTAL');

try {
    $proceso = $sm->registrarFirma($proceso, 'contratista', $unidad);
    verificarNotificacion($procesoId, 'unidad_solicitante', 'contrato_firmado_parcial', 'FIRMADO_PARCIAL → Unidad');

    ProcesoCDDocumento::create([
        'proceso_cd_id' => $procesoId, 'tipo_documento' => 'contrato_firmado',
        'nombre_archivo' => 'contrato_firm.pdf', 'ruta_archivo' => 'test/contrato_firm.pdf',
        'mime_type' => 'application/pdf', 'tamano_bytes' => 2048,
        'etapa' => 5, 'estado_aprobacion' => 'aprobado', 'subido_por' => $juridica->id,
    ]);

    $proceso = $sm->registrarFirma($proceso, 'ordenador_gasto', $juridica);
    verificarNotificacion($procesoId, 'planeacion', 'contrato_firmado_total', 'FIRMADO_TOTAL → Planeación');
    exito('Etapa 5 completada con notificaciones');
} catch (\Throwable $e) {
    falla("Etapa 5: " . $e->getMessage());
}

// ──────────────────────────────────────────────────
// PASO 6: RPC y Radicación
// ──────────────────────────────────────────────────
paso(6, 'Etapa 6: RPC → EXPEDIENTE_RADICADO');

try {
    auth()->login($planeacion);
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::RPC_SOLICITADO, $planeacion, 'RPC solicitado');
    verificarNotificacion($procesoId, 'hacienda', 'rpc_solicitado', 'RPC_SOLICITADO → Hacienda');

    foreach (['solicitud_rpc', 'rpc'] as $tipo) {
        ProcesoCDDocumento::create([
            'proceso_cd_id' => $procesoId, 'tipo_documento' => $tipo,
            'nombre_archivo' => "{$tipo}.pdf", 'ruta_archivo' => "test/{$tipo}.pdf",
            'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
            'etapa' => 6, 'estado_aprobacion' => 'aprobado',
            'subido_por' => $tipo === 'rpc' ? $hacienda->id : $planeacion->id,
        ]);
    }
    $proceso->update(['numero_rpc' => 'RPC-NOTIF-001']);

    auth()->login($hacienda);
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::RPC_FIRMADO, $hacienda, 'RPC firmado', ['numero_rpc' => 'RPC-NOTIF-001']);

    ProcesoCDDocumento::create([
        'proceso_cd_id' => $procesoId, 'tipo_documento' => 'expediente_fisico_final',
        'nombre_archivo' => 'expediente.pdf', 'ruta_archivo' => 'test/expediente.pdf',
        'mime_type' => 'application/pdf', 'tamano_bytes' => 3072,
        'etapa' => 6, 'estado_aprobacion' => 'aprobado', 'subido_por' => $planeacion->id,
    ]);

    auth()->login($planeacion);
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::EXPEDIENTE_RADICADO, $planeacion, 'Radicado');
    verificarNotificacion($procesoId, 'unidad_solicitante', 'expediente_radicado', 'EXPEDIENTE_RADICADO → Unidad');
    exito('Etapa 6 completada con notificaciones');
} catch (\Throwable $e) {
    falla("Etapa 6: " . $e->getMessage());
}

// ──────────────────────────────────────────────────
// PASO 7: Ejecución
// ──────────────────────────────────────────────────
paso(7, 'Etapa 7: EN_EJECUCION → notifica a todos');

try {
    auth()->login($unidad);
    foreach (['solicitud_arl', 'acta_inicio'] as $tipo) {
        ProcesoCDDocumento::create([
            'proceso_cd_id' => $procesoId, 'tipo_documento' => $tipo,
            'nombre_archivo' => "{$tipo}.pdf", 'ruta_archivo' => "test/{$tipo}.pdf",
            'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
            'etapa' => 7, 'estado_aprobacion' => 'aprobado', 'subido_por' => $unidad->id,
        ]);
    }
    $proceso->update(['numero_contrato' => 'CONT-NOTIF-001', 'arl_solicitada' => true, 'acta_inicio_firmada' => true]);

    $antes = contarAlertas($procesoId);
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::EN_EJECUCION, $unidad, 'En ejecución', [
        'numero_contrato' => 'CONT-NOTIF-001', 'fecha_inicio' => now()->format('Y-m-d'),
    ]);
    $despues = contarAlertas($procesoId);
    info("Alertas EN_EJECUCION: " . ($despues - $antes) . " (notifica a todas las áreas)");
    exito('Etapa 7 completada - flujo terminado');
} catch (\Throwable $e) {
    falla("Etapa 7: " . $e->getMessage());
}

// ──────────────────────────────────────────────────
// RESUMEN
// ──────────────────────────────────────────────────
paso(8, 'RESUMEN DE NOTIFICACIONES');

$totalAlertas = Alerta::where('proceso_cd_id', $procesoId)->count();
info("Total alertas generadas: {$totalAlertas}");

$porTipo = Alerta::where('proceso_cd_id', $procesoId)
    ->selectRaw('tipo, COUNT(*) as total')
    ->groupBy('tipo')->pluck('total', 'tipo');
foreach ($porTipo as $tipo => $total) info("  Tipo {$tipo}: {$total}");

$porArea = Alerta::where('proceso_cd_id', $procesoId)
    ->selectRaw('area_responsable, COUNT(*) as total')
    ->groupBy('area_responsable')->pluck('total', 'area_responsable');
foreach ($porArea as $area => $total) info("  Área {$area}: {$total}");

info("");
info("Alertas por usuario:");
$porUsuario = Alerta::where('proceso_cd_id', $procesoId)
    ->selectRaw('user_id, COUNT(*) as total')
    ->groupBy('user_id')->get();
foreach ($porUsuario as $row) {
    $u = User::find($row->user_id);
    $roles = $u->getRoleNames()->implode(', ');
    info("  {$u->name} ({$roles}): {$row->total}");
}

if ($totalAlertas > 0) {
    exito("Sistema de notificaciones operativo: {$totalAlertas} alertas");
} else {
    falla("No se generaron alertas");
}

// ──────────────────────────────────────────────────
// LIMPIEZA
// ──────────────────────────────────────────────────
paso(9, 'Limpieza');

try {
    Alerta::where('proceso_cd_id', $procesoId)->delete();
    ProcesoCDDocumento::where('proceso_cd_id', $procesoId)->delete();
    \Illuminate\Support\Facades\DB::table('proceso_cd_auditoria')->where('proceso_cd_id', $procesoId)->delete();
    ProcesoContratacionDirecta::destroy($procesoId);
    exito('Datos de prueba eliminados');
} catch (\Throwable $e) {
    falla("Limpieza: " . $e->getMessage());
}

echo "\n═══════════════════════════════════════════════════\n";
echo " RESULTADO: {$ok} exitosas, " . count($errores) . " fallidas\n";
echo "═══════════════════════════════════════════════════\n";
if (count($errores) > 0) {
    echo "\nErrores:\n";
    foreach ($errores as $e) echo "  ✗ {$e}\n";
}
echo "\n";
