<?php
// =========================================
// libros.php - API para gestión de libros
// =========================================

require_once 'config.php';

// Determinar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        switch ($request) {
            case 'catalogo':
                getCatalogo();
                break;
            case 'buscar':
                buscarLibros();
                break;
            case 'detalle':
                getLibroDetalle();
                break;
            case 'comentarios':
                getComentariosLibro();
                break;
            case 'biblioteca':
                getBibliotecaPersonal();
                break;
            case 'obras':
                getObrasUsuario();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'POST':
        switch ($request) {
            case 'guardar':
                guardarLibroBiblioteca();
                break;
            case 'crear':
                crearObra();
                break;
            case 'comentar':
                comentarLibro();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'PUT':
        switch ($request) {
            case 'actualizar':
                actualizarObra();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    case 'DELETE':
        switch ($request) {
            case 'quitar':
                quitarLibroBiblioteca();
                break;
            case 'eliminar':
                eliminarObra();
                break;
            default:
                enviarRespuesta(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;

    default:
        enviarRespuesta(['success' => false, 'message' => 'Método HTTP no soportado'], 405);
}

// =========================================
// FUNCIONES PARA LIBROS
// =========================================

// Obtener catálogo completo de libros
function getCatalogo() {
    $conn = getDBConnection();

    try {
        $stmt = $conn->query("
            SELECT id, titulo, autor, genero, descripcion,
                   DATE_FORMAT(fecha_publicacion, '%Y-%m-%d') as fecha_publicacion
            FROM libros
            WHERE estado = 'publicado'
            ORDER BY fecha_publicacion DESC
        ");

        $libros = $stmt->fetchAll();

        enviarRespuesta([
            'success' => true,
            'data' => $libros
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener catálogo'], 500);
    }
}

// Buscar libros por término
function buscarLibros() {
    $termino = isset($_GET['q']) ? sanitizar($_GET['q']) : '';

    if (empty($termino)) {
        enviarRespuesta(['success' => false, 'message' => 'Término de búsqueda requerido'], 400);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT id, titulo, autor, genero, descripcion,
                   DATE_FORMAT(fecha_publicacion, '%Y-%m-%d') as fecha_publicacion
            FROM libros
            WHERE estado = 'publicado'
            AND (titulo LIKE ? OR autor LIKE ? OR genero LIKE ?)
            ORDER BY titulo
        ");

        $searchTerm = '%' . $termino . '%';
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $libros = $stmt->fetchAll();

        enviarRespuesta([
            'success' => true,
            'data' => $libros
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error en la búsqueda'], 500);
    }
}

// Obtener detalle completo de un libro
function getLibroDetalle() {
    $libroId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($libroId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de libro inválido'], 400);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT l.*, u.nombre as nombre_autor,
                   DATE_FORMAT(l.fecha_publicacion, '%Y-%m-%d') as fecha_publicacion
            FROM libros l
            LEFT JOIN usuarios u ON l.id_autor = u.id
            WHERE l.id = ? AND l.estado = 'publicado'
        ");
        $stmt->execute([$libroId]);
        $libro = $stmt->fetch();

        if (!$libro) {
            enviarRespuesta(['success' => false, 'message' => 'Libro no encontrado'], 404);
        }

        enviarRespuesta([
            'success' => true,
            'data' => $libro
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener libro'], 500);
    }
}

// Obtener comentarios de un libro
function getComentariosLibro() {
    $libroId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($libroId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de libro inválido'], 400);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT c.id, c.comentario, c.fecha_creacion, u.nombre as autor FROM comentarios c
                                 INNER JOIN usuarios u ON c.id_usuario = u.id
                                 WHERE c.id_libro = ?
                                 ORDER BY c.fecha_creacion DESC");
        $stmt->execute([$libroId]);
        $comentarios = $stmt->fetchAll();

        enviarRespuesta([
            'success' => true,
            'data' => $comentarios
        ]);
    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener comentarios'], 500);
    }
}

// Comentar en un libro y notificar al autor
function comentarLibro() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $libroId = isset($data['libro_id']) ? (int)$data['libro_id'] : 0;
    $comentario = isset($data['comentario']) ? trim($data['comentario']) : '';

    if ($libroId <= 0 || empty($comentario)) {
        enviarRespuesta(['success' => false, 'message' => 'ID de libro y comentario son requeridos'], 400);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT id_autor, titulo FROM libros WHERE id = ? AND estado = 'publicado'");
        $stmt->execute([$libroId]);
        $libro = $stmt->fetch();

        if (!$libro) {
            enviarRespuesta(['success' => false, 'message' => 'Libro no encontrado'], 404);
        }

        $stmt = $conn->prepare("INSERT INTO comentarios (id_libro, id_usuario, comentario) VALUES (?, ?, ?)");
        $stmt->execute([$libroId, $usuario['id'], $comentario]);

        if ($libro['id_autor'] && $libro['id_autor'] != $usuario['id']) {
            $tituloNotificacion = 'Tienes un nuevo comentario';
            $mensajeNotificacion = sprintf(
                'El lector %s comentó en tu libro "%s".',
                $usuario['nombre'],
                $libro['titulo']
            );

            $stmt = $conn->prepare("INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo) VALUES (?, ?, ?, 'info')");
            $stmt->execute([$libro['id_autor'], $tituloNotificacion, $mensajeNotificacion]);
        }

        enviarRespuesta([
            'success' => true,
            'message' => 'Comentario agregado correctamente'
        ], 201);
    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al guardar comentario'], 500);
    }
}

// Obtener biblioteca personal del usuario
function getBibliotecaPersonal() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT l.id, l.titulo, l.autor, l.genero,
                   DATE_FORMAT(bp.fecha_guardado, '%Y-%m-%d') as fecha_guardado
            FROM libros l
            INNER JOIN biblioteca_personal bp ON l.id = bp.id_libro
            WHERE bp.id_usuario = ?
            ORDER BY bp.fecha_guardado DESC
        ");
        $stmt->execute([$usuario['id']]);
        $libros = $stmt->fetchAll();

        enviarRespuesta([
            'success' => true,
            'data' => $libros
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener biblioteca'], 500);
    }
}

// Guardar libro en biblioteca personal
function guardarLibroBiblioteca() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $libroId = isset($data['libro_id']) ? (int)$data['libro_id'] : 0;

    if ($libroId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de libro inválido'], 400);
    }

    $conn = getDBConnection();

    try {
        // Verificar que el libro existe
        $stmt = $conn->prepare("SELECT id FROM libros WHERE id = ? AND estado = 'publicado'");
        $stmt->execute([$libroId]);
        if (!$stmt->fetch()) {
            enviarRespuesta(['success' => false, 'message' => 'Libro no encontrado'], 404);
        }

        // Intentar guardar en biblioteca (evita duplicados con UNIQUE KEY)
        $stmt = $conn->prepare("
            INSERT IGNORE INTO biblioteca_personal (id_usuario, id_libro)
            VALUES (?, ?)
        ");
        $stmt->execute([$usuario['id'], $libroId]);

        if ($stmt->rowCount() > 0) {
            enviarRespuesta([
                'success' => true,
                'message' => 'Libro guardado en tu biblioteca'
            ]);
        } else {
            enviarRespuesta([
                'success' => false,
                'message' => 'El libro ya está en tu biblioteca'
            ], 409);
        }

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al guardar libro'], 500);
    }
}

// Quitar libro de biblioteca personal
function quitarLibroBiblioteca() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $libroId = isset($_GET['libro_id']) ? (int)$_GET['libro_id'] : 0;

    if ($libroId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de libro inválido'], 400);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            DELETE FROM biblioteca_personal
            WHERE id_usuario = ? AND id_libro = ?
        ");
        $stmt->execute([$usuario['id'], $libroId]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Libro removido de tu biblioteca'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al quitar libro'], 500);
    }
}

// =========================================
// FUNCIONES PARA OBRAS (ESCRITORES)
// =========================================

// Obtener obras del usuario actual (escritor)
function getObrasUsuario() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT id, titulo, estado,
                   DATE_FORMAT(fecha_publicacion, '%Y-%m-%d') as fecha_publicacion,
                   CASE
                       WHEN contenido IS NOT NULL THEN LENGTH(contenido) - LENGTH(REPLACE(contenido, ' ', '')) + 1
                       ELSE 0
                   END as capitulos
            FROM libros
            WHERE id_autor = ?
            ORDER BY fecha_publicacion DESC
        ");
        $stmt->execute([$usuario['id']]);
        $obras = $stmt->fetchAll();

        enviarRespuesta([
            'success' => true,
            'data' => $obras
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al obtener obras'], 500);
    }
}

// Crear nueva obra
function crearObra() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    if ($usuario['rol'] !== 'escritor') {
        enviarRespuesta(['success' => false, 'message' => 'Solo los escritores pueden crear obras'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['titulo'])) {
        enviarRespuesta(['success' => false, 'message' => 'Título es requerido'], 400);
    }

    $titulo = sanitizar($data['titulo']);
    $genero = isset($data['genero']) ? sanitizar($data['genero']) : '';
    $descripcion = isset($data['descripcion']) ? sanitizar($data['descripcion']) : '';

    if (strlen($titulo) < 1) {
        enviarRespuesta(['success' => false, 'message' => 'El título no puede estar vacío'], 400);
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            INSERT INTO libros (titulo, autor, genero, descripcion, id_autor, estado)
            VALUES (?, ?, ?, ?, ?, 'borrador')
        ");
        $stmt->execute([$titulo, $usuario['nombre'], $genero, $descripcion, $usuario['id']]);

        $obraId = $conn->lastInsertId();

        enviarRespuesta([
            'success' => true,
            'message' => 'Obra creada exitosamente',
            'data' => ['obra_id' => $obraId]
        ], 201);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al crear obra'], 500);
    }
}

// Actualizar obra existente
function actualizarObra() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $obraId = isset($data['obra_id']) ? (int)$data['obra_id'] : 0;

    if ($obraId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de obra inválido'], 400);
    }

    $conn = getDBConnection();

    // Verificar que la obra pertenece al usuario
    $stmt = $conn->prepare("SELECT id FROM libros WHERE id = ? AND id_autor = ?");
    $stmt->execute([$obraId, $usuario['id']]);
    if (!$stmt->fetch()) {
        enviarRespuesta(['success' => false, 'message' => 'Obra no encontrada o no tienes permisos'], 404);
    }

    $updates = [];
    $params = [];

    if (isset($data['titulo'])) {
        $updates[] = "titulo = ?";
        $params[] = sanitizar($data['titulo']);
    }

    if (isset($data['genero'])) {
        $updates[] = "genero = ?";
        $params[] = sanitizar($data['genero']);
    }

    if (isset($data['descripcion'])) {
        $updates[] = "descripcion = ?";
        $params[] = sanitizar($data['descripcion']);
    }

    if (isset($data['contenido'])) {
        $updates[] = "contenido = ?";
        $params[] = $data['contenido']; // No sanitizar contenido largo
    }

    if (isset($data['estado']) && in_array($data['estado'], ['borrador', 'revision', 'publicado'])) {
        $updates[] = "estado = ?";
        $params[] = $data['estado'];
    }

    if (empty($updates)) {
        enviarRespuesta(['success' => false, 'message' => 'No hay campos para actualizar'], 400);
    }

    $params[] = $obraId;

    try {
        $stmt = $conn->prepare("
            UPDATE libros SET " . implode(', ', $updates) . " WHERE id = ?
        ");
        $stmt->execute($params);

        enviarRespuesta([
            'success' => true,
            'message' => 'Obra actualizada exitosamente'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al actualizar obra'], 500);
    }
}

// Eliminar obra
function eliminarObra() {
    $usuario = getUsuarioActual();
    if (!$usuario) {
        enviarRespuesta(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $obraId = isset($_GET['obra_id']) ? (int)$_GET['obra_id'] : 0;

    if ($obraId <= 0) {
        enviarRespuesta(['success' => false, 'message' => 'ID de obra inválido'], 400);
    }

    $conn = getDBConnection();

    try {
        // Verificar que la obra pertenece al usuario y no está publicada
        $stmt = $conn->prepare("
            SELECT id FROM libros
            WHERE id = ? AND id_autor = ? AND estado != 'publicado'
        ");
        $stmt->execute([$obraId, $usuario['id']]);

        if (!$stmt->fetch()) {
            enviarRespuesta(['success' => false, 'message' => 'Obra no encontrada, no tienes permisos o está publicada'], 404);
        }

        // Eliminar obra
        $stmt = $conn->prepare("DELETE FROM libros WHERE id = ?");
        $stmt->execute([$obraId]);

        enviarRespuesta([
            'success' => true,
            'message' => 'Obra eliminada exitosamente'
        ]);

    } catch (Exception $e) {
        enviarRespuesta(['success' => false, 'message' => 'Error al eliminar obra'], 500);
    }
}
?>