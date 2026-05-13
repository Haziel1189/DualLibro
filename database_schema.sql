-- =========================================
-- DualLibro Database Schema
-- Base de datos para la aplicación de libros
-- =========================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS duallibro_db;
USE duallibro_db;

-- =========================================
-- TABLA: usuarios
-- =========================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('lector', 'escritor') DEFAULT 'lector',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE
);

-- =========================================
-- TABLA: libros
-- =========================================
CREATE TABLE libros (
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
);

-- =========================================
-- TABLA: biblioteca_personal
-- =========================================
CREATE TABLE biblioteca_personal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_guardado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libros(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_libro (id_usuario, id_libro)
);

-- =========================================
-- TABLA: notificaciones
-- =========================================
CREATE TABLE notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =========================================
-- TABLA: comentarios
-- =========================================
CREATE TABLE comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_libro INT NOT NULL,
    id_usuario INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_libro) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =========================================
-- TABLA: sesiones
-- =========================================
CREATE TABLE sesiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =========================================
-- ÍNDICES PARA MEJORAR PERFORMANCE
-- =========================================
CREATE UNIQUE INDEX idx_usuarios_nombre ON usuarios(nombre);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_rol ON usuarios(rol);
CREATE INDEX idx_libros_autor ON libros(id_autor);
CREATE INDEX idx_libros_genero ON libros(genero);
CREATE INDEX idx_libros_estado ON libros(estado);
CREATE INDEX idx_biblioteca_usuario ON biblioteca_personal(id_usuario);
CREATE INDEX idx_notificaciones_usuario ON notificaciones(id_usuario);
CREATE INDEX idx_notificaciones_leida ON notificaciones(leida);
CREATE INDEX idx_comentarios_libro ON comentarios(id_libro);
CREATE INDEX idx_comentarios_usuario ON comentarios(id_usuario);
CREATE INDEX idx_sesiones_token ON sesiones(token);
CREATE INDEX idx_sesiones_usuario ON sesiones(id_usuario);

-- =========================================
-- DATOS DE EJEMPLO
-- =========================================

-- Usuarios de ejemplo
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Juan Pérez', 'juan@example.com', '$2y$10$hashedpassword1', 'lector'),
('María García', 'maria@example.com', '$2y$10$hashedpassword2', 'escritor'),
('Carlos Rodríguez', 'carlos@example.com', '$2y$10$hashedpassword3', 'lector'),
('Ana López', 'ana@example.com', '$2y$10$hashedpassword4', 'escritor');

-- Libros de ejemplo
INSERT INTO libros (titulo, autor, genero, descripcion, contenido, id_autor, estado) VALUES
('Don Quijote de la Mancha', 'Miguel de Cervantes', 'Clásico',
 'La obra maestra de Cervantes sobre las aventuras de un caballero andante.',
 'En un lugar de la Mancha, de cuyo nombre no quiero acordarme, no ha mucho tiempo que vivía un hidalgo...',
 NULL, 'publicado'),

('1984', 'George Orwell', 'Ficción Distópica',
 'Una novela distópica sobre un régimen totalitario y la vigilancia masiva.',
 'Era un día frío y luminoso de abril y los relojes daban las trece...',
 NULL, 'publicado'),

('Cien Años de Soledad', 'Gabriel García Márquez', 'Realismo Mágico',
 'La saga de la familia Buendía en el pueblo de Macondo.',
 'Muchos años después, frente al pelotón de fusilamiento, el coronel Aureliano Buendía...',
 NULL, 'publicado'),

('El Principito', 'Antoine de Saint-Exupéry', 'Infantil',
 'Un cuento filosófico sobre la amistad y el amor.',
 'Cuando yo tenía seis años vi una vez una lámina extraordinaria...',
 NULL, 'publicado'),

('Ficciones', 'Jorge Luis Borges', 'Cuentos',
 'Colección de cuentos filosóficos y metafísicos.',
 'El jardín de senderos que se bifurcan...',
 NULL, 'publicado'),

