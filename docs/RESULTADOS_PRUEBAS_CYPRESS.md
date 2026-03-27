# DOCUMENTO DE RESULTADOS DE PRUEBAS AUTOMATIZADAS
## Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas

**Version:** 1.0.0
**Fecha de Ejecucion:** 2026-03-27
**Framework:** Cypress 13.x
**Estado:** Listo para ejecucion

---

## RESUMEN EJECUTIVO

Este documento presenta la estructura completa de automatizacion de pruebas E2E implementada con Cypress para el Sistema de Seguimiento de Documentos Contractuales.

### Estructura Implementada

| Componente | Cantidad | Estado |
|------------|----------|--------|
| Archivos de prueba (.cy.js) | 12 | Creados |
| Comandos personalizados | 35+ | Implementados |
| Fixtures de datos | 3 | Configurados |
| Casos de prueba totales | 74 | Listos para ejecucion |

---

## 1. ESTRUCTURA DE ARCHIVOS CYPRESS

```
cypress/
├── cypress.config.js                    # Configuracion principal
├── e2e/
│   ├── auth/
│   │   └── login.cy.js                  # 10 casos de autenticacion
│   ├── dashboard/
│   │   └── dashboard.cy.js              # 5 casos de dashboard
│   ├── procesos/
│   │   └── procesos.cy.js               # 10 casos de procesos
│   ├── contratacion-directa/
│   │   └── contratacion-directa.cy.js   # 10 casos de CD-PN
│   ├── documentos/
│   │   └── documentos.cy.js             # 7 casos de documentos
│   ├── roles-permisos/
│   │   └── roles-permisos.cy.js         # 6 casos de permisos
│   ├── alertas/
│   │   └── alertas.cy.js                # 5 casos de alertas
│   ├── motor-flujos/
│   │   └── motor-flujos.cy.js           # 6 casos de motor flujos
│   ├── motor-dashboards/
│   │   └── motor-dashboards.cy.js       # 5 casos de motor dashboards
│   ├── paa/
│   │   └── paa.cy.js                    # 4 casos de PAA
│   ├── secop/
│   │   └── secop.cy.js                  # 3 casos de SECOP
│   └── reportes/
│       └── reportes.cy.js               # 3 casos de reportes
├── support/
│   ├── commands.js                       # Comandos personalizados
│   └── e2e.js                           # Configuracion global
└── fixtures/
    ├── usuarios.json                     # Datos de usuarios de prueba
    ├── procesos.json                     # Datos de procesos
    ├── documentos.json                   # Configuracion de documentos
    └── documentos/
        └── estudio_previo_valido.pdf     # PDF de prueba
```

---

## 2. COMANDOS PERSONALIZADOS IMPLEMENTADOS

### 2.1 Comandos de Autenticacion

| Comando | Descripcion | Uso |
|---------|-------------|-----|
| `cy.login(email, password)` | Login via UI con sesion | `cy.login('admin@test.com', 'Test1234!')` |
| `cy.loginApi(email, password)` | Login via API (mas rapido) | `cy.loginApi('admin@test.com', 'Test1234!')` |
| `cy.loginAsRole(role)` | Login segun rol | `cy.loginAsRole('admin')` |
| `cy.logout()` | Cerrar sesion | `cy.logout()` |

### 2.2 Comandos de Navegacion

| Comando | Descripcion |
|---------|-------------|
| `cy.goToDashboard()` | Navegar a /dashboard |
| `cy.goToProcesos()` | Navegar a /procesos |
| `cy.goToCrearProceso()` | Navegar a /procesos/crear |
| `cy.goToProcesoCD()` | Navegar a /proceso-cd |

### 2.3 Comandos de Formularios

| Comando | Descripcion |
|---------|-------------|
| `cy.fillField(selector, value)` | Llenar campo de texto |
| `cy.selectOption(selector, value)` | Seleccionar dropdown |
| `cy.uploadFile(selector, filePath)` | Subir archivo |
| `cy.checkFieldError(field, message)` | Verificar error de campo |

