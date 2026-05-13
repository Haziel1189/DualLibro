<?php
// =========================================
// install.php - Instalador automático de DualLibro
// =========================================

// Verificar si ya está instalado
if (file_exists(__DIR__ . '/.installed')) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'DualLibro ya está instalado.',
        'info' => 'Si quieres reinstalar, elimina el archivo .installed'
    ]);
    exit;
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'duallibro_db');
define('DB_USER', 'root');
define('DB_PASS', '');

$errors = [];
$success_messages = [];

try {
    // Paso 1: Crear conexión sin seleccionar BD
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Paso 2: Crear base de datos si no existe
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    $conn->exec($sql);
    $success_messages[] = "✓ Base de datos creada o ya existe";

    // Paso 3: Seleccionar la BD
    $conn->exec("USE " . DB_NAME);

    // Paso 4: Crear tablas
    $tablas_sql = [
        // Tabla usuarios
        "CREATE TABLE IF NOT EXISTS usuarios (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(150) UNIQUE DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            rol ENUM('lector', 'escritor') DEFAULT 'lector',
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ultimo_acceso TIMESTAMP NULL,
            activo BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Tabla libros
        "CREATE TABLE IF NOT EXISTS libros (
            id INT PRIMARY KEY AUTO_INCREMENT,
            titulo VARCHAR(255) NOT NULL,
            autor VARCHAR(150) NOT NULL,
            genero VARCHAR(100),
            descripcion TEXT,
            contenido LONGTEXT,
            fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            id_autor INT,
            estado ENUM('borrador', 'revision', 'publicado') DEFAULT 'publicado',
            FOREIGN KEY (id_autor) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Tabla biblioteca_personal
        "CREATE TABLE IF NOT EXISTS biblioteca_personal (
            id INT PRIMARY KEY AUTO_INCREMENT,
            id_usuario INT NOT NULL,
            id_libro INT NOT NULL,
            fecha_guardado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (id_libro) REFERENCES libros(id) ON DELETE CASCADE,
            UNIQUE KEY unique_usuario_libro (id_usuario, id_libro)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Tabla notificaciones
        "CREATE TABLE IF NOT EXISTS notificaciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            id_usuario INT NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            mensaje TEXT NOT NULL,
            tipo ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
            leida BOOLEAN DEFAULT FALSE,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Tabla comentarios
        "CREATE TABLE IF NOT EXISTS comentarios (
            id INT PRIMARY KEY AUTO_INCREMENT,
            id_libro INT NOT NULL,
            id_usuario INT NOT NULL,
            comentario TEXT NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_libro) REFERENCES libros(id) ON DELETE CASCADE,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Tabla sesiones
        "CREATE TABLE IF NOT EXISTS sesiones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            id_usuario INT NOT NULL,
            token VARCHAR(255) UNIQUE NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_expiracion TIMESTAMP,
            activa BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($tablas_sql as $sql) {
        $conn->exec($sql);
    }
    $success_messages[] = "✓ Tablas creadas exitosamente";

    // Paso 5: Crear índices
    $indices_sql = [
        "CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_nombre ON usuarios(nombre)",
        "CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email)",
        "CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol)",
        "CREATE INDEX IF NOT EXISTS idx_libros_autor ON libros(id_autor)",
        "CREATE INDEX IF NOT EXISTS idx_libros_genero ON libros(genero)",
        "CREATE INDEX IF NOT EXISTS idx_libros_estado ON libros(estado)",
        "CREATE INDEX IF NOT EXISTS idx_biblioteca_usuario ON biblioteca_personal(id_usuario)",
        "CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario ON notificaciones(id_usuario)",
        "CREATE INDEX IF NOT EXISTS idx_notificaciones_leida ON notificaciones(leida)",
        "CREATE INDEX IF NOT EXISTS idx_comentarios_libro ON comentarios(id_libro)",
        "CREATE INDEX IF NOT EXISTS idx_comentarios_usuario ON comentarios(id_usuario)",
        "CREATE INDEX IF NOT EXISTS idx_sesiones_token ON sesiones(token)",
        "CREATE INDEX IF NOT EXISTS idx_sesiones_usuario ON sesiones(id_usuario)"
    ];

    foreach ($indices_sql as $sql) {
        $conn->exec($sql);
    }
    $success_messages[] = "✓ Índices creados";

    // Paso 6: Insertar datos de ejemplo
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM usuarios");
    $stmt->execute();
    $usuarios_count = $stmt->fetch()['count'];

    if ($usuarios_count == 0) {
        // Datos de ejemplo (contraseña: "123456")
        $ejemplos_sql = [
            [
                'nombre' => 'juan',
                'email' => 'juan@duallibro.local',
                'password' => '$2y$10$GuXAYyFa/S.fMfLUw.8sj.HfgqO/LdDC5ldhFb3g4JJ/zxVxNxIAW',
                'rol' => 'lector'
            ],
            [
                'nombre' => 'maria',
                'email' => 'maria@duallibro.local',
                'password' => '$2y$10$GuXAYyFa/S.fMfLUw.8sj.HfgqO/LdDC5ldhFb3g4JJ/zxVxNxIAW',
                'rol' => 'escritor'
            ]
        ];

        foreach ($ejemplos_sql as $user) {
            $stmt = $conn->prepare(
                "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$user['nombre'], $user['email'], $user['password'], $user['rol']]);
        }

        $success_messages[] = "✓ Datos de ejemplo insertados";
    }

    // Paso 7: Marcar como instalado
    file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => '✓ DualLibro instalado correctamente',
        'details' => $success_messages,
        'next_steps' => [
            'Ve a http://localhost:8080/DualLibro/InicioSesion.html',
            'Registra un nuevo usuario o inicia sesión con: juan / 123456',
            'Este archivo install.php solo se ejecuta una vez'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la instalación',
        'error' => $e->getMessage(),
        'debug_info' => [
            'DB_HOST' => DB_HOST,
            'DB_NAME' => DB_NAME,
            'DB_USER' => DB_USER
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error general',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>
