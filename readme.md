# Laravel Library API - Prueba Técnica

API REST desarrollada en Laravel 5.8 para gestión de una biblioteca (Autores y Libros) con autenticación JWT.

## 📋 Requisitos

- PHP >= 7.1.3
- Composer
- SQLite (incluido en PHP)
- Extensiones PHP requeridas:
  - PDO
  - PDO_SQLite
  - OpenSSL
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - GD (opcional, para exportación XLSX avanzada)

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd LaravelLibrary
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

Copia el archivo `.env.example` a `.env`:

```bash
cp .env.example .env
```

O si estás en Windows:

```powershell
copy .env.example .env
```

### 4. Generar clave de aplicación

```bash
php artisan key:generate
```

### 5. Configurar base de datos

El proyecto está configurado para usar SQLite. El archivo de base de datos se encuentra en:

```
database/database.sqlite
```

Si el archivo no existe, créalo:

```bash
# Windows PowerShell
New-Item -Path database\database.sqlite -ItemType File -Force

# Linux/Mac
touch database/database.sqlite
```

### 6. Ejecutar migraciones

```bash
php artisan migrate
```

### 7. Generar clave JWT

```bash
php artisan jwt:secret
```

### 8. (Opcional) Instalar PhpSpreadsheet para exportación XLSX

Si deseas usar la funcionalidad de exportación a XLSX, instala PhpSpreadsheet:

```bash
composer require phpoffice/phpspreadsheet:^1.29
```

**Nota:** Requiere las extensiones PHP `gd` y `mbstring` habilitadas.

## 🔧 Configuración

### Variables de entorno importantes

En el archivo `.env`:

```env
APP_NAME=LaravelLibrary
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
# DB_DATABASE se usa automáticamente: database/database.sqlite

JWT_SECRET=tu_clave_secreta_generada
```

## 📚 Estructura del Proyecto

```
LaravelLibrary/
├── app/
│   ├── Events/              # Eventos (BookCreated, BookUpdated, BookDeleted)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/         # Controladores de la API
│   │   └── Requests/        # Form Requests (validaciones)
│   ├── Listeners/           # Listeners (UpdateAuthorBookCount)
│   ├── Author.php           # Modelo Author
│   ├── Book.php             # Modelo Book
│   └── User.php             # Modelo User
├── database/
│   ├── migrations/          # Migraciones de base de datos
│   └── database.sqlite      # Base de datos SQLite
├── routes/
│   └── api.php              # Rutas de la API
├── config/
│   ├── auth.php             # Configuración de autenticación
│   └── jwt.php              # Configuración JWT
├── tests/                   # Tests automatizados
├── POSTMAN_DOCUMENTATION.md # Documentación completa para Postman
└── readme.md                # Este archivo
```

## 🔐 Autenticación JWT

La API utiliza autenticación JWT. Todas las rutas (excepto login y register) requieren un token válido.

### Obtener token (Login)

```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Respuesta:**

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "user": {
        "id": 1,
        "name": "User Name",
        "email": "user@example.com"
    }
}
```

### Usar el token

Incluye el token en el header `Authorization`:

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## 📡 Endpoints de la API

### Autenticación (Públicas)

- `POST /api/login` - Iniciar sesión
- `POST /api/register` - Registrar nuevo usuario

### Autenticación (Protegidas)

- `POST /api/logout` - Cerrar sesión
- `GET /api/me` - Obtener usuario autenticado
- `POST /api/refresh` - Refrescar token

### Usuarios (CRUD) - Protegidas

- `GET /api/users` - Listar usuarios
- `POST /api/users` - Crear usuario
- `GET /api/users/{id}` - Obtener usuario
- `PUT /api/users/{id}` - Actualizar usuario
- `DELETE /api/users/{id}` - Eliminar usuario

### Autores (CRUD) - Protegidas

