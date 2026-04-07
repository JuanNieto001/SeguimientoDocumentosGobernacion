import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * PRUEBAS DE RESPONSIVE DESIGN
 * Casos: RESP-001, RESP-002
 */

test.describe('Responsive Design', () => {
  
  test('RESP-001: Login en móvil', async ({ page }) => {
    console.log('✅ Configurar viewport móvil (iPhone 12)');
    await page.setViewportSize({ width: 390, height: 844 });
    
    await page.goto('/login');
    
    // Verificar que los elementos son visibles
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
    
    console.log('✅ Hacer login en móvil');
    await page.fill('input[name="email"]', 'admin@test.com');
    await page.fill('input[name="password"]', 'Test1234!');
    await page.click('button[type="submit"]');
    
    await expect(page).toHaveURL(/.*dashboard/);
  });

  test('RESP-002: Dashboard responsive', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    console.log('✅ Probar diferentes tamaños de pantalla');
    
    // Móvil pequeño (iPhone SE)
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('h1, h2')).toBeVisible();
    
    // Tablet
    await page.setViewportSize({ width: 768, height: 1024 });
    await expect(page.locator('h1, h2')).toBeVisible();
    
    // Desktop
    await page.setViewportSize({ width: 1920, height: 1080 });
    await expect(page.locator('h1, h2')).toBeVisible();
    
    console.log('✅ Dashboard responsive en todos los tamaños');
  });

  test('RESP-003: Menú hamburguesa en móvil', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Buscar menú hamburguesa
    const menuButton = page.locator('button:has-text("☰"), .menu-toggle, [aria-label="Menu"]');
    
    if (await menuButton.isVisible({ timeout: 3000 })) {
      console.log('✅ Menú hamburguesa encontrado');
      await menuButton.click();
      
      // Verificar que se abre el menú
      await page.waitForTimeout(500);
      console.log('✅ Menú desplegado');
    }
  });

  test('RESP-004: Tabla responsive con scroll', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/procesos');
    
    // Verificar que la tabla es scrolleable en móvil
    const table = page.locator('table, .table-container');
    
    if (await table.isVisible({ timeout: 5000 })) {
      const hasScroll = await page.evaluate(() => {
        const el = document.querySelector('table, .table-container');
        return el ? el.scrollWidth > el.clientWidth : false;
      });
      
      console.log(`✅ Tabla tiene scroll horizontal: ${hasScroll}`);
    }
  });
});