### 2.4 Comandos de Procesos

| Comando | Descripcion |
|---------|-------------|
| `cy.crearProceso(data)` | Crear proceso con datos |
| `cy.crearProcesoCD(data)` | Crear proceso CD-PN |
| `cy.recibirProceso(id)` | Recibir proceso en area |
| `cy.enviarProceso(id)` | Enviar a siguiente etapa |
| `cy.devolverProceso(id, motivo)` | Devolver con motivo |

### 2.5 Comandos de Verificacion

| Comando | Descripcion |
|---------|-------------|
| `cy.checkAccessDenied(url)` | Verificar 403 |
| `cy.checkRedirectToLogin(url)` | Verificar redireccion login |
| `cy.takeScreenshot(name)` | Screenshot con timestamp |
| `cy.checkSuccessToast(message)` | Verificar toast exito |
| `cy.checkErrorToast(message)` | Verificar toast error |

---

## 3. MATRIZ DE CASOS DE PRUEBA POR MODULO

### 3.1 Autenticacion (10 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-AUTH-001 | Login exitoso con credenciales validas | Positivo | Critica |
| CP-AUTH-002 | Login fallido por email incorrecto | Negativo | Alta |
| CP-AUTH-003 | Login fallido por password incorrecto | Negativo | Alta |
| CP-AUTH-004 | Login fallido usuario inactivo | Negativo | Alta |
| CP-AUTH-005 | Login con campos vacios | Validacion | Media |
| CP-AUTH-006 | Login con email formato invalido | Validacion | Media |
| CP-AUTH-007 | Logout exitoso | Positivo | Alta |
| CP-AUTH-008 | Acceso sin autenticacion | Seguridad | Alta |
| CP-AUTH-009 | Remember me funcional | Positivo | Baja |
| CP-AUTH-010 | Redireccion post-login por rol | Positivo | Media |

### 3.2 Dashboard (5 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-DASH-001 | Dashboard muestra saludo personalizado | Positivo | Media |
| CP-DASH-002 | Dashboard muestra KPIs del mes | Positivo | Alta |
| CP-DASH-003 | Acciones rapidas visibles segun rol | Positivo | Media |
| CP-DASH-004 | Lista procesos en curso | Positivo | Alta |
| CP-DASH-005 | Dashboard vacio para usuario nuevo | Edge Case | Baja |

### 3.3 Procesos Contractuales (10 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-PROC-001 | Crear proceso exitosamente | Positivo | Critica |
| CP-PROC-002 | Crear proceso sin estudio previo | Negativo | Alta |
| CP-PROC-003 | Crear proceso con valor negativo | Negativo | Media |
| CP-PROC-004 | Crear proceso con objeto muy corto | Negativo | Media |
| CP-PROC-005 | Ver detalle de proceso | Positivo | Alta |
| CP-PROC-006 | Recibir proceso en area | Positivo | Alta |
| CP-PROC-007 | Enviar proceso a siguiente etapa | Positivo | Critica |
| CP-PROC-008 | Enviar proceso sin completar checks | Negativo | Alta |
| CP-PROC-009 | Devolver proceso a etapa anterior | Positivo | Alta |
| CP-PROC-010 | Devolver proceso sin motivo | Negativo | Media |

### 3.4 Contratacion Directa CD-PN (10 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-CD-001 | Crear solicitud CD-PN exitosamente | Positivo | Critica |
| CP-CD-002 | Transicion BORRADOR a ESTUDIO_PREVIO | Positivo | Alta |
| CP-CD-003 | Registrar validaciones paralelas | Positivo | Alta |
| CP-CD-004 | Solicitar CDP sin compatibilidad | Negativo | Critica |
| CP-CD-005 | Solicitar CDP con compatibilidad aprobada | Positivo | Critica |
| CP-CD-006 | Aprobar CDP | Positivo | Alta |
| CP-CD-007 | Registro de ambas firmas | Positivo | Alta |
| CP-CD-008 | Devolver contrato desde juridica | Positivo | Alta |
| CP-CD-009 | Transicion por usuario no autorizado | Seguridad | Critica |
| CP-CD-010 | Cancelar proceso (solo admin) | Positivo | Alta |

