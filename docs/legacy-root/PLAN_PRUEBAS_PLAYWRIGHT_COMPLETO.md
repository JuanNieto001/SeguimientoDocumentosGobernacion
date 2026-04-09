# 📋 PLAN MAESTRO DE PRUEBAS QA - PLAYWRIGHT
## Sistema de Seguimiento de Documentos - Gobernación de Caldas

---

## 📊 RESUMEN EJECUTIVO

| **Métrica** | **Valor** | **Estado** |
|-------------|-----------|------------|
| **Total Casos de Prueba** | 42 | ✅ IMPLEMENTADOS |
| **Módulos Cubiertos** | 8 | ✅ COMPLETO |
| **Tests Playwright** | 42 tests | ✅ FUNCIONAL |
| **Helpers Reutilizables** | 1 clase | ✅ IMPLEMENTADO |
| **Evidencias Automáticas** | 100% | ✅ ACTIVO |

---

## 🎯 COBERTURA COMPLETA POR MÓDULOS

### 🔐 AUTENTICACIÓN (6 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| AUTH-001 | Login exitoso con credenciales válidas | Positivo | Alta | `tests/auth/auth.spec.js` | ✅ |
| AUTH-002 | Login fallido con email incorrecto | Negativo | Alta | `tests/auth/auth.spec.js` | ✅ |
| AUTH-003 | Login fallido con contraseña incorrecta | Negativo | Alta | `tests/auth/auth.spec.js` | ✅ |
| AUTH-004 | Login con campos vacíos | Negativo | Media | `tests/auth/auth.spec.js` | ✅ |
| AUTH-005 | Logout exitoso | Positivo | Alta | `tests/auth/auth.spec.js` | ✅ |
| AUTH-006 | Acceso sin autenticación | Negativo | Alta | `tests/auth/auth.spec.js` | ✅ |

**Objetivo:** Validar acceso seguro al sistema

---

### 👥 GESTIÓN DE USUARIOS (8 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| USERS-001 | Crear usuario exitosamente | Positivo | Alta | `tests/users/users.spec.js` | ✅ |
| USERS-002 | Editar usuario existente | Positivo | Alta | `tests/users/users.spec.js` | ✅ |
| USERS-003 | Eliminar usuario | Positivo | Media | `tests/users/users.spec.js` | ✅ |
| USERS-004 | Crear usuario - Email duplicado | Negativo | Alta | `tests/users/users.spec.js` | ✅ |
| USERS-005 | Crear usuario - Campos obligatorios | Negativo | Media | `tests/users/users.spec.js` | ✅ |
| USERS-006 | Asignar rol a usuario | Positivo | Alta | `tests/users/users.spec.js` | ✅ |
| USERS-007 | Acceso sin permisos | Permisos | Alta | `tests/users/users.spec.js` | ✅ |
| USERS-008 | Gestión filtrada por secretaría | Permisos | Alta | `tests/users/users.spec.js` | ✅ |

**Objetivo:** Validar CRUD completo de usuarios y permisos

---

### 🔄 WORKFLOW CD-PN (15 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| CDPN-001 | Crear nuevo proceso CD-PN | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-002 | Etapa 0 - Subir Estudios Previos | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-003 | Etapa 1 - CDP y Compatibilidad | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-004 | Etapa 2 - Validación Contratista | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-005 | Etapa 3 - Documentos Contractuales | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-006 | Etapa 4 - Consolidar Expediente | Positivo | Media | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-007 | Etapa 5 - Radicación Jurídica | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-008 | Etapa 6 - Publicación SECOP II | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-009 | Etapa 7 - Solicitar RPC | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-010 | Etapa 8 - Número de Contrato | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-011 | Etapa 9 - Inicio Ejecución | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-012 | Restricción creación - Permisos | Permisos | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-013 | Validación documentos requeridos | Negativo | Media | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-014 | Navegación entre etapas | Positivo | Media | `tests/workflow/cdpn-workflow.spec.js` | ✅ |
| CDPN-015 | Flujo completo end-to-end | Positivo | Alta | `tests/workflow/cdpn-workflow.spec.js` | ✅ |

