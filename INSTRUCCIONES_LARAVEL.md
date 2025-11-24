# Instrucciones para Configurar el Proyecto Laravel 5.8 - LaravelLibrary

## ⚠️ IMPORTANTE: Convenciones de Código

**TODO EL CÓDIGO DEBE ESTAR EN INGLÉS:**
- ✅ Nombres de variables, funciones, clases, métodos: **English**
- ✅ Nombres de campos en base de datos: **English**
- ✅ Comentarios en código: **English**
- ✅ Mensajes de respuesta API: **English**
- ✅ Nombres de archivos: **English**

**La configuración de idiomas (i18n) se manejará a nivel de interfaz/pantallas:**
- El sistema soportará **English** y **Español** para la interfaz de usuario
- Pero el código fuente siempre será en inglés

---

## 📋 Índice
1. [Configuración Inicial](#1-configuración-inicial)
2. [Configuración de Base de Datos SQLite](#2-configuración-de-base-de-datos-sqlite)
3. [Creación de Migraciones](#3-creación-de-migraciones)
4. [Creación de Modelos](#4-creación-de-modelos)
5. [Configuración de JWT](#5-configuración-de-jwt)
6. [Creación de Controladores](#6-creación-de-controladores)
7. [Configuración de Rutas API](#7-configuración-de-rutas-api)
8. [Implementación de Eventos y Listeners](#8-implementación-de-eventos-y-listeners)
9. [Exportación a XLSX](#9-exportación-a-xlsx)
10. [Middleware de Autenticación](#10-middleware-de-autenticación)
11. [Validaciones](#11-validaciones)
12. [Ejecutar Migraciones](#12-ejecutar-migraciones)

---

## 1. Configuración Inicial

### Verificar que el proyecto se creó correctamente:
```bash
cd C:\Users\juan_\LaravelLibrary
php artisan --version
```

Debería mostrar: `Laravel Framework 5.8.38`

---

## 2. Configuración de Base de Datos SQLite

### Paso 2.1: Modificar archivo `.env`
Abrir el archivo `.env` y cambiar las siguientes líneas:

```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

### Paso 2.2: Crear archivo de base de datos SQLite
```bash
cd C:\Users\juan_\LaravelLibrary
New-Item -Path database\database.sqlite -ItemType File -Force
```

O manualmente: Crear un archivo vacío llamado `database.sqlite` en la carpeta `database/`

---

## 3. Creación de Migraciones

### Paso 3.1: Crear migración para tabla `authors`
```bash
php artisan make:migration create_authors_table --create=authors
```

**Editar el archivo generado** en `database/migrations/XXXX_XX_XX_XXXXXX_create_authors_table.php`:

```php
public function up()
{
    Schema::create('authors', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name');
        $table->integer('books_count')->default(0); // Updated by Jobs, also calculated dynamically as backup
        $table->timestamps();
    });
}
```

### Paso 3.2: Crear migración para tabla `books`
```bash
php artisan make:migration create_books_table --create=books
```

**Editar el archivo generado** en `database/migrations/XXXX_XX_XX_XXXXXX_create_books_table.php`:

```php
public function up()
{
    Schema::create('books', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('title');
        $table->integer('publication_date'); // Year only (e.g., 1967), required
        $table->unsignedBigInteger('author_id');
        $table->timestamps();

        $table->foreign('author_id')
              ->references('id')
              ->on('authors')
              ->onDelete('restrict');
        
        $table->index('author_id');
    });
}
```

**Nota sobre `publication_date` (Integer vs Date):**

El campo `publication_date` se define como `integer` en lugar de `date` por las siguientes razones:

1. **Práctica común en bibliotecas**: Para la mayoría de los libros, solo se conoce el año de publicación, no el día y mes exactos. Muchas bibliografías y catálogos bibliográficos solo registran el año.

2. **Simplicidad de uso**: Los usuarios solo necesitan ingresar un número (ej: `1967`) en lugar de una fecha completa (ej: `1967-05-30`), lo que simplifica la entrada de datos.

3. **Flexibilidad**: Permite registrar libros antiguos donde solo se conoce el año aproximado, sin necesidad de inventar un día y mes específicos.

4. **Validación más simple**: Es más fácil validar que un año esté en un rango razonable (1000 - año actual) que validar fechas completas.

5. **Menor complejidad**: Evita problemas de formato de fecha, zonas horarias y conversiones innecesarias cuando solo se necesita el año.

Si en el futuro se requiere almacenar fechas completas, se puede crear una migración para cambiar el tipo de dato.

### Paso 3.3: Verificar migración de `users`
Asegurarse de que la migración `2014_10_12_000000_create_users_table.php` tenga:
- `name` (string)
- `email` (string, unique)
- `password` (string)
- `timestamps`

---

## 4. Creación de Modelos

### Paso 4.1: Crear modelo `Author`
```bash
php artisan make:model Author
```

**Editar** `app/Author.php`:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'name'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['books_count'];

    /**
     * Relationship: An author has many books
     */
    public function books()
    {
        return $this->hasMany(Book::class, 'author_id');
    }

    /**
     * Get the books_count attribute (calculated dynamically as backup)
     * This ensures the count is always accurate even if Jobs fail or data is manually deleted
     *
     * @return int
     */
    public function getBooksCountAttribute()
    {
        // If the relationship is already loaded, use it for better performance
        if ($this->relationLoaded('books')) {
            return $this->books->count();
        }

        // Otherwise, count from the database
        return $this->books()->count();
    }
}
```

**Nota importante sobre `books_count`:**

El sistema implementa un enfoque híbrido:

1. **Jobs actualizan el contador** (cumpliendo el requisito):
   - Cuando se crea/actualiza/elimina un libro, se dispara un Job que actualiza `books_count`
   - El campo se mantiene sincronizado en la base de datos

2. **Accessor calculado como respaldo**:
   - El accessor `getBooksCountAttribute()` siempre calcula el valor real desde la relación
   - Garantiza precisión incluso si hay desincronización o los Jobs fallan

3. **Job de verificación**:
   - `VerifyAndFixAuthorBookCountJob` verifica y corrige desincronizaciones automáticamente
   - Se puede ejecutar manualmente: `php artisan authors:verify-books-count`

**Ventajas:**
- ✅ Cumple el requisito literal: Jobs que actualizan el contador
- ✅ Previene desincronizaciones: Job de verificación automática
- ✅ Garantiza precisión: Accessor calculado como respaldo
- ✅ Robusto: Funciona correctamente incluso si se borran datos manualmente

### Paso 4.2: Crear modelo `Book`
```bash
php artisan make:model Book
```

**Editar** `app/Book.php`:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'publication_date',
        'author_id'
    ];

    protected $casts = [
        'publication_date' => 'integer' // Year only (e.g., 1967)
        // Se usa integer en lugar de date porque para libros generalmente solo se conoce el año,
        // no el día y mes exactos. Esto simplifica la entrada de datos y es más práctico.
    ];

    /**
     * Relationship: A book belongs to an author
     */
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
}
```

### Paso 4.3: Verificar modelo `User`
El modelo `User` ya existe en `app/User.php`. Asegurarse de que tenga:
- `fillable` con: `name`, `email`, `password`
- Método para hashear password (si no existe, agregar en el modelo o usar mutator)

---

## 5. Configuración de JWT

### Paso 5.1: Instalar paquete JWT
```bash
composer require tymon/jwt-auth:^1.0
```

### Paso 5.2: Publicar configuración JWT
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

### Paso 5.3: Configurar modelo User para JWT
**Editar** `app/User.php`:

```php
<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

### Paso 5.4: Configurar auth guard en `config/auth.php`
Buscar la sección `guards` y agregar:

```php
'api' => [
    'driver' => 'jwt',
    'provider' => 'users',
],
```

---

## 6. Creación de Controladores

### Paso 6.1: Crear AuthController
```bash
php artisan make:controller Api/AuthController
```

**Crear** `app/Http/Controllers/Api/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => auth()->user()
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
```

### Paso 6.2: Crear UserController
```bash
php artisan make:controller Api/UserController --resource
```

**Editar** `app/Http/Controllers/Api/UserController.php` (CRUD completo)

### Paso 6.3: Crear AuthorController
```bash
php artisan make:controller Api/AuthorController --resource
```

**Editar** `app/Http/Controllers/Api/AuthorController.php` (CRUD completo)

### Paso 6.4: Crear BookController
```bash
php artisan make:controller Api/BookController --resource
```

**Editar** `app/Http/Controllers/Api/BookController.php` (CRUD completo)

### Paso 6.5: Crear ExportController
```bash
php artisan make:controller Api/ExportController
```

**Crear** `app/Http/Controllers/Api/ExportController.php` para exportar a XLSX

---

## 7. Configuración de Rutas API

### Editar `routes/api.php`:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ExportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (sin autenticación)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas (requieren JWT)
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Users
    Route::apiResource('users', UserController::class);

    // Authors
    Route::apiResource('authors', AuthorController::class);

    // Books
    Route::apiResource('books', BookController::class);

    // Export
    Route::get('/export/xlsx', [ExportController::class, 'exportToXlsx']);
});
```

**Nota:** En Laravel 5.8, la sintaxis de rutas puede ser diferente. Si no funciona `[Controller::class, 'method']`, usar:
```php
Route::post('/login', 'Api\AuthController@login');
```

---

## 8. Implementación de Eventos y Listeners

### Paso 8.1: Crear Eventos para libros

Los eventos ya deberían existir. Verificar que existan:
- `app/Events/BookCreated.php`
- `app/Events/BookUpdated.php`
- `app/Events/BookDeleted.php`

### Paso 8.2: Crear Jobs para actualizar contador

```bash
php artisan make:job UpdateAuthorBookCountJob
php artisan make:job VerifyAndFixAuthorBookCountJob
```

**Editar** `app/Jobs/UpdateAuthorBookCountJob.php`:

```php
<?php

namespace App\Jobs;

use App\Author;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAuthorBookCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $authorId;
    protected $action; // 'increment', 'decrement', or 'recalculate'

    public function __construct($authorId, $action = 'recalculate')
    {
        $this->authorId = $authorId;
        $this->action = $action;
    }

    public function handle()
    {
        $author = Author::find($this->authorId);
        
        if (!$author) {
            return;
        }

        switch ($this->action) {
            case 'increment':
                $author->increment('books_count');
                break;
            case 'decrement':
                $author->decrement('books_count');
                break;
            case 'recalculate':
            default:
                $actualCount = $author->books()->count();
                $author->update(['books_count' => $actualCount]);
                break;
        }
    }
}
```

**Editar** `app/Jobs/VerifyAndFixAuthorBookCountJob.php`:

```php
<?php

namespace App\Jobs;

use App\Author;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyAndFixAuthorBookCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $authorId;

    public function __construct($authorId = null)
    {
        $this->authorId = $authorId;
    }

    public function handle()
    {
        if ($this->authorId) {
            $this->verifyAndFixAuthor($this->authorId);
        } else {
            $authors = Author::all();
            foreach ($authors as $author) {
                $this->verifyAndFixAuthor($author->id);
            }
        }
    }

    protected function verifyAndFixAuthor($authorId)
    {
        $author = Author::find($authorId);
        
        if (!$author) {
            return;
        }

        $actualCount = $author->books()->count();
        $storedCount = DB::table('authors')->where('id', $authorId)->value('books_count') ?? 0;
        
        if ($actualCount != $storedCount) {
            DB::table('authors')->where('id', $authorId)->update(['books_count' => $actualCount]);
            Log::info("Fixed books_count for author {$authorId}: {$storedCount} -> {$actualCount}");
        }
    }
}
```

### Paso 8.3: Crear Listener para actualizar contador

El listener `app/Listeners/UpdateAuthorBookCount.php` ya debería existir. **Editar** para que dispare Jobs:

```php
<?php

namespace App\Listeners;

use App\Events\BookCreated;
use App\Events\BookDeleted;
use App\Events\BookUpdated;
use App\Jobs\UpdateAuthorBookCountJob;
use App\Jobs\VerifyAndFixAuthorBookCountJob;

class UpdateAuthorBookCount
{
    public function handleBookCreated(BookCreated $event)
    {
        UpdateAuthorBookCountJob::dispatch($event->book->author_id, 'increment');
        VerifyAndFixAuthorBookCountJob::dispatch($event->book->author_id);
    }

    public function handleBookUpdated(BookUpdated $event)
    {
        $oldAuthorId = $event->oldAuthorId ?? $event->book->getOriginal('author_id');
        
        if ($oldAuthorId !== null && $oldAuthorId != $event->book->author_id) {
            UpdateAuthorBookCountJob::dispatch($oldAuthorId, 'decrement');
            VerifyAndFixAuthorBookCountJob::dispatch($oldAuthorId);
            
            if ($event->book->author_id) {
                UpdateAuthorBookCountJob::dispatch($event->book->author_id, 'increment');
                VerifyAndFixAuthorBookCountJob::dispatch($event->book->author_id);
            }
        }
    }

    public function handleBookDeleted(BookDeleted $event)
    {
        UpdateAuthorBookCountJob::dispatch($event->book->author_id, 'decrement');
        VerifyAndFixAuthorBookCountJob::dispatch($event->book->author_id);
    }
}
```

### Paso 8.4: Registrar Eventos y Listeners

**Editar** `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    Registered::class => [
        SendEmailVerificationNotification::class,
    ],
    \App\Events\BookCreated::class => [
        \App\Listeners\UpdateAuthorBookCount::class . '@handleBookCreated',
    ],
    \App\Events\BookUpdated::class => [
        \App\Listeners\UpdateAuthorBookCount::class . '@handleBookUpdated',
    ],
    \App\Events\BookDeleted::class => [
        \App\Listeners\UpdateAuthorBookCount::class . '@handleBookDeleted',
    ],
];
```

### Paso 8.5: Disparar eventos en el modelo Book

**Editar** `app/Book.php`:

```php
protected static function boot()
{
    parent::boot();

    static::created(function ($book) {
        event(new \App\Events\BookCreated($book));
    });

    static::updating(function ($book) {
        if ($book->exists && !isset($book->old_author_id)) {
            $book->old_author_id = $book->getOriginal('author_id') ?? $book->author_id;
        }
    });

    static::updated(function ($book) {
        if ($book->wasChanged('author_id')) {
            $oldAuthorId = $book->old_author_id ?? $book->getOriginal('author_id');
            event(new \App\Events\BookUpdated($book, $oldAuthorId));
        }
    });

    static::deleted(function ($book) {
        event(new \App\Events\BookDeleted($book));
    });
}
```

### Paso 8.6: Crear comando Artisan para verificación manual

```bash
php artisan make:command VerifyAuthorBookCounts
```

**Editar** `app/Console/Commands/VerifyAuthorBookCounts.php`:

```php
<?php

namespace App\Console\Commands;

use App\Jobs\VerifyAndFixAuthorBookCountJob;
use Illuminate\Console\Command;

class VerifyAuthorBookCounts extends Command
{
    protected $signature = 'authors:verify-books-count {--author-id= : Verify a specific author by ID}';
    protected $description = 'Verify and fix books_count for authors';

    public function handle()
    {
        $authorId = $this->option('author-id');

        if ($authorId) {
            $this->info("Verifying books_count for author ID: {$authorId}");
            VerifyAndFixAuthorBookCountJob::dispatch($authorId);
        } else {
            $this->info("Verifying books_count for all authors...");
            VerifyAndFixAuthorBookCountJob::dispatch();
        }

        $this->info("Verification job dispatched successfully!");
        return 0;
    }
}
```

**Nota importante sobre el sistema híbrido:**

El sistema implementa un enfoque híbrido que combina lo mejor de ambos mundos:

1. **Jobs que actualizan el contador** (cumpliendo el requisito literal):
   - `UpdateAuthorBookCountJob` se dispara cuando se crea/actualiza/elimina un libro
   - Actualiza el campo `books_count` en la base de datos

2. **Job de verificación y corrección**:
   - `VerifyAndFixAuthorBookCountJob` verifica que el contador almacenado coincida con el real
   - Corrige automáticamente cualquier desincronización
   - Se puede ejecutar manualmente con: `php artisan authors:verify-books-count`

3. **Accessor calculado como respaldo**:
   - El accessor `getBooksCountAttribute()` siempre calcula el valor real
   - Garantiza que `books_count` siempre sea preciso, incluso si hay desincronización

**Ventajas de este enfoque:**
- ✅ Cumple el requisito literal: Jobs que actualizan el contador
- ✅ Previene desincronizaciones: Job de verificación automática
- ✅ Garantiza precisión: Accessor calculado como respaldo
- ✅ Robusto: Funciona correctamente incluso si se borran datos manualmente

---

## 9. Exportación a XLSX

### Paso 9.1: Instalar paquete para Excel
```bash
composer require maatwebsite/excel:^3.1
```

### Paso 9.2: Publicar configuración
```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

### Paso 9.3: Implementar método de exportación en ExportController

---

## 10. Middleware de Autenticación

### Verificar que el middleware JWT esté configurado
El paquete `tymon/jwt-auth` ya incluye el middleware. Solo asegurarse de que esté registrado en `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ... otros middlewares
    'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
    'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
];
```

---

## 11. Validaciones

### Crear Form Requests para validaciones:
```bash
php artisan make:request StoreAuthorRequest
php artisan make:request UpdateAuthorRequest
php artisan make:request StoreBookRequest
php artisan make:request UpdateBookRequest
```

---

## 12. Ejecutar Migraciones

### Ejecutar todas las migraciones:
```bash
php artisan migrate
```

### Si hay errores, hacer rollback:
```bash
php artisan migrate:rollback
```

### Ver estado de migraciones:
```bash
php artisan migrate:status
```

---

## 📝 Notas Adicionales

1. **Códigos HTTP**: Asegurarse de usar códigos apropiados:
   - 200: OK
   - 201: Created
   - 400: Bad Request
   - 401: Unauthorized
   - 404: Not Found
   - 422: Validation Error
   - 500: Server Error

2. **Estructura de Respuestas**: Mantener consistencia en las respuestas JSON

3. **Validaciones**: Validar todos los inputs en cada endpoint

4. **Relaciones**: Asegurarse de que las relaciones entre modelos funcionen correctamente

5. **Testing**: Considerar crear tests unitarios (opcional pero recomendado)

---

## 🚀 Comandos Útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas registradas
php artisan route:list

# Iniciar servidor de desarrollo
php artisan serve

# Acceder a Tinker para probar modelos
php artisan tinker
```

---

## 📚 Recursos

- [Documentación Laravel 5.8](https://laravel.com/docs/5.8)
- [JWT Auth para Laravel](https://jwt-auth.readthedocs.io/)
- [Laravel Excel](https://docs.laravel-excel.com/3.1/)

