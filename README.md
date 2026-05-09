# DualLibro 📚

Una plataforma web completa para lectores y escritores, con sistema de autenticación, catálogo de libros, y gestión de obras literarias.

## 🚀 Características

- **Sistema de autenticación completo**: Login, registro, y gestión de sesiones
- **Dos roles de usuario**: Lector y Escritor con funcionalidades específicas
- **Catálogo de libros**: Búsqueda, filtrado, y guardado en biblioteca personal
- **Gestión de obras**: Creación, edición, y publicación de libros
- **Sistema de notificaciones**: Correos y alertas para usuarios
- **Interfaz responsiva**: Diseño moderno y adaptable a dispositivos móviles

## 🛠️ Tecnologías

### Backend
- **PHP 7.4+** con PDO para conexiones seguras a base de datos
- **MySQL** como sistema de gestión de base de datos
- **Arquitectura REST** con APIs JSON

### Frontend
- **HTML5** y **CSS3** para estructura y estilos
- **JavaScript vanilla** para interactividad
- **Fetch API** para comunicación con el backend

## 📋 Requisitos del Sistema

- Servidor web (Apache/Nginx) con soporte PHP
- MySQL 5.7+ o MariaDB 10.0+
- PHP 7.4+ con extensiones:
  - `pdo`
  - `pdo_mysql`
  - `json`
  - `mbstring`

## 🔧 Instalación y Configuración

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/duallibro.git
cd duallibro
```

### 2. Configurar la base de datos

#### Opción A: Usar el script SQL incluido
```bash
# Ejecutar el script de creación de base de datos
mysql -u root -p < database_schema.sql
```

#### Opción B: Configuración manual
1. Crear una base de datos llamada `duallibro`
2. Ejecutar las consultas SQL del archivo `database_schema.sql`

### 3. Configurar la conexión a la base de datos

Editar el archivo `config.php` con tus credenciales de base de datos:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'duallibro');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### 4. Configurar el servidor web

Asegúrate de que el directorio raíz del servidor web apunte a la carpeta del proyecto, o configura un virtual host.

**Ejemplo para Apache (httpd.conf o virtual host):**
```
<VirtualHost *:80>
    ServerName duallibro.local
    DocumentRoot "/ruta/a/duallibro"

    <Directory "/ruta/a/duallibro">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Configurar CORS (si es necesario)

Si el frontend y backend están en dominios diferentes, asegúrate de configurar los headers CORS apropiados en los archivos PHP.

## 📁 Estructura del Proyecto

```
DualLibro/
├── InicioSesion.html          # Página de login
├── inicioSesion.js            # Lógica de autenticación
├── Registro.html              # Página de registro
├── registro.js                # Lógica de registro
├── css/
│   └── estilos.css           # Estilos globales
├── Lector/                    # Páginas para lectores
│   ├── InicioLector.html
│   ├── cuenta.html
│   ├── Correo.html
│   ├── lector.js             # Funciones comunes para lectores
│   ├── cssLector/
│   └── lectura.html
├── Escritor/                  # Páginas para escritores
│   ├── InicioEscritor.html
│   ├── cuenta.html
│   ├── Correo.html
│   ├── Creacion.html
│   ├── escritor.js           # Funciones comunes para escritores
│   └── Escritor estilos/
├── auth.php                   # API de autenticación
├── libros.php                 # API de gestión de libros
├── usuarios.php               # API de gestión de usuarios
├── notificaciones.php         # API de notificaciones
├── config.php                 # Configuración global
└── database_schema.sql        # Esquema de base de datos
```

## 🔐 APIs Disponibles

### Autenticación (`auth.php`)
- `POST /auth.php?action=login` - Iniciar sesión
- `POST /auth.php?action=register` - Registrar usuario
- `POST /auth.php?action=logout` - Cerrar sesión
- `GET /auth.php?action=verify` - Verificar sesión activa

### Libros (`libros.php`)
- `GET /libros.php?action=catalogo` - Obtener catálogo completo
- `GET /libros.php?action=buscar&q=termino` - Buscar libros
- `GET /libros.php?action=detalle&id=ID` - Detalle de libro
- `GET /libros.php?action=biblioteca` - Biblioteca personal
- `POST /libros.php?action=guardar` - Guardar libro en biblioteca
- `DELETE /libros.php?action=quitar&libro_id=ID` - Quitar de biblioteca
- `GET /libros.php?action=obras` - Obras del usuario (escritores)
- `POST /libros.php?action=crear` - Crear nueva obra
- `PUT /libros.php?action=actualizar` - Actualizar obra
- `DELETE /libros.php?action=eliminar&obra_id=ID` - Eliminar obra

### Usuarios (`usuarios.php`)
- `GET /usuarios.php?action=perfil` - Perfil del usuario
- `PUT /usuarios.php?action=actualizar` - Actualizar perfil
- `PUT /usuarios.php?action=cambiar-rol` - Cambiar rol
- `PUT /usuarios.php?action=cambiar-password` - Cambiar contraseña
- `DELETE /usuarios.php?action=desactivar` - Desactivar cuenta

### Notificaciones (`notificaciones.php`)
- `GET /notificaciones.php?action=listar` - Listar notificaciones
- `GET /notificaciones.php?action=no-leidas` - Contar no leídas
- `PUT /notificaciones.php?action=marcar-leida` - Marcar como leída
- `PUT /notificaciones.php?action=marcar-todas-leidas` - Marcar todas como leídas
- `POST /notificaciones.php?action=crear` - Crear notificación
- `DELETE /notificaciones.php?action=eliminar&notificacion_id=ID` - Eliminar notificación

## 🎯 Uso de la Aplicación

### Para Lectores
1. Regístrate como "lector" o inicia sesión
2. Explora el catálogo de libros
3. Usa la búsqueda para encontrar libros específicos
4. Guarda libros en tu biblioteca personal
5. Cambia a rol "escritor" si deseas crear contenido

### Para Escritores
1. Regístrate como "escritor" o cambia tu rol desde la cuenta
2. Crea nuevas obras usando el editor integrado
3. Gestiona tus obras (editar, publicar, eliminar)
4. Recibe notificaciones sobre tu actividad

## 🔒 Seguridad

- **Hashing de contraseñas**: Usando `password_hash()` con algoritmo bcrypt
- **Validación de tokens**: Sistema de sesiones con tokens JWT-like
- **Sanitización de inputs**: Prevención de inyección SQL y XSS
- **Validación de datos**: Verificación de emails, longitudes, y formatos

## 🐛 Solución de Problemas

### Error de conexión a BD
- Verificar credenciales en `config.php`
- Asegurar que la base de datos existe y está corriendo
- Revisar permisos del usuario de BD

### Problemas de CORS
- Configurar headers apropiados en archivos PHP
- Verificar configuración del servidor web

### Errores de JavaScript
- Verificar que los archivos JS se carguen correctamente
- Revisar consola del navegador para errores
- Asegurar que las APIs respondan correctamente

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o preguntas:
- Abre un issue en GitHub
- Revisa la documentación de APIs
- Verifica los logs de error del servidor

---

¡Disfruta creando y leyendo con DualLibro! 📖✍️</content>
<parameter name="filePath">g:\DualLibro\README.md