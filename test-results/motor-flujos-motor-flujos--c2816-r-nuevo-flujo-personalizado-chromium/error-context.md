# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: motor-flujos\motor-flujos.spec.js >> Motor de Flujos Personalizados >> MOTOR-001: Crear nuevo flujo personalizado
- Location: tests\motor-flujos\motor-flujos.spec.js:16:3

# Error details

```
TimeoutError: page.click: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('button:has-text("Crear Flujo"), a:has-text("Nuevo Flujo")')

```

# Page snapshot

```yaml
- main [ref=e2]:
  - generic [ref=e4]:
    - heading "404" [level=1] [ref=e5]
    - generic [ref=e6]: Not Found
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { LoginHelper } from '../helpers/login.helper.js';
  3  | 
  4  | /**
  5  |  * PRUEBAS DE MOTOR DE FLUJOS
  6  |  * Casos: MOTOR-001 a MOTOR-003
  7  |  */
  8  | 
  9  | test.describe('Motor de Flujos Personalizados', () => {
  10 |   
  11 |   test.beforeEach(async ({ page }) => {
  12 |     const login = new LoginHelper(page);
  13 |     await login.loginAsAdmin();
  14 |   });
  15 | 
  16 |   test('MOTOR-001: Crear nuevo flujo personalizado', async ({ page }) => {
  17 |     console.log('✅ Ir a motor de flujos');
  18 |     await page.goto('/flujos');
  19 |     
  20 |     console.log('✅ Crear nuevo flujo');
> 21 |     await page.click('button:has-text("Crear Flujo"), a:has-text("Nuevo Flujo")');
     |                ^ TimeoutError: page.click: Timeout 10000ms exceeded.
  22 |     
  23 |     await page.fill('input[name="nombre"]', 'Flujo Playwright Test');
  24 |     await page.fill('textarea[name="descripcion"]', 'Flujo creado por Playwright');
  25 |     
  26 |     await page.click('button[type="submit"]');
  27 |     
  28 |     await expect(page.locator('body')).toContainText(/creado|exitoso/i);
  29 |   });
  30 | 
  31 |   test('MOTOR-002: Publicar versión de flujo', async ({ page }) => {
  32 |     await page.goto('/flujos');
  33 |     
  34 |     console.log('✅ Entrar a un flujo');
  35 |     const firstFlow = page.locator('tbody tr a, .flujo-item a').first();
  36 |     
  37 |     if (await firstFlow.isVisible({ timeout: 5000 })) {
  38 |       await firstFlow.click();
  39 |       
  40 |       console.log('✅ Publicar flujo');
  41 |       const publishButton = page.locator('button:has-text("Publicar")');
  42 |       
  43 |       if (await publishButton.isVisible({ timeout: 3000 })) {
  44 |         await publishButton.click();
  45 |         await expect(page.locator('body')).toContainText(/publicado|activo/i);
  46 |       }
  47 |     } else {
  48 |       test.skip();
  49 |     }
  50 |   });
  51 | 
  52 |   test('MOTOR-003: Versionado de flujos', async ({ page }) => {
  53 |     await page.goto('/flujos');
  54 |     
  55 |     const firstFlow = page.locator('tbody tr a').first();
  56 |     
  57 |     if (await firstFlow.isVisible({ timeout: 5000 })) {
  58 |       await firstFlow.click();
  59 |       
  60 |       console.log('✅ Verificar versiones');
  61 |       const versionSelect = page.locator('select:has-text("Versión"), [name="version"]');
  62 |       
  63 |       if (await versionSelect.isVisible({ timeout: 3000 })) {
  64 |         const versionCount = await versionSelect.locator('option').count();
  65 |         console.log(`✅ Versiones encontradas: ${versionCount}`);
  66 |         expect(versionCount).toBeGreaterThan(0);
  67 |       }
  68 |     } else {
  69 |       test.skip();
  70 |     }
  71 |   });
  72 | });
  73 | 
```