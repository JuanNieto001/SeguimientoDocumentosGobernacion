# DOCUMENTO DE EJECUCION DE PRUEBAS
## Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas

**Version:** 1.0.0
**Fecha:** 2026-03-27
**Framework:** Cypress 13.7.0
**Estado:** Listo para ejecucion

---

# 1. ESTRUCTURA DE PRUEBAS AUTOMATIZADAS

```
cypress/
├── e2e/
│   ├── 01-authentication/
│   │   ├── auth-completo.cy.js      # AUTH-001 a AUTH-011
│   │   ├── login.cy.js              # Pruebas login existentes
│   │   ├── logout.cy.js             # Pruebas logout existentes
│   │   └── roles.cy.js              # Pruebas roles existentes
│   │
│   ├── 02-dashboard/
│   │   ├── dashboard-completo.cy.js # DASH-001 a DASH-015
│   │   ├── dashboard-interactivo.cy.js
│   │   └── filtros-responsive.cy.js
│   │
│   ├── 03-procesos/
│   │   └── procesos-completo.cy.js  # PROC-001 a PROC-020
│   │
│   ├── 04-contratacion-directa/
│   │   └── cdpn-completo.cy.js      # CDPN-001 a CDPN-033
│   │
│   ├── 05-dashboard-builder/
│   │   └── dashboard-builder.cy.js  # BUILD-001 a BUILD-040
│   │
│   └── 06-seguridad-rendimiento/
│       └── seguridad-rendimiento.cy.js # SEC-001 a SEC-008, PERF-001 a PERF-006
│
├── fixtures/
│   ├── users.json
│   ├── procesos.json
│   ├── documentos.json
│   └── documentos/
│       └── estudio_previo_valido.pdf
│
├── support/
│   ├── commands.js      # Comandos personalizados
│   └── e2e.js          # Configuracion soporte
│
├── screenshots/         # Capturas automaticas
├── videos/              # Videos de ejecucion
└── downloads/           # Archivos descargados
```

---

# 2. COMANDOS DE EJECUCION

## 2.1 Preparacion del Ambiente

```bash
# 1. Instalar dependencias
npm install

# 2. Configurar base de datos de pruebas
php artisan migrate:refresh
php artisan db:seed --class=TestingSeederStructure

# 3. Iniciar servidor
php artisan serve

# 4. Compilar frontend
npm run dev
```

## 2.2 Ejecucion de Pruebas

```bash
# Abrir Cypress interactivo
npm run test

# Ejecutar todas las pruebas (headless)
npm run test:headless

# Ejecutar solo autenticacion
npm run test:auth

# Ejecutar solo dashboard
npm run test:dashboard

# Ejecutar solo CD-PN
npm run test:cdpn

# Ejecutar pruebas smoke (criticas)
npm run test:smoke

# Ejecutar con reporte
npm run test:reports

# Ejecutar en mobile
npm run test:mobile

# Ejecutar en desktop full
npm run test:desktop
```

## 2.3 Comandos Especificos por Modulo

```bash
# Autenticacion
npx cypress run --spec "cypress/e2e/01-authentication/**"

# Dashboard
npx cypress run --spec "cypress/e2e/02-dashboard/**"

# Procesos
npx cypress run --spec "cypress/e2e/03-procesos/**"

# Contratacion Directa
npx cypress run --spec "cypress/e2e/04-contratacion-directa/**"

# Dashboard Builder
npx cypress run --spec "cypress/e2e/05-dashboard-builder/**"

# Seguridad y Rendimiento
npx cypress run --spec "cypress/e2e/06-seguridad-rendimiento/**"
```

---

# 3. PLANTILLA DE RESULTADOS DE PRUEBAS

## 3.1 Formato Excel (copiar a hoja de calculo)

