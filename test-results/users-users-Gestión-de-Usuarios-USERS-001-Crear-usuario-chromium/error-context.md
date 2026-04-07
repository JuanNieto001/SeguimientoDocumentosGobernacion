# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: users\users.spec.js >> Gestión de Usuarios >> USERS-001: Crear usuario
- Location: tests\users\users.spec.js:10:3

# Error details

```
TimeoutError: page.click: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('button:has-text("Crear")')

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
  4  | test.describe('Gestión de Usuarios', () => {
  5  |   test.beforeEach(async ({ page }) => {
  6  |     const login = new LoginHelper(page);
  7  |     await login.loginAsAdmin();
  8  |   });
  9  | 
  10 |   test('USERS-001: Crear usuario', async ({ page }) => {
  11 |     await page.goto('/usuarios');
> 12 |     await page.click('button:has-text("Crear")');
     |                ^ TimeoutError: page.click: Timeout 10000ms exceeded.
  13 |     await page.fill('input[name="name"]', 'Usuario PW');
  14 |     await page.fill('input[name="email"]', `pw${Date.now()}@test.com`);
  15 |     await page.fill('input[name="password"]', 'Test1234!');
  16 |     await page.click('button[type="submit"]');
  17 |     await expect(page.locator('body')).toContainText(/creado|exitoso/i);
  18 |   });
  19 | 
  20 |   test('USERS-002: Editar usuario', async ({ page }) => {
  21 |     await page.goto('/usuarios');
  22 |     await page.locator('a:has-text("Editar")').first().click();
  23 |     await page.fill('input[name="name"]', 'Editado');
  24 |     await page.click('button[type="submit"]');
  25 |     await expect(page.locator('body')).toContainText(/actualizado/i);
  26 |   });
  27 | 
  28 |   test('USERS-003: Eliminar usuario', async ({ page }) => {
  29 |     await page.goto('/usuarios');
  30 |     page.on('dialog', dialog => dialog.accept());
  31 |     await page.locator('button:has-text("Eliminar")').first().click();
  32 |     await expect(page.locator('body')).toContainText(/eliminado/i);
  33 |   });
  34 | 
  35 |   test('USERS-004: Email duplicado', async ({ page }) => {
  36 |     await page.goto('/usuarios');
  37 |     await page.click('button:has-text("Crear")');
  38 |     await page.fill('input[name="email"]', 'admin@test.com');
  39 |     await page.click('button[type="submit"]');
  40 |     await expect(page.locator('body')).toContainText(/duplicado|existe/i);
  41 |   });
  42 | });
  43 | 
```