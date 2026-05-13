// =========================================
// inicioSesion.js - Funcionalidad de login y registro
// =========================================

// Función de login
document.getElementById('loginBtn').addEventListener('click', async function() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!username || !password) {
        mostrarMensaje('Por favor, ingrese usuario y contraseña.', 'error');
        return;
    }

    // Mostrar loading
    const btn = this;
    const textoOriginal = btn.textContent;
    btn.textContent = 'Iniciando sesión...';
    btn.disabled = true;

    try {
        const response = await fetch('auth.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nombre: username,
                password: password
            })
        });

        const data = await response.json();

        if (data.success) {
            // Guardar token y datos de usuario
            localStorage.setItem('authToken', data.data.token);
            localStorage.setItem('userData', JSON.stringify(data.data.usuario));

            mostrarMensaje('Login exitoso. Redirigiendo...', 'success');

            // Redirigir según rol después de un breve delay
            setTimeout(() => {
                if (data.data.usuario.rol === 'lector') {
                    window.location.href = 'Lector/InicioLector.html';
                } else if (data.data.usuario.rol === 'escritor') {
                    window.location.href = 'Escritor/InicioEscritor.html';
                }
            }, 1000);

        } else {
            mostrarMensaje(data.message || 'Error en el login', 'error');
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

// Función de registro
document.getElementById('registerBtn').addEventListener('click', function() {
    window.location.href = 'Registro.html';
});

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo) {
    // Remover mensaje anterior si existe
    const mensajeAnterior = document.querySelector('.mensaje-login');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }

    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `mensaje-login ${tipo}`;
    mensajeDiv.textContent = mensaje;

    const form = document.querySelector('.login-form');
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
            mostrarMensaje(`Ya estás logueado como ${usuario.nombre}. Redirigiendo...`, 'success');

            setTimeout(() => {
                if (usuario.rol === 'lector') {
                    window.location.href = 'Lector/InicioLector.html';
                } else if (usuario.rol === 'escritor') {
                    window.location.href = 'Escritor/InicioEscritor.html';
                }
            }, 1500);
        } catch (error) {
            // Token inválido, limpiar
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
        }
    }
});