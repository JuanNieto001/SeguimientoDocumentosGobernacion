# PLAN DE PREPARACIÓN DE TESTS - FASE 4
## Sistema de Seguimiento Contractual - Gobernación de Caldas

**Versión:** 1.0
**Fecha:** Marzo 2026
**Arquitecto:** Senior Software Architect
**Fase:** 4 - Preparación de Tests

---

## 🎯 OBJETIVOS DE LA FASE 4

1. **✅ Configurar Cypress** para testing end-to-end
2. **✅ Diseñar casos de prueba** para flujos críticos
3. **✅ Preparar datos de prueba** (fixtures y seeders)
4. **✅ Crear tests de autenticación** y sistema de roles
5. **✅ Validar dashboards dinámicos** por rol
6. **✅ Documentar estrategia** de testing completa

---

## 📊 ESTRATEGIA DE TESTING

### **🔧 HERRAMIENTAS Y TECNOLOGÍAS**

```yaml
Testing Framework:
  - Cypress: E2E Testing principal
  - Laravel Dusk: Backup para tests complejos
  - PHPUnit: Unit testing backend
  - Jest: Testing componentes React

Entornos:
  - Local: Desarrollo y debugging
  - CI/CD: Automatización en GitHub Actions
  - Staging: Validación pre-producción

Datos:
  - Fixtures: Datos estáticos para tests
  - Seeders: Datos dinámicos por escenario
  - Factory: Generación automática de datos
```

### **📋 CATEGORÍAS DE TESTING**

#### **🔐 Tests Críticos (Prioridad 1)**
1. **Autenticación y Autorización**
   - Login con credenciales válidas
   - Manejo de credenciales inválidas
   - Verificación de roles y permisos
   - Logout y sesiones

2. **Flujo CD-PN Completo**
   - Creación de proceso desde etapa 0
   - Transición entre todas las 10 etapas
   - Validación de documentos por etapa
   - Finalización exitosa del proceso

3. **Dashboard por Roles**
   - Vista ejecutiva (Gobernador)
   - Vista secretarial (Secretarios)
   - Vista de gestión (Jefes de Unidad)
   - Widgets dinámicos y filtros

#### **⚙️ Tests Funcionales (Prioridad 2)**
1. **Gestión de Usuarios**
   - CRUD de usuarios
   - Asignación de roles
   - Gestión de permisos

2. **Gestión de Procesos**
   - Crear procesos
   - Editar procesos
   - Eliminar procesos
   - Filtros y búsquedas

3. **Sistema de Documentos**
   - Subida de archivos
   - Validación de formatos
   - Descarga de documentos

#### **🎨 Tests de UI/UX (Prioridad 3)**
1. **Responsive Design**
   - Desktop (1920px, 1366px)
   - Tablet (768px)
   - Mobile (375px, 414px)

2. **Interactividad**
   - Drag & Drop dashboard
   - Filtros dinámicos
   - Navegación fluida

---

## 🧪 CASOS DE PRUEBA DETALLADOS

### **TEST SUITE 1: AUTENTICACIÓN**

```typescript
describe('Sistema de Autenticación', () => {
  it('LOGIN_001: Login exitoso con administrador', () => {
    // Given: Usuario administrador válido
    // When: Ingresa credenciales correctas
    // Then: Redirige a dashboard ejecutivo
  });

  it('LOGIN_002: Login fallido con credenciales incorrectas', () => {
    // Given: Credenciales inválidas
    // When: Intenta hacer login
    // Then: Muestra mensaje de error
  });

  it('LOGIN_003: Verificación de roles después del login', () => {
    // Given: Usuario con rol secretario
    // When: Hace login exitoso
    // Then: Ve dashboard secretarial correspondiente
  });

  it('AUTH_004: Logout correcto', () => {
    // Given: Usuario autenticado
    // When: Hace logout
    // Then: Redirige a página de login
  });
});
```

### **TEST SUITE 2: FLUJO CD-PN COMPLETO**

