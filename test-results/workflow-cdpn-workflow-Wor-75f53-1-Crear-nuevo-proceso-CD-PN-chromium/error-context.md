# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflow\cdpn-workflow.spec.js >> Workflow Contratación Directa (CD-PN) >> CDPN-001: Crear nuevo proceso CD-PN
- Location: tests\workflow\cdpn-workflow.spec.js:12:3

# Error details

```
TimeoutError: page.fill: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('input[name="nombre"]')

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
  - button "Marsetiv bot" [ref=e132] [cursor=pointer]:
    - img [ref=e134]
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | import { LoginHelper } from '../helpers/login.helper.js';
  3   | 
  4   | /**
  5   |  * PRUEBAS DE WORKFLOW CD-PN - COMPLETO
  6   |  * Casos: CDPN-001 a CDPN-015
  7   |  * Flujo de 9 etapas completo
  8   |  */
  9   | 
  10  | test.describe('Workflow Contratación Directa (CD-PN)', () => {
  11  |   
  12  |   test('CDPN-001: Crear nuevo proceso CD-PN', async ({ page }) => {
  13  |     const login = new LoginHelper(page);
  14  |     await login.loginAsUnidad();
  15  |     
  16  |     await page.goto('/procesos/crear');
  17  |     
> 18  |     await page.fill('input[name="nombre"]', `Proceso PW ${Date.now()}`);
      |                ^ TimeoutError: page.fill: Timeout 10000ms exceeded.
  19  |     await page.fill('textarea[name="descripcion"]', 'Proceso de prueba Playwright');
  20  |     
  21  |     const tipoSelect = page.locator('select[name="tipo"]');
  22  |     if (await tipoSelect.isVisible()) {
  23  |       await tipoSelect.selectOption('CD-PN');
  24  |     }
  25  |     
  26  |     await page.click('button[type="submit"]');
  27  |     await expect(page.locator('body')).toContainText(/creado|exitoso/i);
  28  |   });
  29  | 
  30  |   test('CDPN-002: Etapa 0 - Subir Estudios Previos', async ({ page }) => {
  31  |     const login = new LoginHelper(page);
  32  |     await login.loginAsUnidad();
  33  |     
  34  |     await page.goto('/procesos');
  35  |     await page.locator('tbody tr a').first().click();
  36  |     
  37  |     const fileInput = page.locator('input[type="file"]');
  38  |     if (await fileInput.isVisible({ timeout: 5000 })) {
  39  |       await fileInput.setInputFiles({
  40  |         name: 'estudios-previos.pdf',
  41  |         mimeType: 'application/pdf',
  42  |         buffer: Buffer.from('Estudios Previos PDF'),
  43  |       });
  44  |       
  45  |       await page.click('button:has-text("Subir")');
  46  |       await expect(page.locator('body')).toContainText(/subido|exitoso/i);
  47  |     }
  48  |   });
  49  | 
  50  |   test('CDPN-003: Etapa 1 - CDP y Compatibilidad Presupuestal', async ({ page }) => {
  51  |     const login = new LoginHelper(page);
  52  |     await login.loginAs('planeacion@test.com', 'Test1234!');
  53  |     
  54  |     await page.goto('/procesos');
  55  |     await page.locator('tbody tr a').first().click();
  56  |     
  57  |     console.log('✅ Verificar etapa 1 visible');
  58  |     const etapa1 = page.locator(':text("CDP"), :text("Disponibilidad")');
  59  |     if (await etapa1.first().isVisible({ timeout: 3000 })) {
  60  |       console.log('✅ Etapa 1 encontrada');
  61  |     }
  62  |   });
  63  | 
  64  |   test('CDPN-004: Etapa 2 - Validación Contratista', async ({ page }) => {
  65  |     const login = new LoginHelper(page);
  66  |     await login.loginAsUnidad();
  67  |     
  68  |     await page.goto('/procesos');
  69  |     await page.locator('tbody tr a').first().click();
  70  |     
  71  |     const validacionInput = page.locator('input[name="nombre_contratista"], textarea[name="contratista"]');
  72  |     if (await validacionInput.first().isVisible({ timeout: 3000 })) {
  73  |       await validacionInput.first().fill('Contratista Prueba S.A.');
  74  |       await page.click('button:has-text("Guardar"), button[type="submit"]');
  75  |     }
  76  |   });
  77  | 
  78  |   test('CDPN-005: Etapa 3 - Elaborar Documentos Contractuales', async ({ page }) => {
  79  |     const login = new LoginHelper(page);
  80  |     await login.loginAsUnidad();
  81  |     
  82  |     await page.goto('/procesos');
  83  |     await page.locator('tbody tr a').first().click();
  84  |     
  85  |     console.log('✅ Etapa 3: Documentos contractuales');
  86  |     const fileInput = page.locator('input[type="file"]');
  87  |     if (await fileInput.isVisible({ timeout: 3000 })) {
  88  |       await fileInput.setInputFiles({
  89  |         name: 'minuta-contrato.pdf',
  90  |         mimeType: 'application/pdf',
  91  |         buffer: Buffer.from('Minuta de Contrato'),
  92  |       });
  93  |       
  94  |       await page.click('button:has-text("Subir")');
  95  |     }
  96  |   });
  97  | 
  98  |   test('CDPN-006: Etapa 4 - Consolidar Expediente', async ({ page }) => {
  99  |     const login = new LoginHelper(page);
  100 |     await login.loginAsUnidad();
  101 |     
  102 |     await page.goto('/procesos');
  103 |     await page.locator('tbody tr a').first().click();
  104 |     
  105 |     console.log('✅ Verificar expediente consolidado');
  106 |     const expediente = page.locator(':text("Expediente"), :text("Consolidar")');
  107 |     if (await expediente.first().isVisible({ timeout: 3000 })) {
  108 |       console.log('✅ Sección de expediente visible');
  109 |     }
  110 |   });
  111 | 
  112 |   test('CDPN-007: Etapa 5 - Radicación Jurídica', async ({ page }) => {
  113 |     const login = new LoginHelper(page);
  114 |     await login.loginAs('juridica@test.com', 'Test1234!');
  115 |     
  116 |     await page.goto('/procesos');
  117 |     await page.locator('tbody tr a').first().click();
  118 |     
```