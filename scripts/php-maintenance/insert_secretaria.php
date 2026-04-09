<?php
require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=127.0.0.1;dbname=gobernacion_db', 'root', 'S4p4rt213');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->exec("INSERT INTO secretarias (id, nombre, codigo, activo, created_at, updated_at) 
                VALUES (1, 'Secretaría General', 'SG', 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE nombre='Secretaría General'");
    echo "✓ Secretaría creada/actualizada\n";
} catch (PDOException $e) {
    echo "Secretaría ya existe o error: " . $e->getMessage() . "\n";
}
