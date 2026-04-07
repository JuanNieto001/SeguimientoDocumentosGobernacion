<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', 'S4p4rt213');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS gobernacion_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de datos 'gobernacion_db' creada o ya existe\n";
    
    // Verificar
    $stmt = $pdo->query("SHOW DATABASES LIKE 'gobernacion_db'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Confirmado: Base de datos existe\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
