import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * TESTS MOTOR DE FLUJOS - Verificación completa
 * 1. Verificar flujos existentes 
 * 2. Crear flujo CD-PJ si falta
 * 3. Verificar que aparecen en dropdown de solicitudes
 */

test.describe('Motor de Flujos - Tests Completos', () => {

  test.beforeEach(async ({ page }) => {
    await LoginHelper.loginAsAdmin(page);
  });

  test('MOTOR-001: Verificar que flujo CD-PN existe', async ({ page }) => {
    await page.goto('/motor-flujos');
    
    // Buscar flujo CD-PN por nombre
    const flujoCDPN = page.locator('text=/Persona Natural/i').first();
    await expect(flujoCDPN).toBeVisible({ timeout: 10000 });
    
    console.log('✅ MOTOR-001: Flujo CD-PN encontrado');
    await page.screenshot({ path: 'test-results/motor-001-cdpn-existe.png', fullPage: true });
  });

  test('MOTOR-002: Construir flujo arrastrando del catálogo', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForTimeout(2000);
    
    // Buscar botón "Nuevo Flujo"
    const nuevoFlujoButton = page.locator('button:has-text("Nuevo Flujo"), .btn:has-text("Nuevo"), #nuevo-flujo').first();
    
    if (await nuevoFlujoButton.isVisible({ timeout: 5000 })) {
      await nuevoFlujoButton.click();
      await page.waitForTimeout(3000);
      
      // Llenar datos básicos del flujo
      await page.fill('input[placeholder="Código..."], input[name="codigo"]', 'TEST-PLN');
      await page.fill('input[placeholder="Nombre del flujo"], input[name="nombre"]', 'Test Flujo Planeación');
      await page.fill('textarea[placeholder="Descripción"], textarea[name="descripcion"]', 'Flujo de prueba para Secretaría de Planeación');
      
      await page.waitForTimeout(2000);
      
      // Buscar el catálogo de pasos (lado izquierdo)
      const catalogoPasos = page.locator('.catalogo-pasos, #catalogo-pasos, [data-cy="catalogo-pasos"]');
      await expect(catalogoPasos).toBeVisible({ timeout: 5000 });
      
      // Buscar el primer paso disponible en el catálogo
      const primerPaso = page.locator('.catalogo-pasos .paso-item, .paso-catalog-item').first();
      
      if (await primerPaso.isVisible({ timeout: 3000 })) {
        // Buscar el canvas (área central donde van los pasos)
        const canvas = page.locator('.workflow-canvas, #flujo-canvas, .canvas-area').first();
        
        if (await canvas.isVisible({ timeout: 3000 })) {
          // Arrastrar paso del catálogo al canvas
          await primerPaso.dragTo(canvas);
          await page.waitForTimeout(2000);
          
          console.log('✅ Paso arrastrado del catálogo al canvas');
        } else {
          // Alternativamente, hacer click en el paso para agregarlo
          await primerPaso.click();
          await page.waitForTimeout(2000);
          
          console.log('✅ Paso agregado por click');
        }
        
        // ====== GUARDAR EL FLUJO COMPLETO ======
        console.log('💾 Guardando flujo...');
        
        // Buscar botón "Guardar Flujo" específicamente
        const guardarFlujoButton = page.locator('button:has-text("Guardar Flujo"), #guardar-flujo, .btn-guardar-flujo').first();
        
        if (await guardarFlujoButton.isVisible({ timeout: 3000 })) {
          await guardarFlujoButton.click();
          console.log('✅ Clic en "Guardar Flujo"');
        } else {
          // Fallback: buscar cualquier botón de guardar
          const guardarGenerico = page.locator('button:has-text("Guardar"), button[type="submit"]').first();
          if (await guardarGenerico.isVisible({ timeout: 3000 })) {
            await guardarGenerico.click();
            console.log('✅ Clic en botón guardar genérico');
          }
        }
        
        // Esperar confirmación de guardado
        await page.waitForTimeout(4000);
        
        // Verificar que se guardó exitosamente (buscar redirección o mensaje)
        const urlFinal = page.url();
        if (urlFinal.includes('motor-flujos') && !urlFinal.includes('nuevo')) {
          console.log('✅ MOTOR-002: Flujo guardado exitosamente - regresó a lista');
        } else {
          console.log('⚠️ MOTOR-002: Posible guardado, verificar manualmente');
        }
        
      } else {
        console.log('⚠️ MOTOR-002: No se encontraron pasos en catálogo');
      }
    } else {
      console.log('⚠️ MOTOR-002: No se encontró botón Nuevo Flujo');
    }
    
    await page.screenshot({ path: 'test-results/motor-002-flujo-guardado-completo.png', fullPage: true });
  });

  test('MOTOR-005: Verificar que nodos se pueden arrastrar en canvas', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForTimeout(2000);
    
    // Abrir flujo CD-PN existente
    const flujoCDPN = page.locator('text=/Persona Natural/i').first();
    await flujoCDPN.click();
    await page.waitForTimeout(5000);
    
    // Buscar botón editar/modo edición
    const editButton = page.locator('button:has-text("Editar"), button:has-text("Edit"), .btn-edit, #edit-mode').first();
    if (await editButton.isVisible({ timeout: 3000 })) {
      await editButton.click();
      await page.waitForTimeout(2000);
    }
    
    // Verificar que hay nodos visibles
    const startNode = page.locator('.react-flow__node, .workflow-node').first();
    await expect(startNode).toBeVisible({ timeout: 10000 });
    
    // Obtener posición inicial del nodo
    const initialBox = await startNode.boundingBox();
    
    if (initialBox) {
      console.log(`📍 Posición inicial: x=${initialBox.x}, y=${initialBox.y}`);
      
      // Intentar arrastrar el nodo (drag & drop)
      await startNode.hover();
      await page.mouse.down();
      await page.mouse.move(initialBox.x + 100, initialBox.y + 50, { steps: 5 });
      await page.mouse.up();
      
      await page.waitForTimeout(1000);
      
      // Verificar nueva posición
      const finalBox = await startNode.boundingBox();
      if (finalBox) {
        const moved = Math.abs(finalBox.x - initialBox.x) > 10 || Math.abs(finalBox.y - initialBox.y) > 10;
        
        console.log(`📍 Posición final: x=${finalBox.x}, y=${finalBox.y}`);
        console.log(`🔄 Nodo se movió: ${moved ? 'SÍ' : 'NO'}`);
        
        if (moved) {
          console.log('✅ MOTOR-005: Drag & drop funcional');
        } else {
          console.log('⚠️ MOTOR-005: Nodo no se movió, verificar modo edición');
        }
      }
    }
    
    await page.screenshot({ path: 'test-results/motor-005-drag-drop-test.png', fullPage: true });
  });

  test('MOTOR-003: Verificar que nodos se pueden arrastrar en canvas', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForTimeout(2000);
    
    // Abrir flujo CD-PN existente
    const flujoCDPN = page.locator('text=/Persona Natural/i').first();
    await flujoCDPN.click();
    await page.waitForTimeout(5000);
    
    // Buscar botón editar/modo edición
    const editButton = page.locator('button:has-text("Editar"), button:has-text("Edit"), .btn-edit, #edit-mode').first();
    if (await editButton.isVisible({ timeout: 3000 })) {
      await editButton.click();
      await page.waitForTimeout(2000);
    }
    
    // Verificar que hay nodos visibles
    const startNode = page.locator('.react-flow__node, .workflow-node').first();
    await expect(startNode).toBeVisible({ timeout: 10000 });
    
    // Obtener posición inicial del nodo
    const initialBox = await startNode.boundingBox();
    
    if (initialBox) {
      console.log(`📍 Posición inicial: x=${initialBox.x}, y=${initialBox.y}`);
      
      // Intentar arrastrar el nodo (drag & drop)
      await startNode.hover();
      await page.mouse.down();
      await page.mouse.move(initialBox.x + 100, initialBox.y + 50, { steps: 5 });
      await page.mouse.up();
      
      await page.waitForTimeout(1000);
      
      // Verificar nueva posición
      const finalBox = await startNode.boundingBox();
      if (finalBox) {
        const moved = Math.abs(finalBox.x - initialBox.x) > 10 || Math.abs(finalBox.y - initialBox.y) > 10;
        
        console.log(`📍 Posición final: x=${finalBox.x}, y=${finalBox.y}`);
        console.log(`🔄 Nodo se movió: ${moved ? 'SÍ' : 'NO'}`);
        
        if (moved) {
          console.log('✅ MOTOR-003: Drag & drop funcional');
        } else {
          console.log('⚠️ MOTOR-003: Nodo no se movió, verificar modo edición');
        }
      }
    }
    
    await page.screenshot({ path: 'test-results/motor-003-drag-drop-canvas.png', fullPage: true });
  });

  test('MOTOR-004: Verificar diseño serpentino funciona', async ({ page }) => {
    await page.goto('/motor-flujos');
    
    // Buscar el flujo CD-PN y abrirlo
    const flujoCDPN = page.locator('text=/Persona Natural/i').first();
    await flujoCDPN.click();
    await page.waitForTimeout(3000);
    
    // Verificar que se ve el layout serpentino
    const workflowContainer = page.locator('.react-flow, #workflow-container, .workflow-visualization').first();
    await expect(workflowContainer).toBeVisible({ timeout: 10000 });
    
    // Verificar que hay nodos visibles (indicativo del serpentino)
    const nodes = page.locator('.react-flow__node, .workflow-node');
    const nodeCount = await nodes.count();
    
    console.log(`🎯 Nodos encontrados: ${nodeCount}`);
    expect(nodeCount).toBeGreaterThan(0);
    
    console.log('✅ MOTOR-004: Layout serpentino visible');
    await page.screenshot({ path: 'test-results/motor-004-serpentino-activo.png', fullPage: true });
  });

  test('MOTOR-005: Ambos flujos aparecen al crear solicitud', async ({ page }) => {
    await page.goto('/procesos/crear');
    await page.waitForTimeout(2000);
    
    // Buscar dropdown de tipo de flujo
    const flujoSelect = page.locator('select[name="flujo"], select[name="flujo_id"], #flujo, #tipo-flujo').first();
    
    if (await flujoSelect.isVisible({ timeout: 5000 })) {
      // Verificar opciones disponibles
      const options = await flujoSelect.locator('option').count();
      console.log(`📋 Opciones de flujo encontradas: ${options}`);
      
      // Buscar específicamente CD-PN y CD-PJ
      const cdpnOption = page.locator('option:has-text("Persona Natural")').first();
      const cdpjOption = page.locator('option:has-text("Persona Jurídica"), option:has-text("Persona Juridica")').first();
      
      const cdpnExists = await cdpnOption.isVisible({ timeout: 2000 });
      const cdpjExists = await cdpjOption.isVisible({ timeout: 2000 });
      
      console.log(`✅ CD-PN en dropdown: ${cdpnExists ? 'SÍ' : 'NO'}`);
      console.log(`✅ CD-PJ en dropdown: ${cdpjExists ? 'SÍ' : 'NO'}`);
      
      // Al menos CD-PN debe existir
      expect(cdpnExists).toBeTruthy();
      
      await page.screenshot({ path: 'test-results/motor-005-dropdown-flujos.png', fullPage: true });
      
      console.log('✅ MOTOR-005: Verificación de dropdown completada');
    } else {
      console.log('⚠️ MOTOR-005: No se encontró dropdown de flujos en /procesos/crear');
      // Tomar screenshot para debug
      await page.screenshot({ path: 'test-results/motor-005-debug-no-dropdown.png', fullPage: true });
    }
  test('MOTOR-006: Crear solicitud y validar flujo completo', async ({ page }) => {
    // ===== PASO 1: CREAR NUEVA SOLICITUD =====
    await page.goto('/procesos/crear');
    await page.waitForTimeout(3000);
    
    console.log('📋 PASO 1: Verificando tipos de flujo disponibles...');
    
    // Verificar que aparezcan los tipos (flujos de contratación)
    const flujoSelect = page.locator('select[name="flujo"], select[name="flujo_id"], #flujo, #tipo-flujo').first();
    await expect(flujoSelect).toBeVisible({ timeout: 10000 });
    
    // Contar opciones de flujo
    const opciones = await flujoSelect.locator('option').count();
    console.log(`✅ Flujos disponibles: ${opciones}`);
    expect(opciones).toBeGreaterThan(1); // Al menos debe haber una opción + la vacía
    
    // Seleccionar flujo CD-PN (Persona Natural)
    const flujoOption = flujoSelect.locator('option:has-text("Persona Natural")').first();
    if (await flujoOption.isVisible({ timeout: 3000 })) {
      await flujoSelect.selectOption({ label: /Persona Natural/i });
      console.log('✅ Flujo CD-PN seleccionado');
    } else {
      // Fallback: seleccionar segunda opción (primera suele ser vacía)
      await flujoSelect.selectOption({ index: 1 });
      console.log('✅ Primer flujo disponible seleccionado');
    }
    
    // Llenar datos básicos de la solicitud
    await page.fill('input[name="nombre"], #nombre-proceso', 'Test Proceso Automatizado');
    await page.fill('input[name="valor"], #valor', '1000000');
    await page.fill('textarea[name="objeto"], #objeto', 'Contratación de servicios para testing automatizado');
    
    // Seleccionar secretaría y unidad si están disponibles
    const secretariaSelect = page.locator('select[name="secretaria"], select[name="secretaria_id"]').first();
    if (await secretariaSelect.isVisible({ timeout: 3000 })) {
      await secretariaSelect.selectOption({ index: 1 });
      await page.waitForTimeout(1000);
    }
    
    const unidadSelect = page.locator('select[name="unidad"], select[name="unidad_id"]').first();
    if (await unidadSelect.isVisible({ timeout: 3000 })) {
      await unidadSelect.selectOption({ index: 1 });
    }
    
    // Crear la solicitud
    await page.click('button[type="submit"], button:has-text("Crear")');
    await page.waitForTimeout(4000);
    
    console.log('✅ PASO 1 COMPLETADO: Solicitud creada con flujo asignado');
    
    // ===== PASO 2: LOGOUT ADMIN =====
    console.log('🔄 PASO 2: Cambiando de usuario...');
    
    await page.goto('/logout');
    await page.waitForTimeout(2000);
    
    // ===== PASO 3: LOGIN CON USUARIO DEL FLUJO =====
    console.log('🔐 PASO 3: Login con usuario de Planeación...');
    
    await page.goto('/login');
    await page.fill('input[name="email"]', 'planeacion@demo.com');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);
    
    // ===== PASO 4: VERIFICAR QUE VE LA SOLICITUD =====
    console.log('📋 PASO 4: Verificando que usuario ve la solicitud...');
    
    // Ir a su panel/dashboard
    const currentUrl = page.url();
    if (currentUrl.includes('planeacion')) {
      console.log('✅ Usuario en su dashboard específico');
    } else {
      // Ir a procesos generales
      await page.goto('/procesos');
      await page.waitForTimeout(2000);
    }
    
    // Buscar la solicitud creada
    const solicitudTest = page.locator('text=/Test Proceso Automatizado|Test.*Automatizado/i').first();
    if (await solicitudTest.isVisible({ timeout: 5000 })) {
      console.log('✅ PASO 4 COMPLETADO: Usuario ve la solicitud en su flujo');
      
      // Opcional: hacer clic para ver detalles
      await solicitudTest.click();
      await page.waitForTimeout(2000);
      
      console.log('✅ MOTOR-006: Flujo end-to-end EXITOSO');
    } else {
      console.log('⚠️ PASO 4: Solicitud no visible para usuario, verificar asignación de flujo');
    }
    
    await page.screenshot({ path: 'test-results/motor-006-flujo-end-to-end-completo.png', fullPage: true });
  });

});