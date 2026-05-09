<?php
// =========================================
// verificar_conexion.php - Verifica la conexión a la base de datos
// =========================================

require_once 'config.php';

echo "<h2>🔍 VERIFICACIÓN DE CONEXIÓN A LA BASE DE DATOS</h2>";
echo "<hr>";

// Verificar conexión
try {
    $conn = getDBConnection();
    echo "✅ <strong>Conexión a BD:</strong> EXITOSA<br>";
    
    // Verificar tablas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "✅ <strong>Tablas encontradas:</strong> " . count($tables) . "<br>";
    foreach ($tables as $table) {
        echo "   - " . $table[0] . "<br>";
    }
    
    // Verificar registros en usuarios
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "<br>✅ <strong>Usuarios en BD:</strong> " . $result['total'] . "<br>";
    
    // Listar usuarios
    if ($result['total'] > 0) {
        echo "<strong>Usuarios registrados:</strong><br>";
        $stmt = $conn->query("SELECT id, nombre, email, rol FROM usuarios");
        $usuarios = $stmt->fetchAll();
        foreach ($usuarios as $user) {
            echo "   - ID: {$user['id']}, Nombre: {$user['nombre']}, Email: {$user['email']}, Rol: {$user['rol']}<br>";
        }
    }
    
    echo "<br><hr>";
    echo "<h3>✅ ¡CONEXIÓN CORRECTA!</h3>";
    echo "Si ves este mensaje, la base de datos está correctamente configurada.<br>";
    echo "Ahora puedes intentar registrarte e iniciar sesión en InicioSesion.html";
    
} catch (Exception $e) {
    echo "❌ <strong>ERROR DE CONEXIÓN:</strong><br>";
    echo "<pre style='background: #ffcccc; padding: 10px; border-radius: 5px;'>";
    echo $e->getMessage();
    echo "</pre>";
    echo "<br>";
    echo "<strong>💡 SOLUCIONES:</strong><br>";
    echo "1. Verifica que XAMPP esté corriendo (Apache y MySQL)<br>";
    echo "2. Verifica que phpMyAdmin esté disponible en http://localhost/phpmyadmin<br>";
    echo "3. Verifica que la base de datos 'duallibro_db' esté creada<br>";
    echo "4. Verifica el usuario y contraseña en config.php<br>";
}
?>
