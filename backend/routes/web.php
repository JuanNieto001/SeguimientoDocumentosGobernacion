<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SecretariaController;
use App\Http\Controllers\Admin\UnidadController as AdminUnidadController;
use App\Http\Controllers\Area\UnidadController;
use App\Http\Controllers\Area\PlaneacionController;
use App\Http\Controllers\Area\HaciendaController;
use App\Http\Controllers\Area\JuridicaController;
use App\Http\Controllers\Area\SecopController;
use App\Http\Controllers\Area\SolicitudDocumentosController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardMotorController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowFilesController;
use App\Http\Controllers\PAAController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ProcesoSiaObservaController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Admin\AuthEventsController;
use App\Http\Controllers\Admin\SiaObservaAdminController;
use App\Http\Controllers\Admin\ResetPasswordAdminController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\SecopConsultaController;
use App\Http\Controllers\Admin\EstivenGuideController;
use App\Http\Controllers\EstivenHelpController;
use App\Http\Controllers\ContratoAplicacionController;

/*
|--------------------------------------------------------------------------
| Inicio
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Dashboard base
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/panel-principal', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Motor de Flujos Dinámico (admin)
    Route::get('/motor-flujos', fn () => view('motor-flujos'))
        ->name('motor-flujos')
        ->middleware('role:admin|admin_general|admin_unidad');
    
    // Estadísticas y reportes
    Route::get('/dashboard/area', [DashboardController::class, 'estadisticasPorArea'])
        ->name('dashboard.area');
    Route::get('/dashboard/reporte', [DashboardController::class, 'reporte'])
        ->name('dashboard.reporte');
    Route::get('/dashboard/buscar', [DashboardController::class, 'buscar'])
        ->name('dashboard.buscar');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD ADMIN (motor de dashboards)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/dashboard')
    ->name('dashboards.motor.')
    ->group(function () {
        Route::get('/', [DashboardMotorController::class, 'index'])->name('index');
        Route::post('/assign', [DashboardMotorController::class, 'guardarAsignaciones'])->name('assign');
        Route::post('/assign-user', [DashboardMotorController::class, 'guardarAsignacionUsuario'])->name('assign-user');
        Route::post('/assign-secretaria', [DashboardMotorController::class, 'guardarAsignacionSecretaria'])->name('assign-secretaria');
        Route::post('/assign-unidad', [DashboardMotorController::class, 'guardarAsignacionUnidad'])->name('assign-unidad');
    });

/*
|--------------------------------------------------------------------------
| CONTRATOS DE APLICACIONES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin|admin_general|admin_secretaria|gobernador|secretario|jefe_unidad'])
    ->group(function () {
        Route::post('/contratos-aplicaciones/adicionar-secop', [ContratoAplicacionController::class, 'adicionarDesdeSecop'])
            ->name('contratos-aplicaciones.adicionar-secop');
        Route::post('/contratos-aplicaciones/sincronizar-secop', [ContratoAplicacionController::class, 'sincronizarSecop'])
            ->name('contratos-aplicaciones.sincronizar-secop');
        Route::post('/contratos-aplicaciones/{contratos_aplicacione}/sincronizar-secop', [ContratoAplicacionController::class, 'sincronizarContrato'])
            ->name('contratos-aplicaciones.sincronizar-uno');
        Route::patch('/contratos-aplicaciones/{contratos_aplicacione}/activo', [ContratoAplicacionController::class, 'actualizarActivo'])
            ->name('contratos-aplicaciones.actualizar-activo');
        Route::resource('contratos-aplicaciones', ContratoAplicacionController::class)
            ->except(['create', 'store', 'edit', 'update']);
    });

/*
|--------------------------------------------------------------------------
| PAA (Plan Anual de Adquisiciones)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin|planeacion'])->prefix('paa')->name('paa.')->group(function () {
    Route::get('/',              [PAAController::class, 'index'])->name('index');
    Route::get('/crear',         [PAAController::class, 'create'])->name('create');
    Route::post('/',             [PAAController::class, 'store'])->name('store');
    Route::get('/exportar/csv',  [PAAController::class, 'exportarCSV'])->name('exportar.csv');
    Route::get('/exportar/pdf',  [PAAController::class, 'exportarPDF'])->name('exportar.pdf');
    Route::post('/verificar',    [PAAController::class, 'verificarInclusion'])->name('verificar');
    Route::get('/{paa}',         [PAAController::class, 'show'])->name('show');
    Route::get('/{paa}/editar',  [PAAController::class, 'edit'])->name('edit');
    Route::put('/{paa}',         [PAAController::class, 'update'])->name('update');
    Route::get('/{paa}/certificado', [PAAController::class, 'certificadoInclusion'])->name('certificado');
});

/*
|--------------------------------------------------------------------------
| ALERTAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('alertas')->name('alertas.')->group(function () {
    Route::get('/', [AlertaController::class, 'index'])->name('index');
    Route::get('/abrir', [AlertaController::class, 'abrirCentro'])->name('abrir');
    Route::post('/{alerta}/leer', [AlertaController::class, 'marcarLeida'])->name('leer');
    Route::post('/leer-todas', [AlertaController::class, 'marcarTodasLeidas'])->name('leer.todas');
    Route::delete('/{alerta}', [AlertaController::class, 'destroy'])->name('destroy');
    Route::get('/widget', [AlertaController::class, 'widget'])->name('widget');
});

/*
|--------------------------------------------------------------------------
| SOLICITUDES DE DOCUMENTOS
|--------------------------------------------------------------------------
| Para áreas que reciben solicitudes de otras áreas (compras, contabilidad, etc.)
*/
Route::middleware(['auth'])->prefix('solicitudes')->name('solicitudes.')->group(function () {
    Route::get('/', [SolicitudDocumentosController::class, 'index'])->name('index');
    Route::get('/{proceso}', [SolicitudDocumentosController::class, 'detalle'])->name('detalle');
});

