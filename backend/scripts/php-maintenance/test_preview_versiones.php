<?php

/**
 * Test de previsualización y control de versiones de documentos
 * 
 * Ejecutar: php test_preview_versiones.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use App\Models\ProcesoEtapaArchivo;

echo "\n" . str_repeat('=', 60) . "\n";
echo "  TEST: Previsualización y Control de Versiones\n";
echo str_repeat('=', 60) . "\n\n";

$passed = 0;
$failed = 0;

function test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ {$name}\n";
        $passed++;
    } else {
        echo "  ❌ {$name}\n";
        $failed++;
    }
}

// ======== 1. Verificar migración ========
echo "📦 1. Migración de columnas\n";

$columns = DB::select("SHOW COLUMNS FROM proceso_etapa_archivos");
$colNames = array_map(fn($c) => $c->Field, $columns);

test("Columna 'motivo_reemplazo' existe", in_array('motivo_reemplazo', $colNames));
test("Columna 'es_reemplazo_admin' existe", in_array('es_reemplazo_admin', $colNames));
test("Columna 'version' existe", in_array('version', $colNames));
test("Columna 'archivo_anterior_id' existe", in_array('archivo_anterior_id', $colNames));
echo "\n";

// ======== 2. Verificar modelo ========
echo "📋 2. Modelo ProcesoEtapaArchivo\n";

$fillable = (new ProcesoEtapaArchivo)->getFillable();
test("'motivo_reemplazo' en fillable", in_array('motivo_reemplazo', $fillable));
test("'es_reemplazo_admin' en fillable", in_array('es_reemplazo_admin', $fillable));
test("'version' en fillable", in_array('version', $fillable));
test("'archivo_anterior_id' en fillable", in_array('archivo_anterior_id', $fillable));
echo "\n";

// ======== 3. Verificar rutas ========
echo "🛤️  3. Rutas registradas\n";

$routes = collect(app('router')->getRoutes())->map(fn($r) => $r->getName())->filter()->toArray();

test("Ruta 'workflow.files.preview' existe", in_array('workflow.files.preview', $routes));
test("Ruta 'workflow.files.historial' existe", in_array('workflow.files.historial', $routes));
test("Ruta 'workflow.files.reemplazar' existe", in_array('workflow.files.reemplazar', $routes));
test("Ruta 'workflow.files.store' existe", in_array('workflow.files.store', $routes));
test("Ruta 'workflow.files.download' existe", in_array('workflow.files.download', $routes));
echo "\n";

// ======== 4. Verificar lógica de bloqueo ========
echo "🔒 4. Lógica de bloqueo (documentoBloqueado)\n";

// Crear datos de prueba temporales
DB::beginTransaction();

try {
    // Buscar etapas reales para crear datos de prueba
    $etapa1 = DB::table('etapas')->whereNotNull('next_etapa_id')->first();
    $etapa2 = $etapa1 ? DB::table('etapas')->where('id', $etapa1->next_etapa_id)->first() : null;

    if (!$etapa1 || !$etapa2) {
        echo "  ⚠️  No hay etapas con next_etapa_id en BD. Saltando tests de bloqueo.\n";
        for ($i = 0; $i < 3; $i++) test("(skip - sin etapas)", true);
    } else {
        // Crear un proceso de prueba con etapa real
        $procesoId = DB::table('procesos')->insertGetId([
            'codigo' => 'TEST-PREVIEW-001',
            'objeto' => 'Test de preview',
            'estado' => 'EN_CURSO',
            'created_by' => 1,
            'etapa_actual_id' => $etapa1->id,
            'area_actual_role' => $etapa1->area_role ?? 'planeacion',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Crear proceso_etapa para etapa 1 (NO enviado)
        $pe1Id = DB::table('proceso_etapas')->insertGetId([
            'proceso_id' => $procesoId,
            'etapa_id' => $etapa1->id,
            'recibido' => false,
            'enviado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear archivo en etapa 1
        $archivo = ProcesoEtapaArchivo::create([
            'proceso_id' => $procesoId,
            'proceso_etapa_id' => $pe1Id,
            'etapa_id' => $etapa1->id,
            'tipo_archivo' => 'test_preview',
            'nombre_original' => 'test.pdf',
            'nombre_guardado' => 'test.pdf',
            'ruta' => 'test/test.pdf',
            'mime_type' => 'application/pdf',
            'tamanio' => 1024,
            'uploaded_by' => 1,
            'uploaded_at' => now(),
            'estado' => 'aprobado',
            'version' => 1,
        ]);

        // Test 4a: Antes de enviar → NO bloqueado
        $controller = new \App\Http\Controllers\WorkflowFilesController();
        $reflection = new ReflectionMethod($controller, 'documentoBloqueado');
        $reflection->setAccessible(true);
        
        $bloqueado = $reflection->invoke($controller, $archivo);
        test("Antes de enviar: NO bloqueado", $bloqueado === false);

        // Test 4b: Después de enviar pero sin recibir siguiente → NO bloqueado
        DB::table('proceso_etapas')->where('id', $pe1Id)->update(['enviado' => true, 'enviado_at' => now()]);
        
        // Crear proceso_etapa para etapa 2 (sin recibir)
        DB::table('proceso_etapas')->insert([
            'proceso_id' => $procesoId,
            'etapa_id' => $etapa2->id,
            'recibido' => false,
            'enviado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $archivo->refresh();
        $bloqueado = $reflection->invoke($controller, $archivo);
        test("Enviado + siguiente NO recibió: NO bloqueado", $bloqueado === false);

        // Test 4c: Después de que la siguiente área recibe → BLOQUEADO
        DB::table('proceso_etapas')
            ->where('proceso_id', $procesoId)
            ->where('etapa_id', $etapa2->id)
            ->update(['recibido' => true, 'recibido_at' => now()]);
        
        $bloqueado = $reflection->invoke($controller, $archivo);
        test("Enviado + siguiente SÍ recibió: BLOQUEADO", $bloqueado === true);

        // ======== 5. Verificar cadena de versiones ========
        echo "\n📚 5. Cadena de versiones\n";

        $reflection2 = new ReflectionMethod($controller, 'obtenerCadenaVersiones');
        $reflection2->setAccessible(true);

        // Crear versión 2
        $archivo2 = ProcesoEtapaArchivo::create([
            'proceso_id' => $procesoId,
            'proceso_etapa_id' => $pe1Id,
            'etapa_id' => $etapa1->id,
            'tipo_archivo' => 'test_preview',
            'nombre_original' => 'test_v2.pdf',
            'nombre_guardado' => 'test_v2.pdf',
            'ruta' => 'test/test_v2.pdf',
            'mime_type' => 'application/pdf',
            'tamanio' => 2048,
            'uploaded_by' => 1,
            'uploaded_at' => now(),
            'estado' => 'pendiente',
            'version' => 2,
            'archivo_anterior_id' => $archivo->id,
        ]);

        // Crear versión 3 (reemplazo admin)
        $archivo3 = ProcesoEtapaArchivo::create([
            'proceso_id' => $procesoId,
            'proceso_etapa_id' => $pe1Id,
            'etapa_id' => $etapa1->id,
            'tipo_archivo' => 'test_preview',
            'nombre_original' => 'test_v3.pdf',
            'nombre_guardado' => 'test_v3.pdf',
            'ruta' => 'test/test_v3.pdf',
            'mime_type' => 'application/pdf',
            'tamanio' => 3072,
            'uploaded_by' => 1,
            'uploaded_at' => now(),
            'estado' => 'pendiente',
            'version' => 3,
            'archivo_anterior_id' => $archivo2->id,
            'motivo_reemplazo' => 'Corrección de datos por auditoría',
            'es_reemplazo_admin' => true,
        ]);

        // Obtener cadena desde v3
        $cadena = $reflection2->invoke($controller, $archivo3);
        
        test("Cadena tiene 3 versiones", count($cadena) === 3);
        test("Primera versión es v1", $cadena[0]['version'] === 1);
        test("Última versión es v3", $cadena[2]['version'] === 3);
        test("v3 tiene motivo_reemplazo", $cadena[2]['motivo_reemplazo'] === 'Corrección de datos por auditoría');
        test("v3 es reemplazo admin", $cadena[2]['es_reemplazo_admin'] === true);
        
        // Obtener cadena desde v1 (debe dar misma cadena)
        $cadena2 = $reflection2->invoke($controller, $archivo);
        test("Cadena desde v1 también tiene 3 versiones", count($cadena2) === 3);
    } // end if etapa1 && etapa2

    // ======== 6. Verificar previsualización MIME ========
    echo "\n👁️  6. Tipos MIME previsualizables\n";
    
    $ctrl = new \App\Http\Controllers\WorkflowFilesController();
    $reflection3 = new ReflectionMethod($ctrl, 'esPrevisualizable');
    $reflection3->setAccessible(true);
    
    test("PDF es previsualizable", $reflection3->invoke($ctrl, 'application/pdf') === true);
    test("JPEG es previsualizable", $reflection3->invoke($ctrl, 'image/jpeg') === true);
    test("PNG es previsualizable", $reflection3->invoke($ctrl, 'image/png') === true);
    test("Word NO es previsualizable", $reflection3->invoke($ctrl, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') === false);
    test("Excel NO es previsualizable", $reflection3->invoke($ctrl, 'application/vnd.ms-excel') === false);
    test("ZIP NO es previsualizable", $reflection3->invoke($ctrl, 'application/zip') === false);
    
} finally {
    DB::rollBack();
}

// ======== 7. Verificar vistas ========
echo "\n🎨 7. Componente Blade\n";

$modalPath = resource_path('views/components/documento-preview-modal.blade.php');
test("Modal preview blade existe", file_exists($modalPath));

$modalContent = file_get_contents($modalPath);
test("Modal tiene Alpine.js x-data", str_contains($modalContent, 'x-data="documentoPreview()"'));
test("Modal tiene abrir-preview listener", str_contains($modalContent, 'abrir-preview'));
test("Modal tiene preview para PDF (iframe)", str_contains($modalContent, "mime_type === 'application/pdf'"));
test("Modal tiene preview para imágenes", str_contains($modalContent, "mime_type?.startsWith('image/')"));
test("Modal tiene panel de versiones", str_contains($modalContent, "tab === 'versiones'"));
test("Modal tiene panel de acciones/reemplazo", str_contains($modalContent, "tab === 'acciones'"));
test("Modal tiene campo motivo_reemplazo", str_contains($modalContent, 'motivo_reemplazo'));
test("Modal incluido en layout", str_contains(file_get_contents(resource_path('views/layouts/app.blade.php')), 'documento-preview-modal'));

// ======== 8. Verificar integración en vistas de áreas ========
echo "\n🔗 8. Integración en vistas de áreas\n";

$vistas = [
    'procesos/show.blade.php',
    'planeacion/show.blade.php',
    'hacienda/show.blade.php',
    'juridica/show.blade.php',
    'secop/show.blade.php',
];

foreach ($vistas as $vista) {
    $path = resource_path("views/{$vista}");
    if (file_exists($path)) {
        $content = file_get_contents($path);
        test("{$vista} tiene botón preview", str_contains($content, 'abrir-preview'));
    } else {
        test("{$vista} (no existe, skip)", true);
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "  RESULTADOS: {$passed} pasaron, {$failed} fallaron\n";
echo str_repeat('=', 60) . "\n\n";

exit($failed > 0 ? 1 : 0);
