// tests/simple-login-test.spec.js
// TEST SIMPLE PARA DIAGNOSTICAR LOGIN
import { test, expect } from '@playwright/test';

test.describe('Test Simple Login', () => {
  
  test('LOGIN-SIMPLE: Verificar ruta y formulario', async ({ page }) => {
    console.log('🔍 Paso 1: Ir a localhost:8000');
    await page.goto('http://localhost:8000');
    await page.waitForLoadState('domcontentloaded');
    
    console.log('📸 Screenshot homepage');
    await page.screenshot({ path: 'test-results/homepage.png', fullPage: true });
    
    console.log('🔍 Paso 2: Ir a /login');
    await page.goto('http://localhost:8000/login');
    await page.waitForLoadState('domcontentloaded');
    
    console.log('📸 Screenshot login page');
    await page.screenshot({ path: 'test-results/login-page.png', fullPage: true });
    
    console.log('🔍 Paso 3: Buscar campos de formulario');
    const emailField = await page.locator('input[type="email"], input[name="email"]').count();
    const passwordField = await page.locator('input[type="password"], input[name="password"]').count();
    const submitButton = await page.locator('button[type="submit"]').count();
    
    console.log(`✅ Campos encontrados:`);
    console.log(`   - Email inputs: ${emailField}`);
    console.log(`   - Password inputs: ${passwordField}`);
    console.log(`   - Submit buttons: ${submitButton}`);
    
    if (emailField > 0 && passwordField > 0) {
      console.log('🔍 Paso 4: Llenar formulario');
      await page.locator('input[type="email"], input[name="email"]').first().fill('admin@demo.com');
      await page.locator('input[type="password"], input[name="password"]').first().fill('12345678');
      
      console.log('📸 Screenshot form filled');
      await page.screenshot({ path: 'test-results/form-filled.png', fullPage: true });
      
      console.log('🔍 Paso 5: Submit form');
      await page.locator('button[type="submit"]').first().click();
      
      // Esperar 5 segundos
      await page.waitForTimeout(5000);
      
      console.log('📸 Screenshot after submit');
      await page.screenshot({ path: 'test-results/after-submit.png', fullPage: true });
      
      console.log(`✅ URL actual: ${page.url()}`);
      
    } else {
      console.error('❌ NO se encontraron campos de login');
      throw new Error('Formulario de login no encontrado');
    }
  });
  
});
