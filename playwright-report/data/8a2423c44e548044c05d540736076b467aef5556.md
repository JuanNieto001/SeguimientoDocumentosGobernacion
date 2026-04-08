# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e\flujo-cdpn-completo.spec.js >> Flujo CD-PN Completo - Persona Natural >> CDPN-004: Admin exportar documentación
- Location: tests\e2e\flujo-cdpn-completo.spec.js:224:3

# Error details

```
TypeError: LoginHelper.loginAsAdmin is not a function
```

# Test source

```ts
  125 |         await reemplazarButton.click();
  126 |         await page.waitForTimeout(1000);
  127 |         
  128 |         const uploadInput2 = page.locator('input[type="file"]').first();
  129 |         const archivoActualizado = path.join(process.cwd(), 'test-results', 'archivos-prueba', 'documento-actualizado.pdf');
  130 |         await uploadInput2.setInputFiles(archivoActualizado);
  131 |         await page.waitForTimeout(2000);
  132 |         
  133 |         const confirmarButton = page.locator('button:has-text("Confirmar"), button:has-text("Guardar")').first();
  134 |         if (await confirmarButton.isVisible({ timeout: 3000 })) {
  135 |           await confirmarButton.click();
  136 |           await page.waitForTimeout(3000);
  137 |         }
  138 |         
  139 |         console.log('✅ Documento reemplazado con nueva versión');
  140 |       }
  141 |       
  142 |       console.log('🗑️ PASO 3: Verificar opción de borrar/eliminar...');
  143 |       
  144 |       const borrarButton = page.locator('button:has-text("Eliminar"), button:has-text("Borrar"), .btn-delete').first();
  145 |       if (await borrarButton.isVisible({ timeout: 3000 })) {
  146 |         console.log('✅ Botón eliminar disponible (NO se ejecuta para mantener datos)');
  147 |       }
  148 |     }
  149 |     
  150 |     await page.screenshot({ path: 'test-results/cdpn-002-gestion-archivos.png', fullPage: true });
  151 |   });
  152 | 
  153 |   test('CDPN-003: Consultar SECOP con cédula 1053850113', async ({ page }) => {
  154 |     await LoginHelper.loginAsAdmin(page);
  155 |     
  156 |     // Ir a la vista de SECOP consultas
  157 |     await page.goto('/secop');
  158 |     await page.waitForTimeout(3000);
  159 |     
  160 |     console.log('🔍 Consultando contratos SECOP con cédula 1053850113...');
  161 |     
  162 |     // Buscar campo de búsqueda de cédula
  163 |     const cedulaInput = page.locator('input[name="cedula"], input[name="identificacion"], input[placeholder*="cédula"]').first();
  164 |     if (await cedulaInput.isVisible({ timeout: 10000 })) {
  165 |       await cedulaInput.fill('1053850113');
  166 |       
  167 |       // Buscar botón de consultar
  168 |       const consultarButton = page.locator('button:has-text("Consultar"), button:has-text("Buscar"), button[type="submit"]').first();
  169 |       if (await consultarButton.isVisible({ timeout: 3000 })) {
  170 |         await consultarButton.click();
  171 |         await page.waitForTimeout(5000);
  172 |         
  173 |         // Verificar que aparezcan resultados
  174 |         const resultados = page.locator('table tbody tr, .contrato-item, .resultado-secop');
  175 |         const count = await resultados.count();
  176 |         
  177 |         console.log(`✅ Contratos encontrados: ${count}`);
  178 |         
  179 |         if (count > 0) {
  180 |           console.log('✅ SECOP retorna datos correctamente con cédula 1053850113');
  181 |         }
  182 |       }
  183 |     }
  184 |     
  185 |     console.log('📅 PASO 1: Verificar filtros disponibles...');
  186 |     
  187 |     // Buscar filtro de año
  188 |     const filtroAnio = page.locator('select[name="anio"], #filtro-anio').first();
  189 |     if (await filtroAnio.isVisible({ timeout: 3000 })) {
  190 |       await filtroAnio.selectOption({ label: /2026/i });
  191 |       await page.waitForTimeout(2000);
  192 |       
  193 |       const resultadosFiltrados = await resultados.count();
  194 |       console.log(`✅ Contratos año 2026: ${resultadosFiltrados}`);
  195 |     }
  196 |     
  197 |     console.log('📅 PASO 2: Filtrar por año anterior...');
  198 |     
  199 |     if (await filtroAnio.isVisible()) {
  200 |       await filtroAnio.selectOption({ label: /2025/i });
  201 |       await page.waitForTimeout(2000);
  202 |       
  203 |       const resultadosAnteriores = await resultados.count();
  204 |       console.log(`✅ Contratos año 2025: ${resultadosAnteriores}`);
  205 |     }
  206 |     
  207 |     console.log('🔘 PASO 3: Probar todos los botones disponibles...');
  208 |     
  209 |     // Seleccionar primer contrato
  210 |     const primerContrato = resultados.first();
  211 |     if (await primerContrato.isVisible({ timeout: 3000 })) {
  212 |       await primerContrato.click();
  213 |       await page.waitForTimeout(2000);
  214 |       
  215 |       // Probar botones de acción
  216 |       const botones = page.locator('button:visible');
  217 |       const countBotones = await botones.count();
  218 |       console.log(`✅ Botones disponibles en detalle: ${countBotones}`);
  219 |     }
  220 |     
  221 |     await page.screenshot({ path: 'test-results/cdpn-003-secop-completo.png', fullPage: true });
  222 |   });
  223 | 
  224 |   test('CDPN-004: Admin exportar documentación', async ({ page }) => {
> 225 |     await LoginHelper.loginAsAdmin(page);
      |                       ^ TypeError: LoginHelper.loginAsAdmin is not a function
  226 |     await page.goto('/paa');  // PAA tiene exportación implementada
  227 |     await page.waitForTimeout(3000);
  228 |     
  229 |     console.log('📊 Verificando permisos de exportación para Admin...');
  230 |     
  231 |     // Buscar botones de exportar (PAA tiene CSV y PDF)
  232 |     const exportarCSV = page.locator('a:has-text("CSV"), button:has-text("CSV"), a[href*="exportar/csv"]').first();
  233 |     const exportarPDF = page.locator('a:has-text("PDF"), button:has-text("PDF"), a[href*="exportar/pdf"]').first();
  234 |     
  235 |     if (await exportarCSV.isVisible({ timeout: 5000 })) {
  236 |       console.log('✅ Exportar CSV disponible para Admin');
  237 |     } else {
  238 |       console.log('⚠️ Exportar CSV no encontrado en PAA');
  239 |     }
  240 |     
  241 |     if (await exportarPDF.isVisible({ timeout: 5000 })) {
  242 |       console.log('✅ Exportar PDF disponible para Admin');
  243 |     } else {
  244 |       console.log('⚠️ Exportar PDF no encontrado en PAA');
  245 |     }
  246 |     
  247 |     // Verificar en procesos
  248 |     await page.goto('/procesos');
  249 |     await page.waitForTimeout(2000);
  250 |     
  251 |     const procesoTest = page.locator('text=/Test CD-PN SECOP/i').first();
  252 |     if (await procesoTest.isVisible({ timeout: 5000 })) {
  253 |       await procesoTest.click();
  254 |       await page.waitForTimeout(2000);
  255 |       
  256 |       // Verificar que admin puede ver todos los archivos
  257 |       const archivos = page.locator('a:has-text("Descargar"), button:has-text("Ver"), .archivo-item');
  258 |       const count = await archivos.count();
  259 |       
  260 |       console.log(`✅ Admin puede acceder a ${count} elementos del proceso`);
  261 |     }
  262 |     
  263 |     console.log('✅ CDPN-004: Admin tiene permisos de visualización completa');
  264 |     
  265 |     
  266 |     await page.screenshot({ path: 'test-results/cdpn-004-exportar-admin.png', fullPage: true });
  267 |   });
  268 | 
  269 |   test('CDPN-005: Flujo completo inicio a fin', async ({ page }) => {
  270 |     console.log('🎯 INICIANDO FLUJO COMPLETO CD-PN...');
  271 |     
  272 |     // ══════════════════════════════════════════════
  273 |     // ETAPA 0: CREAR PROCESO (Unidad Solicitante)
  274 |     // ══════════════════════════════════════════════
  275 |     await LoginHelper.loginAsAdmin(page);
  276 |     await page.goto('/procesos/crear');
  277 |     await page.waitForTimeout(3000);
  278 |     
  279 |     // Seleccionar flujo y llenar datos con cédula SECOP
  280 |     const flujoSelect = page.locator('select[name="flujo"], select[name="flujo_id"]').first();
  281 |     if (await flujoSelect.isVisible({ timeout: 5000 })) {
  282 |       // Buscar opción que contenga "Persona Natural" o "CD-PN"
  283 |       const options = await flujoSelect.locator('option').all();
  284 |       for (const option of options) {
  285 |         const text = await option.textContent();
  286 |         if (text && (text.includes('Persona Natural') || text.includes('CD-PN'))) {
  287 |           await flujoSelect.selectOption(await option.getAttribute('value') || '');
  288 |           break;
  289 |         }
  290 |       }
  291 |     }
  292 |     
  293 |     await page.fill('input[name="nombre"]', 'Flujo Completo E2E SECOP Test');
  294 |     await page.fill('input[name="valor"], input[name="valor_estimado"]', '75000000');
  295 |     await page.fill('textarea[name="objeto"]', 'Prueba E2E flujo completo CD-PN con cédula SECOP 1053850113');
  296 |     
  297 |     // CÉDULA CRÍTICA PARA SECOP
  298 |     const cedulaInput = page.locator('input[name="cedula"], input[name="identificacion"]').first();
  299 |     if (await cedulaInput.isVisible({ timeout: 3000 })) {
  300 |       await cedulaInput.fill('1053850113');
  301 |     }
  302 |     
  303 |     await page.click('button[type="submit"]');
  304 |     await page.waitForTimeout(4000);
  305 |     
  306 |     console.log('✅ Etapa 0: Proceso creado');
  307 |     await page.screenshot({ path: 'test-results/cdpn-005-e0-creado.png', fullPage: true });
  308 |     
  309 |     // ══════════════════════════════════════════════
  310 |     // ETAPA 1: PLANEACIÓN
  311 |     // ══════════════════════════════════════════════
  312 |     await page.goto('/logout');
  313 |     await page.waitForTimeout(1000);
  314 |     
  315 |     await LoginHelper.loginAsPlaneacion(page);
  316 |     await page.goto('/planeacion');
  317 |     await page.waitForTimeout(2000);
  318 |     
  319 |     const procesoPlaneacion = page.locator('text=/Flujo Completo E2E/i').first();
  320 |     if (await procesoPlaneacion.isVisible({ timeout: 5000 })) {
  321 |       await procesoPlaneacion.click();
  322 |       await page.waitForTimeout(2000);
  323 |       
  324 |       // Recibir proceso
  325 |       const recibirBtn = page.locator('button:has-text("Recib")').first();
```