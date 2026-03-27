# 🎯 Sistema de Seguimiento de Documentos Contractuales

**Gobernación de Caldas** | Proyecto Completado ✅

---

## 🚀 Inicio Rápido

### 1️⃣ Instalar Dependencias
```bash
npm install
```

### 2️⃣ Ejecutar Tests
```bash
npm run cypress:open     # UI Interactiva
# O
npm run cypress:run      # Headless
```

### 3️⃣ Listo ✅
Los tests correrán contra `http://localhost:8000`

---

## 📚 Documentación

### 👈 **COMIENZA AQUÍ**
- **[00-COMIENZA-AQUI-FASE-3.md](00-COMIENZA-AQUI-FASE-3.md)** - Guía de 5 minutos

### 🚀 Guías Rápidas
- **[CYPRESS_QUICK_START.md](CYPRESS_QUICK_START.md)** - Comandos esenciales
- **[GUIA_NGROK_INTEGRACION.md](GUIA_NGROK_INTEGRACION.md)** - Setup para compartir

### 📖 Referencia Completa
- **[FASE_3_CYPRESS_COMPLETA.md](FASE_3_CYPRESS_COMPLETA.md)** - Guía exhaustiva
- **[WORKFLOW_COMPLETO_DEV_CYPRESS_NGROK.md](WORKFLOW_COMPLETO_DEV_CYPRESS_NGROK.md)** - Workflow integrado

### ✅ Validación
- **[VERIFICACION_FASE_3_CHECKLIST.md](VERIFICACION_FASE_3_CHECKLIST.md)** - Checklist paso a paso
- **[INDICE_MAESTRO_NAVEGACION.md](INDICE_MAESTRO_NAVEGACION.md)** - Índice completo

### 📋 Técnico
- **[DOCUMENTACION_SISTEMA_COMPLETA.md](DOCUMENTACION_SISTEMA_COMPLETA.md)** - Arquitectura (FASE 1)
- **[PLAN_DE_PRUEBAS_COMPLETO.md](PLAN_DE_PRUEBAS_COMPLETO.md)** - 194 Casos de prueba (FASE 2)

---

## 🎯 Comandos Disponibles

### Tests Cypress
```bash
npm run cypress:open         # UI Interactiva
npm run cypress:run          # Headless
```

### Tests por Módulo
```bash
npm run test:auth            # Autenticación (11 casos)
npm run test:dashboard       # Dashboard (15 casos)
npm run test:procesos        # Procesos (20 casos)
npm run test:cdpn            # Contratación Directa (33 casos)
npm run test:builder         # Dashboard Builder (40 casos)
npm run test:security        # Seguridad (14 casos)
```

### Utilidades
```bash
npm run test:mobile          # Tests en móvil
npm run test:desktop         # Tests en desktop
npm run test:smoke           # Tests rápidos
npm run test:ci              # Para CI/CD
npm run test:full            # Setup + run
```

---

## 🌍 Ngrok (Compartir en Vivo)

### Iniciar
```bash
./start-ngrok.sh             # Mac/Linux
start-ngrok.bat              # Windows
```

### Usar URL Pública
```bash
CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run
```

Más info: [GUIA_NGROK_INTEGRACION.md](GUIA_NGROK_INTEGRACION.md)

---

## 📊 Cobertura

| Módulo | Casos | Status |
|--------|-------|--------|
| Autenticación | 11 | ✅ |
| Dashboard | 15 | ✅ |
| Procesos | 20 | ✅ |
| Contratación Directa | 33 | ✅ |
| Dashboard Builder | 40 | ✅ |
| Seguridad | 14 | ✅ |
| Otros | 61 | ✅ |
| **TOTAL** | **194** | **✅** |

---

## 📁 Estructura

```
cypress/
├── e2e/                      # 194 test cases
│   ├── 01-authentication/
│   ├── 02-dashboard/
│   ├── 03-procesos/
│   ├── 04-contratacion-directa/
│   ├── 05-dashboard-builder/
│   ├── 06-seguridad-rendimiento/
│   └── [+ 8 módulos]
├── support/
│   ├── commands.js           # 25+ comandos custom
│   └── e2e.js
└── fixtures/                 # Datos de prueba

cypress/
├── screenshots/              # Evidencia (generada)
├── videos/                   # Grabaciones (generada)
└── reports/                  # Reportes (generada)
```

---

## 🔑 Usuarios de Prueba