- `GET /api/authors` - Listar autores
- `POST /api/authors` - Crear autor
- `GET /api/authors/{id}` - Obtener autor
- `PUT /api/authors/{id}` - Actualizar autor
- `DELETE /api/authors/{id}` - Eliminar autor

**Ejemplo crear autor:**

```json
{
    "name": "Gabriel García Márquez"
}
```

### Libros (CRUD) - Protegidas

- `GET /api/books` - Listar libros
- `POST /api/books` - Crear libro
- `GET /api/books/{id}` - Obtener libro
- `PUT /api/books/{id}` - Actualizar libro
- `DELETE /api/books/{id}` - Eliminar libro

**Ejemplo crear libro:**

```json
{
    "title": "Cien años de soledad",
    "publication_date": 1967,
    "author_id": 1
}
```

**Nota sobre `publication_date`:**

El campo `publication_date` es **requerido** y debe ser un año (número entero, por ejemplo: `1967`). El año mínimo es 1000 y el máximo es el año actual.

**¿Por qué usar Integer en lugar de Date?**

Se utiliza `integer` (año) en lugar de `date` (fecha completa) porque:

1. **Práctica común**: Para la mayoría de los libros solo se conoce el año de publicación, no el día y mes exactos.
2. **Simplicidad**: Los usuarios solo ingresan un número (ej: `1967`) en lugar de una fecha completa.
3. **Flexibilidad**: Permite registrar libros antiguos donde solo se conoce el año aproximado.
4. **Validación simple**: Es más fácil validar un rango de años que fechas completas.
5. **Menor complejidad**: Evita problemas de formato de fecha y conversiones innecesarias.

**Nota:** El campo `books_count` del autor se actualiza automáticamente mediante Jobs cuando se crea/actualiza/elimina un libro (cumpliendo el requisito de la prueba técnica). También se calcula dinámicamente como respaldo para garantizar que siempre refleje el número real de libros asociados. Un Job de verificación corrige automáticamente cualquier desincronización.

### Exportación - Protegida

- `GET /api/export/xlsx` - Exportar autores y libros a Excel

## 🎯 Funcionalidades Implementadas

✅ Autenticación JWT completa (login, register, logout, refresh, me)
✅ CRUD completo de Usuarios
✅ CRUD completo de Autores
✅ CRUD completo de Libros
✅ Actualización automática de `books_count` mediante eventos/listeners
✅ Exportación a XLSX (requiere PhpSpreadsheet)
✅ Validaciones mediante Form Requests en todos los endpoints
✅ Manejo de excepciones JWT
✅ Códigos HTTP apropiados (200, 201, 400, 401, 404, 422, 500)
✅ Relaciones Eloquent entre modelos
✅ Tests automatizados (15 tests, 52 assertions)

## 🧪 Probar la API

### 1. Iniciar el servidor de desarrollo

```bash
php artisan serve
```

El servidor estará disponible en: `http://localhost:8000`

### 2. Usar Postman

Para una guía completa y detallada sobre cómo probar la API con Postman, consulta el archivo **[POSTMAN_DOCUMENTATION.md](POSTMAN_DOCUMENTATION.md)** que incluye:

- Configuración paso a paso de Postman
- Variables de entorno
- Ejemplos de todos los endpoints
- Scripts para guardar tokens automáticamente
- Checklist de pruebas
- Manejo de errores

### 3. Ejecutar Tests

```bash
vendor/bin/phpunit
```

O para ejecutar tests específicos:

```bash
vendor/bin/phpunit --filter AuthTest
vendor/bin/phpunit --filter AuthorTest
vendor/bin/phpunit --filter BookTest
```

## 📊 Base de Datos

### Estructura de Tablas

#### `users`

- `id` (PK)
- `name`
- `email` (unique)
- `password`
- `email_verified_at`
- `remember_token`
- `created_at`
- `updated_at`

#### `authors`

- `id` (PK)
- `name`
- `books_count` (default: 0) - Se actualiza automáticamente mediante Jobs cuando se crea/actualiza/elimina un libro. También se calcula dinámicamente como respaldo.
- `created_at`
- `updated_at`

