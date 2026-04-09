<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=gobernacion_db', 'root', 'S4p4rt213');
    echo "✓ Conexión exitosa a MariaDB con contraseña\n";
    echo "Base de datos: gobernacion_db\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'gobernacion_db'");
    echo "Tablas encontradas: " . $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
