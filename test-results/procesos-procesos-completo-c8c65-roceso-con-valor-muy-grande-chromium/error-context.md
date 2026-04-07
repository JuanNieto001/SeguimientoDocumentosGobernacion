# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: procesos\procesos-completo.spec.js >> Gestión de Procesos Contractuales >> PROC-012: Proceso con valor muy grande
- Location: tests\procesos\procesos-completo.spec.js:176:3

# Error details

```
TimeoutError: page.fill: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('input[name="valor"]')

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
  79  |   });
  80  | 
  81  |   test('PROC-005: Filtrar procesos por estado', async ({ page }) => {
  82  |     await page.goto('/procesos');
  83  |     
  84  |     const filterSelect = page.locator('select[name="estado"], #estado-filter');
  85  |     if (await filterSelect.isVisible({ timeout: 3000 })) {
  86  |       await filterSelect.selectOption({ index: 1 });
  87  |       await page.waitForTimeout(1000);
  88  |       
  89  |       console.log('✅ PROC-005: Filtro aplicado');
  90  |     }
  91  |   });
  92  | 
  93  |   test('PROC-006: Buscar proceso por código', async ({ page }) => {
  94  |     await page.goto('/procesos');
  95  |     
  96  |     const searchInput = page.locator('input[type="search"], input[name="search"]');
  97  |     if (await searchInput.isVisible({ timeout: 3000 })) {
  98  |       await searchInput.fill('CD-PN');
  99  |       await page.waitForTimeout(1000);
  100 |       
  101 |       console.log('✅ PROC-006: Búsqueda ejecutada');
  102 |     }
  103 |   });
  104 | 
  105 |   test('PROC-007: Avanzar proceso a siguiente etapa', async ({ page }) => {
  106 |     await page.goto('/procesos');
  107 |     
  108 |     const firstProcess = page.locator('tbody tr a').first();
  109 |     if (await firstProcess.isVisible({ timeout: 5000 })) {
  110 |       await firstProcess.click();
  111 |       
  112 |       const avanzarButton = page.locator('button:has-text("Avanzar"), button:has-text("Enviar")');
  113 |       if (await avanzarButton.isVisible({ timeout: 3000 })) {
  114 |         await avanzarButton.click();
  115 |         console.log('✅ PROC-007: Proceso avanzado');
  116 |       }
  117 |     }
  118 |   });
  119 | 
  120 |   // ============ CASOS NEGATIVOS ============
  121 | 
  122 |   test('PROC-008: Crear proceso sin nombre (validación)', async ({ page }) => {
  123 |     await page.goto('/procesos/crear');
  124 |     
  125 |     await page.fill('input[name="nombre"]', '');
  126 |     await page.click('button[type="submit"]');
  127 |     
  128 |     const hasError = await page.locator('.error, .invalid-feedback, [role="alert"]').count() > 0;
  129 |     expect(hasError).toBeTruthy();
  130 |     console.log('✅ PROC-008: Validación funcionó');
  131 |   });
  132 | 
  133 |   test('PROC-009: Crear proceso sin permisos', async ({ page }) => {
  134 |     await page.goto('/logout');
  135 |     await login.loginAs('consulta@test.com', 'Test1234!');
  136 |     
  137 |     await page.goto('/procesos/crear');
  138 |     
  139 |     const cannotCreate = await page.url().includes('/dashboard') ||
  140 |                          await page.locator('body').textContent().then(t => t.includes('permiso'));
  141 |     
  142 |     expect(cannotCreate).toBeTruthy();
  143 |     console.log('✅ PROC-009: Permiso denegado correctamente');
  144 |   });
  145 | 
  146 |   test('PROC-010: Editar proceso de otra unidad', async ({ page }) => {
  147 |     // Asumimos que hay procesos de otras unidades
  148 |     await page.goto('/procesos');
  149 |     
  150 |     const processFromOther = page.locator('tbody tr').first();
  151 |     if (await processFromOther.isVisible({ timeout: 3000 })) {
  152 |       await processFromOther.click();
  153 |       
  154 |       const editButton = page.locator('button:has-text("Editar")');
  155 |       const canEdit = await editButton.isVisible({ timeout: 2000 });
  156 |       
  157 |       console.log(`✅ PROC-010: Puede editar otros procesos: ${canEdit}`);
  158 |     }
  159 |   });
  160 | 
  161 |   // ============ EDGE CASES ============
  162 | 
  163 |   test('PROC-011: Proceso con valor negativo', async ({ page }) => {
  164 |     await page.goto('/procesos/crear');
  165 |     
  166 |     await page.fill('input[name="valor"]', '-1000');
  167 |     await page.click('button[type="submit"]');
  168 |     
  169 |     const hasError = await page.locator('body').textContent().then(t => 
  170 |       t.includes('positivo') || t.includes('válido') || t.includes('mayor')
  171 |     );
  172 |     
  173 |     console.log('✅ PROC-011: Validación de valor negativo');
  174 |   });
  175 | 
  176 |   test('PROC-012: Proceso con valor muy grande', async ({ page }) => {
  177 |     await page.goto('/procesos/crear');
  178 |     
> 179 |     await page.fill('input[name="valor"]', '999999999999');
      |                ^ TimeoutError: page.fill: Timeout 10000ms exceeded.
  180 |     await page.fill('input[name="nombre"]', 'Proceso valor grande');
  181 |     
  182 |     // Intentar guardar
  183 |     await page.click('button[type="submit"]');
  184 |     
  185 |     console.log('✅ PROC-012: Valor grande probado');
  186 |   });
  187 | 
  188 |   test('PROC-013: Código de proceso auto-generado', async ({ page }) => {
  189 |     await page.goto('/procesos/crear');
  190 |     
  191 |     await page.fill('input[name="nombre"]', 'Test AutoCodigo');
  192 |     await page.click('button[type="submit"]');
  193 |     
  194 |     await page.waitForTimeout(2000);
  195 |     
  196 |     await page.goto('/procesos');
  197 |     const hasCodigo = await page.locator('tbody td').first().textContent();
  198 |     
  199 |     console.log(`✅ PROC-013: Código generado: ${hasCodigo.slice(0, 20)}`);
  200 |   });
  201 | 
  202 |   test('PROC-014: Paginación de procesos', async ({ page }) => {
  203 |     await page.goto('/procesos');
  204 |     
  205 |     const pagination = page.locator('.pagination, [role="navigation"]');
  206 |     if (await pagination.isVisible({ timeout: 3000 })) {
  207 |       const pages = await pagination.locator('a, button').count();
  208 |       console.log(`✅ PROC-014: ${pages} páginas encontradas`);
  209 |     } else {
  210 |       console.log('✅ PROC-014: Sin paginación (pocos procesos)');
  211 |     }
  212 |   });
  213 | 
  214 |   test('PROC-015: Exportar lista de procesos', async ({ page }) => {
  215 |     await page.goto('/procesos');
  216 |     
  217 |     const exportButton = page.locator('button:has-text("Exportar"), a:has-text("Excel")');
  218 |     if (await exportButton.isVisible({ timeout: 3000 })) {
  219 |       await exportButton.click();
  220 |       console.log('✅ PROC-015: Exportación iniciada');
  221 |     } else {
  222 |       console.log('⚠️ PROC-015: Botón exportar no encontrado');
  223 |     }
  224 |   });
  225 | 
  226 |   test('PROC-016: Proceso en estado FINALIZADO no editable', async ({ page }) => {
  227 |     await page.goto('/procesos');
  228 |     
  229 |     // Buscar proceso finalizado
  230 |     const finalizadoRow = page.locator('tbody tr:has-text("FINALIZADO")').first();
  231 |     if (await finalizadoRow.isVisible({ timeout: 3000 })) {
  232 |       await finalizadoRow.click();
  233 |       
  234 |       const editButton = page.locator('button:has-text("Editar")');
  235 |       const canEdit = await editButton.isVisible({ timeout: 2000 });
  236 |       
  237 |       expect(canEdit).toBeFalsy();
  238 |       console.log('✅ PROC-016: Proceso finalizado no editable');
  239 |     }
  240 |   });
  241 | 
  242 |   test('PROC-017: Eliminar proceso en BORRADOR', async ({ page }) => {
  243 |     await page.goto('/procesos');
  244 |     
  245 |     const deleteButton = page.locator('button:has-text("Eliminar")').first();
  246 |     if (await deleteButton.isVisible({ timeout: 3000 })) {
  247 |       page.on('dialog', dialog => dialog.accept());
  248 |       await deleteButton.click();
  249 |       
  250 |       console.log('✅ PROC-017: Eliminación ejecutada');
  251 |     }
  252 |   });
  253 | 
  254 |   test('PROC-018: Ver historial de cambios', async ({ page }) => {
  255 |     await page.goto('/procesos');
  256 |     
  257 |     const firstProcess = page.locator('tbody tr a').first();
  258 |     if (await firstProcess.isVisible({ timeout: 5000 })) {
  259 |       await firstProcess.click();
  260 |       
  261 |       const historyTab = page.locator('a:has-text("Historial"), button:has-text("Auditoría")');
  262 |       if (await historyTab.isVisible({ timeout: 3000 })) {
  263 |         await historyTab.click();
  264 |         console.log('✅ PROC-018: Historial visible');
  265 |       }
  266 |     }
  267 |   });
  268 | 
  269 |   test('PROC-019: Rechazar proceso con motivo', async ({ page }) => {
  270 |     await page.goto('/procesos');
  271 |     
  272 |     const firstProcess = page.locator('tbody tr a').first();
  273 |     if (await firstProcess.isVisible({ timeout: 5000 })) {
  274 |       await firstProcess.click();
  275 |       
  276 |       const rechazarButton = page.locator('button:has-text("Rechazar"), button:has-text("Devolver")');
  277 |       if (await rechazarButton.isVisible({ timeout: 3000 })) {
  278 |         await rechazarButton.click();
  279 |         
```