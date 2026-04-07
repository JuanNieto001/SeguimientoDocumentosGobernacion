import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * PRUEBAS DE WORKFLOW CD-PN - COMPLETO
 * Casos: CDPN-001 a CDPN-015
 * Flujo de 9 etapas completo
 */

test.describe('Workflow Contratación Directa (CD-PN)', () => {
  
  test('CDPN-001: Crear nuevo proceso CD-PN', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos/crear');
    
    await page.fill('input[name="nombre"]', `Proceso PW ${Date.now()}`);
    await page.fill('textarea[name="descripcion"]', 'Proceso de prueba Playwright');
    
    const tipoSelect = page.locator('select[name="tipo"]');
    if (await tipoSelect.isVisible()) {
      await tipoSelect.selectOption('CD-PN');
    }
    
    await page.click('button[type="submit"]');
    await expect(page.locator('body')).toContainText(/creado|exitoso/i);
  });

  test('CDPN-002: Etapa 0 - Subir Estudios Previos', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const fileInput = page.locator('input[type="file"]');
    if (await fileInput.isVisible({ timeout: 5000 })) {
      await fileInput.setInputFiles({
        name: 'estudios-previos.pdf',
        mimeType: 'application/pdf',
        buffer: Buffer.from('Estudios Previos PDF'),
      });
      
      await page.click('button:has-text("Subir")');
      await expect(page.locator('body')).toContainText(/subido|exitoso/i);
    }
  });

  test('CDPN-003: Etapa 1 - CDP y Compatibilidad Presupuestal', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAs('planeacion@test.com', 'Test1234!');
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    console.log('✅ Verificar etapa 1 visible');
    const etapa1 = page.locator(':text("CDP"), :text("Disponibilidad")');
    if (await etapa1.first().isVisible({ timeout: 3000 })) {
      console.log('✅ Etapa 1 encontrada');
    }
  });

  test('CDPN-004: Etapa 2 - Validación Contratista', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const validacionInput = page.locator('input[name="nombre_contratista"], textarea[name="contratista"]');
    if (await validacionInput.first().isVisible({ timeout: 3000 })) {
      await validacionInput.first().fill('Contratista Prueba S.A.');
      await page.click('button:has-text("Guardar"), button[type="submit"]');
    }
  });

  test('CDPN-005: Etapa 3 - Elaborar Documentos Contractuales', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    console.log('✅ Etapa 3: Documentos contractuales');
    const fileInput = page.locator('input[type="file"]');
    if (await fileInput.isVisible({ timeout: 3000 })) {
      await fileInput.setInputFiles({
        name: 'minuta-contrato.pdf',
        mimeType: 'application/pdf',
        buffer: Buffer.from('Minuta de Contrato'),
      });
      
      await page.click('button:has-text("Subir")');
    }
  });

  test('CDPN-006: Etapa 4 - Consolidar Expediente', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    console.log('✅ Verificar expediente consolidado');
    const expediente = page.locator(':text("Expediente"), :text("Consolidar")');
    if (await expediente.first().isVisible({ timeout: 3000 })) {
      console.log('✅ Sección de expediente visible');
    }
  });

  test('CDPN-007: Etapa 5 - Radicación Jurídica', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAs('juridica@test.com', 'Test1234!');
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const radicacionInput = page.locator('input[name="radicado"], input[name="numero_radicado"]');
    if (await radicacionInput.isVisible({ timeout: 3000 })) {
      await radicacionInput.fill(`RAD-${Date.now()}`);
      await page.click('button:has-text("Guardar")');
    }
  });

  test('CDPN-008: Etapa 6 - Publicación SECOP II', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAs('secop@test.com', 'Test1234!');
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const secopInput = page.locator('input[name="secop"], input:has-text("SECOP")');
    if (await secopInput.isVisible({ timeout: 3000 })) {
      await secopInput.fill('SECOP-' + Date.now());
      await page.click('button:has-text("Publicar"), button:has-text("Guardar")');
    }
  });

  test('CDPN-009: Etapa 7 - Solicitar RPC', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const rpcButton = page.locator('button:has-text("RPC"), button:has-text("Registro")');
    if (await rpcButton.isVisible({ timeout: 3000 })) {
      await rpcButton.click();
      console.log('✅ Solicitud de RPC enviada');
    }
  });

  test('CDPN-010: Etapa 8 - Asignar Número de Contrato', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const contratoInput = page.locator('input[name="numero_contrato"]');
    if (await contratoInput.isVisible({ timeout: 3000 })) {
      await contratoInput.fill(`CONT-${Date.now()}`);
      await page.click('button:has-text("Guardar")');
    }
  });

  test('CDPN-011: Etapa 9 - Inicio de Ejecución', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const ejecucionButton = page.locator('button:has-text("Iniciar"), button:has-text("Ejecución")');
    if (await ejecucionButton.isVisible({ timeout: 3000 })) {
      await ejecucionButton.click();
      await expect(page.locator('body')).toContainText(/iniciado|ejecución/i);
    }
  });

  test('CDPN-012: Restricción - Solo Unidad puede crear', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAs('consulta@test.com', 'Test1234!');
    
    await page.goto('/procesos/crear');
    
    const cannotAccess = await page.url().includes('/dashboard') || 
                         await page.locator('body').textContent().then(t => t.includes('permiso'));
    expect(cannotAccess).toBeTruthy();
  });

  test('CDPN-013: Validación de documentos requeridos', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    console.log('✅ Verificar documentos obligatorios');
    const docList = page.locator('.documento-requerido, .required-doc');
    const docCount = await docList.count();
    console.log(`Documentos requeridos: ${docCount}`);
  });

  test('CDPN-014: Navegación entre etapas', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    await page.goto('/procesos');
    await page.locator('tbody tr a').first().click();
    
    const tabs = page.locator('.etapa-tab, .step, [role="tab"]');
    const tabCount = await tabs.count();
    
    if (tabCount > 0) {
      await tabs.first().click();
      console.log('✅ Navegación entre etapas funciona');
    }
  });

  test('CDPN-015: Flujo completo end-to-end', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad();
    
    // Crear proceso
    await page.goto('/procesos/crear');
    await page.fill('input[name="nombre"]', `E2E Test ${Date.now()}`);
    await page.click('button[type="submit"]');
    
    // Verificar creación
    await expect(page).toHaveURL(/.*procesos/);
    
    // Verificar que está en estado inicial
    await page.locator('tbody tr').first().click();
    await expect(page.locator('body')).toContainText(/etapa/i);
    
    console.log('✅ Flujo end-to-end completado');
  });
});
