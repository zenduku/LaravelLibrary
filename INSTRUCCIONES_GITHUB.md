# 📤 Instrucciones para Subir el Proyecto Laravel a GitHub

## 📋 Pasos a Seguir

### Paso 1: Verificar que Git esté instalado

Abre PowerShell o Terminal y ejecuta:
```bash
git --version
```

Si no está instalado, descárgalo desde: https://git-scm.com/download/win

---

### Paso 2: Inicializar el repositorio Git

Navega a la carpeta del proyecto y ejecuta:

```bash
cd "C:\Users\juan_\Documents\Github\Prueba Tecnica\Library\LaravelLibrary"
git init
```

---

### Paso 3: Verificar el archivo .gitignore

El archivo `.gitignore` ya está configurado correctamente y excluye:
- ✅ `.env` (archivo de configuración con datos sensibles)
- ✅ `vendor/` (dependencias de Composer)
- ✅ `node_modules/` (dependencias de NPM)
- ✅ Archivos de cache y logs

**IMPORTANTE:** Nunca subas el archivo `.env` a GitHub porque contiene información sensible.

---

### Paso 4: Agregar todos los archivos al staging

```bash
git add .
```

---

### Paso 5: Crear el commit inicial

```bash
git commit -m "Initial commit: Laravel 5.8 Library API project"
```

O si prefieres un mensaje más descriptivo:
```bash
git commit -m "Initial commit: Laravel 5.8 Library API - Prueba Técnica

- Configuración inicial de Laravel 5.8
- Base de datos SQLite configurada
- Estructura base del proyecto lista"
```

---

### Paso 6: Crear el repositorio en GitHub

1. Ve a [GitHub.com](https://github.com) e inicia sesión
2. Haz clic en el botón **"+"** en la esquina superior derecha
3. Selecciona **"New repository"**
4. Completa el formulario:
   - **Repository name:** `LaravelLibrary` (o el nombre que prefieras)
   - **Description:** `API REST Laravel 5.8 - Sistema de Biblioteca (Autores y Libros)`
   - **Visibility:** Selecciona **Public** (según los requisitos de la prueba)
   - **NO marques** "Initialize this repository with a README" (ya tenemos archivos)
   - **NO selecciones** .gitignore ni license (ya los tenemos)
5. Haz clic en **"Create repository"**

---

### Paso 7: Conectar el repositorio local con GitHub

GitHub te mostrará comandos después de crear el repositorio. Ejecuta estos comandos (reemplaza `TU_USUARIO` con tu usuario de GitHub):

```bash
git remote add origin https://github.com/TU_USUARIO/LaravelLibrary.git
```

O si prefieres usar SSH (si tienes configuradas las claves SSH):
```bash
git remote add origin git@github.com:TU_USUARIO/LaravelLibrary.git
```

---

### Paso 8: Verificar la conexión remota

```bash
git remote -v
```

Deberías ver algo como:
```
origin  https://github.com/TU_USUARIO/LaravelLibrary.git (fetch)
origin  https://github.com/TU_USUARIO/LaravelLibrary.git (push)
```

---

### Paso 9: Subir el código a GitHub

```bash
git branch -M main
git push -u origin main
```

Si GitHub te pide autenticación:
- **Usuario:** Tu usuario de GitHub
- **Contraseña:** Usa un **Personal Access Token** (no tu contraseña normal)

### 🔑 Crear Personal Access Token (si es necesario)

Si GitHub te pide autenticación:

1. Ve a GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Haz clic en **"Generate new token (classic)"**
3. Dale un nombre descriptivo (ej: "LaravelLibrary")
4. Selecciona los scopes: **repo** (marca todo lo relacionado con repositorios)
5. Haz clic en **"Generate token"**
6. **Copia el token** (solo se muestra una vez)
7. Úsalo como contraseña cuando Git te la pida

---

### Paso 10: Verificar que se subió correctamente

1. Ve a tu repositorio en GitHub: `https://github.com/TU_USUARIO/LaravelLibrary`
2. Deberías ver todos los archivos del proyecto
3. **Verifica que NO esté el archivo `.env`** (debe estar en .gitignore)

---

## 📝 Comandos Rápidos (Resumen)

```bash
# 1. Navegar al proyecto
cd "C:\Users\juan_\Documents\Github\Prueba Tecnica\Library\LaravelLibrary"

# 2. Inicializar Git
git init

# 3. Agregar archivos
git add .

# 4. Crear commit
git commit -m "Initial commit: Laravel 5.8 Library API project"

# 5. Agregar remoto (reemplaza TU_USUARIO)
git remote add origin https://github.com/TU_USUARIO/LaravelLibrary.git

# 6. Subir a GitHub
git branch -M main
git push -u origin main
```

---

## ⚠️ Importante

### Archivos que NO deben subirse:
- ✅ `.env` - Ya está en .gitignore
- ✅ `vendor/` - Ya está en .gitignore
- ✅ `node_modules/` - Ya está en .gitignore

### Archivo .env.example

Laravel incluye un archivo `.env.example` que SÍ debe subirse. Este archivo sirve como plantilla para otros desarrolladores.

---

## 🔄 Para futuros cambios

Después del commit inicial, para subir cambios futuros:

```bash
git add .
git commit -m "Descripción de los cambios"
git push
```

---

## 📚 Recursos Adicionales

- [Documentación de Git](https://git-scm.com/doc)
- [Guía de GitHub](https://guides.github.com/)
- [Personal Access Tokens](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token)

---

## ✅ Checklist Final

- [ ] Git inicializado
- [ ] Archivos agregados al staging
- [ ] Commit inicial creado
- [ ] Repositorio creado en GitHub
- [ ] Remoto configurado
- [ ] Código subido exitosamente
- [ ] Verificado que `.env` NO está en GitHub
- [ ] Repositorio es público (según requisitos)

---

¡Listo! Tu proyecto debería estar ahora en GitHub. 🎉

