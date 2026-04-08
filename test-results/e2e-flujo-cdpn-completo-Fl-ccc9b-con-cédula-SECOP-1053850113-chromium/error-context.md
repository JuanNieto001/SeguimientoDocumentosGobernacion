# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e\flujo-cdpn-completo.spec.js >> Flujo CD-PN Completo - Persona Natural >> CDPN-001: Crear proceso con cédula SECOP 1053850113
- Location: tests\e2e\flujo-cdpn-completo.spec.js:40:3

# Error details

```
TypeError: LoginHelper.loginAsAdmin is not a function
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | import { LoginHelper } from '../helpers/login.helper.js';
  3   | import path from 'path';
  4   | import fs from 'fs';
  5   | 
  6   | /**
  7   |  * FLUJO COMPLETO CD-PN - PERSONA NATURAL
  8   |  * Tests end-to-end con:
  9   |  * - Cédula SECOP real: 1053850113
  10  |  * - Gestión completa de archivos (subir, reemplazar, borrar)
  11  |  * - Filtros SECOP (contratos año pasado vs este año)
  12  |  * - Exportaciones Admin
  13  |  */
  14  | 
  15  | test.describe('Flujo CD-PN Completo - Persona Natural', () => {
  16  |   
  17  |   let procesoId;
  18  | 
  19  |   test.beforeEach(async ({ page }) => {
  20  |     
  21  |     // Crear archivos de prueba si no existen
  22  |     const testDir = path.join(process.cwd(), 'test-results', 'archivos-prueba');
  23  |     if (!fs.existsSync(testDir)) {
  24  |       fs.mkdirSync(testDir, { recursive: true });
  25  |     }
  26  |     
  27  |     // Crear archivo PDF de prueba
  28  |     const pdfPath = path.join(testDir, 'documento-prueba.pdf');
  29  |     if (!fs.existsSync(pdfPath)) {
  30  |       fs.writeFileSync(pdfPath, '%PDF-1.4\nTest PDF Document');
  31  |     }
  32  |     
  33  |     // Crear archivo actualizado
  34  |     const pdfPath2 = path.join(testDir, 'documento-actualizado.pdf');
  35  |     if (!fs.existsSync(pdfPath2)) {
  36  |       fs.writeFileSync(pdfPath2, '%PDF-1.4\nTest PDF Updated Version');
  37  |     }
  38  |   });
  39  | 
  40  |   test('CDPN-001: Crear proceso con cédula SECOP 1053850113', async ({ page }) => {
> 41  |     await LoginHelper.loginAsAdmin(page);
      |                       ^ TypeError: LoginHelper.loginAsAdmin is not a function
  42  |     await page.goto('/procesos/crear');
  43  |     await page.waitForTimeout(3000);
  44  |     
  45  |     console.log('📋 Creando proceso CD-PN con cédula SECOP...');
  46  |     
  47  |     // Seleccionar flujo CD-PN
  48  |     const flujoSelect = page.locator('select[name="flujo"], select[name="flujo_id"]').first();
  49  |     if (await flujoSelect.isVisible({ timeout: 5000 })) {
  50  |       await flujoSelect.selectOption({ label: /Persona Natural/i });
  51  |     }
  52  |     
  53  |     // Llenar datos del proceso
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
```