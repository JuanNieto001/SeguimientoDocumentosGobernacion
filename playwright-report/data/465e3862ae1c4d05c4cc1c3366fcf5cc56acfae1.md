# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e\flujo-cdpn-completo.spec.js >> Flujo CD-PN Completo - Persona Natural >> CDPN-005: Flujo completo inicio a fin
- Location: tests\e2e\flujo-cdpn-completo.spec.js:269:3

# Error details

```
TypeError: LoginHelper.loginAsAdmin is not a function
```

# Test source

```ts
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
  225 |     await LoginHelper.loginAsAdmin(page);
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
> 275 |     await LoginHelper.loginAsAdmin(page);
      |                       ^ TypeError: LoginHelper.loginAsAdmin is not a function
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
  326 |       if (await recibirBtn.isVisible({ timeout: 5000 })) {
  327 |         await recibirBtn.click();
  328 |         await page.waitForTimeout(2000);
  329 |         console.log('📥 Proceso recibido en Planeación');
  330 |       }
  331 |       
  332 |       // Marcar checks si existen
  333 |       const checks = page.locator('input[type="checkbox"]:not(:checked)');
  334 |       const checkCount = await checks.count();
  335 |       for (let i = 0; i < Math.min(checkCount, 5); i++) {
  336 |         await checks.nth(i).check({ timeout: 3000 }).catch(() => {});
  337 |         await page.waitForTimeout(500);
  338 |       }
  339 |       
  340 |       // Enviar a siguiente etapa
  341 |       const enviarBtn = page.locator('button:has-text("Enviar"), button:has-text("Aprobar")').first();
  342 |       if (await enviarBtn.isVisible({ timeout: 5000 })) {
  343 |         await enviarBtn.click();
  344 |         await page.waitForTimeout(3000);
  345 |         console.log('✅ Etapa 1: Planeación aprobó');
  346 |       }
  347 |     }
  348 |     
  349 |     await page.screenshot({ path: 'test-results/cdpn-005-e1-planeacion.png', fullPage: true });
  350 |     
  351 |     // ══════════════════════════════════════════════
  352 |     // ETAPA 2: HACIENDA
  353 |     // ══════════════════════════════════════════════
  354 |     await page.goto('/logout');
  355 |     await page.waitForTimeout(1000);
  356 |     
  357 |     await LoginHelper.loginAsHacienda(page);
  358 |     await page.goto('/hacienda');
  359 |     await page.waitForTimeout(2000);
  360 |     
  361 |     const procesoHacienda = page.locator('text=/Flujo Completo E2E/i').first();
  362 |     if (await procesoHacienda.isVisible({ timeout: 5000 })) {
  363 |       await procesoHacienda.click();
  364 |       await page.waitForTimeout(2000);
  365 |       
  366 |       // Recibir
  367 |       const recibirHacienda = page.locator('button:has-text("Recib")').first();
  368 |       if (await recibirHacienda.isVisible({ timeout: 5000 })) {
  369 |         await recibirHacienda.click();
  370 |         await page.waitForTimeout(2000);
  371 |         console.log('📥 Proceso recibido en Hacienda');
  372 |       }
  373 |       
  374 |       // Emitir CDP si hay formulario
  375 |       const cdpNumero = page.locator('input[name="numero_cdp"], input[placeholder*="CDP"]').first();
```