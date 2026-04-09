<?php
/**
 * Script de prueba del flujo completo de Contratación Directa - Persona Natural.
 * Verifica que cada etapa transicione correctamente de inicio a fin.
 *
 * Ejecutar: php test_flujo_cdpn.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->handleRequest(\Illuminate\Http\Request::capture());

// Necesitamos boot manual para servicios
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

use App\Enums\EstadoProcesoCD;
use App\Models\ProcesoContratacionDirecta;
use App\Models\ProcesoCDDocumento;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Models\User;
use App\Services\ContratoDirectoPNStateMachine;

$sm = app(ContratoDirectoPNStateMachine::class);

$admin     = User::find(1);    // admin
$unidad    = User::find(3);    // unidad_solicitante
$planeacion= User::find(5);    // planeacion
$hacienda  = User::find(8);    // hacienda
$juridica  = User::find(11);   // juridica

$errores = [];
$ok = 0;

function paso($num, $desc) { echo "\n[Paso {$num}] {$desc}\n"; }
function exito($msg) { global $ok; $ok++; echo "  ✓ {$msg}\n"; }
function falla($msg) { global $errores; $errores[] = $msg; echo "  ✗ {$msg}\n"; }
function info($msg) { echo "  → {$msg}\n"; }

echo "═══════════════════════════════════════════════════\n";
echo " PRUEBA COMPLETA: FLUJO CD-PN (7 ETAPAS)\n";
echo "═══════════════════════════════════════════════════\n";

// ──────────────────────────────────────────────────
// ETAPA 1: Crear solicitud con estudio previo
// ──────────────────────────────────────────────────
paso(1, 'ETAPA 1 – Crear solicitud con Estudio Previo');

try {
    $secretaria = Secretaria::first();
    $unidadOrg  = Unidad::first();

    if (!$secretaria || !$unidadOrg) {
        falla("No hay Secretarías o Unidades en la BD. Ejecute los seeders primero.");
        exit(1);
    }

    $proceso = $sm->crearSolicitud([
        'objeto'         => 'TEST - Prueba flujo completo CD-PN ' . date('Y-m-d H:i:s'),
        'valor'          => 15000000,
        'plazo_meses'    => 6,
        'estudio_previo_path' => 'test/dummy_estudio_previo.pdf',
        'secretaria_id'  => $secretaria->id,
        'unidad_id'      => $unidadOrg->id,
        'contratista_nombre'         => 'Juan Prueba García',
        'contratista_tipo_documento' => 'CC',
        'contratista_documento'      => '1234567890',
        'contratista_email'          => 'juan.prueba@test.com',
    ], $unidad);

    info("Código: {$proceso->codigo}");
    info("Estado: {$proceso->estado->value} (Etapa {$proceso->etapa_actual})");

    if ($proceso->estado === EstadoProcesoCD::EN_VALIDACION_PLANEACION) {
        exito("Solicitud creada y ENVIADA AUTOMÁTICAMENTE a Planeación (etapa 2)");
    } else {
        falla("Estado esperado: en_validacion_planeacion, obtenido: {$proceso->estado->value}");
    }
} catch (\Exception $e) {
    falla("Etapa 1 falló: " . $e->getMessage());
    exit(1);
}

// ──────────────────────────────────────────────────
// ETAPA 2: Validaciones Paralelas + CDP
// ──────────────────────────────────────────────────
paso(2, 'ETAPA 2 – Validaciones Paralelas (Planeación)');

try {
    // 2a. Registrar validaciones paralelas una a una
    $campos = [
        'paa_solicitado',
        'certificado_no_planta',
        'paz_salvo_rentas',
        'paz_salvo_contabilidad',
        'compatibilidad_gasto',
    ];

    foreach ($campos as $campo) {
        $proceso = $sm->registrarValidacionParalela($proceso, $campo, $planeacion);
        info("Validación paralela: {$campo} ✓");
    }

    // Verificar que todo esté completo
    $proceso->refresh();
    if ($proceso->validacionesParalelasCompletas()) {
        exito("Todas las validaciones paralelas completadas");
    } else {
        falla("Validaciones paralelas incompletas");
    }

    // 2b. Transicionar a COMPATIBILIDAD_APROBADA
    // Subir documentos obligatorios de etapa 2
    $docsEtapa2 = ['paa', 'no_planta', 'paz_salvo_rentas', 'paz_salvo_contabilidad', 'compatibilidad_gasto'];
    foreach ($docsEtapa2 as $tipo) {
        ProcesoCDDocumento::create([
            'proceso_cd_id'     => $proceso->id,
            'tipo_documento'    => $tipo,
            'nombre_archivo'    => "{$tipo}_test.pdf",
            'ruta_archivo'      => "test/{$tipo}_test.pdf",
            'mime_type'         => 'application/pdf',
            'tamano_bytes'      => 1024,
            'etapa'             => 2,
            'estado_aprobacion' => 'aprobado',
            'subido_por'        => $planeacion->id,
        ]);
    }
    // Primero poner compatibilidad_gasto = true (ya hecho arriba)
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::COMPATIBILIDAD_APROBADA, $planeacion, 'Compatibilidad aprobada por Planeación.');
    info("Estado: {$proceso->estado->value}");
    exito("Transición a COMPATIBILIDAD_APROBADA");

    // 2c. CDP_SOLICITADO
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::CDP_SOLICITADO, $planeacion, 'CDP solicitado a Hacienda.');
    info("Estado: {$proceso->estado->value}");
    exito("Transición a CDP_SOLICITADO");

    // 2d. Subir doc CDP y aprobar
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'cdp',
        'nombre_archivo'    => 'cdp_test.pdf',
        'ruta_archivo'      => 'test/cdp_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 1024,
        'etapa'             => 2,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $hacienda->id,
    ]);

    // 2e. CDP_APROBADO
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::CDP_APROBADO, $hacienda, 'CDP aprobado por Hacienda.', [
        'numero_cdp' => 'CDP-001-2026',
        'valor_cdp'  => 15000000,
    ]);
    info("Estado: {$proceso->estado->value} | CDP: {$proceso->numero_cdp}");
    exito("Transición a CDP_APROBADO. Etapa 2 completa.");

} catch (\Exception $e) {
    falla("Etapa 2 falló: " . $e->getMessage());
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        foreach ($e->errors() as $k => $msgs) {
            foreach ($msgs as $msg) { info("  Detalle [{$k}]: {$msg}"); }
        }
    }
}

// ──────────────────────────────────────────────────
// ETAPA 3: Documentación del Contratista
// ──────────────────────────────────────────────────
paso(3, 'ETAPA 3 – Documentación del Contratista');

try {
    // Transicionar de CDP_APROBADO → DOCUMENTACION_INCOMPLETA (entra a etapa 3)
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::DOCUMENTACION_INCOMPLETA, $unidad, 'Inicio recopilación documentos contratista.');
    info("Estado: {$proceso->estado->value} (Etapa {$proceso->etapa_actual})");
    exito("Transición a DOCUMENTACION_INCOMPLETA");

    // Subir documentos obligatorios de Etapa 3
    $docsEtapa3 = [
        'hoja_vida_sigep', 'cedula', 'rut',
        'antecedentes_disciplinarios', 'antecedentes_fiscales',
        'antecedentes_judiciales', 'seguridad_social_salud',
        'seguridad_social_pension', 'certificado_cuenta_bancaria',
    ];
    foreach ($docsEtapa3 as $tipo) {
        ProcesoCDDocumento::create([
            'proceso_cd_id'     => $proceso->id,
            'tipo_documento'    => $tipo,
            'nombre_archivo'    => "{$tipo}_test.pdf",
            'ruta_archivo'      => "test/{$tipo}_test.pdf",
            'mime_type'         => 'application/pdf',
            'tamano_bytes'      => 1024,
            'etapa'             => 3,
            'estado_aprobacion' => 'aprobado',
            'subido_por'        => $unidad->id,
        ]);
    }

    // Marcar hoja de vida cargada y checklist
    $proceso->update([
        'hoja_vida_cargada'  => true,
        'checklist_validado' => true,
    ]);
    info("Documentos Etapa 3 subidos y checklist validado");

    // DOCUMENTACION_VALIDADA
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::DOCUMENTACION_VALIDADA, $unidad, 'Documentación validada por Unidad.');
    info("Estado: {$proceso->estado->value}");
    exito("Transición a DOCUMENTACION_VALIDADA");

    // EN_REVISION_JURIDICA
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::EN_REVISION_JURIDICA, $unidad, 'Enviado a revisión jurídica.');
    info("Estado: {$proceso->estado->value} (Etapa {$proceso->etapa_actual})");
    exito("Transición a EN_REVISION_JURIDICA. Etapa 3 completa.");

} catch (\Exception $e) {
    falla("Etapa 3 falló: " . $e->getMessage());
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        foreach ($e->errors() as $k => $msgs) {
            foreach ($msgs as $msg) { info("  Detalle [{$k}]: {$msg}"); }
        }
    }
}

// ──────────────────────────────────────────────────
// ETAPA 4: Revisión Jurídica
// ──────────────────────────────────────────────────
paso(4, 'ETAPA 4 – Revisión Jurídica');

try {
    // Subir checklist jurídica
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'checklist_juridica',
        'nombre_archivo'    => 'checklist_juridica_test.pdf',
        'ruta_archivo'      => 'test/checklist_juridica_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 1024,
        'etapa'             => 4,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $juridica->id,
    ]);

    // Asignar número de proceso
    $proceso->update(['numero_proceso_juridica' => 'CD-PS-TEST-2026']);

    // PROCESO_NUMERO_GENERADO
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::PROCESO_NUMERO_GENERADO, $juridica, 'Número de proceso generado.', [
        'numero_proceso' => 'CD-PS-TEST-2026',
    ]);
    info("Estado: {$proceso->estado->value} | Nº: {$proceso->numero_proceso_juridica}");
    exito("Transición a PROCESO_NUMERO_GENERADO");

    // GENERACION_CONTRATO
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::GENERACION_CONTRATO, $juridica, 'Contrato en generación.');
    info("Estado: {$proceso->estado->value}");
    exito("Transición a GENERACION_CONTRATO");

    // Subir contrato electrónico
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'contrato_electronico',
        'nombre_archivo'    => 'contrato_test.pdf',
        'ruta_archivo'      => 'test/contrato_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 2048,
        'etapa'             => 4,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $juridica->id,
    ]);

    // CONTRATO_GENERADO → Etapa 5
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::CONTRATO_GENERADO, $juridica, 'Contrato generado.');
    info("Estado: {$proceso->estado->value} (Etapa {$proceso->etapa_actual})");
    exito("Transición a CONTRATO_GENERADO. Etapa 4 completa.");

} catch (\Exception $e) {
    falla("Etapa 4 falló: " . $e->getMessage());
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        foreach ($e->errors() as $k => $msgs) {
            foreach ($msgs as $msg) { info("  Detalle [{$k}]: {$msg}"); }
        }
    }
}

// ──────────────────────────────────────────────────
// ETAPA 5: Firma de Contrato
// ──────────────────────────────────────────────────
paso(5, 'ETAPA 5 – Generación y Firma de Contrato');

try {
    // 5a. Registrar firma contratista
    $proceso = $sm->registrarFirma($proceso, 'contratista', $unidad);
    info("Estado: {$proceso->estado->value}");
    exito("Firma contratista registrada");

    // Subir contrato firmado
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'contrato_firmado',
        'nombre_archivo'    => 'contrato_firmado_test.pdf',
        'ruta_archivo'      => 'test/contrato_firmado_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 2048,
        'etapa'             => 5,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $juridica->id,
    ]);

    // 5b. Registrar firma ordenador del gasto → auto-avanza a CONTRATO_FIRMADO_TOTAL
    $proceso = $sm->registrarFirma($proceso, 'ordenador_gasto', $juridica);
    info("Estado: {$proceso->estado->value}");

    if ($proceso->estado === EstadoProcesoCD::CONTRATO_FIRMADO_TOTAL) {
        exito("Ambas firmas → CONTRATO_FIRMADO_TOTAL (auto-transición). Etapa 5 completa.");
    } else {
        falla("Se esperaba CONTRATO_FIRMADO_TOTAL, obtenido: {$proceso->estado->value}");
    }

} catch (\Exception $e) {
    falla("Etapa 5 falló: " . $e->getMessage());
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        foreach ($e->errors() as $k => $msgs) {
            foreach ($msgs as $msg) { info("  Detalle [{$k}]: {$msg}"); }
        }
    }
}

// ──────────────────────────────────────────────────
// ETAPA 6: RPC
// ──────────────────────────────────────────────────
paso(6, 'ETAPA 6 – RPC y Radicación');

try {
    // RPC_SOLICITADO
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::RPC_SOLICITADO, $planeacion, 'RPC solicitado.');
    info("Estado: {$proceso->estado->value} (Etapa {$proceso->etapa_actual})");
    exito("Transición a RPC_SOLICITADO");

    // Subir docs RPC
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'solicitud_rpc',
        'nombre_archivo'    => 'solicitud_rpc_test.pdf',
        'ruta_archivo'      => 'test/solicitud_rpc_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 1024,
        'etapa'             => 6,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $planeacion->id,
    ]);
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'rpc',
        'nombre_archivo'    => 'rpc_test.pdf',
        'ruta_archivo'      => 'test/rpc_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 1024,
        'etapa'             => 6,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $hacienda->id,
    ]);

    // Asignar RPC
    $proceso->update(['numero_rpc' => 'RPC-001-2026']);

    // RPC_FIRMADO
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::RPC_FIRMADO, $hacienda, 'RPC firmado por Hacienda.', [
        'numero_rpc' => 'RPC-001-2026',
    ]);
    info("Estado: {$proceso->estado->value} | RPC: {$proceso->numero_rpc}");
    exito("Transición a RPC_FIRMADO");

    // Subir expediente
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'expediente_fisico_final',
        'nombre_archivo'    => 'expediente_final_test.pdf',
        'ruta_archivo'      => 'test/expediente_final_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 3072,
        'etapa'             => 6,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $planeacion->id,
    ]);

    // EXPEDIENTE_RADICADO
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::EXPEDIENTE_RADICADO, $planeacion, 'Expediente final radicado.');
    info("Estado: {$proceso->estado->value}");
    exito("Transición a EXPEDIENTE_RADICADO. Etapa 6 completa.");

} catch (\Exception $e) {
    falla("Etapa 6 falló: " . $e->getMessage());
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        foreach ($e->errors() as $k => $msgs) {
            foreach ($msgs as $msg) { info("  Detalle [{$k}]: {$msg}"); }
        }
    }
}

// ──────────────────────────────────────────────────
// ETAPA 7: Inicio de Ejecución
// ──────────────────────────────────────────────────
paso(7, 'ETAPA 7 – Inicio de Ejecución');

try {
    // Subir docs etapa 7
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'solicitud_arl',
        'nombre_archivo'    => 'solicitud_arl_test.pdf',
        'ruta_archivo'      => 'test/solicitud_arl_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 1024,
        'etapa'             => 7,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $unidad->id,
    ]);
    ProcesoCDDocumento::create([
        'proceso_cd_id'     => $proceso->id,
        'tipo_documento'    => 'acta_inicio',
        'nombre_archivo'    => 'acta_inicio_test.pdf',
        'ruta_archivo'      => 'test/acta_inicio_test.pdf',
        'mime_type'         => 'application/pdf',
        'tamano_bytes'      => 1024,
        'etapa'             => 7,
        'estado_aprobacion' => 'aprobado',
        'subido_por'        => $unidad->id,
    ]);

    // Marcar campos obligatorios
    $proceso->update([
        'numero_contrato'    => 'CONT-001-2026',
        'arl_solicitada'     => true,
        'acta_inicio_firmada'=> true,
    ]);

    // EN_EJECUCION
    $proceso = $sm->transicionar($proceso, EstadoProcesoCD::EN_EJECUCION, $unidad, 'Contrato en ejecución.', [
        'numero_contrato' => 'CONT-001-2026',
        'fecha_inicio'    => now()->format('Y-m-d'),
    ]);
    info("Estado: {$proceso->estado->value} (Etapa {$proceso->etapa_actual})");
    info("Contrato: {$proceso->numero_contrato}");
    info("Fecha inicio: {$proceso->fecha_inicio_ejecucion}");

    if ($proceso->estado === EstadoProcesoCD::EN_EJECUCION) {
        exito("¡PROCESO EN EJECUCIÓN! Flujo completo terminado correctamente.");
    } else {
        falla("Se esperaba EN_EJECUCION, obtenido: {$proceso->estado->value}");
    }

} catch (\Exception $e) {
    falla("Etapa 7 falló: " . $e->getMessage());
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        foreach ($e->errors() as $k => $msgs) {
            foreach ($msgs as $msg) { info("  Detalle [{$k}]: {$msg}"); }
        }
    }
}

// ──────────────────────────────────────────────────
// PRUEBA EXTRA: Verificar que estado final no permite más transiciones
// ──────────────────────────────────────────────────
paso('E', 'PRUEBA EXTRA – Estado final no permite más transiciones');

$transicionesDisponibles = $proceso->estado->transicionesPermitidas();
if (empty($transicionesDisponibles)) {
    exito("Estado EN_EJECUCION no permite más transiciones (correcto).");
} else {
    falla("EN_EJECUCION NO debería tener transiciones, pero tiene: " . count($transicionesDisponibles));
}

// Verificar auditoría
$totalAuditoria = $proceso->auditorias()->count();
info("Total registros de auditoría: {$totalAuditoria}");
if ($totalAuditoria >= 10) {
    exito("Auditoría completa con {$totalAuditoria} registros.");
} else {
    falla("Se esperaban al menos 10 registros de auditoría, hay: {$totalAuditoria}");
}

// ──────────────────────────────────────────────────
// PRUEBA EXTRA: Devolución desde Jurídica
// ──────────────────────────────────────────────────
paso('D', 'PRUEBA EXTRA – Flujo de Devolución');

try {
    // Crear nuevo proceso para probar devolución
    $p2 = $sm->crearSolicitud([
        'objeto'         => 'TEST - Prueba devolución CD-PN ' . date('Y-m-d H:i:s'),
        'valor'          => 5000000,
        'plazo_meses'    => 3,
        'estudio_previo_path' => 'test/dummy2.pdf',
        'secretaria_id'  => $secretaria->id,
        'unidad_id'      => $unidadOrg->id,
    ], $unidad);

    // Avanzar rápido hasta EN_REVISION_JURIDICA
    foreach ($campos as $c) { $sm->registrarValidacionParalela($p2, $c, $planeacion); }

    // Subir documentos obligatorios etapa 2
    foreach (['paa', 'no_planta', 'paz_salvo_rentas', 'paz_salvo_contabilidad', 'compatibilidad_gasto'] as $tipo) {
        ProcesoCDDocumento::create([
            'proceso_cd_id' => $p2->id, 'tipo_documento' => $tipo,
            'nombre_archivo' => "{$tipo}.pdf", 'ruta_archivo' => "test/{$tipo}.pdf",
            'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
            'etapa' => 2, 'estado_aprobacion' => 'aprobado', 'subido_por' => $planeacion->id,
        ]);
    }

    $p2 = $sm->transicionar($p2, EstadoProcesoCD::COMPATIBILIDAD_APROBADA, $planeacion);
    $p2 = $sm->transicionar($p2, EstadoProcesoCD::CDP_SOLICITADO, $planeacion);

    ProcesoCDDocumento::create([
        'proceso_cd_id' => $p2->id, 'tipo_documento' => 'cdp',
        'nombre_archivo' => 'cdp.pdf', 'ruta_archivo' => 'test/cdp.pdf',
        'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
        'etapa' => 2, 'estado_aprobacion' => 'aprobado', 'subido_por' => $hacienda->id,
    ]);
    $p2 = $sm->transicionar($p2, EstadoProcesoCD::CDP_APROBADO, $hacienda, null, ['numero_cdp' => 'CDP-002']);
    $p2 = $sm->transicionar($p2, EstadoProcesoCD::DOCUMENTACION_INCOMPLETA, $unidad);
    $p2->update(['hoja_vida_cargada' => true, 'checklist_validado' => true]);
    foreach ($docsEtapa3 as $t) {
        ProcesoCDDocumento::create([
            'proceso_cd_id' => $p2->id, 'tipo_documento' => $t,
            'nombre_archivo' => "{$t}.pdf", 'ruta_archivo' => "test/{$t}.pdf",
            'mime_type' => 'application/pdf', 'tamano_bytes' => 1024,
            'etapa' => 3, 'estado_aprobacion' => 'aprobado', 'subido_por' => $unidad->id,
        ]);
    }
    $p2 = $sm->transicionar($p2, EstadoProcesoCD::DOCUMENTACION_VALIDADA, $unidad);
    $p2 = $sm->transicionar($p2, EstadoProcesoCD::EN_REVISION_JURIDICA, $unidad);

    // DEVOLVER desde jurídica
    $p2 = $sm->devolverDesdeJuridica($p2, 'Documentos incompletos, falta firma de jefe.', $juridica);
    info("Estado tras devolución: {$p2->estado->value}");

    if ($p2->estado === EstadoProcesoCD::DOCUMENTACION_INCOMPLETA) {
        exito("Devolución desde jurídica funciona correctamente.");
    } else {
        falla("Después de devolución esperaba DOCUMENTACION_INCOMPLETA, obtuvo: {$p2->estado->value}");
    }

    // Cleanup proceso de prueba de devolución
    $p2->auditorias()->delete();
    $p2->documentos()->delete();
    $p2->forceDelete();

} catch (\Exception $e) {
    falla("Prueba devolución falló: " . $e->getMessage());
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        foreach ($e->errors() as $k => $msgs) {
            foreach ($msgs as $msg) { info("  Detalle [{$k}]: {$msg}"); }
        }
    }
}

// ──────────────────────────────────────────────────
// RESUMEN
// ──────────────────────────────────────────────────
echo "\n═══════════════════════════════════════════════════\n";
echo " RESUMEN DE PRUEBAS\n";
echo "═══════════════════════════════════════════════════\n";
echo " Pruebas exitosas: {$ok}\n";
echo " Errores:          " . count($errores) . "\n";

if (!empty($errores)) {
    echo "\n DETALLE DE ERRORES:\n";
    foreach ($errores as $i => $err) {
        echo "   " . ($i + 1) . ". {$err}\n";
    }
}

// Limpiar proceso de prueba
echo "\n Limpiando datos de prueba...\n";
$proceso->auditorias()->delete();
$proceso->documentos()->delete();
$proceso->forceDelete();
echo " Datos de prueba eliminados.\n";

echo "\n" . (empty($errores) ? "✓ TODAS LAS PRUEBAS PASARON CORRECTAMENTE" : "✗ HAY ERRORES QUE CORREGIR") . "\n\n";

exit(empty($errores) ? 0 : 1);