| ID | Modulo | Nombre | Resultado Esperado | Resultado Obtenido | Estado | Screenshot | Video | Observaciones |
|----|--------|--------|-------------------|-------------------|--------|------------|-------|---------------|
| AUTH-001 | Autenticacion | Login exitoso con credenciales validas | Redireccion a /dashboard | | PENDIENTE | | | |
| AUTH-002 | Autenticacion | Login fallido con email incorrecto | Mensaje error credenciales | | PENDIENTE | | | |
| AUTH-003 | Autenticacion | Login fallido con contrasena incorrecta | Permanecer en /login | | PENDIENTE | | | |
| AUTH-004 | Autenticacion | Login fallido con campos vacios | Validacion HTML5 | | PENDIENTE | | | |
| AUTH-005 | Autenticacion | Login fallido con usuario inactivo | Mensaje usuario desactivado | | PENDIENTE | | | |
| AUTH-006 | Autenticacion | Login con Recordarme | Sesion persistida | | PENDIENTE | | | |
| AUTH-007 | Autenticacion | Redireccion segun rol planeacion | Redireccion a /planeacion | | PENDIENTE | | | |
| AUTH-008 | Autenticacion | Formato email invalido | Validacion email invalido | | PENDIENTE | | | |
| AUTH-009 | Autenticacion | Logout exitoso | Redireccion a /login | | PENDIENTE | | | |
| AUTH-010 | Autenticacion | Acceso ruta protegida sin sesion | Redireccion a /login | | PENDIENTE | | | |
| AUTH-011 | Autenticacion | Regeneracion token sesion | Session ID diferente | | PENDIENTE | | | |
| DASH-001 | Dashboard | Ver dashboard segun rol admin | Widgets visibles | | PENDIENTE | | | |
| DASH-002 | Dashboard | Ver dashboard segun rol secretario | Datos de secretaria | | PENDIENTE | | | |
| DASH-003 | Dashboard | Ver dashboard segun rol jefe_unidad | Datos de unidad | | PENDIENTE | | | |
| DASH-004 | Dashboard | KPIs muestran valores correctos | Valores numericos | | PENDIENTE | | | |
| DASH-005 | Dashboard | Graficas cargan correctamente | Graficas visibles | | PENDIENTE | | | |
| DASH-006 | Dashboard | Busqueda global funciona | Resultados relevantes | | PENDIENTE | | | |
| DASH-008 | Dashboard | Navegacion lateral funciona | Navegacion correcta | | PENDIENTE | | | |
| DASH-009 | Dashboard | Dashboard responsive tablet | Layout adaptado | | PENDIENTE | | | |
| DASH-010 | Dashboard | Dashboard responsive mobile | Layout una columna | | PENDIENTE | | | |
| DASH-011 | Dashboard | Acceso a mi-dashboard | Dashboard cargado | | PENDIENTE | | | |
| PROC-001 | Procesos | Ver listado de procesos | Tabla visible | | PENDIENTE | | | |
| PROC-002 | Procesos | Filtrar por estado | Solo estado seleccionado | | PENDIENTE | | | |
| PROC-005 | Procesos | Buscar por codigo | Proceso encontrado | | PENDIENTE | | | |
| PROC-006 | Procesos | Paginacion funciona | Navegacion paginas | | PENDIENTE | | | |
| PROC-008 | Procesos | Admin ve todos los procesos | Todos visibles | | PENDIENTE | | | |
| PROC-009 | Procesos | Crear proceso exitoso | Proceso creado | | PENDIENTE | | | |
| PROC-010 | Procesos | Crear proceso sin estudio previo | Error validacion | | PENDIENTE | | | |
| PROC-011 | Procesos | Crear proceso con valor 0 | Error validacion | | PENDIENTE | | | |
| PROC-013 | Procesos | Crear proceso sin permiso | Error 403 | | PENDIENTE | | | |
| PROC-015 | Procesos | Ver detalle proceso | Datos visibles | | PENDIENTE | | | |
| PROC-019 | Procesos | Ver proceso inexistente | Error 404 | | PENDIENTE | | | |
| CDPN-001 | CD-PN | Crear CD-PN exitoso | Estado BORRADOR | | PENDIENTE | | | |
| CDPN-002 | CD-PN | Validar campos requeridos | Mensajes error | | PENDIENTE | | | |
| CDPN-003 | CD-PN | Cargar estudio previo PDF | Archivo cargado | | PENDIENTE | | | |
| CDPN-011 | CD-PN | Solicitar CDP sin compatibilidad | Error regla negocio | | PENDIENTE | | | |
| BUILD-001 | DashboardBuilder | Acceder dashboard builder | Builder cargado | | PENDIENTE | | | |
| BUILD-002 | DashboardBuilder | Cargar catalogo entidades | Entidades listadas | | PENDIENTE | | | |
| BUILD-003 | DashboardBuilder | Expandir entidad | Campos visibles | | PENDIENTE | | | |
| BUILD-004 | DashboardBuilder | Ver scope indicator | Indicador visible | | PENDIENTE | | | |
| BUILD-005 | DashboardBuilder | Arrastrar campo al canvas | Widget creado | | PENDIENTE | | | |
| BUILD-022 | DashboardBuilder | Scope aplicado automaticamente | Solo datos permitidos | | PENDIENTE | | | |
| BUILD-023 | DashboardBuilder | Scope global para admin | Todos los datos | | PENDIENTE | | | |
| BUILD-025 | DashboardBuilder | Guardar dashboard | Dashboard persistido | | PENDIENTE | | | |
| BUILD-026 | DashboardBuilder | Cargar dashboard | Dashboard restaurado | | PENDIENTE | | | |
| SEC-001 | Seguridad | Proteccion CSRF | Request rechazado 419 | | PENDIENTE | | | |
| SEC-002 | Seguridad | Proteccion XSS en inputs | Script no ejecutado | | PENDIENTE | | | |
| SEC-003 | Seguridad | SQL Injection prevenida | Query segura | | PENDIENTE | | | |
| SEC-004 | Seguridad | Acceso horizontal denegado | Error 403 | | PENDIENTE | | | |
| SEC-005 | Seguridad | Acceso vertical denegado | Error 403 | | PENDIENTE | | | |
| SEC-006 | Seguridad | Archivos sensibles protegidos | Error 403/404 | | PENDIENTE | | | |
| SEC-007 | Seguridad | Rate limiting login | Bloqueo temporal | | PENDIENTE | | | |
| SEC-008 | Seguridad | Headers seguridad | Headers presentes | | PENDIENTE | | | |
| PERF-001 | Rendimiento | Tiempo carga dashboard | < 3 segundos | | PENDIENTE | | | |
| PERF-002 | Rendimiento | Tiempo carga listado procesos | < 2 segundos | | PENDIENTE | | | |
| PERF-004 | Rendimiento | Tiempo carga dashboard builder | < 4 segundos | | PENDIENTE | | | |
| PERF-005 | Rendimiento | Tiempo ejecucion widget | < 2 segundos | | PENDIENTE | | | |

