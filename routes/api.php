<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\SecretariaApiController;
use App\Http\Controllers\Api\UnidadApiController;
use App\Http\Controllers\Api\RolPermisoApiController;

/*
|--------------------------------------------------------------------------
| API Routes – Sistema de Contratación Gobernación de Caldas
|--------------------------------------------------------------------------
|
| Prefijo automático: /api
| Middleware por defecto: 'web' (sesión, CSRF)
|
| Para usar tokens (Sanctum) en vez de sesión, cambiar el middleware
| 'auth' por 'auth:sanctum' y generar tokens en el AuthController.
|
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login',  [AuthController::class, 'login'])->name('api.login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('api.logout');
    Route::get('/me',      [AuthController::class, 'me'])->middleware('auth')->name('api.me');
    Route::post('/validar-permiso', [AuthController::class, 'validarPermiso'])->middleware('auth')->name('api.validar-permiso');
});

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (requiere autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'usuario.activo'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | SECRETARÍAS
    |----------------------------------------------------------------------
    */
    Route::get('/secretarias',                       [SecretariaApiController::class, 'index'])->name('api.secretarias.index');
    Route::get('/secretarias/{secretaria}',          [SecretariaApiController::class, 'show'])->name('api.secretarias.show');
    Route::get('/secretarias/{secretaria}/unidades', [SecretariaApiController::class, 'unidades'])->name('api.secretarias.unidades');

    Route::middleware('permiso:secretarias.crear')->group(function () {
        Route::post('/secretarias',                  [SecretariaApiController::class, 'store'])->name('api.secretarias.store');
    });
    Route::middleware('permiso:secretarias.editar')->group(function () {
        Route::put('/secretarias/{secretaria}',      [SecretariaApiController::class, 'update'])->name('api.secretarias.update');
    });
    Route::middleware('permiso:secretarias.eliminar')->group(function () {
        Route::delete('/secretarias/{secretaria}',   [SecretariaApiController::class, 'destroy'])->name('api.secretarias.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | UNIDADES
    |----------------------------------------------------------------------
    */
    Route::get('/unidades',              [UnidadApiController::class, 'index'])->name('api.unidades.index');
    Route::get('/unidades/{unidad}',     [UnidadApiController::class, 'show'])->name('api.unidades.show');

    Route::middleware('permiso:unidades.crear')->group(function () {
        Route::post('/unidades',             [UnidadApiController::class, 'store'])->name('api.unidades.store');
    });
    Route::middleware('permiso:unidades.editar')->group(function () {
        Route::put('/unidades/{unidad}',     [UnidadApiController::class, 'update'])->name('api.unidades.update');
    });
    Route::middleware('permiso:unidades.eliminar')->group(function () {
        Route::delete('/unidades/{unidad}',  [UnidadApiController::class, 'destroy'])->name('api.unidades.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | USUARIOS
    |----------------------------------------------------------------------
    */
    Route::middleware('permiso:usuarios.ver')->group(function () {
        Route::get('/usuarios',          [UserApiController::class, 'index'])->name('api.usuarios.index');
        Route::get('/usuarios/{user}',   [UserApiController::class, 'show'])->name('api.usuarios.show');
    });
    Route::middleware('permiso:usuarios.crear')->group(function () {
        Route::post('/usuarios',         [UserApiController::class, 'store'])->name('api.usuarios.store');
    });
    Route::middleware('permiso:usuarios.editar')->group(function () {
        Route::put('/usuarios/{user}',   [UserApiController::class, 'update'])->name('api.usuarios.update');
    });
    Route::middleware('permiso:usuarios.eliminar')->group(function () {
        Route::delete('/usuarios/{user}',[UserApiController::class, 'destroy'])->name('api.usuarios.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | ROLES Y PERMISOS
    |----------------------------------------------------------------------
    */
    Route::get('/roles',                       [RolPermisoApiController::class, 'roles'])->name('api.roles.index');
    Route::get('/roles/{role}',                [RolPermisoApiController::class, 'showRole'])->name('api.roles.show');
    Route::get('/permisos',                    [RolPermisoApiController::class, 'permisos'])->name('api.permisos.index');

    Route::middleware('permiso:roles.editar')->group(function () {
        Route::post('/roles/{role}/permisos',  [RolPermisoApiController::class, 'asignarPermisos'])->name('api.roles.permisos');
    });
    Route::middleware('permiso:asignar_roles')->group(function () {
        Route::post('/usuarios/{userId}/roles', [RolPermisoApiController::class, 'asignarRolUsuario'])->name('api.usuarios.roles');
    });
});
