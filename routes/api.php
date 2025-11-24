<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (no authentication required)
Route::post('/login', 'Api\AuthController@login');
Route::post('/register', 'Api\AuthController@register');

// Protected routes (require JWT authentication)
Route::middleware('auth:api')->group(function () {
    // Authentication routes
    Route::post('/logout', 'Api\AuthController@logout');
    Route::get('/me', 'Api\AuthController@me');
    Route::post('/refresh', 'Api\AuthController@refresh');

    // User management routes (CRUD)
    Route::apiResource('users', 'Api\UserController');

    // Author management routes (CRUD)
    Route::apiResource('authors', 'Api\AuthorController');

    // Book management routes (CRUD)
    Route::apiResource('books', 'Api\BookController');

    // Export routes
    Route::get('/export/xlsx', 'Api\ExportController@exportToXlsx');
});