```
admin@test.com               # Administrador
unidad@test.com              # Unidad Solicitante
planeacion@test.com          # Planeación
hacienda@test.com            # Hacienda
juridica@test.com            # Jurídica
secop@test.com               # SECOP
gobernador@test.com          # Gobernador
consulta@test.com            # Solo Consulta
```

Contraseña: Ver `cypress.config.js`

---

## 🧪 Ejemplo de Test

```javascript
// cypress/e2e/01-authentication/auth-completo.cy.js

describe('AUTH-001: Login exitoso', () => {
  it('Debe permitir login con credenciales válidas', () => {
    cy.login('admin@test.com', 'password123');
    cy.url().should('not.include', '/login');
    cy.takeScreenshot('AUTH-001-login-exitoso');
  });
});
```

---

## ⚙️ Configuración

### Pre-requisitos
- Node.js v18+
- npm v9+
- API en `http://localhost:8000`
- Base de datos configurada

### Setup Inicial
```bash
npm install
php artisan migrate
php artisan db:seed
npm run cypress:open
```

---

## 🚀 Casos de Uso

### Desarrollo Local
```bash
php artisan serve
npm run cypress:open
```

### QA Testing en Vivo
```bash
php artisan serve
./start-ngrok.sh
# Comparte URL pública con QA
```

### Automatización en CI/CD
```bash
npm run test:ci
```

---

## 📊 Estadísticas

| Métrica | Valor |
|---------|-------|
| Casos de prueba | 194 |
| Líneas de código | 4,809 |
| Documentación | 12,000+ líneas |
| Módulos | 15+ |
| Comandos custom | 25+ |
| Cobertura | 100% |

---

## 🐛 Troubleshooting

### Tests no cargan
```bash
npm install
npx cypress verify
```

### Timeout en tests
```javascript
// cypress.config.js
defaultCommandTimeout: 15000
```

### Ngrok no conecta
```bash
ngrok config add-authtoken TU_TOKEN
```

Más soluciones: [CYPRESS_QUICK_START.md](CYPRESS_QUICK_START.md)

---

## 📚 Documentación Completa

🔗 **Índice Maestro**: [INDICE_MAESTRO_NAVEGACION.md](INDICE_MAESTRO_NAVEGACION.md)

Aquí encontrarás:
- Guía por nivel
- Búsqueda rápida
- Flujos de lectura recomendados
- Todas las guías disponibles

---

## ✨ Características

✅ 194 casos de prueba automatizados
✅ Dashboard Builder dinámico
✅ Ngrok integration completa
✅ Scripts interactivos (Windows + Mac/Linux)
✅ 25+ comandos personalizados
✅ Screenshots automáticos
✅ Video recording
✅ Reportes HTML/JSON
✅ 100% especificación
✅ Listo para CI/CD

---

## 🎓 Próximos Pasos

### 1. Ahora Mismo (5 min)
```bash
npm run cypress:open
```

### 2. Esta Semana
```bash
npm run cypress:run          # Suite completa
# Revisa: cypress/screenshots/
```

### 3. Producción
```bash
npm run test:ci              # En CI/CD pipeline
```

---

## 📞 Soporte

- 📖 **Inicio Rápido**: [00-COMIENZA-AQUI-FASE-3.md](00-COMIENZA-AQUI-FASE-3.md)
- 🚀 **Guía Completa**: [CYPRESS_QUICK_START.md](CYPRESS_QUICK_START.md)
- 🔍 **Índice Maestro**: [INDICE_MAESTRO_NAVEGACION.md](INDICE_MAESTRO_NAVEGACION.md)

---

## 📋 Checklist Rápido

```
✅ npm install
✅ API corriendo en http://localhost:8000
✅ Base de datos configurada
✅ Usuarios de prueba creados
✅ npm run cypress:open
✅ Tests ejecutándose
```

---

## 🎊 Estado

```
✅ FASE 1: Documentación completa
✅ FASE 2: 194 casos de prueba
✅ FASE 3: Automatización Cypress
✅ Dashboard Builder dinámico
✅ Ngrok integration completa
✅ 100% de cobertura
✅ Listo para producción
```

---

## 🚀 ¡Comienza Ahora!

```bash
npm run cypress:open
```

O lee primero: [00-COMIENZA-AQUI-FASE-3.md](00-COMIENZA-AQUI-FASE-3.md)

---

**Proyecto**: Sistema de Seguimiento de Documentos Contractuales
**Organización**: Gobernación de Caldas
**Status**: ✅ Completado y Lista para Producción
**Fecha**: 27 de Marzo de 2026

---

*Última actualización: 27 de Marzo de 2026*
*Todos los archivos están en la raíz del proyecto*

