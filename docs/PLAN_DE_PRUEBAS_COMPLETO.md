# PLAN DE PRUEBAS Y CASOS DE PRUEBA
## Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas

**Version:** 1.0.0
**Fecha:** 2026-03-27
**Autor:** QA Senior
**Estado:** Documento oficial para automatizacion

---

# TABLA DE CONTENIDOS

1. [Informacion General](#1-informacion-general)
2. [Alcance de las Pruebas](#2-alcance-de-las-pruebas)
3. [Estrategia de Pruebas](#3-estrategia-de-pruebas)
4. [Modulo 01: Autenticacion](#4-modulo-01-autenticacion)
5. [Modulo 02: Dashboard y Navegacion](#5-modulo-02-dashboard-y-navegacion)
6. [Modulo 03: Gestion de Procesos](#6-modulo-03-gestion-de-procesos)
7. [Modulo 04: Contratacion Directa PN](#7-modulo-04-contratacion-directa-pn)
8. [Modulo 05: Gestion Documental](#8-modulo-05-gestion-documental)
9. [Modulo 06: Flujos de Trabajo](#9-modulo-06-flujos-de-trabajo)
10. [Modulo 07: Dashboard Builder](#10-modulo-07-dashboard-builder)
11. [Modulo 08: Administracion](#11-modulo-08-administracion)
12. [Modulo 09: Alertas y Notificaciones](#12-modulo-09-alertas-y-notificaciones)
13. [Modulo 10: Reportes](#13-modulo-10-reportes)
14. [Pruebas de Seguridad](#14-pruebas-de-seguridad)
15. [Pruebas de Rendimiento](#15-pruebas-de-rendimiento)

---

# 1. INFORMACION GENERAL

## 1.1 Objetivo

Validar que todas las funcionalidades del Sistema de Seguimiento de Documentos Contractuales operan correctamente segun los requerimientos funcionales y no funcionales especificados.

## 1.2 Ambiente de Pruebas

| Componente | Especificacion |
|------------|----------------|
| URL Base | http://localhost:8000 |
| Navegadores | Chrome 120+, Firefox 115+, Edge 120+ |
| Resolucion Desktop | 1920x1080, 1366x768 |
| Resolucion Mobile | 375x667 (iPhone SE), 414x896 (iPhone 11) |
| Base de Datos | MySQL 8.0 (testing) |

## 1.3 Usuarios de Prueba

| Usuario | Email | Contrasena | Rol | Secretaria | Unidad |
|---------|-------|------------|-----|------------|--------|
| Admin Sistema | admin@test.com | password123 | admin | - | - |
| Gobernador | gobernador@test.com | password123 | gobernador | - | - |
| Secretario Hacienda | secretario.hacienda@test.com | password123 | secretario | Hacienda | - |
| Jefe Unidad Compras | jefe.compras@test.com | password123 | jefe_unidad | Hacienda | Compras |
| Profesional Contratacion | prof.contratacion@test.com | password123 | profesional_contratacion | Hacienda | Compras |
| Planeacion | planeacion@test.com | password123 | planeacion | Planeacion | - |
| Juridica | juridica@test.com | password123 | juridica | Juridica | - |
| Presupuesto | presupuesto@test.com | password123 | presupuesto | Hacienda | Presupuesto |
| Usuario Inactivo | inactivo@test.com | password123 | consulta | - | - |

---

# 2. ALCANCE DE LAS PRUEBAS

## 2.1 Funcionalidades Incluidas

- Autenticacion y autorizacion
- Dashboard principal y por rol
- Dashboard Builder dinamico
- Gestion de procesos contractuales
- Flujo de Contratacion Directa PN (7 etapas)
- Gestion documental
- Motor de flujos configurable
- Administracion de usuarios, roles, secretarias, unidades
- Sistema de alertas
- Reportes y exportaciones
- Integracion SECOP II (consulta)

## 2.2 Tipos de Prueba

| Tipo | Descripcion | Prioridad |
|------|-------------|-----------|
| Funcionales | Verificacion de requisitos | Alta |
| Regresion | Validacion tras cambios | Alta |
| Seguridad | Control de acceso, XSS, CSRF | Alta |
| Usabilidad | Experiencia de usuario | Media |
| Rendimiento | Tiempos de respuesta | Media |
| Compatibilidad | Navegadores y dispositivos | Media |

---

# 3. ESTRATEGIA DE PRUEBAS

## 3.1 Criterios de Entrada

- Ambiente de pruebas configurado
- Base de datos con datos de prueba (seeders)
- Todas las migraciones ejecutadas
- Servicios backend funcionando

## 3.2 Criterios de Salida

- 100% casos criticos ejecutados
- 95% casos ejecutados exitosamente
- 0 defectos criticos abiertos
- 0 defectos mayores abiertos

## 3.3 Criterios de Aceptacion

- Tiempos de respuesta < 3 segundos
- Sin errores en consola del navegador
- Mensajes de error claros al usuario
- Datos persistidos correctamente

---

# 4. MODULO 01: AUTENTICACION

## Casos de Prueba - Login

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| AUTH-001 | Login exitoso con credenciales validas | Verificar que un usuario puede iniciar sesion correctamente | Usuario registrado y activo | 1. Navegar a /login<br>2. Ingresar email<br>3. Ingresar contrasena<br>4. Click en "Iniciar Sesion" | Email: admin@test.com<br>Password: password123 | Usuario redirigido a /dashboard, sesion iniciada, nombre visible en header | Positivo |
| AUTH-002 | Login fallido con email incorrecto | Verificar mensaje de error con email inexistente | Ninguna | 1. Navegar a /login<br>2. Ingresar email inexistente<br>3. Ingresar contrasena<br>4. Click en "Iniciar Sesion" | Email: noexiste@test.com<br>Password: password123 | Mensaje error "Credenciales incorrectas", permanecer en /login | Negativo |
| AUTH-003 | Login fallido con contrasena incorrecta | Verificar mensaje de error con contrasena incorrecta | Usuario registrado | 1. Navegar a /login<br>2. Ingresar email valido<br>3. Ingresar contrasena incorrecta<br>4. Click en "Iniciar Sesion" | Email: admin@test.com<br>Password: wrongpassword | Mensaje error "Credenciales incorrectas", permanecer en /login | Negativo |
| AUTH-004 | Login fallido con campos vacios | Verificar validacion de campos requeridos | Ninguna | 1. Navegar a /login<br>2. Dejar campos vacios<br>3. Click en "Iniciar Sesion" | Email: (vacio)<br>Password: (vacio) | Validacion HTML5 o mensaje "Campo requerido" | Negativo |
| AUTH-005 | Login fallido con usuario inactivo | Verificar bloqueo de usuarios inactivos | Usuario con activo=false | 1. Navegar a /login<br>2. Ingresar credenciales de usuario inactivo<br>3. Click en "Iniciar Sesion" | Email: inactivo@test.com<br>Password: password123 | Mensaje "Usuario desactivado" o similar, no permitir acceso | Negativo |
| AUTH-006 | Login con "Recordarme" | Verificar persistencia de sesion | Usuario activo | 1. Navegar a /login<br>2. Ingresar credenciales<br>3. Marcar "Recordarme"<br>4. Iniciar sesion<br>5. Cerrar navegador<br>6. Reabrir navegador | Email: admin@test.com<br>Password: password123 | Sesion persistida, usuario autenticado al reabrir | Positivo |
| AUTH-007 | Redireccion segun rol (planeacion) | Verificar redireccion especifica para rol planeacion | Usuario con solo rol planeacion | 1. Navegar a /login<br>2. Iniciar sesion con usuario planeacion | Email: planeacion@test.com<br>Password: password123 | Redireccion a /planeacion en lugar de /dashboard | Positivo |
| AUTH-008 | Formato email invalido | Verificar validacion formato email | Ninguna | 1. Navegar a /login<br>2. Ingresar email con formato invalido<br>3. Click en "Iniciar Sesion" | Email: emailsinformato<br>Password: password123 | Validacion "Email invalido" | Negativo |

## Casos de Prueba - Logout

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| AUTH-009 | Logout exitoso | Verificar cierre de sesion correcto | Usuario autenticado | 1. Hacer click en menu usuario<br>2. Click en "Cerrar Sesion" | - | Redireccion a /login, sesion terminada, no poder acceder a rutas protegidas | Positivo |
| AUTH-010 | Acceso ruta protegida sin sesion | Verificar redireccion a login | Sin sesion | 1. Navegar directamente a /dashboard | - | Redireccion automatica a /login | Seguridad |
| AUTH-011 | Regeneracion token sesion | Verificar proteccion session fixation | Usuario autenticado | 1. Guardar session ID antes de login<br>2. Hacer login<br>3. Comparar session ID | - | Session ID diferente despues del login | Seguridad |

---

# 5. MODULO 02: DASHBOARD Y NAVEGACION

## Casos de Prueba - Dashboard Principal

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| DASH-001 | Ver dashboard segun rol admin | Verificar dashboard completo para admin | Login como admin | 1. Navegar a /dashboard | - | Ver todos los widgets, estadisticas globales, acceso a todas las secciones | Positivo |
| DASH-002 | Ver dashboard segun rol secretario | Verificar dashboard limitado a secretaria | Login como secretario | 1. Navegar a /dashboard | - | Ver solo datos de su secretaria, widgets correspondientes | Positivo |
| DASH-003 | Ver dashboard segun rol jefe_unidad | Verificar dashboard limitado a unidad | Login como jefe_unidad | 1. Navegar a /dashboard | - | Ver solo datos de su unidad, metricas de equipo | Positivo |
| DASH-004 | KPIs muestran valores correctos | Verificar calculo de KPIs | Datos de prueba cargados | 1. Navegar a /dashboard<br>2. Verificar cada KPI contra BD | Procesos existentes en BD | KPIs reflejan conteos reales de la base de datos | Positivo |
| DASH-005 | Graficas cargan correctamente | Verificar renderizado de graficas | Datos de prueba cargados | 1. Navegar a /dashboard<br>2. Verificar cada grafica | - | Graficas visibles, sin errores JS, datos coherentes | Positivo |
| DASH-006 | Busqueda global funciona | Verificar busqueda por codigo/objeto | Procesos existentes | 1. Navegar a /dashboard<br>2. Usar barra de busqueda<br>3. Ingresar termino | Buscar: "CD-PN-001" | Resultados relevantes mostrados | Positivo |
| DASH-007 | Filtro por fecha funciona | Verificar filtrado temporal | Procesos con diferentes fechas | 1. Aplicar filtro "Este mes"<br>2. Verificar resultados | - | Solo procesos del mes actual visibles | Positivo |
| DASH-008 | Navegacion lateral funciona | Verificar menu lateral | Usuario autenticado | 1. Hacer click en cada item del menu | - | Navegacion correcta a cada seccion | Positivo |
| DASH-009 | Dashboard responsive (tablet) | Verificar adaptacion a tablet | Usuario autenticado | 1. Redimensionar a 768px<br>2. Verificar layout | - | Layout adaptado, menu colapsado, widgets reorganizados | Positivo |
| DASH-010 | Dashboard responsive (mobile) | Verificar adaptacion a mobile | Usuario autenticado | 1. Redimensionar a 375px<br>2. Verificar layout | - | Layout de una columna, menu hamburguesa, scroll vertical | Positivo |

## Casos de Prueba - Mi Dashboard (Por Rol)

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| DASH-011 | Acceso a mi-dashboard | Verificar acceso a dashboard personalizado | Usuario autenticado | 1. Navegar a /mi-dashboard | - | Dashboard cargado segun asignacion del usuario | Positivo |
| DASH-012 | Dashboard por asignacion de usuario | Verificar prioridad de asignacion usuario | Asignacion por usuario configurada | 1. Login<br>2. Navegar a /mi-dashboard | - | Dashboard especifico del usuario mostrado | Positivo |
| DASH-013 | Dashboard por asignacion de unidad | Verificar fallback a asignacion unidad | Sin asignacion usuario, con asignacion unidad | 1. Login<br>2. Navegar a /mi-dashboard | - | Dashboard de la unidad mostrado | Positivo |
| DASH-014 | Dashboard por asignacion de secretaria | Verificar fallback a asignacion secretaria | Sin asignacion usuario/unidad | 1. Login<br>2. Navegar a /mi-dashboard | - | Dashboard de la secretaria mostrado | Positivo |
| DASH-015 | Dashboard por asignacion de rol | Verificar fallback a asignacion rol | Sin otras asignaciones | 1. Login<br>2. Navegar a /mi-dashboard | - | Dashboard por defecto del rol mostrado | Positivo |

---

# 6. MODULO 03: GESTION DE PROCESOS

## Casos de Prueba - Listado de Procesos

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| PROC-001 | Ver listado de procesos | Verificar visualizacion de tabla de procesos | Procesos existentes | 1. Navegar a /procesos | - | Tabla con procesos visible, paginacion funcional | Positivo |
| PROC-002 | Filtrar por estado | Verificar filtro de estado | Procesos en diferentes estados | 1. Navegar a /procesos<br>2. Seleccionar filtro estado "EN_CURSO" | - | Solo procesos EN_CURSO visibles | Positivo |
| PROC-003 | Filtrar por secretaria | Verificar filtro de secretaria | Procesos de varias secretarias | 1. Navegar a /procesos<br>2. Seleccionar secretaria | Secretaria: Hacienda | Solo procesos de Hacienda visibles | Positivo |
| PROC-004 | Ordenar por fecha | Verificar ordenamiento | Varios procesos | 1. Navegar a /procesos<br>2. Click en columna "Fecha" | - | Procesos ordenados por fecha asc/desc | Positivo |
| PROC-005 | Buscar por codigo | Verificar busqueda especifica | Proceso existente | 1. Navegar a /procesos<br>2. Buscar por codigo | Codigo: "PROC-001-2026" | Proceso encontrado | Positivo |
| PROC-006 | Paginacion funciona | Verificar navegacion entre paginas | +10 procesos | 1. Navegar a /procesos<br>2. Ir a pagina 2 | - | Segunda pagina de resultados visible | Positivo |
| PROC-007 | Ver solo procesos de mi secretaria | Verificar scope por secretaria | Login como secretario | 1. Navegar a /procesos | - | Solo procesos de su secretaria visibles | Seguridad |
| PROC-008 | Admin ve todos los procesos | Verificar scope global para admin | Login como admin | 1. Navegar a /procesos | - | Todos los procesos del sistema visibles | Positivo |

## Casos de Prueba - Crear Proceso

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| PROC-009 | Crear proceso exitoso | Verificar creacion completa | Usuario con permiso procesos.crear | 1. Navegar a /procesos/crear<br>2. Completar formulario<br>3. Subir estudio previo<br>4. Click "Guardar" | Objeto: "Test proceso"<br>Valor: 50000000<br>Plazo: 30 | Proceso creado, codigo generado, redireccion a detalle | Positivo |
| PROC-010 | Crear proceso sin estudio previo | Verificar validacion documento obligatorio | Usuario con permiso | 1. Navegar a /procesos/crear<br>2. Completar formulario SIN archivo<br>3. Click "Guardar" | Sin archivo | Error "Estudio previo es requerido" | Negativo |
| PROC-011 | Crear proceso con valor 0 | Verificar validacion valor minimo | Usuario con permiso | 1. Navegar a /procesos/crear<br>2. Ingresar valor 0<br>3. Click "Guardar" | Valor: 0 | Error "Valor debe ser mayor a 0" | Negativo |
| PROC-012 | Crear proceso con plazo negativo | Verificar validacion plazo | Usuario con permiso | 1. Navegar a /procesos/crear<br>2. Ingresar plazo -5<br>3. Click "Guardar" | Plazo: -5 | Error "Plazo debe ser positivo" | Negativo |
| PROC-013 | Crear proceso sin permiso | Verificar control de acceso | Usuario sin permiso | 1. Intentar navegar a /procesos/crear | - | Error 403 o redireccion | Seguridad |
| PROC-014 | Codigo generado automatico | Verificar formato codigo | Usuario con permiso | 1. Crear proceso correctamente | - | Codigo formato: XXXX-NNN-YYYY autogenerado | Positivo |

## Casos de Prueba - Ver Detalle Proceso

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| PROC-015 | Ver detalle proceso | Verificar visualizacion completa | Proceso existente | 1. Navegar a /procesos/{id} | ID proceso valido | Todos los datos del proceso visibles | Positivo |
| PROC-016 | Ver historial de etapas | Verificar timeline de proceso | Proceso con varias etapas | 1. Navegar a /procesos/{id}<br>2. Ver seccion historial | - | Timeline con etapas y fechas | Positivo |
| PROC-017 | Ver documentos adjuntos | Verificar lista de documentos | Proceso con documentos | 1. Navegar a /procesos/{id}<br>2. Ver seccion documentos | - | Lista de documentos con enlaces de descarga | Positivo |
| PROC-018 | Descargar documento | Verificar descarga funcional | Proceso con documentos | 1. Navegar a /procesos/{id}<br>2. Click descargar documento | - | Archivo descargado correctamente | Positivo |
| PROC-019 | Ver proceso inexistente | Verificar manejo de error | Ninguna | 1. Navegar a /procesos/99999 | ID inexistente | Error 404 pagina no encontrada | Negativo |
| PROC-020 | Ver proceso de otra secretaria | Verificar control acceso | Login como usuario de otra secretaria | 1. Navegar a /procesos/{id} | ID de proceso de otra secretaria | Error 403 acceso denegado | Seguridad |

---

# 7. MODULO 04: CONTRATACION DIRECTA PN

## Casos de Prueba - Crear Proceso CD-PN

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-001 | Crear CD-PN exitoso | Verificar creacion proceso CD-PN | Usuario con permiso | 1. Navegar a /proceso-cd/crear<br>2. Completar formulario<br>3. Subir estudio previo<br>4. Guardar | Objeto: "Prestacion servicios"<br>Valor: 30000000<br>Plazo: 6 meses | Proceso creado en estado BORRADOR | Positivo |
| CDPN-002 | Validar campos requeridos CD-PN | Verificar validaciones | Usuario con permiso | 1. Navegar a /proceso-cd/crear<br>2. Dejar campos vacios<br>3. Click guardar | Campos vacios | Mensajes de error en campos requeridos | Negativo |
| CDPN-003 | Cargar estudio previo PDF | Verificar carga archivo | Usuario con permiso | 1. En formulario crear<br>2. Seleccionar archivo PDF<br>3. Guardar | Archivo: estudio.pdf (< 10MB) | Archivo cargado y asociado | Positivo |
| CDPN-004 | Rechazar archivo no PDF | Verificar validacion tipo archivo | Usuario con permiso | 1. En formulario crear<br>2. Seleccionar archivo .exe | Archivo: malware.exe | Error "Solo se permiten archivos PDF" | Negativo |
| CDPN-005 | Rechazar archivo muy grande | Verificar limite tamano | Usuario con permiso | 1. En formulario crear<br>2. Seleccionar PDF > 10MB | Archivo: grande.pdf (15MB) | Error "Archivo excede tamano maximo" | Negativo |

## Casos de Prueba - Flujo Etapa 1: Estudios Previos

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-006 | Subir estudio previo | Verificar transicion a ESTUDIO_PREVIO_CARGADO | Proceso en BORRADOR | 1. Abrir proceso<br>2. Subir estudio previo<br>3. Confirmar | Archivo PDF valido | Estado cambia a ESTUDIO_PREVIO_CARGADO | Positivo |
| CDPN-007 | Enviar a validacion Planeacion | Verificar transicion | Proceso en ESTUDIO_PREVIO_CARGADO | 1. Abrir proceso<br>2. Click "Enviar a Planeacion" | - | Estado cambia a EN_VALIDACION_PLANEACION | Positivo |

## Casos de Prueba - Flujo Etapa 2: Validaciones

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-008 | Solicitar validacion PAA | Verificar solicitud PAA | Proceso en validacion | 1. Marcar "Solicitar PAA"<br>2. Guardar | - | paa_solicitado = true | Positivo |
| CDPN-009 | Aprobar compatibilidad gasto | Verificar aprobacion por planeacion | Login como planeacion, PAA solicitado | 1. Revisar proceso<br>2. Aprobar compatibilidad | - | compatibilidad_aprobada = true | Positivo |
| CDPN-010 | Solicitar CDP | Verificar solicitud CDP | compatibilidad_aprobada = true | 1. Click "Solicitar CDP" | - | Estado cambia a CDP_SOLICITADO | Positivo |
| CDPN-011 | Solicitar CDP sin compatibilidad | Verificar regla de negocio critica | compatibilidad_aprobada = false | 1. Intentar solicitar CDP | - | Error "Debe aprobar compatibilidad primero" | Negativo - Critico |
| CDPN-012 | Aprobar CDP | Verificar aprobacion CDP | Login como presupuesto, CDP solicitado | 1. Ingresar numero CDP<br>2. Ingresar valor<br>3. Aprobar | Numero: 12345<br>Valor: 30000000 | Estado cambia a CDP_APROBADO | Positivo |
| CDPN-013 | Rechazar CDP | Verificar rechazo CDP | Login como presupuesto | 1. Click rechazar<br>2. Ingresar motivo | Motivo: "Sin disponibilidad" | Estado cambia a CDP_BLOQUEADO | Negativo |

## Casos de Prueba - Flujo Etapa 3: Documentacion Contratista

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-014 | Completar checklist contratista | Verificar checklist obligatorio | CDP aprobado | 1. Marcar todos los items<br>2. Subir documentos<br>3. Validar | Todos los checks marcados | checklist_validado = true | Positivo |
| CDPN-015 | Subir documento cedula | Verificar carga documento | CDP aprobado | 1. Seleccionar tipo "Cedula"<br>2. Subir archivo<br>3. Confirmar | Archivo cedula.pdf | Documento asociado al proceso | Positivo |
| CDPN-016 | Avanzar con documentos incompletos | Verificar bloqueo | faltan documentos obligatorios | 1. Intentar avanzar a siguiente etapa | - | Error "Documentos obligatorios faltantes" | Negativo |
| CDPN-017 | Verificar vigencia documentos | Verificar alerta de vencimiento | Documentos proximos a vencer | 1. Ver proceso | Antecedentes vencen en 2 dias | Alerta visible "Documentos por vencer" | Positivo |

## Casos de Prueba - Flujo Etapa 4: Revision Juridica

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-018 | Enviar a revision juridica | Verificar transicion | Documentacion completa | 1. Click "Enviar a Juridica" | - | Estado = EN_REVISION_JURIDICA | Positivo |
| CDPN-019 | Aprobar revision juridica | Verificar aprobacion | Login como juridica | 1. Revisar expediente<br>2. Asignar numero proceso<br>3. Aprobar | Numero: J-001-2026 | Estado = PROCESO_NUMERO_GENERADO | Positivo |
| CDPN-020 | Devolver a documentacion | Verificar devolucion | Login como juridica | 1. Rechazar<br>2. Ingresar observaciones | Observaciones: "Falta firma" | Estado = DOCUMENTACION_INCOMPLETA, observaciones guardadas | Negativo |

## Casos de Prueba - Flujo Etapa 5: Contrato

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-021 | Generar contrato | Verificar generacion | Numero proceso asignado | 1. Click "Generar Contrato" | - | Contrato generado, archivo disponible | Positivo |
| CDPN-022 | Registrar firma contratista | Verificar firma parcial | Contrato generado | 1. Marcar "Firma contratista"<br>2. Fecha firma | Fecha: 2026-03-27 | Estado = CONTRATO_FIRMADO_PARCIAL | Positivo |
| CDPN-023 | Registrar firma ordenador | Verificar firma completa | Firma contratista registrada | 1. Marcar "Firma ordenador"<br>2. Fecha firma | Fecha: 2026-03-27 | Estado = CONTRATO_FIRMADO_TOTAL | Positivo |

## Casos de Prueba - Flujo Etapa 6: RPC

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-024 | Solicitar RPC | Verificar solicitud | Contrato firmado total | 1. Click "Solicitar RPC" | - | Estado = RPC_SOLICITADO | Positivo |
| CDPN-025 | Registrar RPC | Verificar registro | Login como presupuesto | 1. Ingresar numero RPC<br>2. Confirmar | Numero: RPC-001-2026 | Estado = RPC_FIRMADO | Positivo |
| CDPN-026 | Radicar expediente | Verificar radicacion | RPC firmado | 1. Click "Radicar"<br>2. Confirmar | - | Estado = EXPEDIENTE_RADICADO | Positivo |

## Casos de Prueba - Flujo Etapa 7: Ejecucion

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-027 | Solicitar ARL | Verificar solicitud ARL | Expediente radicado | 1. Marcar "ARL solicitada"<br>2. Guardar | - | arl_solicitada = true | Positivo |
| CDPN-028 | Registrar acta inicio | Verificar acta | ARL solicitada | 1. Subir acta inicio<br>2. Registrar fecha | Fecha: 2026-04-01 | acta_inicio_firmada = true, fecha registrada | Positivo |
| CDPN-029 | Iniciar ejecucion | Verificar inicio | Acta registrada | 1. Click "Iniciar Ejecucion" | - | Estado = EN_EJECUCION, fecha_inicio registrada | Positivo |

## Casos de Prueba - Estados Especiales

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| CDPN-030 | Cancelar proceso | Verificar cancelacion | Login como admin, proceso activo | 1. Abrir proceso<br>2. Click "Cancelar"<br>3. Confirmar | Motivo: "Anulado" | Estado = CANCELADO, proceso no editable | Negativo |
| CDPN-031 | Suspender proceso | Verificar suspension | Login como admin | 1. Abrir proceso<br>2. Click "Suspender" | - | Estado = SUSPENDIDO | Negativo |
| CDPN-032 | Reactivar proceso suspendido | Verificar reactivacion | Proceso suspendido | 1. Click "Reactivar" | - | Estado vuelve al anterior | Positivo |
| CDPN-033 | Devolver contrato | Verificar devolucion desde juridica | Contrato generado | 1. Click "Devolver"<br>2. Observaciones | Obs: "Corregir clausula" | Estado = CONTRATO_DEVUELTO | Negativo |

---

# 8. MODULO 05: GESTION DOCUMENTAL

## Casos de Prueba - Subida de Documentos

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| DOC-001 | Subir documento PDF | Verificar carga PDF | Proceso existente | 1. Seleccionar tipo documento<br>2. Seleccionar archivo PDF<br>3. Subir | Archivo: documento.pdf | Documento cargado y visible | Positivo |
| DOC-002 | Subir documento Word | Verificar carga DOCX | Proceso existente | 1. Seleccionar archivo DOCX<br>2. Subir | Archivo: documento.docx | Documento cargado | Positivo |
| DOC-003 | Subir documento Excel | Verificar carga XLSX | Proceso existente | 1. Seleccionar archivo XLSX<br>2. Subir | Archivo: datos.xlsx | Documento cargado | Positivo |
| DOC-004 | Rechazar tipo no permitido | Verificar validacion tipo | Proceso existente | 1. Seleccionar archivo .exe<br>2. Intentar subir | Archivo: programa.exe | Error "Tipo de archivo no permitido" | Negativo |
| DOC-005 | Validar tamano maximo | Verificar limite 10MB | Proceso existente | 1. Seleccionar archivo > 10MB<br>2. Intentar subir | Archivo: grande.pdf (15MB) | Error "Archivo excede tamano maximo" | Negativo |
| DOC-006 | Reemplazar documento | Verificar reemplazo | Documento existente | 1. Click "Reemplazar"<br>2. Subir nuevo archivo | Nuevo archivo | Documento anterior reemplazado, version guardada | Positivo |

## Casos de Prueba - Descarga de Documentos

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| DOC-007 | Descargar documento propio | Verificar descarga | Documento de mi secretaria | 1. Click "Descargar" | - | Archivo descargado correctamente | Positivo |
| DOC-008 | Descargar documento otra secretaria | Verificar control acceso | Documento de otra secretaria | 1. Intentar descargar | - | Error 403 acceso denegado | Seguridad |
| DOC-009 | Descargar como admin | Verificar acceso admin | Login como admin | 1. Descargar cualquier documento | - | Descarga exitosa | Positivo |

## Casos de Prueba - Aprobacion de Documentos

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| DOC-010 | Aprobar documento | Verificar aprobacion | Login con permiso aprobar, documento pendiente | 1. Revisar documento<br>2. Click "Aprobar" | - | estado_documento = 'aprobado' | Positivo |
| DOC-011 | Rechazar documento | Verificar rechazo | Login con permiso, documento pendiente | 1. Click "Rechazar"<br>2. Ingresar observacion | Obs: "Ilegible" | estado_documento = 'rechazado', observacion guardada | Negativo |
| DOC-012 | Aprobar sin permiso | Verificar control acceso | Usuario sin permiso aprobar | 1. Intentar aprobar | - | Error 403, boton no visible | Seguridad |

---

# 9. MODULO 06: FLUJOS DE TRABAJO

## Casos de Prueba - Motor de Flujos

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| FLOW-001 | Acceder motor flujos | Verificar acceso admin | Login como admin | 1. Navegar a /motor-flujos | - | Vista del motor cargada correctamente | Positivo |
| FLOW-002 | Acceso denegado no admin | Verificar control acceso | Login como usuario normal | 1. Navegar a /motor-flujos | - | Error 403 o redireccion | Seguridad |
| FLOW-003 | Ver catalogo de pasos | Verificar catalogo | Admin en motor flujos | 1. Abrir panel catalogo | - | Lista de pasos predefinidos visible | Positivo |
| FLOW-004 | Arrastrar paso al canvas | Verificar drag-drop | Admin en motor flujos | 1. Arrastrar paso desde catalogo<br>2. Soltar en canvas | Paso: "Estudio Previo" | Nodo creado en el canvas | Positivo |
| FLOW-005 | Conectar dos pasos | Verificar conexion | Dos pasos en canvas | 1. Arrastrar desde salida de paso 1<br>2. Conectar a entrada de paso 2 | - | Conexion creada visualmente | Positivo |
| FLOW-006 | Guardar flujo completo | Verificar persistencia | Flujo configurado | 1. Click "Guardar Flujo"<br>2. Ingresar nombre | Nombre: "Flujo Test" | Flujo guardado en BD, mensaje exito | Positivo |
| FLOW-007 | Editar flujo existente | Verificar edicion | Flujo existente | 1. Seleccionar flujo<br>2. Modificar<br>3. Guardar | - | Cambios persistidos | Positivo |
| FLOW-008 | Eliminar flujo | Verificar eliminacion | Flujo sin instancias | 1. Seleccionar flujo<br>2. Click "Eliminar"<br>3. Confirmar | - | Flujo eliminado | Negativo |
| FLOW-009 | Eliminar flujo con instancias | Verificar proteccion | Flujo con instancias activas | 1. Intentar eliminar | - | Error "Flujo tiene procesos activos" | Negativo |
| FLOW-010 | Crear version de flujo | Verificar versionado | Flujo existente | 1. Click "Nueva Version"<br>2. Modificar<br>3. Guardar | - | Nueva version creada, anterior intacta | Positivo |

---

# 10. MODULO 07: DASHBOARD BUILDER

## Casos de Prueba - Acceso y Carga

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| BUILD-001 | Acceder dashboard builder | Verificar carga inicial | Login como admin | 1. Navegar a /dashboards/builder | - | Builder cargado con 3 paneles visibles | Positivo |
| BUILD-002 | Cargar catalogo entidades | Verificar catalogo | Builder abierto | 1. Ver panel izquierdo | - | Lista de entidades (procesos, usuarios, alertas, etc.) | Positivo |
| BUILD-003 | Expandir entidad | Verificar campos | Builder abierto | 1. Click en entidad "Procesos" | - | Lista de campos expandida | Positivo |
| BUILD-004 | Ver scope indicator | Verificar indicador scope | Builder abierto | 1. Ver header | - | Indicador de scope del usuario visible | Positivo |

## Casos de Prueba - Drag and Drop

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| BUILD-005 | Arrastrar campo al canvas | Verificar creacion widget | Builder abierto | 1. Arrastrar campo "estado" de "Procesos"<br>2. Soltar en canvas | Campo: estado | Widget creado automaticamente | Positivo |
| BUILD-006 | Widget KPI automatico | Verificar tipo inferido | Builder abierto | 1. Arrastrar campo numerico | Campo: valor_estimado | Widget tipo KPI creado | Positivo |
| BUILD-007 | Widget Chart automatico | Verificar tipo inferido | Builder abierto | 1. Arrastrar campo enum | Campo: estado | Widget tipo Chart creado | Positivo |
| BUILD-008 | Widget Table automatico | Verificar tipo inferido | Builder abierto | 1. Arrastrar campo string | Campo: objeto | Widget tipo Table creado | Positivo |
| BUILD-009 | Widget Timeline automatico | Verificar tipo inferido | Builder abierto | 1. Arrastrar campo datetime | Campo: created_at | Widget tipo Timeline creado | Positivo |
| BUILD-010 | Mover widget | Verificar drag dentro canvas | Widget existente | 1. Arrastrar widget<br>2. Soltar en nueva posicion | - | Widget movido, layout actualizado | Positivo |
| BUILD-011 | Redimensionar widget | Verificar resize | Widget existente | 1. Arrastrar borde del widget | - | Widget redimensionado | Positivo |

## Casos de Prueba - Panel de Propiedades

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| BUILD-012 | Seleccionar widget | Verificar seleccion | Widget existente | 1. Click en widget | - | Widget resaltado, panel propiedades abierto | Positivo |
| BUILD-013 | Cambiar titulo widget | Verificar edicion titulo | Widget seleccionado | 1. Editar campo titulo<br>2. Tab/Enter | Titulo: "Mi KPI" | Titulo actualizado inmediatamente | Positivo |
| BUILD-014 | Cambiar tipo widget | Verificar cambio tipo | Widget seleccionado | 1. Cambiar tipo a "Chart" | - | Widget re-renderizado como chart | Positivo |
| BUILD-015 | Cambiar metrica | Verificar cambio metrica | Widget seleccionado | 1. Cambiar metrica a "sum" | - | Datos actualizados con suma | Positivo |
| BUILD-016 | Cambiar dimension | Verificar cambio dimension | Widget chart seleccionado | 1. Seleccionar dimension "area_actual" | - | Chart agrupado por area | Positivo |
| BUILD-017 | Cambiar tipo grafica | Verificar tipo chart | Widget chart seleccionado | 1. Seleccionar "pie" | - | Grafica de pastel renderizada | Positivo |
| BUILD-018 | Agregar filtro | Verificar filtro | Widget seleccionado | 1. Click "Agregar filtro"<br>2. Configurar campo/operador/valor | Campo: estado<br>Operador: =<br>Valor: EN_CURSO | Datos filtrados, widget actualizado | Positivo |
| BUILD-019 | Eliminar filtro | Verificar eliminacion filtro | Widget con filtro | 1. Click X en filtro | - | Filtro removido, datos actualizados | Positivo |
| BUILD-020 | Configurar columnas tabla | Verificar columnas | Widget tabla seleccionado | 1. Seleccionar/deseleccionar columnas | Columnas: id, codigo, estado | Tabla muestra solo columnas seleccionadas | Positivo |

## Casos de Prueba - Ejecucion de Queries

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| BUILD-021 | Query ejecuta en tiempo real | Verificar ejecucion | Widget configurado | 1. Cambiar cualquier propiedad | - | Datos actualizados sin recargar pagina | Positivo |
| BUILD-022 | Scope aplicado automaticamente | Verificar scope secretaria | Login como secretario | 1. Crear widget de procesos | - | Solo datos de su secretaria visibles | Seguridad |
| BUILD-023 | Scope global para admin | Verificar scope global | Login como admin | 1. Crear widget de procesos | - | Todos los datos visibles | Positivo |
| BUILD-024 | Error query mostrado | Verificar manejo error | Configuracion invalida | 1. Configurar widget con campo inexistente | - | Mensaje de error en widget | Negativo |

## Casos de Prueba - Guardar y Cargar

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| BUILD-025 | Guardar dashboard | Verificar persistencia | Widgets configurados | 1. Click "Guardar"<br>2. Ingresar nombre<br>3. Confirmar | Nombre: "Mi Dashboard" | Dashboard guardado, mensaje exito | Positivo |
| BUILD-026 | Cargar dashboard | Verificar carga | Dashboard guardado | 1. Recargar pagina | - | Dashboard restaurado con widgets | Positivo |
| BUILD-027 | Mantener layout | Verificar layout | Dashboard con layout modificado | 1. Guardar<br>2. Recargar | - | Posiciones y tamanos restaurados | Positivo |
| BUILD-028 | Guardar sin nombre | Verificar validacion | Widgets existentes | 1. Click guardar<br>2. Dejar nombre vacio | - | Error "Nombre requerido" | Negativo |

## Casos de Prueba - Modo Edicion vs Visualizacion

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| BUILD-029 | Toggle modo edicion | Verificar toggle | Builder abierto | 1. Click boton "Editando/Visualizando" | - | Cambio de modo, paneles ocultos/visibles | Positivo |
| BUILD-030 | Modo visualizacion sin drag | Verificar bloqueo | Modo visualizacion | 1. Intentar arrastrar widget | - | Widget no se mueve | Positivo |
| BUILD-031 | Modo visualizacion sin resize | Verificar bloqueo | Modo visualizacion | 1. Intentar redimensionar | - | Widget no cambia tamano | Positivo |
| BUILD-032 | Usuario readonly | Verificar readonly | Usuario sin permisos edicion | 1. Acceder builder | - | Solo modo visualizacion disponible | Seguridad |

## Casos de Prueba - Widgets Especificos

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| BUILD-033 | KPI muestra valor formateado | Verificar formato | Widget KPI con suma | 1. Ver KPI de valor_estimado | Valor: 1500000000 | Mostrado como "$1.5B" | Positivo |
| BUILD-034 | Chart bar renderiza | Verificar bar chart | Widget chart tipo bar | 1. Configurar como bar | - | Grafica de barras visible | Positivo |
| BUILD-035 | Chart pie renderiza | Verificar pie chart | Widget chart tipo pie | 1. Configurar como pie | - | Grafica de pastel visible | Positivo |
| BUILD-036 | Chart line renderiza | Verificar line chart | Widget chart tipo line | 1. Configurar como line | - | Grafica de lineas visible | Positivo |
| BUILD-037 | Table paginacion | Verificar paginacion | Widget tabla con +5 registros | 1. Ver paginacion<br>2. Navegar | - | Paginacion funcional | Positivo |
| BUILD-038 | Table ordenamiento | Verificar ordenamiento | Widget tabla | 1. Click header columna | - | Datos ordenados asc/desc | Positivo |
| BUILD-039 | Timeline renderiza | Verificar timeline | Widget timeline | 1. Configurar timeline | - | Grafica de area con fechas | Positivo |
| BUILD-040 | Heatmap renderiza | Verificar heatmap | Widget heatmap configurado | 1. Configurar xDimension y yDimension | - | Matriz de calor visible | Positivo |

---

# 11. MODULO 08: ADMINISTRACION

## Casos de Prueba - Gestion de Usuarios

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ADMIN-001 | Listar usuarios | Verificar listado | Login como admin | 1. Navegar a /admin/usuarios | - | Lista de usuarios con paginacion | Positivo |
| ADMIN-002 | Crear usuario | Verificar creacion | Login como admin | 1. Click "Nuevo Usuario"<br>2. Completar formulario<br>3. Guardar | Nombre: Juan Test<br>Email: juan@test.com | Usuario creado, listado actualizado | Positivo |
| ADMIN-003 | Crear usuario email duplicado | Verificar unicidad | Usuario existente | 1. Crear usuario con email existente | Email: admin@test.com | Error "Email ya registrado" | Negativo |
| ADMIN-004 | Editar usuario | Verificar edicion | Usuario existente | 1. Click editar<br>2. Modificar campos<br>3. Guardar | Cambiar nombre | Datos actualizados | Positivo |
| ADMIN-005 | Desactivar usuario | Verificar desactivacion | Usuario activo | 1. Click "Desactivar" | - | activo = false, usuario no puede login | Negativo |
| ADMIN-006 | Activar usuario | Verificar activacion | Usuario inactivo | 1. Click "Activar" | - | activo = true | Positivo |
| ADMIN-007 | Asignar roles | Verificar asignacion roles | Usuario existente | 1. Click editar<br>2. Seleccionar roles<br>3. Guardar | Roles: admin, secretario | Roles asignados correctamente | Positivo |
| ADMIN-008 | Asignar secretaria | Verificar asignacion secretaria | Usuario existente | 1. Seleccionar secretaria<br>2. Guardar | Secretaria: Hacienda | secretaria_id actualizado | Positivo |
| ADMIN-009 | Asignar unidad | Verificar asignacion unidad | Usuario con secretaria | 1. Seleccionar unidad<br>2. Guardar | Unidad: Compras | unidad_id actualizado | Positivo |

## Casos de Prueba - Gestion de Secretarias

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ADMIN-010 | Listar secretarias | Verificar listado | Login como admin | 1. Navegar a /admin/secretarias | - | Lista de secretarias | Positivo |
| ADMIN-011 | Crear secretaria | Verificar creacion | Login como admin | 1. Click "Nueva"<br>2. Ingresar nombre<br>3. Guardar | Nombre: "Nueva Secretaria" | Secretaria creada | Positivo |
| ADMIN-012 | Editar secretaria | Verificar edicion | Secretaria existente | 1. Click editar<br>2. Modificar<br>3. Guardar | Cambiar nombre | Nombre actualizado | Positivo |
| ADMIN-013 | Desactivar secretaria | Verificar desactivacion | Secretaria sin procesos activos | 1. Click "Desactivar" | - | activo = false | Negativo |

## Casos de Prueba - Gestion de Unidades

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ADMIN-014 | Listar unidades | Verificar listado | Login como admin | 1. Navegar a /admin/unidades | - | Lista de unidades | Positivo |
| ADMIN-015 | Crear unidad | Verificar creacion | Secretaria existente | 1. Click "Nueva"<br>2. Seleccionar secretaria<br>3. Ingresar nombre<br>4. Guardar | Secretaria: Hacienda<br>Nombre: "Nueva Unidad" | Unidad creada | Positivo |
| ADMIN-016 | Filtrar por secretaria | Verificar filtrado | Unidades en varias secretarias | 1. Seleccionar filtro secretaria | Secretaria: Hacienda | Solo unidades de Hacienda visibles | Positivo |

## Casos de Prueba - Gestion de Roles

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ADMIN-017 | Listar roles | Verificar listado | Login como admin | 1. Navegar a /admin/roles | - | Lista de roles del sistema | Positivo |
| ADMIN-018 | Ver permisos de rol | Verificar permisos | Rol existente | 1. Click en rol | - | Lista de permisos del rol | Positivo |
| ADMIN-019 | Asignar permisos a rol | Verificar asignacion | Login como admin | 1. Seleccionar rol<br>2. Marcar permisos<br>3. Guardar | Rol: secretario<br>Permisos: varios | Permisos asignados | Positivo |

## Casos de Prueba - Logs de Auditoria

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ADMIN-020 | Ver logs de autenticacion | Verificar logs | Eventos de login existentes | 1. Navegar a /admin/logs | - | Lista de eventos auth | Positivo |
| ADMIN-021 | Filtrar logs por usuario | Verificar filtrado | Varios eventos | 1. Seleccionar filtro usuario | Usuario: admin | Solo eventos del usuario | Positivo |
| ADMIN-022 | Filtrar logs por tipo | Verificar filtrado | Varios tipos eventos | 1. Seleccionar tipo "login_success" | - | Solo logins exitosos | Positivo |
| ADMIN-023 | Ver detalle evento | Verificar detalle | Evento existente | 1. Click en evento | - | IP, timestamp, user agent visibles | Positivo |

---

# 12. MODULO 09: ALERTAS Y NOTIFICACIONES

## Casos de Prueba - Visualizacion de Alertas

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ALERT-001 | Ver alertas en dashboard | Verificar visualizacion | Alertas existentes | 1. Navegar a /dashboard | - | Widget de alertas visible con conteo | Positivo |
| ALERT-002 | Ver detalle alerta | Verificar detalle | Alerta existente | 1. Click en alerta | - | Titulo, mensaje, proceso asociado visible | Positivo |
| ALERT-003 | Filtrar por prioridad | Verificar filtrado | Alertas varias prioridades | 1. Seleccionar "Alta" | - | Solo alertas alta prioridad | Positivo |
| ALERT-004 | Ver solo mis alertas | Verificar scope | Login como usuario normal | 1. Ver alertas | - | Solo alertas asignadas al usuario | Seguridad |

## Casos de Prueba - Gestion de Alertas

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ALERT-005 | Marcar alerta leida | Verificar lectura | Alerta no leida | 1. Click "Marcar como leida" | - | leida = true, timestamp guardado | Positivo |
| ALERT-006 | Ir al proceso desde alerta | Verificar navegacion | Alerta con proceso asociado | 1. Click enlace al proceso | - | Navegacion al proceso correcto | Positivo |
| ALERT-007 | Contador alertas actualiza | Verificar contador | Alertas no leidas | 1. Marcar como leida<br>2. Verificar contador | - | Contador disminuye en 1 | Positivo |

## Casos de Prueba - Generacion Automatica

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| ALERT-008 | Alerta tiempo excedido | Verificar generacion | Proceso excede dias estimados | 1. Ejecutar comando alertas:generar | Proceso con 15 dias en etapa de 5 dias | Alerta creada tipo "tiempo_excedido" | Positivo |
| ALERT-009 | Alerta documento por vencer | Verificar generacion | Documento vence en 3 dias | 1. Ejecutar comando alertas:generar | Certificado vence en 3 dias | Alerta creada tipo "documento_vencido" | Positivo |
| ALERT-010 | Prioridad critica automatica | Verificar prioridad | Documento vence en 1 dia | 1. Ejecutar comando alertas:generar | - | Alerta con prioridad "critica" | Positivo |

---

# 13. MODULO 10: REPORTES

## Casos de Prueba - Reportes

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| REP-001 | Generar reporte estado | Verificar reporte | Procesos existentes | 1. Navegar a reportes<br>2. Seleccionar "Estado General"<br>3. Generar | - | Reporte generado con datos correctos | Positivo |
| REP-002 | Filtrar reporte por fechas | Verificar filtrado | Procesos varias fechas | 1. Seleccionar rango fechas<br>2. Generar | Desde: 2026-01-01<br>Hasta: 2026-03-31 | Solo datos del rango | Positivo |
| REP-003 | Filtrar reporte por secretaria | Verificar filtrado | Procesos varias secretarias | 1. Seleccionar secretaria<br>2. Generar | Secretaria: Hacienda | Solo datos de Hacienda | Positivo |
| REP-004 | Exportar a Excel | Verificar exportacion | Reporte generado | 1. Click "Exportar Excel" | - | Archivo .xlsx descargado | Positivo |
| REP-005 | Exportar a CSV | Verificar exportacion | Reporte generado | 1. Click "Exportar CSV" | - | Archivo .csv descargado | Positivo |
| REP-006 | Ver solo datos permitidos | Verificar scope | Login como secretario | 1. Generar reporte | - | Solo datos de su secretaria | Seguridad |

---

# 14. PRUEBAS DE SEGURIDAD

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| SEC-001 | Proteccion CSRF | Verificar token CSRF | Formulario de login | 1. Intentar submit sin token CSRF | - | Request rechazado 419 | Seguridad |
| SEC-002 | Proteccion XSS en inputs | Verificar sanitizacion | Formulario proceso | 1. Ingresar <script>alert('xss')</script> en campo<br>2. Guardar<br>3. Ver | - | Script no ejecutado, texto escapado | Seguridad |
| SEC-003 | SQL Injection prevenida | Verificar proteccion | Barra de busqueda | 1. Buscar: ' OR '1'='1 | - | No resultados anormales, query segura | Seguridad |
| SEC-004 | Acceso horizontal denegado | Verificar aislamiento | Usuario de Secretaria A | 1. Intentar acceder recurso Secretaria B | ID de proceso otra secretaria | Error 403 | Seguridad |
| SEC-005 | Acceso vertical denegado | Verificar roles | Usuario sin rol admin | 1. Navegar a /admin/usuarios | - | Error 403 o redireccion | Seguridad |
| SEC-006 | Archivos sensibles protegidos | Verificar acceso archivos | Sin autenticacion | 1. Intentar acceder a /storage/procesos/archivo.pdf directo | - | Error 403 o 404 | Seguridad |
| SEC-007 | Rate limiting login | Verificar limite intentos | Ninguna | 1. Intentar 10 logins fallidos rapidos | - | Bloqueo temporal despues de N intentos | Seguridad |
| SEC-008 | Headers seguridad | Verificar headers | Cualquier pagina | 1. Inspeccionar headers respuesta | - | X-Frame-Options, CSP headers presentes | Seguridad |

---

# 15. PRUEBAS DE RENDIMIENTO

| ID | Nombre | Descripcion | Precondiciones | Pasos | Datos de Prueba | Resultado Esperado | Tipo |
|----|--------|-------------|----------------|-------|-----------------|-------------------|------|
| PERF-001 | Tiempo carga dashboard | Verificar tiempo | Usuario autenticado | 1. Navegar a /dashboard<br>2. Medir tiempo total | - | Carga completa < 3 segundos | Rendimiento |
| PERF-002 | Tiempo carga listado procesos | Verificar tiempo | 100+ procesos | 1. Navegar a /procesos<br>2. Medir tiempo | - | Carga < 2 segundos | Rendimiento |
| PERF-003 | Tiempo busqueda | Verificar tiempo | 1000+ procesos | 1. Buscar por codigo<br>2. Medir tiempo | - | Resultados < 1 segundo | Rendimiento |
| PERF-004 | Tiempo carga dashboard builder | Verificar tiempo | Usuario autenticado | 1. Navegar a /dashboards/builder<br>2. Medir tiempo | - | Carga < 4 segundos | Rendimiento |
| PERF-005 | Tiempo ejecucion widget | Verificar tiempo | Widget configurado | 1. Crear widget<br>2. Medir tiempo query | - | Datos < 2 segundos | Rendimiento |
| PERF-006 | Carga concurrente | Verificar estabilidad | 10 usuarios simultaneos | 1. Simular 10 users accediendo dashboard | - | Sin errores, tiempos aceptables | Rendimiento |

---

# RESUMEN DE CASOS DE PRUEBA

| Modulo | Total Casos | Positivos | Negativos | Seguridad | Rendimiento |
|--------|-------------|-----------|-----------|-----------|-------------|
| Autenticacion | 11 | 6 | 3 | 2 | 0 |
| Dashboard | 15 | 13 | 0 | 2 | 0 |
| Procesos | 20 | 14 | 3 | 3 | 0 |
| CD-PN | 33 | 22 | 8 | 3 | 0 |
| Documentos | 12 | 8 | 2 | 2 | 0 |
| Flujos | 10 | 8 | 2 | 0 | 0 |
| Dashboard Builder | 40 | 35 | 2 | 3 | 0 |
| Administracion | 23 | 20 | 2 | 1 | 0 |
| Alertas | 10 | 8 | 0 | 2 | 0 |
| Reportes | 6 | 4 | 0 | 2 | 0 |
| Seguridad | 8 | 0 | 0 | 8 | 0 |
| Rendimiento | 6 | 0 | 0 | 0 | 6 |
| **TOTAL** | **194** | **138** | **22** | **28** | **6** |

---

# CRITERIOS DE APROBACION

## Por Prioridad

| Prioridad | Criterio |
|-----------|----------|
| Critico | 100% casos exitosos |
| Alto | 100% casos exitosos |
| Medio | 95% casos exitosos |
| Bajo | 90% casos exitosos |

## Por Tipo

| Tipo | Criterio |
|------|----------|
| Funcional | 95% casos exitosos |
| Seguridad | 100% casos exitosos |
| Rendimiento | 90% casos dentro de umbral |

---

**Documento preparado para automatizacion con Cypress**
**Formato estructurado tipo tabla para facil copia a Excel**
