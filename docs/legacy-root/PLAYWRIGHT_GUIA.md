# 🎭 PLAYWRIGHT - PRUEBAS AUTOMATIZADAS

## ✅ YA ESTÁ LISTO PARA USAR

### 🚀 Comandos Rápidos

```bash
# Ver interfaz interactiva (recomendado)
npm test

# Ejecutar todas las pruebas
npm run test:run

# Ejecutar con navegador visible
npm run test:headed

# Debug paso a paso
npm run test:debug

# Ver reporte HTML
npm run test:report
```

### 📂 Pruebas por Módulo

```bash
# Solo autenticación
npm run test:auth

# Solo dashboard
npm run test:dashboard

# Solo workflow CD-PN
npm run test:workflow
```

### 🌐 Navegadores Específicos

```bash
# Chrome
npm run test:chromium

# Firefox
npm run test:firefox

# Móvil (Pixel 5)
npm run test:mobile
```

### 🔥 BONUS: Evidencias Automáticas

**Playwright guarda AUTOMÁTICAMENTE:**
- 📸 **Screenshots** en cada paso
- 🎥 **Videos** de toda la prueba
- 📊 **Traces** con timeline completo
- 📄 **Reportes HTML** hermosos

**Ubicación de evidencias:**
- `test-results/` - Screenshots y videos
- `playwright-report/` - Reporte HTML interactivo

### 🎯 Pruebas Incluidas

✅ **Autenticación (6 tests)**
- Login exitoso/fallido
- Validaciones de campos
- Logout
- Acceso sin permisos

✅ **Dashboard (3 tests)**
- Carga correcta
- Responsive móvil
- Filtros

✅ **Workflow CD-PN (4 tests)**
- Crear proceso
- Subir documentos
- Navegación de etapas
- Validación de permisos

### 🔧 Configuración

Todo está en `playwright.config.js`:
- Screenshots: **ON** (siempre)
- Videos: **ON** (siempre)
- Traces: **ON** (completo)
- Reintentos: **2** (automático)

### 💡 Tips

1. **Interfaz UI** es la mejor forma de desarrollar tests
2. **Traces** te muestran TODO lo que pasó (mejor que video)
3. **Codegen** genera tests automáticamente: `npx playwright codegen http://localhost:8000`

---

## 🆚 Diferencias vs Cypress

| Feature | Cypress | Playwright |
|---------|---------|------------|
| Screenshots | 💰 Pago | ✅ GRATIS |
| Videos | 💰 Pago | ✅ GRATIS |
| Traces | ❌ No | ✅ GRATIS |
| Reportes | 💰 Pago | ✅ GRATIS |
| Multi-browser | 💰 Pago | ✅ GRATIS |

**Playwright = TODO GRATIS** 🎉