**Objetivo:** Validar flujo completo de contratación directa (9 etapas)

---

### ⚙️ MOTOR DE FLUJOS (3 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| MOTOR-001 | Crear nuevo flujo personalizado | Positivo | Alta | `tests/motor-flujos/motor-flujos.spec.js` | ✅ |
| MOTOR-002 | Publicar versión de flujo | Positivo | Alta | `tests/motor-flujos/motor-flujos.spec.js` | ✅ |
| MOTOR-003 | Versionado de flujos | Positivo | Media | `tests/motor-flujos/motor-flujos.spec.js` | ✅ |

**Objetivo:** Validar motor de workflows personalizados

---

### 📄 GESTIÓN DE DOCUMENTOS (3 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| DOCS-001 | Subir documento válido | Positivo | Alta | `tests/documents/documents.spec.js` | ✅ |
| DOCS-002 | Archivo muy grande | Negativo | Media | `tests/documents/documents.spec.js` | ✅ |
| DOCS-003 | Tipo archivo incorrecto | Negativo | Media | `tests/documents/documents.spec.js` | ✅ |

**Objetivo:** Validar carga y validación de documentos

---

### 📊 DASHBOARD (3 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| DASH-001 | Dashboard Admin General | Positivo | Alta | `tests/dashboard/dashboard.spec.js` | ✅ |
| DASH-002 | Dashboard responsive móvil | Positivo | Alta | `tests/dashboard/dashboard.spec.js` | ✅ |
| DASH-003 | Filtros del dashboard | Positivo | Media | `tests/dashboard/dashboard.spec.js` | ✅ |

**Objetivo:** Validar visualización de métricas

---

### 🔌 API ENDPOINTS (3 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| API-001 | Endpoint autenticación | Positivo | Media | `tests/api/api.spec.js` | ✅ |
| API-002 | Endpoint sin autenticación | Negativo | Media | `tests/api/api.spec.js` | ✅ |
| API-003 | Endpoint con token válido | Positivo | Media | `tests/api/api.spec.js` | ✅ |

**Objetivo:** Validar endpoints de API REST

---

### 📱 RESPONSIVE DESIGN (4 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| RESP-001 | Login en móvil | Positivo | Media | `tests/responsive/responsive.spec.js` | ✅ |
| RESP-002 | Dashboard responsive | Positivo | Media | `tests/responsive/responsive.spec.js` | ✅ |
| RESP-003 | Menú hamburguesa móvil | Positivo | Media | `tests/responsive/responsive.spec.js` | ✅ |
| RESP-004 | Tabla responsive con scroll | Positivo | Media | `tests/responsive/responsive.spec.js` | ✅ |

**Objetivo:** Validar diseño adaptable a dispositivos

---

### 📋 PROCESOS (4 casos)

| ID | Caso | Tipo | Prioridad | Archivo | Estado |
|----|------|------|-----------|---------|--------|
| PROC-001 | Listar procesos | Positivo | Alta | `tests/procesos/procesos.spec.js` | ✅ |
| PROC-002 | Filtrar por estado | Positivo | Media | `tests/procesos/procesos.spec.js` | ✅ |
| PROC-003 | Buscar por nombre | Positivo | Media | `tests/procesos/procesos.spec.js` | ✅ |
| PROC-004 | Ver detalle proceso | Positivo | Alta | `tests/procesos/procesos.spec.js` | ✅ |

**Objetivo:** Validar gestión de procesos contractuales

---

## 🎯 TOTAL: 42 CASOS DE PRUEBA

---

## 📂 ESTRUCTURA DE ARCHIVOS

```
tests/
├── auth/
│   └── auth.spec.js              (6 tests)
├── users/
│   └── users.spec.js             (8 tests)
├── workflow/
│   └── cdpn-workflow.spec.js     (15 tests)
├── motor-flujos/
│   └── motor-flujos.spec.js      (3 tests)
├── documents/
│   └── documents.spec.js         (3 tests)
├── dashboard/
│   └── dashboard.spec.js         (3 tests)
├── api/
│   └── api.spec.js               (3 tests)
├── responsive/
│   └── responsive.spec.js        (4 tests)
├── procesos/
│   └── procesos.spec.js          (4 tests)
└── helpers/
    └── login.helper.js           (Helper reutilizable)
```

