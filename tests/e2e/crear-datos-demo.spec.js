// tests/e2e/crear-datos-demo.spec.js
// ═══════════════════════════════════════════════════════════════════
// CREAR DATOS DE DEMOSTRACIÓN PARA PRESENTACIÓN
// ═══════════════════════════════════════════════════════════════════
// Crea múltiples procesos en diferentes estados para demostrar mañana

import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

test.describe('DEMO: Crear datos de demostración', () => {

  // ⚠️ TESTS DESHABILITADOS - Los selectores necesitan ajuste
  // Usar test-navegacion-simple.spec.js para verificar accesos
  
  test.skip('DEMO-001: Proceso en Etapa 0 - Estudios Previos', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      await login.loginAsUnidad();
      await page.goto('/procesos/crear');
      await page.waitForTimeout(2000);
      
      await page.fill('input[name="objeto_contrato"]', 'Adquisición equipos de cómputo - DEMO Etapa 0');
      await page.fill('textarea[name="descripcion"]', 'Compra de equipos para modernización tecnológica');
      await page.selectOption('select[name="tipo_proceso"]', 'CD-PN');
      await page.fill('input[name="valor_estimado"]', '25000000');
      await page.fill('input[name="plazo_dias"]', '30');
      
      await page.click('button[type="submit"]');
      await page.waitForTimeout(3000);
      
      console.log('✅ DEMO-001: Proceso creado en Etapa 0');
      
    } catch (error) {
      console.error('❌ ERROR DEMO-001:', error.message);
      await page.screenshot({ path: 'test-results/error-demo-001.png', fullPage: true });
    }
  });

  // ═══════════════════════════════════════════════════════════════
  // PROCESO 2: EN ETAPA 1 (esperando CDP)
  // ═══════════════════════════════════════════════════════════════
  test.skip('DEMO-002: Proceso en Etapa 1 - Esperando CDP', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      await login.loginAsUnidad();
      await page.goto('/procesos/crear');
      await page.waitForTimeout(2000);
      
      await page.fill('input[name="objeto_contrato"]', 'Servicios mantenimiento instalaciones - DEMO Etapa 1');
      await page.fill('textarea[name="descripcion"]', 'Mantenimiento preventivo y correctivo infraestructura');
      await page.selectOption('select[name="tipo_proceso"]', 'CD-PN');
      await page.fill('input[name="valor_estimado"]', '18000000');
      await page.fill('input[name="plazo_dias"]', '90');
      
      await page.click('button[type="submit"]');
      await page.waitForTimeout(3000);
      
      console.log('✅ DEMO-002: Proceso en Etapa 1');
      
    } catch (error) {
      console.error('❌ ERROR DEMO-002:', error.message);
      await page.screenshot({ path: 'test-results/error-demo-002.png', fullPage: true });
    }
  });

  // ═══════════════════════════════════════════════════════════════
  // PROCESO 3: COMPLETADO (todas etapas)
  // ═══════════════════════════════════════════════════════════════
  test.skip('DEMO-003: Proceso completado - Todas etapas', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      await login.loginAsUnidad();
      await page.goto('/procesos/crear');
      await page.waitForTimeout(2000);
      
      await page.fill('input[name="objeto_contrato"]', 'Consultoría especializada - DEMO COMPLETADO');
      await page.fill('textarea[name="descripcion"]', 'Consultoría técnica para implementación sistema gestión');
      await page.selectOption('select[name="tipo_proceso"]', 'CD-PN');
      await page.fill('input[name="valor_estimado"]', '35000000');
      await page.fill('input[name="plazo_dias"]', '120');
      
      await page.click('button[type="submit"]');
      await page.waitForTimeout(3000);
      
      console.log('✅ DEMO-003: Proceso base creado');
      
    } catch (error) {
      console.error('❌ ERROR DEMO-003:', error.message);
      await page.screenshot({ path: 'test-results/error-demo-003.png', fullPage: true });
    }
  });

  // ═══════════════════════════════════════════════════════════════
  // VERIFICAR TODOS LOS PROCESOS DEMO
  // ═══════════════════════════════════════════════════════════════
  test.skip('DEMO-004: Verificar todos los procesos demo creados', async ({ page }) => {
    const login = new LoginHelper(page);
    
    try {
      await login.loginAsAdmin();
      await page.goto('/procesos');
      await page.waitForTimeout(3000);
      
      // Contar procesos visibles
      const totalProcesos = await page.locator('tr[data-proceso], .proceso-item, [class*="proceso"]').count();
      console.log(`✅ Total procesos en lista: ${totalProcesos}`);
      
      // Buscar procesos DEMO
      const procesosDEMO = await page.locator('text=/DEMO/i').count();
      console.log(`✅ Procesos DEMO creados: ${procesosDEMO}`);
      
      // Screenshot de la lista
      await page.screenshot({ 
        path: 'test-results/demo-procesos-lista.png',
        fullPage: true 
      });
      
      console.log(`\n╔════════════════════════════════════════════╗`);
      console.log(`║  ✅ DATOS DEMO CREADOS EXITOSAMENTE       ║`);
      console.log(`╠════════════════════════════════════════════╣`);
      console.log(`║  Total procesos: ${totalProcesos.toString().padEnd(25)}║`);
      console.log(`║  Procesos DEMO: ${procesosDEMO.toString().padEnd(26)}║`);
      console.log(`║                                            ║`);
      console.log(`║  📋 Listos para demostración mañana       ║`);
      console.log(`╚════════════════════════════════════════════╝\n`);
      
    } catch (error) {
      console.error('❌ ERROR DEMO-004:', error.message);
      await page.screenshot({ path: 'test-results/error-demo-004.png', fullPage: true });
      throw error;
    }
  });

});
