# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e\flujo-cdpn-completo.spec.js >> Flujo CD-PN Completo - Persona Natural >> CDPN-003: Consultar SECOP con cédula 1053850113
- Location: tests\e2e\flujo-cdpn-completo.spec.js:153:3

# Error details

```
TypeError: LoginHelper.loginAsAdmin is not a function
```

# Test source

```ts
  54  |     await page.fill('input[name="nombre"]', 'Test CD-PN SECOP 1053850113');
  55  |     await page.fill('input[name="valor"]', '50000000');
  56  |     await page.fill('textarea[name="objeto"]', 'Contratación servicios profesionales para pruebas SECOP');
  57  |     
  58  |     // CÉDULA CRÍTICA PARA SECOP
  59  |     await page.fill('input[name="cedula"], input[name="identificacion"], #cedula-contratista', '1053850113');
  60  |     
  61  |     // Seleccionar secretaría y unidad
  62  |     const secretariaSelect = page.locator('select[name="secretaria"]').first();
  63  |     if (await secretariaSelect.isVisible({ timeout: 3000 })) {
  64  |       await secretariaSelect.selectOption({ index: 1 });
  65  |       await page.waitForTimeout(1000);
  66  |     }
  67  |     
  68  |     const unidadSelect = page.locator('select[name="unidad"]').first();
  69  |     if (await unidadSelect.isVisible({ timeout: 3000 })) {
  70  |       await unidadSelect.selectOption({ index: 1 });
  71  |     }
  72  |     
  73  |     // Crear proceso
  74  |     await page.click('button[type="submit"]');
  75  |     await page.waitForTimeout(4000);
  76  |     
  77  |     // Capturar ID del proceso creado desde URL
  78  |     const url = page.url();
  79  |     const match = url.match(/\/procesos\/(\d+)/);
  80  |     if (match) {
  81  |       procesoId = match[1];
  82  |       console.log(`✅ Proceso creado con ID: ${procesoId}`);
  83  |     }
  84  |     
  85  |     await page.screenshot({ path: 'test-results/cdpn-001-proceso-creado-secop.png', fullPage: true });
  86  |   });
  87  | 
  88  |   test('CDPN-002: Subir documento y gestionar versiones', async ({ page }) => {
  89  |     await LoginHelper.loginAsPlaneacion(page);
  90  |     
  91  |     // Ir a bandeja de Planeación
  92  |     await page.goto('/planeacion');
  93  |     await page.waitForTimeout(2000);
  94  |     
  95  |     const procesoTest = page.locator('text=/Test CD-PN SECOP/i').first();
  96  |     if (await procesoTest.isVisible({ timeout: 5000 })) {
  97  |       await procesoTest.click();
  98  |       await page.waitForTimeout(3000);
  99  |       
  100 |       console.log('📄 PASO 1: Recibir proceso en Planeación...');
  101 |       
  102 |       // Marcar como recibido
  103 |       const recibirButton = page.locator('button:has-text("Recib")').first();
  104 |       if (await recibirButton.isVisible({ timeout: 5000 })) {
  105 |         await recibirButton.click();
  106 |         await page.waitForTimeout(2000);
  107 |         console.log('✅ Proceso recibido en Planeación');
  108 |       }
  109 |       
  110 |       // Buscar área de subida de archivos (upload input)
  111 |       const uploadInput = page.locator('input[type="file"]').first();
  112 |       if (await uploadInput.isVisible({ timeout: 5000 })) {
  113 |         const archivoPrueba = path.join(process.cwd(), 'test-results', 'archivos-prueba', 'documento-prueba.pdf');
  114 |         await uploadInput.setInputFiles(archivoPrueba);
  115 |         await page.waitForTimeout(3000);
  116 |         
  117 |         console.log('✅ Documento inicial subido');
  118 |       }
  119 |       
  120 |       console.log('🔄 PASO 2: Reemplazar con versión actualizada...');
  121 |       
  122 |       // Buscar opción de reemplazar/actualizar documento
  123 |       const reemplazarButton = page.locator('button:has-text("Reemplazar"), button:has-text("Actualizar"), .btn-replace').first();
  124 |       if (await reemplazarButton.isVisible({ timeout: 3000 })) {
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
> 154 |     await LoginHelper.loginAsAdmin(page);
      |                       ^ TypeError: LoginHelper.loginAsAdmin is not a function
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
```