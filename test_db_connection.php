<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "✓ Conexión exitosa a MariaDB\n";
    echo "Versión: " . $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