/*
|--------------------------------------------------------------------------
| REPORTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReportesController::class, 'index'])->name('index');
    Route::get('/estado-general', [ReportesController::class, 'estadoGeneral'])->name('estado.general');
    Route::get('/por-dependencia', [ReportesController::class, 'porDependencia'])->name('por.dependencia');
    Route::get('/actividad-actor', [ReportesController::class, 'actividadPorActor'])->name('actividad.actor');
    Route::get('/auditoria/{proceso}', [ReportesController::class, 'auditoria'])->name('auditoria');
    Route::get('/certificados-vencer', [ReportesController::class, 'certificadosVencer'])->name('certificados.vencer');
    Route::get('/eficiencia', [ReportesController::class, 'eficiencia'])->name('eficiencia');
});

/*
|- -------------------------------------------------------------------------
|- PROCESOS
|- -------------------------------------------------------------------------
|- - Crear/store: Roles permitidos con permiso procesos.crear
|- - Index/show:  Todos los autenticados (cada rol ve lo suyo)
|- NOTA: /procesos/crear debe definirse ANTES de /procesos/{id}
|- -------------------------------------------------------------------------
*/

// Crear solicitud (roles permitidos + controlado por permisos)
Route::middleware(['auth', 'role:admin|planeacion|unidad_solicitante', 'permission:procesos.crear'])->group(function () {
    Route::get('/procesos/crear', [ProcesoController::class, 'create'])
        ->name('procesos.create');
    Route::post('/procesos', [ProcesoController::class, 'store'])
        ->name('procesos.store');
});

// Ver lista y detalle: todos los usuarios autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/procesos', [ProcesoController::class, 'index'])
        ->name('procesos.index');
    Route::get('/procesos/{id}', [ProcesoController::class, 'show'])
        ->name('procesos.show');
});

// API interna: unidades por secretaría (para selects dinámicos)
// Nota: también disponible en routes/api.php como api.secretarias.unidades
Route::middleware(['auth'])->get('/api/secretarias/{secretariaId}/unidades', function ($secretariaId) {
    return response()->json(
        \Illuminate\Support\Facades\DB::table('unidades')
            ->where('secretaria_id', $secretariaId)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
    );
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
});

