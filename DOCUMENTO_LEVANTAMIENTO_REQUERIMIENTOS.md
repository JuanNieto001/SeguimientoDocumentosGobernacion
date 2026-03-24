# DOCUMENTO DE LEVANTAMIENTO DE REQUERIMIENTOS

## Sistema de Seguimiento de Documentos de Contratación
### Gobernación de Caldas

**Versión:** 1.0  
**Fecha:** 11 de marzo de 2026  
**Elaborado por:** Equipo de Desarrollo  
**Entidad:** Gobernación de Caldas  

---

## TABLA DE CONTENIDO

1. [Introducción](#1-introducción)
2. [Alcance del Sistema](#2-alcance-del-sistema)
3. [Exclusiones del Sistema](#3-exclusiones-del-sistema)
4. [Actores Involucrados](#4-actores-involucrados)
5. [Requerimientos Funcionales](#5-requerimientos-funcionales)
6. [Requerimientos No Funcionales](#6-requerimientos-no-funcionales)
7. [Restricciones](#7-restricciones)
8. [Suposiciones y Dependencias](#8-suposiciones-y-dependencias)
9. [Casos de Uso](#9-casos-de-uso)
10. [Reglas de Negocio](#10-reglas-de-negocio)
11. [Flujo General del Sistema](#11-flujo-general-del-sistema)
12. [Flujo de Contratación Directa – Persona Natural (CD-PN)](#12-flujo-cd-pn)
13. [Flujo de Contratación Directa – Persona Jurídica (CD-PJ)](#13-flujo-cd-pj)
14. [Motor de Flujos Configurable](#14-motor-de-flujos-configurable)
15. [Indicadores del Sistema (Dashboard)](#15-indicadores-del-sistema)
16. [Reportes Exportables](#16-reportes-exportables)
17. [Configuración de Alertas Automáticas](#17-configuración-de-alertas-automáticas)
18. [Módulo de Supervisión de Contratos](#18-módulo-de-supervisión)
19. [Módulo de Modificaciones Contractuales](#19-módulo-de-modificaciones-contractuales)
20. [Asistente de Ayuda (Agente Estiven)](#20-asistente-de-ayuda)
21. [Glosario](#21-glosario)

---

## 1. INTRODUCCIÓN

### 1.1 Propósito del Documento

El presente documento establece el levantamiento de requerimientos funcionales y no funcionales del **Sistema de Seguimiento de Documentos de Contratación** de la Gobernación de Caldas. Su objetivo es formalizar las necesidades, reglas de negocio, casos de uso y especificaciones técnicas que rigen el desarrollo y operación del sistema.

### 1.2 Descripción General del Sistema

El sistema es una plataforma web diseñada para gestionar, rastrear y controlar el ciclo completo de los procesos de contratación de la Gobernación de Caldas. Permite la trazabilidad documental desde la creación de una solicitud hasta la ejecución del contrato, involucrando múltiples dependencias y actores con roles diferenciados.

### 1.3 Nota Importante – Escalabilidad de Modalidades de Contratación

> **El sistema cuenta con un Motor de Flujos Configurable** que permite crear, versionar y ejecutar flujos de trabajo personalizados para cualquier modalidad de contratación sin necesidad de modificaciones en el código fuente.
>
> En la fase actual se encuentran implementados de forma nativa los flujos de **Contratación Directa – Persona Natural (CD-PN)** y **Contratación Directa – Persona Jurídica (CD-PJ)**. Las demás modalidades (Licitación Pública, Selección Abreviada, Concurso de Méritos, Mínima Cuantía, entre otras) **podrán ser creadas por los administradores del sistema** a través del Motor de Flujos en cualquier momento, sin intervención del equipo de desarrollo.
>
> Esto garantiza que el sistema es **completamente escalable** y puede adaptarse a nuevas necesidades normativas o procedimentales de la entidad de forma autónoma.

---

## 2. ALCANCE DEL SISTEMA

### 2.1 Alcance Funcional

El sistema cubre los siguientes procesos y funcionalidades:

| # | Módulo | Descripción |
|---|--------|-------------|
| 1 | **Gestión de Procesos de Contratación** | Creación, seguimiento y cierre de procesos de contratación con flujo por etapas entre dependencias. |
| 2 | **Contratación Directa – Persona Natural** | Flujo nativo de 9 etapas con 21 estados y máquina de estados validada. |
| 3 | **Contratación Directa – Persona Jurídica** | Flujo nativo de 10 etapas con documentación específica para personas jurídicas. |
| 4 | **Motor de Flujos Configurable** | Motor para crear flujos de trabajo personalizados con versionamiento, catálogo de pasos, documentos por paso, condiciones y responsables. |
| 5 | **Gestión Documental con Versionado** | Carga, previsualización, aprobación, rechazo, reemplazo y versionado de más de 50 tipos de documentos. |
| 6 | **Control por Roles y Áreas** | Autenticación, autorización por roles (19 roles) y permisos granulares con control por secretaría/unidad. |
| 7 | **Dashboard con Indicadores** | Panel de control con métricas en tiempo real por rol, área, etapa y eficiencia. |
| 8 | **Sistema de Alertas Automáticas** | Alertas por tiempo, documentos, responsabilidad y transiciones de estado. |
| 9 | **Reportes Exportables** | 6 tipos de reportes en formatos PDF, Excel y HTML. |
| 10 | **Auditoría y Trazabilidad** | Registro completo de todas las acciones realizadas sobre cada proceso. |
| 11 | **Notificaciones por Correo** | Notificaciones automáticas por transición de estado, devolución y carga documental. |
| 12 | **Supervisión de Contratos** | Informes de supervisión, control de pagos y seguimiento de ejecución. |
| 13 | **Modificaciones Contractuales** | Adiciones, prórrogas, suspensiones, cesiones y terminaciones con validación presupuestal. |
| 14 | **Administración del Sistema** | CRUD de usuarios, roles, permisos, secretarías, unidades y guías de ayuda. |
| 15 | **Asistente de Ayuda (Agente Estiven)** | Asistente flotante con guías paso a paso y formulario de solicitud de soporte por correo. |
| 16 | **Restablecimiento de Contraseña Personalizado** | Flujo de doble campo: identificación del usuario + correo de destino independiente. |

### 2.2 Alcance Geográfico

El sistema opera en la **Gobernación de Caldas** y sus secretarías, con acceso vía navegador web en red interna o a través de la dirección de servidor configurada.

---

## 3. EXCLUSIONES DEL SISTEMA

Los siguientes elementos **NO** forman parte del alcance actual del sistema:

| # | Exclusión | Observación |
|---|-----------|-------------|
| 1 | **Flujos nativos de Licitación Pública, Selección Abreviada, Concurso de Méritos y Mínima Cuantía** | Estas modalidades podrán ser creadas a demanda mediante el Motor de Flujos Configurable, sin requerir desarrollo adicional. |
| 2 | **Integración bidireccional con SECOP II** | Actualmente el sistema registra datos de publicación en SECOP (URL, número de proceso). La integración vía API para publicación automática no está en alcance. Se cuenta con un servicio de consulta (`SecopDatosAbiertoService`) para lectura de datos abiertos. |
| 3 | **Firma electrónica o digital certificada** | El sistema registra las firmas (contratista y ordenador del gasto) como marca lógica. No se integra con servicios de firma electrónica avanzada. |
| 4 | **Módulo contable o financiero** | El sistema registra valores de CDP y RP pero no realiza operaciones contables. |
| 5 | **Generación automática de minutas de contrato** | Los documentos contractuales se cargan manualmente al sistema. |
| 6 | **Gestión documental externa (ECM)** | Los documentos se almacenan en el filesystem del servidor. No hay integración con sistemas ECM como SharePoint o Alfresco. |
| 7 | **Aplicación móvil nativa** | El acceso se realiza exclusivamente vía navegador web. La interfaz es responsiva. |
| 8 | **Multi-tenencia** | El sistema está diseñado para una única entidad (Gobernación de Caldas). |

---

## 4. ACTORES INVOLUCRADOS

### 4.1 Roles del Sistema

| # | Actor / Rol | Descripción | Responsabilidades Principales |
|---|-------------|-------------|-------------------------------|
| 1 | **Administrador** (`admin`) | Administrador general del sistema | CRUD completo de usuarios, roles, permisos, secretarías, unidades. Acceso total a todos los módulos. Configuración de flujos y guías. |
| 2 | **Administrador General** (`admin_general`) | Administrador con visión global | Gestión administrativa amplia con acceso a reportes y configuración. |
| 3 | **Administrador de Unidad** (`admin_unidad`) | Administrador de secretaría | Gestión de usuarios y procesos dentro de su secretaría. Creación de flujos personalizados en el Motor de Flujos. |
| 4 | **Unidad Solicitante** (`unidad_solicitante`) | Dependencia que inicia el proceso | Crear solicitudes de contratación, cargar estudios previos, recopilar documentos del contratista, supervisar contratos. |
| 5 | **Planeación** (`planeacion`) | Oficina de Planeación | Verificar inclusión en PAA, emitir certificado de compatibilidad de gasto, solicitar documentos paralelos a otras áreas. |
| 6 | **Hacienda** (`hacienda`) | Secretaría de Hacienda | Emitir CDP y RP, validar disponibilidad presupuestal. |
| 7 | **Jurídica** (`juridica`) | Oficina Jurídica | Revisión jurídica, verificación de contratista, emisión de concepto "Ajustado a Derecho", aprobación de pólizas, generación de contratos. |
| 8 | **SECOP** (`secop`) | Responsable de publicación SECOP | Publicar procesos en SECOP II, registrar contratos y actas de inicio. |
| 9 | **Profesional de Contratación** (`profesional_contratacion`) | Profesional de apoyo | Apoyo transversal en procesos de contratación. |
| 10 | **Revisor Jurídico** (`revisor_juridico`) | Apoyo jurídico | Revisión documental desde perspectiva legal. |
| 11 | **Gobernador** (`gobernador`) | Autoridad máxima | Consulta de dashboard, reportes y estado general de procesos. Solo lectura. |
| 12 | **Consulta** (`consulta`) | Rol de solo lectura | Visualización de procesos y reportes sin capacidad de modificación. |
| 13 | **Compras** (`compras`) | Área de Compras | Carga de documentos solicitados por Planeación para procesos asignados. |
| 14 | **Talento Humano** (`talento_humano`) | Área de Talento Humano | Carga de certificados de no planta y documentos de personal. |
| 15 | **Rentas** (`rentas`) | Área de Rentas | Emisión y carga de paz y salvo de rentas. |
| 16 | **Contabilidad** (`contabilidad`) | Área de Contabilidad | Emisión y carga de paz y salvo de contabilidad. |
| 17 | **Inversiones Públicas** (`inversiones_publicas`) | Área de Inversiones | Carga de documentos de inversión pública solicitados. |
| 18 | **Presupuesto** (`presupuesto`) | Área de Presupuesto | Carga de documentos presupuestales solicitados. |
| 19 | **Radicación** (`radicacion`) | Área de Radicación | Radicación de expedientes contractuales. |

### 4.2 Actores Externos

| Actor | Interacción |
|-------|-------------|
| **Contratista** | No accede al sistema. Su documentación es cargada por la Unidad Solicitante. |
| **SECOP II** | Sistema externo de contratación pública. El sistema registra datos de publicación y consulta datos abiertos vía API. |
| **Correo Office365** | Servicio SMTP para envío de notificaciones, alertas y restablecimiento de contraseñas. |

---

## 5. REQUERIMIENTOS FUNCIONALES

### RF-01: Gestión de Procesos de Contratación

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-01.1 | El sistema debe permitir crear un proceso de contratación seleccionando un flujo activo, secretaría de origen, unidad de origen, objeto contractual, valor estimado y plazo de ejecución. | Alta |
| RF-01.2 | El sistema debe generar automáticamente un código consecutivo único por tipo de flujo (ej: CD-PN-001-2026). | Alta |
| RF-01.3 | El sistema debe exigir la carga del documento de Estudios Previos al momento de crear el proceso. | Alta |
| RF-01.4 | El sistema debe mostrar una bandeja de procesos filtrable por estado, etapa actual, secretaría, unidad y búsqueda por texto (código, objeto, contratista). | Alta |
| RF-01.5 | La bandeja debe mostrar solo los procesos que correspondan al rol del usuario autenticado: los administradores ven todos, las áreas ven los de su bandeja, las unidades solicitantes ven los que crearon. | Alta |
| RF-01.6 | El sistema debe permitir avanzar un proceso a la siguiente etapa mediante la acción "Enviar", validando que se cumplan los requisitos de la etapa actual. | Alta |
| RF-01.7 | El sistema debe permitir que el área receptora confirme la recepción del proceso mediante la acción "Recibir". | Alta |

### RF-02: Gestión Documental

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-02.1 | El sistema debe permitir cargar documentos en formato PDF e imagen, con un tamaño máximo de 10 MB por archivo. | Alta |
| RF-02.2 | El sistema debe clasificar cada documento según su tipo (más de 50 tipos: estudios previos, CDP, RUT, cédula, hoja de vida SIGEP, certificados, antecedentes, etc.). | Alta |
| RF-02.3 | El sistema debe permitir previsualizar documentos PDF e imágenes directamente dentro de un modal sin necesidad de descargarlos. | Media |
| RF-02.4 | El sistema debe mantener el historial de versiones de cada documento, permitiendo consultar versiones anteriores. | Alta |
| RF-02.5 | El sistema debe permitir reemplazar un documento existente, generando automáticamente una nueva versión y conservando la anterior. | Alta |
| RF-02.6 | El sistema debe permitir reemplazos administrativos con registro de motivo obligatorio. | Media |
| RF-02.7 | El sistema debe bloquear la eliminación de documentos cuando la siguiente área ya ha recibido el proceso. | Alta |
| RF-02.8 | El sistema debe permitir el registro de aprobación o rechazo de documentos con observaciones. | Alta |

### RF-03: Checklist por Etapa

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-03.1 | Cada etapa del workflow debe tener un checklist de ítems verificables configurados por el administrador. | Media |
| RF-03.2 | Los usuarios del área responsable deben poder marcar/desmarcar cada ítem del checklist. | Media |
| RF-03.3 | El envío de etapa debe validar que todos los ítems requeridos del checklist estén marcados. | Alta |

### RF-04: Solicitud de Documentos Paralelos

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-04.1 | Planeación debe poder solicitar documentos a múltiples áreas simultáneamente (compras, talento humano, rentas, contabilidad, inversiones públicas, presupuesto). | Alta |
| RF-04.2 | Cada solicitud debe indicar el tipo de documento requerido, el área responsable y si tiene dependencias con otros documentos. | Alta |
| RF-04.3 | Las áreas documentales deben ver en su bandeja únicamente los procesos que tengan solicitudes pendientes dirigidas a su rol. | Alta |
| RF-04.4 | Al cargar un documento solicitado, el sistema debe actualizar automáticamente el estado de la solicitud y habilitar documentos dependientes. | Alta |

### RF-05: Administración del Sistema

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-05.1 | El sistema debe permitir la gestión CRUD de usuarios con asignación de rol, secretaría y unidad. | Alta |
| RF-05.2 | El sistema debe permitir activar/desactivar usuarios sin eliminarlos. | Alta |
| RF-05.3 | El sistema debe permitir la gestión de roles con asignación de permisos granulares. | Alta |
| RF-05.4 | El sistema debe permitir la gestión de secretarías y sus unidades dependientes. | Alta |
| RF-05.5 | El administrador debe poder restablecer la contraseña de cualquier usuario. | Media |
| RF-05.6 | El sistema debe registrar eventos de autenticación (login, logout, intentos fallidos) con IP y user agent. | Media |

### RF-06: Dashboard e Indicadores

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-06.1 | El sistema debe mostrar un dashboard personalizado según el rol del usuario autenticado. | Alta |
| RF-06.2 | El dashboard del administrador debe mostrar: total de procesos, activos, finalizados, rechazados, del mes actual, alertas activas, documentos pendientes y rechazados. | Alta |
| RF-06.3 | El dashboard debe incluir indicadores por etapa del proceso. | Media |
| RF-06.4 | El dashboard debe incluir indicadores por área/actor. | Media |
| RF-06.5 | El dashboard debe incluir indicadores de eficiencia (promedio de días por proceso, por modalidad, por etapa). | Media |
| RF-06.6 | El dashboard debe incluir seguimiento de procesos con estado documental (aprobados, pendientes, rechazados). | Alta |
| RF-06.7 | El dashboard debe mostrar tendencia de procesos creados en los últimos 6 meses. | Baja |

### RF-07: Alertas y Notificaciones

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-07.1 | El sistema debe generar alertas automáticas por tiempo excedido en etapa, procesos sin actividad (7+ días) y certificados próximos a vencer. | Alta |
| RF-07.2 | El sistema debe generar alertas por documentos rechazados y documentos pendientes por más de 3 días. | Alta |
| RF-07.3 | El sistema debe generar alertas de responsabilidad cuando un proceso es recibido en una nueva área. | Media |
| RF-07.4 | El sistema debe enviar notificaciones por correo electrónico al cambiar el estado de un proceso CD-PN/CD-PJ. | Alta |
| RF-07.5 | El usuario debe poder consultar sus alertas desde un ícono de campana 🔔 en la barra superior, con indicador de conteo no leído. | Alta |
| RF-07.6 | El usuario debe poder marcar alertas como leídas individualmente o todas a la vez. | Media |
| RF-07.7 | Las alertas deben clasificarse por prioridad (alta, media, baja) con código de color visual. | Media |

### RF-08: Reportes

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-08.1 | El sistema debe generar un reporte de Estado General de Procesos filtrable por fecha, modalidad y estado, exportable a PDF, Excel y HTML. | Alta |
| RF-08.2 | El sistema debe generar un reporte de Procesos por Dependencia agrupado por unidad solicitante. | Media |
| RF-08.3 | El sistema debe generar un reporte de Actividad por Actor con conteo de acciones por usuario y tipo. | Media |
| RF-08.4 | El sistema debe generar un reporte de Auditoría por proceso individual con línea de tiempo completa. | Alta |
| RF-08.5 | El sistema debe generar un reporte de Certificados por Vencer con filtro de días de anticipación (por defecto 5 días). | Alta |
| RF-08.6 | El sistema debe generar un reporte de Eficiencia y Tiempos con promedios por modalidad y etapa. | Media |

### RF-09: Restablecimiento de Contraseña

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-09.1 | El sistema debe permitir al usuario solicitar restablecimiento de contraseña ingresando su correo o nombre de usuario registrado y un correo de destino donde recibirá el enlace. | Alta |
| RF-09.2 | El enlace de restablecimiento debe incluir un token seguro con expiración de 60 minutos. | Alta |
| RF-09.3 | El botón "¿Olvidaste tu contraseña?" solo debe mostrarse después de un intento fallido de inicio de sesión, pre-llenando el correo ingresado. | Baja |

### RF-10: Supervisión de Contratos

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-10.1 | El sistema debe permitir crear informes de supervisión con período, estado de avance (en ejecución, con retraso, completado, suspendido), porcentaje de avance y archivo soporte. | Media |
| RF-10.2 | El sistema debe permitir registrar pagos asociados al contrato con valor, fecha de solicitud, fecha estimada y estado (pendiente, en trámite, aprobado, pagado, rechazado). | Media |
| RF-10.3 | El sistema debe mostrar estadísticas de supervisión: total informes, aprobados, pendientes, pagos realizados, valor total y porcentaje de avance general. | Media |

### RF-11: Modificaciones Contractuales

| ID | Requerimiento | Prioridad |
|----|---------------|-----------|
| RF-11.1 | El sistema debe permitir registrar modificaciones contractuales de tipo: adición, prórroga, suspensión, cesión, terminación u otro. | Media |
| RF-11.2 | Las adiciones deben validar que el valor acumulado no supere el 50% del valor estimado original del contrato. | Alta |
| RF-11.3 | Las modificaciones deben poder ser aprobadas o rechazadas por jurídica o administrador con observaciones obligatorias en caso de rechazo. | Media |
| RF-11.4 | Al aprobar una adición, el sistema debe actualizar automáticamente el valor estimado del proceso. | Alta |

---

## 6. REQUERIMIENTOS NO FUNCIONALES

### RNF-01: Rendimiento

| ID | Requerimiento |
|----|---------------|
| RNF-01.1 | Las páginas del sistema deben cargar en menos de 3 segundos bajo condiciones normales de uso. |
| RNF-01.2 | La bandeja de procesos debe soportar la visualización de al menos 500 procesos activos sin degradación. |
| RNF-01.3 | El sistema implementa cache de rutas, vistas y configuración de Laravel para optimizar tiempos de respuesta. |
| RNF-01.4 | La consulta de alertas en la barra superior se mantiene en cache por 30 segundos para evitar consultas repetitivas a la base de datos. |
| RNF-01.5 | Los enlaces de navegación implementan prefetch (precarga al pasar el mouse) para reducir la percepción de tiempo de carga. |
| RNF-01.6 | Las fuentes externas (Google Fonts) se cargan de manera no bloqueante para no retrasar el primer renderizado. |

### RNF-02: Seguridad

| ID | Requerimiento |
|----|---------------|
| RNF-02.1 | Toda la autenticación se gestiona mediante sesiones con tokens CSRF de Laravel. |
| RNF-02.2 | Las contraseñas se almacenan con hash bcrypt. |
| RNF-02.3 | El sistema implementa control de acceso basado en roles (RBAC) mediante Spatie Laravel Permission. |
| RNF-02.4 | Cada controlador valida la autorización del usuario antes de ejecutar acciones. |
| RNF-02.5 | Los archivos se almacenan con nombres UUID para prevenir acceso directo por nombre original. |
| RNF-02.6 | El middleware `CheckUsuarioActivo` verifica que el usuario esté activo en cada solicitud autenticada. |
| RNF-02.7 | Se registran todos los eventos de autenticación (login, logout, intentos fallidos) con dirección IP y user agent. |

### RNF-03: Usabilidad

| ID | Requerimiento |
|----|---------------|
| RNF-03.1 | La interfaz es responsiva y accesible desde navegadores de escritorio y dispositivos móviles. |
| RNF-03.2 | El sistema presenta la interfaz completamente en idioma español. |
| RNF-03.3 | El sistema incluye un asistente de ayuda flotante (Agente Estiven) con guías paso a paso para las funcionalidades principales. |
| RNF-03.4 | Los mensajes de error y éxito son descriptivos y orientados al usuario final. |
| RNF-03.5 | La navegación lateral (sidebar) presenta las opciones agrupadas por contexto y visibles según el rol del usuario. |

### RNF-04: Mantenibilidad

| ID | Requerimiento |
|----|---------------|
| RNF-04.1 | El sistema sigue la arquitectura MVC de Laravel con separación clara entre controladores, modelos y vistas. |
| RNF-04.2 | La lógica de negocio compleja se encapsula en servicios (WorkflowEngine, AlertaService, ContratoDirectoPNStateMachine, etc.). |
| RNF-04.3 | Los estados, tipos de proceso, tipos de documento y estados de aprobación se definen mediante Enums de PHP para facilitar su mantenimiento. |
| RNF-04.4 | El sistema de flujos es completamente configurable vía Motor de Flujos para agregar nuevas modalidades sin modificar código. |

### RNF-05: Disponibilidad y Confiabilidad

| ID | Requerimiento |
|----|---------------|
| RNF-05.1 | El sistema debe estar disponible durante el horario laboral de la entidad (lunes a viernes, 7:00 AM – 6:00 PM). |
| RNF-05.2 | Toda acción sobre un proceso queda registrada en la tabla de auditoría con usuario, acción, fecha y metadatos. |
| RNF-05.3 | Los documentos reemplazados no se eliminan; se mantienen como versiones anteriores. |

---

## 7. RESTRICCIONES

| # | Restricción | Descripción |
|---|-------------|-------------|
| 1 | **Tecnológicas** | El sistema está desarrollado en PHP 8.2 con Laravel 12, Alpine.js y Tailwind CSS. Cualquier extensión debe ser compatible con este stack. |
| 2 | **Base de Datos** | Se utiliza MySQL como motor de base de datos. |
| 3 | **Correo Electrónico** | Las notificaciones dependen del servicio SMTP de Office365 de la Gobernación. |
| 4 | **Almacenamiento** | Los documentos se almacenan en el filesystem local del servidor. El espacio disponible limita la cantidad de archivos. |
| 5 | **Tamaño de Archivos** | Máximo 10 MB por archivo cargado (configurable a 5 MB para ciertos tipos). |
| 6 | **Formatos de Archivo** | Los documentos deben ser cargados en formato PDF, JPG, JPEG o PNG. |
| 7 | **Navegador** | El sistema es compatible con navegadores modernos (Chrome, Firefox, Edge). No se garantiza compatibilidad con Internet Explorer. |
| 8 | **Usuarios Concurrentes** | El servidor actual soporta un número limitado de usuarios concurrentes determinado por la configuración del servidor PHP. |

---

## 8. SUPOSICIONES Y DEPENDENCIAS

### 8.1 Suposiciones

| # | Suposición |
|---|------------|
| 1 | Los usuarios cuentan con acceso a un navegador web moderno y conectividad a la red donde se aloja el sistema. |
| 2 | La estructura organizacional de la Gobernación (secretarías y unidades) está definida y se mantendrá relativamente estable. |
| 3 | Los roles y sus responsabilidades dentro del proceso de contratación siguen la normativa vigente de Colombia. |
| 4 | Los documentos cargados al sistema son copias digitalizadas de documentos válidos; el sistema no verifica la autenticidad del contenido. |
| 5 | Los administradores del sistema tendrán la capacitación necesaria para crear flujos en el Motor de Flujos. |
| 6 | El proceso de contratación directa sigue las 9 etapas definidas por la entidad y puede ser ajustado si la normativa cambia. |
| 7 | Las adiciones presupuestales no superan el 50% del valor original del contrato, conforme a la normativa vigente. |

### 8.2 Dependencias

| # | Dependencia | Tipo |
|---|-------------|------|
| 1 | **Servidor PHP 8.2+** con extensiones requeridas por Laravel 12. | Infraestructura |
| 2 | **MySQL 8.0+** como motor de base de datos. | Infraestructura |
| 3 | **Servicio SMTP Office365** (`smtp.office365.com:587`) para envío de correos electrónicos. | Servicio Externo |
| 4 | **Node.js** para compilación de assets frontend con Vite. | Desarrollo |
| 5 | **Spatie Laravel Permission v6** para gestión de roles y permisos. | Paquete |
| 6 | **SECOP II – Datos Abiertos** para consulta de procesos publicados. | Servicio Externo (Opcional) |
| 7 | **Acceso a internet** para carga de fuentes tipográficas (Google Fonts – Inter). | Recurso Externo |

---

## 9. CASOS DE USO

### CU-01: Crear Proceso de Contratación

| Campo | Detalle |
|-------|---------|
| **ID** | CU-01 |
| **Nombre** | Crear Proceso de Contratación |
| **Actor Principal** | Unidad Solicitante, Planeación, Administrador |
| **Precondiciones** | El usuario está autenticado con rol autorizado. Existe al menos un flujo activo y una secretaría configurada. |
| **Postcondiciones** | Se crea un proceso con código consecutivo, se registra la etapa inicial como completada y el proceso queda visible en la bandeja del área de la siguiente etapa. |

**Flujo Principal:**
1. El usuario accede a "Crear Proceso".
2. El sistema muestra el formulario con los flujos disponibles.
3. El usuario selecciona el flujo, secretaría y unidad de origen.
4. El usuario ingresa el objeto contractual, valor estimado y plazo de ejecución en meses.
5. El usuario carga el documento de Estudios Previos (PDF, máx. 10 MB).
6. El usuario confirma la creación.
7. El sistema genera el código consecutivo (ej: `CD-PN-003-2026`).
8. El sistema crea el proceso en estado "EN_CURSO".
9. El sistema registra la etapa 0 (Estudios Previos) como completada automáticamente.
10. El sistema crea la siguiente etapa como pendiente de recepción.
11. El sistema registra la auditoría y genera una alerta para el área receptora.
12. El sistema redirige a la bandeja de procesos con mensaje de éxito.

**Flujo Alterno:**
- **4a.** Si los campos obligatorios no están completos, el sistema muestra errores de validación y permanece en el formulario.
- **5a.** Si el archivo supera 10 MB o no es PDF, el sistema rechaza la carga con mensaje descriptivo.

---

### CU-02: Recibir Proceso en Área

| Campo | Detalle |
|-------|---------|
| **ID** | CU-02 |
| **Nombre** | Recibir Proceso en Área Responsable |
| **Actor Principal** | Planeación, Hacienda, Jurídica, SECOP |
| **Precondiciones** | El proceso está en la bandeja del área del usuario. El proceso no ha sido recibido aún en esta etapa. |
| **Postcondiciones** | El proceso queda marcado como recibido con timestamp y usuario. Se generan los ítems del checklist de la etapa si no existen. |

**Flujo Principal:**
1. El usuario accede a la bandeja de procesos de su área.
2. El usuario selecciona un proceso en estado "Pendiente de Recepción".
3. El usuario hace clic en "Recibir".
4. El sistema marca la etapa como recibida (fecha, hora, usuario).
5. El sistema genera el checklist de ítems verificables de la etapa.
6. El sistema registra la auditoría.
7. El sistema muestra el proceso con el checklist y opciones de acción.

**Flujo Alterno:**
- **3a.** Si el usuario ya recibió el proceso, el botón no es visible.

---

### CU-03: Enviar Proceso a Siguiente Etapa

| Campo | Detalle |
|-------|---------|
| **ID** | CU-03 |
| **Nombre** | Enviar Proceso a la Siguiente Etapa |
| **Actor Principal** | Usuario del área responsable de la etapa actual |
| **Precondiciones** | El proceso ha sido recibido en la etapa actual. Los documentos requeridos están cargados y aprobados. Los ítems requeridos del checklist están marcados. |
| **Postcondiciones** | La etapa actual se marca como enviada. Se crea la siguiente etapa. El proceso avanza al área siguiente. |

**Flujo Principal:**
1. El usuario revisa los documentos y checklist de la etapa actual.
2. El usuario hace clic en "Enviar a siguiente etapa".
3. El sistema valida:
   - Que la etapa esté marcada como recibida.
   - Que los ítems requeridos del checklist estén marcados.
   - Que no haya documentos en estado "pendiente" o "rechazado".
4. El sistema marca la etapa como enviada (fecha, hora, usuario).
5. El sistema calcula la siguiente etapa del workflow.
6. El sistema crea el registro de la siguiente etapa como pendiente de recepción.
7. El sistema actualiza el área responsable del proceso.
8. El sistema registra la auditoría.
9. El sistema genera una alerta para el área de la siguiente etapa.
10. El sistema muestra mensaje de éxito.

**Flujo Alterno:**
- **3a.** Si faltan documentos o checklist, el sistema muestra un listado específico de lo que falta y no permite el envío.
- **5a.** Si no hay siguiente etapa (última), el proceso se marca como completado.

---

### CU-04: Cargar Documento

| Campo | Detalle |
|-------|---------|
| **ID** | CU-04 |
| **Nombre** | Cargar Documento al Proceso |
| **Actor Principal** | Cualquier usuario autorizado (área actual, creador, área documental con solicitud) |
| **Precondiciones** | El proceso existe y la etapa actual no ha sido enviada (salvo override de administrador). |
| **Postcondiciones** | El documento queda registrado como versión 1 (o nueva versión si es reemplazo). Si existe solicitud, se marca como subida. |

**Flujo Principal:**
1. El usuario accede al detalle del proceso.
2. El usuario selecciona el tipo de documento a cargar.
3. El usuario arrastra o selecciona el archivo (PDF/imagen, máx. 10 MB).
4. El sistema valida formato y tamaño.
5. El sistema almacena el archivo con nombre UUID.
6. El sistema registra el documento en base de datos con estado "aprobado", tipo, versión y metadatos.
7. Si existe una solicitud de documento pendiente del mismo tipo, el sistema la marca como "subido" y habilita documentos dependientes.
8. El sistema registra la auditoría.

**Flujo Alterno:**
- **3a.** Si el archivo no es PDF o imagen, el sistema rechaza la carga.
- **3b.** Si el archivo supera el tamaño máximo, el sistema muestra error.
- **6a.** Si es un reemplazo, el sistema crea una nueva versión vinculada a la anterior.

---

### CU-05: Previsualizar Documento

| Campo | Detalle |
|-------|---------|
| **ID** | CU-05 |
| **Nombre** | Previsualizar Documento en Modal |
| **Actor Principal** | Cualquier usuario con acceso al proceso |
| **Precondiciones** | El documento existe y el usuario tiene autorización para verlo. |
| **Postcondiciones** | N/A (solo lectura). |

**Flujo Principal:**
1. El usuario hace clic en el ícono de previsualización (👁️) junto al documento.
2. El sistema abre un modal de pantalla completa.
3. El panel principal muestra el contenido del documento (PDF en iframe o imagen).
4. El panel lateral derecho muestra metadatos: nombre, tipo, tamaño, versión, estado, autor, fecha.
5. El usuario puede navegar entre pestañas: "Detalles", "Versiones", "Acciones".
6. En la pestaña "Versiones", el usuario puede ver el historial y seleccionar versiones anteriores.
7. En la pestaña "Acciones", el usuario puede reemplazar el documento mediante un dropzone.

**Flujo Alterno:**
- **3a.** Si el formato no es previsualizable, el sistema muestra un botón de descarga.

---

### CU-06: Emitir CDP (Certificado de Disponibilidad Presupuestal)

| Campo | Detalle |
|-------|---------|
| **ID** | CU-06 |
| **Nombre** | Emitir CDP |
| **Actor Principal** | Hacienda |
| **Precondiciones** | El proceso está en la bandeja de Hacienda. La etapa está recibida. |
| **Postcondiciones** | Se registra el CDP con número, valor y rubro. El archivo queda almacenado. El proceso se marca como cdp_emitido. |

**Flujo Principal:**
1. El usuario de Hacienda accede al detalle del proceso.
2. Ingresa: número de CDP, valor CDP, rubro presupuestal.
3. Carga el archivo del CDP (PDF, máx. 5 MB).
4. El sistema valida los campos y almacena el archivo.
5. El sistema registra el CDP como documento de la etapa.
6. El sistema marca `cdp_emitido = true` en el proceso.
7. El sistema registra la auditoría.

---

### CU-07: Emitir Concepto Ajustado a Derecho

| Campo | Detalle |
|-------|---------|
| **ID** | CU-07 |
| **Nombre** | Emitir Concepto "Ajustado a Derecho" |
| **Actor Principal** | Jurídica |
| **Precondiciones** | El proceso está en la bandeja de Jurídica. Los documentos del contratista están completos. |
| **Postcondiciones** | Se registra el concepto jurídico con número y archivo. El proceso se marca como ajustado. |

**Flujo Principal:**
1. El usuario de Jurídica accede al detalle del proceso.
2. Revisa la documentación del contratista completa.
3. Ingresa el número de concepto y carga el archivo del Ajustado a Derecho.
4. El sistema registra el documento y marca `ajustado_emitido = true`.
5. El sistema registra la auditoría.

**Flujo Alterno:**
- **2a.** Si la documentación está incompleta, Jurídica devuelve el proceso a la Unidad Solicitante con observaciones. El sistema revierte el proceso a etapa de documentación.

---

### CU-08: Publicar en SECOP II

| Campo | Detalle |
|-------|---------|
| **ID** | CU-08 |
| **Nombre** | Publicar Proceso en SECOP II |
| **Actor Principal** | SECOP |
| **Precondiciones** | El proceso está en la bandeja de SECOP. |
| **Postcondiciones** | Se registra la URL de SECOP, número de proceso y archivo de soporte. |

**Flujo Principal:**
1. El usuario de SECOP accede al detalle del proceso.
2. Ingresa la URL de publicación en SECOP II y el número de proceso asignado.
3. Opcionalmente carga pantallazo o PDF de constancia.
4. El sistema marca `publicado_secop = true` con fecha de publicación.
5. El sistema registra la auditoría.

---

### CU-09: Restablecer Contraseña

| Campo | Detalle |
|-------|---------|
| **ID** | CU-09 |
| **Nombre** | Restablecer Contraseña de Usuario |
| **Actor Principal** | Cualquier usuario registrado |
| **Precondiciones** | El usuario tiene un correo registrado en el sistema o conoce su nombre de usuario. |
| **Postcondiciones** | Se envía un correo con enlace de restablecimiento al correo de destino indicado. |

**Flujo Principal:**
1. El usuario intenta iniciar sesión con credenciales incorrectas.
2. El sistema muestra error y hace visible el botón "¿Olvidaste tu contraseña?" con el correo pre-llenado.
3. El usuario hace clic y es redirigido al formulario de restablecimiento.
4. El formulario muestra dos campos: correo/usuario registrado (pre-llenado) y correo de destino donde recibirá el enlace.
5. El usuario completa ambos campos y envía.
6. El sistema busca al usuario por email o nombre.
7. El sistema genera un token seguro con expiración de 60 minutos.
8. El sistema envía un correo HTML al correo de destino con un botón de restablecimiento.
9. El usuario accede al enlace, ingresa nueva contraseña y confirma.
10. El sistema actualiza la contraseña.

**Flujo Alterno:**
- **6a.** Si no se encuentra el usuario, el sistema muestra error descriptivo.
- **9a.** Si el token expiró, el sistema informa y solicita generar uno nuevo.

---

### CU-10: Crear Flujo Personalizado (Motor de Flujos)

| Campo | Detalle |
|-------|---------|
| **ID** | CU-10 |
| **Nombre** | Crear Flujo de Trabajo Personalizado |
| **Actor Principal** | Administrador, Administrador de Unidad |
| **Precondiciones** | Existen pasos en el Catálogo de Pasos. La secretaría está configurada. |
| **Postcondiciones** | Se crea un flujo con su primera versión activa, listo para ser usado en la creación de procesos. |

**Flujo Principal:**
1. El administrador accede al módulo de Motor de Flujos.
2. Crea un nuevo flujo indicando: código, nombre, tipo de contratación y secretaría.
3. El sistema crea el flujo con una versión inicial activa.
4. El administrador agrega pasos al flujo desde el Catálogo de Pasos, definiendo: orden, nombre personalizado, instrucciones, días estimados y área responsable.
5. Para cada paso, el administrador puede definir: documentos requeridos con formatos permitidos, responsables (usuarios o unidades) y condiciones de bifurcación.
6. El administrador publica la versión.
7. El flujo queda disponible para crear nuevos procesos.

**Flujo Alterno:**
- **4a.** Si necesita un paso que no existe en el catálogo, primero lo crea en el Catálogo de Pasos.
- **6a.** Para modificar un flujo publicado, el administrador crea una nueva versión (borrador) que duplica los pasos existentes, realiza cambios y publica la nueva versión.

---

### CU-11: Registrar Modificación Contractual

| Campo | Detalle |
|-------|---------|
| **ID** | CU-11 |
| **Nombre** | Registrar Modificación Contractual |
| **Actor Principal** | Unidad Solicitante, Supervisor |
| **Precondiciones** | El proceso tiene un contrato registrado y está en ejecución. |
| **Postcondiciones** | Se registra la modificación pendiente de aprobación. Si es adición aprobada, se actualiza el valor del contrato. |

**Flujo Principal:**
1. El usuario accede al módulo de Modificaciones del proceso.
2. Selecciona el tipo de modificación: adición, prórroga, suspensión, cesión, terminación u otro.
3. Completa los campos según el tipo: valor (adición), plazo adicional en días (prórroga), descripción y justificación.
4. Carga archivo soporte (PDF, máx. 10 MB).
5. El sistema valida: si es adición, que el valor acumulado no supere el 50% del valor original.
6. El sistema registra la modificación con estado "pendiente".
7. Jurídica o Administrador aprueba la modificación con observaciones.
8. Si es adición, el sistema suma el valor al valor estimado del proceso.

**Flujo Alterno:**
- **5a.** Si la adición supera el 50%, el sistema rechaza con mensaje: "El valor acumulado de adiciones supera el 50% del valor original del contrato."
- **7a.** Si se rechaza, el solicitante recibe observaciones de rechazo (mínimo 10 caracteres).

---

### CU-12: Solicitar Ayuda (Agente Estiven)

| Campo | Detalle |
|-------|---------|
| **ID** | CU-12 |
| **Nombre** | Solicitar Ayuda al Equipo de Soporte |
| **Actor Principal** | Cualquier usuario autenticado |
| **Precondiciones** | El usuario está autenticado. |
| **Postcondiciones** | Se envía un correo al equipo de soporte con los datos del usuario y su solicitud. |

**Flujo Principal:**
1. El usuario hace clic en el botón flotante del Agente Estiven.
2. El sistema muestra la lista de guías comunes (restablecer contraseña, previsualizar documentos, reemplazar documento, ver versiones, alertas por correo, cambiar contraseña).
3. El usuario puede consultar cualquier guía con pasos detallados.
4. Si necesita más ayuda, hace clic en "¿Necesitas más ayuda? Escríbenos".
5. El sistema muestra un formulario con campos: asunto (máx. 150 caracteres) y descripción (máx. 1.500 caracteres con contador).
6. El usuario completa y envía.
7. El sistema envía un correo HTML al equipo de soporte incluyendo automáticamente: nombre, email y rol del usuario, con Reply-To configurado al correo del solicitante.
8. El sistema muestra confirmación: "¡Correo enviado! El equipo te responderá pronto."

---

## 10. REGLAS DE NEGOCIO

### RN-01: Reglas de Proceso

| ID | Regla |
|----|-------|
| RN-01.1 | Un proceso solo puede ser creado por usuarios con rol `admin`, `admin_general`, `planeacion` o `unidad_solicitante`. |
| RN-01.2 | El código del proceso es generado automáticamente y no puede ser modificado manualmente. Formato: `{PREFIJO}-{CONSECUTIVO}-{AÑO}`. |
| RN-01.3 | Un proceso no puede avanzar de etapa si tiene documentos en estado "pendiente" o "rechazado" en la etapa actual. |
| RN-01.4 | Un proceso debe ser marcado como "recibido" por el área antes de poder operar sobre él (excepto la Unidad Solicitante que es la creadora). |
| RN-01.5 | Solo el administrador puede cancelar un proceso. La cancelación es irreversible y requiere registro de motivo. |

### RN-02: Reglas de Documentos

| ID | Regla |
|----|-------|
| RN-02.1 | Los documentos cargados se almacenan con nombre UUID para prevenir conflictos y acceso directo. |
| RN-02.2 | Un documento reemplazado genera una nueva versión; la versión anterior se conserva permanentemente. |
| RN-02.3 | Un documento no puede eliminarse si la siguiente área ya recibió el proceso. Queda marcado como "bloqueado". |
| RN-02.4 | Los reemplazos administrativos (sobre documentos bloqueados) requieren motivo obligatorio y se marcan como `es_reemplazo_admin = true`. |
| RN-02.5 | El tamaño máximo por archivo es 10 MB para documentos generales y 5 MB para certificados y soportes menores. |

### RN-03: Reglas de Contratación Directa

| ID | Regla |
|----|-------|
| RN-03.1 | El flujo CD-PN tiene 21 estados distribuidos en 7 etapas + 2 estados especiales (cancelado, suspendido). |
| RN-03.2 | Las transiciones de estado solo pueden ser ejecutadas por los roles autorizados definidos en cada estado del enum. |
| RN-03.3 | Para solicitar CDP, todas las validaciones paralelas deben estar completas: PAA, certificado no planta, paz y salvo rentas, paz y salvo contabilidad y compatibilidad de gasto. |
| RN-03.4 | El contrato requiere ambas firmas (contratista y ordenador del gasto) para considerarse firmado totalmente. No hay orden obligatorio de firmas. |
| RN-03.5 | Para iniciar ejecución se requiere: número de contrato asignado, ARL solicitada y acta de inicio firmada. |
| RN-03.6 | Jurídica puede devolver el proceso a documentación del contratista si encuentra documentos insuficientes, reseteando el checklist de validación. |
| RN-03.7 | Un contrato en estado de firma puede ser devuelto, lo cual resetea ambas firmas y lo regresa a generación de contrato. |

### RN-04: Reglas de Modificaciones Contractuales

| ID | Regla |
|----|-------|
| RN-04.1 | Las adiciones presupuestales acumuladas no pueden superar el 50% del valor estimado original del contrato. |
| RN-04.2 | Al aprobar una adición, el valor de la adición se suma automáticamente al valor estimado del proceso. |
| RN-04.3 | El rechazo de una modificación requiere observaciones con un mínimo de 10 caracteres. |

### RN-05: Reglas de Alertas

| ID | Regla |
|----|-------|
| RN-05.1 | Las alertas de certificados próximos a vencer se clasifican como prioridad "alta" si faltan 2 días o menos, y "media" si faltan entre 3 y 5 días. |
| RN-05.2 | Un proceso se considera "sin actividad" si no se ha actualizado en 7 o más días. Prioridad "media" entre 7-15 días, "alta" si supera 15 días. |
| RN-05.3 | Un documento pendiente genera alerta después de 3 días. Prioridad "media" entre 3-5 días, "alta" si supera 5 días. |
| RN-05.4 | El sistema evita duplicación de alertas: antes de crear una alerta, verifica que no exista una del mismo tipo, referencia y proceso que no haya sido leída. |
| RN-05.5 | Los usuarios administradores ven todas las alertas del sistema. Los demás roles ven solo las dirigidas a su usuario o a su área responsable. |

### RN-06: Reglas de Supervisión

| ID | Regla |
|----|-------|
| RN-06.1 | El número de informe de supervisión se genera automáticamente de forma incremental por proceso. |
| RN-06.2 | Los pagos pueden tener los estados: pendiente, en trámite, aprobado, pagado o rechazado. |
| RN-06.3 | La fecha estimada de pago debe ser posterior a la fecha de solicitud. |

---

## 11. FLUJO GENERAL DEL SISTEMA

### 11.1 Ciclo de Vida de un Proceso

```
┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   CREACIÓN   │───>│  TRAMITACIÓN │───>│  CONTRATO    │───>│  EJECUCIÓN   │
│              │    │  POR ÁREAS   │    │  Y FIRMAS    │    │  Y CIERRE    │
└──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘
       │                   │                   │                   │
  • Estudios          • Planeación        • Jurídica          • Supervisor
    Previos           • Hacienda          • Firmas            • Informes
  • Flujo             • Documentos        • RPC               • Pagos
    seleccionado        paralelos         • Registro          • Modificaciones
  • Código auto       • CDP                                   • SECOP
```

### 11.2 Flujo entre Áreas

```
Unidad ──> Planeación ──> Hacienda (CDP) ──> Unidad (docs contratista)
   ──> Jurídica ──> Firmas ──> Hacienda (RPC) ──> Jurídica (radicar) ──> Ejecución
                       │
                       ├── Solicitudes paralelas a:
                       │   Compras, Talento Humano, Rentas,
                       │   Contabilidad, Inversiones, Presupuesto
                       │
                       └── Cada área sube su documento,
                           se habilitan dependientes automáticamente
```

---

## 12. FLUJO CD-PN

### Contratación Directa – Persona Natural (9 Etapas, 21 Estados)

| Etapa | Nombre | Área Responsable | Estados | Acciones |
|-------|--------|-----------------|---------|----------|
| **0** | Estudios Previos | Unidad Solicitante | `BORRADOR` → `ESTUDIO_PREVIO_CARGADO` | Crear solicitud, cargar estudios previos. |
| **1** | Validaciones y CDP | Planeación / Hacienda | `EN_VALIDACION_PLANEACION` → `COMPATIBILIDAD_APROBADA` → `CDP_SOLICITADO` → `CDP_APROBADO` | Verificar PAA, solicitar documentos paralelos (5 validaciones), emitir concepto de compatibilidad, solicitar y aprobar CDP. |
| **2** | Documentación Contratista | Unidad Solicitante | `DOCUMENTACION_INCOMPLETA` → `DOCUMENTACION_VALIDADA` | Recopilar: hoja de vida SIGEP, certificados de estudio y experiencia, RUT, cédula, antecedentes (5), REDAM, seguridad social, cuenta bancaria, certificado médico, tarjeta profesional. |
| **3** | Revisión Jurídica | Jurídica | `EN_REVISION_JURIDICA` → `PROCESO_NUMERO_GENERADO` | Verificar contratista, emitir concepto "Ajustado a Derecho", generar número de proceso. |
| **4** | Generación de Contrato | Jurídica | `GENERACION_CONTRATO` → `CONTRATO_GENERADO` | Generar minuta del contrato. |
| **5** | Firma de Contrato | Contratista + Ordenador | `CONTRATO_FIRMADO_PARCIAL` → `CONTRATO_FIRMADO_TOTAL` | Registrar firma del contratista y del ordenador del gasto (sin orden). Flujo alterno: `CONTRATO_DEVUELTO` → regenerar. |
| **6** | RPC | Hacienda | `RPC_SOLICITADO` → `RPC_FIRMADO` → `EXPEDIENTE_RADICADO` | Solicitar y firmar RPC, radicar expediente. |
| **7** | Ejecución | Unidad / Supervisor | `EN_EJECUCION` | Generar acta de inicio, solicitar ARL, asignar número de contrato. Supervisión e informes. |
| **Esp.** | Especiales | Admin | `CANCELADO`, `SUSPENDIDO` | Cancelar (solo admin, irreversible) o suspender proceso desde cualquier estado. |

### Validaciones Paralelas (Etapa 1)

Antes de solicitar el CDP, las 5 siguientes validaciones deben completarse:

1. **PAA solicitado** – Verificación de inclusión en el Plan Anual de Adquisiciones.
2. **Certificado de No Planta** – Emitido por Talento Humano.
3. **Paz y Salvo Rentas** – Emitido por Rentas.
4. **Paz y Salvo Contabilidad** – Emitido por Contabilidad.
5. **Compatibilidad de Gasto** – Emitido por Planeación.

---

## 13. FLUJO CD-PJ

### Contratación Directa – Persona Jurídica (10 Etapas)

| Etapa | Nombre | Área Responsable | Acciones |
|-------|--------|-----------------|----------|
| **1** | Estudios Previos y Análisis del Sector | Unidad Solicitante | Elaborar estudios previos con análisis del sector empresarial. |
| **2** | Verificación PAA y Compatibilidad | Planeación | Validar inclusión en PAA y emitir compatibilidad de gasto. |
| **3** | Solicitud y Emisión de CDP | Hacienda | Emitir CDP con número, valor y rubro presupuestal. |
| **4** | Documentos del Contratista (PJ) | Unidad Solicitante | Recopilar: RUT, Cámara de Comercio, estados financieros, cédula representante legal, certificados tributarios, experiencia, pólizas. |
| **5** | Revisión Jurídica y Concepto | Jurídica | Revisar documentación, verificar antecedentes, emitir concepto de legalidad. |
| **6** | Elaboración y Firma del Contrato | Ordenador del Gasto | Generar contrato, proceso de firmas. |
| **7** | Registro Presupuestal (RP) | Hacienda | Emitir RP vinculado al contrato firmado. |
| **8** | Publicación en SECOP II | SECOP | Registrar URL de publicación, número de proceso SECOP. |
| **9** | Aprobación de Pólizas y Garantías | Jurídica | Verificar y aprobar pólizas de cumplimiento. |
| **10** | Acta de Inicio y Ejecución | Supervisor | Firmar acta de inicio, iniciar ejecución del contrato. |

---

## 14. MOTOR DE FLUJOS CONFIGURABLE

### 14.1 Descripción

El Motor de Flujos es el componente que permite al sistema ser **completamente escalable** sin necesidad de desarrollo adicional. Permite a los administradores crear flujos de trabajo personalizados para cualquier modalidad de contratación futura.

### 14.2 Componentes del Motor

| Componente | Descripción |
|------------|-------------|
| **Catálogo de Pasos** | Biblioteca reutilizable de pasos (secuencial, paralelo, condicional) con código, nombre, icono y color. |
| **Flujos** | Definiciones de flujo vinculadas a una secretaría con código, nombre y tipo de contratación. |
| **Versiones de Flujo** | Cada flujo tiene versiones (borrador / activa). Solo una versión activa a la vez. La publicación es irreversible. |
| **Pasos del Flujo** | Instancias de pasos del catálogo asignados a una versión, con: orden, nombre personalizado, instrucciones, días estimados, área responsable, obligatoriedad y paralelismo. |
| **Documentos por Paso** | Cada paso puede tener documentos requeridos con formatos permitidos y dependencias entre documentos. |
| **Responsables por Paso** | Asignación de usuarios o unidades responsables por paso. |
| **Condiciones de Bifurcación** | Reglas lógicas por paso que determinan el paso destino según campo, operador y valor. |

### 14.3 Ciclo de Vida de un Flujo

```
Crear Flujo → Versión 1 (activa) → Agregar Pasos → Configurar docs/responsables/condiciones
                                                              ↓
         Nuevo Borrador (v2) ← Se necesita cambio ← Flujo en uso (instancias)
                ↓
    Modificar pasos ─→ Publicar v2 (v1 se archiva) ─→ Nuevos procesos usan v2
                                                       (procesos existentes siguen con v1)
```

### 14.4 Modalidades que Podrán Crearse

> Las siguientes modalidades no están implementadas como flujos nativos pero **podrán ser creadas en cualquier momento** por los administradores mediante el Motor de Flujos:

- Licitación Pública (LP)
- Selección Abreviada (SA)
- Concurso de Méritos (CM)
- Mínima Cuantía (MC)
- Contratación Directa – Interadministrativos
- Contratación de Urgencia
- Convenios de Cooperación
- Cualquier otra modalidad que requiera la entidad

---

## 15. INDICADORES DEL SISTEMA (DASHBOARD)

### 15.1 Dashboard Administrador – Indicadores Generales

| Indicador | Descripción | Visualización |
|-----------|-------------|---------------|
| Total de Procesos | Cantidad total de procesos registrados en el sistema. | Tarjeta numérica |
| Procesos Activos | Procesos no finalizados ni rechazados ni cerrados. | Tarjeta numérica |
| Finalizados | Procesos en estado completado o cerrado. | Tarjeta numérica |
| Rechazados | Procesos rechazados. | Tarjeta numérica |
| Creados este Mes | Procesos creados en el mes en curso. | Tarjeta numérica |
| Finalizados este Mes | Procesos finalizados en el mes en curso. | Tarjeta numérica |
| Alertas Activas | Cantidad de alertas no leídas. | Tarjeta numérica con color |
| Alertas de Prioridad Alta | Alertas no leídas con prioridad alta. | Tarjeta numérica roja |
| Total de Documentos | Cantidad total de archivos en el sistema. | Tarjeta numérica |
| Documentos Pendientes | Archivos en estado pendiente. | Tarjeta numérica |
| Documentos Rechazados | Archivos rechazados. | Tarjeta numérica |
| Distribución por Modalidad | Procesos agrupados por workflow (nombre de flujo). | Gráfico de barras / tabla |
| Tendencia 6 Meses | Procesos creados por mes en los últimos 6 meses. | Gráfico de línea |

### 15.2 Indicadores por Etapa del Proceso

| Indicador | Descripción |
|-----------|-------------|
| Procesos por Etapa | Cantidad de procesos actualmente en cada etapa, agrupados por workflow y área responsable. |
| Distribución | Tabla con columnas: etapa, workflow, área responsable, cantidad. |

### 15.3 Indicadores por Actor (Rol)

| Rol | Métricas |
|-----|----------|
| **Unidad Solicitante** | Solicitudes creadas este mes, procesos activos ahora, finalizados este mes, rechazados este mes. |
| **Planeación** | Recibidos este mes, enviados este mes, en bandeja actualmente, rechazados este mes. |
| **Hacienda** | Recibidos este mes, enviados este mes, en bandeja actualmente, rechazados este mes. |
| **Jurídica** | Recibidos este mes, enviados este mes, en bandeja actualmente, rechazados este mes. |
| **SECOP** | Recibidos este mes, enviados este mes, en bandeja actualmente, rechazados este mes. |
| **Áreas Documentales** (compras, talento humano, rentas, contabilidad, inversiones, presupuesto, radicación) | Procesos asignados este mes, documentos subidos este mes, documentos pendientes actualmente, total histórico asignados. |

### 15.4 Indicadores de Cumplimiento Documental

| Indicador | Descripción |
|-----------|-------------|
| Documentos Aprobados | Conteo de archivos en estado aprobado por proceso en seguimiento. |
| Documentos Pendientes | Conteo de archivos en estado pendiente por proceso. |
| Documentos Rechazados | Conteo de archivos rechazados por proceso. |
| Estado Documental por Proceso | Para cada proceso en seguimiento: código, objeto, estado, área, total docs aprobados, pendientes y rechazados. |

### 15.5 Indicadores de Alertas y Riesgos

| Indicador | Descripción |
|-----------|-------------|
| Alertas por Tipo | Desglose: tiempo, documentos, responsabilidad. |
| Alertas de Alta Prioridad | Conteo de alertas críticas no leídas. |
| Alertas por Área | Distribución de alertas no leídas por área responsable. |

### 15.6 Indicadores de Eficiencia

| Indicador | Descripción |
|-----------|-------------|
| Promedio General de Días | Promedio de días que toma un proceso desde creación hasta finalización. |
| Por Modalidad | Cantidad de procesos, promedio, mínimo y máximo de días por tipo de workflow. |
| Por Etapa | Promedio de duración en días de cada etapa del proceso. |

### 15.7 Seguimiento de Procesos (Top 20)

Tabla resumen de los 20 procesos más recientes con:
- Código, objeto contractual, estado, área actual, última actualización.
- Nombre del workflow y nombre de la etapa actual.
- Estado de recepción/envío en la etapa actual.
- Conteo de documentos por estado (aprobados, pendientes, rechazados).

---

## 16. REPORTES EXPORTABLES

### 16.1 Reporte de Estado General

| Campo | Detalle |
|-------|---------|
| **Nombre** | Estado General de Procesos |
| **Filtros** | Fecha inicio, fecha fin, modalidad (workflow), estado. |
| **Columnas** | Código, objeto, modalidad, estado, etapa actual, fecha de creación. |
| **Métricas** | Total, en trámite, finalizados, rechazados. |
| **Formatos** | PDF, Excel, HTML. |

### 16.2 Reporte de Procesos por Dependencia

| Campo | Detalle |
|-------|---------|
| **Nombre** | Procesos por Dependencia |
| **Agrupación** | Por usuario creador (dependencia solicitante). |
| **Columnas por Grupo** | Total, finalizados, en trámite. |
| **Filtros** | Rango de fechas. |
| **Formatos** | PDF, Excel, HTML. |

### 16.3 Reporte de Actividad por Actor

| Campo | Detalle |
|-------|---------|
| **Nombre** | Actividad por Actor |
| **Agrupación** | Por usuario (registros de auditoría). |
| **Columnas** | Nombre, email, total de acciones, acciones por tipo. |
| **Filtros** | Rango de fechas, usuario específico (opcional). |
| **Formatos** | PDF, Excel, HTML. |

### 16.4 Reporte de Auditoría

| Campo | Detalle |
|-------|---------|
| **Nombre** | Auditoría de Proceso |
| **Alcance** | Por proceso individual. |
| **Columnas** | Fecha, hora, usuario, acción, descripción, metadatos. |
| **Métricas** | Total eventos, usuarios involucrados, duración total en días, desglose por tipo de acción. |
| **Formatos** | PDF, HTML. |

### 16.5 Reporte de Certificados por Vencer

| Campo | Detalle |
|-------|---------|
| **Nombre** | Certificados Próximos a Vencer |
| **Filtros** | Días de anticipación (por defecto 5 días). |
| **Columnas** | Nombre del archivo, fecha de vigencia, proceso asociado, etapa. |
| **Métricas** | Total por vencer, vencen hoy, vencen mañana, próximos 3 días. |
| **Formatos** | PDF, Excel, HTML. |

### 16.6 Reporte de Eficiencia y Tiempos

| Campo | Detalle |
|-------|---------|
| **Nombre** | Eficiencia y Tiempos |
| **Período** | Últimos 3 meses (configurable). |
| **Datos** | Procesos finalizados en el período. |
| **Métricas** | Promedio general de días, por modalidad (cantidad, promedio, mínimo, máximo). |
| **Formatos** | PDF, Excel, HTML. |

---

## 17. CONFIGURACIÓN DE ALERTAS AUTOMÁTICAS

### 17.1 Alertas de Tiempo

| Tipo | Condición | Prioridad | Destinatario |
|------|-----------|-----------|--------------|
| **Certificado por Vencer** | Archivo aprobado con fecha_vigencia ≤ 5 días futuros. | Alta (≤2 días), Media (3-5 días) | Usuario que subió el archivo + área responsable. |
| **Tiempo Excedido en Etapa** | Proceso en etapa más días que los días estimados del workflow. | Alta | Área responsable de la etapa. |
| **Sin Actividad** | Proceso sin actualización en 7+ días. | Media (7-15 días), Alta (>15 días) | Área responsable actual del proceso. |

### 17.2 Alertas de Documentos

| Tipo | Condición | Prioridad | Destinatario |
|------|-----------|-----------|--------------|
| **Documento Rechazado** | Archivo con estado "rechazado". | Alta | Área que cargó el documento. |
| **Documento Pendiente** | Archivo en estado "pendiente" por más de 3 días. | Media (3-5 días), Alta (>5 días) | Área responsable de la etapa. |

### 17.3 Alertas de Responsabilidad

| Tipo | Condición | Prioridad | Destinatario |
|------|-----------|-----------|--------------|
| **Nueva Tarea** | Proceso recibido en área en las últimas 24 horas. | Media | Área que recibió el proceso. |

### 17.4 Notificaciones por Transición de Estado (CD-PN)

Cada transición de estado en el flujo CD-PN genera notificaciones automáticas por correo y alerta interna a los roles involucrados. Ejemplos:

| Transición | Título | Roles Notificados | Prioridad |
|------------|--------|-------------------|-----------|
| → EN_VALIDACION_PLANEACION | Nuevo proceso para validar | Planeación | Alta |
| → CDP_APROBADO | CDP aprobado – Recopilar documentos | Unidad Solicitante | Alta |
| → EN_REVISION_JURIDICA | Proceso para revisión jurídica | Jurídica | Alta |
| → CONTRATO_GENERADO | Contrato listo para firmas | Unidad Solicitante, Jurídica | Alta |
| → CONTRATO_FIRMADO_TOTAL | Ambas firmas completadas | Planeación | Alta |
| → EN_EJECUCION | Contrato en ejecución | Unidad Solicitante + todas las áreas | Media |
| → CANCELADO | Proceso cancelado | Unidad Solicitante | Alta |

### 17.5 Personalización por Rol

Las alertas se distribuyen según el rol del usuario:

| Rol | Ve alertas de |
|-----|---------------|
| **Administrador** | Todas las alertas del sistema. |
| **Planeación** | Alertas donde `area_responsable = 'planeacion'` o `user_id = su ID`. |
| **Hacienda** | Alertas donde `area_responsable = 'hacienda'` o `user_id = su ID`. |
| **Jurídica** | Alertas donde `area_responsable = 'juridica'` o `user_id = su ID`. |
| **SECOP** | Alertas donde `area_responsable = 'secop'` o `user_id = su ID`. |
| **Unidad Solicitante** | Alertas donde `area_responsable = 'unidad_solicitante'` o `user_id = su ID`. |
| **Jefes de Dependencia** | Alertas de su unidad y las dirigidas a su usuario. |
| **Supervisor de Contrato** | Alertas del proceso asignado y las dirigidas a su usuario. |
| **Áreas Documentales** | Alertas de solicitudes de documentos dirigidas a su área. |

---

## 18. MÓDULO DE SUPERVISIÓN

### 18.1 Informes de Supervisión

| Campo | Tipo | Validación |
|-------|------|------------|
| Período inicio | Fecha | Requerido |
| Período fin | Fecha | Requerido, posterior a inicio |
| Fecha del informe | Fecha | Requerido |
| Estado de avance | Selección | en_ejecucion, con_retraso, completado, suspendido |
| Porcentaje de avance | Número | 0-100 |
| Descripción de actividades | Texto | Requerido, mínimo 20 caracteres |
| Observaciones | Texto | Opcional |
| Archivo soporte | Archivo | PDF/DOC/DOCX, máximo 10 MB |

**Comportamiento:**
- El número de informe se genera automáticamente de forma incremental.
- El estado del informe se registra como "enviado" al crearse.
- El supervisor se registra como el usuario autenticado.

### 18.2 Control de Pagos

| Campo | Tipo | Validación |
|-------|------|------------|
| Valor | Numérico | Requerido, mínimo 1 |
| Fecha de solicitud | Fecha | Requerido |
| Fecha estimada de pago | Fecha | Requerido, posterior a solicitud |
| Informe asociado | Referencia | Opcional |
| Observaciones | Texto | Opcional |
| Archivo soporte | Archivo | PDF, máximo 5 MB |

**Estados del pago:** pendiente → en trámite → aprobado → pagado | rechazado

### 18.3 Estadísticas de Supervisión

- Total de informes, aprobados, pendientes.
- Total de pagos, realizados, valor total acumulado.
- Próximo pago estimado.
- Porcentaje de avance general.

---

## 19. MÓDULO DE MODIFICACIONES CONTRACTUALES

### 19.1 Tipos de Modificación

| Tipo | Campos Específicos | Validación |
|------|--------------------|------------|
| **Adición** | Valor de la modificación (numérico) | Acumulado ≤ 50% del valor original |
| **Prórroga** | Plazo adicional en días (entero, ≥1) | Requerido |
| **Suspensión** | Descripción y justificación | Mínimo 10 y 20 caracteres respectivamente |
| **Cesión** | Descripción y justificación | Mínimo 10 y 20 caracteres |
| **Terminación** | Descripción y justificación | Mínimo 10 y 20 caracteres |
| **Otro** | Descripción y justificación | Mínimo 10 y 20 caracteres |

### 19.2 Flujo de Aprobación

```
Solicitar Modificación (estado: pendiente)
        │
        ├── Aprobar (Jurídica / Admin) → Si adición: actualizar valor del proceso
        │
        └── Rechazar (con observaciones obligatorias, mín. 10 caracteres)
```

### 19.3 Control Presupuestal

- El sistema calcula y muestra: valor acumulado de adiciones aprobadas, porcentaje utilizado del 50% máximo, porcentaje disponible.

---

## 20. ASISTENTE DE AYUDA (AGENTE ESTIVEN)

### 20.1 Descripción

El Agente Estiven es un asistente flotante disponible en todas las páginas del sistema. Proporciona guías paso a paso de las funcionalidades principales y permite a los usuarios solicitar ayuda al equipo de soporte por correo electrónico.

### 20.2 Guías Disponibles

| # | Guía | Pasos |
|---|------|-------|
| 1 | Restablecer contraseña | 7 pasos: desde el intento de login fallido hasta la nueva contraseña. |
| 2 | Previsualizar documentos | 6 pasos: buscar proceso, seleccionar archivo, usar modal. |
| 3 | Reemplazar un documento | 7 pasos: abrir preview, ir a acciones, dropzone, confirmar. |
| 4 | Ver versiones de un documento | 6 pasos: abrir preview, pestaña versiones, navegar historial. |
| 5 | Alertas por correo | 5 pasos: entender notificaciones automáticas del sistema. |
| 6 | Cambiar contraseña | 6 pasos: proceso de cambio de contraseña propia. |

### 20.3 Solicitud de Ayuda por Correo

- Formulario con: asunto (máx. 150 caracteres) y descripción (máx. 1.500 caracteres con contador en tiempo real).
- Datos automáticos incluidos: nombre, email y rol del usuario.
- Reply-To configurado al correo del solicitante para facilitar la respuesta.
- Indicador de carga durante el envío.

### 20.4 Gestión Administrativa de Guías

Los administradores pueden gestionar las guías desde el panel de administración, creando, editando o eliminando guías por rol. Las guías dinámicas de base de datos tienen prioridad sobre las guías hardcoded.

---

## 21. GLOSARIO

| Término | Definición |
|---------|------------|
| **CDP** | Certificado de Disponibilidad Presupuestal. Documento que certifica que existe presupuesto para ejecutar un gasto. |
| **RP / RPC** | Registro Presupuestal del Compromiso. Garantiza que los recursos están comprometidos para un contrato específico. |
| **PAA** | Plan Anual de Adquisiciones. Plan que establece las compras y contrataciones previstas para el año fiscal. |
| **CD-PN** | Contratación Directa – Persona Natural. Modalidad para contratar servicios profesionales con personas naturales. |
| **CD-PJ** | Contratación Directa – Persona Jurídica. Modalidad para contratar con empresas o entidades jurídicas. |
| **SECOP II** | Sistema Electrónico de Contratación Pública. Plataforma nacional para la gestión de la contratación estatal. |
| **SIGEP** | Sistema de Información y Gestión del Empleo Público. Plataforma para hojas de vida del sector público. |
| **ARL** | Aseguradora de Riesgos Laborales. Cobertura de riesgos del contratista durante la ejecución. |
| **REDAM** | Registro de Deudores Alimentarios Morosos. Certificado de consulta obligatoria. |
| **Workflow** | Flujo de trabajo que define las etapas y reglas de un proceso de contratación. |
| **Motor de Flujos** | Componente del sistema que permite crear y configurar flujos de trabajo personalizados sin programación. |
| **Etapa** | Fase del proceso de contratación que es responsabilidad de una dependencia específica. |
| **Checklist** | Lista de verificación de ítems que deben completarse antes de avanzar una etapa. |
| **Dropzone** | Área de arrastre para carga de archivos en la interfaz de usuario. |
| **Agente Estiven** | Asistente virtual flotante de ayuda integrado en el sistema. |

---

> **Documento de Levantamiento de Requerimientos**  
> Sistema de Seguimiento de Documentos de Contratación  
> Gobernación de Caldas  
> Versión 1.0 – 11 de marzo de 2026
