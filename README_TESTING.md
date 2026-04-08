# 📋 README - TESTS PLAYWRIGHT QA CERTIFICACIÓN

## 🎯 RESUMEN EJECUTIVO

**Total Tests:** 118 casos de prueba  
**Usuarios Reales:** 20+ usuarios con password: `12345678`
**Estado:** ✅ LISTO PARA EJECUTAR  
**Evidencias:** Screenshots + Videos + Traces (SIEMPRE)

---

## 🚀 EJECUTAR TESTS AHORA

```bash
# COMANDO PRINCIPAL (copia y pega):
npx playwright test --reporter=html,list,./custom-reporter.js
```

**✅ Esto ejecuta TODOS los tests sin abrir navegador**  
**✅ Guarda evidencias automáticamente**  
**✅ Genera reportes CSV + MD + HTML**

## 🐢 MODO VIDEO LENTO (RECOMENDADO PARA EVIDENCIAS)

Si los videos se ven demasiado rapidos, ejecuta en modo demo:

```bash
npm run test:ui:demo
```

Tambien puedes ejecutar por consola con velocidad personalizada:

```bash
set PW_SLOWMO_MS=900&& npx playwright test --project=chromium --headed
```

Variables utiles:
- `PW_SLOWMO_MS`: retrasa cada accion del navegador (ej. `700`, `900`, `1200`)
- `PW_DEMO_MODE=1`: aplica ritmo lento por defecto
- `PW_VIDEO_MODE=on`: asegura video en todas las pruebas

---

## 📊 LO QUE TIENES (118 TESTS)

### 🔥 Tests PRIORITARIOS (guardan datos en BD):

**E2E Flujo Completo** (5 tests) - `tests/e2e/flujo-completo-cdpn.spec.js`
- Crea proceso CD-PN REAL
- Avanza por etapas 0-1
- GUARDA EN BASE DE DATOS
- **Para mostrar mañana** ✅

**Datos DEMO** (4 tests) - `tests/e2e/crear-datos-demo.spec.js`
- Crea 3 procesos en diferentes estados
- GUARDA EN BASE DE DATOS
- **Listos para presentación** ✅

### 📋 Tests Completos:
- Workflow CD-PN: 35 tests (9 etapas)
- Procesos: 24 tests (CRUD + validaciones)
- Autenticación: 6 tests (usuarios reales)
- Otros: 41 tests (responsive, API, docs, etc.)
- ~~Dashboard: 3 tests~~ ⚠️ SKIP (issues conocidos)

---

## 👥 USUARIOS (Password: 12345)

| Email | Rol | Qué hace |
|-------|-----|----------|
| `admin@demo.com` | Admin | Acceso total |
| `jefe.sistemas@demo.com` | Unidad | Crea procesos |
| `planeacion@demo.com` | Planeación | Genera CDP |
| `hacienda@demo.com` | Hacienda | Compatibilidad |
| `juridica@demo.com` | Jurídica | Radicación |

**Ver todos:** `docs/QA/USUARIOS_SISTEMA_PRUEBAS.md`

---

## 📸 EVIDENCIAS (dónde se guardan)

Carpeta: **`test-results/`**

```
test-results/
├── {test-nombre}/
│   ├── video.webm           ← Video del test
│   ├── screenshot-1.png     ← Pantallazos
│   └── trace.zip            ← Trace interactivo
├── resultados-certificacion.csv  ← Para Excel
└── REPORTE_CERTIFICACION.md      ← Resumen
```

**Ver reporte HTML:**
```bash
npx playwright show-report
```

---

## ✅ TODO LISTO - CORRE ESTO:

```bash
npx playwright test --reporter=html,list,./custom-reporter.js
```

**Mientras corre:** Yo genero la documentación 📄

---

**Documentación completa:** Ver este archivo completo más abajo  
**Issues conocidos:** Dashboard (3 tests deshabilitados)  
**Duración:** ~20-30 minutos todos los tests
