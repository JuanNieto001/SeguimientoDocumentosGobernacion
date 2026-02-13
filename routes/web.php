<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

// (Estos controladores los crearemos después)
use App\Http\Controllers\Area\UnidadController;
use App\Http\Controllers\Area\PlaneacionController;
use App\Http\Controllers\Area\HaciendaController;
use App\Http\Controllers\Area\JuridicaController;
use App\Http\Controllers\Area\SecopController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard base (redirige según rol)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        // Puedes cambiar esto luego por un controller que redirija
        return view('dashboard');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin (solo rol admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('usuarios', UserController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('permisos', PermissionController::class)->except(['show']);

        // (Opcional) Vista de admin para ver "todas las bandejas"
        // Route::get('bandeja', [AdminInboxController::class, 'index'])->name('bandeja');
    });

/*
|--------------------------------------------------------------------------
| Áreas / Secretarías (cada una con su vista)
|--------------------------------------------------------------------------
| Roles definidos:
| - unidad_solicitante
| - planeacion
| - hacienda
| - juridica
| - secop
|
| Regla: solo pueden entrar a SU prefijo.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|unidad_solicitante'])
    ->prefix('unidad')
    ->name('unidad.')
    ->group(function () {
        Route::get('/', [UnidadController::class, 'index'])->name('index');
    });

Route::middleware(['auth', 'role:admin|planeacion'])
    ->prefix('planeacion')
    ->name('planeacion.')
    ->group(function () {
        Route::get('/', [PlaneacionController::class, 'index'])->name('index');
    });

Route::middleware(['auth', 'role:admin|hacienda'])
    ->prefix('hacienda')
    ->name('hacienda.')
    ->group(function () {
        Route::get('/', [HaciendaController::class, 'index'])->name('index');
    });

Route::middleware(['auth', 'role:admin|juridica'])
    ->prefix('juridica')
    ->name('juridica.')
    ->group(function () {
        Route::get('/', [JuridicaController::class, 'index'])->name('index');
    });

Route::middleware(['auth', 'role:admin|secop'])
    ->prefix('secop')
    ->name('secop.')
    ->group(function () {
        Route::get('/', [SecopController::class, 'index'])->name('index');
    });

require __DIR__.'/auth.php';
