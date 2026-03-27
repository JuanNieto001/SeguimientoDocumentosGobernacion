# DOCUMENTACIÓN TÉCNICA COMPLETA
## Sistema de Seguimiento Contractual - Gobernación de Caldas

**Versión:** 1.0
**Fecha:** Marzo 2026
**Autor:** Arquitecto de Software Senior
**Proyecto:** Sistema de Seguimiento Documentos Gubernamentales

---

## TABLA DE CONTENIDOS

1. [Introducción](#1-introducción)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Descripción Funcional](#3-descripción-funcional)
4. [Roles y Gestión de Usuarios](#4-roles-y-gestión-de-usuarios)
5. [Motor de Dashboard (Estado Actual)](#5-motor-de-dashboard-estado-actual)
6. [Problemas Identificados](#6-problemas-identificados)
7. [Recomendaciones](#7-recomendaciones)
8. [Flujos del Sistema](#8-flujos-del-sistema)
9. [Plan de Pruebas](#9-plan-de-pruebas)
10. [Manual de Usuario (Base)](#10-manual-de-usuario-base)
11. [Notas Argumentativas para Revisión en Word](#11-notas-argumentativas-para-revisión-en-word)

---

## 1. INTRODUCCIÓN

### 1.1 Propósito del Sistema

El Sistema de Seguimiento Contractual de la Gobernación de Caldas es una plataforma web integral diseñada para gestionar, monitorear y supervisar los procesos de contratación pública. El sistema implementa flujos configurables que permiten el seguimiento detallado de cada etapa del proceso contractual, desde la planificación hasta la liquidación.

### 1.2 Alcance

La aplicación abarca:
- Gestión de flujos de contratación (Directa Persona Natural, Directa Persona Jurídica, Licitación Pública, etc.)
- Motor de dashboards interactivo con vistas personalizables por rol y usuario
- Sistema de alertas y notificaciones automatizadas
- Gestión documental con control de versiones
- Auditoría completa de acciones y cambios
- Reportes y analytics en tiempo real

### 1.3 Usuarios Objetivo

- **Gobernador**: Vista ejecutiva y estratégica
- **Secretarios de Despacho**: Supervisión por secretaría
- **Jefes de Unidad**: Gestión operativa por unidad
- **Personal Administrativo**: Ejecución de procesos específicos
- **Área Jurídica**: Revisión legal y conceptos
- **Área de Hacienda**: Revisión presupuestal
- **SECOP**: Publicación en plataforma nacional

---

## 2. ARQUITECTURA DEL SISTEMA

### 2.1 Stack Tecnológico

**Backend:**
- **Framework:** Laravel 12.x (PHP 8.2+)
- **Base de Datos:** SQLite (desarrollo) / MySQL (producción)
- **Autenticación:** Laravel Breeze con sesiones
- **Autorización:** Spatie Laravel Permission v6.24
- **ORM:** Eloquent con 50+ modelos relacionados

**Frontend:**
- **Motor de Vistas:** Blade Templates (PHP)
- **Componentes Dinámicos:** React 19.2.4 + React DOM
- **Visualización de Flujos:** @xyflow/react 12.10.1 (React Flow)
- **Estilos:** Tailwind CSS 3.x + @tailwindcss/forms
- **Build System:** Vite 7.0.7 con HMR

### 2.2 Estructura de Directorios

```
/app
  /Http
    /Controllers/
      /Admin/           → Gestión administrativa
      /Api/             → Endpoints REST
      /Area/            → Controladores por área funcional
      /Auth/            → Autenticación personalizada
    /Middleware/        → Middlewares de autorización
    /Requests/          → Form Request Validation
  /Models/              → 50+ modelos Eloquent
  /Services/            → Lógica de negocio encapsulada
  /Support/             → Clases helper y utilidades

/database
  /migrations/          → 56 migraciones estructuradas
  /seeders/             → 16 seeders con datos iniciales

/resources
  /views/               → 200+ vistas Blade organizadas por módulos
    /admin/             → Panel administrativo
    /areas/             → Vistas por área funcional
    /auth/              → Autenticación
    /dashboard/         → Dashboards estáticos
    /dashboards/motor/  → Dashboard dinámico
  /js/                  → Componentes React
    motor-flujos.jsx    → Editor visual de flujos
    dashboard-motor.jsx → Dashboard interactivo
  /css/                 → Estilos Tailwind

/routes
  web.php               → 550 líneas de rutas web
  api.php               → 166 líneas de rutas API
```

### 2.3 Base de Datos - Arquitectura

**Entidades Principales:**

1. **Usuarios y Permisos:**
   - users, roles, permissions (Spatie)
   - secretarias, unidades
   - auth_events (registro de sesiones)

2. **Flujos y Procesos:**
   - flujos, flujo_pasos, flujo_paso_documentos
   - procesos, proceso_etapas, proceso_etapa_checks
   - proceso_etapa_archivos

3. **Dashboard Motor:**
   - dashboard_plantillas, dashboard_widgets
   - dashboard_rol_asignaciones
   - dashboard_usuario_asignaciones
   - dashboard_secretaria_asignaciones
   - dashboard_unidad_asignaciones
   - dashboard_asignacion_auditorias

4. **Gestión Documental:**
   - documentos, documento_versiones
   - tipo_archivo_por_etapas

5. **Auditoría y Alertas:**
   - proceso_auditorias
   - alertas
   - tracking_eventos

### 2.4 Patrones Arquitectónicos

- **MVC:** Separación clara de responsabilidades
- **Repository Pattern:** Encapsulación de acceso a datos
- **Observer Pattern:** Eventos y listeners para auditoría
- **Factory Pattern:** Creación de widgets de dashboard
- **Strategy Pattern:** Diferentes tipos de validaciones por flujo

---

## 3. DESCRIPCIÓN FUNCIONAL

### 3.1 Módulos Principales

#### 3.1.1 Motor de Flujos Configurable

**Ubicación:** `/motor-flujos` (React Component: motor-flujos.jsx)

**Funcionalidades:**
- Editor visual drag & drop para diseño de flujos
- Configuración de pasos con documentos requeridos
- Definición de roles responsables por etapa
- Validaciones personalizadas por tipo de proceso
- Exportación e importación de configuraciones

**Flujos Implementados:**
1. **CD-PN:** Contratación Directa Persona Natural (9 etapas)
2. **CD-PJ:** Contratación Directa Persona Jurídica (9 etapas)
3. **LP:** Licitación Pública (15+ etapas)
4. **MC:** Menor Cuantía (12 etapas)
5. **SA:** Selección Abreviada (10 etapas)

#### 3.1.2 Motor de Dashboard Interactivo

**Ubicación:** `/dashboards/motor` (React Component: dashboard-motor.jsx)

**Características:**
- Constructor BI-style con drag & drop
- Plantillas reutilizables por rol
- Widgets configurables (KPIs, Charts, Tables)
- Persistencia de layout personalizado
- Herencia: Global → Secretaría → Unidad → Usuario

**Tipos de Widget:**
- **KPIs:** Métricas numéricas con iconografía
- **Charts:** 6 tipos (bar, line, pie, doughnut, polarArea, radar)
- **Tables:** Listados filtrados y paginados

#### 3.1.3 Sistema de Procesos

**Funcionalidades:**
- Creación de procesos basados en flujos configurados
- Seguimiento de estados y transiciones
- Carga y validación de documentos por etapa
- Comentarios y observaciones
- Timeline con historial de cambios

#### 3.1.4 Gestión de Usuarios y Roles

**Roles Implementados:**
- Super Admin, Admin
- Gobernador, Secretarios, Jefes de Unidad
- Profesional Junior/Senior por área
- Abogado, Contador, SECOP

**Capacidades:**
- Asignación múltiple de roles
- Permisos granulares por funcionalidad
- Gestión de unidades y secretarías
- Histórico de cambios de permisos

### 3.2 APIs Disponibles

**Endpoints Principales:**

```
GET    /api/procesos                    → Lista de procesos
POST   /api/procesos                    → Crear proceso
GET    /api/procesos/{id}               → Detalle de proceso
PUT    /api/procesos/{id}/avanzar       → Avanzar etapa
POST   /api/procesos/{id}/documentos    → Subir documento

GET    /api/dashboard/widgets           → Widgets disponibles
POST   /api/dashboard/configuracion     → Guardar configuración
GET    /api/dashboard/datos/{metrica}   → Datos para widget

GET    /api/flujos                      → Lista de flujos
POST   /api/flujos                      → Crear flujo
PUT    /api/flujos/{id}                 → Actualizar flujo
```

---

## 4. ROLES Y GESTIÓN DE USUARIOS

### 4.1 Jerarquía de Roles

```
Super Admin
├── Admin
├── Gobernador
├── Secretario
│   ├── Jefe de Unidad
│   │   ├── Profesional Senior
│   │   └── Profesional Junior
│   ├── Abogado
│   └── Contador
└── SECOP
```

### 4.2 Permisos Granulares

| Funcionalidad | Super Admin | Admin | Gobernador | Secretario | Jefe Unidad | Profesional |
|---------------|-------------|--------|------------|------------|-------------|-------------|
| Gestionar usuarios | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| Ver todos los procesos | ✓ | ✓ | ✓ | ✓ (su secretaría) | ✓ (su unidad) | ✓ (asignados) |
| Crear procesos | ✓ | ✓ | ✗ | ✓ | ✓ | ✓ |
| Configurar flujos | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| Dashboard global | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| Dashboard secretaría | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| Dashboard unidad | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |

### 4.3 Segmentación de Datos

**Por Usuario:**
- Solo procesos asignados o de su unidad
- Dashboard personalizable

**Por Unidad:**
- Procesos de la unidad específica
- Reportes agregados por unidad
- Dashboard heredado de plantilla de unidad

**Por Secretaría:**
- Todos los procesos de unidades bajo la secretaría
- Consolidados por secretaría
- Dashboard ejecutivo de secretaría

**Global (Gobernador):**
- Vista consolidada de todas las secretarías
- Métricas estratégicas
- Dashboard ejecutivo global

---

## 5. MOTOR DE DASHBOARD (ESTADO ACTUAL)

### 5.1 Arquitectura del Dashboard

**Modelo de Herencia:**

```
Plantilla Global (Base)
├── Asignación por Rol
├── Asignación por Secretaría
├── Asignación por Unidad
└── Asignación por Usuario (Individual)
```

### 5.2 Componentes Actuales

#### 5.2.1 DashboardMotorController
- **Responsabilidades:** CRUD de plantillas, asignaciones, widgets
- **Endpoints:** 15+ rutas para gestión completa
- **Validaciones:** Configuración JSON de widgets
- **Auditoría:** Registro de cambios en asignaciones

#### 5.2.2 Modelos de Datos

```php
DashboardPlantilla          → Plantillas base reutilizables
├── DashboardWidget         → Widgets configurados
DashboardRolAsignacion      → Asignación por rol
DashboardUsuarioAsignacion  → Asignación individual
DashboardSecretariaAsignacion → Asignación por secretaría
DashboardUnidadAsignacion   → Asignación por unidad
DashboardAsignacionAuditoria → Historial de cambios
```

#### 5.2.3 Widget Library

**KPIs Disponibles:**
- Procesos en curso
- Procesos finalizados del mes
- Alertas altas no leídas
- Contratos vigentes
- Contratos por vencer (90 días)
- Valor total de contratos

**Charts Disponibles:**
- Procesos por área
- Procesos por estado
- Contratos por mes

### 5.3 Configuración Actual

**Tipos de Gráfico por Métrica:**
```php
'procesos_por_area' => ['bar', 'line', 'pie', 'doughnut', 'polarArea', 'radar']
'procesos_por_estado' => ['doughnut', 'pie', 'bar', 'polarArea', 'radar']
'contratos_por_mes' => ['line', 'bar', 'area']
```

**Alcances de Datos:**
- **usuario:** Solo datos del usuario autenticado
- **unidad:** Datos de la unidad del usuario
- **secretaria:** Datos de toda la secretaría
- **global:** Datos de toda la organización

### 5.4 Funcionalidades Implementadas

✅ **Completado:**
- Constructor visual drag & drop
- Persistencia de configuraciones
- Herencia de plantillas por rol
- Asignaciones individuales por usuario
- Auditoría de cambios
- Widgets KPI y Chart básicos

### 5.5 Limitaciones Identificadas

❌ **Pendientes:**
- Filtros dinámicos por fecha
- Widgets de tipo tabla/listado
- Exportación de dashboards
- Compartir dashboards entre usuarios
- Templates predefinidos por industria
- Cache inteligente de datos
- Alertas en tiempo real en widgets

---

## 6. PROBLEMAS IDENTIFICADOS

### 6.1 Problemas de Arquitectura

#### P1: Falta de Separación de Responsabilidades
**Descripción:** Los controladores tienen demasiadas responsabilidades, mezclando lógica de presentación con lógica de negocio.

**Impacto:**
- Dificulta el mantenimiento
- Complica las pruebas unitarias
- Reduce la reutilización de código

**Evidencia:** DashboardMotorController tiene 800+ líneas con lógica de persistencia, validación y presentación.

#### P2: Inconsistencia en Manejo de Errores
**Descripción:** No hay un manejo centralizado de excepciones ni respuestas de error consistentes.

**Impacto:**
- Experiencia de usuario inconsistente
- Dificultad en debugging
- Información sensible expuesta en errores

### 6.2 Problemas de Performance

#### P3: N+1 Queries en Dashboard
**Descripción:** Las consultas para widgets generan múltiples queries no optimizadas.

**Impacto:**
- Tiempo de carga elevado (>3s para dashboard completo)
- Degradación con usuarios concurrentes
- Uso excesivo de recursos de base de datos

#### P4: Ausencia de Cache
**Descripción:** Los datos de dashboard se calculan en cada request sin cache.

**Impacto:**
- Consultas repetitivas innecesarias
- Latencia alta en métricas complejas
- Escalabilidad limitada

### 6.3 Problemas de UX/UI

#### P5: Dashboard No Responsivo
**Descripción:** El motor de dashboard no se adapta correctamente a dispositivos móviles.

**Impacto:**
- Inutilizable en tablets/móviles
- Limitación para usuarios de campo
- Experiencia fragmentada

#### P6: Falta de Filtros Dinámicos
**Descripción:** Los usuarios no pueden filtrar widgets por fechas, usuarios o categorías.

**Impacto:**
- Información estática poco útil
- No permite análisis ad-hoc
- Reduce valor analítico

### 6.4 Problemas de Seguridad

#### P7: Autorización Granular Incompleta
**Descripción:** Los permisos de dashboard no consideran la sensibilidad de los datos.

**Impacto:**
- Posible exposición de datos confidenciales
- Violación de principio de mínimo privilegio
- Riesgo de compliance

#### P8: Validación Insuficiente de Configuraciones
**Descripción:** Los JSONs de configuración de widgets no tienen validación robusta.

**Impacto:**
- Posibilidad de inyección de código
- Configuraciones corruptas
- Inestabilidad del sistema

### 6.5 Problemas de Datos

#### P9: Inconsistencia en Datos de Prueba
**Descripción:** Los seeders crean usuarios y datos de prueba mezclados con datos reales.

**Impacto:**
- Confusión en desarrollo
- Posible deploy de datos de prueba
- Dificultad en testing

#### P10: Ausencia de Data Governance
**Descripción:** No hay políticas claras de retención, archivado o limpieza de datos.

**Impacto:**
- Crecimiento descontrolado de BD
- Performance degradada con el tiempo
- Dificultad para cumplir GDPR/normativas

---

## 7. RECOMENDACIONES

### 7.1 Recomendaciones de Arquitectura

#### R1: Implementar Pattern Repository + Service Layer
**Objetivo:** Separar responsabilidades y mejorar testabilidad

**Implementación:**
```php
// Ejemplo propuesto
App/Repositories/DashboardRepository.php
App/Services/DashboardService.php
App/Services/WidgetDataService.php
App/Http/Resources/DashboardResource.php
```

**Beneficios:**
- Código más mantenible
- Testing más sencillo
- Reutilización entre controladores

#### R2: Centralizar Manejo de Errores
**Implementación:**
```php
App/Exceptions/DashboardException.php
App/Http/Responses/ErrorResponse.php
```

### 7.2 Recomendaciones de Performance

#### R3: Implementar Cache Estratégico
**Niveles propuestos:**
- **Cache L1:** Redis para datos de sesión y widgets frecuentes
- **Cache L2:** Query cache para métricas complejas (TTL: 15min)
- **Cache L3:** File cache para configuraciones estáticas

#### R4: Optimizar Consultas de Dashboard
**Estrategias:**
- Implementar eager loading consistente
- Crear vistas materializadas para métricas complejas
- Índices optimizados para queries frecuentes

### 7.3 Recomendaciones de UX/UI

#### R5: Dashboard Responsivo Total
**Componentes necesarios:**
- Grid system responsive
- Widgets que colapsan en móvil
- Touch-friendly drag & drop
- Menu lateral colapsable

#### R6: Filtros Dinámicos Avanzados
**Filtros propuestos:**
```javascript
// Filtros recomendados
{
  'fechas': 'date-range-picker',
  'usuarios': 'multi-select-autocomplete',
  'etapas': 'multi-select-checkbox',
  'secretarias': 'hierarchical-select',
  'estados': 'button-group',
  'montos': 'range-slider'
}
```

### 7.4 Recomendaciones de Dashboard

#### R7: Sistema de Templates Predefinidos
**Templates estratégicos:**
- **Ejecutivo:** Vista consolidada para Gobernador
- **Operativo:** Para Jefes de Unidad
- **Analítico:** Para profesionales senior
- **Monitor:** Para seguimiento en tiempo real

#### R8: Widgets Avanzados
**Nuevos tipos de widget:**
- **Tablas dinámicas** con ordenamiento y filtrado
- **Mapas de calor** para distribución geográfica
- **Timeline** para seguimiento de procesos
- **Comparativas** periodo vs periodo
- **Alertas inteligentes** con machine learning

### 7.5 Recomendaciones de Seguridad

#### R9: Autorización Granular por Widget
**Matriz de permisos propuesta:**
```php
'widget_permissions' => [
    'valor_total_contratos' => ['gobernador', 'secretario', 'admin'],
    'procesos_por_area' => ['*'], // Todos los roles
    'alertas_alta_prioridad' => ['jefe_unidad', 'admin'],
]
```

#### R10: Validación Robusta de Configuraciones
**Schema de validación:**
```json
{
  "widget_config_schema": {
    "type": "object",
    "properties": {
      "metrica": {"type": "string", "enum": ["allowed_metrics"]},
      "tipo_grafico": {"type": "string", "enum": ["allowed_charts"]},
      "filtros": {"type": "object", "additionalProperties": false}
    },
    "required": ["metrica", "tipo_grafico"],
    "additionalProperties": false
  }
}
```

---

## 8. FLUJOS DEL SISTEMA

### 8.1 Flujo de Contratación Directa Persona Natural (CD-PN)

**Estado:** ✅ Implementado y Funcional

**Etapas (9 pasos):**

1. **Etapa 0: Inicio y PAA**
   - Verificación en Plan Anual de Adquisiciones
   - Validación presupuestal inicial
   - **Responsable:** Unidad solicitante
   - **Documentos:** PAA, CDP inicial

2. **Etapa 1: Solicitud**
   - Elaboración de términos de referencia
   - Justificación técnica y económica
   - **Responsable:** Unidad ejecutora
   - **Documentos:** Términos de referencia, estudios previos

3. **Etapa 2: Planeación**
   - Revisión técnica de viabilidad
   - Análisis de riesgos
   - **Responsable:** Oficina de Planeación
   - **Documentos:** Concepto técnico, matriz de riesgos

4. **Etapa 3: Hacienda**
   - Verificación presupuestal
   - Expedición de certificado de disponibilidad presupuestal (CDP)
   - **Responsable:** Secretaría de Hacienda
   - **Documentos:** CDP, concepto presupuestal

5. **Etapa 4: Jurídica**
   - Revisión jurídica de términos
   - Validación de procedimiento
   - **Responsable:** Oficina Jurídica
   - **Documentos:** Concepto jurídico, minuta contractual

6. **Etapa 5: Invitación**
   - Invitación directa al contratista
   - Solicitud de propuesta
   - **Responsable:** Unidad ejecutora
   - **Documentos:** Invitación, propuesta del contratista

7. **Etapa 6: Evaluación**
   - Evaluación técnica y económica
   - Verificación de requisitos habilitantes
   - **Responsable:** Comité evaluador
   - **Documentos:** Informe de evaluación, verificación SIGEP

8. **Etapa 7: Adjudicación**
   - Expedición de acto administrativo
   - Notificación al contratista
   - **Responsable:** Ordenador del gasto
   - **Documentos:** Resolución de adjudicación

9. **Etapa 8: Suscripción**
   - Perfeccionamiento del contrato
   - Publicación en SECOP II
   - **Responsable:** SECOP + Unidad ejecutora
   - **Documentos:** Contrato suscrito, garantías, registro SECOP

### 8.2 Flujo de Contratación Directa Persona Jurídica (CD-PJ)

**Estado:** ✅ Implementado

**Diferencias con CD-PN:**
- No requiere documentos personales (diplomas, SIGEP)
- Incluye verificación de existencia y representación legal
- Requiere autorización de órgano societario
- Certificación de seguridad social de todos los empleados (6 meses)

### 8.3 Estados de Proceso

```php
Estados Implementados:
- borrador          → Proceso en creación
- en_curso          → Proceso activo en alguna etapa
- pausado           → Proceso suspendido temporalmente
- finalizado        → Proceso completado exitosamente
- cancelado         → Proceso cancelado sin completar
- observaciones     → Proceso con observaciones pendientes
```

### 8.4 Motor de Flujos Configurable

**Capacidades:**
- ✅ Editor visual con React Flow
- ✅ Drag & drop de etapas
- ✅ Configuración de documentos por etapa
- ✅ Asignación de roles responsables
- ✅ Validaciones personalizadas
- ✅ Persistencia en base de datos

**Limitaciones actuales:**
- ❌ No permite flujos paralelos
- ❌ Falta validación de ciclos infinitos
- ❌ No soporta flujos condicionales (if/else)
- ❌ Ausencia de templates predefinidos

---

## 9. PLAN DE PRUEBAS

### 9.1 Estrategia de Testing

#### 9.1.1 Tipos de Prueba

**Unit Tests (PHPUnit):**
- Modelos Eloquent y relaciones
- Servicios de lógica de negocio
- Helpers y utilidades
- **Objetivo:** 80% de cobertura en modelos

**Feature Tests (PHPUnit):**
- Controladores y rutas
- Middleware de autorización
- APIs REST
- **Objetivo:** 95% de endpoints críticos

**Browser Tests (Cypress):**
- Flujos de usuario completos
- Dashboard interactivo
- Motor de flujos
- **Objetivo:** Casos de uso principales

#### 9.1.2 Ambientes de Prueba

**Testing Environment:**
```php
DB_CONNECTION=sqlite_testing
APP_ENV=testing
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

**Staging Environment:**
- Réplica exacta de producción
- Base de datos con datos anonimizados
- SSL/TLS configurado
- Monitoreo de performance

### 9.2 Casos de Prueba Críticos

#### 9.2.1 Autenticación y Autorización

```php
TestCase: test_user_can_login_with_valid_credentials()
TestCase: test_user_cannot_access_unauthorized_dashboard()
TestCase: test_role_based_permission_enforcement()
TestCase: test_session_timeout_security()
```

#### 9.2.2 Dashboard Motor

```php
TestCase: test_dashboard_widget_creation()
TestCase: test_dashboard_drag_and_drop()
TestCase: test_dashboard_data_filtering()
TestCase: test_dashboard_role_inheritance()
TestCase: test_dashboard_configuration_persistence()
```

#### 9.2.3 Flujo de Procesos

```php
TestCase: test_proceso_creation_with_valid_data()
TestCase: test_proceso_state_transitions()
TestCase: test_documento_upload_and_validation()
TestCase: test_proceso_approval_workflow()
```

### 9.3 Datos de Prueba

#### 9.3.1 Seeders de Testing

```php
TestingSeeder:
├── RoleTestSeeder           → Roles mínimos necesarios
├── UserTestSeeder           → 5 usuarios por rol
├── SecretariaTestSeeder     → 3 secretarías de prueba
├── FlujoPruebaSeeder        → Flujo simplificado de 3 etapas
├── DashboardTestSeeder      → Plantillas básicas
└── ProcesoTestSeeder        → 10 procesos en diferentes estados
```

#### 9.3.2 Factories

```php
UserFactory               → Usuarios con datos realistas
ProcesoFactory           → Procesos en diferentes estados
DashboardPlantillaFactory → Configuraciones variadas
SecretariaFactory        → Secretarías con unidades
```

### 9.4 Performance Testing

#### 9.4.1 Métricas Objetivo

- **Tiempo de carga página:** <2s
- **Dashboard completo:** <3s
- **API response:** <500ms
- **Usuarios concurrentes:** 100+
- **Throughput:** 1000 requests/min

#### 9.4.2 Herramientas

- **Apache Bench:** Para testing de carga básico
- **Artillery.io:** Para testing avanzado de APIs
- **Laravel Telescope:** Para profiling en desarrollo

### 9.5 Security Testing

#### 9.5.1 Pruebas de Seguridad

- **SQL Injection:** En formularios y APIs
- **XSS:** En inputs de dashboard
- **CSRF:** En todas las formas POST
- **Authorization Bypass:** Intentos de escalation
- **File Upload Security:** Validación de archivos

#### 9.5.2 Herramientas

- **OWASP ZAP:** Para scanning automatizado
- **SQLMap:** Para pruebas de SQL injection
- **Burp Suite:** Para análisis manual

---

## 10. MANUAL DE USUARIO (BASE)

### 10.1 Guía de Inicio Rápido

#### 10.1.1 Primer Acceso al Sistema

1. **Acceso inicial:**
   - URL: `https://sistema-contratacion.gobernacion-caldas.gov.co`
   - Credenciales proporcionadas por administrador
   - Cambio obligatorio de contraseña en primer login

2. **Navegación principal:**
   - **Dashboard:** Vista personalizada según rol
   - **Mis Procesos:** Procesos asignados
   - **Flujos:** Configuración de procesos (admin)
   - **Reportes:** Analytics y exportaciones
   - **Configuración:** Perfil y preferencias

#### 10.1.2 Roles y Permisos

**Para Usuarios Operativos:**
- Crear procesos de contratación
- Subir documentos requeridos
- Hacer seguimiento de estado
- Recibir notificaciones automáticas

**Para Jefes de Unidad:**
- Supervisar procesos de su unidad
- Aprobar o rechazar solicitudes
- Ver dashboard de unidad
- Gestionar asignaciones

**Para Secretarios:**
- Vista consolidada por secretaría
- Dashboard ejecutivo
- Reportes agregados
- Supervisión global

### 10.2 Módulo de Dashboard

#### 10.2.1 Personalización Básica

1. **Agregar Widget:**
   - Click en "+" en el dashboard
   - Seleccionar tipo (KPI/Chart/Table)
   - Configurar métrica y filtros
   - Posicionar con drag & drop

2. **Configurar Filtros:**
   - Seleccionar rango de fechas
   - Filtrar por secretaría/unidad
   - Aplicar filtros de estado
   - Guardar configuración personal

3. **Exportar Dashboard:**
   - Botón "Exportar" > Formato (PDF/Excel)
   - Incluir filtros aplicados
   - Programar envío automático

#### 10.2.2 Widgets Disponibles

**Métricas KPI:**
- Procesos en curso
- Procesos finalizados este mes
- Alertas de alta prioridad
- Contratos vigentes
- Valor total adjudicado

**Gráficos:**
- Procesos por área (barras/pie)
- Estados de procesos (doughnut)
- Contratos por mes (líneas)
- Distribución presupuestal (radar)

### 10.3 Módulo de Procesos

#### 10.3.1 Crear Nuevo Proceso

1. **Información básica:**
   - Nombre del proceso
   - Tipo (CD-PN, CD-PJ, LP, etc.)
   - Unidad ejecutora
   - Valor estimado

2. **Configuración:**
   - Flujo aplicable
   - Responsables por etapa
   - Fechas críticas
   - Documentos requeridos

3. **Iniciación:**
   - Validar información
   - Generar número de proceso
   - Notificar a responsables

#### 10.3.2 Seguimiento de Proceso

1. **Timeline visual:**
   - Estado actual claramente marcado
   - Etapas completadas (verde)
   - Etapas pendientes (gris)
   - Etapa actual (azul)

2. **Acciones disponibles:**
   - Subir documentos
   - Agregar comentarios
   - Solicitar revisión
   - Avanzar a siguiente etapa

### 10.4 Solución de Problemas Comunes

#### 10.4.1 Problemas de Acceso

**Error de login:**
- Verificar credenciales
- Comprobar estado de cuenta (activa/suspendida)
- Contactar administrador si persiste

**Permisos insuficientes:**
- Ver roles asignados en perfil
- Solicitar permisos adicionales
- Verificar asignación de unidad/secretaría

#### 10.4.2 Problemas del Dashboard

**Dashboard no carga:**
- Refrescar navegador (Ctrl+F5)
- Verificar conexión a internet
- Reportar si persiste el error

**Widgets sin datos:**
- Verificar filtros aplicados
- Comprobar permisos de acceso
- Revisar si hay procesos en el período

#### 10.4.3 Problemas con Documentos

**Error al subir archivo:**
- Verificar tamaño (<10MB)
- Formato permitido (PDF, DOC, XLS)
- Comprobar que no esté corrupto

### 10.5 Contacto y Soporte

**Mesa de Ayuda:**
- Email: soporte-sistema@gobernacion-caldas.gov.co
- Teléfono: +57 6 878-4400 ext. 1234
- Horario: Lunes a Viernes 8:00 AM - 6:00 PM

**Capacitación:**
- Talleres mensuales por rol
- Videos tutoriales en plataforma interna
- Documentación actualizada en wiki interno

---

## 11. NOTAS ARGUMENTATIVAS PARA REVISIÓN EN WORD

### Dashboard y Personalización

**Nota D01:** Se observa que el sistema de dashboard presenta limitaciones en la gestión dinámica de filtros temporales, lo cual impacta la capacidad analítica de los usuarios al no permitir comparativas período a período. Se recomienda implementar filtros de rango de fechas con opciones predefinidas (último mes, trimestre, año) para mejorar la utilidad de los widgets de tipo chart.

**Nota D02:** El motor de dashboard carece de funcionalidades de exportación y compartir configuraciones entre usuarios del mismo rol, limitando la estandarización de vistas estratégicas. Se sugiere incorporar templates predefinidos por rol y la capacidad de exportar dashboards a PDF o Excel con los datos filtrados aplicados.

**Nota D03:** La personalización individual del dashboard no contempla la capacidad de crear widgets personalizados o métricas calculadas, reduciendo el valor analítico para usuarios avanzados. Se recomienda desarrollar un constructor de métricas que permita fórmulas personalizadas basadas en los datos del sistema.

### Roles y Gestión de Usuarios

**Nota R01:** Se identifica que la estructura de roles es rígida y no contempla herencia dinámica o personalización granular por usuario dentro del mismo rol, limitando la flexibilidad organizacional. Se recomienda implementar un modelo híbrido que permita vistas por rol con ajustes específicos por usuario individual.

**Nota R02:** Actualmente no existen filtros dinámicos para la visualización de usuarios por rol, unidad o estado de actividad, lo que dificulta la administración del sistema especialmente en organizaciones grandes. Se sugiere incorporar filtros avanzados con segmentación por perfil y herramientas de búsqueda avanzada.

**Nota R03:** El sistema carece de auditoría completa para cambios de permisos y asignaciones de rol, presentando un riesgo de compliance y dificultando el troubleshooting de problemas de autorización. Se recomienda implementar un log detallado de cambios en permisos con timestamp y usuario responsable.

### UX/UI y Experiencia de Usuario

**Nota U01:** La interfaz actual presenta inconsistencias en el diseño responsive, particularmente en el motor de dashboard, lo cual limita significativamente el uso del sistema en dispositivos móviles y tablets. Se recomienda implementar un rediseño responsive completo con enfoque mobile-first para garantizar accesibilidad desde cualquier dispositivo.

**Nota U02:** Se observa ausencia de feedback visual inmediato en acciones críticas como guardado de configuraciones y carga de documentos, generando incertidumbre en los usuarios sobre el estado de sus acciones. Se sugiere implementar indicadores de progreso, notificaciones toast y confirmaciones visuales para mejorar la experiencia de usuario.

**Nota U03:** La navegación entre módulos carece de consistency y breadcrumbs claros, especialmente en flujos largos de configuración, lo que puede generar desorientación en usuarios novatos. Se recomienda estandarizar patrones de navegación y agregar breadcrumbs contextuales en todas las vistas.

### Flujos del Sistema y Procesos

**Nota F01:** El motor de flujos configurable no soporta condiciones lógicas (if/else) ni flujos paralelos, limitando la representación de procesos complejos reales de contratación que pueden tener ramificaciones. Se recomienda evolucionar el motor para soportar decisiones condicionales y tareas paralelas.

**Nota F02:** La validación de documentos por etapa es básica y no contempla validaciones automáticas de contenido o integridad de archivos, lo cual puede permitir el avance de procesos con documentación incompleta o incorrecta. Se sugiere implementar validaciones automáticas de metadata, checksums y contenido básico de documentos.

**Nota F03:** Los estados de proceso están predefinidos y no permiten estados personalizados por tipo de flujo, reduciendo la flexibilidad para representar particularidades de diferentes tipos de contratación. Se recomienda implementar estados configurables por flujo con reglas de transición personalizables.

### Datos de Prueba y Gestión de Información

**Nota T01:** Se identifica contaminación de datos entre ambiente de desarrollo y configuraciones de producción, particularmente en seeders que mezclan usuarios de prueba con configuraciones reales. Se recomienda implementar una estrategia de separación clara entre datos de prueba y datos de producción con flags identificatorios.

**Nota T02:** El sistema carece de políticas de retención de datos y archivado automático, lo cual puede resultar en degradación de performance conforme crece el volumen de procesos históricos. Se sugiere implementar un sistema de archivado automático para procesos finalizados con más de 2 años de antigüedad.

**Nota T03:** Los datos de métricas del dashboard no implementan cache inteligente, generando consultas repetitivas y costosas que impactan el performance especialmente con múltiples usuarios concurrentes. Se recomienda implementar una estrategia de cache en múltiples niveles con TTL apropiado por tipo de métrica.

### Arquitectura y Escalabilidad

**Nota A01:** Se observa concentración excesiva de responsabilidades en controladores principales, particularmente DashboardMotorController con más de 800 líneas, violando principios SOLID y dificultando el mantenimiento. Se recomienda refactorizar hacia una arquitectura de servicios con separación clara de responsabilidades.

**Nota A02:** La ausencia de un layer de cache estructurado y la dependencia directa de consultas de base de datos en tiempo real para widgets presenta riesgos de escalabilidad significativos. Se sugiere implementar Redis como cache primario con estrategias de invalidación inteligente basadas en eventos del sistema.

**Nota A03:** El sistema no implementa rate limiting ni throttling en endpoints críticos, presentando vulnerabilidades ante ataques de denegación de servicio y uso excesivo de recursos. Se recomienda implementar throttling diferenciado por rol y endpoint con límites apropiados según criticidad.

### Seguridad y Compliance

**Nota S01:** La validación de configuraciones JSON para widgets del dashboard es insuficiente y podría permitir inyección de código malicioso o configuraciones que comprometan la estabilidad del sistema. Se recomienda implementar validación estricta con schemas JSON y sanitización completa de inputs.

**Nota S02:** Los logs de auditoría no capturan suficiente contexto sobre acciones sensibles como modificación de roles o acceso a datos confidenciales, dificultando investigaciones de seguridad. Se sugiere enriquecer los logs con información de contexto incluyendo IP, user agent, y metadata de la sesión.

**Nota S03:** El sistema carece de mecanismos de detección de acceso anómalo o intentos de escalación de privilegios, presentando riesgos de seguridad no detectados. Se recomienda implementar monitoring de patrones de acceso con alertas automáticas ante comportamientos sospechosos.

---

**Documento generado por:** Arquitecto de Software Senior
**Fecha de elaboración:** Marzo 2026
**Versión:** 1.0
**Estado:** Listo para revisión en Word
**Páginas:** 35+

---

*Este documento contiene análisis técnico completo del Sistema de Seguimiento Contractual de la Gobernación de Caldas, incluyendo arquitectura actual, problemas identificados y recomendaciones de mejora. Las notas argumentativas incluidas están diseñadas para ser utilizadas como comentarios de revisión en documentos Word durante procesos de evaluation y aprobación.*