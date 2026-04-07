# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e\flujo-completo-cdpn.spec.js >> E2E-CDPN: Flujo Completo Contratación Directa >> E2E-001: Crear proceso CD-PN completo
- Location: tests\e2e\flujo-completo-cdpn.spec.js:19:3

# Error details

```
TimeoutError: page.fill: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('input[name="objeto_contrato"]')

```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - generic [ref=e2]:
    - complementary [ref=e3]:
      - generic [ref=e4]:
        - img "Escudo" [ref=e6]
        - generic [ref=e7]:
          - paragraph [ref=e8]: Gobernación
          - paragraph [ref=e9]: Caldas
      - generic [ref=e10]:
        - generic [ref=e11]: JE
        - generic [ref=e12]:
          - paragraph [ref=e13]: Jefe Unidad Sistemas
          - paragraph [ref=e14]: jefe.sistemas@demo.com
      - navigation [ref=e15]:
        - link "Panel principal" [ref=e16] [cursor=pointer]:
          - /url: http://localhost:8000/panel-principal
          - img [ref=e17]
          - text: Panel principal
        - paragraph [ref=e19]: Mi Área
        - link "Mi bandeja" [ref=e20] [cursor=pointer]:
          - /url: http://localhost:8000/unidad
          - img [ref=e21]
          - text: Mi bandeja
        - link "Nueva solicitud" [ref=e23] [cursor=pointer]:
          - /url: http://localhost:8000/procesos/crear
          - img [ref=e24]
          - text: Nueva solicitud
        - link "Consulta SECOP II" [ref=e26] [cursor=pointer]:
          - /url: http://localhost:8000/secop-consulta
          - img [ref=e27]
          - text: Consulta SECOP II
        - link "Notificaciones" [ref=e29] [cursor=pointer]:
          - /url: http://localhost:8000/alertas
          - img [ref=e30]
          - text: Notificaciones
      - button "Cerrar sesión" [ref=e34] [cursor=pointer]:
        - img [ref=e35]
        - text: Cerrar sesión
    - generic [ref=e37]:
      - banner [ref=e38]:
        - generic [ref=e41]:
          - link [ref=e42] [cursor=pointer]:
            - /url: http://localhost:8000/procesos
            - img [ref=e43]
          - generic [ref=e45]:
            - heading "Nueva solicitud" [level=1] [ref=e46]
            - paragraph [ref=e47]: Registrar un nuevo proceso de contratación
        - generic [ref=e48]:
          - link [ref=e49] [cursor=pointer]:
            - /url: http://localhost:8000/alertas
            - img [ref=e50]
          - generic [ref=e52]:
            - generic [ref=e53]: JE
            - generic [ref=e54]: Jefe
      - main [ref=e55]:
        - generic [ref=e58]:
          - generic [ref=e59]:
            - generic [ref=e60]:
              - generic [ref=e61]: "1"
              - heading "Identificación del proceso" [level=2] [ref=e62]
            - generic [ref=e63]:
              - generic [ref=e64]:
                - generic [ref=e65]: Flujo de contratación *
                - generic [ref=e66]:
                  - img [ref=e67]
                  - generic [ref=e69]:
                    - text: "Flujo:"
                    - strong [ref=e70]: Contratación Directa - Persona Natural (CD_PN)
              - generic [ref=e71]:
                - generic [ref=e72]: Objeto del contrato *
                - 'textbox "Ej: Prestación de servicios profesionales para apoyar la gestión de..." [ref=e73]'
              - generic [ref=e74]:
                - generic [ref=e75]: Descripción (opcional)
                - textbox "Detalle adicional del proceso..." [ref=e76]
          - generic [ref=e77]:
            - generic [ref=e78]:
              - generic [ref=e79]: "2"
              - heading "Dependencia solicitante" [level=2] [ref=e80]
              - generic [ref=e81]:
                - img [ref=e82]
                - text: Asignada a tu perfil
            - generic [ref=e84]:
              - generic [ref=e85]:
                - generic [ref=e86]: Secretaría
                - generic [ref=e87]:
                  - img [ref=e88]
                  - text: Secretaría de Planeación
              - generic [ref=e90]:
                - generic [ref=e91]: Unidad
                - generic [ref=e92]:
                  - img [ref=e93]
                  - text: Unidad de Sistemas
          - generic [ref=e95]:
            - generic [ref=e96]:
              - generic [ref=e97]: "3"
              - heading "Datos económicos" [level=2] [ref=e98]
            - generic [ref=e99]:
              - generic [ref=e100]:
                - generic [ref=e101]: Valor estimado (COP) (opcional)
                - generic [ref=e102]:
                  - generic [ref=e103]: $
                  - spinbutton [ref=e104]
              - generic [ref=e105]:
                - generic [ref=e106]: Plazo de ejecución (meses) *
                - spinbutton [ref=e107]
                - paragraph [ref=e108]: Ingrese solo el número de meses (1-60)
          - generic [ref=e109]:
            - generic [ref=e110]:
              - generic [ref=e111]: "4"
              - heading "Estudios Previos *" [level=2] [ref=e112]
            - generic [ref=e114]:
              - generic [ref=e115]: Cargar documento de Estudios Previos *
              - button "Choose File" [ref=e116] [cursor=pointer]
              - paragraph [ref=e117]: "Formatos permitidos: PDF, DOC, DOCX. Este archivo es obligatorio para crear la solicitud."
          - button "5 Datos del contratista (opcional — se puede completar después)" [ref=e119] [cursor=pointer]:
            - generic [ref=e120]:
              - generic [ref=e121]: "5"
              - generic [ref=e122]: Datos del contratista
              - generic [ref=e123]: (opcional — se puede completar después)
            - img [ref=e124]
          - generic [ref=e126]:
            - button "Crear proceso" [ref=e127] [cursor=pointer]:
              - img [ref=e128]
              - text: Crear proceso
            - link "Cancelar" [ref=e130] [cursor=pointer]:
              - /url: http://localhost:8000/procesos
  - generic [ref=e131]:
    - generic [ref=e132]:
      - generic [ref=e133]:
        - generic [ref=e134]:
          - img [ref=e136]
          - generic [ref=e149]:
            - paragraph [ref=e150]: Marsetiv bot
            - paragraph [ref=e151]: En línea · Listo para ayudarte
        - button [ref=e153] [cursor=pointer]:
          - img [ref=e154]
      - generic [ref=e157]:
        - img [ref=e159]
        - generic [ref=e164]:
          - paragraph [ref=e165]:
            - text: ¡Hola,
            - strong [ref=e166]: Jefe
            - text: "! Soy"
            - strong [ref=e167]: Marsetiv bot
            - text: ", tu asistente. ¿En qué te ayudo hoy?"
          - paragraph [ref=e168]:
            - img [ref=e169]
            - text: "Tu rol:"
            - strong [ref=e171]: Unidad Solicitante
      - generic [ref=e173]:
        - paragraph [ref=e174]: Guías disponibles
        - button "🆕 Cómo crear una solicitud" [ref=e175] [cursor=pointer]:
          - generic [ref=e176]: 🆕
          - generic [ref=e177]: Cómo crear una solicitud
          - img [ref=e178]
        - button "📎 Cargar documentos de mi etapa" [ref=e180] [cursor=pointer]:
          - generic [ref=e181]: 📎
          - generic [ref=e182]: Cargar documentos de mi etapa
          - img [ref=e183]
        - button "🔄 Ver el estado de mis procesos" [ref=e185] [cursor=pointer]:
          - generic [ref=e186]: 🔄
          - generic [ref=e187]: Ver el estado de mis procesos
          - img [ref=e188]
        - button "✅ Validar información del contratista" [ref=e190] [cursor=pointer]:
          - generic [ref=e191]: ✅
          - generic [ref=e192]: Validar información del contratista
          - img [ref=e193]
        - button "📋 Ver mis tareas pendientes" [ref=e195] [cursor=pointer]:
          - generic [ref=e196]: 📋
          - generic [ref=e197]: Ver mis tareas pendientes
          - img [ref=e198]
        - button "🔔 Revisar notificaciones" [ref=e200] [cursor=pointer]:
          - generic [ref=e201]: 🔔
          - generic [ref=e202]: Revisar notificaciones
          - img [ref=e203]
        - button "🔒 Restablecer mi contraseña" [ref=e205] [cursor=pointer]:
          - generic [ref=e206]: 🔒
          - generic [ref=e207]: Restablecer mi contraseña
          - img [ref=e208]
        - button "👁️ Previsualizar documentos" [ref=e210] [cursor=pointer]:
          - generic [ref=e211]: 👁️
          - generic [ref=e212]: Previsualizar documentos
          - img [ref=e213]
        - button "🔄 Reemplazar un documento" [ref=e215] [cursor=pointer]:
          - generic [ref=e216]: 🔄
          - generic [ref=e217]: Reemplazar un documento
          - img [ref=e218]
        - button "📌 Ver versiones de un documento" [ref=e220] [cursor=pointer]:
          - generic [ref=e221]: 📌
          - generic [ref=e222]: Ver versiones de un documento
          - img [ref=e223]
        - button "📧 Cómo funcionan las alertas por correo" [ref=e225] [cursor=pointer]:
          - generic [ref=e226]: 📧
          - generic [ref=e227]: Cómo funcionan las alertas por correo
          - img [ref=e228]
        - button "¿Necesitas más ayuda? Escríbenos" [ref=e231] [cursor=pointer]:
          - img [ref=e233]
          - generic [ref=e235]: ¿Necesitas más ayuda? Escríbenos
          - img [ref=e236]
      - generic [ref=e238]:
        - img [ref=e239]
        - text: Marsetiv bot · Asistente de Gobernación de Caldas
    - button "Marsetiv bot" [ref=e241] [cursor=pointer]:
      - img [ref=e243]
