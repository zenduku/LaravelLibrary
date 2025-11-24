# 📬 Documentación de Postman - Laravel Library API

Esta guía te ayudará a configurar y probar la API de Laravel Library usando Postman.

## 📋 Tabla de Contenidos

1. [Configuración Inicial](#configuración-inicial)
2. [Variables de Entorno](#variables-de-entorno)
3. [Autenticación](#autenticación)
4. [Endpoints de la API](#endpoints-de-la-api)
5. [Colección de Postman](#colección-de-postman)
6. [Ejemplos de Requests](#ejemplos-de-requests)
7. [Códigos de Respuesta HTTP](#códigos-de-respuesta-http)
8. [Manejo de Errores](#manejo-de-errores)

---

## 🔧 Configuración Inicial

### Paso 1: Crear una Colección

1. Abre Postman
2. Click en **"New"** → **"Collection"**
3. Nombra la colección: **"Laravel Library API"**
4. Click en **"Create"**

### Paso 2: Crear un Environment

1. Click en el ícono de engranaje (⚙️) en la esquina superior derecha
2. Click en **"Add"** para crear un nuevo environment
3. Nombra el environment: **"Laravel Local"**
4. Agrega las siguientes variables:

| Variable     | Initial Value                 | Current Value                 |
| ------------ | ----------------------------- | ----------------------------- |
| `base_url` | `http://localhost:8000/api` | `http://localhost:8000/api` |
| `token`    | (vacío)                      | (vacío)                      |

5. Click en **"Save"**
6. Selecciona este environment desde el dropdown en la esquina superior derecha

---

## 🔐 Variables de Entorno

Las variables de entorno permiten reutilizar valores en múltiples requests:

- **`{{base_url}}`**: URL base de la API
- **`{{token}}`**: Token JWT para autenticación (se guarda automáticamente después del login)

---

## 🔑 Autenticación

### Configurar Authorization en la Colección

1. Click derecho en la colección → **"Edit"**
2. Ve a la pestaña **"Authorization"**
3. Type: **"Bearer Token"**
4. Token: `{{token}}`
5. Click en **"Update"**

Esto aplicará automáticamente el token a todos los requests de la colección (excepto los públicos).

---

## 📡 Endpoints de la API

### 🔓 Rutas Públicas (Sin Autenticación)

| Método | Endpoint      | Descripción                        |
| ------- | ------------- | ----------------------------------- |
| POST    | `/register` | Registrar un nuevo usuario          |
| POST    | `/login`    | Iniciar sesión y obtener token JWT |

### 🔒 Rutas Protegidas (Requieren Autenticación)

#### Autenticación

| Método | Endpoint     | Descripción                |
| ------- | ------------ | --------------------------- |
| GET     | `/me`      | Obtener usuario autenticado |
| POST    | `/logout`  | Cerrar sesión              |
| POST    | `/refresh` | Refrescar token JWT         |

#### Usuarios

| Método   | Endpoint        | Descripción                   |
| --------- | --------------- | ------------------------------ |
| GET       | `/users`      | Listar todos los usuarios      |
| POST      | `/users`      | Crear un nuevo usuario         |
| GET       | `/users/{id}` | Obtener un usuario específico |
| PUT/PATCH | `/users/{id}` | Actualizar un usuario          |
| DELETE    | `/users/{id}` | Eliminar un usuario            |

#### Autores

| Método   | Endpoint          | Descripción                 |
| --------- | ----------------- | ---------------------------- |
| GET       | `/authors`      | Listar todos los autores     |
| POST      | `/authors`      | Crear un nuevo autor         |
| GET       | `/authors/{id}` | Obtener un autor específico |
| PUT/PATCH | `/authors/{id}` | Actualizar un autor          |
| DELETE    | `/authors/{id}` | Eliminar un autor            |

#### Libros

| Método   | Endpoint        | Descripción                 |
| --------- | --------------- | ---------------------------- |
| GET       | `/books`      | Listar todos los libros      |
| POST      | `/books`      | Crear un nuevo libro         |
| GET       | `/books/{id}` | Obtener un libro específico |
| PUT/PATCH | `/books/{id}` | Actualizar un libro          |
| DELETE    | `/books/{id}` | Eliminar un libro            |

#### Exportación

| Método | Endpoint         | Descripción                      |
| ------- | ---------------- | --------------------------------- |
| GET     | `/export/xlsx` | Exportar autores y libros a Excel |

---

## 📝 Ejemplos de Requests

### 1. Register (POST)

**URL:** `{{base_url}}/register`

**Method:** `POST`

**Headers:**

```
Content-Type: application/json
```

**Body (raw JSON):**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Respuesta Exitosa (201):**

```json
{
    "message": "User registered successfully",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-01-20T10:00:00.000000Z",
        "updated_at": "2025-01-20T10:00:00.000000Z"
    }
}
```

---

### 2. Login (POST)

**URL:** `{{base_url}}/login`

**Method:** `POST`

**Headers:**

```
Content-Type: application/json
```

**Body (raw JSON):**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Tests Script (para guardar token automáticamente):**

En la pestaña **"Tests"** del request, agrega:

```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.token);
    console.log("Token guardado: " + jsonData.token);
}
```

**Respuesta Exitosa (200):**

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

---

### 3. Get Me (GET)

**URL:** `{{base_url}}/me`

**Method:** `GET`

**Authorization:** Bearer Token (automático desde la colección)

**Respuesta Exitosa (200):**

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-01-20T10:00:00.000000Z",
    "updated_at": "2025-01-20T10:00:00.000000Z"
}
```

---

### 4. Logout (POST)

**URL:** `{{base_url}}/logout`

**Method:** `POST`

**Authorization:** Bearer Token (automático desde la colección)

**Respuesta Exitosa (200):**

```json
{
    "message": "Successfully logged out"
}
```

---

### 5. Refresh Token (POST)

**URL:** `{{base_url}}/refresh`

**Method:** `POST`

**Authorization:** Bearer Token (automático desde la colección)

**Tests Script (para actualizar token automáticamente):**

```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.token);
    console.log("Token actualizado: " + jsonData.token);
}
```

**Respuesta Exitosa (200):**

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer"
}
```

---

### 6. Create Author (POST)

**URL:** `{{base_url}}/authors`

**Method:** `POST`

**Headers:**

```
Content-Type: application/json
```

**Body (raw JSON):**

```json
{
    "name": "Gabriel García Márquez"
}
```

**Respuesta Exitosa (201):**

```json
{
    "message": "Author created successfully",
    "author": {
        "id": 1,
        "name": "Gabriel García Márquez",
        "books_count": 0,
        "created_at": "2025-01-20T10:00:00.000000Z",
        "updated_at": "2025-01-20T10:00:00.000000Z"
    }
}
```

---

### 7. List Authors (GET)

**URL:** `{{base_url}}/authors`

**Method:** `GET`

**Respuesta Exitosa (200):**

```json
[
    {
        "id": 1,
        "name": "Gabriel García Márquez",
        "books_count": 2,
        "created_at": "2025-01-20T10:00:00.000000Z",
        "updated_at": "2025-01-20T10:00:00.000000Z",
        "books": [
            {
                "id": 1,
                "title": "Cien años de soledad",
                "publication_date": 1967,
                "author_id": 1,
                "created_at": "2025-01-20T10:00:00.000000Z",
                "updated_at": "2025-01-20T10:00:00.000000Z"
            }
        ]
    }
]
```

---

### 8. Get Author (GET)

**URL:** `{{base_url}}/authors/1`

**Method:** `GET`

**Respuesta Exitosa (200):**

```json
{
    "id": 1,
    "name": "Gabriel García Márquez",
    "books_count": 2,
    "created_at": "2025-01-20T10:00:00.000000Z",
    "updated_at": "2025-01-20T10:00:00.000000Z",
    "books": [...]
}
```

---

### 9. Update Author (PUT)

**URL:** `{{base_url}}/authors/1`

**Method:** `PUT`

**Headers:**

```
Content-Type: application/json
```

**Body (raw JSON):**

```json
{
    "name": "García Márquez"
}
```

**Respuesta Exitosa (200):**

```json
{
    "message": "Author updated successfully",
    "author": {
        "id": 1,
        "name": "García Márquez",
        "books_count": 2,
        "created_at": "2025-01-20T10:00:00.000000Z",
        "updated_at": "2025-01-20T10:30:00.000000Z"
    }
}
```

---

### 10. Delete Author (DELETE)

**URL:** `{{base_url}}/authors/1`

**Method:** `DELETE`

**Respuesta Exitosa (200):**

```json
{
    "message": "Author deleted successfully"
}
```

**Error (400) - Si el autor tiene libros asociados:**

```json
{
    "error": "Cannot delete author with associated books"
}
```

---

### 11. Create Book (POST)

**URL:** `{{base_url}}/books`

**Method:** `POST`

**Headers:**

```
Content-Type: application/json
```

**Body (raw JSON):**

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

Se utiliza `integer` (año) en lugar de `date` (fecha completa) por las siguientes razones:

1. **Práctica común en bibliotecas**: Para la mayoría de los libros, solo se conoce el año de publicación, no el día y mes exactos. Muchas bibliografías y catálogos bibliográficos solo registran el año.
2. **Simplicidad de uso**: Los usuarios solo necesitan ingresar un número (ej: `1967`) en lugar de una fecha completa (ej: `1967-05-30`), lo que simplifica la entrada de datos.
3. **Flexibilidad**: Permite registrar libros antiguos donde solo se conoce el año aproximado, sin necesidad de inventar un día y mes específicos.
4. **Validación más simple**: Es más fácil validar que un año esté en un rango razonable (1000 - año actual) que validar fechas completas.
5. **Menor complejidad**: Evita problemas de formato de fecha, zonas horarias y conversiones innecesarias cuando solo se necesita el año.

**Respuesta Exitosa (201):**

```json
{
    "message": "Book created successfully",
    "book": {
        "id": 1,
        "title": "Cien años de soledad",
        "publication_date": 1967,
        "author_id": 1,
        "created_at": "2025-01-20T10:00:00.000000Z",
        "updated_at": "2025-01-20T10:00:00.000000Z",
        "author": {
            "id": 1,
            "name": "Gabriel García Márquez",
            "books_count": 1,
            "created_at": "2025-01-20T10:00:00.000000Z",
            "updated_at": "2025-01-20T10:00:00.000000Z"
        }
    }
}
```

**Nota:** El campo `books_count` del autor se actualiza automáticamente mediante Jobs cuando se crea/actualiza/elimina un libro. También se calcula dinámicamente como respaldo para garantizar que siempre refleje el número real de libros asociados.

---

### 12. List Books (GET)

**URL:** `{{base_url}}/books`

**Method:** `GET`

**Respuesta Exitosa (200):**

```json
[
    {
        "id": 1,
        "title": "Cien años de soledad",
        "publication_date": 1967,
        "author_id": 1,
        "created_at": "2025-01-20T10:00:00.000000Z",
        "updated_at": "2025-01-20T10:00:00.000000Z",
        "author": {
            "id": 1,
            "name": "Gabriel García Márquez",
            "books_count": 1,
            "created_at": "2025-01-20T10:00:00.000000Z",
            "updated_at": "2025-01-20T10:00:00.000000Z"
        }
    }
]
```

---

### 13. Get Book (GET)

**URL:** `{{base_url}}/books/1`

**Method:** `GET`

**Respuesta Exitosa (200):**

```json
{
    "id": 1,
    "title": "Cien años de soledad",
    "publication_date": 1967,
    "author_id": 1,
    "created_at": "2025-01-20T10:00:00.000000Z",
    "updated_at": "2025-01-20T10:00:00.000000Z",
    "author": {...}
}
```

---

### 14. Update Book (PUT)

**URL:** `{{base_url}}/books/1`

**Method:** `PUT`

**Headers:**

```
Content-Type: application/json
```

**Body (raw JSON):**

```json
{
    "title": "One Hundred Years of Solitude",
    "author_id": 2
}
```

**Nota:** Todos los campos son opcionales en la actualización. Solo envía los campos que deseas actualizar. Si envías `publication_date`, debe ser un año válido (requerido cuando se incluye).

**Respuesta Exitosa (200):**

```json
{
    "message": "Book updated successfully",
    "book": {
        "id": 1,
        "title": "One Hundred Years of Solitude",
        "publication_date": 1967,
        "author_id": 2,
        "created_at": "2025-01-20T10:00:00.000000Z",
        "updated_at": "2025-01-20T10:30:00.000000Z",
        "author": {
            "id": 2,
            "name": "Otro Autor",
            "books_count": 1,
            "created_at": "2025-01-20T10:00:00.000000Z",
            "updated_at": "2025-01-20T10:30:00.000000Z"
        }
    }
}
```

**Nota:** El campo `books_count` se actualiza automáticamente mediante Jobs cuando cambias el `author_id`. Los contadores de ambos autores (anterior y nuevo) se actualizan automáticamente, y también se calculan dinámicamente como respaldo para garantizar precisión.

---

### 15. Delete Book (DELETE)

**URL:** `{{base_url}}/books/1`

**Method:** `DELETE`

**Respuesta Exitosa (200):**

```json
{
    "message": "Book deleted successfully"
}
```

**Nota:** El campo `books_count` del autor se actualiza automáticamente mediante Jobs cuando se elimina un libro. También se calcula dinámicamente como respaldo para garantizar que siempre refleje el número real de libros restantes.

---

### 16. Export to XLSX (GET)

**URL:** `{{base_url}}/export/xlsx`

**Method:** `GET`

**Nota:** Este endpoint descarga un archivo Excel. En Postman:

1. Click en **"Send and Download"** en lugar de solo "Send"
2. El archivo se descargará con el nombre: `library_export_YYYY-MM-DD_HHMMSS.xlsx`

El archivo Excel contiene dos hojas:

- **Authors**: Lista de todos los autores con sus datos
- **Books**: Lista de todos los libros con sus datos

---

## 📊 Códigos de Respuesta HTTP

| Código | Descripción          | Cuándo se usa                                      |
| ------- | --------------------- | --------------------------------------------------- |
| 200     | OK                    | Operación exitosa (GET, PUT, DELETE)               |
| 201     | Created               | Recurso creado exitosamente (POST)                  |
| 400     | Bad Request           | Solicitud inválida (ej: eliminar autor con libros) |
| 401     | Unauthorized          | No autenticado o token inválido                    |
| 404     | Not Found             | Recurso no encontrado                               |
| 422     | Unprocessable Entity  | Error de validación                                |
| 500     | Internal Server Error | Error del servidor                                  |

---

## ⚠️ Manejo de Errores

### Error de Validación (422)

**Request:**

```json
POST {{base_url}}/authors
{
    "name": ""
}
```

**Respuesta:**

```json
{
    "error": "Validation failed",
    "messages": {
        "name": [
            "The author name is required."
        ]
    }
}
```

---

### Error de Autenticación (401)

**Request sin token:**

```
GET {{base_url}}/authors
```

**Respuesta:**

```json
{
    "message": "Unauthenticated."
}
```

---

### Error de Recurso No Encontrado (404)

**Request:**

```
GET {{base_url}}/authors/999
```

**Respuesta:**

```json
{
    "error": "Author not found"
}
```

---

### Error al Eliminar Autor con Libros (400)

**Request:**

```
DELETE {{base_url}}/authors/1
```

**Respuesta:**

```json
{
    "error": "Cannot delete author with associated books"
}
```

---

## ✅ Checklist de Pruebas

Usa esta lista para verificar que todos los endpoints funcionan correctamente:

### Autenticación

- [ ] Registrar usuario
- [ ] Login (verificar que el token se guarda automáticamente)
- [ ] Obtener usuario autenticado (`/me`)
- [ ] Logout
- [ ] Refrescar token

### Usuarios

- [ ] Listar usuarios
- [ ] Crear usuario
- [ ] Obtener usuario específico
- [ ] Actualizar usuario
- [ ] Eliminar usuario

### Autores

- [ ] Crear autor
- [ ] Listar autores
- [ ] Obtener autor específico
- [ ] Actualizar autor
- [ ] Intentar eliminar autor con libros (debe fallar con 400)
- [ ] Eliminar autor sin libros

### Libros

- [ ] Crear libro
- [ ] Verificar que `books_count` del autor refleja correctamente el número de libros (se calcula dinámicamente)
- [ ] Listar libros
- [ ] Obtener libro específico
- [ ] Actualizar libro (cambiar título)
- [ ] Actualizar libro (cambiar autor)
- [ ] Verificar que los contadores de ambos autores se recalculan correctamente al cambiar autor
- [ ] Eliminar libro
- [ ] Verificar que `books_count` del autor se recalcula correctamente después de eliminar libro

### Exportación

- [ ] Exportar a XLSX
- [ ] Verificar que el archivo contiene ambas hojas (Authors y Books)

### Validaciones

- [ ] Probar validaciones (campos vacíos, tipos incorrectos, emails duplicados)
- [ ] Probar autenticación (requests sin token)
- [ ] Probar recursos inexistentes (404)

---

## 🎯 Flujo Completo de Ejemplo

Sigue este flujo para probar toda la funcionalidad:

1. **Registrar usuario** → Guarda el token automáticamente
2. **Login** → Verifica que el token se actualiza
3. **Crear autor 1** → ID: 1
4. **Crear autor 2** → ID: 2
5. **Crear libro 1** (asociado a autor 1) → Verifica `books_count = 1` en autor 1 (calculado dinámicamente)
6. **Crear libro 2** (asociado a autor 1) → Verifica `books_count = 2` en autor 1 (calculado dinámicamente)
7. **Obtener autor 1** → Verifica que tiene 2 libros y `books_count = 2`
8. **Actualizar libro 1** (cambiar a autor 2) → Verifica:
   - Autor 1: `books_count = 1` (recalculado automáticamente)
   - Autor 2: `books_count = 1` (recalculado automáticamente)
9. **Eliminar libro 2** → Verifica autor 1: `books_count = 0` (recalculado automáticamente)
10. **Intentar eliminar autor 2** → Debe fallar (tiene libro asociado)
11. **Eliminar libro 1** → Verifica autor 2: `books_count = 0`
12. **Eliminar autor 2** → Ahora debe funcionar
13. **Exportar datos a XLSX** → Verifica que el archivo se descarga correctamente

---

## 💡 Tips y Mejores Prácticas

1. **Usa Variables de Entorno**: Configura `base_url` y `token` como variables para facilitar el cambio entre ambientes (local, staging, producción).
2. **Guarda el Token Automáticamente**: Usa scripts de test en los requests de login/refresh para guardar el token automáticamente.
3. **Organiza tus Requests**: Crea carpetas en la colección para agrupar requests relacionados (Authentication, Authors, Books, etc.).
4. **Usa Pre-request Scripts**: Si necesitas generar datos dinámicos, usa pre-request scripts.
5. **Documenta tus Requests**: Agrega descripciones a cada request explicando qué hace y qué espera recibir.
6. **Crea Tests Automatizados**: Usa la pestaña "Tests" para verificar respuestas automáticamente.

---

## 📚 Recursos Adicionales

- [Documentación de Postman](https://learning.postman.com/docs/)
- [Documentación de Laravel 5.8](https://laravel.com/docs/5.8)
- [Documentación de JWT Auth](https://jwt-auth.readthedocs.io/)
