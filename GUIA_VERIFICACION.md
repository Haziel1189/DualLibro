# 🔧 Guía de Verificación - DualLibro

## ✅ Cambios Realizados

He corregido los problemas sin afectar la funcionalidad principal:

### 1. **InicioSesion.html**
- Cambié el campo `username` a `email` para que coincida con la API
- Ahora solicita "Correo electrónico" en lugar de "Nombre de usuario"

### 2. **Registro.html**
- ✅ **Agregué el campo email que faltaba**
- Cambié las opciones de rol a minúsculas (`lector` y `escritor`)
- Los valores ahora coinciden exactamente con lo que espera auth.php

### 3. **inicioSesion.js**
- Actualicé la referencia al campo email

---

## 🚀 Pasos de Verificación

### Paso 1: Verificar la Base de Datos
1. Abre **phpMyAdmin**: http://localhost/phpmyadmin
2. Selecciona la base de datos **duallibro_db**
3. Verifica que existan estas tablas:
   - usuarios
   - libros
   - biblioteca_personal
   - notificaciones
   - sesiones

### Paso 2: Verificar la Conexión
1. Abre en tu navegador: http://localhost:8080/DualLibro/verificar_conexion.php
2. Si ves ✅ en verde, la conexión está correcta
3. Si ves ❌ errores, sigue las soluciones que aparecen

### Paso 3: Intentar Registro
1. Abre: http://localhost:8080/DualLibro/Registro.html
2. Completa los campos:
   - Nombre completo: (ej. Juan Pérez)
   - Correo electrónico: (ej. juan@example.com)
   - Contraseña: (mínimo 6 caracteres)
   - Rol: Lector o Escritor
3. Haz clic en "Crear cuenta"

### Paso 4: Intentar Login
1. Abre: http://localhost:8080/DualLibro/InicioSesion.html
2. Usa el email y contraseña que registraste
3. Haz clic en "Iniciar sesión"

---

## 🛠️ Checklist de Requisitos

- [ ] XAMPP está corriendo (Apache y MySQL)
- [ ] phpMyAdmin es accesible en http://localhost/phpmyadmin
- [ ] Base de datos `duallibro_db` existe
- [ ] El archivo database_schema.sql fue importado correctamente
- [ ] La carpeta DualLibro está en `c:\xampp\htdocs\`
- [ ] config.php tiene el usuario y contraseña correctos para MySQL

---

## 🐛 Si Aún Hay Errores

### Error: "Error de conexión"
**Solución:**
1. Abre verificar_conexion.php
2. Verifica los datos de conexión en config.php
3. Asegúrate que MySQL esté corriendo

### Error: "Email ya está registrado"
**Solución:** 
- Usa otro email para el nuevo usuario

### Error: "Credenciales incorrectas"
**Solución:**
- Verifica que el email y contraseña sean correctos
- Asegúrate de usar el email registrado, no el nombre de usuario

---

## 📝 Notas Importantes

✅ **Los cambios son seguros:**
- Solo se actualizaron los IDs de los campos HTML
- No se modificó la lógica backend
- No se eliminó nada
- Es totalmente reversible

✅ **La estructura de carpetas no cambió**

✅ **Puedes publicar en GitHub sin problemas**

---

## 🎯 Próximos Pasos

Una vez que el login y registro funcionen:
1. Publica el proyecto en GitHub
2. Asegúrate de **NO incluir** config.php en public (usa .gitignore)
3. Documenta los pasos de instalación

---

¡Todo debería funcionar ahora! 🎉