/*
|--------------------------------------------------------------------------
| WORKFLOW FILES (gestión de archivos por etapa)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('workflow/procesos')->name('workflow.files.')->group(function () {

    Route::post('/{proceso}/archivos',
        [WorkflowFilesController::class, 'store'])
        ->name('store');

    Route::get('/{proceso}/archivos',
        [WorkflowFilesController::class, 'index'])
        ->name('index');

    Route::get('/archivos/{archivo}/descargar',
        [WorkflowFilesController::class, 'download'])
        ->name('download');

    Route::delete('/archivos/{archivo}',
        [WorkflowFilesController::class, 'destroy'])
        ->name('destroy');

    // Aprobación, rechazo y reemplazo de archivos
    Route::post('/archivos/{archivo}/aprobar',
        [WorkflowFilesController::class, 'aprobar'])
        ->name('aprobar');

    Route::post('/archivos/{archivo}/rechazar',
        [WorkflowFilesController::class, 'rechazar'])
        ->name('rechazar');

    Route::post('/archivos/{archivo}/reemplazar',
        [WorkflowFilesController::class, 'reemplazar'])
        ->name('reemplazar');

    // Preview y control de versiones
    Route::get('/archivos/{archivo}/preview',
        [WorkflowFilesController::class, 'preview'])
        ->name('preview');

    Route::get('/archivos/{archivo}/historial',
        [WorkflowFilesController::class, 'historialVersiones'])
        ->name('historial');
});

/*
|--------------------------------------------------------------------------
| REPOSITORIO SIA OBSERVA (interno, sin navegación visible)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('interno/sia-observa')->name('sia-observa.')->group(function () {
    Route::get('/procesos/{proceso}/paquete-final', [ProcesoSiaObservaController::class, 'descargarPaqueteFinal'])
        ->name('paquete-final.descargar');
    Route::get('/procesos/{proceso}/archivos', [ProcesoSiaObservaController::class, 'index'])
        ->name('archivos.index');
    Route::post('/procesos/{proceso}/archivos', [ProcesoSiaObservaController::class, 'store'])
        ->name('archivos.store');
    Route::get('/archivos/{archivo}/descargar', [ProcesoSiaObservaController::class, 'descargarArchivo'])
        ->name('archivos.descargar');
    Route::post('/procesos/{proceso}/accesos/rol', [ProcesoSiaObservaController::class, 'asignarRol'])
        ->name('accesos.rol');
    Route::post('/procesos/{proceso}/accesos/usuario', [ProcesoSiaObservaController::class, 'asignarUsuario'])
        ->name('accesos.usuario');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin|admin_general'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('usuarios', UserController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('permisos', PermissionController::class)->except(['show']);
        Route::resource('secretarias', SecretariaController::class)->except(['show']);
        Route::resource('unidades', AdminUnidadController::class)->except(['show']);

        // AJAX: unidades por secretaría (para selects dinámicos)
        Route::get('secretarias/{secretaria}/unidades', [AdminUnidadController::class, 'porSecretaria'])
            ->name('secretarias.unidades');

        Route::get('logs', [LogsController::class, 'index'])->name('logs');
        Route::get('logs/proceso/{proceso}', [LogsController::class, 'show'])->name('logs.proceso');

        // Repositorio final SIA Observa (gestion admin)
        Route::get('sia-observa', [SiaObservaAdminController::class, 'index'])->name('sia-observa.index');
        Route::get('sia-observa/{proceso}', [SiaObservaAdminController::class, 'show'])->name('sia-observa.show');
        Route::post('sia-observa/{proceso}/accesos/rol', [SiaObservaAdminController::class, 'asignarRol'])->name('sia-observa.accesos.rol');
        Route::post('sia-observa/{proceso}/accesos/usuario', [SiaObservaAdminController::class, 'asignarUsuario'])->name('sia-observa.accesos.usuario');
        Route::post('sia-observa/{proceso}/accesos/{acceso}/estado', [SiaObservaAdminController::class, 'cambiarEstado'])->name('sia-observa.accesos.estado');

        // Guías de Agente Estiven
        Route::resource('estiven-guides', EstivenGuideController::class)
            ->except(['show'])
            ->names('estiven-guides');
    });

/*
|--------------------------------------------------------------------------
| Agente Estiven – Solicitud de ayuda por correo
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->post('/estiven/solicitar-ayuda', [EstivenHelpController::class, 'solicitarAyuda'])
    ->name('estiven.solicitar-ayuda');

/*
|--------------------------------------------------------------------------
| ÁREAS / SECRETARÍAS
|--------------------------------------------------------------------------
| Cada área solo puede entrar a su bandeja
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|unidad_solicitante'])
    ->prefix('unidad')
    ->name('unidad.')
    ->group(function () {
        Route::get('/', [UnidadController::class, 'index'])->name('index');
        Route::get('/procesos/{proceso}', [UnidadController::class, 'show'])->name('show');
        Route::match(['get', 'post'], '/crear', [UnidadController::class, 'crear'])->name('crear');
        Route::post('/procesos/{proceso}/enviar', [UnidadController::class, 'enviar'])->name('enviar');
        Route::post('/procesos/{proceso}/recibir', [UnidadController::class, 'recibir'])->name('recibir');
        Route::post('/procesos/{proceso}/doc/{check}/recibido-fisico', [UnidadController::class, 'marcarRecibidoFisico'])->name('recibido.fisico');
        Route::post('/procesos/{proceso}/doc/{check}/subir-archivo', [UnidadController::class, 'subirArchivoDigital'])->name('subir.archivo');
        Route::post('/procesos/{proceso}/aprobar-etapa2', [UnidadController::class, 'aprobarEtapa2'])->name('aprobar.etapa2');
    });

Route::middleware(['auth', 'role:admin|planeacion'])
    ->prefix('planeacion')
    ->name('planeacion.')
    ->group(function () {
        Route::get('/', [PlaneacionController::class, 'index'])->name('index');
        Route::get('/procesos/{proceso}', [PlaneacionController::class, 'show'])->name('show');
        Route::post('/procesos/{proceso}/aprobar', [PlaneacionController::class, 'aprobar'])->name('aprobar');
        Route::post('/procesos/{proceso}/rechazar', [PlaneacionController::class, 'rechazar'])->name('rechazar');
        Route::get('/reportes', [PlaneacionController::class, 'reportes'])->name('reportes');
    });

Route::middleware(['auth', 'role:admin|hacienda'])
    ->prefix('hacienda')
    ->name('hacienda.')
    ->group(function () {
        Route::get('/', [HaciendaController::class, 'index'])->name('index');
        Route::get('/procesos/{proceso}', [HaciendaController::class, 'show'])->name('show');
        Route::post('/procesos/{proceso}/cdp', [HaciendaController::class, 'emitirCDP'])->name('cdp');
        Route::post('/procesos/{proceso}/rp', [HaciendaController::class, 'emitirRP'])->name('rp');
        Route::post('/procesos/{proceso}/aprobar', [HaciendaController::class, 'aprobar'])->name('aprobar');
        Route::post('/procesos/{proceso}/rechazar', [HaciendaController::class, 'rechazar'])->name('rechazar');
        Route::get('/reportes', [HaciendaController::class, 'reportes'])->name('reportes');
    });

Route::middleware(['auth', 'role:admin|juridica'])
    ->prefix('juridica')
    ->name('juridica.')
    ->group(function () {
        Route::get('/', [JuridicaController::class, 'index'])->name('index');
        Route::get('/procesos/{proceso}', [JuridicaController::class, 'show'])->name('show');
        Route::post('/procesos/{proceso}/ajustado', [JuridicaController::class, 'emitirAjustado'])->name('ajustado');
        Route::post('/procesos/{proceso}/verificar-contratista', [JuridicaController::class, 'verificarContratista'])->name('verificar.contratista');
        Route::post('/procesos/{proceso}/polizas', [JuridicaController::class, 'aprobarPolizas'])->name('polizas');
        Route::post('/procesos/{proceso}/aprobar', [JuridicaController::class, 'aprobar'])->name('aprobar');
        Route::post('/procesos/{proceso}/rechazar', [JuridicaController::class, 'rechazar'])->name('rechazar');
        Route::get('/reportes', [JuridicaController::class, 'reportes'])->name('reportes');
    });

Route::middleware(['auth', 'role:admin|secop'])
    ->prefix('secop')
    ->name('secop.')
    ->group(function () {
        Route::get('/', [SecopController::class, 'index'])->name('index');
        Route::get('/procesos/{proceso}', [SecopController::class, 'show'])->name('show');
        Route::post('/procesos/{proceso}/publicar', [SecopController::class, 'publicar'])->name('publicar');
        Route::post('/procesos/{proceso}/contrato', [SecopController::class, 'registrarContrato'])->name('contrato');
        Route::post('/procesos/{proceso}/acta-inicio', [SecopController::class, 'registrarActaInicio'])->name('acta.inicio');
        Route::post('/procesos/{proceso}/cerrar', [SecopController::class, 'cerrar'])->name('cerrar');
        Route::post('/procesos/{proceso}/aprobar', [SecopController::class, 'aprobar'])->name('aprobar');
        Route::get('/reportes', [SecopController::class, 'reportes'])->name('reportes');
    });

/*
|--------------------------------------------------------------------------
| CONSULTA SECOP II – Datos Abiertos (datos.gov.co)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin|unidad_solicitante|gobernador'])
    ->prefix('secop-consulta')
    ->name('secop.consulta')
    ->group(function () {
        Route::get('/', [SecopConsultaController::class, 'index']);
        Route::get('/contrato/{idContrato}', [SecopConsultaController::class, 'detalle'])->where('idContrato', '.*')->name('.detalle');
        Route::post('/contrato/{idContrato}/refrescar', [SecopConsultaController::class, 'refrescar'])->where('idContrato', '.*')->name('.refrescar');
    });

use App\Http\Controllers\ContractProcessController;
use App\Http\Controllers\ProcessDocumentController;
use App\Http\Controllers\ProcesoContratacionDirectaController;

/*
|--------------------------------------------------------------------------
| MÓDULO CD-PN: Contratación Directa – Persona Natural (State Machine)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('proceso-cd')->name('proceso-cd.')->group(function () {

    // Listado y creación
    Route::get('/', [ProcesoContratacionDirectaController::class, 'index'])->name('index');
    Route::get('/crear', [ProcesoContratacionDirectaController::class, 'create'])
        ->middleware('role:admin|unidad_solicitante')
        ->name('create');
    Route::post('/', [ProcesoContratacionDirectaController::class, 'store'])
        ->middleware('role:admin|unidad_solicitante')
        ->name('store');

    // Detalle
    Route::get('/{procesoCD}', [ProcesoContratacionDirectaController::class, 'show'])->name('show');

    // Transición de estado (middleware valida rol por estado)
    Route::post('/{procesoCD}/transicionar', [ProcesoContratacionDirectaController::class, 'transicionar'])
        ->middleware('validar.rol.proceso.cd')
        ->name('transicionar');

    // Validaciones paralelas (Etapa 2)
    Route::post('/{procesoCD}/validacion', [ProcesoContratacionDirectaController::class, 'registrarValidacion'])
        ->middleware('validar.rol.proceso.cd')
        ->name('validacion');

    // Firmas (Etapa 5)
    Route::post('/{procesoCD}/firma', [ProcesoContratacionDirectaController::class, 'registrarFirma'])
        ->middleware('validar.rol.proceso.cd')
        ->name('firma');

    // Devolución (Etapa 4 y 5)
    Route::post('/{procesoCD}/devolver', [ProcesoContratacionDirectaController::class, 'devolver'])
        ->middleware('validar.rol.proceso.cd')
        ->name('devolver');

    // Documentos
    Route::post('/{procesoCD}/documentos', [ProcesoContratacionDirectaController::class, 'subirDocumento'])
        ->name('documentos.subir');
    Route::get('/{procesoCD}/documentos/{documento}/descargar', [ProcesoContratacionDirectaController::class, 'descargarDocumento'])
        ->name('documentos.descargar');
    Route::post('/{procesoCD}/documentos/{documento}/aprobar', [ProcesoContratacionDirectaController::class, 'aprobarDocumento'])
        ->middleware('validar.rol.proceso.cd')
        ->name('documentos.aprobar');

    // Cancelar
    Route::post('/{procesoCD}/cancelar', [ProcesoContratacionDirectaController::class, 'cancelar'])
        ->middleware('role:admin')
        ->name('cancelar');

    // Auditoría
    Route::get('/{procesoCD}/auditoria', [ProcesoContratacionDirectaController::class, 'auditoria'])
        ->name('auditoria');
});

/*
|--------------------------------------------------------------------------
| MÓDULO WORKFLOW - Contratación Directa Persona Natural (legacy)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('contract-processes')->name('contract-processes.')->group(function () {
    // CRUD básico de procesos
    Route::get('/', [ContractProcessController::class, 'index'])->name('index');
    Route::get('/create', [ContractProcessController::class, 'create'])->name('create');
    Route::post('/', [ContractProcessController::class, 'store'])->name('store');
    Route::get('/{contractProcess}', [ContractProcessController::class, 'show'])->name('show');
    Route::put('/{contractProcess}', [ContractProcessController::class, 'update'])->name('update');
    
    // Visualización por etapas
    Route::get('/{contractProcess}/step/{step}', [ContractProcessController::class, 'showStep'])
        ->name('step')
        ->where('step', '[0-9]');
    
    // Acciones del workflow
    Route::post('/{contractProcess}/advance', [ContractProcessController::class, 'advance'])->name('advance');
    Route::post('/{contractProcess}/return', [ContractProcessController::class, 'returnToStep'])->name('return');
    Route::post('/{contractProcess}/cancel', [ContractProcessController::class, 'cancel'])->name('cancel');
    
    // Auditoría y exportación
    Route::get('/{contractProcess}/audit-log', [ContractProcessController::class, 'auditLog'])->name('audit-log');
    Route::get('/{contractProcess}/export', [ContractProcessController::class, 'export'])->name('export');
    
    // Gestión de documentos
    Route::post('/{contractProcess}/documents', [ProcessDocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/{contractProcess}/documents/{document}/download', [ProcessDocumentController::class, 'download'])->name('documents.download');
    Route::delete('/{contractProcess}/documents/{document}', [ProcessDocumentController::class, 'destroy'])->name('documents.destroy');
    Route::put('/{contractProcess}/documents/{document}/replace', [ProcessDocumentController::class, 'replace'])->name('documents.replace');
    
    // Aprobación de documentos
    Route::post('/{contractProcess}/documents/{document}/approve', [ProcessDocumentController::class, 'approve'])->name('documents.approve');
    Route::post('/{contractProcess}/documents/{document}/reject', [ProcessDocumentController::class, 'reject'])->name('documents.reject');
    Route::post('/{contractProcess}/documents/{document}/request-fixes', [ProcessDocumentController::class, 'requestFixes'])->name('documents.request-fixes');
    
    // Firmas
    Route::post('/{contractProcess}/documents/{document}/sign', [ProcessDocumentController::class, 'addSignature'])->name('documents.sign');
});

// Ruta global para documentos próximos a vencer
Route::get('/documents/expiring', [ProcessDocumentController::class, 'expiring'])
    ->middleware('auth')
    ->name('documents.expiring');

// RF-17: Log de eventos de autenticación (solo admin)
Route::get('/admin/auth-events', [AuthEventsController::class, 'index'])
    ->middleware('auth')
    ->name('admin.auth-events');

// RF-14: Restablecimiento de contraseña por administrador (sin email)
Route::get('/admin/usuarios/{usuario}/reset-password', [ResetPasswordAdminController::class, 'show'])
    ->middleware('auth')
    ->name('admin.reset-password.show');
Route::post('/admin/usuarios/{usuario}/reset-password', [ResetPasswordAdminController::class, 'generate'])
    ->middleware('auth')
    ->name('admin.reset-password.generate');

// RF-20: Rastreo físico de documentos por código (solo admin)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/tracking/buscar', [TrackingController::class, 'buscar'])->name('tracking.buscar');
    Route::post('/tracking/registrar', [TrackingController::class, 'registrar'])->name('tracking.registrar');
});

require __DIR__.'/auth.php';