```typescript
describe('Flujo Contratación Directa Persona Natural', () => {
  beforeEach(() => {
    // Setup: Usuario con permisos de contratación
    cy.loginAs('coord_contratacion');
  });

  it('CDPN_001: Crear proceso en etapa 0 - Identificación de Necesidad', () => {
    // Given: Usuario coordinador autenticado
    // When: Crea nuevo proceso CD-PN
    // Then: Proceso inicia en etapa 0 correctamente
  });

  it('CDPN_002: Transición etapa 0 → 1 - Estudios Previos', () => {
    // Given: Proceso en etapa 0 con documentos cargados
    // When: Envía a siguiente etapa
    // Then: Proceso avanza a etapa 1
  });

  it('CDPN_003: Validación de documentos requeridos por etapa', () => {
    // Given: Proceso en etapa específica
    // When: Intenta avanzar sin documentos requeridos
    // Then: Sistema impide avance y muestra errores
  });

  it('CDPN_004: Flujo completo 10 etapas', () => {
    // Given: Proceso recién creado
    // When: Ejecuta flujo completo con todos los documentos
    // Then: Proceso finaliza exitosamente en estado completado
  });

  it('CDPN_005: Validaciones automáticas por etapa', () => {
    // Given: Proceso con datos de prueba específicos
    // When: Ejecuta validaciones automáticas
    // Then: Sistema valida según reglas configuradas
  });
});
```

### **TEST SUITE 3: DASHBOARDS POR ROL**

```typescript
describe('Dashboards Dinámicos por Rol', () => {
  it('DASH_001: Dashboard Ejecutivo - Gobernador', () => {
    // Given: Usuario con rol gobernador
    // When: Accede al dashboard
    // Then: Ve vista ejecutiva con widgets apropiados
  });

  it('DASH_002: Dashboard Secretarial - Secretario', () => {
    // Given: Usuario con rol secretario
    // When: Accede al dashboard
    // Then: Ve datos filtrados por su secretaría
  });

  it('DASH_003: Dashboard Gestión - Jefe Unidad', () => {
    // Given: Usuario jefe de unidad
    // When: Accede al dashboard
    // Then: Ve carga de trabajo de su equipo
  });

  it('DASH_004: Filtros dinámicos funcionando', () => {
    // Given: Dashboard con filtros disponibles
    // When: Aplica filtros de fecha y unidad
    // Then: Datos se actualizan dinámicamente
  });

  it('DASH_005: Drag & Drop widgets', () => {
    // Given: Dashboard personalizable
    // When: Arrastra widget a nueva posición
    // Then: Posición se guarda correctamente
  });
});
```

---

## 🗂️ DATOS DE PRUEBA Y FIXTURES

### **👥 USUARIOS DE TESTING**

```javascript
// cypress/fixtures/users.json
{
  "admin": {
    "email": "admin.test@gobernacion-caldas.gov.co",
    "password": "TestingPassword123!",
    "role": "super_admin"
  },
  "gobernador": {
    "email": "gobernador.test@gobernacion-caldas.gov.co",
    "password": "TestingPassword123!",
    "role": "gobernador"
  },
  "secretario_planeacion": {
    "email": "secretario.planeacion.test@gobernacion-caldas.gov.co",
    "password": "TestingPassword123!",
    "role": "secretario",
    "secretaria_id": 1
  },
  "coord_contratacion": {
    "email": "coord.contratacion.test@gobernacion-caldas.gov.co",
    "password": "TestingPassword123!",
    "role": "coord_contratacion",
    "unidad_id": 5
  }
}
```

### **📄 PROCESOS DE TESTING**

