<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

use App\Http\Controllers\Area\UnidadController;
use App\Http\Controllers\Area\PlaneacionController;
use App\Http\Controllers\Area\HaciendaController;
use App\Http\Controllers\Area\JuridicaController;
use App\Http\Controllers\Area\SecopController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowFilesController;

/*
|--------------------------------------------------------------------------
| Inicio
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard base
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| PROCESOS (crear solicitud)
|--------------------------------------------------------------------------
| Admin y Unidad pueden crear procesos
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin|unidad_solicitante'])->group(function () {

    Route::get('/procesos', [ProcesoController::class, 'index'])
        ->name('procesos.index');

    Route::get('/procesos/crear', [ProcesoController::class, 'create'])
        ->name('procesos.create');

    Route::post('/procesos', [ProcesoController::class, 'store'])
        ->name('procesos.store');
});

/*
|--------------------------------------------------------------------------
| WORKFLOW (acciones internas del flujo)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::post('/workflow/procesos/{proceso}/recibir',
        [WorkflowController::class, 'recibir'])
        ->name('workflow.recibir');

    Route::post('/workflow/procesos/{proceso}/checks/{check}/toggle',
        [WorkflowController::class, 'toggleCheck'])
        ->name('workflow.checks.toggle');

    Route::post('/workflow/procesos/{proceso}/enviar',
        [WorkflowController::class, 'enviar'])
        ->name('workflow.enviar');

    // Archivos del workflow (PROTEGIDOS por auth)
    Route::post('/workflow/procesos/{proceso}/archivos',
        [WorkflowFilesController::class, 'store'])
        ->name('workflow.archivos.store');

    Route::get('/workflow/procesos/{proceso}/archivos/{archivo}',
        [WorkflowFilesController::class, 'download'])
        ->name('workflow.archivos.download');

    Route::delete('/workflow/procesos/{proceso}/archivos/{archivo}',
        [WorkflowFilesController::class, 'destroy'])
        ->name('workflow.archivos.destroy');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('usuarios', UserController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('permisos', PermissionController::class)->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| ÁREAS / SECRETARÍAS
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
