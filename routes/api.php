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

    /*
    |----------------------------------------------------------------------
    | MOTOR DE FLUJOS CONFIGURABLE POR SECRETARÍA
    |----------------------------------------------------------------------
    | Permite que cada Secretaría defina sus propios flujos de contratación
    | con pasos, orden, responsables, documentos y condiciones diferentes.
    | Solo los administradores de unidad pueden modificar flujos.
    |----------------------------------------------------------------------
    */
    Route::prefix('motor-flujos')->group(function () {

        $ctrl = \App\Http\Controllers\Api\MotorFlujoController::class;

        // ── Catálogo de pasos (lectura: todos; escritura: admin) ──
        Route::get('/catalogo-pasos',      [$ctrl, 'catalogoPasos'])->name('api.motor-flujos.catalogo');
        Route::post('/catalogo-pasos',     [$ctrl, 'crearCatalogoPaso'])->name('api.motor-flujos.catalogo.store');

        // ── Flujos por Secretaría ──
        Route::get('/secretarias/{secretariaId}/flujos', [$ctrl, 'flujosPorSecretaria'])->name('api.motor-flujos.flujos.porSecretaria');
        Route::post('/flujos',                           [$ctrl, 'crearFlujo'])->name('api.motor-flujos.flujos.store');
        Route::post('/flujos/guardar-completo',          [$ctrl, 'guardarFlujoCompleto'])->name('api.motor-flujos.flujos.guardarCompleto');
        Route::put('/flujos/{flujoId}',                  [$ctrl, 'actualizarFlujo'])->name('api.motor-flujos.flujos.update');
        Route::delete('/flujos/{flujoId}',               [$ctrl, 'eliminarFlujo'])->name('api.motor-flujos.flujos.destroy');
        Route::get('/flujos/{flujoId}/pasos',            [$ctrl, 'pasosDelFlujo'])->name('api.motor-flujos.flujos.pasos');

        // ── Versiones ──
        Route::get('/flujos/{flujoId}/versiones',        [$ctrl, 'versionesDelFlujo'])->name('api.motor-flujos.versiones.index');
        Route::post('/flujos/{flujoId}/versiones',       [$ctrl, 'crearVersion'])->name('api.motor-flujos.versiones.store');
        Route::post('/versiones/{versionId}/publicar',   [$ctrl, 'publicarVersion'])->name('api.motor-flujos.versiones.publicar');

        // ── Gestión de pasos en versión borrador ──
        Route::post('/versiones/{versionId}/pasos',      [$ctrl, 'agregarPaso'])->name('api.motor-flujos.pasos.store');
        Route::put('/pasos/{pasoId}',                    [$ctrl, 'actualizarPaso'])->name('api.motor-flujos.pasos.update');
        Route::delete('/pasos/{pasoId}',                 [$ctrl, 'eliminarPaso'])->name('api.motor-flujos.pasos.destroy');

        // ── Condiciones, documentos y responsables por paso ──
        Route::post('/pasos/{pasoId}/condiciones',       [$ctrl, 'agregarCondicion'])->name('api.motor-flujos.condiciones.store');
        Route::post('/pasos/{pasoId}/documentos',        [$ctrl, 'agregarDocumento'])->name('api.motor-flujos.documentos.store');
        Route::post('/pasos/{pasoId}/responsables',      [$ctrl, 'agregarResponsable'])->name('api.motor-flujos.responsables.store');
        Route::delete('/condiciones/{condicionId}',      [$ctrl, 'eliminarCondicion'])->name('api.motor-flujos.condiciones.destroy');
        Route::delete('/documentos/{documentoId}',       [$ctrl, 'eliminarDocumento'])->name('api.motor-flujos.documentos.destroy');
        Route::delete('/responsables/{responsableId}',   [$ctrl, 'eliminarResponsable'])->name('api.motor-flujos.responsables.destroy');

        // ── Instancias (procesos en ejecución) ──
        Route::post('/instancias',                       [$ctrl, 'crearInstancia'])->name('api.motor-flujos.instancias.store');
        Route::get('/instancias/{instanciaId}',          [$ctrl, 'detalleInstancia'])->name('api.motor-flujos.instancias.show');
        Route::get('/flujos/{flujoId}/instancia-activa', [$ctrl, 'instanciaActiva'])->name('api.motor-flujos.instancias.activa');
        Route::post('/instancias/{instanciaId}/avanzar', [$ctrl, 'avanzarInstancia'])->name('api.motor-flujos.instancias.avanzar');
        Route::post('/instancias/{instanciaId}/devolver',[$ctrl, 'devolverInstancia'])->name('api.motor-flujos.instancias.devolver');
    });

    /*
    |----------------------------------------------------------------------
    | DASHBOARD BUILDER - Motor Visual Dinámico
    |----------------------------------------------------------------------
    | Sistema de construcción de dashboards drag-and-drop con:
    | - Catálogo de entidades y campos
    | - Queries dinámicas en runtime
    | - Filtros automáticos por rol/secretaría/unidad
    | - Renderizado dinámico de widgets
    |----------------------------------------------------------------------
    */
    Route::prefix('dashboard-builder')->group(function () {
        $ctrl = \App\Http\Controllers\Api\DashboardBuilderController::class;

        // Catálogo de entidades y campos disponibles
        Route::get('/catalog', [$ctrl, 'catalog'])->name('api.dashboard-builder.catalog');

        // Información de scope del usuario actual
        Route::get('/user-scope', [$ctrl, 'userScope'])->name('api.dashboard-builder.user-scope');

        // Ejecutar query de un widget
        Route::post('/execute-widget', [$ctrl, 'executeWidget'])->name('api.dashboard-builder.execute-widget');

        // Ejecutar todos los widgets de un dashboard
        Route::post('/execute-dashboard', [$ctrl, 'executeDashboard'])->name('api.dashboard-builder.execute-dashboard');

        // Preview de widget antes de guardar
        Route::post('/preview-widget', [$ctrl, 'previewWidget'])->name('api.dashboard-builder.preview-widget');

        // Guardar y cargar dashboard
        Route::post('/save', [$ctrl, 'saveDashboard'])->name('api.dashboard-builder.save');
        Route::get('/load', [$ctrl, 'loadDashboard'])->name('api.dashboard-builder.load');

        // Valores de campo para filtros
        Route::get('/field-values', [$ctrl, 'fieldValues'])->name('api.dashboard-builder.field-values');
    });
});
