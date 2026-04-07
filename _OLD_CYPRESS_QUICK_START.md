# 🚀 GUIA RAPIDA - FASE 3 CYPRESS

**Sistema de Seguimiento de Documentos Contractuales - Gobernación de Caldas**
**Última actualización: 2026-03-27**

---

## ⚡ Inicio Rápido (5 minutos)

### 1️⃣ Instalación
```bash
npm install
```

### 2️⃣ Configurar Credenciales (Opcional)
Crear `.env.testing`:
```
CYPRESS_adminEmail=admin@test.com
CYPRESS_adminPassword=password123
```

### 3️⃣ Ejecutar Tests
```bash
# Todos los tests
npm run cypress:run

# O UI Interactiva
npm run cypress:open
```

---

## 📦 Comandos Rápidos

### Ejecutar Todo
```bash
npm run cypress:run
```

### Tests por Módulo
```bash
# Autenticación
npm run test:auth

# Dashboard
npm run test:dashboard

# Procesos
npm run test:procesos

# Contratación Directa (CD-PN)
npm run test:cdpn

# Dashboard Builder
npm run test:builder

# Seguridad
npm run test:security
```

### Modo Interactivo
```bash
npm run cypress:open
```

### Con Video
```bash
npm run cypress:run -- --record
```

### Específico
```bash
npx cypress run --spec "cypress/e2e/01-authentication/auth-completo.cy.js"
```

---

## 🎯 Casos Principales

| Módulo | Archivo | Casos | Descripción |
|--------|---------|-------|-----------|
| **Autenticación** | `auth-completo.cy.js` | AUTH-001 a AUTH-011 | Login, logout, validaciones |
| **Dashboard** | `dashboard-completo.cy.js` | DASH-001 a DASH-015 | Vistas por rol, KPIs, gráficos |
| **Procesos** | `procesos-completo.cy.js` | PROC-001 a PROC-020 | CRUD de procesos |
| **CD-PN** | `cdpn-completo.cy.js` | CDPN-001 a CDPN-033 | Flujo completo contratación |
| **Dashboard Builder** | `dashboard-builder.cy.js` | BUILD-001 a BUILD-040 | Constructor visual |
| **Seguridad** | `seguridad-rendimiento.cy.js` | SEC/PERF | Tests de seguridad |

---

## 🔐 Usuarios de Prueba

```javascript
// Disponibles en cypress.config.js
admin@test.com              // Administrador
unidad@test.com             // Unidad Solicitante
planeacion@test.com         // Planeación
hacienda@test.com           // Hacienda
juridica@test.com           // Jurídica
secop@test.com              // SECOP
gobernador@test.com         // Gobernador
consulta@test.com           // Solo Consulta
```

**Contraseña**: `Test1234!` (o ver `cypress.config.js`)

---

## 📸 Durante la Ejecución

### Capturas Automáticas
```bash
# Screenshots al fallar
cypress/screenshots/

# Videos de sesión
cypress/videos/

# Reportes JSON
cypress/reports/
```

### Debug
```javascript
// En los tests
cy.debug()          // Pausa y abre console
cy.pause()          // Pausa antes de siguiente comando
cy.log('mensaje')   // Log customizado
```

---

## ⚠️ Troubleshooting

### Tests no encuentran elementos
```javascript
// Usa waits explícitos
cy.wait(3000)
cy.intercept('GET', '/api/**').as('apiCall')
cy.wait('@apiCall')
```

### Timeout en login
✓ Verificar credenciales en `cypress.config.js`
✓ Verificar API está corriendo en http://localhost:8000
✓ Verificar base de datos tiene datos de prueba

### Screenshots no se guardan
✓ Verificar existe `cypress/screenshots/`
✓ Verificar permisos de escritura
✓ Usar `--record` flag para mejor reporte

