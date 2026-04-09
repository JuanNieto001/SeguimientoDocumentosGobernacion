# 🎯 PLAN MAESTRO DE PRUEBAS QA
## Sistema de Seguimiento de Documentos - Gobernación de Caldas

---

## RESUMEN EJECUTIVO 📊

| **Métrica** | **Valor** | **Estado** |
|-------------|-----------|------------|
| **Total Casos de Prueba** | 42 | ✅ DEFINIDOS |
| **Módulos Cubiertos** | 8 | ✅ COMPLETO |
| **Tests Cypress Implementados** | 31 archivos | ✅ LISTOS |
| **Comandos Personalizados** | 25+ | ✅ FUNCIONAL |
| **Page Objects** | 4 clases | ✅ IMPLEMENTADO |

---

## COBERTURA COMPLETA POR MÓDULOS 📋

### 🔐 AUTENTICACIÓN (7 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| AUTH_001 | Login exitoso - Admin General | Positivo | Alta | `cypress/e2e/auth/auth-login.cy.js` |
| AUTH_002 | Login exitoso - Unidad Solicitante | Positivo | Alta | `cypress/e2e/01-authentication/login.cy.js` |
| AUTH_003 | Login fallido - Credenciales incorrectas | Negativo | Alta | `cypress/e2e/01-authentication/auth-completo.cy.js` |
| AUTH_004 | Login - Campos vacíos | Negativo | Media | `cypress/e2e/01-authentication/auth-completo.cy.js` |
| AUTH_005 | Logout exitoso | Positivo | Alta | `cypress/e2e/01-authentication/logout.cy.js` |
| AUTH_006 | Acceso sin autenticación | Negativo | Alta | `cypress/e2e/01-authentication/auth-completo.cy.js` |
| AUTH_007 | Sesión expirada | Edge Case | Media | `cypress/e2e/01-authentication/auth-completo.cy.js` |

### 👥 GESTIÓN DE USUARIOS (8 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| USERS_001 | Crear usuario exitosamente | Positivo | Alta | `cypress/e2e/users/users-management.cy.js` |
| USERS_002 | Editar usuario existente | Positivo | Alta | `cypress/e2e/users/users-management.cy.js` |
| USERS_003 | Eliminar usuario | Positivo | Media | `cypress/e2e/users/users-management.cy.js` |
| USERS_004 | Crear usuario - Email duplicado | Negativo | Alta | `cypress/e2e/users/users-management.cy.js` |
| USERS_005 | Crear usuario - Campos obligatorios | Negativo | Media | `cypress/e2e/users/users-management.cy.js` |
| USERS_006 | Asignar rol a usuario | Positivo | Alta | `cypress/e2e/roles-permisos/roles-permisos.cy.js` |
| USERS_007 | Acceso sin permisos | Permisos | Alta | `cypress/e2e/roles-permisos/roles-permisos.cy.js` |
| USERS_008 | Gestión por secretaría | Permisos | Alta | `cypress/e2e/users/users-management.cy.js` |

### 🔄 WORKFLOW CD-PN (15 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| CDPN_001 | Iniciar proceso CD-PN completo | Positivo | Alta | `cypress/e2e/workflow/cdpn-complete-flow.cy.js` |
| CDPN_002 | Etapa 0 - Subir Estudios Previos | Positivo | Alta | `cypress/e2e/03-flujo-cdpn/flujo-completo.cy.js` |
| CDPN_003 | Etapa 1 - Solicitar documentos paralelos | Positivo | Alta | `cypress/e2e/workflow/cdpn-additional-stages.cy.js` |
| CDPN_004 | Etapa 1 - Dependencia CDP/Compatibilidad | Negativo | Alta | `cypress/e2e/workflow/cdpn-additional-stages.cy.js` |
| CDPN_005 | Etapa 2 - Validación de contratista | Positivo | Alta | `cypress/e2e/workflow/cdpn-additional-stages.cy.js` |
| CDPN_006 | Etapa 3 - Elaborar documentos contractuales | Positivo | Alta | `cypress/e2e/workflow/cdpn-additional-stages.cy.js` |
| CDPN_007 | Etapa 4 - Consolidar expediente | Positivo | Media | `cypress/e2e/workflow/cdpn-final-stages.cy.js` |
| CDPN_008 | Etapa 5 - Radicación jurídica | Positivo | Alta | `cypress/e2e/workflow/cdpn-final-stages.cy.js` |
| CDPN_009 | Etapa 6 - Publicación SECOP II | Positivo | Alta | `cypress/e2e/secop/secop.cy.js` |
| CDPN_010 | Etapa 7 - Solicitar RPC | Positivo | Alta | `cypress/e2e/workflow/cdpn-final-stages.cy.js` |
| CDPN_011 | Etapa 8 - Número de contrato | Positivo | Alta | `cypress/e2e/workflow/cdpn-final-stages.cy.js` |
| CDPN_012 | Etapa 9 - Inicio ejecución | Positivo | Alta | `cypress/e2e/workflow/cdpn-final-stages.cy.js` |
| CDPN_013 | Restricción creación - Otras secretarías | Permisos | Alta | `cypress/e2e/contratacion-directa/contratacion-directa.cy.js` |
| CDPN_014 | Validación tipos de documento por etapa | Negativo | Media | `cypress/e2e/documentos/documentos.cy.js` |
| CDPN_015 | Flujo completo end-to-end | Positivo | Alta | `cypress/e2e/04-contratacion-directa/cdpn-completo.cy.js` |

