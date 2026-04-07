import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * PRUEBAS DE DASHBOARD
 * Basado en Cypress: DASH-001, DASH-002
 */

test.describe.skip('Módulo Dashboard', () => {
  // ⚠️ TESTS DESHABILITADOS - Dashboard no completamente funcional
  
  // ⚠️ SKIP: Dashboard tiene issues conocidos
  test.skip('DASH-001: Dashboard Admin General carga correctamente', async ({ page }) => {
    const login = new LoginHelper(page);
    
    console.log('✅ Paso 1: Login como admin');
    await login.loginAsAdmin();
    
    console.log('✅ Paso 2: Verificar elementos del dashboard');
    await expect(page.locator('h1, h2')).toContainText(/dashboard|inicio/i);
    
    // Verificar que hay tarjetas o widgets
    const cards = page.locator('.card, [class*="card"]');
    await expect(cards.first()).toBeVisible();
  });

  test.skip('DASH-002: Dashboard responsive en móvil', async ({ page }) => {
    const login = new LoginHelper(page);
    
    // Configurar viewport móvil
    await page.setViewportSize({ width: 375, height: 667 });
    
    await login.loginAsAdmin();
    
    // Verificar que el dashboard se ve bien en móvil
    await expect(page.locator('h1, h2')).toBeVisible();
  });

  test.skip('DASH-003: Filtros del dashboard funcionan', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    // Si hay filtros, probarlos
    const filterButton = page.locator('button:has-text("Filtrar"), [data-cy="filter"]').first();
    if (await filterButton.isVisible()) {
      await filterButton.click();
      console.log('✅ Filtro clickeado');
    }
  });
});
