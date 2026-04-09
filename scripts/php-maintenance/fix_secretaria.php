<?php
require 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gobernacion_db', 'root', 'S4p4rt213');
$stmt = $pdo->query('DESCRIBE secretarias');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
echo "\nInsertar secretaría:\n";
$pdo->exec("INSERT INTO secretarias (id, nombre, activo, created_at, updated_at) VALUES (1, 'Secretaría General', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE nombre='Secretaría General'");
echo "✓ Secretaría ID 1 lista\n";