('La Metamorfosis', 'Franz Kafka', 'Ficción',
 'La transformación de Gregor Samsa en un insecto gigante.',
 'Una mañana, al despertar de sueños intranquilos...',
 NULL, 'publicado'),

('Orgullo y Prejuicio', 'Jane Austen', 'Romance',
 'La historia de Elizabeth Bennet y el señor Darcy.',
 'Es una verdad universalmente reconocida...',
 NULL, 'publicado'),

('El Hobbit', 'J.R.R. Tolkien', 'Fantasía',
 'Las aventuras de Bilbo Bolsón en la Tierra Media.',
 'En un agujero en el suelo vivía un hobbit...',
 NULL, 'publicado'),

('El Gran Gatsby', 'F. Scott Fitzgerald', 'Novela',
 'La historia del millonario Jay Gatsby y sus sueños americanos.',
 'En mi juventud más vulnerable, mi padre me dio un consejo...',
 NULL, 'publicado'),

('Mujercitas', 'Louisa May Alcott', 'Clásico',
 'Las aventuras de las cuatro hermanas March durante la Guerra Civil.',
 'Nuestra historia comienza en una Navidad gris...',
 NULL, 'publicado'),

('Jane Eyre', 'Charlotte Brontë', 'Romance Gótico',
 'La historia de la huérfana Jane Eyre y su amor por Rochester.',
 'No había nada notable en los alrededores...',
 NULL, 'publicado'),

('Los Miserables', 'Victor Hugo', 'Clásico Épico',
 'La historia de Jean Valjean y su redención.',
 'En 1815, Monsieur Charles-François-Bienvenu...',
 NULL, 'publicado'),

('Crimen y Castigo', 'Fiódor Dostoievski', 'Psicológico',
 'La historia de Raskólnikov y su crimen.',
 'A principios de julio, con un calor sofocante...',
 NULL, 'publicado'),

('El Conde de Montecristo', 'Alejandro Dumas', 'Aventura',
 'La venganza de Edmond Dantès.',
 'El 24 de febrero de 1815...',
 NULL, 'publicado'),

('La Revolución Silenciosa', 'Ayn Rand', 'Filosofía',
 'Una exploración de las ideas filosóficas de Ayn Rand.',
 'La filosofía de Ayn Rand es un sistema integrado...',
 NULL, 'publicado'),

('El Juego de Ender', 'Orson Scott Card', 'Ciencia Ficción',
 'La formación de un niño genio para salvar a la humanidad.',
 'El monitor de Ender colgaba en el pasillo...',
 NULL, 'publicado'),

('Fundación', 'Isaac Asimov', 'Ciencia Ficción',
 'La caída y reconstrucción del Imperio Galáctico.',
 'HARI SELDON —nacido en el año 11.988 de la Era Galáctica...',
 NULL, 'publicado'),

('Dune', 'Frank Herbert', 'Ciencia Ficción Épica',
 'La lucha por el control del planeta Arrakis.',
 'En el principio fue el verbo...',
 NULL, 'publicado'),

('El Retrato de Dorian Gray', 'Oscar Wilde', 'Gótico',
 'La corrupción moral a través de un retrato mágico.',
 'El estudio era muy grande y cómodo...',
 NULL, 'publicado'),

('Sherlock Holmes', 'Arthur Conan Doyle', 'Misterio',
 'Las aventuras del detective más famoso del mundo.',
 'Sherlock Holmes, que solía levantarse muy tarde...',
 NULL, 'publicado');

-- Biblioteca personal de ejemplo
INSERT INTO biblioteca_personal (id_usuario, id_libro) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), -- Juan Pérez
(2, 7), (2, 8), (2, 9), (2, 10), (2, 11), -- María García
(3, 12), (3, 13), (3, 14), (3, 15), -- Carlos Rodríguez
(4, 16), (4, 17), (4, 18), (4, 19), (4, 20); -- Ana López

