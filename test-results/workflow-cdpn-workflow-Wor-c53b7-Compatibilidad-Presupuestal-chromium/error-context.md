# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflow\cdpn-workflow.spec.js >> Workflow Contratación Directa (CD-PN) >> CDPN-003: Etapa 1 - CDP y Compatibilidad Presupuestal
- Location: tests\workflow\cdpn-workflow.spec.js:50:3

# Error details

```
TimeoutError: locator.click: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('tbody tr a').first()

```

# Page snapshot

```yaml
- generic [ref=e2]:
  - generic [ref=e5]:
    - generic [ref=e6]:
      - img "Escudo Gobernación de Caldas" [ref=e8]
      - generic [ref=e9]:
        - paragraph [ref=e10]: Gobernación de Caldas
        - paragraph [ref=e11]: Manizales, Colombia
    - generic [ref=e12]:
      - heading "Sistema de Seguimiento Contractual" [level=1] [ref=e13]:
        - text: Sistema de
        - text: Seguimiento
        - text: Contractual
      - paragraph [ref=e14]: Gestión integral del proceso de contratación.
    - paragraph [ref=e15]: © 2026 Gobernación de Caldas — Todos los derechos reservados
  - generic [ref=e17]:
    - generic [ref=e18]:
      - heading "Iniciar sesión" [level=2] [ref=e19]
      - paragraph [ref=e20]: Ingresa tus credenciales institucionales para continuar
    - generic [ref=e21]:
      - generic [ref=e22]:
        - generic [ref=e23]: Correo electrónico
        - textbox "Correo electrónico" [active] [ref=e24]:
          - /placeholder: usuario@gobernacion.gov.co
      - generic [ref=e25]:
        - generic [ref=e26]: Contraseña
        - textbox "Contraseña" [ref=e27]:
          - /placeholder: ••••••••
      - generic [ref=e28]:
        - checkbox "Mantener sesión iniciada" [ref=e29]
        - generic [ref=e30] [cursor=pointer]: Mantener sesión iniciada
      - button "Ingresar al sistema" [ref=e31] [cursor=pointer]
    - paragraph [ref=e32]: Gobernación de Caldas — Acceso restringido a usuarios autorizados
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
  18  |     await page.fill('input[name="nombre"]', `Proceso PW ${Date.now()}`);
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
> 55  |     await page.locator('tbody tr a').first().click();
      |                                              ^ TimeoutError: locator.click: Timeout 10000ms exceeded.
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
  119 |     const radicacionInput = page.locator('input[name="radicado"], input[name="numero_radicado"]');
  120 |     if (await radicacionInput.isVisible({ timeout: 3000 })) {
  121 |       await radicacionInput.fill(`RAD-${Date.now()}`);
  122 |       await page.click('button:has-text("Guardar")');
  123 |     }
  124 |   });
  125 | 
  126 |   test('CDPN-008: Etapa 6 - Publicación SECOP II', async ({ page }) => {
  127 |     const login = new LoginHelper(page);
  128 |     await login.loginAs('secop@test.com', 'Test1234!');
  129 |     
  130 |     await page.goto('/procesos');
  131 |     await page.locator('tbody tr a').first().click();
  132 |     
  133 |     const secopInput = page.locator('input[name="secop"], input:has-text("SECOP")');
  134 |     if (await secopInput.isVisible({ timeout: 3000 })) {
  135 |       await secopInput.fill('SECOP-' + Date.now());
  136 |       await page.click('button:has-text("Publicar"), button:has-text("Guardar")');
  137 |     }
  138 |   });
  139 | 
  140 |   test('CDPN-009: Etapa 7 - Solicitar RPC', async ({ page }) => {
  141 |     const login = new LoginHelper(page);
  142 |     await login.loginAsUnidad();
  143 |     
  144 |     await page.goto('/procesos');
  145 |     await page.locator('tbody tr a').first().click();
  146 |     
  147 |     const rpcButton = page.locator('button:has-text("RPC"), button:has-text("Registro")');
  148 |     if (await rpcButton.isVisible({ timeout: 3000 })) {
  149 |       await rpcButton.click();
  150 |       console.log('✅ Solicitud de RPC enviada');
  151 |     }
  152 |   });
  153 | 
  154 |   test('CDPN-010: Etapa 8 - Asignar Número de Contrato', async ({ page }) => {
  155 |     const login = new LoginHelper(page);
```