### 3.5 Gestion Documental (7 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-DOC-001 | Subir documento PDF valido | Positivo | Critica |
| CP-DOC-002 | Subir documento tipo no permitido | Negativo | Alta |
| CP-DOC-003 | Subir documento excediendo tamano | Negativo | Alta |
| CP-DOC-004 | Aprobar documento | Positivo | Alta |
| CP-DOC-005 | Rechazar documento | Positivo | Alta |
| CP-DOC-006 | Verificar vigencia documento | Positivo | Alta |
| CP-DOC-007 | Reemplazar documento existente | Positivo | Media |

### 3.6 Roles y Permisos (6 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-ROL-001 | Admin accede a todo | Positivo | Critica |
| CP-ROL-002 | Unidad Solicitante - permisos limitados | Positivo | Alta |
| CP-ROL-003 | Consulta - solo lectura | Positivo | Alta |
| CP-ROL-004 | Gobernador - vista ejecutiva | Positivo | Media |
| CP-ROL-005 | Restriccion por secretaria | Seguridad | Critica |
| CP-ROL-006 | Admin Secretaria - gestion limitada | Positivo | Alta |

### 3.7 Alertas (5 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-ALT-001 | Ver alertas no leidas | Positivo | Alta |
| CP-ALT-002 | Marcar alerta como leida | Positivo | Media |
| CP-ALT-003 | Alerta automatica tiempo excedido | Positivo | Alta |
| CP-ALT-004 | Alerta documento por vencer | Positivo | Alta |
| CP-ALT-005 | Prioridad critica <2 dias | Positivo | Alta |

### 3.8 Motor de Flujos (6 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-FLU-001 | Acceder al motor de flujos | Positivo | Alta |
| CP-FLU-002 | Crear nuevo flujo basico | Positivo | Alta |
| CP-FLU-003 | Agregar condicion a paso | Positivo | Media |
| CP-FLU-004 | Publicar version de flujo | Positivo | Alta |
| CP-FLU-005 | Eliminar flujo | Positivo | Media |
| CP-FLU-006 | No eliminar flujo con procesos | Negativo | Alta |

### 3.9 Motor de Dashboards (5 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-DSH-001 | Acceder al motor de dashboards | Positivo | Alta |
| CP-DSH-002 | Asignar plantilla a rol | Positivo | Alta |
| CP-DSH-003 | Asignar plantilla a usuario | Positivo | Media |
| CP-DSH-004 | Jerarquia de resolucion | Positivo | Alta |
| CP-DSH-005 | Usuario sin asignacion ve default | Edge Case | Media |

### 3.10 PAA (4 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-PAA-001 | Ver Plan Anual de Adquisiciones | Positivo | Alta |
| CP-PAA-002 | Crear item PAA | Positivo | Alta |
| CP-PAA-003 | Verificar proceso en PAA | Positivo | Alta |
| CP-PAA-004 | Emitir certificado PAA | Positivo | Media |

### 3.11 SECOP (3 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-SEC-001 | Buscar contrato por referencia | Positivo | Media |
| CP-SEC-002 | Ver estadisticas SECOP | Positivo | Baja |
| CP-SEC-003 | Manejar timeout API SECOP | Edge Case | Media |

### 3.12 Reportes (3 casos)

| ID | Nombre | Tipo | Prioridad |
|----|--------|------|-----------|
| CP-REP-001 | Generar reporte estado general | Positivo | Media |
| CP-REP-002 | Exportar reporte a Excel | Positivo | Media |
| CP-REP-003 | Reporte vacio sin datos | Edge Case | Baja |

