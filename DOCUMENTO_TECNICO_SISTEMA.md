# DOCUMENTO TÉCNICO DEL SISTEMA
## Sistema de Seguimiento de Documentos Contractuales
### Gobernación de Caldas

---

**Versión:** 1.0
**Fecha:** Abril 2026
**Elaborado por:** Equipo de Tecnología — Gobernación de Caldas
**Clasificación:** Documento Técnico Interno
**Estado:** Aprobado

---

## TABLA DE CONTENIDOS

1. [Introducción](#1-introducción)
2. [Arquitectura General](#2-arquitectura-general)
3. [Tecnologías Utilizadas](#3-tecnologías-utilizadas)
4. [Estructura del Sistema](#4-estructura-del-sistema)
5. [Base de Datos](#5-base-de-datos)
6. [Motor de Flujos — Aspectos Técnicos](#6-motor-de-flujos--aspectos-técnicos)
7. [Gestión de Usuarios y Seguridad](#7-gestión-de-usuarios-y-seguridad)
8. [APIs y Servicios](#8-apis-y-servicios)
9. [Integraciones](#9-integraciones)
10. [Despliegue e Infraestructura](#10-despliegue-e-infraestructura)
11. [Logs y Monitoreo](#11-logs-y-monitoreo)
12. [Mantenimiento](#12-mantenimiento)
13. [Limitaciones Técnicas](#13-limitaciones-técnicas)
14. [Anexos](#14-anexos)

---

## 1. INTRODUCCIÓN

### 1.1 Propósito del Documento

Este documento describe la arquitectura técnica, las tecnologías, la estructura del código, el modelo de datos, las APIs y los procedimientos de despliegue del **Sistema de Seguimiento de Documentos Contractuales** de la Gobernación de Caldas. Está dirigido a desarrolladores, administradores de sistemas y personal técnico de TI.

### 1.2 Alcance Técnico

El sistema es una aplicación web de pila completa (full-stack) que combina un backend en Laravel 12.x con un frontend híbrido (Blade + React 19). Está desplegado sobre un servidor Linux con Nginx o Apache, base de datos MySQL 8.0+ e integración con servicios externos (SMTP Office 365 y API SECOP II).

### 1.3 Audiencia

- Desarrolladores del equipo de TI de la Gobernación de Caldas.
- Administradores de servidores y bases de datos.
- Auditores técnicos de sistemas de información.

### 1.4 Versión del Sistema

| Componente | Versión |
|---|---|
| Laravel | 12.x |
| PHP | 8.2+ |
| React | 19.2.4 |
| React DOM | 19.2.4 |
| Vite | 7.0.7 |
| Tailwind CSS | 3.x |
| MySQL | 8.0+ |
| Spatie Laravel Permission | 6.24 |
| @xyflow/react (React Flow) | 12.10.1 |
| Recharts | 2.x |
| Node.js | 20+ |

---

## 2. ARQUITECTURA GENERAL

### 2.1 Patrón Arquitectónico

El sistema implementa el patrón **MVC (Model-View-Controller)** de Laravel, complementado con los siguientes patrones:

- **Repository Pattern:** Encapsulación del acceso a datos en repositorios por entidad.
- **Service Layer:** Lógica de negocio encapsulada en clases de servicio independientes del controlador.
- **Observer Pattern:** Eventos y listeners de Laravel para auditoría automática.
- **Factory Pattern:** Creación dinámica de widgets en el Motor de Dashboards.
- **Strategy Pattern:** Validaciones diferenciadas por tipo de flujo contractual.

### 2.2 Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENTE (Navegador)                      │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │  Blade Templates (HTML/CSS/Tailwind)                     │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │    │
│  │  │ motor-flujos │  │dashboard-    │  │dashboard-    │  │    │
│  │  │ .jsx         │  │motor.jsx     │  │builder.jsx   │  │    │
│  │  │ (React Flow) │  │ (Recharts)   │  │ (Recharts)   │  │    │
│  │  └──────────────┘  └──────────────┘  └──────────────┘  │    │
│  └─────────────────────────────────────────────────────────┘    │
└───────────────────────────┬─────────────────────────────────────┘
                            │ HTTPS (HTTP/1.1 + WebSocket)
┌───────────────────────────▼─────────────────────────────────────┐
│                    SERVIDOR WEB (Nginx / Apache)                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │              Laravel 12.x (PHP 8.2+)                     │    │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐  │    │
│  │  │ Routes   │  │Controllers│  │ Services │  │ Models │  │    │
│  │  │ web.php  │  │ (MVC)    │  │ (Biz.    │  │(Elo-   │  │    │
│  │  │ api.php  │  │          │  │  Logic)  │  │quent)  │  │    │
│  │  └──────────┘  └──────────┘  └──────────┘  └────────┘  │    │
│  │  ┌──────────┐  ┌──────────┐  ┌────────────────────────┐ │    │
│  │  │ Middleware│  │ Events / │  │  Spatie Permission     │ │    │
│  │  │ (Auth,   │  │ Listeners│  │  (RBAC - 13 roles)     │ │    │
│  │  │ Perms)   │  │ (Audit)  │  │                        │ │    │
│  │  └──────────┘  └──────────┘  └────────────────────────┘ │    │
│  └─────────────────────────────────────────────────────────┘    │
└───────────────────────────┬─────────────────────────────────────┘
                            │
        ┌───────────────────┴──────────────────────┐
        │                                           │
┌───────▼────────┐                       ┌──────────▼──────────┐
│   MySQL 8.0+   │                       │  Servicios Externos  │
│  (61 tablas)   │                       │  ┌───────────────┐  │
│                │                       │  │ SMTP Office365│  │
│  - usuarios    │                       │  │ (Notif.)      │  │
│  - procesos    │                       │  └───────────────┘  │
│  - flujos      │                       │  ┌───────────────┐  │
│  - documentos  │                       │  │ API SECOP II  │  │
│  - dashboards  │                       │  │ (Contratos)   │  │
│  - auditoria   │                       │  └───────────────┘  │
└────────────────┘                       └─────────────────────┘
```

### 2.3 Comunicación Frontend-Backend

- **Vistas Blade:** Renderizado en servidor para páginas completas.
- **API REST:** Comunicación asíncrona JSON entre componentes React y el backend Laravel.
- **CSRF:** Tokens CSRF en todos los formularios Blade y cabeceras de peticiones AJAX.
- **Sesiones:** Manejo de sesiones mediante cookies seguras con Laravel Session.

### 2.4 Gestión de Assets

- Los assets del frontend (JS, CSS) son compilados con **Vite 7.0.7**.
- React se monta en elementos `<div id="...">` dentro de las vistas Blade.
- Tailwind CSS procesa las clases en tiempo de compilación para producción (PurgeCSS).
- Los archivos compilados se publican en `/public/build/`.

---

## 3. TECNOLOGÍAS UTILIZADAS

### 3.1 Lenguajes de Programación

| Lenguaje | Versión | Uso |
|---|---|---|
| PHP | 8.2+ | Backend, lógica de negocio, ORM |
| JavaScript (ES2022+) | — | Frontend React, lógica de componentes |
| JSX | — | Componentes React |
| SQL | — | Consultas y migraciones MySQL |
| HTML5 | — | Plantillas Blade |
| CSS3 | — | Estilos (mediante Tailwind) |

### 3.2 Frameworks y Librerías

| Framework / Librería | Versión | Propósito |
|---|---|---|
| **Laravel** | 12.x | Framework backend PHP (MVC, ORM, routing, auth) |
| **React** | 19.2.4 | Biblioteca de componentes UI del frontend |
| **Vite** | 7.0.7 | Build system y HMR del frontend |
| **Tailwind CSS** | 3.x | Framework de utilidades CSS |
| **Spatie Permission** | 6.24 | RBAC (roles y permisos) |
| **@xyflow/react** | 12.10.1 | Editor visual de flujos (Motor de Flujos) |
| **Recharts** | 2.x | Gráficas del Motor de Dashboards |
| **Laravel Breeze** | — | Scaffolding de autenticación |
| **Axios** | — | Cliente HTTP para peticiones API desde React |

### 3.3 Base de Datos

| Componente | Versión | Uso |
|---|---|---|
| MySQL | 8.0+ | Base de datos principal en producción |
| SQLite | 3.x | Base de datos en ambiente de desarrollo/pruebas |

### 3.4 Servidor e Infraestructura

| Componente | Descripción |
|---|---|
| **Sistema Operativo** | Linux (Ubuntu 22.04 LTS recomendado) |
| **Servidor Web** | Nginx 1.24+ o Apache 2.4+ |
| **PHP-FPM** | Gestión de procesos PHP |
| **Composer** | Gestor de dependencias PHP |
| **npm** | Gestor de dependencias Node.js |
| **Git** | Control de versiones del código fuente |

### 3.5 Herramientas de Desarrollo y Pruebas

| Herramienta | Uso |
|---|---|
| **PHPUnit** | Pruebas unitarias e integración del backend |
| **Playwright** | Pruebas E2E del frontend |
| **Cypress** | Pruebas E2E automatizadas (194 casos) |
| **Artisan CLI** | CLI de Laravel para gestión del sistema |
| **Laravel Telescope** | Debugging y monitoreo en desarrollo |

---

## 4. ESTRUCTURA DEL SISTEMA

### 4.1 Frontend — Estructura de Carpetas

```
resources/
├── js/
│   ├── motor-flujos.jsx          # Editor visual React Flow (Motor de Flujos)
│   ├── dashboard-motor.jsx       # Dashboard interactivo con Recharts
│   ├── dashboard-builder.jsx     # Constructor visual drag-and-drop de dashboards
│   ├── components/
│   │   ├── FlowNode.jsx          # Nodo de etapa en el editor de flujos
│   │   ├── FlowEdge.jsx          # Conexión entre nodos
│   │   ├── WidgetKPI.jsx         # Widget de métrica numérica
│   │   ├── WidgetChart.jsx       # Widget de gráfica (Recharts)
│   │   ├── WidgetTable.jsx       # Widget de tabla dinámica
│   │   └── WidgetTimeline.jsx    # Widget de línea de tiempo
│   └── app.js                    # Entry point de Vite
├── css/
│   └── app.css                   # Estilos base con directivas Tailwind
└── views/
    ├── admin/                    # Panel administrativo (usuarios, roles, config)
    ├── areas/                    # Vistas por área funcional
    │   ├── unidad/               # Vistas del rol unidad/jefe-unidad
    │   ├── juridica/             # Vistas del rol jurídica
    │   ├── planeacion/           # Vistas del rol planeación
    │   ├── hacienda/             # Vistas del rol hacienda
    │   └── secop/                # Vistas del rol SECOP
    ├── auth/                     # Login, recuperar contraseña
    ├── dashboard/                # Dashboards estáticos por rol
    ├── dashboards/
    │   └── motor/                # Motor de dashboards dinámico
    ├── flujos/                   # Gestión de flujos (Motor de Flujos)
    ├── procesos/                 # Seguimiento de procesos
    ├── layouts/
    │   ├── app.blade.php         # Layout principal con sidebar
    │   └── guest.blade.php       # Layout para páginas públicas (login)
    └── components/               # Componentes Blade reutilizables
```

#### Componentes React Principales

**`motor-flujos.jsx`**
- Editor visual de flujos contractuales basado en `@xyflow/react`.
- Permite crear, editar, conectar y publicar nodos de etapas.
- Comunicación con backend vía API REST (`/api/flujos`).
- Persistencia del estado del flujo en MySQL.
- Vista de serpentina o lineal configurable.

**`dashboard-motor.jsx`**
- Dashboard interactivo con visualización de métricas en tiempo real.
- Consume datos del endpoint `/api/dashboard/data`.
- Renderiza widgets según la configuración asignada al rol del usuario.
- Soporta: KPI, BarChart, LineChart, PieChart, AreaChart, RadarChart, Table, Timeline.

**`dashboard-builder.jsx`**
- Constructor visual drag & drop de plantillas de dashboard.
- Permite al Administrador diseñar dashboards sin código.
- Motor SQL dinámico: cada widget tiene un query configurable con scope por rol.
- Persistencia de configuración en tablas `dashboard_plantillas` y `dashboard_widgets`.

### 4.2 Backend — Estructura de Carpetas

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── UserController.php          # CRUD usuarios
│   │   │   ├── RoleController.php          # Gestión de roles
│   │   │   ├── ConfiguracionController.php # Parametrización del sistema
│   │   │   └── AuditoriaController.php     # Consulta de logs
│   │   ├── Api/
│   │   │   ├── FlujoApiController.php      # API Motor de Flujos
│   │   │   ├── DashboardApiController.php  # API Motor de Dashboards
│   │   │   ├── ProcesoApiController.php    # API procesos contractuales
│   │   │   └── DocumentoApiController.php  # API gestión documental
│   │   ├── Area/
│   │   │   ├── UnidadController.php        # Lógica unidades solicitantes
│   │   │   ├── JuridicaController.php      # Lógica área jurídica
│   │   │   ├── PlaneacionController.php    # Lógica área planeación
│   │   │   ├── HaciendaController.php      # Lógica área hacienda
│   │   │   └── SecopController.php         # Lógica integración SECOP
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php
│   │   │   └── PasswordResetController.php
│   │   ├── WorkflowController.php          # Control de flujo de etapas
│   │   └── DashboardController.php         # Dashboard general
│   ├── Middleware/
│   │   ├── CheckRole.php                   # Validación de rol por ruta
│   │   ├── CheckPermission.php             # Validación de permiso
│   │   ├── InactivityTimeout.php           # Control de inactividad
│   │   └── AuditRequest.php               # Registro de auditoría
│   └── Requests/
│       ├── CreateProcesoRequest.php        # Validación creación proceso
│       ├── UploadDocumentoRequest.php      # Validación carga documentos
│       └── CreateUserRequest.php          # Validación creación usuario
├── Models/
│   ├── User.php                           # Usuario + HasRoles (Spatie)
│   ├── Proceso.php                        # Proceso contractual
│   ├── ProcesoEtapa.php                   # Etapa de un proceso
│   ├── ProcesoEtapaArchivo.php            # Documento de una etapa
│   ├── Flujo.php                          # Plantilla de flujo
│   ├── FlujoPaso.php                      # Etapa de un flujo
│   ├── FlujoPasoDocumento.php             # Doc. requerido por etapa de flujo
│   ├── DashboardPlantilla.php             # Plantilla de dashboard
│   ├── DashboardWidget.php                # Widget de dashboard
│   ├── ProcesoAuditoria.php               # Log de auditoría
│   ├── Alerta.php                         # Alertas del sistema
│   ├── Secretaria.php                     # Secretaría de despacho
│   └── Unidad.php                         # Unidad dentro de secretaría
├── Services/
│   ├── FlujoService.php                   # Lógica motor de flujos
│   ├── ProcesoService.php                 # Lógica procesos contractuales
│   ├── NotificacionService.php            # Envío de correos y alertas
│   ├── DashboardService.php               # Generación de datos dashboard
│   ├── AuditoriaService.php               # Registro de auditoría
│   └── SecopService.php                   # Integración API SECOP
└── Support/
    ├── Helpers.php                        # Funciones auxiliares globales
    └── Constants.php                     # Constantes del sistema
```

---

## 5. BASE DE DATOS

### 5.1 Motor y Versión

- **Motor:** MySQL 8.0+ (producción) / SQLite 3.x (desarrollo)
- **Codificación:** utf8mb4 (soporte completo Unicode)
- **Collation:** utf8mb4_unicode_ci
- **Motor de tablas:** InnoDB (soporte transacciones y FK)

### 5.2 Migraciones

El sistema cuenta con **61 migraciones** estructuradas cronológicamente desde febrero de 2026. Las migraciones cubren la creación de todas las tablas, modificaciones incrementales y ajustes de columnas.

Comando para ejecutar migraciones:
```bash
php artisan migrate
php artisan migrate --seed  # Con datos iniciales (16 seeders)
```

### 5.3 Tablas Principales

| Grupo | Tablas |
|---|---|
| **Usuarios y Seguridad** | `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `auth_events` |
| **Estructura Organizacional** | `secretarias`, `unidades` |
| **Flujos Contractuales** | `flujos`, `flujo_pasos`, `flujo_paso_documentos` |
| **Procesos** | `procesos`, `proceso_etapas`, `proceso_etapa_checks`, `proceso_etapa_archivos` |
| **Documentos** | `documento_versiones`, `tipos_archivo_por_etapa`, `plan_anual_adquisiciones` |
| **Dashboard Motor** | `dashboard_plantillas`, `dashboard_widgets`, `dashboard_rol_asignaciones`, `dashboard_usuario_asignaciones`, `dashboard_secretaria_asignaciones`, `dashboard_unidad_asignaciones`, `dashboard_asignacion_auditorias` |
| **Alertas y Notificaciones** | `alertas`, `tracking_eventos` |
| **Auditoría** | `proceso_auditorias` |
| **Configuración** | `configuracion_sistema`, `configuracion` |
| **Contratos** | `contract_processes`, `process_steps`, `modificaciones_contractuales` |
| **Sistema Laravel** | `cache`, `jobs`, `failed_jobs`, `sessions`, `personal_access_tokens` |

### 5.4 Descripción de Tablas Clave

#### `users`
```
id, name, email (unique), password, secretaria_id (FK), unidad_id (FK),
estado (activo/inactivo), email_verified_at, remember_token,
created_at, updated_at
```

#### `procesos`
```
id, nombre, numero_proceso (unique), flujo_id (FK), secretaria_id (FK),
unidad_id (FK), contratista_nombre, contratista_documento, valor_contrato,
fecha_inicio, fecha_terminacion, estado (borrador/en_progreso/completado/anulado),
etapa_actual, user_id (creador), area_responsable, campos_validacion,
created_at, updated_at
```

#### `proceso_etapas`
```
id, proceso_id (FK), flujo_paso_id (FK), numero_etapa, nombre, estado,
responsable_id (FK → users), fecha_inicio_etapa, fecha_fin_etapa,
dias_estimados, comentarios, enviado (boolean), created_at, updated_at
```

#### `proceso_etapa_archivos`
```
id, proceso_etapa_id (FK), tipo_archivo, nombre_archivo, ruta_archivo,
estado (pendiente/aprobado/rechazado), subido_por (FK → users),
validado_por (FK → users), comentario_validacion, version,
created_at, updated_at
```

#### `flujos`
```
id, nombre, descripcion, tipo_contratacion, version, estado (borrador/publicado/inactivo),
configuracion_json, created_by (FK → users), created_at, updated_at
```

#### `flujo_pasos`
```
id, flujo_id (FK), numero_paso, nombre, descripcion, rol_responsable,
dias_estimados, es_paralelo (boolean), orden, configuracion_json,
created_at, updated_at
```

#### `proceso_auditorias`
```
id, proceso_id (FK), user_id (FK), etapa_id (FK), accion, modulo,
dato_anterior (JSON), dato_nuevo (JSON), ip_address, user_agent,
created_at
```

#### `dashboard_plantillas`
```
id, nombre, descripcion, configuracion_layout (JSON), es_global (boolean),
created_by (FK → users), created_at, updated_at
```

### 5.5 Relaciones Principales

```
users ──< model_has_roles >── roles ──< role_has_permissions >── permissions
users ──→ secretarias
users ──→ unidades
procesos ──→ flujos
procesos ──→ secretarias
procesos ──→ unidades
procesos ──< proceso_etapas >── flujo_pasos
proceso_etapas ──< proceso_etapa_archivos
flujos ──< flujo_pasos ──< flujo_paso_documentos
dashboard_plantillas ──< dashboard_widgets
dashboard_plantillas ──< dashboard_rol_asignaciones >── roles
```

### 5.6 Integridad Referencial

Todas las relaciones entre tablas están protegidas con llaves foráneas (`FOREIGN KEY`) con las acciones:
- `ON DELETE CASCADE` para registros dependientes (ej. etapas de proceso).
- `ON DELETE RESTRICT` para relaciones críticas (ej. no eliminar un flujo con procesos activos).
- Las transacciones de base de datos (`DB::transaction`) se usan en operaciones críticas multitabla.

---

## 6. MOTOR DE FLUJOS — ASPECTOS TÉCNICOS

### 6.1 Arquitectura del Motor de Flujos

El Motor de Flujos combina un componente React (`motor-flujos.jsx`) para la interfaz visual y un conjunto de endpoints API para la persistencia. La configuración de cada flujo se almacena en MySQL como JSON estructurado.

### 6.2 Estructura de un Flujo en Base de Datos

```
flujos (1)
  └── flujo_pasos (N)
        └── flujo_paso_documentos (N)
```

Cada `flujo_paso` almacena en `configuracion_json`:
```json
{
  "rol_responsable": "juridica",
  "es_paralelo": false,
  "dependencias": [4],
  "documentos_obligatorios": ["ajustado_derecho", "contrato_firmado"],
  "documentos_opcionales": [],
  "dias_estimados": 5,
  "permite_devolucion": true
}
```

### 6.3 Etapas del Flujo CDPN (Implementadas)

| N.° | Nombre | Rol | Paralela | Docs Obligatorios |
|---|---|---|---|---|
| 0 | Definición de la Necesidad | `unidad_solicitante` | No | `estudios_previos` |
| 1 | Solicitud Documentos Iniciales | `planeacion` | Sí | `compatibilidad_gasto`, `cdp`, `paa`, `no_planta`, `paz_salvo_rentas`, `paz_salvo_contabilidad`, `sigep` |
| 2 | Validación del Contratista | `unidad_solicitante` | Sí | 21 documentos del contratista |
| 3 | Elaboración Docs. Contractuales | `unidad_solicitante` | No | 6 documentos contractuales |
| 4 | Consolidación Expediente | `unidad_solicitante` | No | `carpeta_precontractual` (35 docs) |
| 5 | Radicación Jurídica | `juridica` | No | `solicitud_sharepoint`, `numero_proceso`, `lista_chequeo`, `ajustado_derecho`, `contrato_firmado` |
| 6 | Publicación SECOP II | `secop` | No | `firma_contratista`, `firma_secretario`, `contrato_electronico` |
| 7 | Solicitud RPC | `planeacion` | No | `solicitud_rpc`, `rpc_expedido`, `expediente_fisico` |
| 8 | Radicación Final | `juridica` | No | `radicado_final`, `numero_contrato` |
| 9 | ARL, Acta Inicio, SECOP | `unidad_solicitante` | No | `solicitud_arl`, `acta_inicio`, `registro_secop_inicio` |

### 6.4 Transiciones entre Etapas

El `WorkflowController.php` gestiona las transiciones:

1. Verificar que el usuario tiene el rol de la etapa activa.
2. Verificar que todos los documentos obligatorios están en estado `aprobado`.
3. Marcar la etapa actual como `completada`.
4. Activar la siguiente etapa con el responsable correcto.
5. Registrar la transición en `proceso_auditorias`.
6. Disparar notificación al responsable de la nueva etapa.

### 6.5 Responsables por Etapa

El sistema resuelve el responsable de una etapa en el momento de la transición buscando el usuario activo con el rol correspondiente en la secretaría/unidad del proceso.

### 6.6 Estados de Etapa

```
pendiente → en_progreso → completada
     ↑___________________________|
           (devolución posible)
                    ↓
               devuelta
```

### 6.7 Trazabilidad Completa

Cada transición genera un registro en `proceso_auditorias`:
```json
{
  "proceso_id": 42,
  "user_id": 7,
  "etapa_id": 3,
  "accion": "avanzar_etapa",
  "modulo": "workflow",
  "dato_anterior": {"etapa": 2, "estado": "en_progreso"},
  "dato_nuevo": {"etapa": 3, "estado": "en_progreso"},
  "ip_address": "192.168.1.10",
  "created_at": "2026-04-07T10:30:00"
}
```

---

## 7. GESTIÓN DE USUARIOS Y SEGURIDAD

### 7.1 Autenticación

- **Mecanismo:** Laravel Session (cookies seguras, HttpOnly, SameSite).
- **Scaffolding:** Laravel Breeze adaptado con diseño personalizado.
- **Hash de contraseñas:** Bcrypt con factor de costo 12.
- **Tokens API:** Laravel Sanctum para endpoints API consumidos por React.

### 7.2 Autorización RBAC con Spatie

- Los roles se asignan a usuarios mediante `$user->assignRole('rol')`.
- Los permisos se verifican con `$user->can('accion.recurso')` o mediante middleware `permission:accion.recurso`.
- Las rutas web se protegen con `middleware(['auth', 'role:admin|super-admin'])`.
- Las rutas API se protegen con `middleware(['auth:sanctum', 'permission:recurso.accion'])`.

### 7.3 Gestión de Sesiones

- Las sesiones se almacenan en base de datos (tabla `sessions`).
- El tiempo de inactividad es configurable (defecto: 30 minutos).
- El middleware `InactivityTimeout` verifica el tiempo de última actividad en cada request.
- El Administrador puede invalidar sesiones de usuarios mediante el panel de administración.

### 7.4 Política de Contraseñas

- Mínimo 8 caracteres.
- Debe contener: mayúsculas, minúsculas, números y caracteres especiales.
- No puede repetir las últimas 5 contraseñas del usuario.
- Expiración configurable (por defecto: 90 días en producción).

### 7.5 Seguridad de Datos

- Todas las peticiones HTTP deben ser HTTPS en producción.
- Los tokens CSRF se validan en todos los formularios.
- Las entradas de usuario se sanitizan automáticamente contra XSS mediante el Escape de Blade.
- Las consultas ORM de Eloquent previenen SQL Injection mediante prepared statements.
- Los archivos cargados se validan por MIME type y extensión antes de almacenarlos.
- Los archivos cargados se almacenan fuera del directorio público (`storage/app/private/`).

### 7.6 Auditoría de Seguridad

- La tabla `auth_events` registra: login, logout, intentos fallidos, recuperación de contraseña.
- Cada evento incluye: user_id, IP, user_agent, resultado, fecha y hora.
- El middleware `AuditRequest` registra las acciones CRUD sobre entidades críticas.

---

## 8. APIs Y SERVICIOS

### 8.1 Estructura de la API REST

La API sigue convenciones RESTful con los métodos HTTP estándar:

| Método | Acción |
|---|---|
| `GET` | Consulta de recursos |
| `POST` | Creación de recursos |
| `PUT` / `PATCH` | Actualización de recursos |
| `DELETE` | Eliminación de recursos |

La API cuenta con **166 endpoints** definidos en `routes/api.php` y más de **550 rutas web** en `routes/web.php`.

### 8.2 Endpoints Principales

#### Autenticación
| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/login` | Inicio de sesión |
| POST | `/logout` | Cierre de sesión |
| POST | `/forgot-password` | Solicitar recuperación de contraseña |
| POST | `/reset-password` | Restablecer contraseña con token |

#### Procesos Contractuales
| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/procesos` | Listar procesos con filtros |
| POST | `/api/procesos` | Crear proceso |
| GET | `/api/procesos/{id}` | Detalle de proceso |
| PUT | `/api/procesos/{id}` | Actualizar proceso |
| POST | `/api/procesos/{id}/avanzar` | Avanzar etapa |
| POST | `/api/procesos/{id}/devolver` | Devolver etapa |
| POST | `/api/procesos/{id}/anular` | Anular proceso |

#### Gestión Documental
| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/api/procesos/{id}/documentos` | Cargar documento |
| GET | `/api/procesos/{id}/documentos` | Listar documentos |
| POST | `/api/documentos/{id}/validar` | Validar/rechazar documento |
| GET | `/api/documentos/{id}/descargar` | Descargar documento |

#### Motor de Flujos
| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/flujos` | Listar flujos |
| POST | `/api/flujos` | Crear flujo |
| GET | `/api/flujos/{id}` | Detalle flujo con pasos |
| PUT | `/api/flujos/{id}` | Actualizar flujo |
| POST | `/api/flujos/{id}/publicar` | Publicar flujo |
| POST | `/api/flujos/{id}/pasos` | Agregar paso al flujo |

#### Motor de Dashboards
| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/dashboard/data` | Datos del dashboard según rol |
| GET | `/api/dashboard/plantillas` | Listar plantillas |
| POST | `/api/dashboard/plantillas` | Crear plantilla |
| PUT | `/api/dashboard/plantillas/{id}` | Actualizar plantilla |
| POST | `/api/dashboard/asignar` | Asignar plantilla a rol/secretaría/unidad |

#### Administración
| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/admin/users` | Listar usuarios |
| POST | `/api/admin/users` | Crear usuario |
| PUT | `/api/admin/users/{id}` | Actualizar usuario |
| POST | `/api/admin/users/{id}/roles` | Asignar rol |
| GET | `/api/admin/auditoria` | Consultar log de auditoría |

### 8.3 Métodos HTTP y Códigos de Respuesta

| Código | Situación |
|---|---|
| 200 | Petición exitosa |
| 201 | Recurso creado exitosamente |
| 400 | Error de validación (datos inválidos) |
| 401 | No autenticado |
| 403 | Sin permiso para la acción |
| 404 | Recurso no encontrado |
| 422 | Error de validación de negocio |
| 500 | Error interno del servidor |

### 8.4 Formato de Respuesta JSON

```json
{
  "success": true,
  "data": { ... },
  "message": "Proceso creado exitosamente",
  "meta": {
    "current_page": 1,
    "total": 42,
    "per_page": 15
  }
}
```

Error:
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "campo": ["El campo es requerido."]
  }
}
```

### 8.5 Rate Limiting

- Las rutas API tienen límite de 60 peticiones por minuto por usuario autenticado.
- Las rutas de autenticación tienen límite de 5 intentos por minuto por IP.
- Configurado mediante `RateLimiter` en `RouteServiceProvider`.

---

## 9. INTEGRACIONES

### 9.1 Integración SMTP — Office 365

**Configuración en `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=notificaciones@gobernaciondecaldas.gov.co
MAIL_PASSWORD=***
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notificaciones@gobernaciondecaldas.gov.co
MAIL_FROM_NAME="Sistema Contractual Gobernación de Caldas"
```

**Uso:** El `NotificacionService` utiliza la facade `Mail` de Laravel para enviar notificaciones mediante Mailable classes. Los correos se envían de forma asíncrona mediante el sistema de colas (`Queue`) para no bloquear la petición HTTP.

**Templates de correo disponibles:**
- Avance de etapa.
- Documento rechazado.
- Alerta de vencimiento.
- Proceso creado.
- Recuperación de contraseña.

### 9.2 Integración API SECOP II

El `SecopService` encapsula la comunicación con la API de SECOP II:

**Funcionalidades:**
- Publicar contrato en SECOP II con los datos del proceso.
- Consultar estado de publicación.
- Descargar contrato electrónico firmado.
- Registrar inicio de ejecución en SECOP II.

**Configuración:**
```env
SECOP_API_URL=https://api.secop.gov.co/v2
SECOP_API_KEY=***
SECOP_ENTITY_CODE=GOB-CALDAS-001
```

**Manejo de fallos:** La integración con SECOP II es resiliente. Si la API no responde, el sistema registra el error en el log y permite continuar el proceso internamente, marcando la publicación en SECOP como pendiente para reintento posterior.

### 9.3 Integraciones Futuras Planificadas

| Integración | Descripción | Estado |
|---|---|---|
| SIIF Nación | Consulta de disponibilidad presupuestal en tiempo real | Planificado |
| SharePoint | Repositorio de documentos corporativo | Planificado |
| CHIP | Reporte de información presupuestal al CHIP | Planificado |
| SIGEP | Validación automática de hoja de vida del contratista | Planificado |

---

## 10. DESPLIEGUE E INFRAESTRUCTURA

### 10.1 Ambientes

| Ambiente | Propósito | BD |
|---|---|---|
| **Desarrollo** | Desarrollo activo, HMR con Vite | SQLite |
| **Pruebas (QA)** | Pruebas funcionales y automatizadas | MySQL (datos de prueba) |
| **Producción** | Ambiente real de la Gobernación | MySQL 8.0+ |

### 10.2 Servidor de Producción

**Requisitos mínimos de hardware:**
- CPU: 4 núcleos (2.0 GHz+)
- RAM: 8 GB
- Almacenamiento: 100 GB SSD

**Requisitos de software:**
- Ubuntu 22.04 LTS
- Nginx 1.24+ o Apache 2.4+
- PHP 8.2+ con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`
- MySQL 8.0+
- Composer 2.x
- Node.js 20+ y npm

**Configuración Nginx recomendada:**
```nginx
server {
    listen 443 ssl;
    server_name sistema-contractual.caldas.gov.co;
    root /var/www/sistema-contractual/public;
    index index.php;

    ssl_certificate /etc/ssl/certs/caldas.crt;
    ssl_certificate_key /etc/ssl/private/caldas.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}
```

### 10.3 Variables de Entorno (.env)

Variables críticas para producción:

```env
APP_NAME="Sistema Contractual Gobernación de Caldas"
APP_ENV=production
APP_KEY=base64:...  # Generado con php artisan key:generate
APP_DEBUG=false
APP_URL=https://sistema-contractual.caldas.gov.co

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_contractual
DB_USERNAME=app_user
DB_PASSWORD=***

SESSION_DRIVER=database
SESSION_LIFETIME=30
SESSION_ENCRYPT=true

QUEUE_CONNECTION=database

CACHE_STORE=database

LOG_CHANNEL=daily
LOG_LEVEL=error
```

### 10.4 Proceso de Despliegue

```bash
# 1. Clonar repositorio
git clone [repo-url] /var/www/sistema-contractual
cd /var/www/sistema-contractual

# 2. Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# 3. Instalar y compilar frontend
npm install
npm run build

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Ejecutar migraciones y seeders
php artisan migrate --force
php artisan db:seed --force

# 6. Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 7. Configurar permisos
chmod -R 755 /var/www/sistema-contractual
chown -R www-data:www-data storage bootstrap/cache

# 8. Configurar worker de colas
php artisan queue:work --daemon
```

### 10.5 Control de Versiones

- Repositorio Git con ramas: `main` (producción), `develop` (desarrollo), `feature/*` (nuevas funcionalidades).
- Las migraciones de base de datos se versionan junto al código.
- El archivo `.env` **nunca** se sube al repositorio (está en `.gitignore`).

---

## 11. LOGS Y MONITOREO

### 11.1 Logs de Aplicación

Laravel registra los logs en `storage/logs/laravel-YYYY-MM-DD.log` con rotación diaria.

Niveles de log en uso:
- `ERROR`: Errores de aplicación (excepciones no controladas, fallos de integración).
- `WARNING`: Situaciones anómalas que no interrumpen el servicio.
- `INFO`: Eventos importantes del negocio (proceso creado, etapa avanzada).
- `DEBUG`: Solo en ambiente de desarrollo.

### 11.2 Logs de Auditoría

La tabla `proceso_auditorias` sirve como log de auditoría de negocio, accesible desde el panel de administración con filtros por usuario, módulo, acción y fecha.

### 11.3 Logs de Seguridad

La tabla `auth_events` registra todos los eventos de autenticación. Los intentos fallidos y bloqueos de cuenta se registran con la IP de origen.

### 11.4 Monitoreo Recomendado

| Herramienta | Propósito |
|---|---|
| **Laravel Telescope** | Debugging en desarrollo (deshabilitado en producción) |
| **Uptime Robot** | Monitoreo de disponibilidad del servidor |
| **Logwatch** | Análisis periódico de logs del servidor Linux |
| **MySQL Slow Query Log** | Identificación de consultas lentas |

---

## 12. MANTENIMIENTO

### 12.1 Actualizaciones de Dependencias

```bash
# Actualizar dependencias PHP
composer update

# Actualizar dependencias npm
npm update

# Recompilar frontend
npm run build
```

Se recomienda revisar el `CHANGELOG` de cada dependencia antes de actualizar, especialmente Laravel, Spatie Permission y React.

### 12.2 Respaldo de Base de Datos

```bash
# Respaldo diario recomendado (crontab)
0 2 * * * mysqldump -u app_user -p sistema_contractual | gzip > /backups/db-$(date +\%Y\%m\%d).sql.gz
```

Retener respaldos por un mínimo de 30 días en almacenamiento local y 1 año en almacenamiento externo (ej. S3 o NAS).

### 12.3 Limpieza Periódica

```bash
# Limpiar caché de Laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Limpiar sesiones expiradas
php artisan session:gc  # o configurar via scheduler

# Limpiar jobs fallidos
php artisan queue:flush
```

### 12.4 Proceso de Actualización del Sistema

1. Realizar respaldo completo de BD y código.
2. Poner el sistema en modo mantenimiento: `php artisan down`.
3. Desplegar nuevo código con `git pull`.
4. Ejecutar `composer install --no-dev`.
5. Ejecutar migraciones: `php artisan migrate --force`.
6. Recompilar frontend: `npm run build`.
7. Limpiar cachés: `php artisan optimize`.
8. Restaurar servicio: `php artisan up`.
9. Verificar funcionamiento mediante pruebas básicas.

---

## 13. LIMITACIONES TÉCNICAS

| Limitación | Descripción | Mitigación |
|---|---|---|
| **Dependencia de SECOP II** | Si la API de SECOP no está disponible, la publicación de contratos queda pendiente | El sistema continúa operando internamente; reintento automático de publicación |
| **Correo SMTP** | Si Office 365 no está disponible, las notificaciones no se envían | Las alertas quedan en cola para reintento; el proceso no se bloquea |
| **Carga de archivos** | Límite de 10 MB por archivo según configuración actual | Ajustable en `php.ini` y configuración de Nginx (`client_max_body_size`) |
| **Concurrencia** | El sistema está optimizado para 50-100 usuarios concurrentes | Para mayor concurrencia se requiere escalado horizontal (múltiples instancias) |
| **Navegadores** | No soporta Internet Explorer | Se requiere Chrome, Edge o Firefox versión 110+ |
| **React en Blade** | Los componentes React requieren JavaScript habilitado en el navegador | Alerta al usuario si JavaScript está deshabilitado |
| **Tamaño del log de auditoría** | Con uso intensivo, la tabla de auditoría crece rápidamente | Implementar particionado de tabla o archivo a almacenamiento frío después de 2 años |

---

## 14. ANEXOS

### 14.1 Comandos Artisan Útiles

| Comando | Descripción |
|---|---|
| `php artisan migrate:status` | Ver estado de migraciones |
| `php artisan db:seed --class=RolesSeeder` | Ejecutar seeder específico |
| `php artisan route:list` | Listar todas las rutas |
| `php artisan queue:work` | Iniciar worker de colas |
| `php artisan make:controller NombreController` | Crear controlador |
| `php artisan make:model NombreModel -m` | Crear modelo con migración |
| `php artisan tinker` | REPL interactivo de Laravel |

### 14.2 Variables de Configuración Importantes

| Variable | Archivo | Descripción |
|---|---|---|
| `SESSION_LIFETIME` | `.env` | Minutos de inactividad antes de expirar sesión |
| `APP_DEBUG` | `.env` | Mostrar errores detallados (solo `false` en producción) |
| `QUEUE_CONNECTION` | `.env` | Motor de colas (database, redis, sync) |
| `FILESYSTEM_DISK` | `.env` | Disco de almacenamiento de archivos (local, s3) |

### 14.3 Seeders Disponibles (16 total)

| Seeder | Descripción |
|---|---|
| `RolesAndPermissionsSeeder` | Crea los 13 roles y todos los permisos |
| `AdminUserSeeder` | Usuario super-admin inicial |
| `SecretariasSeeder` | Secretarías de la Gobernación de Caldas |
| `UnidadesSeeder` | Unidades dentro de cada secretaría |
| `WorkflowSeeder` | Flujo CDPN con 10 etapas y documentos |
| `DashboardPlantillasSeeder` | Plantillas de dashboard por rol |
| `ConfiguracionSistemaSeeder` | Parámetros iniciales del sistema |
| `UsersSeeder` | Usuarios de prueba por rol |
| *(+ 8 seeders adicionales de datos de prueba)* | — |

---

*Documento elaborado por el Equipo de Tecnología de la Gobernación de Caldas — Versión 1.0 — Abril 2026*
