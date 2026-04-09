# 🎯 GUÍA RÁPIDA - EJECUTAR TESTS CON UI

## ✅ ABRIR UI INTERACTIVA (RECOMENDADO PARA VER TODO)

```bash
npm test
```

## 📋 QUÉ VERÁS:

```
┌─────────────────────────────────────────────────┐
│  Playwright Test Runner                        │
├─────────────────────────────────────────────────┤
│                                                 │
│  📁 tests/                                      │
│    ├─ ☑ e2e/                                   │
│    │   ├─ ☑ flujo-completo-cdpn.spec.js (5)   │
│    │   └─ ☑ crear-datos-demo.spec.js (4)      │
│    ├─ ☑ workflow/                              │
│    │   ├─ ☑ workflow-cdpn-completo (35)       │
│    │   └─ ☑ cdpn-workflow (15)                │
│    ├─ ☑ procesos/                              │
│    │   ├─ ☑ procesos-completo (20)            │
│    │   └─ ☑ procesos (4)                       │
│    ├─ ☑ auth/ (6)                              │
│    ├─ ☑ users/ (4)                             │
│    └─ ... más tests                            │
│                                                 │
│  [▶ Run all]  [⏸ Pause]  [🔍 Watch]           │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 🎬 CÓMO USAR LA UI:

### 1️⃣ **Seleccionar tests:**
- ☑ **Marca checkboxes** de los tests que quieres correr
- O haz clic en **"Run all"** para correr todos

### 2️⃣ **Correr tests seleccionados:**
- Clic en **botón verde ▶**
- Los tests empiezan a correr

### 3️⃣ **Ver progreso EN VIVO:**
- ✅ Verde = Pasó
- ❌ Rojo = Falló
- ⏳ Amarillo = Corriendo
- ⚪ Gris = Pendiente

### 4️⃣ **Ver detalles de cada test:**
- Haz clic en cualquier test
- Verás:
  - Screenshots capturados
  - Video del test
  - Trace interactivo
  - Logs de consola
  - Errores (si hay)

---

## 🔥 TESTS PRIORITARIOS (selecciona estos primero):

### ⭐ Para crear datos de demostración:

1. **E2E Flujo Completo** (`tests/e2e/flujo-completo-cdpn.spec.js`)
   - ☑ Marca este archivo
   - Corre solo estos 5 tests
   - **GUARDA proceso en BD**

2. **Datos DEMO** (`tests/e2e/crear-datos-demo.spec.js`)
   - ☑ Marca este archivo
   - Corre estos 4 tests
   - **GUARDA procesos para presentación**

---

## 💡 CÓMO SABER QUE TERMINÓ:

### En la UI verás:

```
✅ 5 passed (30s)
   E2E-001: Crear proceso ✓
   E2E-002: Cargar estudios ✓
   E2E-003: Compatibilidad ✓
   E2E-004: CDP ✓
   E2E-005: Verificación ✓
```

### Si algo falla:

```
❌ 1 failed, 4 passed (25s)
   E2E-003: Compatibilidad ✗ (click para ver error)
```

**Haces clic** en el test fallido y ves:
- Screenshot del error
- Video hasta donde falló
- Mensaje de error exacto

---

## 🎯 PLAN PARA TI:

### OPCIÓN 1: Ver TODO corriendo (Demo completo)
```bash
npm test
```
1. Click en "Run all"
2. Espera ~30 minutos
3. Ves todo en vivo

### OPCIÓN 2: Solo tests importantes (Rápido - 10 min)
```bash
npm test
```
1. Desmarca todo
2. Marca SOLO:
   - ☑ `e2e/flujo-completo-cdpn.spec.js`
   - ☑ `e2e/crear-datos-demo.spec.js`
3. Click "Run"
4. Espera 10 minutos
5. **DATOS QUEDAN EN BD**

---

## 📸 EVIDENCIAS SE GUARDAN IGUAL:

Aunque uses la UI, las evidencias se guardan en:
- `test-results/` (screenshots, videos, traces)
- `test-results/resultados-certificacion.csv`
- `test-results/REPORTE_CERTIFICACION.md`

---

## ⚡ COMANDO SIMPLE:

```bash
npm test
```

**ESO ES TODO** - La UI se abre y ya puedes:
- Seleccionar tests
- Correrlos
- Ver resultados
- Revisar evidencias

---

## 🆚 DIFERENCIAS:

| Comando | Qué hace | Cuándo usar |
|---------|----------|-------------|
| `npm test` | Abre UI visual | **Para VER todo** ✅ |
| `npx playwright test ...` | Corre en terminal | Para CI/CD o scripts |

---

**USA:** `npm test` **AHORA** 🚀

La UI es MUCHO mejor para ver qué está pasando y revisar resultados.
