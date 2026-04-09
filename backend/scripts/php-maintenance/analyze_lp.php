<?php

/**
 * Script definitivo para revisar y corregir TODO LP
 */

$seederFile = __DIR__ . '/database/seeders/WorkflowSeeder.php';
$content = file_get_contents($seederFile);

// Extraer solo el contenido de LP
preg_match('/\/\/ WORKFLOW 4: LICITACIÓN PÚBLICA.*?\/\/ WORKFLOW 5: CONCURSO DE MÉRITOS/s', $content, $lpMatch);

if (empty($lpMatch[0])) {
    die("No se pudo encontrar LP\n");
}

$lpContent = $lpMatch[0];

// Extraer todos los órdenes
preg_match_all("/'orden' => (\d+),\s+'nombre' => '([^']+)'/", $lpContent, $matches, PREG_SET_ORDER);

echo "=== ANÁLISIS DE LP ===\n\n";
$prev = -1;
$duplicates = [];
foreach ($matches as $match) {
    $orden = $match[1];
    $nombre = $match[2];
    
    if ($orden == $prev) {
        echo "❌ DUPLICADO: Orden $orden - $nombre\n";
        $duplicates[] = $orden;
    } else {
        echo "✓ Orden $orden - $nombre\n";
    }
    
    $prev = $orden;
}

if (!empty($duplicates)) {
    echo "\n⚠️  Se encontraron " . count($duplicates) . " órdenes duplicados\n";
    echo "Los órdenes duplicados son: " . implode(", ", array_unique($duplicates)) . "\n";
} else {
    echo "\n✅ No hay duplicados en LP\n";
}

// Contar total de etapas
echo "\nTotal de etapas en LP: " . count($matches) . "\n";
echo "Rango esperado: 0-" . (count($matches) - 1) . "\n";
