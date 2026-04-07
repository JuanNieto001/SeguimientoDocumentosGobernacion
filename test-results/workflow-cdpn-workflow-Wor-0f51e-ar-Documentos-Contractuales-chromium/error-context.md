# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflow\cdpn-workflow.spec.js >> Workflow Contratación Directa (CD-PN) >> CDPN-005: Etapa 3 - Elaborar Documentos Contractuales
- Location: tests\workflow\cdpn-workflow.spec.js:78:3

# Error details

```
TimeoutError: locator.click: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('tbody tr a').first()

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
          - generic [ref=e42]:
            - heading "Procesos" [level=1] [ref=e43]
            - paragraph [ref=e44]: Gobernación de Caldas — Sistema de Contratación Pública
          - link "Nueva solicitud" [ref=e46] [cursor=pointer]:
            - /url: http://localhost:8000/procesos/crear
            - img [ref=e47]
            - text: Nueva solicitud
        - generic [ref=e49]:
          - link [ref=e50] [cursor=pointer]:
            - /url: http://localhost:8000/alertas
            - img [ref=e51]
          - generic [ref=e53]:
            - generic [ref=e54]: JE
            - generic [ref=e55]: Jefe
      - main [ref=e56]:
        - generic [ref=e57]:
          - generic [ref=e59]:
            - generic [ref=e60]:
              - generic [ref=e61]: Buscar por código
              - textbox "Código del proceso..." [ref=e62]
            - button "Buscar" [ref=e64] [cursor=pointer]
          - generic [ref=e65]:
            - generic [ref=e66]: "Total de procesos:"
            - generic [ref=e67]: "0"
          - table [ref=e69]:
            - rowgroup [ref=e70]:
              - row "Código Objeto Documentos Área actual Creado por Fecha" [ref=e71]:
                - columnheader "Código" [ref=e72]
                - columnheader "Objeto" [ref=e73]
                - columnheader "Documentos" [ref=e74]
                - columnheader "Área actual" [ref=e75]
                - columnheader "Creado por" [ref=e76]
                - columnheader "Fecha" [ref=e77]
            - rowgroup [ref=e78]:
              - row "No se encontraron procesos." [ref=e79]:
                - cell "No se encontraron procesos." [ref=e80]:
                  - generic [ref=e81]:
                    - img [ref=e82]
                    - paragraph [ref=e84]: No se encontraron procesos.
  - button "Marsetiv bot" [ref=e86] [cursor=pointer]:
    - img [ref=e88]
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
> 83  |     await page.locator('tbody tr a').first().click();
      |                                              ^ TimeoutError: locator.click: Timeout 10000ms exceeded.
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
  156 |     await login.loginAsUnidad();
  157 |     
  158 |     await page.goto('/procesos');
  159 |     await page.locator('tbody tr a').first().click();
  160 |     
  161 |     const contratoInput = page.locator('input[name="numero_contrato"]');
  162 |     if (await contratoInput.isVisible({ timeout: 3000 })) {
  163 |       await contratoInput.fill(`CONT-${Date.now()}`);
  164 |       await page.click('button:has-text("Guardar")');
  165 |     }
  166 |   });
  167 | 
  168 |   test('CDPN-011: Etapa 9 - Inicio de Ejecución', async ({ page }) => {
  169 |     const login = new LoginHelper(page);
  170 |     await login.loginAsUnidad();
  171 |     
  172 |     await page.goto('/procesos');
  173 |     await page.locator('tbody tr a').first().click();
  174 |     
  175 |     const ejecucionButton = page.locator('button:has-text("Iniciar"), button:has-text("Ejecución")');
  176 |     if (await ejecucionButton.isVisible({ timeout: 3000 })) {
  177 |       await ejecucionButton.click();
  178 |       await expect(page.locator('body')).toContainText(/iniciado|ejecución/i);
  179 |     }
  180 |   });
  181 | 
  182 |   test('CDPN-012: Restricción - Solo Unidad puede crear', async ({ page }) => {
  183 |     const login = new LoginHelper(page);
```