// =========================================
// registro.js - Funcionalidad de registro de usuarios
// =========================================

// Función de registro
document.getElementById('createAccountBtn').addEventListener('click', async function() {
    const nombre = document.getElementById('regUsername').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value.trim();
    const rol = document.getElementById('regRole').value;

    // Validaciones básicas
    if (!nombre || !email || !password || !rol) {
        mostrarMensaje('Por favor, llene todos los campos.', 'error');
        return;
    }

    if (nombre.length < 2) {
        mostrarMensaje('El nombre debe tener al menos 2 caracteres.', 'error');
        return;
    }

    if (!validarEmail(email)) {
        mostrarMensaje('Por favor, ingrese un email válido.', 'error');
        return;
    }

    if (password.length < 6) {
        mostrarMensaje('La contraseña debe tener al menos 6 caracteres.', 'error');
        return;
    }

    if (rol !== 'lector' && rol !== 'escritor') {
        mostrarMensaje('Por favor, seleccione un rol válido.', 'error');
        return;
    }

    // Mostrar loading
    const btn = this;
    const textoOriginal = btn.textContent;
    btn.textContent = 'Creando cuenta...';
    btn.disabled = true;

    try {
        const response = await fetch('auth.php?action=register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nombre: nombre,
                email: email,
                password: password,
                rol: rol
            })
        });

        const data = await response.json();

        if (data.success) {
            mostrarMensaje('Cuenta creada exitosamente. Redirigiendo...', 'success');

            // Redirigir después de un breve delay
            setTimeout(() => {
                if (rol === 'lector') {
                    window.location.href = 'Lector/InicioLector.html';
                } else if (rol === 'escritor') {
                    window.location.href = 'Escritor/InicioEscritor.html';
                }
            }, 1500);

        } else {
            mostrarMensaje(data.message || 'Error en el registro', 'error');
        }

    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
    } finally {
        // Restaurar botón
        btn.textContent = textoOriginal;
        btn.disabled = false;
    }
});

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo) {
    // Remover mensaje anterior si existe
    const mensajeAnterior = document.querySelector('.mensaje-registro');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }

    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `mensaje-registro ${tipo}`;
    mensajeDiv.textContent = mensaje;

    const form = document.querySelector('.register-form');
    form.parentNode.insertBefore(mensajeDiv, form);

    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (mensajeDiv.parentNode) {
            mensajeDiv.remove();
        }
    }, 5000);
}

// Función de validación de email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Verificar si ya está logueado al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('authToken');
    const userData = localStorage.getItem('userData');

    if (token && userData) {
        try {
            const usuario = JSON.parse(userData);
            mostrarMensaje(`Ya tienes una cuenta activa como ${usuario.nombre}. Redirigiendo...`, 'info');

            setTimeout(() => {
                if (usuario.rol === 'lector') {
                    window.location.href = 'Lector/InicioLector.html';
                } else if (usuario.rol === 'escritor') {
                    window.location.href = 'Escritor/InicioEscritor.html';
                }
            }, 2000);
        } catch (error) {
            // Token inválido, limpiar
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
        }
    }
});