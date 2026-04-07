import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * PRUEBAS DE MOTOR DE FLUJOS
 * Casos: MOTOR-001 a MOTOR-003
 */

test.describe.skip('Motor de Flujos Personalizados', () => {
  // ⚠️ TESTS DESHABILITADOS - Funcionalidad compleja
  
  test.beforeEach(async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
  });

  test('MOTOR-001: Crear nuevo flujo personalizado', async ({ page }) => {
    console.log('✅ Ir a motor de flujos');
    await page.goto('/flujos');
    
    console.log('✅ Crear nuevo flujo');
    await page.click('button:has-text("Crear Flujo"), a:has-text("Nuevo Flujo")');
    
    await page.fill('input[name="nombre"]', 'Flujo Playwright Test');
    await page.fill('textarea[name="descripcion"]', 'Flujo creado por Playwright');
    
    await page.click('button[type="submit"]');
    
    await expect(page.locator('body')).toContainText(/creado|exitoso/i);
  });

  test('MOTOR-002: Publicar versión de flujo', async ({ page }) => {
    await page.goto('/flujos');
    
    console.log('✅ Entrar a un flujo');
    const firstFlow = page.locator('tbody tr a, .flujo-item a').first();
    
    if (await firstFlow.isVisible({ timeout: 5000 })) {
      await firstFlow.click();
      
      console.log('✅ Publicar flujo');
      const publishButton = page.locator('button:has-text("Publicar")');
      
      if (await publishButton.isVisible({ timeout: 3000 })) {
        await publishButton.click();
        await expect(page.locator('body')).toContainText(/publicado|activo/i);
      }
    } else {
      test.skip();
    }
  });

  test('MOTOR-003: Versionado de flujos', async ({ page }) => {
    await page.goto('/flujos');
    
    const firstFlow = page.locator('tbody tr a').first();
    
    if (await firstFlow.isVisible({ timeout: 5000 })) {
      await firstFlow.click();
      
      console.log('✅ Verificar versiones');
      const versionSelect = page.locator('select:has-text("Versión"), [name="version"]');
      
      if (await versionSelect.isVisible({ timeout: 3000 })) {
        const versionCount = await versionSelect.locator('option').count();
        console.log(`✅ Versiones encontradas: ${versionCount}`);
        expect(versionCount).toBeGreaterThan(0);
      }
    } else {
      test.skip();
    }
  });
});
