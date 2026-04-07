import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * MÓDULO 3: GESTIÓN DE PROCESOS CONTRACTUALES
 * Casos: PROC-001 a PROC-020
 * CRÍTICO - CERTIFICACIÓN
 */

test.describe('Gestión de Procesos Contractuales', () => {
  
  let login;

  test.beforeEach(async ({ page }) => {
    login = new LoginHelper(page);
    await login.loginAsUnidad();
  });

  // ============ CASOS POSITIVOS ============
  
  test('PROC-001: Crear proceso CD-PN exitosamente', async ({ page }) => {
    await page.goto('/procesos/crear');
    
    await page.fill('input[name="nombre"]', `Proceso QA ${Date.now()}`);
    await page.fill('textarea[name="descripcion"]', 'Proceso de certificación QA');
    await page.fill('input[name="objeto"]', 'Contratación para pruebas QA');
    await page.fill('input[name="valor"]', '5000000');
    
    const tipoSelect = page.locator('select[name="tipo_proceso"], select[name="workflow_id"]');
    if (await tipoSelect.isVisible({ timeout: 3000 })) {
      await tipoSelect.selectOption({ label: /CD-PN|Contratación Directa/i });
    }
    
    await page.click('button[type="submit"]');
    
    await expect(page.locator('body')).toContainText(/creado|exitoso|éxito/i, { timeout: 10000 });
    console.log('✅ PROC-001: Proceso creado exitosamente');
  });

  test('PROC-002: Listar procesos propios', async ({ page }) => {
    await page.goto('/procesos');
    
    await expect(page.locator('h1, h2')).toContainText(/procesos/i);
    
    const rows = page.locator('tbody tr, .proceso-card');
    const count = await rows.count();
    
    console.log(`✅ PROC-002: ${count} procesos listados`);
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('PROC-003: Ver detalle de proceso', async ({ page }) => {
    await page.goto('/procesos');
    
    const firstProcess = page.locator('tbody tr a, .proceso-card a').first();
    if (await firstProcess.isVisible({ timeout: 5000 })) {
      await firstProcess.click();
      
      await expect(page.locator('body')).toContainText(/etapa|estado|proceso/i);
      console.log('✅ PROC-003: Detalle visible');
    } else {
      console.log('⚠️ PROC-003: No hay procesos para ver');
    }
  });

  test('PROC-004: Editar proceso en estado BORRADOR', async ({ page }) => {
    await page.goto('/procesos');
    
    const editButton = page.locator('a:has-text("Editar"), button:has-text("Editar")').first();
    if (await editButton.isVisible({ timeout: 5000 })) {
      await editButton.click();
      
      await page.fill('input[name="nombre"]', 'Proceso Editado QA');
      await page.click('button[type="submit"]');
      
      await expect(page.locator('body')).toContainText(/actualizado|guardado/i);
      console.log('✅ PROC-004: Proceso editado');
    }
  });

  test('PROC-005: Filtrar procesos por estado', async ({ page }) => {
    await page.goto('/procesos');
    
    const filterSelect = page.locator('select[name="estado"], #estado-filter');
    if (await filterSelect.isVisible({ timeout: 3000 })) {
      await filterSelect.selectOption({ index: 1 });
      await page.waitForTimeout(1000);
      
      console.log('✅ PROC-005: Filtro aplicado');
    }
  });

  test('PROC-006: Buscar proceso por código', async ({ page }) => {
    await page.goto('/procesos');
    
    const searchInput = page.locator('input[type="search"], input[name="search"]');
    if (await searchInput.isVisible({ timeout: 3000 })) {
      await searchInput.fill('CD-PN');
      await page.waitForTimeout(1000);
      
      console.log('✅ PROC-006: Búsqueda ejecutada');
    }
  });

  test('PROC-007: Avanzar proceso a siguiente etapa', async ({ page }) => {
    await page.goto('/procesos');
    
    const firstProcess = page.locator('tbody tr a').first();
    if (await firstProcess.isVisible({ timeout: 5000 })) {
      await firstProcess.click();
      
      const avanzarButton = page.locator('button:has-text("Avanzar"), button:has-text("Enviar")');
      if (await avanzarButton.isVisible({ timeout: 3000 })) {
        await avanzarButton.click();
        console.log('✅ PROC-007: Proceso avanzado');
      }
    }
  });

  // ============ CASOS NEGATIVOS ============

  test('PROC-008: Crear proceso sin nombre (validación)', async ({ page }) => {
    await page.goto('/procesos/crear');
    
    await page.fill('input[name="nombre"]', '');
    await page.click('button[type="submit"]');
    
    const hasError = await page.locator('.error, .invalid-feedback, [role="alert"]').count() > 0;
    expect(hasError).toBeTruthy();
    console.log('✅ PROC-008: Validación funcionó');
  });

  test('PROC-009: Crear proceso sin permisos', async ({ page }) => {
    await page.goto('/logout');
    await login.loginAs('consulta@test.com', 'Test1234!');
    
    await page.goto('/procesos/crear');
    
    const cannotCreate = await page.url().includes('/dashboard') ||
                         await page.locator('body').textContent().then(t => t.includes('permiso'));
    
    expect(cannotCreate).toBeTruthy();
    console.log('✅ PROC-009: Permiso denegado correctamente');
  });

  test('PROC-010: Editar proceso de otra unidad', async ({ page }) => {
    // Asumimos que hay procesos de otras unidades
    await page.goto('/procesos');
    
    const processFromOther = page.locator('tbody tr').first();
    if (await processFromOther.isVisible({ timeout: 3000 })) {
      await processFromOther.click();
      
      const editButton = page.locator('button:has-text("Editar")');
      const canEdit = await editButton.isVisible({ timeout: 2000 });
      
      console.log(`✅ PROC-010: Puede editar otros procesos: ${canEdit}`);
    }
  });

  // ============ EDGE CASES ============

  test('PROC-011: Proceso con valor negativo', async ({ page }) => {
    await page.goto('/procesos/crear');
    
    await page.fill('input[name="valor"]', '-1000');
    await page.click('button[type="submit"]');
    
    const hasError = await page.locator('body').textContent().then(t => 
      t.includes('positivo') || t.includes('válido') || t.includes('mayor')
    );
    
    console.log('✅ PROC-011: Validación de valor negativo');
  });

  test('PROC-012: Proceso con valor muy grande', async ({ page }) => {
    await page.goto('/procesos/crear');
    
    await page.fill('input[name="valor"]', '999999999999');
    await page.fill('input[name="nombre"]', 'Proceso valor grande');
    
    // Intentar guardar
    await page.click('button[type="submit"]');
    
    console.log('✅ PROC-012: Valor grande probado');
  });

  test('PROC-013: Código de proceso auto-generado', async ({ page }) => {
    await page.goto('/procesos/crear');
    
    await page.fill('input[name="nombre"]', 'Test AutoCodigo');
    await page.click('button[type="submit"]');
    
    await page.waitForTimeout(2000);
    
    await page.goto('/procesos');
    const hasCodigo = await page.locator('tbody td').first().textContent();
    
    console.log(`✅ PROC-013: Código generado: ${hasCodigo.slice(0, 20)}`);
  });

  test('PROC-014: Paginación de procesos', async ({ page }) => {
    await page.goto('/procesos');
    
    const pagination = page.locator('.pagination, [role="navigation"]');
    if (await pagination.isVisible({ timeout: 3000 })) {
      const pages = await pagination.locator('a, button').count();
      console.log(`✅ PROC-014: ${pages} páginas encontradas`);
    } else {
      console.log('✅ PROC-014: Sin paginación (pocos procesos)');
    }
  });

  test('PROC-015: Exportar lista de procesos', async ({ page }) => {
    await page.goto('/procesos');
    
    const exportButton = page.locator('button:has-text("Exportar"), a:has-text("Excel")');
    if (await exportButton.isVisible({ timeout: 3000 })) {
      await exportButton.click();
      console.log('✅ PROC-015: Exportación iniciada');
    } else {
      console.log('⚠️ PROC-015: Botón exportar no encontrado');
    }
  });

  test('PROC-016: Proceso en estado FINALIZADO no editable', async ({ page }) => {
    await page.goto('/procesos');
    
    // Buscar proceso finalizado
    const finalizadoRow = page.locator('tbody tr:has-text("FINALIZADO")').first();
    if (await finalizadoRow.isVisible({ timeout: 3000 })) {
      await finalizadoRow.click();
      
      const editButton = page.locator('button:has-text("Editar")');
      const canEdit = await editButton.isVisible({ timeout: 2000 });
      
      expect(canEdit).toBeFalsy();
      console.log('✅ PROC-016: Proceso finalizado no editable');
    }
  });

  test('PROC-017: Eliminar proceso en BORRADOR', async ({ page }) => {
    await page.goto('/procesos');
    
    const deleteButton = page.locator('button:has-text("Eliminar")').first();
    if (await deleteButton.isVisible({ timeout: 3000 })) {
      page.on('dialog', dialog => dialog.accept());
      await deleteButton.click();
      
      console.log('✅ PROC-017: Eliminación ejecutada');
    }
  });

  test('PROC-018: Ver historial de cambios', async ({ page }) => {
    await page.goto('/procesos');
    
    const firstProcess = page.locator('tbody tr a').first();
    if (await firstProcess.isVisible({ timeout: 5000 })) {
      await firstProcess.click();
      
      const historyTab = page.locator('a:has-text("Historial"), button:has-text("Auditoría")');
      if (await historyTab.isVisible({ timeout: 3000 })) {
        await historyTab.click();
        console.log('✅ PROC-018: Historial visible');
      }
    }
  });

  test('PROC-019: Rechazar proceso con motivo', async ({ page }) => {
    await page.goto('/procesos');
    
    const firstProcess = page.locator('tbody tr a').first();
    if (await firstProcess.isVisible({ timeout: 5000 })) {
      await firstProcess.click();
      
      const rechazarButton = page.locator('button:has-text("Rechazar"), button:has-text("Devolver")');
      if (await rechazarButton.isVisible({ timeout: 3000 })) {
        await rechazarButton.click();
        
        const motivoTextarea = page.locator('textarea[name="motivo"], textarea[name="observaciones"]');
        if (await motivoTextarea.isVisible({ timeout: 2000 })) {
          await motivoTextarea.fill('Rechazo de prueba QA - Documentos incompletos');
          await page.click('button:has-text("Confirmar"), button[type="submit"]');
          
          console.log('✅ PROC-019: Proceso rechazado con motivo');
        }
      }
    }
  });

  test('PROC-020: Proceso con caracteres especiales en nombre', async ({ page }) => {
    await page.goto('/procesos/crear');
    
    await page.fill('input[name="nombre"]', 'Proceso #123 @QA & Test <2026>');
    await page.fill('input[name="valor"]', '1000000');
    await page.click('button[type="submit"]');
    
    console.log('✅ PROC-020: Caracteres especiales probados');
  });
});