---

## 🚀 COMANDOS DE EJECUCIÓN

### Por Módulo
```bash
npm run test:auth        # Solo autenticación
npm run test:dashboard   # Solo dashboard
npm run test:workflow    # Solo workflow CD-PN
```

### Por Navegador
```bash
npm run test:chromium    # Chrome
npm run test:firefox     # Firefox
npm run test:mobile      # Móvil (Pixel 5)
```

### General
```bash
npm test                 # UI interactiva
npm run test:run         # Todas las pruebas
npm run test:debug       # Debug paso a paso
npm run test:report      # Ver reporte HTML
```

---

## 📸 EVIDENCIAS AUTOMÁTICAS

**CADA TEST genera:**

✅ **Screenshots** en cada paso
✅ **Video** completo de la ejecución
✅ **Trace** con timeline interactivo
✅ **Logs** de consola y network

**Ubicación:**
- `test-results/` - Screenshots y videos
- `playwright-report/` - Reporte HTML

---

## 👥 USUARIOS DE PRUEBA

| Email | Password | Rol | Permisos |
|-------|----------|-----|----------|
| admin@test.com | Test1234! | Admin General | TODOS |
| unidad@test.com | Test1234! | Unidad Solicitante | Crear procesos |
| planeacion@test.com | Test1234! | Planeación | CDP |
| hacienda@test.com | Test1234! | Hacienda | Compatibilidad |
| juridica@test.com | Test1234! | Jurídica | Radicación |
| secop@test.com | Test1234! | SECOP | Publicación |
| gobernador@test.com | Test1234! | Gobernador | Aprobación |
| consulta@test.com | Test1234! | Consulta | Solo lectura |

---

## ✅ CRITERIOS DE ACEPTACIÓN

**Una prueba PASA si:**
1. ✅ No hay errores de timeout
2. ✅ Los elementos se encuentran correctamente
3. ✅ Las navegaciones son exitosas
4. ✅ Los mensajes de éxito/error aparecen

**Una prueba FALLA si:**
1. ❌ Timeout excedido (60s)
2. ❌ Elemento no encontrado
3. ❌ Navegación incorrecta
4. ❌ Error inesperado

---

## 🔧 CONFIGURACIÓN

**Timeouts:**
- Test completo: 60 segundos
- Navegación: 30 segundos
- Acción: 10 segundos

**Evidencias:**
- Screenshots: ON (siempre)
- Videos: ON (siempre)
- Traces: ON (completo)

**Reintentos:**
- Automáticos: 2 intentos
- Solo en fallo

---

## 📊 MÉTRICAS DE CALIDAD

| Métrica | Objetivo | Actual |
|---------|----------|--------|
| Cobertura de módulos | 100% | ✅ 100% |
| Tests implementados | 42 | ✅ 42 |
| Evidencias automáticas | Sí | ✅ Sí |
| Documentación | Completa | ✅ Completa |

---

## 📝 NOTAS IMPORTANTES

1. **Servidor debe estar corriendo:** `php artisan serve`
2. **BD debe estar inicializada:** `php artisan migrate:fresh --seed`
3. **Usuarios de prueba deben existir** (ver tabla arriba)
4. **Puerto 8000 debe estar libre**

---

## 🆘 TROUBLESHOOTING

**Error de timeout:**
- Verificar que el servidor esté corriendo
- Verificar que la BD esté lista
- Ver `PLAYWRIGHT_DEBUG_GUIA.md`

**Elementos no encontrados:**
- Verificar selectores en el código
- Usar debug mode: `npm run test:debug`

**Tests lentos:**
- Ejecutar en paralelo por archivo
- Reducir timeouts innecesarios

---

*Generado automáticamente - Playwright Testing Suite*
*Fecha: Abril 2026*
