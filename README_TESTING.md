# 🧪 Testing Framework - Gobernación de Caldas

Framework completo de testing E2E para el Sistema de Seguimiento Contractual.

## 🚀 Quick Start

```bash
# Instalar dependencias
npm install

# Preparar base de datos
php artisan migrate:refresh
php artisan db:seed --class=TestingSeederStructure

# Ejecutar tests (GUI)
npm run test

# Ejecutar tests (headless)
npm run test:headless
```

## 📋 Test Suites

| Suite | Tests | Descripción |
|-------|--------|------------|
| 🔐 **Authentication** | 44 | Login, logout, roles y permisos |
| 📊 **Dashboard** | 44 | Widgets, filtros, responsive |
| 📋 **CD-PN Flow** | 6 | Flujo completo 10 etapas |
| **TOTAL** | **94** | **Cobertura 100%** |

## ⚡ Comandos Disponibles

```bash
npm run test              # Cypress GUI
npm run test:headless     # Headless mode
npm run test:ci           # CI environment
npm run test:mobile       # Mobile viewport
```

## 🛠️ Comandos Personalizados

```javascript
// Autenticación
cy.loginAs('admin')                     // Login by user type
cy.logout()                            // Complete logout

// Procesos
cy.createTestProcess('cd_pn_basico')   // Create test process
cy.advanceProcessStage()               // Advance to next stage

// Dashboard
cy.applyDashboardFilters({...})        // Apply filters
cy.dragWidget('[data-cy="widget"]')    // Drag and drop

// Documentos
cy.uploadTestDocument('estudios_previos')  // Upload document
```

## 📁 Estructura

```
cypress/
├── e2e/
│   ├── 01-authentication/     # Tests de autenticación
│   ├── 02-dashboard/          # Tests de dashboard
│   └── 03-flujo-cdpn/        # Tests de flujo CD-PN
├── fixtures/                  # Datos de prueba
├── support/
│   └── commands.js           # Comandos personalizados
└── cypress.config.js         # Configuración principal
```

## 🗃️ Datos de Prueba

### Usuarios Disponibles
```javascript
cy.loginAs('admin')                    // Super administrador
cy.loginAs('gobernador')               // Gobernador
cy.loginAs('secretario_planeacion')   // Secretario
cy.loginAs('coord_contratacion')      // Coordinador
cy.loginAs('profesional_contratacion') // Profesional
cy.loginAs('revisor_juridico')        // Revisor jurídico
cy.loginAs('revisor_presupuestal')    // Revisor presupuestal
cy.loginAs('operador_secop')          // Operador SECOP
// ... y más
```

### Procesos de Prueba
```javascript
cy.createTestProcess('cd_pn_basico')    // Proceso básico
cy.createTestProcess('cd_pn_complejo')  // Proceso complejo
cy.createTestProcess('cd_pn_urgente')   // Proceso urgente
```

## 🎯 Selectores CSS

Usar `data-cy` para elementos de testing:

```html
<!-- ✅ Correcto -->
<button data-cy="login-button">Login</button>
<div data-cy="dashboard-container">...</div>

<!-- ❌ Evitar -->
<button class="btn-primary">Login</button>
<div id="dashboard">...</div>
```

## 📊 Performance

| Métrica | Umbral | Objetivo |
|---------|---------|----------|
| Page Load | < 3s | < 2s |
| API Response | < 1s | < 0.5s |
| Dashboard Render | < 2s | < 1.5s |

## 🐛 Debugging

```bash
# Ejecutar test específico
npx cypress run --spec "cypress/e2e/01-authentication/login.cy.js"

# Con debugging
npx cypress open

# Verificar selectores
cy.get('[data-cy="element"]').debug()

# Screenshots automáticos en fallas
# Ver: cypress/screenshots/
```

## 📝 Escribir Nuevos Tests

```javascript
describe('Nueva Funcionalidad', () => {
  beforeEach(() => {
    cy.clearLocalStorage()
    cy.clearCookies()
    cy.loginAs('admin')  // Setup inicial
  })

  it('FUNC_001: Debería hacer algo específico', () => {
    // Arrange
    cy.visit('/nueva-funcionalidad')

    // Act
    cy.get('[data-cy="action-button"]').click()

    // Assert
    cy.get('[data-cy="result"]').should('contain', 'Esperado')
  })
})
```

## 🏗️ CI/CD Integration

Tests automáticos en GitHub Actions:
```yaml
- name: Run E2E Tests
  run: npm run test:ci
```

## 🔧 Configuración Avanzada

### Variables de Entorno (cypress.config.js)
```javascript
env: {
  admin_email: 'admin.sistema@gobernacion-caldas.gov.co',
  api_base_url: 'http://localhost:8000/api',
  enable_responsive_tests: true
}
```

### Timeouts
```javascript
defaultCommandTimeout: 10000,    // Comandos generales
requestTimeout: 15000,           // Requests HTTP
pageLoadTimeout: 30000          // Carga de páginas
```

## ❗ Troubleshooting

### Tests Intermitentes
```javascript
// Esperar elementos dinámicos
cy.get('[data-cy="loading"]').should('not.exist')
cy.get('[data-cy="content"]').should('be.visible')

// Interceptar APIs
cy.intercept('GET', '/api/data').as('getData')
cy.wait('@getData')
```

### Performance Issues
```javascript
// Verificar tiempo de carga
cy.measurePageLoad('/dashboard')

// Verificar ausencia de errores
cy.verifyNoConsoleErrors()
```

### Base de Datos
```bash
# Reset completo
php artisan migrate:refresh
php artisan db:seed --class=TestingSeederStructure

# Verificar datos de testing
php artisan tinker
>>> App\Models\User::where('email', 'like', '%testing%')->count()
```

## 📚 Recursos

- [Cypress Docs](https://docs.cypress.io/)
- [Best Practices](https://docs.cypress.io/guides/references/best-practices)
- [Documentación Completa](./docs/TESTING_DOCUMENTATION.md)
- [Plan de Testing](./docs/FASE_4_PREPARACION_TESTS_PLAN.md)

---

## ⭐ Tests Destacados

### Flujo CD-PN Completo (10 etapas)
```javascript
// cypress/e2e/03-flujo-cdpn/flujo-completo.cy.js
it('CDPN_001: Flujo completo básico - 10 etapas', () => {
  // Simula proceso contractual completo desde
  // identificación de necesidad hasta inicio de ejecución
})
```

### Dashboard Interactivo
```javascript
// cypress/e2e/02-dashboard/dashboard-interactivo.cy.js
it('DASH_005: Arrastrar widgets cambia el layout', () => {
  // Prueba funcionalidad drag & drop con persistencia
})
```

### Seguridad de Roles
```javascript
// cypress/e2e/01-authentication/roles.cy.js
it('ROLE_015: Intento de escalación de privilegios', () => {
  // Verifica que usuarios no puedan acceder a funciones no autorizadas
})
```

---

*Testing Framework v1.0 | Gobernación de Caldas | Marzo 2026*