```javascript
// cypress/fixtures/procesos.json
{
  "cd_pn_basico": {
    "codigo": "TEST-CD-PN-001",
    "objeto": "Contratación servicios profesionales testing",
    "descripcion": "Proceso de prueba para validar flujo CD-PN",
    "valor_estimado": 25000000,
    "plazo_ejecucion": "3 meses",
    "contratista_nombre": "Juan Pérez Testing",
    "contratista_documento": "12345678"
  },
  "cd_pn_complejo": {
    "codigo": "TEST-CD-PN-002",
    "objeto": "Consultoría especializada testing avanzado",
    "descripcion": "Proceso complejo para validar todas las etapas",
    "valor_estimado": 45000000,
    "plazo_ejecucion": "6 meses",
    "contratista_nombre": "María García Testing",
    "contratista_documento": "87654321"
  }
}
```

### **📋 DOCUMENTOS DE TESTING**

```javascript
// cypress/fixtures/documentos.json
{
  "estudios_previos": {
    "filename": "estudios_previos_test.pdf",
    "tipo": "application/pdf",
    "size": 1048576
  },
  "matriz_riesgos": {
    "filename": "matriz_riesgos_test.xlsx",
    "tipo": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "size": 524288
  },
  "cedula_contratista": {
    "filename": "cedula_test.pdf",
    "tipo": "application/pdf",
    "size": 262144
  }
}
```

---

## 🚀 CONFIGURACIÓN CYPRESS

### **📦 Instalación y Setup**

```json
// package.json - Dependencies
{
  "devDependencies": {
    "cypress": "^13.7.0",
    "@cypress/webpack-preprocessor": "^6.0.1",
    "cypress-file-upload": "^5.0.8",
    "cypress-drag-drop": "^1.1.1",
    "cypress-real-events": "^1.11.0"
  }
}
```

### **⚙️ Configuración Principal**

```javascript
// cypress.config.js
import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8000',
    viewportWidth: 1366,
    viewportHeight: 768,
    video: false,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 10000,
    requestTimeout: 15000,
    responseTimeout: 15000,

    env: {
      admin_email: 'admin.test@gobernacion-caldas.gov.co',
      admin_password: 'TestingPassword123!',
      api_base_url: 'http://localhost:8000/api'
    },

    setupNodeEvents(on, config) {
      // Plugins setup
      on('file:preprocessor', require('@cypress/webpack-preprocessor')({}));
      return config;
    },

    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js'
  }
})
```

### **🛠️ Comandos Personalizados**

```javascript
// cypress/support/commands.js
Cypress.Commands.add('loginAs', (userType) => {
  cy.fixture('users').then((users) => {
    const user = users[userType];
    cy.visit('/login');
    cy.get('[data-cy="email-input"]').type(user.email);
    cy.get('[data-cy="password-input"]').type(user.password);
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('not.include', '/login');
  });
});

Cypress.Commands.add('createTestProcess', (processType) => {
  cy.fixture('procesos').then((procesos) => {
    const proceso = procesos[processType];
    cy.visit('/procesos/crear');
    cy.get('[data-cy="objeto-input"]').type(proceso.objeto);
    cy.get('[data-cy="descripcion-textarea"]').type(proceso.descripcion);
    cy.get('[data-cy="valor-input"]').type(proceso.valor_estimado.toString());
    cy.get('[data-cy="plazo-input"]').type(proceso.plazo_ejecucion);
    cy.get('[data-cy="crear-proceso-btn"]').click();
  });
});

Cypress.Commands.add('uploadTestDocument', (documentType) => {
  cy.fixture('documentos').then((docs) => {
    const doc = docs[documentType];
    cy.get('[data-cy="file-upload"]').selectFile({
      contents: Cypress.Buffer.alloc(doc.size),
      fileName: doc.filename,
      mimeType: doc.tipo
    });
  });
});
```

---

## 📊 ESTRATEGIA DE EJECUCIÓN

### **🔄 Orden de Ejecución**

```yaml
Secuencia de Tests:
  1. Setup inicial y limpieza
  2. Tests de autenticación
  3. Tests de roles y permisos
  4. Tests de dashboard por rol
  5. Tests de flujo CD-PN completo
  6. Tests de documentos y archivos
  7. Tests de responsive design
  8. Cleanup final

Tiempo estimado total: 45-60 minutos
```

### **📈 Métricas de Éxito**

