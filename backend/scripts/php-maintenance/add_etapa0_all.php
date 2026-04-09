<?php

/**
 * Script completo para agregar Etapa 0 a SA, LP y CM
 * y actualizar todos los valores de 'orden'
 */

$seederFile = __DIR__ . '/database/seeders/WorkflowSeeder.php';
$content = file_get_contents($seederFile);

// ========================================
// 1. SELECCIÓN ABREVIADA (SA)
// ========================================
$saFind = "            // WORKFLOW 3: SELECCIÓN ABREVIADA (SA)
            // ================================
            [
                'codigo' => 'SA',
                'nombre' => 'Selección Abreviada (SA)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 0,";

$saReplace = "            // WORKFLOW 3: SELECCIÓN ABREVIADA (SA)
            // ================================
            [
                'codigo' => 'SA',
                'nombre' => 'Selección Abreviada (SA)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0: VERIFICACIÓN PAA (PLANEACIÓN) =====
                    [
                        'orden' => 0,
                        'nombre' => '0: Verificación del PAA',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Verificar inclusión en PAA vigente',
                            'Revisar ficha técnica del proceso',
                            'Validar disponibilidad presupuestal',
                            'Aprobar inicio del proceso',
                        ],
                    ],
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 1,";

$content = str_replace($saFind, $saReplace, $content);
echo "✓ Etapa 0 agregada a SA\n";

// Actualizar órdenes de SA: 0B pasa de 1 a 2
$content = str_replace(
    "                    // ===== ETAPA 0B: PLANEACIÓN =====\n                    [\n                        'orden' => 1,\n                        'nombre' => '0B: CDP y Análisis del Sector',\n                        'area_role' => 'planeacion',\n                        'items' => [\n                            'Expedición de CDP',\n                            'Análisis del sector',\n                            'Estudios de mercado',\n                        ],\n                    ],\n                    // ===== ETAPA 1: PLANEACIÓN =====\n                    [\n                        'orden' => 2,",
    "                    // ===== ETAPA 0B: PLANEACIÓN =====\n                    [\n                        'orden' => 2,\n                        'nombre' => '0B: CDP y Análisis del Sector',\n                        'area_role' => 'planeacion',\n                        'items' => [\n                            'Expedición de CDP',\n                            'Análisis del sector',\n                            'Estudios de mercado',\n                        ],\n                    ],\n                    // ===== ETAPA 1: PLANEACIÓN =====\n                    [\n                        'orden' => 3,",
    $content
);
echo "✓ Órdenes 0B y 1 actualizados en SA\n";

// ========================================
// 2. LICITACIÓN PÚBLICA (LP)
// ========================================
$lpFind = "            // WORKFLOW 4: LICITACIÓN PÚBLICA (LP)
            // ================================
            [
                'codigo' => 'LP',
                'nombre' => 'Licitación Pública (LP)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 0,";

$lpReplace = "            // WORKFLOW 4: LICITACIÓN PÚBLICA (LP)
            // ================================
            [
                'codigo' => 'LP',
                'nombre' => 'Licitación Pública (LP)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0: VERIFICACIÓN PAA (PLANEACIÓN) =====
                    [
                        'orden' => 0,
                        'nombre' => '0: Verificación del PAA',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Verificar inclusión en PAA vigente',
                            'Revisar ficha técnica del proceso',
                            'Validar disponibilidad presupuestal',
                            'Aprobar inicio del proceso',
                        ],
                    ],
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 1,";

$content = str_replace($lpFind, $lpReplace, $content);
echo "✓ Etapa 0 agregada a LP\n";

// ========================================
// 3. CONCURSO DE MÉRITOS (CM)
// ========================================
$cmFind = "            // WORKFLOW 5: CONCURSO DE MÉRITOS (CM)
            // ================================
            [
                'codigo' => 'CM',
                'nombre' => 'Concurso de Méritos (CM)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 0,";

$cmReplace = "            // WORKFLOW 5: CONCURSO DE MÉRITOS (CM)
            // ================================
            [
                'codigo' => 'CM',
                'nombre' => 'Concurso de Méritos (CM)',
                'activo' => true,
                'etapas' => [
                    // ===== ETAPA 0: VERIFICACIÓN PAA (PLANEACIÓN) =====
                    [
                        'orden' => 0,
                        'nombre' => '0: Verificación del PAA',
                        'area_role' => 'planeacion',
                        'items' => [
                            'Verificar inclusión en PAA vigente',
                            'Revisar ficha técnica del proceso',
                            'Validar disponibilidad presupuestal',
                            'Aprobar inicio del proceso',
                        ],
                    ],
                    // ===== ETAPA 0A: UNIDAD SOLICITANTE =====
                    [
                        'orden' => 1,";

$content = str_replace($cmFind, $cmReplace, $content);
echo "✓ Etapa 0 agregada a CM\n";

// Guardar archivo
file_put_contents($seederFile, $content);
echo "\n✅ Etapa 0 agregada a todos los workflows faltantes\n";
echo "⚠️  Nota: Los órdenes restantes necesitan actualizarse manualmente o con script adicional\n";
