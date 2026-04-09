# PLAN DE PRUEBAS DEL SISTEMA
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
2. [Objetivos de Prueba](#2-objetivos-de-prueba)
3. [Alcance de las Pruebas](#3-alcance-de-las-pruebas)
4. [Tipos de Pruebas](#4-tipos-de-pruebas)
5. [Ambiente de Pruebas](#5-ambiente-de-pruebas)
6. [Casos de Prueba](#6-casos-de-prueba)
7. [Resultados de las Pruebas](#7-resultados-de-las-pruebas)
8. [Gestión de Errores](#8-gestión-de-errores)
9. [Criterios de Aceptación](#9-criterios-de-aceptación)
10. [Conclusiones](#10-conclusiones)
11. [Anexos](#11-anexos)

---

## 1. INTRODUCCIÓN

### 1.1 Propósito del Documento

Este documento define la estrategia, los tipos, el alcance y los casos de prueba para la validación del **Sistema de Seguimiento de Documentos Contractuales** de la Gobernación de Caldas. Su objetivo es garantizar que el sistema cumple con todos los requerimientos funcionales y no funcionales especificados antes de su puesta en producción.

### 1.2 Alcance del Plan de Pruebas

El presente plan cubre las pruebas de los seis módulos principales del sistema:
- Autenticación y seguridad de acceso.
- Gestión de procesos contractuales.
- Gestión documental.
- Motor de Flujos (etapas y transiciones CDPN).
- Motor de Dashboards.
- Generación y exportación de reportes.

### 1.3 Referencias

| Documento | Descripción |
|---|---|
| Documento de Requerimientos del Sistema | Define los RF y RNF que se validan |
| Documento Técnico del Sistema | Especifica la arquitectura técnica probada |
| FLUJO_REAL_9_ETAPAS.md | Descripción del flujo CDPN con 10 etapas (0-9) |
| Manual de Usuario del Sistema | Base para validación de usabilidad |

---

## 2. OBJETIVOS DE PRUEBA

### 2.1 Objetivo General

Verificar que el sistema cumple con los requerimientos funcionales y no funcionales definidos, garantizando su correcto funcionamiento, seguridad y usabilidad antes de la entrega formal a los usuarios de la Gobernación de Caldas.

### 2.2 Objetivos Específicos

1. Validar que los flujos de contratación implementados (especialmente CDPN) funcionan correctamente con sus 10 etapas, documentos y responsables.
2. Verificar que el control de acceso RBAC impide que usuarios no autorizados realicen acciones fuera de su rol.
3. Comprobar que la carga, validación y versionado de documentos funciona correctamente en todos los formatos soportados.
4. Validar que los dashboards muestran información correcta y diferenciada por rol.
5. Verificar que las notificaciones automáticas se envían correctamente ante los eventos definidos.
6. Comprobar el correcto funcionamiento de los reportes exportables en PDF y Excel.

### 2.3 Criterios de Éxito de la Campaña de Pruebas

- 100% de los casos de prueba ejecutados.
- 95% o más de los casos de prueba con resultado "Aprobado".
- 0 defectos de severidad Crítica o Alta sin resolver al momento de cierre.
- Los defectos de severidad Media deben tener plan de resolución documentado.

---

## 3. ALCANCE DE LAS PRUEBAS

### 3.1 Módulos Incluidos en las Pruebas

| Módulo | Incluido | Justificación |
|---|---|---|
| Autenticación (Login, logout, recuperación) | Sí | Crítico para el acceso al sistema |
| Gestión de Procesos Contractuales | Sí | Funcionalidad central del sistema |
| Gestión Documental (carga, validación, versiones) | Sí | Crítico para el flujo contractual |
| Motor de Flujos (CDPN 10 etapas) | Sí | Flujo base implementado |
| Motor de Dashboards | Sí | Indicadores de gestión para toma de decisiones |
| Reportes y Exportación | Sí | Rendición de cuentas y control interno |
| Administración de Usuarios y Roles | Sí | Soporte operativo del sistema |
| Integración SMTP Office 365 | Sí (parcial) | Verificación de envío de notificaciones |
| Integración API SECOP II | Sí (parcial) | Verificación de publicación básica |

### 3.2 Funcionalidades Críticas

Las siguientes funcionalidades se consideran críticas y requieren el 100% de aprobación:

1. Inicio de sesión y control de acceso por rol.
2. Creación de proceso contractual con flujo CDPN.
3. Avance de etapas con validación de checklist.
4. Carga y validación de documentos obligatorios.
5. Envío de notificaciones por correo ante avance de etapa.
6. Restricción de acceso a etapas según rol del usuario.

### 3.3 Exclusiones de las Pruebas

| Exclusión | Razón |
|---|---|
| Pruebas de carga con más de 200 usuarios simultáneos | Requiere ambiente especializado (JMeter + servidores dedicados) |
| Pruebas de penetración (pentesting) | Deben realizarse por equipo especializado de seguridad |
| Migración de datos históricos | El sistema opera solo con procesos nuevos |
| Integración completa SECOP II en ambiente de pruebas | API de SECOP no disponible en ambiente QA |
| Pruebas de compatibilidad con IE11 | Navegador fuera de soporte oficial del sistema |

---

## 4. TIPOS DE PRUEBAS

### 4.1 Pruebas Funcionales

Validan que cada funcionalidad del sistema se comporta según lo especificado en el Documento de Requerimientos.

- **Método:** Ejecución manual y automatizada de casos de prueba.
- **Herramientas:** Playwright (E2E), PHPUnit (unitarias y de integración), Cypress (E2E automatizado).
- **Cobertura esperada:** 100% de los requerimientos funcionales.

### 4.2 Pruebas de Integración

Validan la comunicación entre módulos internos del sistema y con servicios externos.

- **Integraciones a probar:**
  - Frontend React ↔ API REST Laravel.
  - Laravel ↔ MySQL (queries y transacciones).
  - Laravel ↔ SMTP Office 365 (envío de correos).
  - Laravel ↔ API SECOP II (publicación de contratos).
- **Método:** Pruebas de integración PHPUnit con base de datos de pruebas.

### 4.3 Pruebas de Usabilidad

Validan que la interfaz es fácil de usar por funcionarios no técnicos.

- **Método:** Sesiones de prueba con usuarios representativos de cada rol.
- **Criterios:** El usuario puede completar las tareas principales sin ayuda en menos de 5 minutos.
- **Escenarios:** Login, crear proceso, cargar documento, ver dashboard, generar reporte.

### 4.4 Pruebas de Seguridad

Validan que el sistema protege adecuadamente los datos y el acceso.

- **Pruebas incluidas:**
  - Acceso no autorizado a rutas protegidas (sin login).
  - Acceso de un rol a funcionalidades de otro rol.
  - Inyección SQL mediante formularios.
  - XSS (Cross-Site Scripting) en campos de texto.
  - Manipulación de tokens CSRF.
  - Fuerza bruta en el formulario de login.
  - Descarga directa de archivos sin autenticación.

### 4.5 Pruebas de Rendimiento

Validan que el sistema responde dentro de los tiempos aceptables.

- **Criterios de aceptación:**
  - Tiempo de carga del dashboard: < 3 segundos.
  - Tiempo de respuesta de la API: < 2 segundos (p95).
  - Carga de archivo de 10 MB: < 30 segundos.
  - Generación de reporte de 100 registros: < 5 segundos.
- **Herramienta:** Apache JMeter (pruebas de carga básica con 50 usuarios simultáneos).

---

## 5. AMBIENTE DE PRUEBAS

### 5.1 Ambiente de Pruebas QA

| Componente | Configuración |
|---|---|
| **Servidor** | Ubuntu 22.04 LTS — 4 vCPU, 8 GB RAM, 50 GB SSD |
| **Servidor Web** | Nginx 1.24 |
| **PHP** | 8.2.x con PHP-FPM |
| **Base de Datos** | MySQL 8.0 (base de datos: `sistema_contractual_qa`) |
| **Laravel** | 12.x en modo `APP_ENV=testing` |
| **Frontend** | Build de producción (`npm run build`) |
| **URL** | `https://qa.sistema-contractual.caldas.gov.co` |

### 5.2 Datos de Prueba

Para la ejecución de los casos de prueba se dispone de los siguientes datos:

**Usuarios de prueba por rol:**

| Usuario | Contraseña | Rol |
|---|---|---|
| `superadmin@caldas.gov.co` | `Test@1234` | super-admin |
| `admin@caldas.gov.co` | `Test@1234` | admin |
| `gobernador@caldas.gov.co` | `Test@1234` | gobernador |
| `secretario@caldas.gov.co` | `Test@1234` | secretario |
| `jefe.unidad@caldas.gov.co` | `Test@1234` | jefe-unidad |
| `profesional@caldas.gov.co` | `Test@1234` | profesional-senior |
| `abogado@caldas.gov.co` | `Test@1234` | abogado |
| `juridica@caldas.gov.co` | `Test@1234` | juridica |
| `planeacion@caldas.gov.co` | `Test@1234` | planeacion |
| `hacienda@caldas.gov.co` | `Test@1234` | hacienda |
| `secop@caldas.gov.co` | `Test@1234` | secop |

**Procesos de prueba:**
- 10 procesos CDPN en diferentes etapas.
- 3 procesos en estado "Anulado".
- 2 procesos con alertas de vencimiento activas.

### 5.3 Herramientas de Prueba

| Herramienta | Versión | Uso |
|---|---|---|
| **Playwright** | 1.x | Pruebas E2E automatizadas del frontend |
| **Cypress** | 13.7.0 | Pruebas E2E con 194 casos implementados |
| **PHPUnit** | 11.x | Pruebas unitarias y de integración backend |
| **Postman** | — | Pruebas manuales de endpoints API REST |
| **Apache JMeter** | 5.6 | Pruebas de rendimiento y carga |
| **Google Chrome DevTools** | — | Análisis de rendimiento frontend |

---

## 6. CASOS DE PRUEBA

### 6.1 Estructura de Casos de Prueba

Cada caso de prueba incluye:

| Campo | Descripción |
|---|---|
| **ID** | Identificador único (ej. CP-AUTH-01) |
| **Nombre** | Nombre descriptivo del caso |
| **Módulo** | Módulo al que pertenece |
| **Descripción** | Qué se está probando |
| **Precondiciones** | Estado requerido antes de ejecutar |
| **Pasos** | Acciones secuenciales a ejecutar |
| **Resultado Esperado** | Comportamiento correcto del sistema |
| **Resultado Obtenido** | Comportamiento real observado (se llena en ejecución) |
| **Estado** | Aprobado / Fallido / Bloqueado / No ejecutado |
| **Severidad (si falla)** | Crítica / Alta / Media / Baja |

### 6.2 Casos de Prueba por Módulo

---

#### MÓDULO: AUTENTICACIÓN (CP-AUTH)

---

**CP-AUTH-01 — Login exitoso con credenciales válidas**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que un usuario activo puede iniciar sesión correctamente |
| **Precondiciones** | Usuario `jefe.unidad@caldas.gov.co` existe y está activo en el sistema |
| **Pasos** | 1. Navegar a la URL del sistema. 2. Ingresar correo `jefe.unidad@caldas.gov.co`. 3. Ingresar contraseña `Test@1234`. 4. Clic en "Ingresar". |
| **Resultado Esperado** | El sistema redirige al dashboard del Jefe de Unidad. El menú lateral muestra las secciones correspondientes al rol. |
| **Estado** | No ejecutado |

---

**CP-AUTH-02 — Login fallido con contraseña incorrecta**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que el sistema rechaza credenciales inválidas |
| **Precondiciones** | Usuario activo registrado en el sistema |
| **Pasos** | 1. Navegar a la URL del sistema. 2. Ingresar correo válido. 3. Ingresar contraseña incorrecta `Password123`. 4. Clic en "Ingresar". |
| **Resultado Esperado** | El sistema muestra mensaje de error "Credenciales inválidas". No redirige al dashboard. El intento queda registrado en `auth_events`. |
| **Estado** | No ejecutado |

---

**CP-AUTH-03 — Bloqueo de cuenta tras 5 intentos fallidos**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que el sistema bloquea la cuenta tras 5 intentos consecutivos fallidos |
| **Precondiciones** | Usuario activo. 4 intentos fallidos previos registrados. |
| **Pasos** | 1. Intentar login con contraseña incorrecta por 5.a vez consecutiva. |
| **Resultado Esperado** | El sistema muestra mensaje de cuenta bloqueada temporalmente. El intento N.° 5 queda registrado. No se puede iniciar sesión hasta desbloqueo. |
| **Estado** | No ejecutado |

---

**CP-AUTH-04 — Cierre de sesión manual**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que el usuario puede cerrar sesión correctamente |
| **Precondiciones** | Usuario autenticado en el sistema |
| **Pasos** | 1. Hacer clic en el nombre del usuario (esquina superior derecha). 2. Seleccionar "Cerrar sesión". |
| **Resultado Esperado** | El sistema redirige a la pantalla de login. La sesión es invalidada en el servidor. El intento de acceder a una URL protegida redirige al login. |
| **Estado** | No ejecutado |

---

**CP-AUTH-05 — Recuperación de contraseña por correo**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar el flujo completo de recuperación de contraseña |
| **Precondiciones** | Usuario activo con correo válido. Servidor SMTP configurado. |
| **Pasos** | 1. Clic en "¿Olvidó su contraseña?". 2. Ingresar correo institucional del usuario. 3. Clic en "Enviar enlace". 4. Revisar correo recibido. 5. Clic en el enlace. 6. Ingresar nueva contraseña válida. 7. Confirmar nueva contraseña. 8. Clic en "Restablecer". |
| **Resultado Esperado** | El correo llega en menos de 2 minutos. El enlace funciona. La contraseña se restablece y el usuario puede iniciar sesión con la nueva contraseña. |
| **Estado** | No ejecutado |

---

**CP-AUTH-06 — Acceso con usuario inactivo**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que usuarios desactivados no pueden iniciar sesión |
| **Precondiciones** | Usuario desactivado por el Administrador |
| **Pasos** | 1. Ingresar credenciales válidas del usuario desactivado. 2. Clic en "Ingresar". |
| **Resultado Esperado** | El sistema muestra mensaje "Su cuenta está inactiva. Contacte al administrador". No permite el acceso. |
| **Estado** | No ejecutado |

---

**CP-AUTH-07 — Expiración de sesión por inactividad**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que la sesión expira automáticamente tras el tiempo de inactividad configurado |
| **Precondiciones** | Tiempo de inactividad configurado en 2 minutos (para prueba). Usuario autenticado. |
| **Pasos** | 1. Autenticarse en el sistema. 2. No realizar ninguna acción durante 2 minutos. 3. Intentar navegar a cualquier página del sistema. |
| **Resultado Esperado** | El sistema redirige al login con mensaje "Su sesión ha expirado por inactividad". |
| **Estado** | No ejecutado |

---

**CP-AUTH-08 — Restricción de acceso a ruta protegida sin autenticación**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación / Seguridad |
| **Descripción** | Verificar que las rutas protegidas no son accesibles sin autenticación |
| **Precondiciones** | Ninguna sesión activa |
| **Pasos** | 1. Escribir directamente en el navegador una URL protegida (ej. `/procesos`). |
| **Resultado Esperado** | El sistema redirige automáticamente a la pantalla de login. |
| **Estado** | No ejecutado |

---

**CP-AUTH-09 — Restricción de acceso entre roles**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación / Seguridad |
| **Descripción** | Verificar que un usuario no puede acceder a módulos de otro rol |
| **Precondiciones** | Usuario autenticado con rol `secop`. |
| **Pasos** | 1. Autenticarse con usuario de rol `secop`. 2. Intentar navegar a `/admin/users` (solo para admin). |
| **Resultado Esperado** | El sistema muestra página de error 403 "No tiene permisos para realizar esta acción". |
| **Estado** | No ejecutado |

---

**CP-AUTH-10 — Expiración de enlace de recuperación de contraseña**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que el enlace de recuperación expira tras 60 minutos |
| **Precondiciones** | Enlace de recuperación generado hace más de 60 minutos |
| **Pasos** | 1. Acceder al enlace de recuperación vencido. |
| **Resultado Esperado** | El sistema muestra mensaje "El enlace de recuperación ha expirado. Solicite uno nuevo." |
| **Estado** | No ejecutado |

---

**CP-AUTH-11 — Cambio de contraseña desde perfil de usuario**

| Campo | Detalle |
|---|---|
| **Módulo** | Autenticación |
| **Descripción** | Verificar que el usuario puede cambiar su contraseña desde su perfil |
| **Precondiciones** | Usuario autenticado |
| **Pasos** | 1. Ir a "Mi Perfil". 2. Ingresar contraseña actual. 3. Ingresar nueva contraseña válida. 4. Confirmar nueva contraseña. 5. Clic en "Cambiar contraseña". |
| **Resultado Esperado** | Contraseña actualizada exitosamente. El sistema muestra mensaje de confirmación. La sesión continúa activa. |
| **Estado** | No ejecutado |

---

#### MÓDULO: PROCESOS CONTRACTUALES (CP-PROC)

---

**CP-PROC-01 — Crear proceso CDPN exitosamente**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que se puede crear un proceso de Contratación Directa Persona Natural |
| **Precondiciones** | Usuario con rol `jefe-unidad` autenticado. Flujo CDPN publicado. |
| **Pasos** | 1. Ir a "Procesos". 2. Clic en "Nuevo Proceso". 3. Seleccionar "Contratación Directa Persona Natural". 4. Llenar: nombre del proceso, contratista, cédula, valor del contrato, fechas, secretaría, unidad, objeto. 5. Clic en "Crear Proceso". |
| **Resultado Esperado** | Proceso creado con número asignado automáticamente (ej. CD-SP-001-2026). Estado "En Progreso". Posicionado en Etapa 0. |
| **Estado** | No ejecutado |

---

**CP-PROC-02 — Validación de campos obligatorios en creación de proceso**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que el sistema valida los campos obligatorios al crear un proceso |
| **Precondiciones** | Usuario con permiso de crear procesos autenticado |
| **Pasos** | 1. Ir a "Nuevo Proceso". 2. Dejar en blanco: nombre del contratista, valor del contrato. 3. Clic en "Crear Proceso". |
| **Resultado Esperado** | El sistema muestra mensajes de error junto a cada campo vacío obligatorio. No crea el proceso. |
| **Estado** | No ejecutado |

---

**CP-PROC-03 — Avanzar etapa con checklist completo**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que el proceso avanza de etapa cuando todos los documentos obligatorios están aprobados |
| **Precondiciones** | Proceso en Etapa 0. Documento "Estudios Previos" cargado y aprobado. Usuario responsable de la etapa autenticado. |
| **Pasos** | 1. Abrir el proceso. 2. Verificar el checklist de la Etapa 0 completo. 3. Clic en "Avanzar a siguiente etapa". 4. Confirmar la acción. |
| **Resultado Esperado** | Etapa 0 marcada como "Completada". Etapa 1 marcada como activa. Notificación enviada al responsable de la Etapa 1 (planeación). Registro en auditoría. |
| **Estado** | No ejecutado |

---

**CP-PROC-04 — Bloqueo de avance con documentos pendientes**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que el sistema bloquea el avance si hay documentos obligatorios sin aprobar |
| **Precondiciones** | Proceso en Etapa 0. Sin documentos cargados. |
| **Pasos** | 1. Abrir el proceso en Etapa 0. 2. Intentar clic en "Avanzar a siguiente etapa" sin cargar Estudios Previos. |
| **Resultado Esperado** | El botón "Avanzar" está deshabilitado o el sistema muestra mensaje "Debe completar todos los documentos obligatorios antes de avanzar". Lista de documentos faltantes visible. |
| **Estado** | No ejecutado |

---

**CP-PROC-05 — Devolución de proceso con comentario**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que un proceso puede ser devuelto a una etapa anterior con justificación |
| **Precondiciones** | Proceso en Etapa 5 (Jurídica). Usuario con rol `juridica` autenticado. |
| **Pasos** | 1. Abrir el proceso en Etapa 5. 2. Clic en "Devolver proceso". 3. Seleccionar la etapa de destino (ej. Etapa 3). 4. Escribir comentario: "Faltan firmas en el contrato de invitación". 5. Confirmar devolución. |
| **Resultado Esperado** | El proceso vuelve a la Etapa 3. Estado "Devuelta". Comentario registrado en el historial. Notificación enviada al responsable de la Etapa 3. Registro en auditoría con dato anterior y nuevo. |
| **Estado** | No ejecutado |

---

**CP-PROC-06 — Búsqueda y filtrado de procesos**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que los filtros de búsqueda de procesos funcionan correctamente |
| **Precondiciones** | Al menos 5 procesos en el sistema con diferentes estados y secretarías |
| **Pasos** | 1. Ir a "Procesos". 2. Filtrar por estado "En Progreso". 3. Verificar resultados. 4. Agregar filtro por secretaría. 5. Verificar resultados combinados. |
| **Resultado Esperado** | La lista muestra únicamente los procesos que cumplen los criterios de filtro seleccionados. |
| **Estado** | No ejecutado |

---

**CP-PROC-07 — Anular un proceso con justificación**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que un proceso puede ser anulado y no puede reactivarse |
| **Precondiciones** | Proceso activo. Usuario con permiso de anular procesos. |
| **Pasos** | 1. Abrir el proceso. 2. Clic en "Anular proceso". 3. Escribir justificación. 4. Confirmar anulación. 5. Intentar reactivar el proceso. |
| **Resultado Esperado** | Proceso en estado "Anulado". No aparece el botón de reactivar. Mensaje "Este proceso ha sido anulado y no puede ser reactivado". |
| **Estado** | No ejecutado |

---

**CP-PROC-08 — Restricción de acceso a procesos de otra secretaría**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos / Seguridad |
| **Descripción** | Verificar que un Jefe de Unidad no puede ver procesos de otra secretaría |
| **Precondiciones** | Usuario rol `jefe-unidad` de Secretaría A. Proceso existente en Secretaría B. |
| **Pasos** | 1. Autenticarse con el usuario del Jefe de Unidad de Secretaría A. 2. Intentar acceder directamente a la URL del proceso de Secretaría B. |
| **Resultado Esperado** | El sistema muestra error 403 o 404. El proceso de otra secretaría no aparece en la lista. |
| **Estado** | No ejecutado |

---

**CP-PROC-09 — Historial de cambios de un proceso**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que el historial de cambios registra correctamente todas las acciones |
| **Precondiciones** | Proceso con al menos 2 avances de etapa registrados |
| **Pasos** | 1. Abrir el proceso. 2. Ir a la pestaña "Historial" o "Auditoría". 3. Revisar los registros listados. |
| **Resultado Esperado** | Se muestran todos los cambios con: usuario, acción, etapa anterior, etapa nueva, fecha y hora. Los registros son de solo lectura. |
| **Estado** | No ejecutado |

---

**CP-PROC-10 — Cálculo de días en etapa**

| Campo | Detalle |
|---|---|
| **Módulo** | Procesos |
| **Descripción** | Verificar que el sistema calcula correctamente los días transcurridos en la etapa activa |
| **Precondiciones** | Proceso en Etapa 1 con 3 días de antigüedad (tiempo estimado: 5 días) |
| **Pasos** | 1. Abrir el proceso. 2. Ver la información de la etapa activa. |
| **Resultado Esperado** | El sistema muestra "3 días / 5 días estimados" o similar indicador de progreso de tiempo. Sin alerta de vencimiento ya que no ha superado el estimado. |
| **Estado** | No ejecutado |

---

#### MÓDULO: GESTIÓN DOCUMENTAL (CP-DOC)

---

**CP-DOC-01 — Carga de documento PDF en etapa activa**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que se puede cargar un documento PDF en la etapa activa |
| **Precondiciones** | Proceso en Etapa 0. Usuario responsable de la etapa autenticado. |
| **Pasos** | 1. Abrir el proceso. 2. En la Etapa 0, clic en "Cargar" junto a "Estudios Previos". 3. Seleccionar archivo `estudios_previos.pdf` (< 10 MB). 4. Clic en "Confirmar carga". |
| **Resultado Esperado** | Documento cargado en estado "Pendiente de Validación". Se muestra nombre, fecha y usuario que lo cargó. Se genera entrada en auditoría. |
| **Estado** | No ejecutado |

---

**CP-DOC-02 — Rechazo de archivo con extensión no permitida**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que el sistema rechaza archivos con extensiones no permitidas |
| **Precondiciones** | Proceso en etapa activa. Usuario responsable autenticado. |
| **Pasos** | 1. Intentar cargar un archivo `.exe` como documento. |
| **Resultado Esperado** | El sistema muestra error "Formato de archivo no permitido. Use: PDF, Word, Excel, JPG, PNG". El archivo no se almacena. |
| **Estado** | No ejecutado |

---

**CP-DOC-03 — Rechazo de archivo mayor a 10 MB**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que el sistema rechaza archivos que superan el tamaño máximo |
| **Precondiciones** | Proceso en etapa activa. Archivo de prueba de 15 MB. |
| **Pasos** | 1. Intentar cargar un archivo de 15 MB. |
| **Resultado Esperado** | El sistema muestra error "El archivo supera el tamaño máximo permitido de 10 MB". El archivo no se almacena. |
| **Estado** | No ejecutado |

---

**CP-DOC-04 — Validación (aprobación) de un documento**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que un documento puede ser aprobado por el validador |
| **Precondiciones** | Documento en estado "Pendiente de Validación". Usuario con permiso de validar documentos autenticado. |
| **Pasos** | 1. Abrir el proceso. 2. Ir a la lista de documentos. 3. Clic en el documento pendiente. 4. Revisar el documento. 5. Clic en "Aprobar". 6. Confirmar. |
| **Resultado Esperado** | Documento en estado "Aprobado". Se muestra el usuario que aprobó y la fecha. Notificación enviada al usuario que cargó el documento. Registro en auditoría. |
| **Estado** | No ejecutado |

---

**CP-DOC-05 — Rechazo de documento con comentario obligatorio**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que el rechazo de un documento requiere comentario explicativo |
| **Precondiciones** | Documento en estado "Pendiente". Usuario validador autenticado. |
| **Pasos** | 1. Abrir documento pendiente. 2. Clic en "Rechazar". 3. Intentar confirmar sin escribir comentario. |
| **Resultado Esperado** | El sistema muestra error "Debe ingresar el motivo del rechazo". No permite rechazar sin comentario. |
| **Estado** | No ejecutado |

---

**CP-DOC-06 — Carga de nueva versión de documento rechazado**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar el versionado de documentos rechazados |
| **Precondiciones** | Documento en estado "Rechazado". Usuario que cargó el documento autenticado. |
| **Pasos** | 1. Ir al documento rechazado. 2. Clic en "Cargar nueva versión". 3. Seleccionar el archivo corregido. 4. Confirmar. |
| **Resultado Esperado** | Nueva versión (v2) cargada en estado "Pendiente". La versión anterior (v1 Rechazada) se conserva en el historial. |
| **Estado** | No ejecutado |

---

**CP-DOC-07 — Descarga de documento desde el sistema**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que los documentos pueden descargarse correctamente |
| **Precondiciones** | Documento aprobado en el proceso. Usuario con acceso al proceso autenticado. |
| **Pasos** | 1. Abrir el proceso. 2. Ir a la lista de documentos. 3. Clic en el ícono de descarga del documento. |
| **Resultado Esperado** | El archivo se descarga al computador del usuario con el nombre y formato originales. |
| **Estado** | No ejecutado |

---

**CP-DOC-08 — Restricción de carga en etapa no activa**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental / Seguridad |
| **Descripción** | Verificar que no se pueden cargar documentos en etapas que no son la activa |
| **Precondiciones** | Proceso en Etapa 3. Usuario autenticado. |
| **Pasos** | 1. Abrir el proceso. 2. Intentar acceder a la Etapa 1 (ya completada) y cargar un nuevo documento. |
| **Resultado Esperado** | El botón "Cargar" está deshabilitado en etapas completadas. Mensaje "No puede modificar etapas ya completadas". |
| **Estado** | No ejecutado |

---

**CP-DOC-09 — Historial de versiones de un documento**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que el historial de versiones de un documento es correcto |
| **Precondiciones** | Documento con 2 versiones (v1 rechazada, v2 aprobada) |
| **Pasos** | 1. Abrir el documento. 2. Ir a la sección "Versiones". |
| **Resultado Esperado** | Se muestran ambas versiones: v1 (Rechazada, fecha, usuario), v2 (Aprobada, fecha, usuario). Las dos versiones son descargables. |
| **Estado** | No ejecutado |

---

**CP-DOC-10 — Documento obligatorio impide avance de etapa**

| Campo | Detalle |
|---|---|
| **Módulo** | Gestión Documental |
| **Descripción** | Verificar que la etapa no avanza si hay documentos obligatorios sin aprobar |
| **Precondiciones** | Proceso en Etapa 1 con CDP en estado "Pendiente" (sin aprobar) |
| **Pasos** | 1. Con el CDP en "Pendiente", intentar avanzar la Etapa 1. |
| **Resultado Esperado** | El sistema bloquea el avance. Mensaje: "El documento 'CDP' es obligatorio y debe estar aprobado para avanzar". |
| **Estado** | No ejecutado |

---

#### MÓDULO: FLUJO DEL PROCESO CDPN (CP-FLJ)

---

**CP-FLJ-01 — Flujo completo CDPN: Etapa 0 → Etapa 1**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos |
| **Descripción** | Verificar la transición de la Etapa 0 a la Etapa 1 del flujo CDPN |
| **Precondiciones** | Proceso CDPN en Etapa 0. Estudios Previos cargados y aprobados. |
| **Pasos** | 1. Verificar checklist de Etapa 0 completo. 2. Clic en "Avanzar". 3. Confirmar. |
| **Resultado Esperado** | Etapa 0 = Completada. Etapa 1 = En Progreso. Notificación enviada al responsable de Planeación. Historial de transición registrado. |
| **Estado** | No ejecutado |

---

**CP-FLJ-02 — Carga de CDP requiere Compatibilidad del Gasto aprobada (Etapa 1)**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos |
| **Descripción** | Verificar la dependencia entre documentos en Etapa 1: CDP requiere Compatibilidad del Gasto |
| **Precondiciones** | Proceso en Etapa 1. Compatibilidad del Gasto NO aprobada. |
| **Pasos** | 1. En la Etapa 1, intentar cargar el documento CDP sin que Compatibilidad del Gasto esté aprobada. |
| **Resultado Esperado** | El sistema bloquea la carga del CDP o muestra advertencia: "El CDP requiere que la Compatibilidad del Gasto esté aprobada previamente". |
| **Estado** | No ejecutado |

---

**CP-FLJ-03 — Etapa paralela Etapa 1: múltiples documentos simultáneos**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos |
| **Descripción** | Verificar que en Etapa 1 se pueden gestionar múltiples documentos en paralelo |
| **Precondiciones** | Proceso en Etapa 1 |
| **Pasos** | 1. Cargar PAA, No Planta, Paz y Salvo Rentas y Compatibilidad del Gasto al mismo tiempo (en sesiones diferentes o secuencialmente). |
| **Resultado Esperado** | El sistema permite cargar múltiples documentos en paralelo en la Etapa 1 sin conflictos. Cada documento tiene su estado independiente. |
| **Estado** | No ejecutado |

---

**CP-FLJ-04 — Restricción de rol en etapa: solo Jurídica actúa en Etapa 5**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos / Seguridad |
| **Descripción** | Verificar que solo el rol Jurídica puede actuar sobre la Etapa 5 |
| **Precondiciones** | Proceso en Etapa 5. Usuario con rol `jefe-unidad` autenticado (no es el responsable de la etapa). |
| **Pasos** | 1. Abrir el proceso en Etapa 5. 2. Verificar que los botones de carga de documentos y avance están deshabilitados para el Jefe de Unidad. |
| **Resultado Esperado** | El usuario puede ver la Etapa 5 pero no puede cargar documentos ni avanzar. Mensaje: "Solo el responsable de esta etapa puede realizar acciones". |
| **Estado** | No ejecutado |

---

**CP-FLJ-05 — Firma en SECOP II: contratista firma antes que Secretario (Etapa 6)**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos |
| **Descripción** | Verificar la regla de orden de firmas en Etapa 6 |
| **Precondiciones** | Proceso en Etapa 6. Usuario con rol `secop`. |
| **Pasos** | 1. Intentar cargar `firma_secretario` sin que `firma_contratista` esté aprobada. |
| **Resultado Esperado** | El sistema bloquea la carga de la firma del Secretario. Mensaje: "El contratista debe firmar primero en SECOP II". |
| **Estado** | No ejecutado |

---

**CP-FLJ-06 — RPC como prerrequisito para Etapa 8**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos |
| **Descripción** | Verificar que la Etapa 8 no puede iniciarse sin el RPC expedido |
| **Precondiciones** | Proceso en Etapa 7. RPC no cargado/aprobado. |
| **Pasos** | 1. Intentar avanzar la Etapa 7 sin el documento `rpc_expedido` aprobado. |
| **Resultado Esperado** | El sistema bloquea el avance. Mensaje: "El RPC es prerrequisito para continuar a la Etapa 8". |
| **Estado** | No ejecutado |

---

**CP-FLJ-07 — Flujo completo CDPN: Etapa 9 (ARL, Acta de Inicio)**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos |
| **Descripción** | Verificar que el proceso llega al estado final "Contrato Iniciado" |
| **Precondiciones** | Proceso en Etapa 9. Documentos: `solicitud_arl`, `acta_inicio`, `registro_secop_inicio` cargados y aprobados. |
| **Pasos** | 1. Verificar checklist de Etapa 9 completo. 2. Clic en "Finalizar proceso". 3. Confirmar. |
| **Resultado Esperado** | Proceso en estado "Contrato Iniciado" (Completado). Todas las etapas en estado "Completada". Registro en auditoría del cierre del proceso. |
| **Estado** | No ejecutado |

---

**CP-FLJ-08 — Vista visual del flujo en el proceso**

| Campo | Detalle |
|---|---|
| **Módulo** | Motor de Flujos |
| **Descripción** | Verificar que la vista visual del flujo muestra correctamente el estado de las etapas |
| **Precondiciones** | Proceso CDPN en Etapa 4 con etapas 0-3 completadas |
| **Pasos** | 1. Abrir el proceso. 2. Ir a la pestaña "Flujo". |
| **Resultado Esperado** | Etapas 0-3 en verde (completadas). Etapa 4 en azul/amarillo (activa). Etapas 5-9 en gris (pendientes). Responsable de la etapa 4 visible. |
| **Estado** | No ejecutado |

---

#### MÓDULO: DASHBOARD (CP-DASH)

---

**CP-DASH-01 — Dashboard del Gobernador muestra datos globales**

| Campo | Detalle |
|---|---|
| **Módulo** | Dashboard |
| **Descripción** | Verificar que el dashboard del Gobernador muestra indicadores de toda la Gobernación |
| **Precondiciones** | Usuario con rol `gobernador` autenticado. Al menos 5 procesos en diferentes secretarías. |
| **Pasos** | 1. Autenticarse como Gobernador. 2. Observar el dashboard de inicio. |
| **Resultado Esperado** | Se muestran KPIs globales: total procesos activos (suma de todas las secretarías), procesos completados, con alertas. Las gráficas muestran datos de todas las secretarías. |
| **Estado** | No ejecutado |

---

**CP-DASH-02 — Dashboard del Secretario muestra solo su secretaría**

| Campo | Detalle |
|---|---|
| **Módulo** | Dashboard |
| **Descripción** | Verificar que el Secretario solo ve datos de su secretaría |
| **Precondiciones** | Usuario rol `secretario` de Secretaría de Planeación autenticado |
| **Pasos** | 1. Autenticarse como Secretario de Planeación. 2. Observar el dashboard. |
| **Resultado Esperado** | Solo aparecen procesos y datos de la Secretaría de Planeación. No aparecen datos de otras secretarías. |
| **Estado** | No ejecutado |

---

**CP-DASH-03 — Widgets KPI muestran valores correctos**

| Campo | Detalle |
|---|---|
| **Módulo** | Dashboard |
| **Descripción** | Verificar que los KPIs muestran los valores correctos según los datos de la BD |
| **Precondiciones** | 10 procesos activos, 3 completados, 2 con alertas registrados en BD |
| **Pasos** | 1. Verificar el KPI "Procesos Activos". 2. Verificar el KPI "Procesos Completados". 3. Verificar el KPI "Con Alertas". |
| **Resultado Esperado** | KPI Activos = 10. KPI Completados = 3. KPI Con Alertas = 2. Los valores coinciden con los datos en la base de datos. |
| **Estado** | No ejecutado |

---

**CP-DASH-04 — Filtro de período en dashboard**

| Campo | Detalle |
|---|---|
| **Módulo** | Dashboard |
| **Descripción** | Verificar que el filtro de período actualiza correctamente los datos del dashboard |
| **Precondiciones** | Procesos distribuidos en diferentes meses. |
| **Pasos** | 1. En el dashboard, cambiar el filtro de período de "Este mes" a "Este año". 2. Observar el cambio en las gráficas y KPIs. |
| **Resultado Esperado** | Los KPIs y gráficas se actualizan para mostrar datos del año completo. Los valores aumentan (o cambian) coherentemente. |
| **Estado** | No ejecutado |

---

**CP-DASH-05 — Gráfica de barras muestra distribución por estado**

| Campo | Detalle |
|---|---|
| **Módulo** | Dashboard |
| **Descripción** | Verificar la correcta representación gráfica de los procesos por estado |
| **Precondiciones** | Procesos en diferentes estados registrados |
| **Pasos** | 1. Observar la gráfica de barras de "Procesos por Estado". 2. Pasar el cursor sobre cada barra. |
| **Resultado Esperado** | Cada barra representa un estado diferente. El valor al pasar el cursor coincide con el conteo real de procesos en ese estado. |
| **Estado** | No ejecutado |

---

**CP-DASH-06 — Configurar widget en el dashboard builder**

| Campo | Detalle |
|---|---|
| **Módulo** | Dashboard |
| **Descripción** | Verificar que el Administrador puede agregar un nuevo widget al dashboard |
| **Precondiciones** | Usuario admin autenticado. Plantilla de dashboard existente. |
| **Pasos** | 1. Ir al Dashboard Builder. 2. Seleccionar una plantilla. 3. Arrastrar un widget KPI al layout. 4. Configurar métrica y etiqueta. 5. Guardar. |
| **Resultado Esperado** | El nuevo widget aparece en el dashboard de los roles asignados a esa plantilla. Muestra la métrica configurada con datos reales. |
| **Estado** | No ejecutado |

---

#### MÓDULO: REPORTES (CP-REP)

---

**CP-REP-01 — Generar reporte de procesos con filtros**

| Campo | Detalle |
|---|---|
| **Módulo** | Reportes |
| **Descripción** | Verificar que el reporte de procesos se genera correctamente con filtros aplicados |
| **Precondiciones** | Al menos 5 procesos activos en el sistema |
| **Pasos** | 1. Ir a "Reportes". 2. Seleccionar "Reporte de Procesos". 3. Filtrar por estado "En Progreso" y secretaría específica. 4. Clic en "Generar Reporte". |
| **Resultado Esperado** | El reporte muestra solo los procesos que cumplen los filtros. Columnas: número, contratista, estado, etapa, secretaría, fecha inicio, días transcurridos. |
| **Estado** | No ejecutado |

---

**CP-REP-02 — Exportar reporte en PDF**

| Campo | Detalle |
|---|---|
| **Módulo** | Reportes |
| **Descripción** | Verificar que la exportación a PDF genera un archivo válido |
| **Precondiciones** | Reporte generado en pantalla |
| **Pasos** | 1. Generar cualquier reporte. 2. Clic en "Exportar PDF". |
| **Resultado Esperado** | Archivo PDF descargado con el nombre del reporte y fecha. Contenido idéntico al mostrado en pantalla. Formato legible y con encabezado de la Gobernación. |
| **Estado** | No ejecutado |

---

**CP-REP-03 — Exportar reporte en Excel**

| Campo | Detalle |
|---|---|
| **Módulo** | Reportes |
| **Descripción** | Verificar que la exportación a Excel genera un archivo válido con datos íntegros |
| **Precondiciones** | Reporte generado en pantalla |
| **Pasos** | 1. Generar reporte de procesos. 2. Clic en "Exportar Excel". 3. Abrir el archivo descargado en Excel. |
| **Resultado Esperado** | Archivo .xlsx descargado. Datos organizados en columnas con encabezados. Todos los registros del reporte presentes. |
| **Estado** | No ejecutado |

---

**CP-REP-04 — Reporte de auditoría con filtro por usuario**

| Campo | Detalle |
|---|---|
| **Módulo** | Reportes |
| **Descripción** | Verificar que el reporte de auditoría filtra correctamente por usuario |
| **Precondiciones** | Log de auditoría con registros de múltiples usuarios |
| **Pasos** | 1. Ir a "Reportes" → "Reporte de Auditoría". 2. Filtrar por usuario específico. 3. Filtrar por rango de fechas de la última semana. 4. Generar reporte. |
| **Resultado Esperado** | Solo aparecen registros del usuario seleccionado dentro del rango de fechas. Columnas: usuario, acción, módulo, dato anterior, dato nuevo, IP, fecha y hora. |
| **Estado** | No ejecutado |

---

**CP-REP-05 — Reporte de documentos pendientes**

| Campo | Detalle |
|---|---|
| **Módulo** | Reportes |
| **Descripción** | Verificar que el reporte de documentos pendientes es preciso |
| **Precondiciones** | Procesos con documentos en estado "Pendiente" y documentos obligatorios sin cargar |
| **Pasos** | 1. Ir a "Reportes" → "Documentos Pendientes". 2. Generar reporte sin filtros. |
| **Resultado Esperado** | Lista todos los documentos pendientes de validación y los documentos obligatorios no cargados, por proceso y etapa. Datos coinciden con el estado real de la BD. |
| **Estado** | No ejecutado |

---

## 7. RESULTADOS DE LAS PRUEBAS

### 7.1 Registro de Ejecución

| ID Caso | Nombre | Fecha Ejecución | Ejecutado por | Estado | Observaciones |
|---|---|---|---|---|---|
| CP-AUTH-01 | Login exitoso | — | — | No ejecutado | — |
| CP-AUTH-02 | Login fallido | — | — | No ejecutado | — |
| *[... continúa para todos los casos]* | | | | | |

### 7.2 Resumen de Resultados

| Módulo | Total Casos | Aprobados | Fallidos | Bloqueados | % Aprobación |
|---|---|---|---|---|---|
| Autenticación | 11 | — | — | — | — |
| Procesos | 10 | — | — | — | — |
| Gestión Documental | 10 | — | — | — | — |
| Flujo CDPN | 8 | — | — | — | — |
| Dashboard | 6 | — | — | — | — |
| Reportes | 5 | — | — | — | — |
| **TOTAL** | **50** | **—** | **—** | **—** | **—** |

### 7.3 Defectos Encontrados

| ID Defecto | Módulo | Descripción | Severidad | Estado |
|---|---|---|---|---|
| DEF-001 | — | — | — | — |
| *[Se completa durante la ejecución]* | | | | |

### 7.4 Métricas de Calidad

| Métrica | Valor Esperado | Valor Obtenido |
|---|---|---|
| % Casos aprobados | ≥ 95% | — |
| Defectos Críticos sin resolver | 0 | — |
| Defectos Altos sin resolver | 0 | — |
| Tiempo promedio de respuesta API | < 2 seg | — |
| Tiempo de carga de dashboard | < 3 seg | — |

---

## 8. GESTIÓN DE ERRORES

### 8.1 Clasificación de Severidad de Defectos

| Severidad | Descripción | Tiempo de Resolución |
|---|---|---|
| **Crítica** | El sistema no funciona, pérdida de datos, brecha de seguridad grave. Bloquea la operación. | Inmediato (< 4 horas) |
| **Alta** | Funcionalidad principal no funciona correctamente. Afecta a múltiples usuarios. | 24 horas hábiles |
| **Media** | Funcionalidad secundaria no funciona. Existe solución alternativa. | 72 horas hábiles |
| **Baja** | Problema estético, texto incorrecto, comportamiento menor. No afecta la operación. | En el próximo ciclo de mantenimiento |

### 8.2 Proceso de Reporte de Defectos

1. El tester identifica un comportamiento inesperado.
2. Documenta el defecto con: ID, módulo, descripción, pasos para reproducir, resultado esperado, resultado obtenido, captura de pantalla, severidad.
3. Reporta el defecto en la herramienta de gestión (GitHub Issues, Jira o equivalente).
4. El líder técnico revisa y asigna al desarrollador.
5. El desarrollador corrige y marca como "Resuelto".
6. El tester verifica la corrección y marca como "Cerrado".

### 8.3 Criterios de Reapertura de Defecto

Un defecto se reabre si:
- La corrección no resolvió el problema original.
- La corrección generó un nuevo defecto (regresión).
- El comportamiento corregido no coincide con el resultado esperado del caso de prueba.

---

## 9. CRITERIOS DE ACEPTACIÓN

### 9.1 Criterios de Aceptación Funcionales

El sistema es aceptado formalmente si:

- [ ] El 100% de los casos de prueba de los módulos de Autenticación y Flujo CDPN están en estado "Aprobado".
- [ ] El 95% o más de todos los casos de prueba están en estado "Aprobado".
- [ ] No existen defectos de severidad Crítica o Alta sin resolver.
- [ ] El flujo completo CDPN (Etapa 0 a Etapa 9) puede ejecutarse exitosamente de extremo a extremo.
- [ ] Los 13 roles del sistema tienen acceso correcto a sus funcionalidades y están bloqueados de las que no les corresponden.
- [ ] Las notificaciones por correo se envían correctamente ante los eventos definidos.
- [ ] Los reportes se generan y exportan en PDF y Excel sin errores.

### 9.2 Criterios de Aceptación No Funcionales

- [ ] El tiempo de respuesta del dashboard no supera 3 segundos con datos de 50 procesos.
- [ ] La carga de un archivo PDF de 5 MB no supera 15 segundos.
- [ ] La generación de un reporte de 50 registros no supera 5 segundos.
- [ ] El sistema opera correctamente en Chrome, Edge y Firefox versión 110+.
- [ ] La interfaz es legible y funcional en resolución 1280 x 720 px.
- [ ] El sistema es accesible desde dispositivos móviles (resolución 375 px) para consulta básica.

---

## 10. CONCLUSIONES

Esta sección se completa al finalizar la campaña de pruebas con base en los resultados obtenidos. Debe incluir:

1. **Resumen de resultados:** Total de casos ejecutados, aprobados, fallidos.
2. **Defectos identificados:** Total por severidad y estado de resolución.
3. **Cumplimiento de criterios:** Si se cumplieron o no los criterios de aceptación.
4. **Recomendaciones:** Acciones sugeridas antes del pase a producción.
5. **Decisión de aceptación:** Aprobado / Aprobado con condiciones / No aprobado.

> **Pendiente de completar:** Esta sección se llenará al término de la ejecución de las pruebas y será firmada por el Coordinador de TI y el Líder de Pruebas.

---

## 11. ANEXOS

### Anexo A — Matriz de Trazabilidad de Casos de Prueba

| ID Caso de Prueba | Requerimiento Asociado | Módulo | Prioridad |
|---|---|---|---|
| CP-AUTH-01 | RF-01-01 | Autenticación | Alta |
| CP-AUTH-02 | RF-01-01 | Autenticación | Alta |
| CP-AUTH-03 | RN-SEG-03 | Autenticación / Seguridad | Alta |
| CP-AUTH-04 | RF-01-04 | Autenticación | Alta |
| CP-AUTH-05 | RF-01-03 | Autenticación | Alta |
| CP-AUTH-06 | RF-01-02 | Autenticación | Alta |
| CP-AUTH-07 | RF-01-04, RNF-15 | Autenticación | Alta |
| CP-AUTH-08 | RF-01-01, RNF-04 | Autenticación / Seguridad | Alta |
| CP-AUTH-09 | RN-SEG-01 | Autenticación / Seguridad | Alta |
| CP-AUTH-10 | RF-01-03 | Autenticación | Media |
| CP-AUTH-11 | RF-01-07, RNF-16 | Autenticación | Media |
| CP-PROC-01 | RF-02-01, RF-02-02 | Procesos | Alta |
| CP-PROC-02 | RF-02-01 | Procesos | Alta |
| CP-PROC-03 | RF-02-03 | Procesos | Alta |
| CP-PROC-04 | RF-02-03, RN-FLJ-01 | Procesos | Alta |
| CP-PROC-05 | RF-02-04 | Procesos | Alta |
| CP-PROC-06 | RF-02-06 | Procesos | Media |
| CP-PROC-07 | RN-CON-04 | Procesos | Media |
| CP-PROC-08 | RN-SEG-01 | Procesos / Seguridad | Alta |
| CP-PROC-09 | RN-AUD-01, RN-AUD-03 | Procesos / Auditoría | Alta |
| CP-PROC-10 | RF-02-07 | Procesos | Media |
| CP-DOC-01 | RF-03-01, RF-03-03 | Gestión Documental | Alta |
| CP-DOC-02 | RF-03-01 | Gestión Documental | Alta |
| CP-DOC-03 | RF-03-01, RN-VAL-04 | Gestión Documental | Alta |
| CP-DOC-04 | RF-03-04 | Gestión Documental | Alta |
| CP-DOC-05 | RF-03-04 | Gestión Documental | Alta |
| CP-DOC-06 | RF-03-03 | Gestión Documental | Alta |
| CP-DOC-07 | RF-03-07 | Gestión Documental | Media |
| CP-DOC-08 | RN-FLJ-01 | Gestión Documental | Alta |
| CP-DOC-09 | RF-03-03 | Gestión Documental | Media |
| CP-DOC-10 | RF-03-06, RN-FLJ-01 | Gestión Documental | Alta |
| CP-FLJ-01 | RF-04-01, RF-04-04 | Flujo CDPN | Alta |
| CP-FLJ-02 | RN-FLJ-03 | Flujo CDPN | Alta |
| CP-FLJ-03 | RF-04-05 | Flujo CDPN | Alta |
| CP-FLJ-04 | RN-SEG-01, RF-04-02 | Flujo CDPN / Seguridad | Alta |
| CP-FLJ-05 | RN-FLJ-04 | Flujo CDPN | Alta |
| CP-FLJ-06 | RN-FLJ-05 | Flujo CDPN | Alta |
| CP-FLJ-07 | RF-02-03 | Flujo CDPN | Alta |
| CP-FLJ-08 | RF-04-01 | Flujo CDPN | Media |
| CP-DASH-01 | RF-05-01 | Dashboard | Alta |
| CP-DASH-02 | RF-05-01 | Dashboard | Alta |
| CP-DASH-03 | RF-05-01 | Dashboard | Alta |
| CP-DASH-04 | RF-05-04 | Dashboard | Media |
| CP-DASH-05 | RF-05-05 | Dashboard | Media |
| CP-DASH-06 | RF-05-02, RF-05-03 | Dashboard | Media |
| CP-REP-01 | RF-06-01 | Reportes | Alta |
| CP-REP-02 | RF-06-02 | Reportes | Alta |
| CP-REP-03 | RF-06-02 | Reportes | Alta |
| CP-REP-04 | RF-06-03 | Reportes | Alta |
| CP-REP-05 | RF-06-04 | Reportes | Media |

### Anexo B — Datos de Prueba Adicionales

**Documentos de prueba disponibles:**
- `estudios_previos_prueba.pdf` (2 MB) — Para Etapa 0
- `cdp_prueba.pdf` (1 MB) — Para Etapa 1
- `cedula_contratista.pdf` (500 KB) — Para Etapa 2
- `contrato_firmado.pdf` (3 MB) — Para Etapa 5
- `archivo_grande.pdf` (15 MB) — Para prueba de límite de tamaño
- `archivo_invalido.exe` (100 KB) — Para prueba de formato no permitido
- `imagen_contratista.jpg` (800 KB) — Para prueba de formato imagen

### Anexo C — Herramientas y Comandos de Prueba

**Ejecutar pruebas Cypress:**
```bash
# Abrir interfaz gráfica de Cypress
npx cypress open

# Ejecutar todos los tests en modo headless
npx cypress run

# Ejecutar módulo específico
npx cypress run --spec "cypress/e2e/01-authentication/auth-completo.cy.js"
```

**Ejecutar pruebas unitarias PHPUnit:**
```bash
# Todas las pruebas
php artisan test

# Con cobertura de código
php artisan test --coverage
```

**Ejecutar pruebas de API con Postman:**
1. Importar la colección Postman del proyecto (`postman_collection.json`).
2. Configurar la variable de entorno `base_url` con la URL del ambiente QA.
3. Ejecutar la colección completa con el Runner de Postman.

---

*Documento elaborado por el Equipo de Tecnología de la Gobernación de Caldas — Versión 1.0 — Abril 2026*
