import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';
import path from 'path';

/**
 * PRUEBAS DE GESTIÓN DE DOCUMENTOS
 * Casos: DOCS-001 a DOCS-003
 */

test.describe('Módulo Gestión de Documentos', () => {
  
  test.beforeEach(async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
  });

  test('DOCS-001: Subir documento válido', async ({ page }) => {
    await page.goto('/procesos');
    
    const firstProcess = page.locator('tbody tr a, .proceso-item a').first();
    if (await firstProcess.isVisible({ timeout: 5000 })) {
      await firstProcess.click();
      
      const fileInput = page.locator('input[type="file"]');
      if (await fileInput.isVisible({ timeout: 3000 })) {
        await fileInput.setInputFiles({
          name: 'documento-prueba.pdf',
          mimeType: 'application/pdf',
          buffer: Buffer.from('PDF de prueba'),
        });
        
        await page.click('button:has-text("Subir"), button[type="submit"]');
        await expect(page.locator('body')).toContainText(/subido|cargado|exitoso/i);
      }
    } else {
      test.skip();
    }
  });

  test('DOCS-002: Archivo muy grande', async ({ page }) => {
    await page.goto('/procesos');
    
    const firstProcess = page.locator('tbody tr a').first();
    if (await firstProcess.isVisible({ timeout: 5000 })) {
      await firstProcess.click();
      
      const fileInput = page.locator('input[type="file"]');
      if (await fileInput.isVisible({ timeout: 3000 })) {
        const largeBuffer = Buffer.alloc(20 * 1024 * 1024);
        await fileInput.setInputFiles({
          name: 'documento-grande.pdf',
          mimeType: 'application/pdf',
          buffer: largeBuffer,
        });
        
        await page.click('button:has-text("Subir")');
        await expect(page.locator('body')).toContainText(/tamaño|grande|máximo/i);
      }
    } else {
      test.skip();
    }
  });

  test('DOCS-003: Tipo de archivo incorrecto', async ({ page }) => {
    await page.goto('/procesos');
    
    const firstProcess = page.locator('tbody tr a').first();
    if (await firstProcess.isVisible({ timeout: 5000 })) {
      await firstProcess.click();
      
      const fileInput = page.locator('input[type="file"]');
      if (await fileInput.isVisible({ timeout: 3000 })) {
        await fileInput.setInputFiles({
          name: 'documento.txt',
          mimeType: 'text/plain',
          buffer: Buffer.from('Archivo de texto'),
        });
        
        await page.click('button:has-text("Subir")');
        await expect(page.locator('body')).toContainText(/tipo.*archivo|formato/i);
      }
    } else {
      test.skip();
    }
  });
});
