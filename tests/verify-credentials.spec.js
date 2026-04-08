import { test, expect } from '@playwright/test';

/**
 * TEST RÁPIDO DE CREDENCIALES
 * Verificar que todas las contraseñas funcionen
 */

test.describe('Verificación de Credenciales', () => {
  
  test('CRED-001: Admin - 12345678', async ({ page }) => {
    await page.goto('/login');
    
    await page.fill('input[name="email"]', 'admin@demo.com');
    await page.fill('input[name="password"]', '12345678');
    await page.click('button[type="submit"]');
    
    // Esperar 5 segundos
    await page.waitForTimeout(5000);
    
    // Verificar que NO estamos en login (éxito)
    const currentUrl = page.url();
    const loginSuccess = !currentUrl.includes('/login');
    
    console.log(`URL después de login: ${currentUrl}`);
    console.log(`Login exitoso: ${loginSuccess}`);
    
    expect(loginSuccess).toBeTruthy();
    
    await page.screenshot({ path: 'test-results/admin-login-success.png', fullPage: true });
  });

  test('CRED-002: Usuario normal - 12345', async ({ page }) => {
    await page.goto('/login');
    
    await page.fill('input[name="email"]', 'planeacion@demo.com');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    
    // Esperar 5 segundos
    await page.waitForTimeout(5000);
    
    // Verificar que NO estamos en login (éxito)
    const currentUrl = page.url();
    const loginSuccess = !currentUrl.includes('/login');
    
    console.log(`URL después de login: ${currentUrl}`);
    console.log(`Login exitoso: ${loginSuccess}`);
    
    expect(loginSuccess).toBeTruthy();
    
    await page.screenshot({ path: 'test-results/user-login-success.png', fullPage: true });
  });

  test('CRED-003: Contraseña incorrecta debe fallar', async ({ page }) => {
    await page.goto('/login');
    
    await page.fill('input[name="email"]', 'admin@demo.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    // Esperar 3 segundos
    await page.waitForTimeout(3000);
    
    // Verificar que SEGUIMOS en login (fallo esperado)
    const currentUrl = page.url();
    const loginFailed = currentUrl.includes('/login');
    
    console.log(`URL después de login fallido: ${currentUrl}`);
    console.log(`Login falló como esperado: ${loginFailed}`);
    
    expect(loginFailed).toBeTruthy();
    
    await page.screenshot({ path: 'test-results/wrong-password-fail.png', fullPage: true });
  });

});