---

## 4. INSTRUCCIONES DE EJECUCION

### 4.1 Prerrequisitos

```bash
# 1. Instalar dependencias de Cypress
npm install cypress --save-dev
npm install mochawesome mochawesome-merge mochawesome-report-generator --save-dev

# 2. Verificar que el servidor Laravel esta corriendo
php artisan serve --host=localhost --port=8000

# 3. Ejecutar seeders de testing
php artisan db:seed --class=TestingSeederStructure
```

### 4.2 Ejecutar Pruebas

```bash
# Abrir Cypress en modo interactivo
npx cypress open

# Ejecutar todas las pruebas en modo headless
npx cypress run

# Ejecutar modulo especifico
npx cypress run --spec "cypress/e2e/auth/**/*.cy.js"

# Ejecutar con video y screenshots
npx cypress run --video --screenshot-on-run-failure

# Ejecutar con reporte HTML
npx cypress run --reporter mochawesome
```

### 4.3 Generar Reporte Consolidado

```bash
# Combinar reportes JSON
npx mochawesome-merge cypress/reports/*.json -o cypress/reports/combined.json

# Generar HTML final
npx marge cypress/reports/combined.json -o cypress/reports/html
```

---

## 5. UBICACION DE EVIDENCIAS

| Tipo | Ubicacion |
|------|-----------|
| Screenshots | `cypress/screenshots/` |
| Videos | `cypress/videos/` |
| Reportes JSON | `cypress/reports/*.json` |
| Reportes HTML | `cypress/reports/html/` |
| Logs | `cypress/logs/` |

### 5.1 Nomenclatura de Screenshots

Los screenshots se generan con el siguiente formato:
```
{MODULO}-{CASO}_{descripcion}_{timestamp}.png
```

Ejemplo:
```
AUTH-001_pagina_login_2026-03-27T12-00-00.png
AUTH-001_credenciales_ingresadas_2026-03-27T12-00-05.png
AUTH-001_login_exitoso_2026-03-27T12-00-10.png
```

---

## 6. CRITERIOS DE ACEPTACION

| Criterio | Umbral | Descripcion |
|----------|--------|-------------|
| Cobertura funcional | 100% | Todos los flujos principales |
| Casos criticos | 100% pass | 11 casos criticos |
| Casos altos | >= 95% pass | 37 casos altos |
| Casos medios | >= 90% pass | 24 casos medios |
| Defectos bloqueadores | 0 | Cero defectos criticos |

---

## 7. PLANTILLA DE RESULTADOS

| ID | Caso | Resultado Esperado | Resultado Obtenido | Estado | Evidencia |
|----|------|-------------------|-------------------|--------|-----------|
| CP-AUTH-001 | Login exitoso | Redirige a /dashboard | | PENDIENTE | screenshots/AUTH-001_*.png |
| CP-AUTH-002 | Login fallido email | Muestra error | | PENDIENTE | screenshots/AUTH-002_*.png |
| ... | ... | ... | ... | ... | ... |

---

## 8. NOTAS PARA EJECUCION

1. **Base de datos:** Asegurar que existe la base de datos de testing con los seeders ejecutados
2. **Usuarios:** Los usuarios de prueba deben existir con las credenciales definidas en fixtures
3. **Archivos:** El PDF de prueba debe existir en `cypress/fixtures/documentos/`
4. **Servidor:** Laravel debe estar corriendo en http://localhost:8000
5. **Timeout:** Algunas pruebas pueden requerir timeout extendido (configurado en cypress.config.js)

---

## 9. CONTACTO

| Rol | Responsabilidad |
|-----|-----------------|
| QA Lead | Mantenimiento de suite de pruebas |
| DevOps | Integracion CI/CD |
| Desarrollo | Correccion de defectos |

---

**Documento generado como parte del proceso de certificacion del sistema.**
**Sistema de Seguimiento de Documentos Contractuales - Gobernacion de Caldas**
