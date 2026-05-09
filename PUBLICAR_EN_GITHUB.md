# 📚 GUÍA PUBLICAR EN GITHUB

## Paso 1: Crear un Repositorio en GitHub

1. Abre https://github.com y **inicia sesión** (si no tienes cuenta, crea una)
2. Haz clic en el icono **+** (arriba a la derecha) → **New repository**
3. **Nombre del repositorio**: `DualLibro`
4. **Descripción**: "Plataforma de lectura y escritura responsiva"
5. **Privado o Público**: Público (para que otros lo vean)
6. **No selecciones** "Initialize this repository with..."
7. Haz clic en **Create repository**

## Paso 2: Inicializar Git en tu Carpeta Local

Abre PowerShell en tu carpeta `C:\xampp\htdocs\DualLibro`:

```powershell
# 1. Inicializar git
git init

# 2. Agregar todos los archivos (respeta .gitignore)
git add .

# 3. Verificar qué se va a subir
git status

# 4. Hacer commit inicial
git commit -m "Inicial commit: DualLibro v1.0"
```

## Paso 3: Conectar con GitHub

GitHub te mostrará 3 comandos después de crear el repositorio. Usa ESTOS (reemplaza con tus datos):

```powershell
# 1. Agregar el repositorio remoto
git branch -M main
git remote add origin https://github.com/TU_USUARIO/DualLibro.git

# 2. Hacer push (subir los archivos)
git push -u origin main
```

**Reemplaza:**
- `TU_USUARIO` con tu usuario de GitHub
- `DualLibro` con el nombre del repo que creaste

## Paso 4: Verificar en GitHub

1. Abre https://github.com/TU_USUARIO/DualLibro
2. **Deberías ver todos tus archivos excepto:**
   - ❌ `config.php` (no debe estar, está en .gitignore)
   - ❌ `verificar_conexion.php` (opcional)
3. Si ves que `config.php` SÍ está subido → **ES UN PROBLEMA**, ve a Paso 5

## Paso 5: Si config.php Está Subido (IMPORTANTE)

**⚠️ ESTO ES UN RIESGO DE SEGURIDAD**

Para eliminar un archivo ya subido:

```powershell
# 1. Eliminar del repositorio (no localmente)
git rm --cached config.php

# 2. Hacer commit
git commit -m "Remove config.php for security"

# 3. Subir
git push origin main
```

Luego asegúrate de que `.gitignore` contenga:
```
config.php
```

## 🚀 Pasos Finales

1. **En GitHub**, va a tu repositorio → **Settings** → **Code and automation** → **Pages**
2. Selecciona **Branch: main** → **Save**
3. Tu sitio estará disponible en: `https://TU_USUARIO.github.io/DualLibro/`

## 📝 Para Futuros Cambios

Cada vez que hagas cambios:

```powershell
# 1. Ver qué cambió
git status

# 2. Agregar cambios
git add .

# 3. Hacer commit
git commit -m "Descripción del cambio"

# 4. Subir a GitHub
git push origin main
```

## 🔒 IMPORTANTE: CREDENCIALES

Asegúrate de que `config.php` NUNCA esté en GitHub. Los usuarios tendrán que:

1. Descargar el proyecto
2. Duplicar el archivo `config.php.example` → `config.php`
3. Llenar sus propias credenciales
4. Crear la BD con `database_schema.sql`

---

## Ejemplo Completo de Comandos

```powershell
# Abre PowerShell en C:\xampp\htdocs\DualLibro

cd C:\xampp\htdocs\DualLibro

# Inicializar git
git init

# Ver estado
git status

# Agregar archivos
git add .

# Commit
git commit -m "DualLibro v1.0 - Inicial commit"

# Configurar rama main
git branch -M main

# Agregar remoto (reemplaza TU_USUARIO)
git remote add origin https://github.com/TU_USUARIO/DualLibro.git

# Subir a GitHub
git push -u origin main

# Verificar el resultado
git log
```

---

¿Necesitas ayuda en algún paso? 🎯
