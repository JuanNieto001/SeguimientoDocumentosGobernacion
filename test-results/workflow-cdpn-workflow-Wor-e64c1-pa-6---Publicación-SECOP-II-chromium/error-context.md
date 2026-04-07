# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflow\cdpn-workflow.spec.js >> Workflow Contratación Directa (CD-PN) >> CDPN-008: Etapa 6 - Publicación SECOP II
- Location: tests\workflow\cdpn-workflow.spec.js:126:3

# Error details

```
Error: page.goto: Target page, context or browser has been closed
```

# Test source

```ts
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
> 130 |     await page.goto('/procesos');
      |                ^ Error: page.goto: Target page, context or browser has been closed
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
  184 |     await login.loginAs('consulta@test.com', 'Test1234!');
  185 |     
  186 |     await page.goto('/procesos/crear');
  187 |     
  188 |     const cannotAccess = await page.url().includes('/dashboard') || 
  189 |                          await page.locator('body').textContent().then(t => t.includes('permiso'));
  190 |     expect(cannotAccess).toBeTruthy();
  191 |   });
  192 | 
  193 |   test('CDPN-013: Validación de documentos requeridos', async ({ page }) => {
  194 |     const login = new LoginHelper(page);
  195 |     await login.loginAsUnidad();
  196 |     
  197 |     await page.goto('/procesos');
  198 |     await page.locator('tbody tr a').first().click();
  199 |     
  200 |     console.log('✅ Verificar documentos obligatorios');
  201 |     const docList = page.locator('.documento-requerido, .required-doc');
  202 |     const docCount = await docList.count();
  203 |     console.log(`Documentos requeridos: ${docCount}`);
  204 |   });
  205 | 
  206 |   test('CDPN-014: Navegación entre etapas', async ({ page }) => {
  207 |     const login = new LoginHelper(page);
  208 |     await login.loginAsUnidad();
  209 |     
  210 |     await page.goto('/procesos');
  211 |     await page.locator('tbody tr a').first().click();
  212 |     
  213 |     const tabs = page.locator('.etapa-tab, .step, [role="tab"]');
  214 |     const tabCount = await tabs.count();
  215 |     
  216 |     if (tabCount > 0) {
  217 |       await tabs.first().click();
  218 |       console.log('✅ Navegación entre etapas funciona');
  219 |     }
  220 |   });
  221 | 
  222 |   test('CDPN-015: Flujo completo end-to-end', async ({ page }) => {
  223 |     const login = new LoginHelper(page);
  224 |     await login.loginAsUnidad();
  225 |     
  226 |     // Crear proceso
  227 |     await page.goto('/procesos/crear');
  228 |     await page.fill('input[name="nombre"]', `E2E Test ${Date.now()}`);
  229 |     await page.click('button[type="submit"]');
  230 |     
```