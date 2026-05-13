<?php
// =========================================
// auth.php - API de autenticación
// Maneja login, registro y gestión de sesiones
// =========================================

require_once 'config.php';

// Determinar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'POST':
        switch ($request) {
            case 'login':
                login();
                break;
            case 'register':
                register();
                break;
            case 'logout':
                logout();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'GET':
        switch ($request) {
            case 'verify':
                verifySession();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    default:
        enviarRespuesta(['success' => false, 'message' => 'Método HTTP no soportado'], 405);
}

// =========================================
// FUNCIONES DE AUTENTICACIÓN
// =========================================

// Función de login
function login() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['nombre']) || !isset($data['password'])) {
        enviarRespuesta(['success' => false, 'message' => 'Usuario y contraseña son requeridos'], 400);
    }

    $nombre = sanitizar($data['nombre']);
    $password = $data['password'];

    if (strlen($nombre) < 2) {
        enviarRespuesta(['success' => false, 'message' => 'Usuario no válido'], 400);
    }

    $conn = getDBConnection();

    // Buscar usuario por nombre de usuario
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre = ? AND activo = TRUE");
    $stmt->execute([$nombre]);
    $usuario = $stmt->fetch();

    if (!$usuario || !verificarPassword($password, $usuario['password'])) {
        enviarRespuesta(['success' => false, 'message' => 'Credenciales incorrectas'], 401);
    }

    // Generar token de sesión
    $token = generarToken();
    $fechaExpiracion = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

    // Guardar sesión en BD
    $stmt = $conn->prepare("
        INSERT INTO sesiones (id_usuario, token, fecha_expiracion)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$usuario['id'], $token, $fechaExpiracion]);

    // Actualizar último acceso
    $stmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
    $stmt->execute([$usuario['id']]);

    enviarRespuesta([
        'success' => true,
        'message' => 'Login exitoso',
        'data' => [
            'usuario' => [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol']
            ],
            'token' => $token
        ]
    ]);
}

// Función de registro
function register() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['nombre']) || !isset($data['password'])) {
        enviarRespuesta(['success' => false, 'message' => 'Nombre de usuario y contraseña son requeridos'], 400);
    }

    $nombre = sanitizar($data['nombre']);
    $password = $data['password'];
    $rol = isset($data['rol']) ? sanitizar($data['rol']) : 'lector';

    // Validaciones
    if (strlen($nombre) < 2) {
        enviarRespuesta(['success' => false, 'message' => 'El usuario debe tener al menos 2 caracteres'], 400);
    }

    if (strlen($password) < 6) {
        enviarRespuesta(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
    }

    if (!in_array($rol, ['lector', 'escritor'])) {
        enviarRespuesta(['success' => false, 'message' => 'Rol no válido'], 400);
    }

    $conn = getDBConnection();

    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->execute([$nombre]);
    if ($stmt->fetch()) {
        enviarRespuesta(['success' => false, 'message' => 'El usuario ya está registrado'], 409);
    }

    // Crear usuario
    $hashedPassword = hashearPassword($password);
    $emailPlaceholder = generarEmailPlaceholder($nombre);
    $stmt = $conn->prepare(
        "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)"
    );

    try {
        $stmt->execute([$nombre, $emailPlaceholder, $hashedPassword, $rol]);
        $usuarioId = $conn->lastInsertId();

        // Crear notificación de bienvenida
        $titulo = 'Bienvenido a ' . APP_NAME;
        $mensaje = 'Gracias por registrarte. ¡Disfruta de tu experiencia de lectura!';
        $stmt = $conn->prepare("
            INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo)
            VALUES (?, ?, ?, 'success')
        ");
        $stmt->execute([$usuarioId, $titulo, $mensaje]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'usuario_id' => $usuarioId
            ]
        ], 201);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al registrar usuario'], 500);
    }
}

// Función de logout
function logout() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];

        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE sesiones SET activa = FALSE WHERE token = ?");
        $stmt->execute([$token]);

        enviarRespuesta(['success' => true, 'message' => 'Sesión cerrada exitosamente']);
    }

    enviarRespuesta(['success' => false, 'message' => 'Token no válido'], 400);
}

// Función para verificar sesión
function verifySession() {
    $usuario = getUsuarioActual();

    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'Sesión inválida o expirada'], 401);
    }

    enviarRespuesta([
        'success' => true,
        'message' => 'Sesión válida',
        'data' => [
            'usuario' => [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol']
            ]
        ]
    ]);
}

// Función para generar un email de marcador de posición cuando no se solicita email
function generarEmailPlaceholder($nombre) {
    $username = preg_replace('/[^a-z0-9]/', '', strtolower($nombre));
    if (empty($username)) {
        $username = 'usuario';
    }
    return $username . '@duallibro.local';
}
?>