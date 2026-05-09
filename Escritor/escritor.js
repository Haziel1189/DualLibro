// =========================================
// escritor.js - Funcionalidad para páginas del escritor
// =========================================

// Configuración global
const API_BASE = '../';

// =========================================
// FUNCIONES DE AUTENTICACIÓN
// =========================================

// Función para obtener headers de autenticación
function getAuthHeaders() {
    const token = localStorage.getItem('authToken');
    return {
        'Content-Type': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
    };
}

// Función para verificar autenticación
async function verificarAutenticacion() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '../InicioSesion.html';
        return null;
    }

    try {
        const response = await fetch(`${API_BASE}auth.php?action=verify`, {
            headers: getAuthHeaders()
        });

        const data = await response.json();

        if (!data.success) {
            // Token inválido, limpiar y redirigir
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
            window.location.href = '../InicioSesion.html';
            return null;
        }

        return data.data.usuario;
    } catch (error) {
        console.error('Error verificando autenticación:', error);
        window.location.href = '../InicioSesion.html';
        return null;
    }
}

// Función para logout
async function logout() {
    try {
        await fetch(`${API_BASE}auth.php?action=logout`, {
            method: 'POST',
            headers: getAuthHeaders()
        });
    } catch (error) {
        console.error('Error en logout:', error);
    } finally {
        localStorage.removeItem('authToken');
        localStorage.removeItem('userData');
        window.location.href = '../InicioSesion.html';
    }
}

// =========================================
// FUNCIONES PARA LIBROS/OBRAS
// =========================================

// Función para cargar obras del usuario
async function cargarObrasUsuario() {
    try {
        const response = await fetch(`${API_BASE}libros.php?action=obras`, {
            headers: getAuthHeaders()
        });

        const data = await response.json();

        if (data.success) {
            return data.data;
        } else {
            console.error('Error cargando obras:', data.message);
            return [];
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        return [];
    }
}

// Función para crear nueva obra
async function crearNuevaObra(datosObra) {
    try {
        const response = await fetch(`${API_BASE}libros.php?action=crear`, {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify(datosObra)
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error creando obra:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// Función para actualizar obra
async function actualizarObra(obraId, datosObra) {
    try {
        const datos = { obra_id: obraId, ...datosObra };
        const response = await fetch(`${API_BASE}libros.php?action=actualizar`, {
            method: 'PUT',
            headers: getAuthHeaders(),
            body: JSON.stringify(datos)
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error actualizando obra:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// Función para eliminar obra
async function eliminarObra(obraId) {
    try {
        const response = await fetch(`${API_BASE}libros.php?action=eliminar&obra_id=${obraId}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error eliminando obra:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// =========================================
// FUNCIONES PARA NOTIFICACIONES
// =========================================

// Función para cargar notificaciones
async function cargarNotificaciones() {
    try {
        const response = await fetch(`${API_BASE}notificaciones.php?action=listar`, {
            headers: getAuthHeaders()
        });

        const data = await response.json();

        if (data.success) {
            return data.data;
        } else {
            console.error('Error cargando notificaciones:', data.message);
            return { notificaciones: [], total: 0 };
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        return { notificaciones: [], total: 0 };
    }
}

// Función para contar notificaciones no leídas
async function contarNotificacionesNoLeidas() {
    try {
        const response = await fetch(`${API_BASE}notificaciones.php?action=no-leidas`, {
            headers: getAuthHeaders()
        });

        const data = await response.json();

        if (data.success) {
            return data.data.no_leidas;
        } else {
            return 0;
        }
    } catch (error) {
        console.error('Error contando notificaciones:', error);
        return 0;
    }
}

// Función para marcar notificación como leída
async function marcarNotificacionLeida(notificacionId) {
    try {
        const response = await fetch(`${API_BASE}notificaciones.php?action=marcar-leida`, {
            method: 'PUT',
            headers: getAuthHeaders(),
            body: JSON.stringify({ notificacion_id: notificacionId })
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error marcando notificación:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// =========================================
// FUNCIONES PARA USUARIOS
// =========================================

// Función para cargar perfil de usuario
async function cargarPerfilUsuario() {
    try {
        const response = await fetch(`${API_BASE}usuarios.php?action=perfil`, {
            headers: getAuthHeaders()
        });

        const data = await response.json();

        if (data.success) {
            return data.data;
        } else {
            console.error('Error cargando perfil:', data.message);
            return null;
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        return null;
    }
}

// Función para actualizar perfil
async function actualizarPerfil(datos) {
    try {
        const response = await fetch(`${API_BASE}usuarios.php?action=actualizar`, {
            method: 'PUT',
            headers: getAuthHeaders(),
            body: JSON.stringify(datos)
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error actualizando perfil:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// Función para cambiar rol
async function cambiarRolUsuario(nuevoRol) {
    try {
        const response = await fetch(`${API_BASE}usuarios.php?action=cambiar-rol`, {
            method: 'PUT',
            headers: getAuthHeaders(),
            body: JSON.stringify({ nuevo_rol: nuevoRol })
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error cambiando rol:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// =========================================
// FUNCIONES DE UTILIDAD
// =========================================

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'info') {
    // Remover mensaje anterior si existe
    const mensajeAnterior = document.querySelector('.mensaje-app');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }

    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `mensaje-app ${tipo}`;
    mensajeDiv.textContent = mensaje;

    document.body.insertBefore(mensajeDiv, document.body.firstChild);

    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (mensajeDiv.parentNode) {
            mensajeDiv.remove();
        }
    }, 5000);
}

// Función para formatear fecha
function formatearFecha(fechaString) {
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Función para truncar texto
function truncarTexto(texto, maxLength = 100) {
    if (texto.length <= maxLength) return texto;
    return texto.substring(0, maxLength) + '...';
}