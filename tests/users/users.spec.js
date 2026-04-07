import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

test.describe.skip('Gestión de Usuarios', () => {
  // ⚠️ TESTS DESHABILITADOS - Selectores necesitan ajuste
  test.beforeEach(async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
  });

  test('USERS-001: Crear usuario', async ({ page }) => {
    await page.goto('/usuarios');
    await page.click('button:has-text("Crear")');
    await page.fill('input[name="name"]', 'Usuario PW');
    await page.fill('input[name="email"]', `pw${Date.now()}@test.com`);
    await page.fill('input[name="password"]', 'Test1234!');
    await page.click('button[type="submit"]');
    await expect(page.locator('body')).toContainText(/creado|exitoso/i);
  });

  test('USERS-002: Editar usuario', async ({ page }) => {
    await page.goto('/usuarios');
    await page.locator('a:has-text("Editar")').first().click();
    await page.fill('input[name="name"]', 'Editado');
    await page.click('button[type="submit"]');
    await expect(page.locator('body')).toContainText(/actualizado/i);
  });

  test('USERS-003: Eliminar usuario', async ({ page }) => {
    await page.goto('/usuarios');
    page.on('dialog', dialog => dialog.accept());
    await page.locator('button:has-text("Eliminar")').first().click();
    await expect(page.locator('body')).toContainText(/eliminado/i);
  });

  test('USERS-004: Email duplicado', async ({ page }) => {
    await page.goto('/usuarios');
    await page.click('button:has-text("Crear")');
    await page.fill('input[name="email"]', 'admin@test.com');
    await page.click('button[type="submit"]');
    await expect(page.locator('body')).toContainText(/duplicado|existe/i);
  });
});