-- Notificaciones de ejemplo
INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida) VALUES
(1, '¡Nuevo libro disponible!', 'Se agregó "El viaje" a tu catálogo', 'info', FALSE),
(1, 'Recomendación personal', 'Te recomendamos leer "Cien Años de Soledad"', 'info', FALSE),
(1, 'Actualización de contenido', 'Tu biblioteca se ha actualizado', 'success', TRUE),
(1, 'Bienvenido a DualLibro', 'Gracias por crear tu cuenta', 'success', TRUE),
(2, 'Tu obra fue comentada', 'Un lector comentó en "El Viaje Inesperado"', 'info', FALSE),
(2, 'Nuevo seguidor', '"Juan González" comenzó a seguir tu perfil', 'success', FALSE),
(2, 'Recordatorio de revisión', '"Sueños Nocturnos" está lista para revisar', 'warning', TRUE),
(2, 'Bienvenido como Escritor', 'Ahora puedes crear y compartir tus obras', 'success', TRUE);

-- =========================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- =========================================

-- Procedimiento para obtener libros por usuario
DELIMITER //
CREATE PROCEDURE obtener_libros_usuario(IN usuario_id INT)
BEGIN
    SELECT l.*, bp.fecha_guardado
    FROM libros l
    INNER JOIN biblioteca_personal bp ON l.id = bp.id_libro
    WHERE bp.id_usuario = usuario_id
    ORDER BY bp.fecha_guardado DESC;
END //
DELIMITER ;

-- Procedimiento para obtener obras por autor
DELIMITER //
CREATE PROCEDURE obtener_obras_autor(IN autor_id INT)
BEGIN
    SELECT * FROM libros
    WHERE id_autor = autor_id
    ORDER BY fecha_publicacion DESC;
END //
DELIMITER ;

-- Procedimiento para buscar libros
DELIMITER //
CREATE PROCEDURE buscar_libros(IN termino VARCHAR(255))
BEGIN
    SELECT * FROM libros
    WHERE titulo LIKE CONCAT('%', termino, '%')
       OR autor LIKE CONCAT('%', termino, '%')
       OR genero LIKE CONCAT('%', termino, '%')
    ORDER BY titulo;
END //
DELIMITER ;

-- Procedimiento para obtener notificaciones no leídas
DELIMITER //
CREATE PROCEDURE obtener_notificaciones_no_leidas(IN usuario_id INT)
BEGIN
    SELECT * FROM notificaciones
    WHERE id_usuario = usuario_id AND leida = FALSE
    ORDER BY fecha_creacion DESC;
END //
DELIMITER ;

-- =========================================
-- VISTAS ÚTILES
-- =========================================

-- Vista de estadísticas de usuarios
CREATE VIEW vista_estadisticas_usuarios AS
SELECT
    u.rol,
    COUNT(*) as total_usuarios,
    AVG(TIMESTAMPDIFF(DAY, u.fecha_registro, NOW())) as dias_promedio_registro
FROM usuarios u
WHERE u.activo = TRUE
GROUP BY u.rol;

-- Vista de libros más populares
CREATE VIEW vista_libros_populares AS
SELECT
    l.*,
    COUNT(bp.id) as veces_guardado
FROM libros l
LEFT JOIN biblioteca_personal bp ON l.id = bp.id_libro
GROUP BY l.id
ORDER BY veces_guardado DESC;

-- Vista de actividad reciente
CREATE VIEW vista_actividad_reciente AS
SELECT
    'libro_guardado' as tipo_actividad,
    bp.fecha_guardado as fecha,
    u.nombre as usuario,
    l.titulo as descripcion
FROM biblioteca_personal bp
JOIN usuarios u ON bp.id_usuario = u.id
JOIN libros l ON bp.id_libro = l.id
UNION ALL
SELECT
    'notificacion_creada' as tipo_actividad,
    n.fecha_creacion as fecha,
    u.nombre as usuario,
    n.titulo as descripcion
FROM notificaciones n
JOIN usuarios u ON n.id_usuario = u.id
ORDER BY fecha DESC
LIMIT 50;