```

# Test source

```ts
  1   | // tests/e2e/flujo-completo-cdpn.spec.js
  2   | // ═══════════════════════════════════════════════════════════════════
  3   | // FLUJO E2E COMPLETO - CONTRATACIÓN DIRECTA PERSONA NATURAL
  4   | // ═══════════════════════════════════════════════════════════════════
  5   | // Este test crea un proceso REAL desde cero y lo avanza por TODAS las etapas
  6   | // Los datos quedan en BD para demostración mañana
  7   | 
  8   | import { test, expect } from '@playwright/test';
  9   | import { LoginHelper } from '../helpers/login.helper.js';
  10  | 
  11  | let procesoId = null;
  12  | let numeroProcesoCreado = null;
  13  | 
  14  | test.describe('E2E-CDPN: Flujo Completo Contratación Directa', () => {
  15  | 
  16  |   // ═══════════════════════════════════════════════════════════════
  17  |   // ETAPA 0: CREAR PROCESO - DEFINICIÓN DE NECESIDAD
  18  |   // ═══════════════════════════════════════════════════════════════
  19  |   test('E2E-001: Crear proceso CD-PN completo', async ({ page }) => {
  20  |     const login = new LoginHelper(page);
  21  |     
  22  |     try {
  23  |       await login.loginAsUnidad(); // jefe.sistemas@demo.com
  24  |       await page.waitForTimeout(2000);
  25  | 
  26  |       // Ir a crear proceso
  27  |       await page.goto('/procesos/crear');
  28  |       await page.waitForLoadState('domcontentloaded');
  29  |       
  30  |       const timestamp = Date.now();
  31  |       const objetoContrato = `Prestación servicios profesionales desarrollo software - TEST E2E ${timestamp}`;
  32  |       
  33  |       // Llenar formulario
> 34  |       await page.fill('input[name="objeto_contrato"]', objetoContrato);
      |                  ^ TimeoutError: page.fill: Timeout 10000ms exceeded.
  35  |       await page.fill('textarea[name="descripcion"]', 'Desarrollo sistema seguimiento documentos para modernización procesos contractuales');
  36  |       await page.selectOption('select[name="tipo_proceso"]', 'CD-PN');
  37  |       await page.fill('input[name="valor_estimado"]', '15000000');
  38  |       await page.fill('input[name="plazo_dias"]', '60');
  39  |       
  40  |       // Guardar y capturar ID
  41  |       await page.click('button[type="submit"]:has-text("Crear"), button:has-text("Guardar")');
  42  |       await page.waitForTimeout(3000);
  43  |       
  44  |       // Capturar número de proceso
  45  |       const urlActual = page.url();
  46  |       const matchId = urlActual.match(/procesos\/(\d+)/);
  47  |       if (matchId) {
  48  |         procesoId = matchId[1];
  49  |         console.log(`✅ Proceso creado con ID: ${procesoId}`);
  50  |       }
  51  |       
  52  |       // Buscar número en pantalla
  53  |       const numeroProceso = await page.locator('text=/CD-PN-\\d{4}-\\d+/').first().textContent().catch(() => null);
  54  |       if (numeroProceso) {
  55  |         numeroProcesoCreado = numeroProceso;
  56  |         console.log(`✅ Número proceso: ${numeroProcesoCreado}`);
  57  |       }
  58  |       
  59  |       expect(procesoId).toBeTruthy();
  60  |       
  61  |     } catch (error) {
  62  |       console.error('❌ ERROR en E2E-001:', error.message);
  63  |       await page.screenshot({ path: 'test-results/error-e2e-001.png', fullPage: true });
  64  |       throw error;
  65  |     }
  66  |   });
  67  | 
  68  |   // ═══════════════════════════════════════════════════════════════
  69  |   // ETAPA 0: CARGAR ESTUDIOS PREVIOS
  70  |   // ═══════════════════════════════════════════════════════════════
  71  |   test('E2E-002: Cargar estudios previos (Etapa 0)', async ({ page }) => {
  72  |     test.skip(!procesoId, 'Depende de E2E-001');
  73  |     
  74  |     const login = new LoginHelper(page);
  75  |     
  76  |     try {
  77  |       await login.loginAsUnidad();
  78  |       await page.goto(`/procesos/${procesoId}`);
  79  |       await page.waitForTimeout(2000);
  80  |       
  81  |       // Buscar sección de documentos Etapa 0
  82  |       const etapa0 = page.locator('text=/Etapa 0|Estudios Previos/i').first();
  83  |       if (await etapa0.isVisible({ timeout: 5000 })) {
  84  |         await etapa0.click();
  85  |         await page.waitForTimeout(1000);
  86  |       }
  87  |       
  88  |       // Subir archivo simulado
  89  |       const fileInput = page.locator('input[type="file"]').first();
  90  |       if (await fileInput.isVisible({ timeout: 3000 })) {
  91  |         await fileInput.setInputFiles({
  92  |           name: 'estudios_previos.pdf',
  93  |           mimeType: 'application/pdf',
  94  |           buffer: Buffer.from('Estudios previos - Documento de prueba E2E')
  95  |         });
  96  |         
  97  |         await page.click('button:has-text("Subir"), button:has-text("Cargar")');
  98  |         await page.waitForTimeout(2000);
  99  |         
  100 |         console.log('✅ Estudios previos cargados');
  101 |       }
  102 |       
  103 |       // Avanzar a Etapa 1
  104 |       const btnAvanzar = page.locator('button:has-text("Avanzar"), button:has-text("Siguiente")').first();
  105 |       if (await btnAvanzar.isVisible({ timeout: 5000 })) {
  106 |         await btnAvanzar.click();
  107 |         await page.waitForTimeout(2000);
  108 |         console.log('✅ Avanzado a Etapa 1');
  109 |       }
  110 |       
  111 |     } catch (error) {
  112 |       console.error('❌ ERROR en E2E-002:', error.message);
  113 |       await page.screenshot({ path: 'test-results/error-e2e-002.png', fullPage: true });
  114 |       // NO throw - continuar con siguientes tests
  115 |     }
  116 |   });
  117 | 
  118 |   // ═══════════════════════════════════════════════════════════════
  119 |   // ETAPA 1: SOLICITAR CDP Y COMPATIBILIDAD
  120 |   // ═══════════════════════════════════════════════════════════════
  121 |   test('E2E-003: Solicitar Compatibilidad (Etapa 1 - Hacienda)', async ({ page }) => {
  122 |     test.skip(!procesoId, 'Depende de E2E-001');
  123 |     
  124 |     const login = new LoginHelper(page);
  125 |     
  126 |     try {
  127 |       await login.loginAsHacienda(); // hacienda@demo.com
  128 |       await page.goto(`/procesos/${procesoId}`);
  129 |       await page.waitForTimeout(2000);
  130 |       
  131 |       // Buscar solicitud de compatibilidad
  132 |       const compatibilidad = page.locator('text=/Compatibilidad/i').first();
  133 |       if (await compatibilidad.isVisible({ timeout: 5000 })) {
  134 |         await compatibilidad.click();
```