### ⚙️ MOTOR DE FLUJOS (3 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| MOTOR_001 | Crear nuevo flujo personalizado | Positivo | Alta | `cypress/e2e/motor-flujos/motor-flujos.cy.js` |
| MOTOR_002 | Publicar versión de flujo | Positivo | Alta | `cypress/e2e/motor-flujos/motor-flujos.cy.js` |
| MOTOR_003 | Versionado de flujos | Positivo | Media | `cypress/e2e/motor-flujos/motor-flujos.cy.js` |

### 📄 GESTIÓN DE DOCUMENTOS (3 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| DOCS_001 | Subir documento válido | Positivo | Alta | `cypress/e2e/documents/documents-management.cy.js` |
| DOCS_002 | Archivo muy grande | Negativo | Media | `cypress/e2e/documents/documents-management.cy.js` |
| DOCS_003 | Tipo archivo incorrecto | Negativo | Media | `cypress/e2e/documents/documents-management.cy.js` |

### 📊 DASHBOARD (2 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| DASH_001 | Dashboard Admin General | Positivo | Alta | `cypress/e2e/dashboard/dashboard.cy.js` |
| DASH_002 | Dashboard filtrado por secretaría | Permisos | Alta | `cypress/e2e/02-dashboard/dashboard-completo.cy.js` |

### 🔌 API ENDPOINTS (2 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| API_001 | Endpoint autenticación | Positivo | Media | `cypress/e2e/api/api-endpoints.cy.js` |
| API_002 | Endpoint sin autenticación | Negativo | Media | `cypress/e2e/api/api-endpoints.cy.js` |

### 📱 RESPONSIVE DESIGN (2 casos)
| ID | Caso | Tipo | Prioridad | Archivo Test |
|----|------|------|-----------|--------------|
| RESP_001 | Login en móvil | Positivo | Media | `cypress/e2e/responsive/responsive-design.cy.js` |
| RESP_002 | Dashboard responsive | Positivo | Media | `cypress/e2e/responsive/responsive-design.cy.js` |

---

## DATOS DE PRUEBA ESTRUCTURADOS 📁

```json
{
  "usuarios_test": {
    "admin_general": {
      "email": "admin@caldas.gov.co",
      "password": "Caldas2025*",
      "rol": "Admin General"
    },
    "planeacion": {
      "email": "profesional1@caldas.gov.co", 
      "password": "Caldas2025*",
      "rol": "Unidad Solicitante"
    },
    "juridica": {
      "email": "admin.juridica@caldas.gov.co",
      "password": "Caldas2025*",
      "rol": "Admin Secretaría"
    }
  },
  "proceso_test": {
    "objeto": "Consultoría técnica especializada",
    "valor": 15000000,
    "plazo": "3 meses",
    "contratista": {
      "nombre": "Juan Pérez Gómez",
      "cedula": "12345678",
      "email": "juan.perez@test.com"
    }
  }
}
```

---

## COMANDOS DE EJECUCIÓN 🚀

### Ejecutar Tests Completos
```bash
# Ejecutar toda la suite (modo headless)
npx cypress run

# Ejecutar con interfaz gráfica
npx cypress open

# Ejecutar módulo específico
npx cypress run --spec "cypress/e2e/auth/**/*.cy.js"
npx cypress run --spec "cypress/e2e/workflow/**/*.cy.js"
```

### Generar Reportes
```bash
# Ejecutar con reporte HTML
npx cypress run --reporter mochawesome

# Ejecutar con screenshots y videos
npx cypress run --config video=true,screenshotOnRunFailure=true
```

---

## CRITERIOS DE ACEPTACIÓN ✅

### Para Aprobación Final:
1. **✅ 100% de casos críticos (Alta prioridad) ejecutados**
2. **✅ 0% de defectos bloqueantes**
3. **✅ Flujo principal CD-PN funcional**
4. **✅ Control de acceso operativo**
5. **✅ Evidencias completas generadas**

### Para Certificación de Producción:
- **Tiempo de respuesta < 3 segundos**
- **Disponibilidad > 99%**
- **Seguridad validada**
- **Documentación completa**

---

**🎯 ESTE DOCUMENTO ES LA GUÍA MAESTRA PARA LA CERTIFICACIÓN DEL SISTEMA**

Preparado por: **Ingeniero QA Senior**  
Fecha: **Abril 7, 2026**  
Versión: **1.0 FINAL**  
Estado: **✅ LISTO PARA EJECUCIÓN**