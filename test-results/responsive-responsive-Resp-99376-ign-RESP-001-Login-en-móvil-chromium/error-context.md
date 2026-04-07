# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: responsive\responsive.spec.js >> Responsive Design >> RESP-001: Login en móvil
- Location: tests\responsive\responsive.spec.js:11:3

# Error details

```
Error: expect(page).toHaveURL(expected) failed

Expected pattern: /.*dashboard/
Received string:  "http://localhost:8000/login"
Timeout: 5000ms

Call log:
  - Expect "toHaveURL" with timeout 5000ms
    9 × unexpected value "http://localhost:8000/login"

```

# Page snapshot

```yaml
- generic [ref=e3]:
  - generic [ref=e4]:
    - img "Gobernación de Caldas" [ref=e6]
    - paragraph [ref=e7]: Gobernación de Caldas
    - paragraph [ref=e8]: Sistema de Seguimiento Contractual
  - generic [ref=e9]:
    - generic [ref=e10]:
      - heading "Iniciar sesión" [level=2] [ref=e11]
      - paragraph [ref=e12]: Ingresa tus credenciales institucionales para continuar
    - generic [ref=e13]:
      - img [ref=e14]
      - generic [ref=e16]: These credentials do not match our records.
    - generic [ref=e17]:
      - generic [ref=e18]:
        - generic [ref=e19]: Correo electrónico
        - textbox "Correo electrónico" [active] [ref=e20]:
          - /placeholder: usuario@gobernacion.gov.co
          - text: admin@test.com
      - generic [ref=e21]:
        - generic [ref=e22]: Contraseña
        - textbox "Contraseña" [ref=e23]:
          - /placeholder: ••••••••
      - link "¿Olvidaste tu contraseña?" [ref=e25] [cursor=pointer]:
        - /url: http://localhost:8000/forgot-password?usuario_email=admin%40test.com
        - img [ref=e26]
        - text: ¿Olvidaste tu contraseña?
      - generic [ref=e28]:
        - checkbox "Mantener sesión iniciada" [ref=e29]
        - generic [ref=e30] [cursor=pointer]: Mantener sesión iniciada
      - button "Ingresar al sistema" [ref=e31] [cursor=pointer]
    - paragraph [ref=e32]: Gobernación de Caldas — Acceso restringido a usuarios autorizados
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { LoginHelper } from '../helpers/login.helper.js';
  3  | 
  4  | /**
  5  |  * PRUEBAS DE RESPONSIVE DESIGN
  6  |  * Casos: RESP-001, RESP-002
  7  |  */
  8  | 
  9  | test.describe('Responsive Design', () => {
  10 |   
  11 |   test('RESP-001: Login en móvil', async ({ page }) => {
  12 |     console.log('✅ Configurar viewport móvil (iPhone 12)');
  13 |     await page.setViewportSize({ width: 390, height: 844 });
  14 |     
  15 |     await page.goto('/login');
  16 |     
  17 |     // Verificar que los elementos son visibles
  18 |     await expect(page.locator('input[name="email"]')).toBeVisible();
  19 |     await expect(page.locator('input[name="password"]')).toBeVisible();
  20 |     await expect(page.locator('button[type="submit"]')).toBeVisible();
  21 |     
  22 |     console.log('✅ Hacer login en móvil');
  23 |     await page.fill('input[name="email"]', 'admin@test.com');
  24 |     await page.fill('input[name="password"]', 'Test1234!');
  25 |     await page.click('button[type="submit"]');
  26 |     
> 27 |     await expect(page).toHaveURL(/.*dashboard/);
     |                        ^ Error: expect(page).toHaveURL(expected) failed
  28 |   });
  29 | 
  30 |   test('RESP-002: Dashboard responsive', async ({ page }) => {
  31 |     const login = new LoginHelper(page);
  32 |     await login.loginAsAdmin();
  33 |     
  34 |     console.log('✅ Probar diferentes tamaños de pantalla');
  35 |     
  36 |     // Móvil pequeño (iPhone SE)
  37 |     await page.setViewportSize({ width: 375, height: 667 });
  38 |     await expect(page.locator('h1, h2')).toBeVisible();
  39 |     
  40 |     // Tablet
  41 |     await page.setViewportSize({ width: 768, height: 1024 });
  42 |     await expect(page.locator('h1, h2')).toBeVisible();
  43 |     
  44 |     // Desktop
  45 |     await page.setViewportSize({ width: 1920, height: 1080 });
  46 |     await expect(page.locator('h1, h2')).toBeVisible();
  47 |     
  48 |     console.log('✅ Dashboard responsive en todos los tamaños');
  49 |   });
  50 | 
  51 |   test('RESP-003: Menú hamburguesa en móvil', async ({ page }) => {
  52 |     const login = new LoginHelper(page);
  53 |     await login.loginAsAdmin();
  54 |     
  55 |     await page.setViewportSize({ width: 375, height: 667 });
  56 |     
  57 |     // Buscar menú hamburguesa
  58 |     const menuButton = page.locator('button:has-text("☰"), .menu-toggle, [aria-label="Menu"]');
  59 |     
  60 |     if (await menuButton.isVisible({ timeout: 3000 })) {
  61 |       console.log('✅ Menú hamburguesa encontrado');
  62 |       await menuButton.click();
  63 |       
  64 |       // Verificar que se abre el menú
  65 |       await page.waitForTimeout(500);
  66 |       console.log('✅ Menú desplegado');
  67 |     }
  68 |   });
  69 | 
  70 |   test('RESP-004: Tabla responsive con scroll', async ({ page }) => {
  71 |     const login = new LoginHelper(page);
  72 |     await login.loginAsAdmin();
  73 |     
  74 |     await page.setViewportSize({ width: 375, height: 667 });
  75 |     await page.goto('/procesos');
  76 |     
  77 |     // Verificar que la tabla es scrolleable en móvil
  78 |     const table = page.locator('table, .table-container');
  79 |     
  80 |     if (await table.isVisible({ timeout: 5000 })) {
  81 |       const hasScroll = await page.evaluate(() => {
  82 |         const el = document.querySelector('table, .table-container');
  83 |         return el ? el.scrollWidth > el.clientWidth : false;
  84 |       });
  85 |       
  86 |       console.log(`✅ Tabla tiene scroll horizontal: ${hasScroll}`);
  87 |     }
  88 |   });
  89 | });
  90 | 
```