# 🚀 DualLibro - Guía de Instalación Rápida

## ⚡ Instalación Local (XAMPP)

### 1. **Requisitos**
- XAMPP (Apache + MySQL)
- PHP 7.4+ (XAMPP trae PHP 8.2)
- Git (opcional, para actualizaciones)

### 2. **Pasos de Instalación**

#### a) Descargar el proyecto
```bash
cd C:\xampp\htdocs
git clone https://github.com/Haziel1189/DualLibro.git
cd DualLibro
```

#### b) Iniciar XAMPP
- Abre el panel de XAMPP
- Inicia Apache y MySQL

#### c) Ejecutar el instalador
- Ve a: `http://localhost:8080/DualLibro/install.php`
- Presiona Enter o recarga la página
- Verás un mensaje de confirmación ✓

#### d) ¡Listo!
- Ve a: `http://localhost:8080/DualLibro/InicioSesion.html`
- Prueba con usuario de ejemplo:
  - **Usuario:** juan
  - **Contraseña:** 123456

---

## 🌐 Instalación en InfinityFree (o cualquier hosting)

### 1. **Crear cuenta en InfinityFree**
- Ve a: https://www.infinityfree.net/
- Regístrate gratis

### 2. **Subir archivos**
- Usa File Manager en tu panel de control
- Sube TODOS los archivos del proyecto (excepto `.git`, `node_modules`)

### 3. **Primera instalación**
- En el navegador, ve a: `https://tudominio.infinityfree.com/install.php`
- Espera a que se instale (~3 segundos)
- Ya está todo listo ✓

### 4. **Acceder a la app**
- Ve a: `https://tudominio.infinityfree.com/InicioSesion.html`
- Regístrate o inicia sesión

---

## 🔄 Actualizaciones Posteriores

### Desde Local (XAMPP):
```bash
cd C:\xampp\htdocs\DualLibro
git pull origin main
```

### En InfinityFree:
- Si usaste Git: Reconecta el repo con SSH
- Si usaste FTP: Sube solo los archivos cambiados

---

## 📁 Estructura del Proyecto

```
DualLibro/
├── install.php                 # Auto-instalador (ejecutar solo 1 vez)
├── .installed                  # Flag de instalación (se crea automáticamente)
├── config.php                  # Configuración de BD
├── auth.php                    # API de autenticación
├── libros.php                  # API de libros y comentarios
├── notificaciones.php          # API de notificaciones
├── InicioSesion.html           # Página de login
├── Registro.html               # Página de registro
├── Lector/
│   ├── InicioLector.html
│   ├── lectura.html            # Con sistema de comentarios
│   ├── Correo.html             # Notificaciones
│   └── lector.js
├── Escritor/
│   ├── InicioEscritor.html
│   ├── Correo.html             # Notificaciones
│   └── escritor.js
└── css/                        # Estilos responsivos
```

---

## 🔧 Características

✅ **Autenticación** - Login/registro con username + password  
✅ **Biblioteca Personal** - Guardar libros favoritos  
✅ **Sistema de Comentarios** - Comentar en libros  
✅ **Notificaciones** - Recibe alertas de comentarios  
✅ **Responsivo** - Funciona en móvil, tablet, desktop  
✅ **Auto-instalación** - Sin ejecutar SQL manualmente  

---

## 🆘 Solucionar Problemas

### "Error de conexión a BD"
- Abre `config.php`
- Verifica que `DB_HOST`, `DB_USER`, `DB_PASS` sean correctos

### "install.php ya fue ejecutado"
- Acceso a la BD está bloqueado
- Elimina el archivo `.installed` si quieres reinstalar

### "Página en blanco"
- Revisa la consola del navegador (F12)
- Revisa los logs de PHP en XAMPP

---

## 📞 Soporte

Para reportar bugs o mejorar DualLibro:
- GitHub Issues: https://github.com/Haziel1189/DualLibro/issues
- Email: haziel1189@example.com

---

**Versión:** 1.1.0  
**Última actualización:** Mayo 2026  
**Licencia:** MIT
