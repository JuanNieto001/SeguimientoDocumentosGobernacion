# PLAN DE PRUEBAS Y CASOS DE PRUEBA
## Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas

**Version:** 1.0.0
**Fecha:** 2026-03-27
**Preparado por:** Equipo QA
**Estado:** Listo para automatizacion con Playwright

---

## TABLA DE CONTENIDOS

1. [Plan de Pruebas](#1-plan-de-pruebas)
2. [Casos de Prueba - Autenticacion](#2-casos-de-prueba---autenticacion)
3. [Casos de Prueba - Dashboard](#3-casos-de-prueba---dashboard)
4. [Casos de Prueba - Procesos Contractuales](#4-casos-de-prueba---procesos-contractuales)
5. [Casos de Prueba - Contratacion Directa CD-PN](#5-casos-de-prueba---contratacion-directa-cd-pn)
6. [Casos de Prueba - Gestion Documental](#6-casos-de-prueba---gestion-documental)
7. [Casos de Prueba - Roles y Permisos](#7-casos-de-prueba---roles-y-permisos)
8. [Casos de Prueba - Alertas](#8-casos-de-prueba---alertas)
9. [Casos de Prueba - Motor de Flujos](#9-casos-de-prueba---motor-de-flujos)
10. [Casos de Prueba - Motor de Dashboards](#10-casos-de-prueba---motor-de-dashboards)
11. [Casos de Prueba - PAA](#11-casos-de-prueba---paa)
12. [Casos de Prueba - SECOP](#12-casos-de-prueba---secop)
13. [Casos de Prueba - Reportes](#13-casos-de-prueba---reportes)
14. [Matriz de Trazabilidad](#14-matriz-de-trazabilidad)

---

# 1. PLAN DE PRUEBAS

## 1.1 Objetivo

Verificar el correcto funcionamiento de todas las funcionalidades del Sistema de Seguimiento de Documentos Contractuales, asegurando que cumple con los requisitos funcionales, reglas de negocio y restricciones de seguridad establecidas.

## 1.2 Alcance

### En Alcance
- Modulo de Autenticacion
- Modulo de Dashboard
- Modulo de Procesos Contractuales
- Modulo de Contratacion Directa (CD-PN)
- Modulo de Gestion Documental
- Sistema de Roles y Permisos
- Sistema de Alertas
- Motor de Flujos Configurables
- Motor de Dashboards BI
- Modulo PAA
- Integracion SECOP II
- Modulo de Reportes

### Fuera de Alcance
- Pruebas de carga y estres
- Pruebas de seguridad avanzadas (penetration testing)
- Pruebas de compatibilidad con navegadores legacy

## 1.3 Estrategia de Pruebas

| Tipo de Prueba | Herramienta | Prioridad |
|----------------|-------------|-----------|
| Funcionales E2E | Playwright | Alta |
| Integracion | Playwright | Alta |
| Validacion de formularios | Playwright | Alta |
| Pruebas de API | Playwright | Media |
| Pruebas de permisos | Playwright | Alta |

## 1.4 Entornos de Prueba

| Entorno | URL | Proposito |
|---------|-----|-----------|
| Local | http://localhost:8000 | Desarrollo |
| Testing | http://test.contratos.local | QA |
| Staging | https://staging.contratos.caldas.gov.co | Pre-produccion |

## 1.5 Datos de Prueba

### Usuarios de Prueba

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Admin General | admin@test.com | Test1234! | admin |
| Unidad Solicitante | unidad@test.com | Test1234! | unidad_solicitante |
| Planeacion | planeacion@test.com | Test1234! | planeacion |
| Hacienda | hacienda@test.com | Test1234! | hacienda |
| Juridica | juridica@test.com | Test1234! | juridica |
| SECOP | secop@test.com | Test1234! | secop |
| Gobernador | gobernador@test.com | Test1234! | gobernador |
| Consulta | consulta@test.com | Test1234! | consulta |

## 1.6 Criterios de Aceptacion

- 100% de casos de prueba criticos ejecutados
- Tasa de exito >= 95%
- Cero defectos criticos bloqueadores
- Todos los flujos principales funcionando correctamente

---

# 2. CASOS DE PRUEBA - AUTENTICACION

## 2.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-AUTH-01 | Login exitoso | Usuario | Usuario ingresa credenciales validas y accede al sistema |
| CU-AUTH-02 | Login fallido | Usuario | Sistema rechaza credenciales invalidas |
| CU-AUTH-03 | Logout | Usuario autenticado | Usuario cierra sesion exitosamente |
| CU-AUTH-04 | Recuperar contrasena | Usuario | Usuario solicita recuperacion de contrasena |
| CU-AUTH-05 | Sesion expirada | Sistema | Sistema cierra sesion por inactividad |

## 2.2 Casos de Prueba Detallados

### CP-AUTH-001: Login exitoso con credenciales validas

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-001 |
| **Nombre** | Login exitoso con credenciales validas |
| **Descripcion** | Verificar que un usuario puede iniciar sesion con credenciales correctas |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario registrado en el sistema<br>- Usuario con estado activo = true<br>- Usuario no tiene sesion activa |
| **Pasos** | 1. Navegar a /login<br>2. Ingresar email valido en campo email<br>3. Ingresar password correcto en campo password<br>4. Click en boton "Iniciar sesion" |
| **Datos de Prueba** | Email: admin@test.com<br>Password: Test1234! |
| **Resultado Esperado** | - Usuario es redirigido a /dashboard<br>- Se muestra nombre del usuario en topbar<br>- Menu lateral muestra opciones segun rol |

---

### CP-AUTH-002: Login fallido por email incorrecto

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-002 |
| **Nombre** | Login fallido por email incorrecto |
| **Descripcion** | Verificar que el sistema rechaza un email no registrado |
| **Prioridad** | Alta |
| **Tipo** | Negativo |
| **Precondiciones** | - Email no existe en base de datos |
| **Pasos** | 1. Navegar a /login<br>2. Ingresar email inexistente<br>3. Ingresar cualquier password<br>4. Click en "Iniciar sesion" |
| **Datos de Prueba** | Email: noexiste@test.com<br>Password: cualquier123 |
| **Resultado Esperado** | - Usuario permanece en /login<br>- Se muestra mensaje "Las credenciales proporcionadas son incorrectas"<br>- Campos no se limpian |

---

### CP-AUTH-003: Login fallido por password incorrecto

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-003 |
| **Nombre** | Login fallido por password incorrecto |
| **Descripcion** | Verificar que el sistema rechaza password incorrecto |
| **Prioridad** | Alta |
| **Tipo** | Negativo |
| **Precondiciones** | - Email existe en sistema<br>- Password es incorrecto |
| **Pasos** | 1. Navegar a /login<br>2. Ingresar email valido<br>3. Ingresar password incorrecto<br>4. Click en "Iniciar sesion" |
| **Datos de Prueba** | Email: admin@test.com<br>Password: wrongpassword |
| **Resultado Esperado** | - Usuario permanece en /login<br>- Se muestra mensaje de error<br>- Se registra evento auth_failed en logs |

---

### CP-AUTH-004: Login fallido usuario inactivo

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-004 |
| **Nombre** | Login fallido por usuario inactivo |
| **Descripcion** | Verificar que usuarios con activo=false no pueden ingresar |
| **Prioridad** | Alta |
| **Tipo** | Negativo |
| **Precondiciones** | - Usuario existe con activo = false |
| **Pasos** | 1. Navegar a /login<br>2. Ingresar credenciales de usuario inactivo<br>3. Click en "Iniciar sesion" |
| **Datos de Prueba** | Email: inactivo@test.com<br>Password: Test1234! |
| **Resultado Esperado** | - Usuario permanece en /login<br>- Se muestra mensaje "Su cuenta esta desactivada" |

---

### CP-AUTH-005: Login con campos vacios

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-005 |
| **Nombre** | Login con campos vacios |
| **Descripcion** | Verificar validacion de campos requeridos |
| **Prioridad** | Media |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Ninguna |
| **Pasos** | 1. Navegar a /login<br>2. Dejar campos vacios<br>3. Click en "Iniciar sesion" |
| **Datos de Prueba** | Email: (vacio)<br>Password: (vacio) |
| **Resultado Esperado** | - Se muestran mensajes de validacion<br>- "El campo email es obligatorio"<br>- "El campo password es obligatorio" |

---

### CP-AUTH-006: Login con email formato invalido

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-006 |
| **Nombre** | Login con email formato invalido |
| **Descripcion** | Verificar validacion de formato email |
| **Prioridad** | Media |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Ninguna |
| **Pasos** | 1. Navegar a /login<br>2. Ingresar email sin formato valido<br>3. Ingresar password<br>4. Click en "Iniciar sesion" |
| **Datos de Prueba** | Email: emailsinformato<br>Password: Test1234! |
| **Resultado Esperado** | - Se muestra mensaje "El email debe ser una direccion valida" |

---

### CP-AUTH-007: Logout exitoso

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-007 |
| **Nombre** | Logout exitoso |
| **Descripcion** | Verificar que el usuario puede cerrar sesion correctamente |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario autenticado en el sistema |
| **Pasos** | 1. Usuario autenticado en dashboard<br>2. Click en menu de usuario (topbar)<br>3. Click en "Cerrar sesion" |
| **Datos de Prueba** | Usuario previamente logueado |
| **Resultado Esperado** | - Usuario redirigido a /login<br>- Sesion invalidada<br>- Evento logout registrado en auth_events |

---

### CP-AUTH-008: Acceso sin autenticacion

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-008 |
| **Nombre** | Acceso a ruta protegida sin autenticacion |
| **Descripcion** | Verificar que rutas protegidas redirigen a login |
| **Prioridad** | Alta |
| **Tipo** | Seguridad |
| **Precondiciones** | - Usuario no autenticado<br>- Cookies de sesion limpias |
| **Pasos** | 1. Limpiar cookies/sesion<br>2. Navegar directamente a /dashboard<br>3. Navegar directamente a /procesos<br>4. Navegar directamente a /admin/usuarios |
| **Datos de Prueba** | URLs protegidas |
| **Resultado Esperado** | - Todas las rutas redirigen a /login<br>- No se muestra contenido protegido |

---

### CP-AUTH-009: Remember me funcional

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-009 |
| **Nombre** | Funcionalidad Remember Me |
| **Descripcion** | Verificar que la opcion recordarme mantiene la sesion |
| **Prioridad** | Baja |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario registrado y activo |
| **Pasos** | 1. Navegar a /login<br>2. Ingresar credenciales<br>3. Marcar checkbox "Recuerdame"<br>4. Click en "Iniciar sesion"<br>5. Cerrar navegador<br>6. Abrir navegador y navegar a /dashboard |
| **Datos de Prueba** | Email: admin@test.com<br>Password: Test1234! |
| **Resultado Esperado** | - Usuario permanece autenticado despues de cerrar navegador<br>- Cookie remember_token presente |

---

### CP-AUTH-010: Redireccion post-login por rol

| Campo | Valor |
|-------|-------|
| **ID** | CP-AUTH-010 |
| **Nombre** | Redireccion despues de login segun rol |
| **Descripcion** | Verificar que cada rol es redirigido a la ruta correcta |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuarios con diferentes roles |
| **Pasos** | 1. Login como usuario planeacion (sin otros roles de doc)<br>2. Verificar redireccion<br>3. Logout<br>4. Login como admin<br>5. Verificar redireccion |
| **Datos de Prueba** | Planeacion: planeacion@test.com<br>Admin: admin@test.com |
| **Resultado Esperado** | - Planeacion puro: redirige a /planeacion<br>- Otros roles: redirigen a /dashboard |

---

# 3. CASOS DE PRUEBA - DASHBOARD

## 3.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-DASH-01 | Ver dashboard | Usuario autenticado | Usuario visualiza su dashboard personalizado |
| CU-DASH-02 | Ver metricas | Usuario autenticado | Usuario ve KPIs del mes |
| CU-DASH-03 | Acciones rapidas | Usuario autenticado | Usuario accede a funciones frecuentes |
| CU-DASH-04 | Ver procesos en curso | Usuario autenticado | Usuario ve lista de procesos activos |

## 3.2 Casos de Prueba Detallados

### CP-DASH-001: Dashboard muestra saludo personalizado

| Campo | Valor |
|-------|-------|
| **ID** | CP-DASH-001 |
| **Nombre** | Dashboard muestra saludo con nombre de usuario |
| **Descripcion** | Verificar que el dashboard muestra el nombre del usuario logueado |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario autenticado |
| **Pasos** | 1. Login con usuario conocido<br>2. Observar seccion de bienvenida en dashboard |
| **Datos de Prueba** | Usuario: Juan Perez (admin@test.com) |
| **Resultado Esperado** | - Se muestra "Bienvenido, Juan Perez"<br>- Se muestra rol del usuario |

---

### CP-DASH-002: Dashboard muestra KPIs del mes

| Campo | Valor |
|-------|-------|
| **ID** | CP-DASH-002 |
| **Nombre** | Dashboard muestra metricas del mes actual |
| **Descripcion** | Verificar que se muestran las tarjetas KPI con datos correctos |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario autenticado<br>- Existen procesos en el sistema |
| **Pasos** | 1. Login como admin<br>2. Observar tarjetas KPI en dashboard<br>3. Verificar valores mostrados |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Se muestran tarjetas con: Procesos en curso, Finalizados mes, Pendientes area<br>- Valores son numericos coherentes |

---

### CP-DASH-003: Acciones rapidas visibles segun rol

| Campo | Valor |
|-------|-------|
| **ID** | CP-DASH-003 |
| **Nombre** | Acciones rapidas segun rol de usuario |
| **Descripcion** | Verificar que las acciones rapidas cambian segun el rol |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuarios con diferentes roles |
| **Pasos** | 1. Login como unidad_solicitante<br>2. Verificar acciones rapidas<br>3. Logout y login como juridica<br>4. Verificar acciones rapidas |
| **Datos de Prueba** | unidad@test.com, juridica@test.com |
| **Resultado Esperado** | - Unidad: "Nueva solicitud", "Mi bandeja"<br>- Juridica: "Procesos pendientes", "Ver en revision" |

---

### CP-DASH-004: Lista procesos en curso

| Campo | Valor |
|-------|-------|
| **ID** | CP-DASH-004 |
| **Nombre** | Dashboard muestra procesos en curso |
| **Descripcion** | Verificar que se listan los procesos activos del usuario/area |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario autenticado<br>- Existen procesos en estado EN_CURSO |
| **Pasos** | 1. Login<br>2. Scroll a seccion "Procesos en curso"<br>3. Verificar lista de procesos |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Se muestra tabla con procesos<br>- Columnas: Codigo, Objeto, Etapa actual, Fecha<br>- Click en proceso lleva a detalle |

---

### CP-DASH-005: Dashboard vacio para usuario nuevo

| Campo | Valor |
|-------|-------|
| **ID** | CP-DASH-005 |
| **Nombre** | Dashboard muestra estado vacio correctamente |
| **Descripcion** | Verificar mensaje cuando no hay datos |
| **Prioridad** | Baja |
| **Tipo** | Edge Case |
| **Precondiciones** | - Usuario nuevo sin procesos asociados |
| **Pasos** | 1. Login como usuario nuevo sin procesos<br>2. Observar dashboard |
| **Datos de Prueba** | Usuario recien creado |
| **Resultado Esperado** | - Se muestra mensaje "No hay procesos en curso"<br>- Se muestra enlace a "Crear primer proceso" si tiene permiso |

---

# 4. CASOS DE PRUEBA - PROCESOS CONTRACTUALES

## 4.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-PROC-01 | Crear proceso | Unidad Solicitante | Crear nueva solicitud de contratacion |
| CU-PROC-02 | Ver proceso | Usuario autorizado | Visualizar detalle de proceso |
| CU-PROC-03 | Recibir proceso | Area destino | Marcar proceso como recibido |
| CU-PROC-04 | Enviar proceso | Usuario responsable | Avanzar proceso a siguiente etapa |
| CU-PROC-05 | Devolver proceso | Usuario autorizado | Regresar proceso a etapa anterior |

## 4.2 Casos de Prueba Detallados

### CP-PROC-001: Crear proceso exitosamente

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-001 |
| **Nombre** | Crear proceso con datos validos |
| **Descripcion** | Verificar creacion exitosa de un proceso contractual |
| **Prioridad** | Critica |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario con rol unidad_solicitante o admin<br>- Usuario tiene secretaria y unidad asignada |
| **Pasos** | 1. Login como unidad_solicitante<br>2. Click en "Nueva solicitud"<br>3. Seleccionar flujo de contratacion<br>4. Completar objeto del contrato<br>5. Completar descripcion<br>6. Ingresar valor estimado<br>7. Ingresar plazo de ejecucion<br>8. Subir archivo de estudio previo (PDF)<br>9. Click en "Crear proceso" |
| **Datos de Prueba** | Flujo: Contratacion Directa PN<br>Objeto: Prestacion de servicios profesionales para asesoria juridica<br>Valor: 25000000<br>Plazo: 6 meses<br>Archivo: estudio_previo.pdf (valido) |
| **Resultado Esperado** | - Proceso creado exitosamente<br>- Se genera codigo automatico (ej: CD-PN-001-2026)<br>- Redirige a vista de detalle del proceso<br>- Estado inicial: BORRADOR o ESTUDIO_PREVIO_CARGADO<br>- Auditoria registrada |

---

### CP-PROC-002: Crear proceso sin estudio previo (obligatorio)

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-002 |
| **Nombre** | Crear proceso sin archivo obligatorio |
| **Descripcion** | Verificar que no se puede crear proceso sin estudio previo |
| **Prioridad** | Alta |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Usuario con rol unidad_solicitante |
| **Pasos** | 1. Login<br>2. Ir a "Nueva solicitud"<br>3. Completar todos los campos EXCEPTO archivo<br>4. Click en "Crear proceso" |
| **Datos de Prueba** | Todos los campos excepto estudio_previo |
| **Resultado Esperado** | - Formulario no se envia<br>- Se muestra error "El estudio previo es obligatorio"<br>- Proceso NO se crea |

---

### CP-PROC-003: Crear proceso con valor negativo

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-003 |
| **Nombre** | Crear proceso con valor estimado negativo |
| **Descripcion** | Verificar validacion de valor minimo |
| **Prioridad** | Media |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Usuario con rol unidad_solicitante |
| **Pasos** | 1. Ir a formulario de creacion<br>2. Ingresar valor negativo en campo valor_estimado<br>3. Completar resto de campos<br>4. Click en "Crear proceso" |
| **Datos de Prueba** | valor_estimado: -5000000 |
| **Resultado Esperado** | - Se muestra error "El valor debe ser mayor a 0" |

---

### CP-PROC-004: Crear proceso con objeto muy corto

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-004 |
| **Nombre** | Crear proceso con objeto menor a 10 caracteres |
| **Descripcion** | Verificar validacion de longitud minima del objeto |
| **Prioridad** | Media |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Usuario con rol unidad_solicitante |
| **Pasos** | 1. Ir a formulario<br>2. Ingresar objeto muy corto<br>3. Completar resto de campos<br>4. Intentar crear |
| **Datos de Prueba** | objeto: "Servicio" (8 caracteres) |
| **Resultado Esperado** | - Error "El objeto debe tener al menos 10 caracteres" |

---

### CP-PROC-005: Ver detalle de proceso

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-005 |
| **Nombre** | Ver detalle completo de proceso |
| **Descripcion** | Verificar que se muestra toda la informacion del proceso |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Existe proceso en sistema<br>- Usuario tiene permiso procesos.ver |
| **Pasos** | 1. Login<br>2. Ir a lista de procesos<br>3. Click en proceso especifico |
| **Datos de Prueba** | Proceso existente |
| **Resultado Esperado** | - Se muestra codigo, objeto, descripcion<br>- Se muestra valor, plazo, estado actual<br>- Se muestra etapa actual<br>- Se muestra historial/timeline<br>- Se muestran documentos adjuntos |

---

### CP-PROC-006: Recibir proceso en area

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-006 |
| **Nombre** | Recibir proceso asignado al area |
| **Descripcion** | Verificar que se puede marcar proceso como recibido |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso en etapa asignada al area del usuario<br>- Proceso no ha sido recibido aun |
| **Pasos** | 1. Login como usuario del area destino<br>2. Ir a "Mi bandeja"<br>3. Ver proceso pendiente de recepcion<br>4. Click en "Recibir"<br>5. Confirmar recepcion |
| **Datos de Prueba** | Proceso en etapa de Planeacion, usuario planeacion |
| **Resultado Esperado** | - Proceso marcado como recibido<br>- Fecha y usuario de recepcion registrados<br>- Auditoria registrada<br>- Mensaje de confirmacion |

---

### CP-PROC-007: Enviar proceso a siguiente etapa

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-007 |
| **Nombre** | Enviar proceso completando requisitos |
| **Descripcion** | Verificar avance de proceso a siguiente etapa |
| **Prioridad** | Critica |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso recibido en etapa actual<br>- Todos los checks completados<br>- Documentos requeridos subidos<br>- Usuario tiene permiso procesos.enviar |
| **Pasos** | 1. Login como usuario responsable<br>2. Abrir proceso<br>3. Completar todos los checks<br>4. Verificar documentos<br>5. Click en "Enviar a siguiente etapa"<br>6. Confirmar envio |
| **Datos de Prueba** | Proceso con todos los requisitos completados |
| **Resultado Esperado** | - Proceso avanza a siguiente etapa<br>- Estado actualizado<br>- Fecha envio registrada<br>- Notificacion a siguiente area<br>- Auditoria registrada |

---

### CP-PROC-008: Enviar proceso sin completar checks

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-008 |
| **Nombre** | Intentar enviar proceso sin completar checklist |
| **Descripcion** | Verificar que no se puede enviar sin requisitos |
| **Prioridad** | Alta |
| **Tipo** | Negativo |
| **Precondiciones** | - Proceso recibido<br>- Checks requeridos NO completados |
| **Pasos** | 1. Abrir proceso con checks pendientes<br>2. Intentar click en "Enviar" |
| **Datos de Prueba** | Proceso con checks pendientes |
| **Resultado Esperado** | - Boton "Enviar" deshabilitado o<br>- Se muestra mensaje "Complete todos los items requeridos"<br>- Proceso NO avanza |

---

### CP-PROC-009: Devolver proceso a etapa anterior

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-009 |
| **Nombre** | Devolver proceso con observaciones |
| **Descripcion** | Verificar devolucion de proceso |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso en etapa que permite devolucion<br>- Usuario con permiso para devolver |
| **Pasos** | 1. Abrir proceso<br>2. Click en "Devolver"<br>3. Seleccionar etapa destino<br>4. Ingresar motivo de devolucion<br>5. Confirmar devolucion |
| **Datos de Prueba** | Motivo: "Falta documento de certificacion" |
| **Resultado Esperado** | - Proceso regresa a etapa seleccionada<br>- Motivo registrado<br>- Estado actualizado<br>- Notificacion a area anterior |

---

### CP-PROC-010: Devolver proceso sin motivo

| Campo | Valor |
|-------|-------|
| **ID** | CP-PROC-010 |
| **Nombre** | Intentar devolver proceso sin motivo |
| **Descripcion** | Verificar que motivo es obligatorio para devolucion |
| **Prioridad** | Media |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Proceso en etapa que permite devolucion |
| **Pasos** | 1. Abrir proceso<br>2. Click en "Devolver"<br>3. Dejar motivo vacio<br>4. Intentar confirmar |
| **Datos de Prueba** | Motivo: (vacio) |
| **Resultado Esperado** | - Error "El motivo de devolucion es obligatorio"<br>- Proceso NO se devuelve |

---

# 5. CASOS DE PRUEBA - CONTRATACION DIRECTA CD-PN

## 5.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-CD-01 | Crear solicitud CD-PN | Unidad Solicitante | Iniciar proceso de contratacion directa PN |
| CU-CD-02 | Transicionar estado | Usuario autorizado por estado | Cambiar estado del proceso |
| CU-CD-03 | Registrar validacion paralela | Planeacion | Registrar PAA, compatibilidad, etc |
| CU-CD-04 | Solicitar CDP | Planeacion | Solicitar certificado de disponibilidad |
| CU-CD-05 | Firmar contrato | Contratista/Ordenador | Registrar firmas del contrato |
| CU-CD-06 | Devolver desde juridica | Juridica | Devolver por observaciones |

## 5.2 Casos de Prueba Detallados

### CP-CD-001: Crear solicitud CD-PN exitosamente

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-001 |
| **Nombre** | Crear solicitud contratacion directa PN |
| **Descripcion** | Verificar creacion de proceso CD-PN |
| **Prioridad** | Critica |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario con rol unidad_solicitante o admin |
| **Pasos** | 1. Login<br>2. Ir a "Crear CD-PN"<br>3. Completar objeto<br>4. Ingresar valor<br>5. Ingresar plazo en meses<br>6. Subir estudio previo<br>7. Seleccionar secretaria/unidad<br>8. Click "Crear" |
| **Datos de Prueba** | Objeto: Prestacion servicios profesionales<br>Valor: 30000000<br>Plazo: 6 meses<br>Archivo: estudio.pdf |
| **Resultado Esperado** | - Proceso creado con codigo CD-PS-XX-2026<br>- Estado: BORRADOR<br>- Etapa: 1 |

---

### CP-CD-002: Transicion BORRADOR a ESTUDIO_PREVIO_CARGADO

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-002 |
| **Nombre** | Primera transicion del proceso CD-PN |
| **Descripcion** | Verificar avance de borrador a estudio cargado |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso CD-PN en estado BORRADOR<br>- Estudio previo cargado |
| **Pasos** | 1. Abrir proceso CD-PN<br>2. Verificar estudio previo cargado<br>3. Click en "Enviar a validacion" |
| **Datos de Prueba** | Proceso con estudio previo valido |
| **Resultado Esperado** | - Estado: ESTUDIO_PREVIO_CARGADO<br>- Auditoria registrada |

---

### CP-CD-003: Registrar validaciones paralelas

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-003 |
| **Nombre** | Registrar PAA, No Planta, Paz y Salvos |
| **Descripcion** | Verificar registro de validaciones previas a CDP |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso en EN_VALIDACION_PLANEACION<br>- Usuario con rol planeacion |
| **Pasos** | 1. Login como planeacion<br>2. Abrir proceso<br>3. Marcar PAA validado<br>4. Marcar Certificado No Planta<br>5. Marcar Paz y Salvo Rentas<br>6. Marcar Paz y Salvo Contabilidad<br>7. Aprobar Compatibilidad del Gasto |
| **Datos de Prueba** | Proceso en validacion |
| **Resultado Esperado** | - Campos actualizados en BD<br>- Proceso puede avanzar a CDP |

---

### CP-CD-004: Solicitar CDP sin compatibilidad (REGLA CRITICA)

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-004 |
| **Nombre** | Intentar solicitar CDP sin Compatibilidad aprobada |
| **Descripcion** | Verificar regla de negocio CDP requiere Compatibilidad |
| **Prioridad** | Critica |
| **Tipo** | Negativo - Regla de Negocio |
| **Precondiciones** | - Proceso en validacion<br>- Compatibilidad NO aprobada |
| **Pasos** | 1. Abrir proceso<br>2. Verificar compatibilidad_aprobada = false<br>3. Intentar transicionar a CDP_SOLICITADO |
| **Datos de Prueba** | Proceso sin compatibilidad |
| **Resultado Esperado** | - Error "CDP requiere Compatibilidad del Gasto aprobada"<br>- Transicion bloqueada |

---

### CP-CD-005: Solicitar CDP con compatibilidad aprobada

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-005 |
| **Nombre** | Solicitar CDP correctamente |
| **Descripcion** | Verificar solicitud de CDP con requisitos completos |
| **Prioridad** | Critica |
| **Tipo** | Positivo |
| **Precondiciones** | - Compatibilidad aprobada<br>- Validaciones paralelas completas |
| **Pasos** | 1. Verificar compatibilidad aprobada<br>2. Click en "Solicitar CDP"<br>3. Confirmar |
| **Datos de Prueba** | Proceso con validaciones completas |
| **Resultado Esperado** | - Estado: CDP_SOLICITADO<br>- Notificacion a Hacienda |

---

### CP-CD-006: Aprobar CDP

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-006 |
| **Nombre** | Aprobar CDP desde Hacienda |
| **Descripcion** | Verificar aprobacion de CDP |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Estado CDP_SOLICITADO<br>- Usuario hacienda |
| **Pasos** | 1. Login como hacienda<br>2. Ver procesos pendientes<br>3. Abrir proceso<br>4. Ingresar numero CDP<br>5. Ingresar valor CDP<br>6. Click "Aprobar CDP" |
| **Datos de Prueba** | numero_cdp: CDP-2026-00123<br>valor_cdp: 30000000 |
| **Resultado Esperado** | - Estado: CDP_APROBADO<br>- numero_cdp y valor_cdp guardados |

---

### CP-CD-007: Registro de ambas firmas del contrato

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-007 |
| **Nombre** | Firmar contrato - contratista y ordenador |
| **Descripcion** | Verificar que se requieren ambas firmas |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Estado CONTRATO_GENERADO |
| **Pasos** | 1. Registrar firma contratista<br>2. Verificar estado CONTRATO_FIRMADO_PARCIAL<br>3. Registrar firma ordenador del gasto<br>4. Verificar estado CONTRATO_FIRMADO_TOTAL |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Firma parcial: una sola firma<br>- Firma total: ambas firmas<br>- Solo con ambas puede avanzar a RPC |

---

### CP-CD-008: Devolver contrato desde juridica

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-008 |
| **Nombre** | Devolver contrato por observaciones |
| **Descripcion** | Verificar devolucion del contrato |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso en revision juridica<br>- Usuario juridica |
| **Pasos** | 1. Login como juridica<br>2. Abrir proceso<br>3. Click "Devolver contrato"<br>4. Ingresar observaciones<br>5. Confirmar |
| **Datos de Prueba** | Observaciones: Falta clausula de confidencialidad |
| **Resultado Esperado** | - Estado: CONTRATO_DEVUELTO<br>- Observaciones guardadas<br>- Notificacion a unidad |

---

### CP-CD-009: Transicion por usuario no autorizado

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-009 |
| **Nombre** | Intentar transicion con rol no autorizado |
| **Descripcion** | Verificar restriccion de roles por estado |
| **Prioridad** | Critica |
| **Tipo** | Negativo - Seguridad |
| **Precondiciones** | - Proceso en estado que requiere rol especifico |
| **Pasos** | 1. Login como usuario con rol incorrecto<br>2. Abrir proceso en estado CDP_SOLICITADO<br>3. Intentar aprobar CDP (requiere hacienda) |
| **Datos de Prueba** | Usuario unidad_solicitante intentando aprobar CDP |
| **Resultado Esperado** | - Error 403 Forbidden<br>- Mensaje "No tiene permisos para esta accion"<br>- Proceso sin cambios |

---

### CP-CD-010: Cancelar proceso (solo admin)

| Campo | Valor |
|-------|-------|
| **ID** | CP-CD-010 |
| **Nombre** | Cancelar proceso CD-PN |
| **Descripcion** | Verificar que solo admin puede cancelar |
| **Prioridad** | Alta |
| **Tipo** | Positivo/Negativo |
| **Precondiciones** | - Proceso en cualquier estado no final |
| **Pasos** | A) Como admin:<br>1. Login como admin<br>2. Abrir proceso<br>3. Click "Cancelar proceso"<br>4. Confirmar<br><br>B) Como otro rol:<br>1. Login como unidad<br>2. Verificar que no hay opcion cancelar |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Admin: puede cancelar, estado CANCELADO<br>- Otros: no ven opcion de cancelar |

---

# 6. CASOS DE PRUEBA - GESTION DOCUMENTAL

## 6.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-DOC-01 | Subir documento | Usuario autorizado | Cargar archivo al proceso |
| CU-DOC-02 | Descargar documento | Usuario autorizado | Obtener copia del documento |
| CU-DOC-03 | Aprobar documento | Revisor | Marcar documento como aprobado |
| CU-DOC-04 | Rechazar documento | Revisor | Rechazar documento con observaciones |
| CU-DOC-05 | Reemplazar documento | Usuario autorizado | Subir nueva version |

## 6.2 Casos de Prueba Detallados

### CP-DOC-001: Subir documento PDF valido

| Campo | Valor |
|-------|-------|
| **ID** | CP-DOC-001 |
| **Nombre** | Subir documento PDF valido |
| **Descripcion** | Verificar carga exitosa de documento |
| **Prioridad** | Critica |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso en etapa que acepta documentos<br>- Usuario con permiso archivos.subir |
| **Pasos** | 1. Abrir proceso<br>2. Ir a seccion de documentos<br>3. Seleccionar tipo de documento<br>4. Click en "Subir archivo"<br>5. Seleccionar PDF valido<br>6. Confirmar carga |
| **Datos de Prueba** | Archivo: documento_valido.pdf (500KB) |
| **Resultado Esperado** | - Documento subido exitosamente<br>- Aparece en lista de documentos<br>- Estado: Pendiente de aprobacion |

---

### CP-DOC-002: Subir documento tipo no permitido

| Campo | Valor |
|-------|-------|
| **ID** | CP-DOC-002 |
| **Nombre** | Subir archivo con extension no permitida |
| **Descripcion** | Verificar rechazo de tipos no validos |
| **Prioridad** | Alta |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Tipo de documento solo acepta PDF |
| **Pasos** | 1. Intentar subir archivo .exe |
| **Datos de Prueba** | Archivo: programa.exe |
| **Resultado Esperado** | - Error "Tipo de archivo no permitido"<br>- Archivo no se carga |

---

### CP-DOC-003: Subir documento excediendo tamano maximo

| Campo | Valor |
|-------|-------|
| **ID** | CP-DOC-003 |
| **Nombre** | Subir archivo mayor a 10MB |
| **Descripcion** | Verificar limite de tamano |
| **Prioridad** | Alta |
| **Tipo** | Negativo - Validacion |
| **Precondiciones** | - Limite configurado en 10MB |
| **Pasos** | 1. Intentar subir archivo de 15MB |
| **Datos de Prueba** | Archivo: documento_grande.pdf (15MB) |
| **Resultado Esperado** | - Error "El archivo excede el tamano maximo de 10MB"<br>- Archivo no se carga |

---

### CP-DOC-004: Aprobar documento

| Campo | Valor |
|-------|-------|
| **ID** | CP-DOC-004 |
| **Nombre** | Aprobar documento en revision |
| **Descripcion** | Verificar aprobacion por usuario autorizado |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Documento en estado pendiente<br>- Usuario con permiso archivos.aprobar |
| **Pasos** | 1. Login como juridica<br>2. Ver documentos pendientes<br>3. Click en "Aprobar"<br>4. Confirmar |
| **Datos de Prueba** | Documento pendiente de aprobacion |
| **Resultado Esperado** | - Estado documento: Aprobado<br>- Fecha y usuario aprobador registrados |

---

### CP-DOC-005: Rechazar documento con observaciones

| Campo | Valor |
|-------|-------|
| **ID** | CP-DOC-005 |
| **Nombre** | Rechazar documento indicando motivo |
| **Descripcion** | Verificar rechazo con observaciones |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Documento pendiente<br>- Usuario con permiso archivos.rechazar |
| **Pasos** | 1. Ver documento<br>2. Click en "Rechazar"<br>3. Ingresar observaciones<br>4. Confirmar |
| **Datos de Prueba** | Observaciones: Documento ilegible, subir nueva version |
| **Resultado Esperado** | - Estado: Rechazado<br>- Observaciones guardadas<br>- Notificacion al cargador |

---

### CP-DOC-006: Verificar vigencia de documento

| Campo | Valor |
|-------|-------|
| **ID** | CP-DOC-006 |
| **Nombre** | Detectar documento vencido |
| **Descripcion** | Verificar alerta de documento expirado |
| **Prioridad** | Alta |
| **Tipo** | Edge Case |
| **Precondiciones** | - Documento con fecha_vigencia pasada |
| **Pasos** | 1. Crear documento con vigencia de ayer<br>2. Ejecutar verificacion de vigencias<br>3. Intentar avanzar proceso |
| **Datos de Prueba** | Antecedentes con vigencia vencida |
| **Resultado Esperado** | - Documento marcado como vencido<br>- Alerta generada<br>- Avance bloqueado |

---

### CP-DOC-007: Reemplazar documento existente

| Campo | Valor |
|-------|-------|
| **ID** | CP-DOC-007 |
| **Nombre** | Reemplazar documento con nueva version |
| **Descripcion** | Verificar versionado de documentos |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Existe documento previo<br>- Usuario con permiso archivos.reemplazar |
| **Pasos** | 1. Seleccionar documento existente<br>2. Click en "Reemplazar"<br>3. Subir nueva version<br>4. Indicar motivo del reemplazo |
| **Datos de Prueba** | Motivo: Correccion de errores tipograficos |
| **Resultado Esperado** | - Nueva version creada<br>- Version anterior conservada en historial<br>- Numero de version incrementado |

---

# 7. CASOS DE PRUEBA - ROLES Y PERMISOS

## 7.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-ROL-01 | Acceso por rol | Sistema | Restringir acceso segun rol |
| CU-ROL-02 | Verificar permiso | Sistema | Validar permisos especificos |
| CU-ROL-03 | Acceso a secretaria | Sistema | Limitar acceso por secretaria |

## 7.2 Casos de Prueba Detallados

### CP-ROL-001: Admin accede a todo

| Campo | Valor |
|-------|-------|
| **ID** | CP-ROL-001 |
| **Nombre** | Admin tiene acceso completo |
| **Descripcion** | Verificar que admin puede acceder a todas las rutas |
| **Prioridad** | Critica |
| **Tipo** | Positivo - Permisos |
| **Precondiciones** | - Usuario con rol admin |
| **Pasos** | 1. Login como admin<br>2. Navegar a /admin/usuarios<br>3. Navegar a /admin/roles<br>4. Navegar a /dashboards/motor<br>5. Navegar a /procesos<br>6. Navegar a /proceso-cd |
| **Datos de Prueba** | admin@test.com |
| **Resultado Esperado** | - Todas las rutas accesibles<br>- Todas las acciones disponibles |

---

### CP-ROL-002: Unidad Solicitante - permisos limitados

| Campo | Valor |
|-------|-------|
| **ID** | CP-ROL-002 |
| **Nombre** | Unidad Solicitante tiene permisos especificos |
| **Descripcion** | Verificar acceso limitado de unidad_solicitante |
| **Prioridad** | Alta |
| **Tipo** | Positivo/Negativo - Permisos |
| **Precondiciones** | - Usuario con rol unidad_solicitante |
| **Pasos** | PUEDE:<br>1. Acceder a /procesos<br>2. Crear nuevo proceso<br>3. Ver sus procesos<br>4. Subir documentos<br><br>NO PUEDE:<br>5. Acceder a /admin/usuarios<br>6. Acceder a /dashboards/motor<br>7. Aprobar documentos |
| **Datos de Prueba** | unidad@test.com |
| **Resultado Esperado** | - Acciones permitidas funcionan<br>- Acciones denegadas retornan 403 |

---

### CP-ROL-003: Consulta - solo lectura

| Campo | Valor |
|-------|-------|
| **ID** | CP-ROL-003 |
| **Nombre** | Rol Consulta es solo lectura |
| **Descripcion** | Verificar que consulta no puede modificar |
| **Prioridad** | Alta |
| **Tipo** | Negativo - Seguridad |
| **Precondiciones** | - Usuario con rol consulta |
| **Pasos** | 1. Login como consulta<br>2. Ver procesos (OK)<br>3. Intentar crear proceso (debe fallar)<br>4. Intentar editar proceso (debe fallar)<br>5. Intentar subir documento (debe fallar) |
| **Datos de Prueba** | consulta@test.com |
| **Resultado Esperado** | - Solo puede ver/leer<br>- Botones de crear/editar no visibles o deshabilitados<br>- Intentos directos retornan 403 |

---

### CP-ROL-004: Gobernador - vista ejecutiva

| Campo | Valor |
|-------|-------|
| **ID** | CP-ROL-004 |
| **Nombre** | Gobernador accede a dashboard ejecutivo |
| **Descripcion** | Verificar acceso especial de gobernador |
| **Prioridad** | Media |
| **Tipo** | Positivo - Permisos |
| **Precondiciones** | - Usuario con rol gobernador |
| **Pasos** | 1. Login como gobernador<br>2. Verificar dashboard especial<br>3. Acceder a reportes consolidados<br>4. Acceder a consulta SECOP |
| **Datos de Prueba** | gobernador@test.com |
| **Resultado Esperado** | - Dashboard muestra KPIs ejecutivos<br>- Acceso a reportes globales<br>- NO puede crear/editar procesos |

---

### CP-ROL-005: Restriccion por secretaria

| Campo | Valor |
|-------|-------|
| **ID** | CP-ROL-005 |
| **Nombre** | Usuario solo ve datos de su secretaria |
| **Descripcion** | Verificar aislamiento de datos por secretaria |
| **Prioridad** | Critica |
| **Tipo** | Seguridad |
| **Precondiciones** | - Usuario asignado a Secretaria A<br>- Existen procesos de Secretaria A y B |
| **Pasos** | 1. Login como usuario de Secretaria A<br>2. Listar procesos<br>3. Intentar acceder a proceso de Secretaria B via URL |
| **Datos de Prueba** | Usuario de Secretaria de Educacion<br>Proceso de Secretaria de Salud |
| **Resultado Esperado** | - Solo ve procesos de su secretaria<br>- Acceso directo a otra secretaria: 403 |

---

### CP-ROL-006: Admin Secretaria - gestion limitada

| Campo | Valor |
|-------|-------|
| **ID** | CP-ROL-006 |
| **Nombre** | Admin Secretaria gestiona su secretaria |
| **Descripcion** | Verificar alcance de admin_secretaria |
| **Prioridad** | Alta |
| **Tipo** | Positivo/Negativo - Permisos |
| **Precondiciones** | - Usuario con rol admin_secretaria<br>- Asignado a Secretaria especifica |
| **Pasos** | PUEDE:<br>1. Ver usuarios de su secretaria<br>2. Crear usuarios en su secretaria<br>3. Ver reportes de su secretaria<br><br>NO PUEDE:<br>4. Ver usuarios de otra secretaria<br>5. Acceder a configuracion global |
| **Datos de Prueba** | admin_secretaria de Educacion |
| **Resultado Esperado** | - Gestion limitada a su secretaria<br>- Sin acceso a otras secretarias |

---

# 8. CASOS DE PRUEBA - ALERTAS

## 8.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-ALT-01 | Ver alertas | Usuario | Listar alertas pendientes |
| CU-ALT-02 | Marcar leida | Usuario | Marcar alerta como leida |
| CU-ALT-03 | Generar alerta auto | Sistema | Crear alertas automaticas |

## 8.2 Casos de Prueba Detallados

### CP-ALT-001: Ver alertas no leidas

| Campo | Valor |
|-------|-------|
| **ID** | CP-ALT-001 |
| **Nombre** | Visualizar alertas pendientes |
| **Descripcion** | Verificar listado de alertas no leidas |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Existen alertas para el usuario<br>- Alertas no leidas |
| **Pasos** | 1. Login<br>2. Observar indicador de alertas en topbar<br>3. Click en campana de alertas<br>4. Ver lista de alertas |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Contador muestra numero de no leidas<br>- Lista muestra titulo, fecha, prioridad<br>- Alertas criticas resaltadas |

---

### CP-ALT-002: Marcar alerta como leida

| Campo | Valor |
|-------|-------|
| **ID** | CP-ALT-002 |
| **Nombre** | Marcar alerta individual como leida |
| **Descripcion** | Verificar marcado de lectura |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Existe alerta no leida |
| **Pasos** | 1. Ver lista de alertas<br>2. Click en alerta especifica o boton "Marcar leida"<br>3. Verificar cambio |
| **Datos de Prueba** | Alerta no leida |
| **Resultado Esperado** | - Alerta marcada como leida<br>- Contador decrementado<br>- Fecha/hora de lectura registrada |

---

### CP-ALT-003: Alerta automatica por tiempo excedido

| Campo | Valor |
|-------|-------|
| **ID** | CP-ALT-003 |
| **Nombre** | Generar alerta por proceso retrasado |
| **Descripcion** | Verificar generacion automatica de alertas |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso con dias_estimados = 3<br>- Proceso lleva 5 dias en etapa |
| **Pasos** | 1. Ejecutar comando alertas:generar<br>2. Verificar alerta creada |
| **Datos de Prueba** | Proceso retrasado en etapa |
| **Resultado Esperado** | - Alerta creada con tipo "tiempo_excedido"<br>- Prioridad ALTA<br>- Destinatario: responsable del area |

---

### CP-ALT-004: Alerta por documento proximo a vencer

| Campo | Valor |
|-------|-------|
| **ID** | CP-ALT-004 |
| **Nombre** | Alerta de documento por vencer |
| **Descripcion** | Verificar alerta de vigencia |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Documento con vigencia en 3 dias |
| **Pasos** | 1. Crear documento con fecha vigencia en 3 dias<br>2. Ejecutar alertas:generar<br>3. Verificar alerta |
| **Datos de Prueba** | Antecedentes vencen en 3 dias |
| **Resultado Esperado** | - Alerta tipo "certificado_por_vencer"<br>- Prioridad MEDIA (2-5 dias)<br>- Enlace al proceso |

---

### CP-ALT-005: Prioridad critica por documento < 2 dias

| Campo | Valor |
|-------|-------|
| **ID** | CP-ALT-005 |
| **Nombre** | Alerta critica por vencimiento inminente |
| **Descripcion** | Verificar prioridad critica automatica |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Documento vence manana |
| **Pasos** | 1. Documento con vigencia = manana<br>2. Ejecutar alertas:generar<br>3. Verificar alerta |
| **Datos de Prueba** | Documento vence en 1 dia |
| **Resultado Esperado** | - Prioridad: CRITICA<br>- Notificacion destacada |

---

# 9. CASOS DE PRUEBA - MOTOR DE FLUJOS

## 9.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-FLU-01 | Ver catalogo pasos | Admin | Listar pasos disponibles |
| CU-FLU-02 | Crear flujo | Admin | Crear nuevo flujo visual |
| CU-FLU-03 | Editar flujo | Admin | Modificar flujo existente |
| CU-FLU-04 | Eliminar flujo | Admin | Borrar flujo |
| CU-FLU-05 | Publicar version | Admin | Activar version de flujo |

## 9.2 Casos de Prueba Detallados

### CP-FLU-001: Acceder al motor de flujos

| Campo | Valor |
|-------|-------|
| **ID** | CP-FLU-001 |
| **Nombre** | Acceso al constructor visual de flujos |
| **Descripcion** | Verificar carga del motor de flujos |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario con rol admin/admin_unidad |
| **Pasos** | 1. Login como admin<br>2. Click en "Motor de Flujos" en menu<br>3. Esperar carga de React app |
| **Datos de Prueba** | admin@test.com |
| **Resultado Esperado** | - Se carga WorkflowApp.jsx<br>- Panel lateral con catalogo de pasos<br>- Area central para canvas<br>- Lista de flujos por secretaria |

---

### CP-FLU-002: Crear nuevo flujo basico

| Campo | Valor |
|-------|-------|
| **ID** | CP-FLU-002 |
| **Nombre** | Crear flujo con pasos arrastrados |
| **Descripcion** | Verificar creacion de flujo mediante drag-drop |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Motor de flujos cargado<br>- Secretaria seleccionada |
| **Pasos** | 1. Click en "Nuevo flujo"<br>2. Ingresar nombre y codigo<br>3. Arrastrar paso del catalogo al canvas<br>4. Arrastrar segundo paso<br>5. Conectar pasos<br>6. Click "Guardar" |
| **Datos de Prueba** | Nombre: Flujo Prueba<br>Codigo: FP-001<br>Pasos: DEF_NECESIDAD, VAL_CONTRATISTA |
| **Resultado Esperado** | - Flujo guardado exitosamente<br>- Aparece en lista de flujos<br>- Version 1 creada en estado borrador |

---

### CP-FLU-003: Agregar condicion a paso

| Campo | Valor |
|-------|-------|
| **ID** | CP-FLU-003 |
| **Nombre** | Configurar condicion dinamica en paso |
| **Descripcion** | Verificar creacion de condiciones |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Flujo con pasos existente |
| **Pasos** | 1. Seleccionar paso en canvas<br>2. Abrir panel de propiedades<br>3. Ir a "Condiciones"<br>4. Agregar condicion: monto > 50000000<br>5. Accion: requerido<br>6. Guardar |
| **Datos de Prueba** | Campo: monto_estimado<br>Operador: ><br>Valor: 50000000 |
| **Resultado Esperado** | - Condicion guardada en paso<br>- Paso muestra indicador de condicion |

---

### CP-FLU-004: Publicar version de flujo

| Campo | Valor |
|-------|-------|
| **ID** | CP-FLU-004 |
| **Nombre** | Activar version de flujo |
| **Descripcion** | Verificar publicacion de version |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Version de flujo en estado borrador |
| **Pasos** | 1. Seleccionar flujo<br>2. Ver versiones<br>3. Click en "Publicar" en version borrador<br>4. Confirmar |
| **Datos de Prueba** | Version 1 (borrador) |
| **Resultado Esperado** | - Estado cambia a "activa"<br>- Flujo usa esta version para nuevos procesos<br>- Version anterior archivada (si existia) |

---

### CP-FLU-005: Eliminar flujo

| Campo | Valor |
|-------|-------|
| **ID** | CP-FLU-005 |
| **Nombre** | Eliminar flujo sin procesos activos |
| **Descripcion** | Verificar eliminacion de flujo |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Flujo sin procesos asociados |
| **Pasos** | 1. Seleccionar flujo<br>2. Click en "Eliminar"<br>3. Confirmar eliminacion |
| **Datos de Prueba** | Flujo de prueba sin uso |
| **Resultado Esperado** | - Flujo eliminado<br>- Desaparece de la lista |

---

### CP-FLU-006: No eliminar flujo con procesos

| Campo | Valor |
|-------|-------|
| **ID** | CP-FLU-006 |
| **Nombre** | Bloquear eliminacion de flujo en uso |
| **Descripcion** | Verificar proteccion de flujos activos |
| **Prioridad** | Alta |
| **Tipo** | Negativo |
| **Precondiciones** | - Flujo con procesos asociados |
| **Pasos** | 1. Intentar eliminar flujo con procesos<br>2. Confirmar |
| **Datos de Prueba** | Flujo CD-PN con procesos |
| **Resultado Esperado** | - Error "No se puede eliminar flujo con procesos activos"<br>- Flujo no se elimina |

---

# 10. CASOS DE PRUEBA - MOTOR DE DASHBOARDS

## 10.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-DSH-01 | Asignar dashboard a rol | Admin | Configurar dashboard por rol |
| CU-DSH-02 | Asignar dashboard a usuario | Admin | Dashboard personalizado |
| CU-DSH-03 | Ver dashboard asignado | Usuario | Visualizar su dashboard |
| CU-DSH-04 | Editar layout | Usuario | Mover widgets |

## 10.2 Casos de Prueba Detallados

### CP-DSH-001: Acceder al motor de dashboards

| Campo | Valor |
|-------|-------|
| **ID** | CP-DSH-001 |
| **Nombre** | Acceso al constructor de dashboards |
| **Descripcion** | Verificar carga del motor de dashboards |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario admin con permiso dashboard.motor.ver |
| **Pasos** | 1. Login como admin<br>2. Click en "Motor de Dashboards"<br>3. Esperar carga |
| **Datos de Prueba** | admin@test.com |
| **Resultado Esperado** | - Panel de plantillas visible<br>- Bloques de asignacion: Rol, Secretaria, Unidad, Usuario<br>- Historial de cambios |

---

### CP-DSH-002: Asignar plantilla a rol

| Campo | Valor |
|-------|-------|
| **ID** | CP-DSH-002 |
| **Nombre** | Drag-drop plantilla a bloque de rol |
| **Descripcion** | Verificar asignacion por rol |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Plantillas disponibles<br>- Motor cargado |
| **Pasos** | 1. Arrastrar plantilla "Dashboard Ejecutivo"<br>2. Soltar en bloque "gobernador"<br>3. Click "Guardar asignaciones" |
| **Datos de Prueba** | Plantilla: Dashboard Ejecutivo<br>Rol: gobernador |
| **Resultado Esperado** | - Asignacion guardada<br>- Usuarios con rol gobernador veran ese dashboard<br>- Registro en historial |

---

### CP-DSH-003: Asignar plantilla a usuario especifico

| Campo | Valor |
|-------|-------|
| **ID** | CP-DSH-003 |
| **Nombre** | Dashboard personalizado por usuario |
| **Descripcion** | Verificar asignacion individual |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario especifico identificado |
| **Pasos** | 1. Buscar usuario<br>2. Arrastrar plantilla<br>3. Soltar en usuario<br>4. Guardar |
| **Datos de Prueba** | Usuario: Juan Perez<br>Plantilla: Dashboard Personalizado |
| **Resultado Esperado** | - Usuario tiene dashboard personalizado<br>- Prioridad sobre rol |

---

### CP-DSH-004: Jerarquia de resolucion de dashboard

| Campo | Valor |
|-------|-------|
| **ID** | CP-DSH-004 |
| **Nombre** | Verificar prioridad Usuario > Unidad > Secretaria > Rol |
| **Descripcion** | Verificar orden de resolucion |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario con asignacion por usuario<br>- Tambien existe asignacion por rol |
| **Pasos** | 1. Crear asignacion por rol para "unidad_solicitante"<br>2. Crear asignacion por usuario para usuario especifico<br>3. Login como ese usuario<br>4. Verificar cual dashboard se muestra |
| **Datos de Prueba** | Asignacion rol: Dashboard A<br>Asignacion usuario: Dashboard B |
| **Resultado Esperado** | - Se muestra Dashboard B (usuario tiene prioridad) |

---

### CP-DSH-005: Usuario sin asignacion ve default

| Campo | Valor |
|-------|-------|
| **ID** | CP-DSH-005 |
| **Nombre** | Dashboard por defecto cuando no hay asignacion |
| **Descripcion** | Verificar fallback a default |
| **Prioridad** | Media |
| **Tipo** | Edge Case |
| **Precondiciones** | - Usuario nuevo sin asignaciones<br>- Su rol tampoco tiene asignacion |
| **Pasos** | 1. Login como usuario sin asignaciones<br>2. Ir a /mi-dashboard |
| **Datos de Prueba** | Usuario nuevo |
| **Resultado Esperado** | - Se muestra dashboard por defecto<br>- O mensaje "Configure su dashboard" |

---

# 11. CASOS DE PRUEBA - PAA

## 11.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-PAA-01 | Ver PAA | Usuario autorizado | Listar Plan Anual |
| CU-PAA-02 | Crear item PAA | SECOP | Agregar item al plan |
| CU-PAA-03 | Verificar PAA | Planeacion | Validar inclusion en PAA |
| CU-PAA-04 | Emitir certificado | SECOP | Generar certificado PAA |

## 11.2 Casos de Prueba Detallados

### CP-PAA-001: Ver Plan Anual de Adquisiciones

| Campo | Valor |
|-------|-------|
| **ID** | CP-PAA-001 |
| **Nombre** | Visualizar PAA del ano vigente |
| **Descripcion** | Verificar listado de items PAA |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Existen items PAA para ano actual<br>- Usuario con permiso paa.ver |
| **Pasos** | 1. Login<br>2. Navegar a "Plan Anual (PAA)"<br>3. Verificar lista |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Lista muestra: codigo_necesidad, descripcion, valor_estimado, modalidad, trimestre, estado |

---

### CP-PAA-002: Crear item PAA

| Campo | Valor |
|-------|-------|
| **ID** | CP-PAA-002 |
| **Nombre** | Agregar nueva necesidad al PAA |
| **Descripcion** | Verificar creacion de item |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Usuario con rol SECOP y permiso paa.crear |
| **Pasos** | 1. Login como SECOP<br>2. Click "Nuevo item PAA"<br>3. Completar codigo_necesidad<br>4. Completar descripcion<br>5. Ingresar valor_estimado<br>6. Seleccionar modalidad<br>7. Seleccionar trimestre<br>8. Guardar |
| **Datos de Prueba** | Codigo: NEC-2026-001<br>Descripcion: Servicios de asesoria<br>Valor: 50000000<br>Modalidad: CD_PN<br>Trimestre: 1 |
| **Resultado Esperado** | - Item creado con estado "vigente"<br>- Aparece en lista PAA |

---

### CP-PAA-003: Verificar que proceso esta en PAA

| Campo | Valor |
|-------|-------|
| **ID** | CP-PAA-003 |
| **Nombre** | Validar inclusion de proceso en PAA |
| **Descripcion** | Verificar validacion de PAA en proceso |
| **Prioridad** | Alta |
| **Tipo** | Positivo |
| **Precondiciones** | - Proceso con valor que coincide con item PAA<br>- Usuario planeacion |
| **Pasos** | 1. Abrir proceso en validacion<br>2. Click en "Verificar PAA"<br>3. Sistema busca coincidencia<br>4. Marcar como verificado |
| **Datos de Prueba** | Proceso con objeto similar a item PAA existente |
| **Resultado Esperado** | - paa_verificado = true<br>- Enlace a item PAA |

---

### CP-PAA-004: Emitir certificado PAA

| Campo | Valor |
|-------|-------|
| **ID** | CP-PAA-004 |
| **Nombre** | Generar certificado de PAA |
| **Descripcion** | Verificar emision de certificado |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Item PAA verificado<br>- Usuario SECOP con permiso paa.certificado |
| **Pasos** | 1. Seleccionar item PAA<br>2. Click "Generar certificado"<br>3. Verificar documento generado |
| **Datos de Prueba** | Item PAA vigente |
| **Resultado Esperado** | - PDF generado con datos del item<br>- Certificado adjuntado a proceso |

---

# 12. CASOS DE PRUEBA - SECOP

## 12.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-SEC-01 | Consultar SECOP | Usuario | Buscar contratos en SECOP II |
| CU-SEC-02 | Ver estadisticas | Usuario | Ver resumen de contratos |

## 12.2 Casos de Prueba Detallados

### CP-SEC-001: Buscar contrato por referencia

| Campo | Valor |
|-------|-------|
| **ID** | CP-SEC-001 |
| **Nombre** | Buscar contrato en SECOP por numero |
| **Descripcion** | Verificar integracion con API SECOP |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Conectividad con API SECOP<br>- Contrato existe en SECOP |
| **Pasos** | 1. Ir a "Consulta SECOP"<br>2. Ingresar numero de proceso o contrato<br>3. Click "Buscar" |
| **Datos de Prueba** | Numero referencia valido de SECOP |
| **Resultado Esperado** | - Se muestran resultados de busqueda<br>- Datos: numero, objeto, valor, estado, proveedor |

---

### CP-SEC-002: Ver estadisticas SECOP

| Campo | Valor |
|-------|-------|
| **ID** | CP-SEC-002 |
| **Nombre** | Visualizar estadisticas de contratos SECOP |
| **Descripcion** | Verificar resumen estadistico |
| **Prioridad** | Baja |
| **Tipo** | Positivo |
| **Precondiciones** | - API SECOP disponible |
| **Pasos** | 1. Ir a consulta SECOP<br>2. Click en "Estadisticas"<br>3. Ver resumen |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Grafico/tabla con contratos por estado<br>- Total de contratos, valor total |

---

### CP-SEC-003: Manejar timeout de API SECOP

| Campo | Valor |
|-------|-------|
| **ID** | CP-SEC-003 |
| **Nombre** | Manejo de error de conexion SECOP |
| **Descripcion** | Verificar manejo de errores de API |
| **Prioridad** | Media |
| **Tipo** | Edge Case |
| **Precondiciones** | - Simular fallo de API (timeout) |
| **Pasos** | 1. Configurar timeout corto o mock de fallo<br>2. Intentar busqueda |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Mensaje amigable "Servicio SECOP no disponible"<br>- Opcion de reintentar<br>- Sistema no se interrumpe |

---

# 13. CASOS DE PRUEBA - REPORTES

## 13.1 Casos de Uso

| CU-ID | Caso de Uso | Actor | Descripcion |
|-------|-------------|-------|-------------|
| CU-REP-01 | Ver reporte estado | Usuario | Reporte de estado general |
| CU-REP-02 | Reporte por dependencia | Usuario | Filtrar por secretaria |
| CU-REP-03 | Exportar reporte | Usuario | Descargar en Excel/PDF |

## 13.2 Casos de Prueba Detallados

### CP-REP-001: Generar reporte de estado general

| Campo | Valor |
|-------|-------|
| **ID** | CP-REP-001 |
| **Nombre** | Reporte de estado general de procesos |
| **Descripcion** | Verificar generacion de reporte |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Existen procesos en sistema<br>- Usuario con permiso reportes.ver |
| **Pasos** | 1. Ir a "Reportes"<br>2. Seleccionar "Estado general"<br>3. Aplicar filtros (opcional)<br>4. Click "Generar" |
| **Datos de Prueba** | Filtros: Fecha desde 2026-01-01 |
| **Resultado Esperado** | - Tabla con procesos<br>- Agrupacion por estado<br>- Totales y porcentajes |

---

### CP-REP-002: Exportar reporte a Excel

| Campo | Valor |
|-------|-------|
| **ID** | CP-REP-002 |
| **Nombre** | Descargar reporte en formato Excel |
| **Descripcion** | Verificar exportacion |
| **Prioridad** | Media |
| **Tipo** | Positivo |
| **Precondiciones** | - Reporte generado en pantalla |
| **Pasos** | 1. Generar reporte<br>2. Click en "Exportar Excel"<br>3. Verificar descarga |
| **Datos de Prueba** | - |
| **Resultado Esperado** | - Archivo .xlsx descargado<br>- Datos coinciden con pantalla<br>- Formato correcto |

---

### CP-REP-003: Reporte vacio sin datos

| Campo | Valor |
|-------|-------|
| **ID** | CP-REP-003 |
| **Nombre** | Reporte cuando no hay datos |
| **Descripcion** | Verificar manejo de resultado vacio |
| **Prioridad** | Baja |
| **Tipo** | Edge Case |
| **Precondiciones** | - Filtros que no retornan datos |
| **Pasos** | 1. Aplicar filtros muy restrictivos<br>2. Generar reporte |
| **Datos de Prueba** | Fecha: 1990-01-01 |
| **Resultado Esperado** | - Mensaje "No hay datos para mostrar"<br>- No se muestra tabla vacia |

---

# 14. MATRIZ DE TRAZABILIDAD

## Trazabilidad Requisitos - Casos de Prueba

| Requisito | Casos de Prueba | Prioridad |
|-----------|-----------------|-----------|
| REQ-AUTH-001: Login con credenciales | CP-AUTH-001, CP-AUTH-002, CP-AUTH-003 | Critica |
| REQ-AUTH-002: Usuario activo requerido | CP-AUTH-004 | Alta |
| REQ-AUTH-003: Sesion segura | CP-AUTH-007, CP-AUTH-008 | Alta |
| REQ-PROC-001: Crear proceso | CP-PROC-001, CP-PROC-002, CP-PROC-003 | Critica |
| REQ-PROC-002: Flujo de etapas | CP-PROC-006, CP-PROC-007, CP-PROC-008 | Critica |
| REQ-PROC-003: Devolucion | CP-PROC-009, CP-PROC-010 | Alta |
| REQ-CD-001: Proceso CD-PN | CP-CD-001 a CP-CD-010 | Critica |
| REQ-CD-002: CDP requiere compatibilidad | CP-CD-004, CP-CD-005 | Critica |
| REQ-DOC-001: Subir documentos | CP-DOC-001, CP-DOC-002, CP-DOC-003 | Critica |
| REQ-DOC-002: Aprobar/Rechazar | CP-DOC-004, CP-DOC-005 | Alta |
| REQ-DOC-003: Vigencia documentos | CP-DOC-006 | Alta |
| REQ-ROL-001: Permisos por rol | CP-ROL-001 a CP-ROL-006 | Critica |
| REQ-ALT-001: Alertas automaticas | CP-ALT-003, CP-ALT-004, CP-ALT-005 | Alta |
| REQ-FLU-001: Motor de flujos | CP-FLU-001 a CP-FLU-006 | Media |
| REQ-DSH-001: Dashboards asignables | CP-DSH-001 a CP-DSH-005 | Media |
| REQ-PAA-001: Plan Anual | CP-PAA-001 a CP-PAA-004 | Alta |
| REQ-SEC-001: Integracion SECOP | CP-SEC-001 a CP-SEC-003 | Media |
| REQ-REP-001: Reportes exportables | CP-REP-001 a CP-REP-003 | Media |

---

## Resumen de Casos de Prueba

| Modulo | Total Casos | Criticos | Altos | Medios | Bajos |
|--------|-------------|----------|-------|--------|-------|
| Autenticacion | 10 | 2 | 5 | 3 | 0 |
| Dashboard | 5 | 0 | 2 | 2 | 1 |
| Procesos | 10 | 2 | 6 | 2 | 0 |
| Contratacion Directa | 10 | 4 | 5 | 1 | 0 |
| Gestion Documental | 7 | 1 | 4 | 2 | 0 |
| Roles y Permisos | 6 | 2 | 4 | 0 | 0 |
| Alertas | 5 | 0 | 4 | 1 | 0 |
| Motor de Flujos | 6 | 0 | 3 | 3 | 0 |
| Motor de Dashboards | 5 | 0 | 2 | 3 | 0 |
| PAA | 4 | 0 | 2 | 2 | 0 |
| SECOP | 3 | 0 | 0 | 3 | 0 |
| Reportes | 3 | 0 | 0 | 2 | 1 |
| **TOTAL** | **74** | **11** | **37** | **24** | **2** |

---

## Criterios de Exito

| Criterio | Umbral | Descripcion |
|----------|--------|-------------|
| Cobertura funcional | 100% | Todos los flujos principales cubiertos |
| Casos criticos | 100% pass | Todos los casos criticos deben pasar |
| Casos altos | >= 95% pass | Minimo 95% de casos altos |
| Casos medios | >= 90% pass | Minimo 90% de casos medios |
| Defectos bloqueadores | 0 | Cero defectos criticos |

---

**Documento preparado para automatizacion con Playwright**
**Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas**
