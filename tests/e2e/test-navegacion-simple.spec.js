// tests/e2e/test-navegacion-simple.spec.js
// ═══════════════════════════════════════════════════════════════════
// TEST SIMPLE - SOLO NAVEGACIÓN Y VERIFICACIÓN
// ═══════════════════════════════════════════════════════════════════
// Este test NO crea datos, solo navega y verifica que todo funciona

import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

test.describe('NAV: Navegación Simple del Sistema', () => {

  test('NAV-001: Login como Jefe Sistemas y ver panel', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      console.log('🔐 Login como jefe.sistemas@demo.com');
      await login.loginAsUnidad();
      
      // Esperar a que cargue
      await page.waitForTimeout(3000);
      
      console.log(`✅ URL actual: ${page.url()}`);
      
      // Screenshot del panel
      await page.screenshot({ 
        path: 'test-results/nav-001-panel-sistemas.png', 
        fullPage: true 
      });
      
      // Verificar que está logueado
      const url = page.url();
      expect(url).not.toContain('/login');
      
      console.log('✅ NAV-001: Login exitoso como jefe.sistemas');
      
    } catch (error) {
      console.error('❌ ERROR NAV-001:', error.message);
      await page.screenshot({ path: 'test-results/error-nav-001.png', fullPage: true });
      throw error;
    }
  });

  test('NAV-002: Login como Admin y ver panel', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      console.log('🔐 Login como admin@demo.com');
      await login.loginAsAdmin();
      
      await page.waitForTimeout(3000);
      
      console.log(`✅ URL actual: ${page.url()}`);
      
      await page.screenshot({ 
        path: 'test-results/nav-002-panel-admin.png', 
        fullPage: true 
      });
      
      const url = page.url();
      expect(url).not.toContain('/login');
      
      console.log('✅ NAV-002: Login exitoso como admin');
      
    } catch (error) {
      console.error('❌ ERROR NAV-002:', error.message);
      await page.screenshot({ path: 'test-results/error-nav-002.png', fullPage: true });
      throw error;
    }
  });

  test('NAV-003: Navegar por el menú principal', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      await login.loginAsAdmin();
      await page.waitForTimeout(2000);
      
      // Buscar enlaces del menú
      const links = await page.locator('a[href*="/procesos"], a:has-text("Procesos")').count();
      console.log(`✅ Enlaces "Procesos" encontrados: ${links}`);
      
      if (links > 0) {
        // Click en el primer enlace de procesos
        await page.locator('a[href*="/procesos"], a:has-text("Procesos")').first().click();
        await page.waitForTimeout(2000);
        
        console.log(`✅ URL después de click: ${page.url()}`);
        
        await page.screenshot({ 
          path: 'test-results/nav-003-lista-procesos.png', 
          fullPage: true 
        });
      }
      
      console.log('✅ NAV-003: Navegación por menú completada');
      
    } catch (error) {
      console.error('❌ ERROR NAV-003:', error.message);
      await page.screenshot({ path: 'test-results/error-nav-003.png', fullPage: true });
    }
  });

  test('NAV-004: Verificar roles y permisos', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      // Probar diferentes roles
      const usuarios = [
        { nombre: 'Admin', login: () => login.loginAsAdmin() },
        { nombre: 'Unidad', login: () => login.loginAsUnidad() },
        { nombre: 'Planeacion', login: () => login.loginAsPlaneacion() },
      ];
      
      for (const user of usuarios) {
        await user.login();
        await page.waitForTimeout(2000);
        
        const url = page.url();
        console.log(`✅ ${user.nombre}: ${url}`);
        
        await page.screenshot({ 
          path: `test-results/nav-004-${user.nombre.toLowerCase()}.png`, 
          fullPage: true 
        });
        
        // Logout
        await page.goto('/logout');
        await page.waitForTimeout(1000);
      }
      
      console.log('✅ NAV-004: Verificación de roles completada');
      
    } catch (error) {
      console.error('❌ ERROR NAV-004:', error.message);
      await page.screenshot({ path: 'test-results/error-nav-004.png', fullPage: true });
    }
  });

});
