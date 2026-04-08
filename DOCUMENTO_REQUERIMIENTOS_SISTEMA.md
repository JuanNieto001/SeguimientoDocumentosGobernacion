# DOCUMENTO DE REQUERIMIENTOS DEL SISTEMA
## Sistema de Seguimiento de Documentos Contractuales
### Gobernación de Caldas

---

**Versión:** 1.0
**Fecha:** Abril 2026
**Elaborado por:** Equipo de Tecnología — Gobernación de Caldas
**Clasificación:** Documento Interno Oficial
**Estado:** Aprobado

---

## TABLA DE CONTENIDOS

1. [Introducción](#1-introducción)
2. [Descripción General](#2-descripción-general)
3. [Modelo de Acceso y Control](#3-modelo-de-acceso-y-control)
4. [Requerimientos del Sistema](#4-requerimientos-del-sistema)
5. [Reglas de Negocio](#5-reglas-de-negocio)
6. [Módulos del Sistema](#6-módulos-del-sistema)
7. [Casos de Uso](#7-casos-de-uso)
8. [Flujo General del Sistema](#8-flujo-general-del-sistema)
9. [Motor de Flujos](#9-motor-de-flujos)
10. [Modelo Operativo](#10-modelo-operativo)
11. [Indicadores y Dashboard](#11-indicadores-y-dashboard)
12. [Reportes Exportables](#12-reportes-exportables)
13. [Alertas y Notificaciones](#13-alertas-y-notificaciones)
14. [Configuración y Parametrización](#14-configuración-y-parametrización)
15. [Restricciones](#15-restricciones)
16. [Suposiciones y Dependencias](#16-suposiciones-y-dependencias)
17. [Anexos](#17-anexos)
18. [Propiedad Intelectual](#18-propiedad-intelectual)

---

## 1. INTRODUCCIÓN

### 1.1 Objetivo General

Especificar de manera formal, completa y verificable los requerimientos funcionales y no funcionales del **Sistema de Seguimiento de Documentos Contractuales** de la Gobernación de Caldas, con el fin de establecer la línea base de desarrollo, validación y aceptación del sistema por parte de los actores involucrados.

### 1.2 Alcance

El presente documento aplica a todas las fases del ciclo de vida del sistema: diseño, desarrollo, pruebas, despliegue y mantenimiento. Sirve como contrato técnico entre el equipo de desarrollo, los usuarios funcionales y la alta dirección de la Gobernación de Caldas.

### 1.3 Alcance del Sistema

El sistema abarca:

- Gestión integral de procesos de contratación pública bajo diferentes modalidades (Contratación Directa, Licitación Pública, Menor Cuantía, Selección Abreviada, entre otras).
- Motor de flujos configurable para el diseño y administración de etapas contractuales.
- Motor de dashboards con visualización dinámica de indicadores por rol y unidad.
- Gestión documental con carga, validación, versiones y trazabilidad de documentos.
- Sistema de alertas y notificaciones automáticas vía correo electrónico.
- Auditoría completa de acciones de usuario sobre el sistema.
- Control de acceso basado en roles (RBAC) con 13 roles diferenciados.
- Integración con la plataforma SECOP II para publicación de contratos.
- Generación de reportes exportables en múltiples formatos.

### 1.4 Exclusiones

El sistema **no** contempla:

- Gestión de nómina o talento humano.
- Contabilidad general o tesorería de la Gobernación.
- Gestión presupuestal externa al proceso contractual.
- Firma digital de documentos (se gestiona en plataformas externas como SECOP II).
- Gestión de contratos de obra pública o concesión.
- Integración directa con el sistema SIIF Nación o CHIP.
- Almacenamiento de expedientes físicos escaneados de años anteriores.

### 1.5 Definiciones

| Término | Definición |
|---|---|
| **CDPN** | Contratación Directa Persona Natural — modalidad de contratación para personas naturales bajo el artículo 2 de la Ley 1150 de 2007 |
| **SECOP II** | Sistema Electrónico de Contratación Pública — plataforma nacional de publicación |
| **CDP** | Certificado de Disponibilidad Presupuestal |
| **RPC** | Registro Presupuestal de Compromiso |
| **PAA** | Plan Anual de Adquisiciones |
| **RBAC** | Control de Acceso Basado en Roles (Role-Based Access Control) |
| **Motor de Flujos** | Módulo visual basado en React Flow para diseñar etapas del proceso contractual |
| **Motor de Dashboards** | Módulo interactivo con React y Recharts para visualización de indicadores |
| **Etapa** | Fase específica dentro de un flujo contractual con responsable, documentos y validaciones definidos |
| **Proceso** | Instancia de un flujo en ejecución para un contrato específico |
| **Flujo** | Plantilla configurable que define la secuencia de etapas de una modalidad contractual |
| **Spatie** | Librería de gestión de permisos y roles para Laravel (spatie/laravel-permission) |
| **BPIN** | Banco de Proyectos de Inversión Nacional |
| **ARL** | Administradora de Riesgos Laborales |
| **REDAM** | Registro de Deudores Alimentarios Morosos |

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Contexto

La Gobernación de Caldas, como entidad pública del orden departamental, gestiona anualmente un número significativo de contratos bajo distintas modalidades establecidas en el Estatuto General de Contratación Pública (Ley 80 de 1993, Ley 1150 de 2007 y Decreto 1082 de 2015). La complejidad de estos procesos, que involucra múltiples dependencias, funcionarios, documentos requeridos y plazos legales, ha generado históricamente problemas de seguimiento, pérdida de información y retrasos en la ejecución contractual.

### 2.2 Problema que Resuelve

| Problema | Impacto | Solución del Sistema |
|---|---|---|
| Dispersión de documentos en carpetas físicas y correos | Pérdida de información y demoras | Repositorio digital centralizado por proceso |
| Desconocimiento del estado actual de un contrato | Falta de trazabilidad | Seguimiento en tiempo real con historial de etapas |
| Ausencia de alertas sobre vencimientos y pendientes | Incumplimientos legales | Motor de alertas automáticas por correo |
| Falta de indicadores de gestión contractual | Decisiones sin datos | Dashboards dinámicos con métricas en tiempo real |
| Acceso no controlado a información sensible | Riesgos de seguridad | RBAC con 13 roles diferenciados |
| Flujos contractuales definidos de forma informal | Errores en el proceso | Motor de flujos configurable y auditable |

### 2.3 Objetivos Específicos

1. Digitalizar y centralizar la gestión documental de los procesos contractuales de la Gobernación de Caldas.
2. Implementar un Motor de Flujos configurable que permita modelar y ejecutar cualquier modalidad contractual con sus etapas, responsables y documentos.
3. Proveer un Motor de Dashboards dinámico con indicadores diferenciados por rol, secretaría y unidad.
4. Garantizar la trazabilidad completa de cada proceso contractual mediante auditoría automática.
5. Automatizar el envío de alertas y notificaciones sobre plazos, pendientes y cambios de estado.
6. Controlar el acceso al sistema mediante un modelo RBAC robusto con 13 roles definidos.
7. Integrar el sistema con SECOP II y el servidor de correo SMTP de Office 365.
8. Permitir la generación de reportes exportables para rendición de cuentas y control interno.

### 2.4 Actores

| Actor | Rol en el Sistema | Nivel de Acceso |
|---|---|---|
| **Super Administrador** | Gestión total del sistema y configuración técnica | Total |
| **Administrador** | Gestión de usuarios, roles y parametrización | Alto |
| **Gobernador** | Vista ejecutiva estratégica de todos los procesos | Consulta total |
| **Secretario de Despacho** | Supervisión de procesos de su secretaría | Secretaría |
| **Jefe de Unidad** | Gestión operativa de procesos de su unidad | Unidad |
| **Profesional Senior** | Ejecución y seguimiento de procesos asignados | Proceso |
| **Profesional Junior** | Apoyo en carga documental y seguimiento | Proceso (limitado) |
| **Abogado** | Revisión jurídica y elaboración de documentos contractuales | Jurídico |
| **Contador** | Revisión financiera y presupuestal | Financiero |
| **SECOP** | Publicación de contratos en plataforma nacional | SECOP |
| **Planeación** | Validación de compatibilidad de gasto y CDP | Planeación |
| **Hacienda** | Emisión de paz y salvos, CDP y RPC | Hacienda |
| **Jurídica** | Revisión legal, radicación y asignación de número de contrato | Jurídica |

---

## 3. MODELO DE ACCESO Y CONTROL

### 3.1 Gestión de Usuarios

- Los usuarios son creados por el Administrador o Super Administrador.
- Cada usuario tiene un correo electrónico único como identificador.
- El sistema registra: nombre completo, correo, contraseña (hasheada con bcrypt), secretaría, unidad, rol principal y estado (activo/inactivo).
- Un usuario puede ser desactivado sin eliminar su histórico en el sistema.
- Los usuarios inactivos no pueden iniciar sesión.

### 3.2 Roles del Sistema

El sistema implementa 13 roles jerarquizados:

| N.° | Nombre Técnico | Descripción |
|---|---|---|
| 1 | `super-admin` | Control total del sistema, configuración técnica |
| 2 | `admin` | Gestión de usuarios, roles, parametrización |
| 3 | `gobernador` | Vista ejecutiva estratégica |
| 4 | `secretario` | Supervisión por secretaría |
| 5 | `jefe-unidad` | Gestión operativa por unidad |
| 6 | `profesional-senior` | Ejecución de procesos con responsabilidad |
| 7 | `profesional-junior` | Apoyo en ejecución de procesos |
| 8 | `abogado` | Revisión jurídica y documentos legales |
| 9 | `contador` | Revisión financiera y presupuestal |
| 10 | `secop` | Publicación en SECOP II |
| 11 | `planeacion` | Validación de compatibilidad de gasto |
| 12 | `hacienda` | Emisión de certificados presupuestales |
| 13 | `juridica` | Radicación y número de contrato |

### 3.3 Control de Acceso Basado en Roles (RBAC)

El sistema implementa RBAC mediante la librería **Spatie Laravel Permission v6.24**, que permite:

- Asignación de múltiples roles a un usuario.
- Definición granular de permisos por acción (crear, ver, editar, eliminar, aprobar, exportar).
- Middleware de autorización en todas las rutas protegidas.
- Verificación de permisos tanto en backend (PHP) como en frontend (React).

### 3.4 Permisos Definidos

Los permisos se agrupan en las siguientes categorías:

| Categoría | Permisos |
|---|---|
| **Procesos** | `procesos.crear`, `procesos.ver`, `procesos.editar`, `procesos.eliminar`, `procesos.aprobar` |
| **Documentos** | `documentos.cargar`, `documentos.ver`, `documentos.validar`, `documentos.eliminar` |
| **Flujos** | `flujos.crear`, `flujos.editar`, `flujos.publicar`, `flujos.ver` |
| **Usuarios** | `usuarios.crear`, `usuarios.editar`, `usuarios.ver`, `usuarios.desactivar` |
| **Dashboard** | `dashboard.ver`, `dashboard.configurar`, `dashboard.exportar` |
| **Reportes** | `reportes.generar`, `reportes.exportar` |
| **Configuración** | `config.ver`, `config.editar` |
| **Auditoría** | `auditoria.ver` |

### 3.5 Asignación de Roles por Flujo

Cada etapa de un flujo contractual tiene asignado un rol responsable. El sistema valida que únicamente los usuarios con ese rol puedan avanzar la etapa o cargar documentos en ella. Un usuario sin el rol correspondiente puede ver el proceso pero no interactuar con la etapa activa.

### 3.6 Personalización de Acceso

- El Administrador puede ajustar los permisos de un rol específico sin afectar los demás.
- Los dashboards se configuran por rol, secretaría, unidad y usuario individual.
- La herencia de configuración sigue el orden: Global → Secretaría → Unidad → Usuario.

### 3.7 Roles Iniciales (Seeders)

Al momento del despliegue, el sistema crea automáticamente mediante seeders:

- Los 13 roles base.
- El conjunto de permisos por categoría.
- Un usuario `super-admin` inicial para configuración.
- Los flujos contractuales predefinidos (CDPN como flujo base).
- Las secretarías y unidades de la Gobernación de Caldas.

---

## 4. REQUERIMIENTOS DEL SISTEMA

### 4.1 Requerimientos Funcionales

#### RF-01: Módulo de Autenticación

| ID | Requerimiento |
|---|---|
| RF-01-01 | El sistema debe permitir el inicio de sesión con correo electrónico y contraseña. |
| RF-01-02 | El sistema debe bloquear el acceso a usuarios inactivos. |
| RF-01-03 | El sistema debe permitir la recuperación de contraseña vía correo electrónico. |
| RF-01-04 | El sistema debe cerrar la sesión automáticamente tras un período de inactividad configurable. |
| RF-01-05 | El sistema debe registrar todos los eventos de autenticación (inicio, cierre, intentos fallidos). |
| RF-01-06 | El sistema debe permitir al administrador forzar el cierre de sesión de cualquier usuario. |
| RF-01-07 | El sistema debe exigir contraseñas con mínimo 8 caracteres, combinando mayúsculas, minúsculas, números y caracteres especiales. |

#### RF-02: Módulo de Procesos Contractuales

| ID | Requerimiento |
|---|---|
| RF-02-01 | El sistema debe permitir la creación de procesos contractuales a partir de un flujo configurado. |
| RF-02-02 | El sistema debe asociar cada proceso a una secretaría, unidad, contratista y tipo de contrato. |
| RF-02-03 | El sistema debe controlar el avance por etapas, impidiendo saltar etapas sin completar las anteriores. |
| RF-02-04 | El sistema debe permitir la devolución de un proceso a etapas anteriores con justificación obligatoria. |
| RF-02-05 | El sistema debe registrar el historial completo de cambios de estado de cada proceso. |
| RF-02-06 | El sistema debe permitir la búsqueda y filtrado de procesos por secretaría, estado, tipo, fecha y responsable. |
| RF-02-07 | El sistema debe calcular y mostrar los días transcurridos en cada etapa versus el tiempo estimado. |
| RF-02-08 | El sistema debe asignar automáticamente el número de proceso según la nomenclatura definida (ej. CD-SP-XX-2026). |

#### RF-03: Módulo de Gestión Documental

| ID | Requerimiento |
|---|---|
| RF-03-01 | El sistema debe permitir la carga de documentos en formatos PDF, Word, Excel e imagen (JPG, PNG). |
| RF-03-02 | El sistema debe restringir los tipos de documentos permitidos por etapa según la configuración del flujo. |
| RF-03-03 | El sistema debe almacenar versiones de cada documento con fecha, hora y usuario que realizó la carga. |
| RF-03-04 | El sistema debe permitir la validación (aprobación/rechazo) de documentos por usuarios autorizados. |
| RF-03-05 | El sistema debe mostrar una lista de chequeo por etapa con los documentos requeridos y su estado. |
| RF-03-06 | El sistema debe impedir el avance de etapa si los documentos marcados como obligatorios no están cargados y validados. |
| RF-03-07 | El sistema debe permitir la descarga individual o masiva de documentos de un proceso. |
| RF-03-08 | El sistema debe registrar en auditoría toda acción sobre documentos (carga, validación, descarga, eliminación). |

#### RF-04: Módulo de Flujo y Seguimiento

| ID | Requerimiento |
|---|---|
| RF-04-01 | El sistema debe mostrar visualmente el estado actual de cada proceso en su flujo de etapas. |
| RF-04-02 | El sistema debe indicar el responsable de la etapa activa y el tiempo transcurrido. |
| RF-04-03 | El sistema debe permitir registrar actividades y comentarios en cada etapa. |
| RF-04-04 | El sistema debe notificar automáticamente al responsable de la siguiente etapa cuando se realice un avance. |
| RF-04-05 | El sistema debe permitir la configuración de etapas paralelas donde múltiples documentos se gestionan simultáneamente. |

#### RF-05: Módulo de Dashboard

| ID | Requerimiento |
|---|---|
| RF-05-01 | El sistema debe mostrar dashboards diferenciados según el rol del usuario. |
| RF-05-02 | El sistema debe permitir la configuración de widgets (KPI, gráficas, tablas) por rol, secretaría, unidad y usuario. |
| RF-05-03 | El sistema debe permitir el ajuste del layout del dashboard mediante drag & drop. |
| RF-05-04 | Los dashboards deben actualizarse con datos en tiempo real sin recargar la página. |
| RF-05-05 | El sistema debe soportar al menos 6 tipos de gráficas: barras, líneas, torta, dona, área polar y radar. |
| RF-05-06 | El sistema debe permitir al Administrador asignar plantillas de dashboard a roles, secretarías y unidades. |

#### RF-06: Módulo de Reportes

| ID | Requerimiento |
|---|---|
| RF-06-01 | El sistema debe generar reportes de procesos por estado, tipo, secretaría, unidad y período. |
| RF-06-02 | El sistema debe permitir la exportación de reportes en formato PDF y Excel. |
| RF-06-03 | El sistema debe generar un reporte de auditoría de acciones por usuario y fecha. |
| RF-06-04 | El sistema debe generar un reporte de documentos pendientes por proceso y etapa. |
| RF-06-05 | El sistema debe generar un reporte de tiempos de cada proceso por etapa. |

#### RF-07: Módulo de Administración

| ID | Requerimiento |
|---|---|
| RF-07-01 | El sistema debe permitir la creación, edición, activación y desactivación de usuarios. |
| RF-07-02 | El sistema debe permitir la asignación y revocación de roles por usuario. |
| RF-07-03 | El sistema debe permitir la gestión de secretarías y unidades de la Gobernación. |
| RF-07-04 | El sistema debe permitir la configuración del tiempo de inactividad de sesión. |
| RF-07-05 | El sistema debe permitir la parametrización del servidor SMTP para envío de correos. |
| RF-07-06 | El sistema debe mostrar el log de auditoría con filtros por usuario, acción, módulo y fecha. |

### 4.2 Requerimientos No Funcionales

| ID | Categoría | Requerimiento |
|---|---|---|
| RNF-01 | **Disponibilidad** | El sistema debe estar disponible el 99.5% del tiempo en horario laboral (7:00 a.m. – 7:00 p.m., días hábiles). |
| RNF-02 | **Rendimiento** | El tiempo de respuesta de las consultas principales no debe superar 2 segundos con hasta 100 usuarios concurrentes. |
| RNF-03 | **Auditoría** | Toda acción sobre datos críticos debe quedar registrada con usuario, fecha, hora, acción y dato modificado. |
| RNF-04 | **Seguridad** | Toda comunicación debe realizarse bajo HTTPS. Las contraseñas deben almacenarse con hash bcrypt. |
| RNF-05 | **Gestión de Usuarios** | El sistema debe soportar al menos 200 usuarios registrados simultáneamente. |
| RNF-06 | **Respaldo** | El sistema debe realizar copias de seguridad automáticas de la base de datos cada 24 horas. |
| RNF-07 | **Usabilidad** | La interfaz debe ser responsiva y funcionar en dispositivos móviles y tabletas (resolución mínima 320px). |
| RNF-08 | **Cumplimiento Normativo** | El sistema debe cumplir con la Ley 1712 de 2014 (Transparencia), Ley 80 de 1993 y Decreto 1082 de 2015. |
| RNF-09 | **Concurrencia** | El sistema debe soportar al menos 50 usuarios operando simultáneamente sin degradación de rendimiento. |
| RNF-10 | **Compatibilidad** | El sistema debe funcionar en los navegadores Chrome (v110+), Edge (v110+) y Firefox (v110+). |
| RNF-11 | **Escalabilidad** | La arquitectura debe permitir escalar horizontalmente ante incremento de usuarios o procesos. |
| RNF-12 | **Exportación** | Los reportes exportados en Excel y PDF deben mantener el formato y datos íntegros. |
| RNF-13 | **Integridad** | La base de datos debe garantizar integridad referencial mediante llaves foráneas y transacciones. |
| RNF-14 | **Recuperación de Fallos** | Ante una falla del servidor, el sistema debe recuperarse sin pérdida de datos en menos de 5 minutos. |
| RNF-15 | **Control de Inactividad** | La sesión debe expirar automáticamente tras 30 minutos de inactividad (configurable por el Administrador). |
| RNF-16 | **Políticas de Contraseñas** | Las contraseñas deben tener mínimo 8 caracteres con mayúsculas, minúsculas, números y caracteres especiales. |
| RNF-17 | **Seguridad de Datos** | Los datos personales de contratistas deben manejarse conforme a la Ley 1581 de 2012 (Habeas Data). |
| RNF-18 | **Retención de Logs** | Los logs de auditoría deben conservarse por un mínimo de 5 años. |
| RNF-19 | **Control de Sesiones** | El sistema debe detectar y bloquear sesiones duplicadas del mismo usuario. |

---

## 5. REGLAS DE NEGOCIO

### 5.1 Reglas de Seguridad

- RN-SEG-01: Un usuario solo puede acceder a los procesos de su secretaría o unidad, salvo que tenga rol de `gobernador`, `admin` o `super-admin`.
- RN-SEG-02: Solo el `super-admin` puede modificar la configuración técnica del sistema.
- RN-SEG-03: Los intentos fallidos de inicio de sesión deben registrarse; a partir del 5.° intento consecutivo, se debe bloquear la cuenta temporalmente.
- RN-SEG-04: Ningún usuario puede asignarse roles superiores al suyo.

### 5.2 Reglas del Flujo Documental

- RN-FLJ-01: No es posible avanzar una etapa sin que todos los documentos obligatorios estén cargados y en estado "Validado".
- RN-FLJ-02: La devolución de un proceso requiere comentario explicativo obligatorio y queda registrada en auditoría.
- RN-FLJ-03: En la etapa 1 (CDPN), el CDP solo puede solicitarse después de que la Compatibilidad del Gasto esté aprobada.
- RN-FLJ-04: En la etapa 6 (CDPN), el contratista debe firmar el contrato en SECOP II antes que el Secretario Privado.
- RN-FLJ-05: El número de contrato solo se asigna después de que el RPC esté expedido (etapa 8 CDPN).

### 5.3 Reglas de Validación

- RN-VAL-01: El valor del contrato no puede ser negativo ni cero.
- RN-VAL-02: La fecha de terminación del contrato debe ser posterior a la fecha de inicio.
- RN-VAL-03: El NIT o cédula del contratista debe ser único por proceso.
- RN-VAL-04: Los documentos cargados deben tener un tamaño máximo de 10 MB por archivo.
- RN-VAL-05: El sistema debe validar que el contratista no esté en listas de inhabilidades (REDAM, antecedentes fiscales, disciplinarios).

### 5.4 Reglas de Contratación

- RN-CON-01: Solo se puede iniciar un proceso si existe un flujo publicado para la modalidad seleccionada.
- RN-CON-02: La modalidad Contratación Directa Persona Natural aplica exclusivamente para personas naturales con cédula de ciudadanía colombiana.
- RN-CON-03: El proceso no puede ser eliminado una vez iniciada la etapa 1; solo puede ser anulado con justificación.
- RN-CON-04: Un proceso anulado no puede ser reactivado; debe crearse uno nuevo.

### 5.5 Reglas de Auditoría

- RN-AUD-01: Todo cambio en datos de un proceso genera un registro de auditoría automático.
- RN-AUD-02: Los registros de auditoría son de solo lectura; ningún usuario puede modificarlos ni eliminarlos.
- RN-AUD-03: El log de auditoría debe incluir: usuario, IP de conexión, módulo, acción, dato anterior, dato nuevo, fecha y hora.

---

## 6. MÓDULOS DEL SISTEMA

### 6.1 Módulo de Autenticación y Seguridad

Gestiona el acceso al sistema, sesiones de usuario, recuperación de contraseñas y registro de eventos de autenticación.

### 6.2 Módulo de Administración

Permite la gestión de usuarios, roles, permisos, secretarías, unidades y configuración general del sistema.

### 6.3 Módulo de Motor de Flujos

Editor visual drag & drop para diseñar flujos contractuales con etapas, documentos requeridos, responsables y validaciones. Permite publicar y versionar flujos.

### 6.4 Módulo de Procesos Contractuales

Gestión del ciclo de vida de cada proceso: creación, seguimiento de etapas, carga documental, validaciones, devoluciones y cierre.

### 6.5 Módulo de Motor de Dashboards

Constructor visual de dashboards con widgets configurables (KPI, gráficas, tablas) por rol, secretaría, unidad y usuario. Datos en tiempo real.

### 6.6 Módulo de Reportes y Exportación

Generación y exportación de reportes de procesos, auditoría, documentos y tiempos en formatos PDF y Excel.

---

## 7. CASOS DE USO

### 7.1 Casos de Uso Principales

#### CU-01: Inicio de Sesión
- **Actor:** Todos los usuarios
- **Precondición:** Usuario activo registrado en el sistema
- **Flujo principal:** Usuario ingresa credenciales → sistema valida → redirige al dashboard según rol
- **Flujo alternativo:** Credenciales inválidas → mensaje de error → registro de intento fallido

#### CU-02: Crear Proceso Contractual
- **Actor:** Jefe de Unidad, Profesional Senior
- **Precondición:** Flujo publicado para la modalidad; usuario con permiso `procesos.crear`
- **Flujo principal:** Seleccionar modalidad → completar datos básicos → sistema asigna número → proceso en etapa 0

#### CU-03: Cargar Documento en Etapa
- **Actor:** Responsable de la etapa activa
- **Precondición:** Proceso en la etapa correspondiente; usuario con rol asignado a esa etapa
- **Flujo principal:** Seleccionar documento → cargar archivo → sistema valida formato y tamaño → documento en estado "Pendiente"

#### CU-04: Validar Documento
- **Actor:** Usuario con permiso `documentos.validar`
- **Precondición:** Documento en estado "Pendiente"
- **Flujo principal:** Revisar documento → aprobar o rechazar con comentario → documento actualiza estado → notificación al cargador

#### CU-05: Avanzar Etapa
- **Actor:** Responsable de la etapa activa
- **Precondición:** Todos los documentos obligatorios validados
- **Flujo principal:** Confirmar avance → sistema valida checklist → etapa marcada como completada → siguiente etapa activa → notificación al nuevo responsable

#### CU-06: Configurar Dashboard
- **Actor:** Administrador, Super Administrador
- **Precondición:** Plantilla de dashboard creada
- **Flujo principal:** Seleccionar rol/secretaría/unidad → arrastrar widgets → configurar métricas → guardar configuración

### 7.2 Casos de Uso por Módulo

#### Motor de Flujos
- CU-MF-01: Crear flujo contractual con editor visual
- CU-MF-02: Agregar etapas con documentos y responsables
- CU-MF-03: Publicar flujo para uso en procesos
- CU-MF-04: Versionar y editar flujo existente
- CU-MF-05: Deshabilitar flujo sin afectar procesos en curso

#### Gestión Documental
- CU-GD-01: Cargar documento en etapa activa
- CU-GD-02: Visualizar historial de versiones de un documento
- CU-GD-03: Descargar expediente completo de un proceso
- CU-GD-04: Validar o rechazar documento con comentario

#### Administración
- CU-ADM-01: Crear usuario y asignar rol
- CU-ADM-02: Desactivar usuario
- CU-ADM-03: Reasignar rol a usuario
- CU-ADM-04: Consultar log de auditoría

---

## 8. FLUJO GENERAL DEL SISTEMA

### 8.1 Acceso al Sistema
El usuario accede mediante navegador web a la URL del sistema, ingresa credenciales y es redirigido al dashboard correspondiente a su rol.

### 8.2 Dashboard Inicial
Cada rol visualiza indicadores diferenciados: el Gobernador ve resúmenes globales, el Secretario ve su secretaría, el Jefe de Unidad ve su unidad, y los profesionales ven sus procesos asignados.

### 8.3 Creación del Proceso
El Jefe de Unidad o Profesional Senior crea el proceso seleccionando la modalidad contractual (flujo). El sistema instancia el flujo y posiciona el proceso en la etapa 0.

### 8.4 Ejecución de Etapas
El responsable de cada etapa carga los documentos requeridos, completa el checklist y avanza la etapa. El sistema notifica automáticamente al siguiente responsable.

### 8.5 Etapas Paralelas
En las etapas que lo permiten (ej. etapa 1 CDPN), múltiples documentos pueden gestionarse simultáneamente por diferentes actores.

### 8.6 Revisión Jurídica
La Secretaría Jurídica revisa el expediente, genera el número de proceso y firma el contrato. Si hay observaciones, devuelve el proceso a la etapa correspondiente.

### 8.7 Publicación SECOP II
El equipo SECOP carga el contrato en la plataforma nacional, gestiona las firmas electrónicas y descarga el contrato definitivo.

### 8.8 Cierre del Proceso
Tras el Acta de Inicio firmada y el registro en SECOP II, el proceso queda en estado "Contrato Iniciado" y pasa a fase de supervisión.

---

## 9. MOTOR DE FLUJOS

### 9.1 Descripción General
El Motor de Flujos es un módulo visual implementado con React Flow (@xyflow/react) que permite diseñar, configurar y publicar flujos contractuales. Es el núcleo configurable del sistema.

### 9.2 Funcionalidades
- Editor drag & drop con nodos para cada etapa.
- Configuración de documentos requeridos por etapa.
- Asignación de roles responsables por etapa.
- Definición de etapas paralelas y dependencias.
- Publicación y versionado de flujos.
- Importación y exportación de configuraciones.

### 9.3 Estructura de Datos
Cada flujo se almacena con: nombre, descripción, tipo de contratación, versión, estado (borrador/publicado/inactivo), y la lista de etapas con sus documentos y responsables.

### 9.4 Tipos de Nodo
- **Nodo de Inicio:** Punto de entrada del flujo.
- **Nodo de Etapa:** Etapa contractual con documentos y responsable.
- **Nodo de Decisión:** Bifurcación condicional (ej. observaciones → devolver).
- **Nodo de Fin:** Estado terminal del proceso.

### 9.5 Estados de Etapa
| Estado | Descripción |
|---|---|
| `pendiente` | Etapa no iniciada |
| `en_progreso` | Etapa activa con documentos en carga |
| `completada` | Todos los documentos validados, etapa avanzada |
| `devuelta` | Etapa reactivada por devolución con observaciones |
| `omitida` | Etapa marcada como no aplicable (requiere justificación) |

### 9.6 Transiciones
Cada transición entre etapas puede ser:
- **Automática:** Se activa al completar el checklist de la etapa anterior.
- **Manual:** Requiere confirmación explícita del responsable.
- **Condicional:** Depende de criterios configurados (ej. valor del contrato).

### 9.7 Responsables por Etapa
El rol responsable de una etapa define qué usuarios pueden interactuar con ella. Si el usuario activo no tiene el rol, puede ver la etapa pero no modificarla.

### 9.8 Validaciones Configurables
Por cada documento de una etapa se puede configurar:
- Si es obligatorio u opcional.
- Formato de archivo permitido.
- Si requiere validación de otro usuario antes de continuar.

### 9.9 Trazabilidad
Cada transición entre etapas queda registrada con: usuario, fecha, hora, estado anterior, estado nuevo y comentarios.

### 9.10 Versiones de Flujo
Un flujo puede ser editado generando una nueva versión. Los procesos en curso continúan con la versión original; los nuevos procesos usan la versión vigente.

### 9.11 Interfaz Visual (Motor de Flujos - React Flow)
El editor visual muestra los nodos interconectados en una vista de serpentina o lineal. Permite zoom, paneo, selección y edición de propiedades de cada nodo.

### 9.12 Flujo Base Implementado: Contratación Directa Persona Natural (CDPN)

El flujo CDPN es el flujo base del sistema con 10 etapas (0 a 9):

| Etapa | Nombre | Responsable | Rol |
|---|---|---|---|
| 0 | Definición de la Necesidad | Jefe de Unidad | `jefe-unidad` / `unidad_solicitante` |
| 1 | Solicitud de Documentos Iniciales (Paralela) | Unidad de Descentralización | `planeacion` |
| 2 | Validación del Contratista (Paralela) | Abogado Unidad | `abogado` / `unidad_solicitante` |
| 3 | Elaboración de Documentos Contractuales | Abogado Unidad | `abogado` / `unidad_solicitante` |
| 4 | Consolidación del Expediente Precontractual | Abogado Unidad | `abogado` / `unidad_solicitante` |
| 5 | Radicación Jurídica y Ajustado a Derecho | Abogado Enlace / Oficina Radicación | `juridica` |
| 6 | Publicación y Firma en SECOP II | Apoyo Estructuración | `secop` |
| 7 | Solicitud de RPC | Unidad de Descentralización | `planeacion` |
| 8 | Radicación Final y Número de Contrato | Oficina de Radicación | `juridica` |
| 9 | ARL, Acta de Inicio e Inicio en SECOP (Final) | Supervisor + Contratista | `unidad_solicitante` |

**Documentos por etapa:**

- **Etapa 0:** Estudios Previos (único documento requerido).
- **Etapa 1:** PAA, No Planta, Paz y Salvo Rentas, Paz y Salvo Contabilidad, Compatibilidad del Gasto, CDP (requiere Compatibilidad primero), SIGEP validado.
- **Etapa 2:** 21 documentos del contratista (hoja de vida SIGEP, RUT, cédula, antecedentes, seguridad social, certificado médico, entre otros).
- **Etapa 3:** 8 documentos contractuales proyectados (invitación, solicitud contratación, certificado idoneidad, estudios previos finales, análisis sector, aceptación oferta, BPIN opcional, excepción fiscal opcional).
- **Etapa 4:** Carpeta precontractual completa (checklist de 35 documentos).
- **Etapa 5:** Solicitud SharePoint, número proceso (CD-SP-XX-2026), lista chequeo, ajustado a derecho, contrato firmado (Secretario Privado + Contratista + Abogado Enlace).
- **Etapa 6:** Contrato SECOP, aprobación jurídica, firma contratista (PRIMERO), firma Secretario Privado (DESPUÉS), contrato electrónico.
- **Etapa 7:** Solicitud RPC firmada por Secretario de Planeación, radicado Hacienda, RPC expedido, expediente físico organizado.
- **Etapa 8:** Radicado final, número de contrato asignado.
- **Etapa 9:** Solicitud ARL, Acta de Inicio firmada, registro inicio ejecución en SECOP II.

---

## 10. MODELO OPERATIVO

### 10.1 Estructura Organizacional Soportada

El sistema modela la estructura de la Gobernación de Caldas con dos niveles:
- **Secretarías de Despacho:** Unidades organizacionales de primer nivel.
- **Unidades:** Dependencias dentro de cada secretaría.

Cada usuario está asociado a una secretaría y una unidad. Cada proceso está asociado a la secretaría y unidad que lo origina.

### 10.2 Ciclo de Vida de un Proceso

```
Borrador → Etapa 0 → Etapa 1 → ... → Etapa 9 → Contrato Iniciado
                ↕ (devoluciones posibles en cualquier punto)
            Anulado (estado terminal alternativo)
```

### 10.3 Responsabilidad Operativa

| Momento | Responsable |
|---|---|
| Creación del proceso | Jefe de Unidad / Profesional Senior |
| Etapas 0-4 | Unidad Solicitante (jefe, abogado, profesional) |
| Etapa 1 (paralela) | Planeación |
| Etapa 5 | Jurídica |
| Etapa 6 | SECOP |
| Etapa 7 | Planeación |
| Etapas 8-9 | Jurídica / Unidad Solicitante |

### 10.4 Integración con Procesos Externos

- **SECOP II:** El sistema integra con la API de SECOP para publicar y consultar contratos.
- **SMTP Office 365:** Todas las notificaciones automáticas se envían mediante el servidor de correo institucional de la Gobernación.

---

## 11. INDICADORES Y DASHBOARD

### 11.1 Indicadores del Gobernador
- Total de procesos activos por secretaría.
- Procesos completados vs. en curso (período seleccionable).
- Procesos con alertas de vencimiento.
- Valor total contratado por secretaría y período.

### 11.2 Indicadores del Secretario
- Procesos de su secretaría por estado y etapa.
- Tiempo promedio de cada etapa en su secretaría.
- Documentos pendientes de validación.

### 11.3 Indicadores del Jefe de Unidad
- Procesos de su unidad activos y completados.
- Etapas con retraso respecto al tiempo estimado.
- Próximos vencimientos de etapa.

### 11.4 Indicadores Operativos (Profesionales)
- Procesos asignados con etapa activa.
- Documentos pendientes de carga.
- Actividades del día.

### 11.5 Tipos de Widget Disponibles

| Tipo | Descripción |
|---|---|
| **KPI** | Métrica numérica con ícono, color y tendencia |
| **Gráfica de Barras** | Comparativo por categoría |
| **Gráfica de Líneas** | Evolución temporal |
| **Gráfica de Torta/Dona** | Distribución porcentual |
| **Área Polar / Radar** | Comparativo multidimensional |
| **Tabla** | Listado filtrado y paginado |
| **Timeline** | Línea de tiempo de eventos |
| **Heatmap** | Mapa de calor por categoría |

### 11.6 Configuración de Dashboard

El Motor de Dashboards permite:
- Crear plantillas reutilizables.
- Asignar plantillas por rol, secretaría, unidad o usuario individual.
- Configurar el query SQL dinámico de cada widget (con scope automático por rol).
- Ajustar el layout mediante drag & drop.
- Persistir la configuración en base de datos.

---

## 12. REPORTES EXPORTABLES

### 12.1 Reporte de Procesos

Contenido: Lista de procesos con número, tipo, contratista, secretaría, unidad, estado, etapa actual, fecha de inicio y días transcurridos.
Filtros: Por secretaría, unidad, estado, tipo de contrato, período.
Formatos: PDF, Excel.

### 12.2 Reporte de Auditoría

Contenido: Registro de acciones con usuario, IP, módulo, acción, datos modificados, fecha y hora.
Filtros: Por usuario, módulo, acción, rango de fechas.
Formatos: PDF, Excel.

### 12.3 Reporte de Documentos Pendientes

Contenido: Documentos requeridos no cargados o pendientes de validación por proceso y etapa.
Filtros: Por secretaría, unidad, proceso, etapa.
Formatos: PDF, Excel.

### 12.4 Reporte de Tiempos por Etapa

Contenido: Tiempo real vs. estimado por etapa para cada proceso activo y completado.
Filtros: Por secretaría, unidad, tipo de contrato, período.
Formatos: PDF, Excel.

### 12.5 Reporte de Indicadores de Gestión

Contenido: Resumen ejecutivo de KPIs del período: total contratos, valor, tiempos promedio, cumplimiento de plazos.
Filtros: Por secretaría, período.
Formatos: PDF.

---

## 13. ALERTAS Y NOTIFICACIONES

### 13.1 Tipos de Alerta

| Tipo | Disparador | Destinatario |
|---|---|---|
| **Etapa Vencida** | Tiempo de etapa supera el estimado configurado | Responsable de la etapa + Jefe de Unidad |
| **Documento Pendiente** | Documento obligatorio sin cargar después de N días | Responsable de la etapa |
| **Avance de Etapa** | Etapa completada → nueva etapa activa | Responsable de la nueva etapa |
| **Devolución** | Proceso devuelto a etapa anterior | Responsable de la etapa anterior |
| **Documento Rechazado** | Documento validado con resultado "Rechazado" | Usuario que cargó el documento |
| **Proceso Creado** | Nuevo proceso iniciado | Jefe de Unidad y Administrador |
| **Vencimiento Próximo** | Etapa vence en N días (N configurable) | Responsable + Jefe de Unidad |

### 13.2 Canal de Notificación

Todas las alertas se envían por correo electrónico usando el servidor SMTP de Office 365 configurado en el sistema. Las notificaciones también se muestran en el panel de notificaciones dentro del sistema.

### 13.3 Configuración de Alertas

- El Administrador puede activar o desactivar tipos de alerta.
- Los umbrales de tiempo (días para alerta de vencimiento) son configurables por tipo de flujo.
- Los usuarios pueden configurar sus preferencias de notificación (solo alertas críticas, todas las alertas).

---

## 14. CONFIGURACIÓN Y PARAMETRIZACIÓN

### 14.1 Configuración del Servidor de Correo

Parámetros SMTP configurables: host, puerto, protocolo (TLS/SSL), usuario, contraseña, nombre remitente, correo remitente.

### 14.2 Configuración de Sesiones

- Tiempo de inactividad para cierre automático de sesión (por defecto: 30 minutos).
- Número máximo de intentos de inicio de sesión antes del bloqueo temporal.

### 14.3 Configuración de Alertas

- Umbral de días para alertas de vencimiento por etapa (configurable por flujo).
- Destinatarios adicionales para alertas críticas.

### 14.4 Configuración de Nomenclatura de Procesos

- Formato del número de proceso por modalidad contractual.
- Prefijos por secretaría y tipo de contrato.
- Contador inicial de numeración por año.

### 14.5 Configuración de Documentos

- Tipos de archivo permitidos por extensión.
- Tamaño máximo de archivo por etapa y por proceso.
- Documentos obligatorios vs. opcionales por etapa y flujo.

### 14.6 Configuración de Integración SECOP

- URL del API de SECOP II.
- Credenciales de integración.
- Modo de publicación automático o manual.

---

## 15. RESTRICCIONES

### 15.1 Restricciones Técnicas

- El sistema requiere PHP 8.2+ y MySQL 8.0+ en el servidor de producción.
- El navegador del usuario debe tener JavaScript habilitado.
- La resolución mínima recomendada de pantalla es 1280 x 720 px para escritorio.

### 15.2 Restricciones de Negocio

- Solo pueden crearse procesos sobre flujos en estado "Publicado".
- Un proceso no puede tener dos etapas activas del mismo tipo simultáneamente (salvo etapas marcadas como paralelas).

### 15.3 Restricciones de Seguridad

- Las contraseñas no pueden reutilizarse (últimas 5 contraseñas).
- Los archivos cargados se validan con MIME type y extensión; no se permiten ejecutables.
- Todas las entradas de usuario se sanitizan para prevenir XSS y SQL Injection.

### 15.4 Restricciones de Datos

- Los registros de auditoría son inmutables: no pueden editarse ni eliminarse por ningún usuario.
- Un proceso anulado no puede ser reactivado.
- Los documentos validados y aprobados no pueden ser eliminados; solo pueden ser reemplazados por una nueva versión.

### 15.5 Restricciones de Integración

- La integración con SECOP II depende de la disponibilidad de la API nacional; fallos en SECOP no deben afectar la operación interna del sistema.
- La integración SMTP requiere conexión activa a internet.

### 15.6 Restricciones de Roles

- Un usuario no puede tener el rol `super-admin` y cualquier otro rol simultáneamente.
- El rol `gobernador` es de solo lectura y no puede realizar acciones sobre procesos.

---

## 16. SUPOSICIONES Y DEPENDENCIAS

### 16.1 Suposiciones

- Los funcionarios de la Gobernación cuentan con equipos de cómputo con navegador web actualizado y conexión a internet.
- Los documentos de los procesos contractuales están disponibles en formato digital para su carga en el sistema.
- La Gobernación de Caldas dispone de credenciales activas en SECOP II y correo institucional Office 365.
- El personal ha recibido o recibirá capacitación básica en el uso del sistema antes del inicio de operaciones.
- Los procesos de contratación iniciados antes de la implementación del sistema no serán migrados; el sistema opera para procesos nuevos.

### 16.2 Dependencias

| Dependencia | Tipo | Impacto |
|---|---|---|
| SECOP II (API nacional) | Externa | La publicación de contratos depende de la disponibilidad del API |
| Office 365 (SMTP) | Externa | El envío de notificaciones depende del servicio de correo |
| MySQL 8.0+ | Infraestructura | Motor de base de datos del servidor de producción |
| PHP 8.2+ | Infraestructura | Entorno de ejecución del backend Laravel |
| Conexión a internet | Infraestructura | Requerida para integraciones externas y acceso remoto |

---

## 17. ANEXOS

### 17.1 Glosario Extendido

| Término | Definición |
|---|---|
| **Eloquent** | ORM de Laravel para la interacción con la base de datos mediante modelos PHP |
| **Vite** | Herramienta de construcción y bundling del frontend (reemplaza Webpack) |
| **Tailwind CSS** | Framework de utilidades CSS para el diseño de la interfaz |
| **React Flow** | Biblioteca de visualización de grafos y flujos para React |
| **Recharts** | Biblioteca de gráficas para React usada en el Motor de Dashboards |
| **Sanctum** | Sistema de autenticación de Laravel mediante tokens o sesiones |
| **Middleware** | Capa intermedia de software que valida permisos antes de ejecutar una acción |
| **Seeder** | Script de Laravel que inserta datos iniciales en la base de datos |
| **Migración** | Script de Laravel que crea o modifica la estructura de la base de datos |

### 17.2 Marco Normativo

| Norma | Descripción |
|---|---|
| Ley 80 de 1993 | Estatuto General de Contratación Pública |
| Ley 1150 de 2007 | Eficiencia y transparencia en la Ley 80 |
| Decreto 1082 de 2015 | Reglamentario del Sector Administrativo de Planeación Nacional |
| Ley 1712 de 2014 | Transparencia y del Derecho de Acceso a la Información Pública |
| Ley 1581 de 2012 | Protección de Datos Personales |
| CONPES 3248 | Política de Contratación Pública |

### 17.3 Histórico de Versiones del Documento

| Versión | Fecha | Descripción | Autor |
|---|---|---|---|
| 1.0 | Abril 2026 | Versión inicial aprobada | Equipo de Tecnología |

### 17.4 Lista de Revisores

| Nombre | Cargo | Rol en la Revisión |
|---|---|---|
| Coordinador de Tecnología | TI Gobernación | Revisor Técnico |
| Secretario de Planeación | Despacho | Revisor Funcional |
| Asesor Jurídico | Jurídica | Revisor Normativo |

---

## 18. PROPIEDAD INTELECTUAL

### 18.1 Titularidad

El Sistema de Seguimiento de Documentos Contractuales es propiedad exclusiva de la **Gobernación de Caldas**. El desarrollo, código fuente, bases de datos, documentación y todos los activos digitales asociados son de titularidad pública conforme a la Ley 1232 de 2008 y la normativa de Software Público.

### 18.2 Licencias de Componentes de Terceros

| Componente | Licencia | Uso |
|---|---|---|
| Laravel 12.x | MIT | Framework backend |
| React 19 | MIT | Framework frontend |
| Spatie Permission | MIT | RBAC |
| React Flow (@xyflow/react) | MIT | Motor visual de flujos |
| Recharts | MIT | Gráficas del dashboard |
| Tailwind CSS | MIT | Estilos |
| Vite | MIT | Build del frontend |

### 18.3 Uso y Distribución

El sistema no puede ser comercializado ni distribuido a terceros sin autorización expresa de la Gobernación de Caldas. Su uso está restringido a los funcionarios y contratistas autorizados de la entidad.

### 18.4 Confidencialidad

Toda la información de los procesos contractuales gestionada en el sistema tiene carácter confidencial hasta su publicación oficial en SECOP II, conforme al principio de transparencia de la contratación pública. Los funcionarios con acceso al sistema están obligados a mantener la reserva de la información no pública.

---

*Documento elaborado por el Equipo de Tecnología de la Gobernación de Caldas — Versión 1.0 — Abril 2026*