---

# 4. UBICACION DE EVIDENCIAS

## 4.1 Screenshots
```
cypress/screenshots/
├── 01-authentication/
│   └── auth-completo.cy.js/
│       ├── AUTH-001-login-exitoso_2026-03-27T...png
│       ├── AUTH-002-email-incorrecto_2026-03-27T...png
│       └── ...
├── 02-dashboard/
│   └── dashboard-completo.cy.js/
│       └── ...
└── ...
```

## 4.2 Videos
```
cypress/videos/
├── 01-authentication/
│   └── auth-completo.cy.js.mp4
├── 02-dashboard/
│   └── dashboard-completo.cy.js.mp4
└── ...
```

---

# 5. RESUMEN DE COBERTURA

| Modulo | Casos Totales | Implementados | Cobertura |
|--------|---------------|---------------|-----------|
| Autenticacion | 11 | 11 | 100% |
| Dashboard | 15 | 12 | 80% |
| Procesos | 20 | 12 | 60% |
| CD-PN | 33 | 20 | 61% |
| Dashboard Builder | 40 | 25 | 63% |
| Seguridad | 8 | 8 | 100% |
| Rendimiento | 6 | 4 | 67% |
| **TOTAL** | **133** | **92** | **69%** |

---

# 6. CRITERIOS DE APROBACION

## Por Modulo

| Modulo | Criterio Minimo | Estado |
|--------|-----------------|--------|
| Autenticacion | 100% exitoso | PENDIENTE |
| Dashboard | 90% exitoso | PENDIENTE |
| Procesos | 90% exitoso | PENDIENTE |
| CD-PN | 85% exitoso | PENDIENTE |
| Dashboard Builder | 85% exitoso | PENDIENTE |
| Seguridad | 100% exitoso | PENDIENTE |
| Rendimiento | 80% dentro de umbral | PENDIENTE |

## Global

- **Aprobado:** >= 90% casos exitosos, 0 criticos fallidos
- **Aprobado con observaciones:** >= 80% casos exitosos, 0 criticos fallidos
- **Rechazado:** < 80% casos exitosos o 1+ criticos fallidos

---

# 7. INSTRUCCIONES PARA GENERAR REPORTE FINAL

1. Ejecutar todas las pruebas:
```bash
npm run test:headless
```

2. Generar reporte HTML:
```bash
npm run test:reports
```

3. Ubicar reportes en:
```
mochawesome-report/
├── mochawesome.html
└── mochawesome.json
```

4. Copiar tabla de resultados a Excel

5. Adjuntar screenshots y videos relevantes

---

**Documento preparado para auditoria**
**Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas**
