# DOCUMENTACION DEL CODIGO ACTUAL

Fecha: 2026-04-09
Proyecto: Sistema de Seguimiento de Documentos - Gobernacion de Caldas

## 1. Resumen tecnico

Este repositorio esta construido sobre Laravel y combina:

- Backend PHP con arquitectura MVC + servicios de dominio.
- Frontend Blade + React para modulos dinamicos (motor de flujos y dashboard builder).
- Control de acceso por roles/permisos con Spatie.
- Flujos de proceso por etapas para contratacion y gestion documental.

## 2. Stack y dependencias

Backend (composer):

- PHP ^8.2
- laravel/framework ^12.0
- spatie/laravel-permission ^6.24

Frontend (npm):

- Vite 7
- React 19
- GridStack 12
- Playwright para E2E

Archivos de referencia:

- `composer.json`
- `package.json`

## 3. Estructura general del proyecto

Capas principales:

- `app/` -> codigo de negocio y HTTP.
- `routes/` -> definicion de rutas web/API/console/auth.
- `resources/views/` -> vistas Blade separadas en `backend`, `frontend` y carpetas compartidas.
- `resources/js/` -> apps React y modulos JS.
- `database/migrations/` -> definicion de esquema.
- `database/seeders/` -> carga de datos base.
- `tests/` -> pruebas de API, feature, E2E y workflow.

## 4. Inventario de backend

Conteo actual por capa:

- `app/Http/Controllers` : 55 archivos PHP
- `app/Models` : 49 archivos PHP
- `app/Services` : 7 archivos PHP
- `app/Policies` : 1 archivo PHP
- `app/Listeners` : 1 archivo PHP
- `app/Console/Commands` : 3 archivos PHP
- `database/migrations` : 61 migraciones
- `database/seeders` : 21 seeders

### 4.1 Controladores principales por dominio

Nucleo de procesos y workflow:

- `app/Http/Controllers/ProcesoController.php`
- `app/Http/Controllers/WorkflowController.php`
- `app/Http/Controllers/WorkflowFilesController.php`
- `app/Http/Controllers/ProcesoContratacionDirectaController.php`
- `app/Http/Controllers/ContractProcessController.php`
- `app/Http/Controllers/ProcessDocumentController.php`

Dashboards y reportes:

- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/DashboardBuilderController.php`
- `app/Http/Controllers/DashboardMotorController.php`
- `app/Http/Controllers/ReportesController.php`

Modulos de area:

- `app/Http/Controllers/Area/UnidadController.php`
- `app/Http/Controllers/Area/PlaneacionController.php`
- `app/Http/Controllers/Area/HaciendaController.php`
- `app/Http/Controllers/Area/JuridicaController.php`
- `app/Http/Controllers/Area/SecopController.php`
- `app/Http/Controllers/Area/SolicitudDocumentosController.php`

Administracion:

- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/RoleController.php`
- `app/Http/Controllers/Admin/PermissionController.php`
- `app/Http/Controllers/Admin/SecretariaController.php`
- `app/Http/Controllers/Admin/UnidadController.php`
- `app/Http/Controllers/Admin/LogsController.php`

API:

- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/UserApiController.php`
- `app/Http/Controllers/Api/SecretariaApiController.php`
- `app/Http/Controllers/Api/UnidadApiController.php`
- `app/Http/Controllers/Api/RolPermisoApiController.php`
- `app/Http/Controllers/Api/MotorFlujoController.php`

### 4.2 Servicios de dominio

Servicios en `app/Services/`:

- `AlertaService.php`
- `ArchivosPorAreaService.php`
- `ContratoDirectoPNStateMachine.php`
- `NotificacionCDService.php`
- `SecopDatosAbiertoService.php`
- `ValidacionContrataciónService.php`
- `WorkflowEngine.php`

### 4.3 Middleware y providers

Middleware de control de acceso y validacion:

- `app/Http/Middleware/CheckAdminUnidad.php`
- `app/Http/Middleware/CheckPermiso.php`
- `app/Http/Middleware/CheckSecretariaAccess.php`
- `app/Http/Middleware/CheckUsuarioActivo.php`
- `app/Http/Middleware/ValidateRolProcesoCD.php`

Providers:

- `app/Providers/AppServiceProvider.php`
- `app/Providers/AuthEventServiceProvider.php`
- `app/Providers/DashboardBuilderServiceProvider.php`

## 5. Modelo de datos

Modelos relevantes por dominio:

Usuarios/seguridad:

- `app/Models/User.php`
- `app/Models/AuthEvent.php`
- `app/Models/Secretaria.php`
- `app/Models/Unidad.php`

Procesos y etapas:

- `app/Models/Proceso.php`
- `app/Models/ProcesoEtapa.php`
- `app/Models/ProcesoEtapaCheck.php`
- `app/Models/ProcesoEtapaArchivo.php`
- `app/Models/ProcesoAuditoria.php`
- `app/Models/TrackingEvento.php`

Contratacion directa (CD-PN):

- `app/Models/ProcesoContratacionDirecta.php`
- `app/Models/ProcesoCDDocumento.php`
- `app/Models/ProcesoCDAuditoria.php`

Motor de flujos dinamico:

- `app/Models/Flujo.php`
- `app/Models/FlujoVersion.php`
- `app/Models/FlujoPaso.php`
- `app/Models/FlujoPasoDocumento.php`
- `app/Models/FlujoPasoResponsable.php`
- `app/Models/FlujoPasoCondicion.php`
- `app/Models/FlujoInstancia.php`
- `app/Models/FlujoInstanciaPaso.php`
- `app/Models/FlujoInstanciaDoc.php`

Dashboard:

- `app/Models/DashboardWidget.php`
- `app/Models/DashboardPlantilla.php`
- `app/Models/DashboardRolAsignacion.php`
- `app/Models/DashboardUnidadAsignacion.php`
- `app/Models/DashboardSecretariaAsignacion.php`
- `app/Models/DashboardUsuarioAsignacion.php`

Supervision y ejecucion:

- `app/Models/InformeSupervision.php`
- `app/Models/PagoContrato.php`
- `app/Models/ModificacionContractual.php`

## 6. Mapa de rutas

### 6.1 Rutas web (`routes/web.php`)

Grupos funcionales principales:

- Dashboard base y metricas.
- PAA (plan anual de adquisiciones).
- Alertas y solicitudes de documentos.
- Reportes y auditoria.
- Procesos + workflow interno (`recibir`, `enviar`, checks).
- Gestion de archivos de workflow (subida, aprobacion, rechazo, reemplazo, historial).
- Administracion (usuarios, roles, permisos, secretarias, unidades, logs).
- Bandejas por area (unidad, planeacion, hacienda, juridica, secop).
- Consulta SECOP datos abiertos.
- Modificaciones contractuales.
- Modulo CD-PN (state machine por etapa).
- Modulo `contract-processes` (legacy).
- Tracking fisico y supervision de pagos.

### 6.2 Rutas API (`routes/api.php`)

Bloques API principales:

- `api/auth/*` autenticacion y validacion de permisos.
- CRUD de secretarias, unidades, usuarios.
- Gestion de roles y permisos.
- Motor de flujos configurable por secretaria.
- APIs de dashboard builder/viewer.

## 7. Frontend y vistas

### 7.1 Vistas Blade

Se identifican 129 vistas Blade activas.

Carpetas de vistas por capa:

- `resources/views/backend/*` -> modulos funcionales internos (admin, areas, procesos, reportes, dashboards, etc.).
- `resources/views/frontend/*` -> autenticacion, perfil y vistas publicas.
- `resources/views/layouts`, `resources/views/components`, `resources/views/partials` -> vistas compartidas.

Vistas clave de dashboard:

- `resources/views/backend/dashboard/admin.blade.php`
- `resources/views/backend/dashboard/reporte.blade.php`
- `resources/views/backend/dashboard/show.blade.php`

### 7.2 Scripts y modulos JS/React

Entradas principales:

- `resources/js/app.js`
- `resources/js/dashboard-builder.jsx`
- `resources/js/dashboard-motor.jsx`
- `resources/js/motor-flujos.jsx`

Modulo dashboard builder:

- `resources/js/modules/dashboard-builder/components/*`
- `resources/js/modules/dashboard-builder/hooks/*`
- `resources/js/modules/dashboard-builder/store/dashboardStore.js`

Modulo dashboard v2:

- `resources/js/dashboard-v2/DashboardMotorV2.jsx`
- `resources/js/dashboard-v2/components/*`
- `resources/js/dashboard-v2/hooks/*`

## 8. Pruebas

Distribucion actual de pruebas por carpeta en `tests/`:

- `api` : 1
- `documents` : 1
- `e2e` : 8
- `Feature` : 9
- `helpers` : 1
- `motor-flujos` : 2
- `procesos` : 2
- `responsive` : 1
- `smoke` : 1
- `Unit` : 1
- `users` : 1
- `workflow` : 2

Comandos de ejecucion en `package.json`:

- `npm run test` (Playwright UI)
- `npm run test:run`
- `npm run test:auth`
- `npm run test:workflow`
- `npm run test:full`

## 9. Flujo operativo recomendado

Desarrollo local:

1. Instalar dependencias PHP y Node.
2. Configurar `.env`.
3. Ejecutar migraciones y seeders.
4. Levantar backend + Vite.
5. Validar modulo trabajado con pruebas puntuales.

Comandos base:

- `composer setup`
- `composer dev`
- `npm run dev`

## 10. Convenciones y mantenimiento

- Mantener logica de negocio en `Services` y no en vistas.
- Mantener control de acceso en middleware/policies y no duplicarlo en front.
- Agrupar nuevas rutas por prefijo y middleware por dominio.
- Mantener trazabilidad en auditoria para acciones criticas de proceso.
- Evitar versionar artefactos temporales de pruebas.

## 11. Estado de limpieza relacionado

Este documento se entrega junto con:

- `REPORTE_LIMPIEZA_REPOSITORIO_2026-04-09.md`

A la fecha ya se excluyen artefactos de ejecucion en `.gitignore`:

- `/playwright-report`
- `/test-results`
- carpeta de metadata local del asistente (regla en `.gitignore`)
