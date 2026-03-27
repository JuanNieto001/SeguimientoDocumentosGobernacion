# DOCUMENTACION OFICIAL DEL SISTEMA
## Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas

**Version:** 1.0.0
**Fecha de documentacion:** 2026-03-27
**Estado:** Documento oficial listo para auditoria

---

## TABLA DE CONTENIDOS

1. [Descripcion General del Sistema](#1-descripcion-general-del-sistema)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Componentes del Sistema](#3-componentes-del-sistema)
4. [Modelos de Datos](#4-modelos-de-datos)
5. [Flujos Funcionales](#5-flujos-funcionales)
6. [Roles y Perfiles](#6-roles-y-perfiles)
7. [Endpoints y Servicios](#7-endpoints-y-servicios)
8. [Reglas de Negocio](#8-reglas-de-negocio)
9. [Validaciones](#9-validaciones)
10. [Integraciones](#10-integraciones)
11. [Dependencias Tecnicas](#11-dependencias-tecnicas)

---

# 1. DESCRIPCION GENERAL DEL SISTEMA

## 1.1 Proposito

El **Sistema de Seguimiento de Documentos Contractuales** es una plataforma web integral desarrollada para la Gobernacion de Caldas que permite gestionar de manera eficiente todos los procesos de contratacion publica. El sistema digitaliza y automatiza los flujos de trabajo desde la definicion de la necesidad hasta la ejecucion del contrato.

## 1.2 Alcance

El sistema cubre las siguientes modalidades de contratacion:
- **CD-PN:** Contratacion Directa - Persona Natural
- **CD-PJ:** Contratacion Directa - Persona Juridiica
- **LP:** Licitacion Publica
- **SA:** Seleccion Abreviada
- **CM:** Concurso de Meritos
- **MC:** Minima Cuantia

## 1.3 Usuarios Principales

| Nivel | Roles | Descripcion |
|-------|-------|-------------|
| Ejecutivo | Gobernador, Secretario | Vision estrategica y reportes consolidados |
| Administrativo | Admin General, Admin Secretaria, Admin Unidad | Configuracion y gestion del sistema |
| Operativo | Unidad Solicitante, Planeacion, Hacienda, Juridica, SECOP | Ejecucion de procesos contractuales |
| Consulta | Consulta Ciudadana, Auditor Interno | Acceso de solo lectura |

## 1.4 Objetivos del Sistema

1. Digitalizar el 100% de los procesos contractuales
2. Reducir tiempos de tramite mediante automatizacion
3. Garantizar trazabilidad completa de cada proceso
4. Cumplir normativa colombiana de contratacion publica
5. Facilitar auditorias internas y externas
6. Integrar con SECOP II automaticamente

---

# 2. ARQUITECTURA DEL SISTEMA

## 2.1 Stack Tecnologico

| Capa | Tecnologia | Version |
|------|------------|---------|
| Backend | Laravel (PHP) | 11.x |
| Frontend | React + Blade | 18.x |
| CSS | Tailwind CSS | 3.x |
| Base de Datos | MySQL/PostgreSQL | 8.x |
| Autenticacion | Laravel Breeze + Spatie Permission | - |
| Build Tool | Vite | 5.x |
| Real-time | Laravel Echo + Pusher | - |

## 2.2 Arquitectura de Capas

```
┌─────────────────────────────────────────────────────────────────┐
│                        CAPA DE PRESENTACION                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │  Blade Views    │  │  React Components│  │  API JSON       │  │
│  │  (Dashboard)    │  │  (Motor Flujos)  │  │  (Integraciones)│  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
├─────────────────────────────────────────────────────────────────┤
│                        CAPA DE CONTROLADORES                     │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐             │
│  │ Web          │ │ API          │ │ Auth         │             │
│  │ Controllers  │ │ Controllers  │ │ Controllers  │             │
│  └──────────────┘ └──────────────┘ └──────────────┘             │
├─────────────────────────────────────────────────────────────────┤
│                        CAPA DE SERVICIOS                         │
│  ┌──────────────────┐  ┌──────────────────┐  ┌────────────────┐ │
│  │ WorkflowEngine   │  │ AlertaService    │  │ SECOPService   │ │
│  │ StateMachine     │  │ NotificacionServ │  │ ValidacionServ │ │
│  └──────────────────┘  └──────────────────┘  └────────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│                        CAPA DE DATOS                             │
│  ┌──────────────────┐  ┌──────────────────┐  ┌────────────────┐ │
│  │ Eloquent Models  │  │ Migrations       │  │ Seeders        │ │
│  │ Policies         │  │ Enums            │  │ Factories      │ │
│  └──────────────────┘  └──────────────────┘  └────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

## 2.3 Patron de Diseno

El sistema implementa una **arquitectura en capas** con los siguientes patrones:

1. **MVC (Model-View-Controller):** Estructura base de Laravel
2. **Service Layer:** Logica de negocio encapsulada en servicios
3. **State Machine:** Gestion de estados de procesos contractuales
4. **Repository Pattern:** Acceso a datos mediante Eloquent ORM
5. **Policy Pattern:** Autorizacion granular por recurso
6. **Observer Pattern:** Eventos y listeners para acciones asincronas

## 2.4 Estructura de Directorios

```
App/
├── Console/Commands/       # Comandos Artisan personalizados
├── Enums/                  # Enumeraciones (estados, tipos)
├── Events/                 # Eventos del sistema
├── Http/
│   ├── Controllers/        # Controladores web y API
│   ├── Middleware/         # Middleware personalizado
│   └── Requests/           # Form Requests con validacion
├── Listeners/              # Manejadores de eventos
├── Models/                 # Modelos Eloquent (40+ modelos)
├── Policies/               # Politicas de autorizacion
├── Providers/              # Service Providers
├── Services/               # Servicios de logica de negocio
└── Support/                # Clases de soporte

resources/
├── js/
│   ├── dashboard-v2/       # Motor de Dashboards React
│   ├── WorkflowApp.jsx     # Motor de Flujos React
│   └── dashboard-motor.jsx # Dashboard Motor V1
└── views/
    ├── layouts/            # Layouts Blade
    ├── components/         # Componentes reutilizables
    ├── procesos/           # Vistas de procesos
    └── admin/              # Panel de administracion
```

---

# 3. COMPONENTES DEL SISTEMA

## 3.1 Modulos Principales

### 3.1.1 Modulo de Autenticacion
- Login/Logout con Laravel Breeze
- Gestion de sesiones con regeneracion de tokens
- Registro de eventos de autenticacion (login, logout, failed, password_reset)
- Verificacion de usuario activo

### 3.1.2 Modulo de Procesos Contractuales
- Creacion de solicitudes por modalidad
- Flujo de trabajo configurable por secretaria
- Seguimiento de estados y transiciones
- Gestion documental por etapa
- Auditoria completa de acciones

### 3.1.3 Modulo de Workflows Configurables
- Motor visual drag-and-drop para crear flujos
- Versionado de flujos
- Condiciones dinamicas por monto, modalidad
- Responsables por paso/etapa
- Documentos requeridos por paso

### 3.1.4 Modulo de Dashboards BI
- Widgets personalizables (KPI, Chart, Table, Timeline, Heatmap)
- Asignacion jerarquica: Usuario > Unidad > Secretaria > Rol
- Temas visuales configurables
- Actualizaciones en tiempo real via WebSockets
- Exportacion a CSV/Excel

### 3.1.5 Modulo de Alertas
- Alertas automaticas por:
  - Tiempos excedidos en etapa
  - Documentos proximos a vencer
  - Procesos sin actividad
  - Documentos pendientes de aprobacion
- Prioridades: Baja, Media, Alta, Critica
- Notificaciones por rol y area

### 3.1.6 Modulo de Reportes
- Reportes de estado general
- Reportes por dependencia
- Reportes de actividad por actor
- Reportes de auditoria
- Certificados por vencer
- Indicadores de eficiencia

### 3.1.7 Modulo PAA (Plan Anual de Adquisiciones)
- Registro de necesidades anuales
- Validacion de inclusion en PAA
- Certificados de PAA
- Estados: Vigente, Modificado, Ejecutado, Cancelado

### 3.1.8 Modulo SECOP II
- Consulta de contratos via API Socrata
- Publicacion automatica
- Sincronizacion de estados
- Cache de consultas

## 3.2 Componentes Frontend

### 3.2.1 Motor de Flujos (WorkflowApp.jsx)
- React Flow para visualizacion de grafos
- Drag-and-drop de nodos
- Catalogo de pasos reutilizables
- Guardado completo de flujos

### 3.2.2 Motor de Dashboards (DashboardMotorV2.jsx)
- React Grid Layout para widgets
- Hooks personalizados:
  - `useDashboardData`: Carga y persistencia
  - `useRealtimeUpdates`: WebSockets
  - `useResponsiveLayout`: Breakpoints adaptativos

### 3.2.3 Asistente Marsetiv Bot
- Guias contextuales por rol
- Interfaz de chat flotante
- Pasos numerados por funcionalidad

---

# 4. MODELOS DE DATOS

## 4.1 Diagrama Entidad-Relacion Simplificado

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   USUARIOS   │────<│   PROCESOS   │────<│   ETAPAS     │
│              │     │              │     │              │
│ - name       │     │ - codigo     │     │ - nombre     │
│ - email      │     │ - objeto     │     │ - orden      │
│ - secretaria │     │ - estado     │     │ - dias_est.  │
│ - unidad     │     │ - valor_est. │     └──────────────┘
│ - roles      │     │ - workflow   │            │
└──────────────┘     └──────────────┘            │
       │                    │               ┌────┴────┐
       │                    │               │ ITEMS   │
       │             ┌──────┴──────┐        │ CHECK   │
       │             │ ARCHIVOS    │        └─────────┘
       └────────────>│             │
                     │ - tipo      │
                     │ - ruta      │
                     │ - estado    │
                     └─────────────┘
```

## 4.2 Tablas Principales

### 4.2.1 Nucleo Organizacional

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `users` | Usuarios del sistema | id, name, email, secretaria_id, unidad_id, activo |
| `secretarias` | Secretarias de la gobernacion | id, nombre, activo |
| `unidades` | Unidades por secretaria | id, nombre, secretaria_id, activo |
| `roles` | Roles (Spatie Permission) | id, name, guard_name |
| `permissions` | Permisos (Spatie Permission) | id, name, guard_name |

### 4.2.2 Sistema de Workflows

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `workflows` | Definicion de workflows | id, codigo, nombre, activo |
| `etapas` | Etapas de cada workflow | id, workflow_id, orden, nombre, dias_estimados |
| `etapa_items` | Items checklist por etapa | id, etapa_id, label, requerido |
| `tipos_archivo_por_etapa` | Documentos permitidos | id, etapa_id, tipo, requerido |

### 4.2.3 Procesos Contractuales

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `procesos` | Procesos en workflows legacy | id, codigo, objeto, estado, etapa_actual_id |
| `proceso_etapas` | Instancias de etapa por proceso | id, proceso_id, etapa_id, enviado, recibido |
| `proceso_etapa_archivos` | Documentos subidos | id, proceso_id, tipo_archivo, ruta, estado |
| `proceso_etapa_checks` | Checks completados | id, proceso_etapa_id, etapa_item_id, checked |
| `proceso_auditoria` | Log de acciones | id, proceso_id, user_id, accion, fecha |

### 4.2.4 Contratacion Directa PN

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `proceso_contratacion_directa` | Procesos CD-PN | id, codigo, estado, etapa_actual, objeto, valor |
| `contract_processes` | Sistema de 10 etapas | id, process_type, status, current_step |
| `process_steps` | Pasos por proceso | id, process_id, step_number, status |
| `process_documents` | Documentos del proceso | id, process_id, document_type, file_path |
| `process_approvals` | Aprobaciones | id, process_id, approval_type, status |

### 4.2.5 Motor de Flujos Configurable

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `catalogo_pasos` | Catalogo maestro de pasos | id, codigo, nombre, tipo, icono |
| `flujos` | Flujos por secretaria | id, codigo, nombre, tipo_contratacion |
| `flujo_versiones` | Versionado de flujos | id, flujo_id, numero_version, estado |
| `flujo_pasos` | Pasos de cada version | id, flujo_version_id, catalogo_paso_id, orden |
| `flujo_paso_condiciones` | Condiciones dinamicas | id, flujo_paso_id, campo, operador, valor |
| `flujo_instancias` | Instancias en ejecucion | id, flujo_id, codigo_proceso, estado |
| `flujo_instancia_pasos` | Estado de cada paso | id, instancia_id, flujo_paso_id, estado |

### 4.2.6 Dashboards

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `dashboard_plantillas` | Plantillas de dashboard | id, nombre, slug, config_json |
| `dashboard_widgets` | Widgets por plantilla | id, dashboard_plantilla_id, tipo, metrica |
| `dashboard_rol_asignaciones` | Asignacion por rol | id, role_name, dashboard_plantilla_id |
| `dashboard_usuario_asignaciones` | Asignacion por usuario | id, user_id, dashboard_plantilla_id |
| `dashboard_secretaria_asignaciones` | Asignacion por secretaria | id, secretaria_id, dashboard_plantilla_id |
| `dashboard_unidad_asignaciones` | Asignacion por unidad | id, unidad_id, dashboard_plantilla_id |

### 4.2.7 Alertas y Auditoria

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `alertas` | Alertas del sistema | id, proceso_id, user_id, tipo, prioridad, leida |
| `auth_events` | Eventos de autenticacion | id, user_id, event_type, ip_address |
| `tracking_eventos` | Tracking de documentos | id, codigo_proceso, tipo, area_origen, area_destino |

### 4.2.8 Supervision y Pagos

| Tabla | Descripcion | Campos Clave |
|-------|-------------|--------------|
| `informes_supervision` | Informes de avance | id, proceso_id, supervisor_id, porcentaje_avance |
| `pagos_contrato` | Pagos programados | id, proceso_id, numero_pago, valor, estado |
| `modificaciones_contractuales` | Adiciones/Prorrogas | id, proceso_id, tipo_modificacion, valor |

## 4.3 Relaciones Principales

```php
// Usuario -> Secretaria -> Unidades
User belongsTo Secretaria
User belongsTo Unidad
Secretaria hasMany Unidades
Secretaria hasMany Users

// Workflow -> Etapas -> Items
Workflow hasMany Etapas (ordenadas)
Etapa hasMany EtapaItems
Etapa hasMany TipoArchivoPorEtapa

// Proceso -> Instancias de Etapa
Proceso belongsTo Workflow
Proceso hasMany ProcesoEtapas
ProcesoEtapa hasMany ProcesoEtapaChecks
ProcesoEtapa hasMany ProcesoEtapaArchivos

// Flujos Configurables
Flujo hasMany FlujoVersiones
FlujoVersion hasMany FlujoPasos
FlujoPaso hasMany FlujoPasoCondiciones
FlujoPaso hasMany FlujoPasoDocumentos
FlujoInstancia hasMany FlujoInstanciaPasos
```

---

# 5. FLUJOS FUNCIONALES

## 5.1 Flujo de Autenticacion

```
┌─────────┐    ┌─────────┐    ┌──────────────┐    ┌────────────┐
│ Usuario │───>│ /login  │───>│ Validar      │───>│ Verificar  │
│         │    │         │    │ Credenciales │    │ Activo=true│
└─────────┘    └─────────┘    └──────────────┘    └────────────┘
                                                        │
                   ┌────────────────────────────────────┘
                   │
              ┌────▼────┐    ┌──────────────┐    ┌────────────┐
              │ Regenerar│───>│ Registrar    │───>│ Redirigir  │
              │ Sesion   │    │ AuthEvent    │    │ /dashboard │
              └──────────┘    └──────────────┘    └────────────┘
```

**Pasos detallados:**
1. Usuario accede a `/login`
2. Ingresa email y contrasena
3. `LoginRequest->authenticate()` valida credenciales
4. Sistema verifica `activo = true`
5. Regenera token de sesion (previene session fixation)
6. Registra evento `login_success` en `auth_events`
7. Redirige segun rol:
   - Rol `planeacion` sin otros roles: `/planeacion`
   - Otros roles: `/dashboard`

---

## 5.2 Flujo de Creacion de Proceso (Unidad Solicitante)

```
┌─────────────┐    ┌───────────────┐    ┌──────────────────┐
│ Unidad      │───>│ Nueva         │───>│ Seleccionar      │
│ Solicitante │    │ Solicitud     │    │ Flujo/Modalidad  │
└─────────────┘    └───────────────┘    └──────────────────┘
                                               │
     ┌─────────────────────────────────────────┘
     │
┌────▼────────────┐    ┌───────────────┐    ┌──────────────┐
│ Completar       │───>│ Subir Estudio │───>│ Validar      │
│ Datos Basicos   │    │ Previo (PDF)  │    │ Formulario   │
└─────────────────┘    └───────────────┘    └──────────────┘
                                                   │
     ┌─────────────────────────────────────────────┘
     │
┌────▼────────────┐    ┌───────────────┐    ┌──────────────┐
│ Generar Codigo  │───>│ Crear         │───>│ Notificar    │
│ Automatico      │    │ Primera Etapa │    │ Planeacion   │
└─────────────────┘    └───────────────┘    └──────────────┘
```

**Campos requeridos:**
- Objeto del contrato (texto)
- Descripcion (texto largo)
- Valor estimado (numerico)
- Plazo de ejecucion (dias/meses)
- Estudio previo (archivo PDF obligatorio)
- Secretaria y Unidad (auto-detectados si usuario tiene asignacion)

**Validaciones:**
- Estudio previo es obligatorio
- Valor > 0
- Plazo > 0
- Usuario debe tener permiso `procesos.crear`

---

## 5.3 Flujo Contratacion Directa Persona Natural (CD-PN)

### Diagrama de Estados

```
┌──────────────────────────────────────────────────────────────────────────┐
│                              ETAPA 1: ESTUDIOS PREVIOS                   │
│  ┌────────────┐    ┌──────────────────────┐    ┌─────────────────────┐  │
│  │ BORRADOR   │───>│ ESTUDIO_PREVIO_      │───>│ EN_VALIDACION_      │  │
│  │            │    │ CARGADO              │    │ PLANEACION          │  │
│  └────────────┘    └──────────────────────┘    └─────────────────────┘  │
└──────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                         ETAPA 2: VALIDACIONES                            │
│  ┌──────────────────┐    ┌─────────────┐    ┌─────────────────────────┐ │
│  │ COMPATIBILIDAD_  │───>│ CDP_        │───>│ CDP_APROBADO            │ │
│  │ APROBADA         │    │ SOLICITADO  │    │                         │ │
│  └──────────────────┘    └─────────────┘    └─────────────────────────┘ │
│                                                                          │
│  Validaciones paralelas: PAA, No Planta, Paz y Salvos                   │
└──────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                      ETAPA 3: DOCUMENTACION CONTRATISTA                  │
│  ┌──────────────────────┐    ┌──────────────────────────────────────┐   │
│  │ DOCUMENTACION_       │───>│ DOCUMENTACION_VALIDADA               │   │
│  │ INCOMPLETA          │    │ (Checklist completo)                 │   │
│  └──────────────────────┘    └──────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                        ETAPA 4: REVISION JURIDICA                        │
│  ┌───────────────────┐    ┌──────────────────────┐                      │
│  │ EN_REVISION_      │───>│ PROCESO_NUMERO_      │                      │
│  │ JURIDICA          │    │ GENERADO             │                      │
│  └───────────────────┘    └──────────────────────┘                      │
└──────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                       ETAPA 5: GENERACION CONTRATO                       │
│  ┌─────────────────┐    ┌───────────────────┐    ┌───────────────────┐  │
│  │ GENERACION_     │───>│ CONTRATO_         │───>│ CONTRATO_FIRMADO_ │  │
│  │ CONTRATO        │    │ GENERADO          │    │ PARCIAL           │  │
│  └─────────────────┘    └───────────────────┘    └───────────────────┘  │
│                                                           │              │
│                                                           ▼              │
│                                               ┌───────────────────────┐ │
│                                               │ CONTRATO_FIRMADO_     │ │
│                                               │ TOTAL                 │ │
│                                               └───────────────────────┘ │
└──────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                             ETAPA 6: RPC                                 │
│  ┌─────────────────┐    ┌───────────────────┐    ┌───────────────────┐  │
│  │ RPC_SOLICITADO  │───>│ RPC_FIRMADO       │───>│ EXPEDIENTE_       │  │
│  │                 │    │                   │    │ RADICADO          │  │
│  └─────────────────┘    └───────────────────┘    └───────────────────┘  │
└──────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                           ETAPA 7: EJECUCION                             │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │                         EN_EJECUCION                              │  │
│  │  - ARL solicitada                                                 │  │
│  │  - Acta de inicio firmada                                         │  │
│  │  - Fecha inicio ejecucion registrada                              │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────────┘
```

### Estados Especiales

| Estado | Descripcion | Transiciones |
|--------|-------------|--------------|
| `CANCELADO` | Proceso cancelado (solo admin) | Estado final |
| `SUSPENDIDO` | Proceso suspendido temporalmente | Puede reactivarse |
| `CONTRATO_DEVUELTO` | Devuelto desde juridica | Vuelve a documentacion |
| `CDP_BLOQUEADO` | CDP rechazado | Requiere correccion |

---

## 5.4 Flujo de 10 Etapas (ContractProcess)

| Etapa | Nombre | Requisitos | Roles |
|-------|--------|------------|-------|
| 0 | Definicion de Necesidad | Estudios previos, valor, plazo | Jefe Unidad, Apoyo |
| 1 | Solicitud Documentos Iniciales | PAA, No Planta, Paz y Salvos, CDP | Jefe Unidad, Presupuesto |
| 2 | Validacion Contratista | Documentos identidad, checklists | Abogado Unidad |
| 3 | Elaboracion Documentos Contractuales | Invitacion, solicitud, designaciones | Abogado Unidad |
| 4 | Consolidacion Expediente | Agrupacion, validacion vigencias | Jefe Unidad |
| 5 | Radicacion en Juridica | SharePoint, Ajustado a Derecho | Abogado Enlace |
| 6 | SECOP II | Publicacion, firmas, contrato electronico | Abogado Enlace |
| 7 | Solicitud RPC | Impresion, radicacion Hacienda | Jefe Unidad, Presupuesto |
| 8 | Radicacion Final | Expediente fisico, numero contrato | Abogado Enlace |
| 9 | Afiliaciones y Acta Inicio | ARL, Acta, registro SECOP | Supervisor |

---

## 5.5 Flujo de Gestion de Bandeja

```
┌─────────────┐    ┌───────────────┐    ┌──────────────────┐
│ Usuario     │───>│ Mi Bandeja    │───>│ Ver Procesos     │
│ (Area X)    │    │               │    │ Pendientes       │
└─────────────┘    └───────────────┘    └──────────────────┘
                                               │
            ┌──────────────────────────────────┴──────────────┐
            │                                                  │
      ┌─────▼─────┐                                     ┌──────▼──────┐
      │ RECIBIR   │                                     │ VER DETALLE │
      │ Proceso   │                                     │ Documentos  │
      └───────────┘                                     └─────────────┘
            │
      ┌─────▼─────────────────────────┐
      │ Completar Items del Checklist │
      └───────────────────────────────┘
            │
      ┌─────▼─────────────────────────┐
      │ Subir Documentos Requeridos   │
      └───────────────────────────────┘
            │
      ┌─────┴─────────────────────────────────────────┐
      │                      │                         │
┌─────▼─────┐         ┌──────▼──────┐          ┌──────▼──────┐
│ APROBAR   │         │ RECHAZAR/   │          │ DEVOLVER    │
│ y Enviar  │         │ Observar    │          │ a Etapa     │
└───────────┘         └─────────────┘          └─────────────┘
```

**Acciones disponibles:**
- **Recibir:** Marcar proceso como recibido en el area
- **Completar checks:** Marcar items del checklist como completados
- **Subir documentos:** Cargar archivos requeridos para la etapa
- **Aprobar y enviar:** Avanzar proceso a siguiente etapa
- **Rechazar:** Devolver con observaciones
- **Devolver:** Regresar a etapa anterior especifica

---

## 5.6 Flujo de Configuracion de Dashboard

```
┌─────────────┐    ┌───────────────────┐    ┌────────────────────┐
│ Admin       │───>│ Motor de          │───>│ Seleccionar        │
│             │    │ Dashboards        │    │ Plantilla          │
└─────────────┘    └───────────────────┘    └────────────────────┘
                                                    │
     ┌──────────────────────────────────────────────┘
     │
┌────▼────────────────────────────────────────────────────────────┐
│                    TIPOS DE ASIGNACION                          │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ Por Usuario  │  │ Por Unidad   │  │ Por Secretaria│          │
│  │ (Prioridad 1)│  │ (Prioridad 2)│  │ (Prioridad 3) │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                  │
│                    ┌──────────────┐                              │
│                    │ Por Rol      │                              │
│                    │ (Prioridad 4)│                              │
│                    └──────────────┘                              │
└──────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│             RESOLUCION DE DASHBOARD PARA USUARIO                 │
│                                                                  │
│  1. Buscar asignacion por user_id                               │
│  2. Si no existe, buscar por unidad_id del usuario              │
│  3. Si no existe, buscar por secretaria_id del usuario          │
│  4. Si no existe, buscar por rol del usuario                    │
│  5. Si no existe, mostrar dashboard por defecto                 │
└──────────────────────────────────────────────────────────────────┘
```

---

# 6. ROLES Y PERFILES

## 6.1 Jerarquia de Roles

```
                    ┌─────────────────┐
                    │   SUPER_ADMIN   │
                    │   (God Mode)    │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
      ┌───────▼───────┐ ┌────▼────┐ ┌───────▼───────┐
      │ ADMIN_GENERAL │ │  ADMIN  │ │ ADMIN_SISTEMA │
      │ (Todo acceso) │ │         │ │               │
      └───────┬───────┘ └────┬────┘ └───────────────┘
              │              │
              ▼              ▼
      ┌───────────────────────────────┐
      │      ADMIN_SECRETARIA         │
      │   (Gestiona su secretaria)    │
      └───────────────┬───────────────┘
                      │
                      ▼
      ┌───────────────────────────────┐
      │        ADMIN_UNIDAD           │
      │    (Configura flujos)         │
      └───────────────────────────────┘
```

## 6.2 Roles Ejecutivos

| Rol | Descripcion | Permisos Clave |
|-----|-------------|----------------|
| `gobernador` | Despacho del Gobernador | Dashboard ejecutivo, reportes consolidados, SECOP |
| `secretario` | Secretario de Despacho | Seguimiento de dependencia, aprobaciones |
| `jefe_unidad` | Jefe de Unidad | Supervision de equipo, gestion operativa |

## 6.3 Roles Operativos

| Rol | Area | Permisos Clave |
|-----|------|----------------|
| `unidad_solicitante` | Unidad Solicitante | Crear procesos, subir documentos, enviar |
| `planeacion` | Planeacion | Verificar PAA, recibir procesos, RPC |
| `hacienda` | Hacienda | CDP, RPC, paz y salvos |
| `juridica` | Juridica | Revision legal, aprobar/rechazar, polizas |
| `secop` | SECOP | Publicacion, PAA, certificados |
| `talento_humano` | Talento Humano | Certificado No Planta |
| `presupuesto` | Presupuesto | CDP |
| `contabilidad` | Contabilidad | Paz y Salvo |
| `rentas` | Rentas | Paz y Salvo |

## 6.4 Roles Especializados

| Rol | Descripcion | Permisos |
|-----|-------------|----------|
| `profesional_contratacion` | Ejecutor de procesos | Crear, editar, enviar |
| `revisor_juridico` | Revision legal | Aprobar, rechazar, auditoria |
| `coord_contratacion` | Coordinador general | Vista consolidada de area |
| `auditor_interno` | Auditoria | Solo lectura, reportes auditoria |
| `consulta` | Consulta general | Solo lectura |
| `consulta_ciudadana` | Acceso publico | Informacion publica |

## 6.5 Matriz de Permisos por Rol

### Permisos de Procesos

| Permiso | admin | unidad_sol | planeacion | hacienda | juridica | secop | gobernador |
|---------|-------|------------|------------|----------|----------|-------|------------|
| procesos.ver | SI | SI | SI | SI | SI | SI | SI |
| procesos.crear | SI | SI | - | - | - | - | - |
| procesos.editar | SI | SI | - | - | - | - | - |
| procesos.recibir | SI | - | SI | SI | SI | SI | - |
| procesos.enviar | SI | SI | SI | - | SI | SI | - |
| procesos.aprobar | SI | - | - | - | SI | - | - |
| procesos.rechazar | SI | - | - | - | SI | - | - |

### Permisos de Archivos

| Permiso | admin | unidad_sol | planeacion | hacienda | juridica | secop |
|---------|-------|------------|------------|----------|----------|-------|
| archivos.subir | SI | SI | SI | SI | SI | SI |
| archivos.descargar | SI | SI | SI | SI | SI | SI |
| archivos.aprobar | SI | - | - | - | SI | - |
| archivos.rechazar | SI | - | - | - | SI | - |
| archivos.reemplazar | SI | SI | - | - | - | - |

### Permisos de PAA

| Permiso | admin | unidad_sol | planeacion | secop |
|---------|-------|------------|------------|-------|
| paa.ver | SI | SI | SI | SI |
| paa.crear | SI | - | - | SI |
| paa.editar | SI | - | - | SI |
| paa.verificar | SI | - | SI | - |
| paa.certificado | SI | - | - | SI |

### Permisos de Dashboard

| Permiso | admin | admin_general | gobernador | secretario | jefe_unidad |
|---------|-------|---------------|------------|------------|-------------|
| dashboard.ver | SI | SI | SI | SI | SI |
| dashboard.admin | SI | SI | - | - | - |
| dashboard.motor.ver | SI | SI | - | - | - |
| dashboard.motor.gestionar | SI | SI | - | - | - |
| dashboard.rol.ver | SI | SI | SI | SI | SI |

---

# 7. ENDPOINTS Y SERVICIOS

## 7.1 Rutas Web Principales

### Autenticacion

| Metodo | URL | Controlador | Middleware | Descripcion |
|--------|-----|-------------|------------|-------------|
| GET | `/login` | AuthenticatedSessionController@create | guest | Formulario login |
| POST | `/login` | AuthenticatedSessionController@store | guest | Procesar login |
| POST | `/logout` | AuthenticatedSessionController@destroy | auth | Cerrar sesion |

### Dashboard

| Metodo | URL | Controlador | Middleware | Descripcion |
|--------|-----|-------------|------------|-------------|
| GET | `/dashboard` | DashboardController@index | auth | Panel principal |
| GET | `/mi-dashboard` | RoleDashboardController@index | auth | Dashboard por rol |
| GET | `/dashboard/area` | DashboardController@estadisticasPorArea | auth | Estadisticas area |
| GET | `/dashboard/buscar` | DashboardController@buscar | auth | Busqueda global |

### Procesos

| Metodo | URL | Controlador | Middleware | Descripcion |
|--------|-----|-------------|------------|-------------|
| GET | `/procesos` | ProcesoController@index | auth | Lista procesos |
| GET | `/procesos/crear` | ProcesoController@create | auth, role:admin\|unidad_solicitante | Formulario creacion |
| POST | `/procesos` | ProcesoController@store | auth | Guardar proceso |
| GET | `/procesos/{id}` | ProcesoController@show | auth | Ver detalle |
| POST | `/procesos/{id}/recibir` | ProcesoController@recibir | auth | Recibir proceso |
| POST | `/procesos/{id}/enviar` | ProcesoController@enviar | auth | Enviar a siguiente etapa |
| POST | `/procesos/{id}/devolver` | ProcesoController@devolver | auth | Devolver proceso |

### Contratacion Directa

| Metodo | URL | Controlador | Middleware | Descripcion |
|--------|-----|-------------|------------|-------------|
| GET | `/proceso-cd` | ProcesoCDController@index | auth | Lista CD-PN |
| GET | `/proceso-cd/crear` | ProcesoCDController@create | auth, role:admin\|unidad_solicitante | Crear CD-PN |
| POST | `/proceso-cd` | ProcesoCDController@store | auth | Guardar CD-PN |
| GET | `/proceso-cd/{id}` | ProcesoCDController@show | auth | Ver CD-PN |
| POST | `/proceso-cd/{id}/transicionar` | ProcesoCDController@transicionar | auth, validar.rol.proceso.cd | Cambiar estado |

### Motor de Flujos

| Metodo | URL | Controlador | Middleware | Descripcion |
|--------|-----|-------------|------------|-------------|
| GET | `/motor-flujos` | View | auth, role:admin\|admin_general\|admin_unidad | Vista motor |

### Motor de Dashboards

| Metodo | URL | Controlador | Middleware | Descripcion |
|--------|-----|-------------|------------|-------------|
| GET | `/dashboards/motor` | DashboardMotorController@index | auth, role:admin, permission:dashboard.motor.ver | Vista motor |
| POST | `/dashboards/motor/asignaciones` | DashboardMotorController@guardarAsignaciones | auth, permission:dashboard.motor.gestionar | Guardar |

### Administracion

| Metodo | URL | Controlador | Middleware | Descripcion |
|--------|-----|-------------|------------|-------------|
| GET | `/admin/usuarios` | UsuarioController@index | auth, role:admin | Lista usuarios |
| GET | `/admin/roles` | RolController@index | auth, role:admin | Lista roles |
| GET | `/admin/secretarias` | SecretariaController@index | auth, role:admin | Lista secretarias |
| GET | `/admin/logs` | AuthEventController@index | auth, role:admin | Logs auth |

## 7.2 API Endpoints

### Autenticacion API

| Metodo | URL | Controlador | Descripcion |
|--------|-----|-------------|-------------|
| POST | `/api/auth/login` | Api\AuthController@login | Login API |
| POST | `/api/auth/logout` | Api\AuthController@logout | Logout API |
| GET | `/api/auth/me` | Api\AuthController@me | Usuario actual |
| POST | `/api/auth/validar-permiso` | Api\AuthController@validarPermiso | Validar permiso |

### Motor de Flujos API

| Metodo | URL | Controlador | Descripcion |
|--------|-----|-------------|-------------|
| GET | `/api/motor-flujos/catalogo-pasos` | MotorFlujosController@catalogoPasos | Catalogo pasos |
| GET | `/api/motor-flujos/secretarias/{id}/flujos` | MotorFlujosController@flujosPorSecretaria | Flujos secretaria |
| GET | `/api/motor-flujos/flujos/{id}/pasos` | MotorFlujosController@obtenerPasos | Pasos de flujo |
| POST | `/api/motor-flujos/flujos/guardar-completo` | MotorFlujosController@guardarFlujoCompleto | Guardar flujo |
| DELETE | `/api/motor-flujos/flujos/{id}` | MotorFlujosController@eliminarFlujo | Eliminar flujo |

### Secretarias y Unidades API

| Metodo | URL | Controlador | Descripcion |
|--------|-----|-------------|-------------|
| GET | `/api/secretarias` | Api\SecretariaController@index | Lista secretarias |
| GET | `/api/secretarias/{id}/unidades` | Api\SecretariaController@unidades | Unidades de secretaria |

### Dashboard API

| Metodo | URL | Controlador | Descripcion |
|--------|-----|-------------|-------------|
| GET | `/api/dashboard/widget-data` | DashboardApiController@widgetData | Datos widget |
| PUT | `/api/dashboard/widget/{id}` | DashboardApiController@updateWidget | Actualizar widget |
| GET | `/api/dashboard/load` | DashboardApiController@loadDashboard | Cargar dashboard |
| POST | `/api/dashboard/save` | DashboardApiController@saveDashboard | Guardar config |
| GET | `/api/dashboard/export` | DashboardApiController@export | Exportar |

### SECOP API

| Metodo | URL | Controlador | Descripcion |
|--------|-----|-------------|-------------|
| GET | `/api/secop/buscar` | SecopController@buscar | Buscar contratos |
| GET | `/api/secop/contrato/{id}` | SecopController@obtenerContrato | Detalle contrato |
| GET | `/api/secop/estadisticas` | SecopController@estadisticas | Estadisticas |

## 7.3 Servicios de Logica de Negocio

### AlertaService

```php
// Generar alertas automaticas (ejecutar via cron)
AlertaService::generarAlertasAutomaticas();

// Crear alerta individual
AlertaService::crear(
    procesoId: 1,
    userId: 5,
    tipo: 'tiempo_excedido',
    titulo: 'Proceso retrasado',
    mensaje: 'El proceso ha excedido el tiempo estimado',
    prioridad: 'alta'
);

// Marcar como leida
AlertaService::marcarLeida($alertaId);
```

### ContratoDirectoPNStateMachine

```php
$stateMachine = new ContratoDirectoPNStateMachine($proceso);

// Crear solicitud inicial
$stateMachine->crearSolicitud($datos);

// Ejecutar transicion
$stateMachine->transicionar('CDP_SOLICITADO', $userId);

// Verificar si puede avanzar
$errores = $stateMachine->erroresParaAvanzar();
$puede = $stateMachine->puedeAvanzar(); // true/false
```

### WorkflowEngine

```php
$engine = new WorkflowEngine();

// Inicializar proceso
$engine->initializeWorkflow($contractProcess);

// Verificar avance
$result = $engine->canAdvance($contractProcess); // ['can' => bool, 'errors' => []]

// Avanzar
$engine->advance($contractProcess, $userId, $notes);

// Devolver
$engine->returnToStep($contractProcess, $stepNumber, $userId, $reason);
```

### ValidacionContratacionService

```php
$validacion = new ValidacionContratacionService();

// Verificar requisitos SECOP
$requiere = $validacion->requierePublicacionSECOP($valor); // >= 10 SMMLV

// Obtener garantias requeridas
$garantias = $validacion->obtenerGarantiasRequeridas($modalidad, $valor);
// ['cumplimiento' => 10%, 'calidad' => 10%, ...]

// Validar modalidad vs cuantia
$valido = $validacion->validarModalidadPorCuantia('minima_cuantia', $valor);
```

---

# 8. REGLAS DE NEGOCIO

## 8.1 Reglas de Contratacion

### 8.1.1 Cuantias y Modalidades (Valores 2026)

| Modalidad | Rango SMMLV | Valor COP |
|-----------|-------------|-----------|
| Minima Cuantia | < 10 | < $17,509,050 |
| Menor Cuantia | 10 - 100 | $17M - $175M |
| Media Cuantia | 100 - 1000 | $175M - $1,750M |
| Mayor Cuantia | > 1000 | > $1,750M |

**SMMLV 2026:** $1,750,905

### 8.1.2 Requisitos por Modalidad

| Requisito | < 10 SMMLV | 10-100 SMMLV | > 100 SMMLV |
|-----------|------------|--------------|-------------|
| Publicacion SECOP | Opcional | Obligatoria | Obligatoria |
| RUP | No | No | SI |
| Plazo publicacion | 1 dia | 3 dias | 5-10 dias |

### 8.1.3 Garantias Requeridas

| Tipo Garantia | Porcentaje | Aplica a |
|---------------|------------|----------|
| Cumplimiento | 10% valor | Todos los contratos |
| Calidad | 10% valor | Bienes y servicios |
| Anticipo | 100% anticipo | Si hay anticipo |
| Salarios | 5% valor | Obra/prestacion servicios |

## 8.2 Reglas de Flujo

### 8.2.1 Validaciones Obligatorias

| Etapa | Validacion | Descripcion |
|-------|------------|-------------|
| 1 | Estudio previo | Archivo PDF obligatorio |
| 2 | Compatibilidad | Debe aprobarse antes de CDP |
| 2 | CDP | Requiere compatibilidad aprobada |
| 3 | Documentos contratista | Checklist completo |
| 5 | Firmas | Ambas firmas (contratista + ordenador) |
| 7 | RPC | CDP debe existir |

### 8.2.2 Regla Critica: CDP y Compatibilidad

```
CDP NO puede emitirse SIN Compatibilidad del Gasto aprobada.

Flujo correcto:
1. Solicitar validacion PAA
2. Solicitar Certificado No Planta
3. Solicitar Paz y Salvos (Rentas, Contabilidad)
4. Aprobar Compatibilidad del Gasto
5. ENTONCES solicitar CDP
```

### 8.2.3 Transiciones Permitidas por Estado

| Estado Actual | Estados Siguientes | Rol Requerido |
|---------------|-------------------|---------------|
| BORRADOR | ESTUDIO_PREVIO_CARGADO | unidad_solicitante |
| ESTUDIO_PREVIO_CARGADO | EN_VALIDACION_PLANEACION | unidad_solicitante |
| EN_VALIDACION_PLANEACION | COMPATIBILIDAD_APROBADA | planeacion |
| CDP_APROBADO | DOCUMENTACION_INCOMPLETA | hacienda |
| EN_REVISION_JURIDICA | PROCESO_NUMERO_GENERADO, CONTRATO_DEVUELTO | juridica |
| CONTRATO_FIRMADO_TOTAL | RPC_SOLICITADO | planeacion |
| RPC_FIRMADO | EXPEDIENTE_RADICADO | hacienda |
| EXPEDIENTE_RADICADO | EN_EJECUCION | unidad_solicitante |

## 8.3 Reglas de Documentos

### 8.3.1 Vigencia de Documentos

| Tipo Documento | Vigencia | Accion al Vencer |
|----------------|----------|------------------|
| Antecedentes Disciplinarios | 30 dias | Alerta + bloqueo avance |
| Antecedentes Fiscales | 30 dias | Alerta + bloqueo avance |
| Antecedentes Penales | 30 dias | Alerta + bloqueo avance |
| Certificado Medico | 90 dias | Alerta critica |
| RUT | 365 dias | Alerta |
| Polizas | Segun contrato | Alerta critica |

### 8.3.2 Documentos por Etapa CD-PN

| Etapa | Documentos Obligatorios |
|-------|------------------------|
| 1 | Estudio Previo |
| 2 | Certificado PAA, Compatibilidad Gasto, CDP |
| 3 | Cedula, RUT, Certificado Medico, Antecedentes (3), SIGEP |
| 4 | Invitacion, Carta Aceptacion, Designacion Supervisor |
| 5 | Contrato Electronico, Verificacion Contratista |
| 6 | Publicacion SECOP, Firmas |
| 7 | RPC |

## 8.4 Reglas de Modificaciones Contractuales

### 8.4.1 Limites Legales

| Tipo | Limite | Restriccion |
|------|--------|-------------|
| Adicion en valor | 50% valor inicial | Acumulativo |
| Prorroga | 50% plazo inicial | Acumulativo |
| Adicion + Prorroga | 50% cada uno | Independientes |

### 8.4.2 Validacion de Modificaciones

```php
// Verificar limite antes de aprobar
$porcentajeActual = ModificacionContractual::porcentajeAdicionesAcumuladas($procesoId);
$excede = ModificacionContractual::excedeLimite50($procesoId, $valorNuevaAdicion);

if ($excede) {
    throw new Exception('La adicion excede el 50% del valor inicial');
}
```

## 8.5 Reglas de Alertas

### 8.5.1 Prioridades Automaticas

| Condicion | Prioridad |
|-----------|-----------|
| Certificado vence en < 2 dias | CRITICA |
| Certificado vence en 2-5 dias | ALTA |
| Tiempo excedido en etapa | ALTA |
| Sin actividad > 15 dias | ALTA |
| Documento pendiente 3-5 dias | MEDIA |
| Documento pendiente < 3 dias | BAJA |

### 8.5.2 Generacion Automatica

```bash
# Ejecutar via cron cada hora
php artisan alertas:generar

# Tipos de alertas generadas:
# - tiempo_excedido: Proceso excede dias estimados en etapa
# - certificado_por_vencer: Documento cerca de expirar
# - sin_actividad: Proceso sin cambios por N dias
# - documento_pendiente: Documento esperando aprobacion
# - documento_rechazado: Documento rechazado requiere accion
```

---

# 9. VALIDACIONES

## 9.1 Validaciones de Formularios

### 9.1.1 Crear Proceso

```php
// ProcesoRequest.php
[
    'flujo_id' => 'required|exists:flujos,id',
    'objeto' => 'required|string|min:10|max:500',
    'descripcion' => 'nullable|string|max:2000',
    'valor_estimado' => 'required|numeric|min:0',
    'plazo_ejecucion' => 'required|integer|min:1',
    'secretaria_id' => 'required|exists:secretarias,id',
    'unidad_id' => 'required|exists:unidades,id',
    'estudio_previo' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB
]
```

### 9.1.2 Crear Proceso CD-PN

```php
// ProcesoCDRequest.php
[
    'objeto' => 'required|string|min:10',
    'valor' => 'required|numeric|min:0',
    'plazo_meses' => 'required|integer|min:1|max:12',
    'estudio_previo' => 'required|file|mimes:pdf|max:10240',
    'secretaria_id' => 'required|exists:secretarias,id',
    'unidad_id' => 'required|exists:unidades,id',
    'contratista_nombre' => 'nullable|string|max:255',
    'contratista_tipo_documento' => 'nullable|in:CC,CE,PA',
    'contratista_documento' => 'nullable|string|max:20',
    'contratista_email' => 'nullable|email',
    'contratista_telefono' => 'nullable|string|max:20',
]
```

### 9.1.3 Subir Documento

```php
// DocumentoRequest.php
[
    'archivo' => 'required|file|max:10240',
    'tipo_archivo' => 'required|string',
    'observaciones' => 'nullable|string|max:500',
]

// Validacion adicional por tipo
$tiposPermitidos = TipoArchivoPorEtapa::where('etapa_id', $etapaId)->pluck('tipo');
if (!$tiposPermitidos->contains($tipoArchivo)) {
    throw ValidationException::withMessages([
        'tipo_archivo' => 'Tipo de archivo no permitido en esta etapa'
    ]);
}
```

## 9.2 Validaciones de Negocio

### 9.2.1 Validacion de Avance de Etapa

```php
// WorkflowEngine::canAdvance()

$errores = [];

// 1. Documentos requeridos
$faltantes = $this->getMissingRequiredDocuments($process);
if (!empty($faltantes)) {
    $errores[] = "Faltan documentos: " . implode(', ', $faltantes);
}

// 2. Documentos expirados
if ($process->hasExpiredDocuments()) {
    $errores[] = "Hay documentos vencidos que deben actualizarse";
}

// 3. Aprobaciones pendientes
$pendientes = $process->getPendingApprovals();
if ($pendientes->isNotEmpty()) {
    $errores[] = "Hay aprobaciones pendientes";
}

// 4. Reglas especificas de etapa
$errores = array_merge($errores, $this->validateStepSpecificRules($process));

return [
    'can' => empty($errores),
    'errors' => $errores
];
```

### 9.2.2 Validacion de Transicion CD-PN

```php
// ContratoDirectoPNStateMachine::transicionar()

// 1. Validar rol autorizado
if (!in_array($user->role, $estadoActual->rolesAutorizados())) {
    throw new UnauthorizedException('Rol no autorizado para esta transicion');
}

// 2. Validar transicion permitida
if (!in_array($nuevoEstado, $estadoActual->transicionesPermitidas())) {
    throw new InvalidTransitionException('Transicion no permitida');
}

// 3. Validar documentos obligatorios
$docsObligatorios = $estadoActual->documentosObligatorios();
foreach ($docsObligatorios as $doc) {
    if (!$proceso->tieneDocumento($doc)) {
        throw new MissingDocumentException("Falta documento: $doc");
    }
}

// 4. Validacion especifica CDP
if ($nuevoEstado === 'CDP_SOLICITADO') {
    if (!$proceso->compatibilidad_aprobada) {
        throw new BusinessRuleException('CDP requiere Compatibilidad del Gasto aprobada');
    }
}
```

## 9.3 Validaciones de Archivos

```php
// ArchivosPorAreaService::validarArchivo()

$config = $this->obtenerConfiguracion($area, $tipoArchivo);

// 1. Validar MIME type
$mimesPermitidos = $config['mime_types'];
if (!in_array($archivo->getMimeType(), $mimesPermitidos)) {
    return ['valido' => false, 'error' => 'Tipo de archivo no permitido'];
}

// 2. Validar tamano
$maxTamano = $config['max_size_mb'] * 1024 * 1024;
if ($archivo->getSize() > $maxTamano) {
    return ['valido' => false, 'error' => "Archivo excede {$config['max_size_mb']}MB"];
}

// 3. Validar extension
$extPermitidas = $config['extensiones'];
$ext = strtolower($archivo->getClientOriginalExtension());
if (!in_array($ext, $extPermitidas)) {
    return ['valido' => false, 'error' => 'Extension no permitida'];
}

return ['valido' => true];
```

## 9.4 Validaciones de Acceso

### 9.4.1 Middleware CheckSecretariaAccess

```php
// Valida que usuario solo acceda a datos de su secretaria
public function handle($request, Closure $next, $strict = false)
{
    $user = $request->user();

    // Admin general tiene acceso total
    if ($user->hasRole(['admin', 'admin_general'])) {
        return $next($request);
    }

    // Obtener secretaria del request
    $secretariaId = $request->route('secretaria_id') ?? $request->input('secretaria_id');

    if ($strict && !$secretariaId) {
        abort(400, 'Secretaria requerida');
    }

    if ($secretariaId && $user->secretaria_id !== (int)$secretariaId) {
        abort(403, 'No tiene acceso a esta secretaria');
    }

    return $next($request);
}
```

### 9.4.2 Policy ContractProcessPolicy

```php
// Validacion de avance por etapa
public function advance(User $user, ContractProcess $process): bool
{
    if ($user->hasRole('super_admin')) {
        return true;
    }

    $currentStep = $process->current_step;
    $allowedRoles = $this->getRolesForStep($currentStep);

    // Verificar rol
    if ($user->hasAnyRole($allowedRoles)) {
        return true;
    }

    // Verificar relacion con proceso
    return $this->isRelatedToProcess($user, $process);
}

private function getRolesForStep(int $step): array
{
    return match($step) {
        0 => ['jefe_unidad', 'apoyo_estructuracion'],
        1 => ['jefe_unidad', 'apoyo_estructuracion', 'presupuesto'],
        2, 3 => ['abogado_unidad', 'abogado_enlace_juridica'],
        4 => ['jefe_unidad', 'apoyo_estructuracion'],
        5, 6, 8 => ['abogado_enlace_juridica'],
        7 => ['jefe_unidad', 'presupuesto'],
        9 => ['supervisor', 'jefe_unidad'],
        default => []
    };
}
```

---

# 10. INTEGRACIONES

## 10.1 SECOP II (Datos Abiertos)

### 10.1.1 Configuracion

```php
// config/services.php
'secop' => [
    'base_url' => env('SECOP_API_URL', 'https://www.datos.gov.co/resource/'),
    'dataset_id' => env('SECOP_DATASET_ID', 'p6dx-8zbt'),
    'app_token' => env('SECOP_APP_TOKEN'),
    'username' => env('SECOP_USERNAME'),
    'password' => env('SECOP_PASSWORD'),
    'cache_ttl' => env('SECOP_CACHE_TTL', 10), // minutos
]
```

### 10.1.2 Endpoints Utilizados

| Operacion | Endpoint Socrata | Metodo |
|-----------|------------------|--------|
| Buscar por referencia | `?$where=referencia_del_contrato='{ref}'` | GET |
| Buscar por entidad | `?$where=nombre_entidad LIKE '%Caldas%'` | GET |
| Obtener contrato | `?$where=id_contrato='{id}'` | GET |
| Estadisticas | `?$select=estado_contrato,count(*)&$group=estado_contrato` | GET |

### 10.1.3 Uso en el Sistema

```php
$secop = new SecopDatosAbiertoService();

// Buscar contratos
$contratos = $secop->buscarPorReferencia('CD-PN-001-2026');

// Obtener estadisticas
$stats = $secop->obtenerEstadisticas();
// ['celebrado' => 150, 'en_ejecucion' => 45, 'terminado' => 200, ...]

// Cache automatico de 10 minutos
// Para forzar actualizacion:
$secop->refrescar('contrato_123');
```

## 10.2 Laravel Echo / Pusher (Real-time)

### 10.2.1 Configuracion

```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true
});
```

### 10.2.2 Canales Utilizados

| Canal | Tipo | Eventos |
|-------|------|---------|
| `alertas-globales` | Publico | AlertaCriticaCreada |
| `user.{id}` | Privado | ProcesoAsignado, DocumentoAprobado |
| `secretaria.{id}` | Presencia | DashboardActualizado, MetricaActualizada |

### 10.2.3 Eventos Emitidos

```php
// Proceso asignado a usuario
event(new ProcesoAsignado($proceso, $usuario));

// Documento aprobado
event(new DocumentoAprobado($documento, $aprobador));

// Alerta critica
event(new AlertaCriticaCreada($alerta));

// Metrica actualizada (dashboard)
event(new MetricaActualizada($widget, $nuevoValor));
```

## 10.3 Sistema de Archivos (Laravel Storage)

### 10.3.1 Configuracion

```php
// config/filesystems.php
'disks' => [
    'procesos' => [
        'driver' => 'local',
        'root' => storage_path('app/procesos'),
        'visibility' => 'private',
    ],
    'documentos_cd' => [
        'driver' => 'local',
        'root' => storage_path('app/documentos-cd'),
        'visibility' => 'private',
    ],
]
```

### 10.3.2 Estructura de Almacenamiento

```
storage/app/
├── procesos/
│   └── {proceso_id}/
│       └── etapa_{etapa_id}/
│           └── {tipo_archivo}_{timestamp}.{ext}
├── documentos-cd/
│   └── {proceso_cd_id}/
│       └── {document_type}_{timestamp}.{ext}
└── exports/
    └── {user_id}/
        └── {report_name}_{date}.{xlsx|csv}
```

---

# 11. DEPENDENCIAS TECNICAS

## 11.1 Dependencias PHP (composer.json)

| Paquete | Version | Proposito |
|---------|---------|-----------|
| `laravel/framework` | ^11.0 | Framework base |
| `laravel/breeze` | ^2.0 | Autenticacion |
| `spatie/laravel-permission` | ^6.0 | Roles y permisos |
| `maatwebsite/excel` | ^3.1 | Exportacion Excel |
| `barryvdh/laravel-dompdf` | ^2.0 | Generacion PDF |
| `guzzlehttp/guzzle` | ^7.0 | Cliente HTTP (SECOP) |
| `pusher/pusher-php-server` | ^7.0 | WebSockets |

## 11.2 Dependencias JavaScript (package.json)

| Paquete | Version | Proposito |
|---------|---------|-----------|
| `react` | ^18.2 | UI Components |
| `react-dom` | ^18.2 | React DOM |
| `@xyflow/react` | ^12.0 | Motor de flujos visual |
| `react-grid-layout` | ^1.4 | Grid de dashboards |
| `recharts` | ^2.12 | Graficos |
| `alpinejs` | ^3.0 | Interactividad Blade |
| `axios` | ^1.6 | Cliente HTTP |
| `laravel-echo` | ^1.15 | WebSockets cliente |
| `pusher-js` | ^8.0 | Pusher cliente |
| `tailwindcss` | ^3.4 | CSS Framework |
| `vite` | ^5.0 | Build tool |
| `@vitejs/plugin-react` | ^4.0 | Plugin React para Vite |

## 11.3 Requisitos del Servidor

| Componente | Requisito Minimo | Recomendado |
|------------|------------------|-------------|
| PHP | 8.2 | 8.3 |
| MySQL | 8.0 | 8.0+ |
| Node.js | 18.x | 20.x |
| Composer | 2.x | 2.x |
| RAM | 2GB | 4GB |
| Almacenamiento | 20GB | 50GB+ |

## 11.4 Configuracion de Entorno (.env)

```env
# Aplicacion
APP_NAME="Seguimiento Documentos Gobernacion"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://contratos.caldas.gov.co

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seguimiento_docs
DB_USERNAME=app_user
DB_PASSWORD=secure_password

# Cache y Sesiones
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# SECOP II
SECOP_API_URL=https://www.datos.gov.co/resource/
SECOP_DATASET_ID=p6dx-8zbt
SECOP_APP_TOKEN=your_app_token
SECOP_CACHE_TTL=10

# Pusher (Real-time)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=us2

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

## 11.5 Comandos Artisan Personalizados

| Comando | Proposito | Frecuencia |
|---------|-----------|------------|
| `alertas:generar` | Generar alertas automaticas | Cada hora |
| `production:clean-test-data` | Limpiar datos de prueba | Una vez |
| `production:verify-readiness` | Verificar produccion | Antes de deploy |

## 11.6 Tareas Programadas (Cron)

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Generar alertas cada hora
    $schedule->command('alertas:generar')
        ->hourly()
        ->withoutOverlapping();

    // Limpiar sesiones expiradas
    $schedule->command('session:gc')
        ->daily();

    // Refrescar cache SECOP
    $schedule->command('cache:clear-tag', ['secop'])
        ->everyThirtyMinutes();
}
```

---

# ANEXOS

## A. Glosario de Terminos

| Termino | Definicion |
|---------|------------|
| **CDP** | Certificado de Disponibilidad Presupuestal |
| **RPC** | Registro Presupuestal del Compromiso |
| **PAA** | Plan Anual de Adquisiciones |
| **SECOP** | Sistema Electronico de Contratacion Publica |
| **SMMLV** | Salario Minimo Mensual Legal Vigente |
| **RUP** | Registro Unico de Proponentes |
| **CD-PN** | Contratacion Directa - Persona Natural |
| **CD-PJ** | Contratacion Directa - Persona Juridica |
| **SIGEP** | Sistema de Informacion y Gestion del Empleo Publico |

## B. Contactos del Sistema

| Rol | Responsabilidad |
|-----|-----------------|
| Administrador General | Configuracion global, usuarios, roles |
| Admin Secretaria | Gestion de su secretaria |
| Soporte Tecnico | Incidencias, mantenimiento |

## C. Historial de Versiones del Documento

| Version | Fecha | Cambios |
|---------|-------|---------|
| 1.0.0 | 2026-03-27 | Version inicial completa |

---

**Documento generado automaticamente basado en analisis del codigo fuente.**
**Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas**