#### `books`

- `id` (PK)
- `title`
- `publication_date` (integer, required) - Year only (e.g., 1967)
- `author_id` (FK -> authors.id)
- `created_at`
- `updated_at`

## 🔄 Eventos y Listeners

El sistema utiliza eventos de Laravel para actualizar automáticamente el contador de libros:

- **BookCreated**: Se dispara al crear un libro → Incrementa `books_count`
- **BookUpdated**: Se dispara al actualizar un libro → Ajusta contadores si cambia el autor
- **BookDeleted**: Se dispara al eliminar un libro → Decrementa `books_count`

## 🛠️ Comandos Útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas registradas
php artisan route:list

# Ver estado de migraciones
php artisan migrate:status

# Acceder a Tinker (consola interactiva)
php artisan tinker
```

## 📝 Notas

- **Todos los nombres de código están en inglés** (variables, funciones, clases, métodos, campos de BD, comentarios, respuestas API, nombres de archivos)
- La base de datos SQLite se encuentra en `database/database.sqlite`
- El archivo `.env` no debe subirse a Git (está en .gitignore)
- Para producción, cambiar `APP_DEBUG=false` y `APP_ENV=production`
- Se utilizan **Form Requests** para validaciones (StoreUserRequest, UpdateUserRequest, StoreAuthorRequest, UpdateAuthorRequest, StoreBookRequest, UpdateBookRequest)
- El manejo de excepciones JWT está implementado en todos los métodos de autenticación

## 🐛 Solución de Problemas

### Error: "could not find driver"

- Asegúrate de que las extensiones `pdo_sqlite` y `sqlite3` estén habilitadas en `php.ini`
- En Windows, busca el archivo `php.ini` en la carpeta de PHP (ej: `C:\xampp\php\php.ini`)
- Descomenta las líneas: `extension=pdo_sqlite` y `extension=sqlite3`
- Reinicia Apache/servidor web

### Error: "JWT secret not set"

- Ejecuta: `php artisan jwt:secret`
- Esto generará automáticamente la clave JWT en el archivo `.env`

### Error al exportar XLSX

- Instala PhpSpreadsheet: `composer require phpoffice/phpspreadsheet:^1.29`
- Habilita las extensiones `gd` (o `gd2`) y `mbstring` en `php.ini`
- Reinicia Apache/servidor web

### Error: "Apache shutdown unexpectedly" (XAMPP)

- Verifica que las extensiones PHP estén correctamente habilitadas
- Revisa los logs de Apache en `C:\xampp\apache\logs\error.log`
- Asegúrate de que el puerto 80/443 no esté en uso por otro servicio

## 📖 Documentación Adicional

- **[POSTMAN_DOCUMENTATION.md](POSTMAN_DOCUMENTATION.md)**: Guía completa para probar la API con Postman

## 🧪 Testing

El proyecto incluye tests automatizados con PHPUnit:

```bash
# Ejecutar todos los tests
vendor/bin/phpunit

# Ejecutar tests específicos
vendor/bin/phpunit --filter AuthTest
vendor/bin/phpunit --filter AuthorTest
vendor/bin/phpunit --filter BookTest
```

**Cobertura de tests:**

- ✅ Tests de autenticación (login, register, logout, refresh, me)
- ✅ Tests de CRUD de autores
- ✅ Tests de CRUD de libros
- ✅ Tests de actualización automática de `books_count`
- ✅ Tests de validaciones

**Resultado:** 15 tests, 52 assertions - Todos pasando ✅

## 📄 Licencia

Este proyecto fue desarrollado como parte de una prueba técnica.

## 👤 Autor

Desarrollado para la prueba técnica de Intelli-Next.

---

**Fecha de creación:** Enero 2025
**Versión Laravel:** 5.8.38
**Última actualización:** Enero 2025
