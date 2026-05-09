<?php
// =========================================
// usuarios.php - API para gestión de usuarios
// =========================================

require_once 'config.php';

// Determinar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        switch ($request) {
            case 'perfil':
                getPerfilUsuario();
                break;
            case 'estadisticas':
                getEstadisticasUsuario();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'PUT':
        switch ($request) {
            case 'actualizar':
                actualizarPerfil();
                break;
            case 'cambiar-rol':
                cambiarRol();
                break;
            case 'cambiar-password':
                cambiarPassword();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'DELETE':
        switch ($request) {
            case 'desactivar':
                desactivarCuenta();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    default:
        enviarRespuesta(['success' => false, 'message' => 'Método HTTP no soportado'], 405);
}

// =========================================
// FUNCIONES PARA USUARIOS
// =========================================

// Obtener perfil del usuario actual
function getPerfilUsuario() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT id, nombre, email, rol, activo,
                   DATE_FORMAT(fecha_registro, '%Y-%m-%d') as fecha_registro,
                   DATE_FORMAT(ultimo_acceso, '%Y-%m-%d %H:%i:%s') as ultimo_acceso
            FROM usuarios
            WHERE id = ?
        ");
        $stmt->execute([$usuario['id']]);
        $perfil = $stmt->fetch();

        if (!$perfil) {
            enviarRespuesta(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        // Obtener estadísticas adicionales
        $stmt = $conn->prepare("
            SELECT
                (SELECT COUNT(*) FROM biblioteca_personal WHERE id_usuario = ?) as libros_guardados,
                (SELECT COUNT(*) FROM libros WHERE id_autor = ?) as obras_publicadas,
                (SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = FALSE) as notificaciones_no_leidas
        ");
        $stmt->execute([$usuario['id'], $usuario['id'], $usuario['id']]);
        $estadisticas = $stmt->fetch();

        $perfil['estadisticas'] = $estadisticas;

        enviarRespuesta([
            'success' => true,
            'data' => $perfil
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener perfil'], 500);
    }
}

// Obtener estadísticas detalladas del usuario
function getEstadisticasUsuario() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $conn = getDBConnection();

    try {
        // Estadísticas generales
        $stmt = $conn->prepare("
            SELECT
                (SELECT COUNT(*) FROM biblioteca_personal WHERE id_usuario = ?) as libros_guardados,
                (SELECT COUNT(*) FROM libros WHERE id_autor = ?) as obras_totales,
                (SELECT COUNT(*) FROM libros WHERE id_autor = ? AND estado = 'publicado') as obras_publicadas,
                (SELECT COUNT(*) FROM libros WHERE id_autor = ? AND estado = 'borrador') as obras_borrador,
                (SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ?) as total_notificaciones,
                (SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = FALSE) as notificaciones_no_leidas
        ");
        $stmt->execute([$usuario['id'], $usuario['id'], $usuario['id'], $usuario['id'], $usuario['id'], $usuario['id']]);
        $estadisticas = $stmt->fetch();

        // Libros por género (en biblioteca)
        $stmt = $conn->prepare("
            SELECT l.genero, COUNT(*) as cantidad
            FROM libros l
            INNER JOIN biblioteca_personal bp ON l.id = bp.id_libro
            WHERE bp.id_usuario = ?
            GROUP BY l.genero
            ORDER BY cantidad DESC
        ");
        $stmt->execute([$usuario['id']]);
        $generosBiblioteca = $stmt->fetchAll();

        // Actividad reciente (últimos 30 días)
        $stmt = $conn->prepare("
            SELECT
                (SELECT COUNT(*) FROM biblioteca_personal
                 WHERE id_usuario = ? AND fecha_guardado >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as libros_guardados_30_dias,
                (SELECT COUNT(*) FROM libros
                 WHERE id_autor = ? AND fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as obras_publicadas_30_dias
        ");
        $stmt->execute([$usuario['id'], $usuario['id']]);
        $actividadReciente = $stmt->fetch();

        enviarRespuesta([
            'success' => true,
            'data' => [
                'estadisticas' => $estadisticas,
                'generos_biblioteca' => $generosBiblioteca,
                'actividad_reciente' => $actividadReciente
            ]
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener estadísticas'], 500);
    }
}

// Actualizar perfil del usuario
function actualizarPerfil() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data)) {
        enviarRespuesta(['success' => false, 'message' => 'Datos de actualización requeridos'], 400);
    }

    $updates = [];
    $params = [];

    // Validar y preparar campos para actualizar
    if (isset($data['nombre'])) {
        $nombre = sanitizar($data['nombre']);
        if (strlen($nombre) < 2) {
            enviarRespuesta(['success' => false, 'message' => 'El nombre debe tener al menos 2 caracteres'], 400);
        }
        $updates[] = "nombre = ?";
        $params[] = $nombre;
    }

    if (isset($data['email'])) {
        $email = sanitizar($data['email']);
        if (!validarEmail($email)) {
            enviarRespuesta(['success' => false, 'message' => 'Email no válido'], 400);
        }

        // Verificar que el email no esté en uso por otro usuario
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $usuario['id']]);
        if ($stmt->fetch()) {
            enviarRespuesta(['success' => false, 'message' => 'El email ya está en uso'], 409);
        }

        $updates[] = "email = ?";
        $params[] = $email;
    }

    if (empty($updates)) {
        enviarRespuesta(['success' => false, 'message' => 'No hay campos válidos para actualizar'], 400);
    }

    $params[] = $usuario['id'];

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id = ?
        ");
        $stmt->execute($params);

        enviarRespuesta([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al actualizar perfil'], 500);
    }
}

// Cambiar rol del usuario (solo para administradores o el propio usuario con restricciones)
function cambiarRol() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['nuevo_rol'])) {
        enviarRespuesta(['success' => false, 'message' => 'Nuevo rol requerido'], 400);
    }

    $nuevoRol = sanitizar($data['nuevo_rol']);

    if (!in_array($nuevoRol, ['lector', 'escritor'])) {
        enviarRespuesta(['success' => false, 'message' => 'Rol no válido'], 400);
    }

    // Solo permitir cambio entre lector y escritor (no admin)
    if ($nuevoRol === $usuario['rol']) {
        enviarRespuesta(['success' => false, 'message' => 'Ya tienes este rol asignado'], 400);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $stmt->execute([$nuevoRol, $usuario['id']]);

        // Crear notificación de cambio de rol
        $titulo = 'Cambio de rol exitoso';
        $mensaje = "Tu rol ha sido cambiado a: " . ucfirst($nuevoRol);
        $stmt = $conn->prepare("
            INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo)
            VALUES (?, ?, ?, 'success')
        ");
        $stmt->execute([$usuario['id'], $titulo, $mensaje]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Rol cambiado exitosamente',
            'data' => ['nuevo_rol' => $nuevoRol]
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al cambiar rol'], 500);
    }
}

// Cambiar contraseña del usuario
function cambiarPassword() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['password_actual']) || !isset($data['password_nuevo'])) {
        enviarRespuesta(['success' => false, 'message' => 'Contraseña actual y nueva son requeridas'], 400);
    }

    $passwordActual = $data['password_actual'];
    $passwordNuevo = $data['password_nuevo'];

    if (strlen($passwordNuevo) < 6) {
        enviarRespuesta(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres'], 400);
    }

    $conn = getDBConnection();

    try {
        // Verificar contraseña actual
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        $usuarioDB = $stmt->fetch();

        if (!$usuarioDB || !verificarPassword($passwordActual, $usuarioDB['password'])) {
            enviarRespuesta(['success' => false, 'message' => 'Contraseña actual incorrecta'], 400);
        }

        // Actualizar contraseña
        $hashedPassword = hashearPassword($passwordNuevo);
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $usuario['id']]);

        // Invalidar todas las sesiones activas (excepto la actual)
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        $tokenActual = '';

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $tokenActual = $matches[1];
        }

        if ($tokenActual) {
            $stmt = $conn->prepare("
                UPDATE sesiones SET activa = FALSE
                WHERE id_usuario = ? AND token != ? AND activa = TRUE
            ");
            $stmt->execute([$usuario['id'], $tokenActual]);
        }

        // Crear notificación
        $titulo = 'Contraseña cambiada';
        $mensaje = 'Tu contraseña ha sido cambiada exitosamente. Todas las demás sesiones han sido cerradas.';
        $stmt = $conn->prepare("
            INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo)
            VALUES (?, ?, ?, 'warning')
        ");
        $stmt->execute([$usuario['id'], $titulo, $mensaje]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Contraseña cambiada exitosamente'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al cambiar contraseña'], 500);
    }
}

// Desactivar cuenta del usuario
function desactivarCuenta() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['confirmacion'])) {
        enviarRespuesta(['success' => false, 'message' => 'Confirmación requerida'], 400);
    }

    if ($data['confirmacion'] !== 'ELIMINAR_CUENTA') {
        enviarRespuesta(['success' => false, 'message' => 'Confirmación incorrecta'], 400);
    }

    $conn = getDBConnection();

    try {
        // Desactivar cuenta
        $stmt = $conn->prepare("UPDATE usuarios SET activo = FALSE WHERE id = ?");
        $stmt->execute([$usuario['id']]);

        // Invalidar todas las sesiones
        $stmt = $conn->prepare("UPDATE sesiones SET activa = FALSE WHERE id_usuario = ?");
        $stmt->execute([$usuario['id']]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Cuenta desactivada exitosamente'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al desactivar cuenta'], 500);
    }
}
?>