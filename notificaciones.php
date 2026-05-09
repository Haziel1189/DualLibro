<?php
// =========================================
// notificaciones.php - API para gestión de notificaciones
// =========================================

require_once 'config.php';

// Determinar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        switch ($request) {
            case 'listar':
                getNotificaciones();
                break;
            case 'no-leidas':
                getNotificacionesNoLeidas();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'PUT':
        switch ($request) {
            case 'marcar-leida':
                marcarNotificacionLeida();
                break;
            case 'marcar-todas-leidas':
                marcarTodasLeidas();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'POST':
        switch ($request) {
            case 'crear':
                crearNotificacion();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'DELETE':
        switch ($request) {
            case 'eliminar':
                eliminarNotificacion();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    default:
        enviarRespuesta(['success' => false, 'message' => 'Método HTTP no soportado'], 405);
}

// =========================================
// FUNCIONES PARA NOTIFICACIONES
// =========================================

// Obtener todas las notificaciones del usuario
function getNotificaciones() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT id, titulo, mensaje, tipo, leida,
                   DATE_FORMAT(fecha_creacion, '%Y-%m-%d %H:%i:%s') as fecha_creacion
            FROM notificaciones
            WHERE id_usuario = ?
            ORDER BY fecha_creacion DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$usuario['id'], $limit, $offset]);
        $notificaciones = $stmt->fetchAll();

        // Contar total de notificaciones
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE id_usuario = ?");
        $stmt->execute([$usuario['id']]);
        $total = $stmt->fetch()['total'];

        enviarRespuesta([
            'success' => true,
            'data' => [
                'notificaciones' => $notificaciones,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener notificaciones'], 500);
    }
}

// Obtener solo notificaciones no leídas
function getNotificacionesNoLeidas() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as no_leidas
            FROM notificaciones
            WHERE id_usuario = ? AND leida = FALSE
        ");
        $stmt->execute([$usuario['id']]);
        $count = $stmt->fetch()['no_leidas'];

        enviarRespuesta([
            'success' => true,
            'data' => [
                'no_leidas' => (int)$count
            ]
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al contar notificaciones'], 500);
    }
}

// Marcar una notificación como leída
function marcarNotificacionLeida() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $notificacionId = isset($data['notificacion_id']) ? (int)$data['notificacion_id'] : 0;

    if ($notificacionId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de notificación inválido'], 400);
    }

    $conn = getDBConnection();

    try {
        // Verificar que la notificación pertenece al usuario
        $stmt = $conn->prepare("
            SELECT id FROM notificaciones
            WHERE id = ? AND id_usuario = ?
        ");
        $stmt->execute([$notificacionId, $usuario['id']]);

        if (!$stmt->fetch()) {
            enviarRespuesta(['success' => false, 'message' => 'Notificación no encontrada'], 404);
        }

        // Marcar como leída
        $stmt = $conn->prepare("
            UPDATE notificaciones SET leida = TRUE
            WHERE id = ? AND id_usuario = ?
        ");
        $stmt->execute([$notificacionId, $usuario['id']]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al marcar notificación'], 500);
    }
}

// Marcar todas las notificaciones como leídas
function marcarTodasLeidas() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            UPDATE notificaciones SET leida = TRUE
            WHERE id_usuario = ? AND leida = FALSE
        ");
        $stmt->execute([$usuario['id']]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al marcar notificaciones'], 500);
    }
}

// Crear nueva notificación (para uso interno/admin)
function crearNotificacion() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['titulo']) || !isset($data['mensaje'])) {
        enviarRespuesta(['success' => false, 'message' => 'Título y mensaje son requeridos'], 400);
    }

    $titulo = sanitizar($data['titulo']);
    $mensaje = sanitizar($data['mensaje']);
    $tipo = isset($data['tipo']) ? sanitizar($data['tipo']) : 'info';
    $idUsuarioDestino = isset($data['usuario_id']) ? (int)$data['usuario_id'] : $usuario['id'];

    // Validar tipo
    if (!in_array($tipo, ['info', 'warning', 'success', 'error'])) {
        $tipo = 'info';
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$idUsuarioDestino, $titulo, $mensaje, $tipo]);

        $notificacionId = $conn->lastInsertId();

        enviarRespuesta([
            'success' => true,
            'message' => 'Notificación creada exitosamente',
            'data' => ['notificacion_id' => $notificacionId]
        ], 201);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al crear notificación'], 500);
    }
}

// Eliminar notificación
function eliminarNotificacion() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $notificacionId = isset($_GET['notificacion_id']) ? (int)$_GET['notificacion_id'] : 0;

    if ($notificacionId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de notificación inválido'], 400);
    }

    $conn = getDBConnection();

    try {
        // Verificar que la notificación pertenece al usuario
        $stmt = $conn->prepare("
            SELECT id FROM notificaciones
            WHERE id = ? AND id_usuario = ?
        ");
        $stmt->execute([$notificacionId, $usuario['id']]);

        if (!$stmt->fetch()) {
            enviarRespuesta(['success' => false, 'message' => 'Notificación no encontrada'], 404);
        }

        // Eliminar notificación
        $stmt = $conn->prepare("DELETE FROM notificaciones WHERE id = ?");
        $stmt->execute([$notificacionId]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Notificación eliminada exitosamente'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al eliminar notificación'], 500);
    }
}
?>