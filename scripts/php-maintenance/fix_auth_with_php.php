<?php
try {
    // Conectar sin contraseña (modo skip-grant-tables activo)
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=mysql', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✓ Conectado en modo seguro\n";
    
    // Cambiar el método de autenticación
    $pdo->exec("FLUSH PRIVILEGES");
    $pdo->exec("UPDATE mysql.user SET plugin='mysql_native_password', Password=PASSWORD('') WHERE User='root'");
    $pdo->exec("FLUSH PRIVILEGES");
    
    echo "✓ Método de autenticación cambiado exitosamente\n";
    echo "Ahora detén MariaDB y reinícialo normalmente desde XAMPP\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