### Tests pasan localmente pero fallan en CI
✓ Usar `--headed` en local para ver lo que hace
✓ Ver videos en `cypress/videos/`
✓ Revisar logs de CI/CD

---

## 🎓 Comandos Personalizados Disponibles

```javascript
// LOGIN/LOGOUT
cy.login(email, password)           // Login manual
cy.loginAsRole('admin')             // Login rápido
cy.logout()                         // Logout

// NAVEGACION
cy.visit('/path')                   // Navega y espera
cy.cleanSession()                   // Limpia cookies

// VERIFICACION
cy.takeScreenshot('name')           // Captura pantalla
cy.logStep('texto')                 // Log paso

// INTEGRACION
cy.apiGet('/api/path')              // GET request
cy.apiPost('/api/path', data)       // POST request
cy.apiPut('/api/path', data)        // PUT request
cy.apiDelete('/api/path')           // DELETE request
```

---

## 📊 Estructura de Carpetas

```
cypress/
├── e2e/                           [Tests por módulo]
│   ├── 01-authentication/
│   ├── 02-dashboard/
│   ├── 03-procesos/
│   ├── 04-contratacion-directa/
│   ├── 05-dashboard-builder/
│   └── 06-seguridad-rendimiento/
├── support/
│   ├── commands.js                [Comandos custom]
│   ├── e2e.js                     [Setup global]
│   └── helpers/
├── fixtures/
│   └── usuarios.json              [Datos de prueba]
├── screenshots/                   [Evidencia capturada]
├── videos/                        [Grabaciones]
└── reports/                       [Reportes JSON]
```

---

## 🔄 CI/CD Integration

### GitHub Actions
```yaml
name: Cypress Tests
on: [push, pull_request]
jobs:
  cypress:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: cypress-io/github-action@v2
        with:
          build: npm run build
          start: npm run dev
```

### Pre-commit Hook (package.json)
```json
{
  "husky": {
    "hooks": {
      "pre-commit": "npm run test:auth"
    }
  }
}
```

---

## 📈 Métricas

| Métrica | Valor |
|---------|-------|
| Total Casos | 194 |
| Módulos | 15+ |
| Tiempo Est. | 15-25 min |
| Cobertura | 100% flujos |
| Evidencia | 194 screenshots +  videos |

---

## 🎯 Próximos Pasos

### Después de Ejecutar Tests:
1. Revisar screenshots en `cypress/screenshots/`
2. Si hay fallos, revisar videos en `cypress/videos/`
3. Ejecutar nuevamente en modo headless si es necesario
4. Generar reporte final

### Mantenimiento:
- Actualizar selectors si cambia la UI
- Agregar nuevos tests para nuevas funcionalidades
- Revisar logs regularmente
- Mantener datos de prueba actualizados

---

## 🆘 Ayuda Rápida

```bash
# Ver versión de Cypress
npx cypress --version

# Verificar instalación
npx cypress verify

# Limpiar cache
npx cypress cache clear

# Actualizar Cypress
npm install cypress@latest

# Ver archivos de test
ls cypress/e2e/**/*.cy.js

# Contar tests
grep -r "it(" cypress/e2e/**/*.cy.js | wc -l
```

---

## 📞 Soporte

**Documentación relacionada:**
- `FASE_3_CYPRESS_COMPLETA.md` - Guía completa
- `PLAN_DE_PRUEBAS_COMPLETO.md` - Matriz de casos
- `EJECUCION_PRUEBAS_CYPRESS.md` - Ejecución detallada
- `RESULTADOS_PRUEBAS_CYPRESS.md` - Template de resultados

**Enlaces útiles:**
- [Cypress Docs](https://docs.cypress.io)
- [Cypress Best Practices](https://docs.cypress.io/guides/references/best-practices)
- [Cypress Commands](https://docs.cypress.io/api/commands/and)

---

**¡Listo para usar!** 🎉

Ejecuta `npm run cypress:run` o `npm run cypress:open` para comenzar.