```yaml
Criterios de Aceptación:
  - Coverage mínimo: 85% funcionalidades críticas
  - Tiempo de ejecución: <60 minutos
  - Flakiness: <5% tests intermitentes
  - Performance: <30 segundos por página crítica

Reportes Generados:
  - HTML Report: Resultado completo
  - Screenshot: Evidencias de fallos
  - Mochawesome: Reportes bonitos
  - Allure: Reportes avanzados
```

---

## 🛡️ TESTING EN CI/CD

### **🔄 GitHub Actions Integration**

```yaml
# .github/workflows/cypress-tests.yml
name: Cypress Tests

on: [push, pull_request]

jobs:
  cypress-run:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: testing_db
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install Dependencies
        run: |
          composer install --no-dev --optimize-autoloader
          npm ci

      - name: Prepare Application
        run: |
          php artisan key:generate
          php artisan migrate:fresh
          php artisan db:seed --class=TestingSeederStructure

      - name: Start Application
        run: php artisan serve &

      - name: Run Cypress Tests
        uses: cypress-io/github-action@v6
        with:
          wait-on: 'http://localhost:8000'
          wait-on-timeout: 120
          browser: chrome
          headless: true
```

---

## 📁 ESTRUCTURA DE ARCHIVOS

```
cypress/
├── e2e/
│   ├── 01-authentication/
│   │   ├── login.cy.js
│   │   ├── logout.cy.js
│   │   └── roles.cy.js
│   ├── 02-dashboard/
│   │   ├── dashboard-ejecutivo.cy.js
│   │   ├── dashboard-secretarial.cy.js
│   │   ├── dashboard-gestion.cy.js
│   │   └── widgets-draggable.cy.js
│   ├── 03-flujo-cdpn/
│   │   ├── flujo-completo.cy.js
│   │   ├── validaciones-etapas.cy.js
│   │   └── documentos-requeridos.cy.js
│   ├── 04-responsive/
│   │   ├── mobile-view.cy.js
│   │   ├── tablet-view.cy.js
│   │   └── desktop-view.cy.js
│   └── 05-integration/
│       ├── api-integration.cy.js
│       └── end-to-end-complete.cy.js
├── fixtures/
│   ├── users.json
│   ├── procesos.json
│   ├── documentos.json
│   └── dashboard-configs.json
├── support/
│   ├── commands.js
│   ├── e2e.js
│   └── helpers/
│       ├── auth-helpers.js
│       ├── process-helpers.js
│       └── dashboard-helpers.js
└── downloads/
    └── (archivos generados por tests)
```

---

## ⚡ COMANDOS ARTISAN PARA TESTING

```php
// Comando para preparar entorno de testing
php artisan test:prepare

// Comando para ejecutar seeders de testing
php artisan db:seed --class=TestingSeederStructure

// Comando para limpiar datos de testing
php artisan test:cleanup

// Comando para generar datos de prueba
php artisan test:generate-data

// Comando para verificar preparación de tests
php artisan test:verify-readiness
```

---

## 🎯 DELIVERABLES FASE 4

### **📋 Entregables Principales:**

1. **✅ Configuración Cypress** completa y funcional
2. **✅ Suite de tests críticos** (autenticación, CD-PN, dashboards)
3. **✅ Fixtures y datos de prueba** organizados
4. **✅ Comandos personalizados** de Cypress
5. **✅ Documentación de testing** completa
6. **✅ Integración CI/CD** preparada

### **📊 Métricas de Completitud:**

- **Tests críticos:** 15+ casos de prueba
- **Tests funcionales:** 25+ casos de prueba
- **Tests UI/UX:** 10+ casos de prueba
- **Coverage objetivo:** 85% funcionalidades
- **Tiempo ejecución:** <60 minutos

---

**🎯 OBJETIVO FASE 4:**
Tener sistema de testing completamente preparado y configurado, listo para ejecución en FASE 5.

---

*Documento generado automáticamente*
*Arquitecto de Software Senior - Marzo 2026*