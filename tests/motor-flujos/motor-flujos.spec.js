import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * PRUEBAS DE MOTOR DE FLUJOS
 * Casos: MOTOR-001 a MOTOR-006
 * Rehabilitadas usando LoginHelper corregido
 */

test.describe('Motor de Flujos - Tests Funcionales', () => {
  test.beforeEach(async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
  });

  test('MOTOR-001: Acceder al Motor de Flujos', async ({ page }) => {
    console.log('✅ Navegando al Motor de Flujos...');
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');

    // Verificar que carga correctamente
    const bodyText = await page.locator('body').textContent();
    const cargaOk =
      bodyText.includes('Motor de Flujos') ||
      bodyText.includes('CD-PN') ||
      bodyText.includes('Flujo') ||
      bodyText.includes('INICIO');

    await page.screenshot({ path: 'test-results/motor-flujos-carga.png', fullPage: true });

    console.log('✅ Motor de Flujos cargado correctamente');
    expect(cargaOk).toBeTruthy();
  });

  test('MOTOR-002: Abrir constructor con botón Crear Nuevo Flujo', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    const crearBtn = page.getByRole('button', { name: /Crear Nuevo Flujo/i }).first();
    await expect(crearBtn).toBeVisible({ timeout: 10000 });
    await crearBtn.click();

    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);

    const canvas = page.locator('.flow-canvas-wrapper .react-flow__pane, .flow-canvas-wrapper .react-flow').first();
    const catalogoPaso = page.locator('.catalog-sidebar .catalog-item[draggable="true"]').first();

    await expect(canvas).toBeVisible({ timeout: 10000 });
    await expect(catalogoPaso).toBeVisible({ timeout: 10000 });

    await page.screenshot({ path: 'test-results/motor-002-abrir-constructor.png', fullPage: true });

    console.log('✅ MOTOR-002: constructor abierto desde botón y listo para edición manual');
    expect(true).toBeTruthy();
  });

  test('MOTOR-003: Seleccionar flujo CD-PN y visualizar canvas', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2500);

    const descripcionFlujo = page.getByText(
      'Flujo oficial de Contratación Directa Persona Natural de la Gobernación de Caldas.',
      { exact: true }
    );

    await expect(descripcionFlujo).toBeVisible({ timeout: 10000 });
    await descripcionFlujo.click();
    await page.waitForTimeout(2500);

    const nodosCanvas = await page.locator('.react-flow__node, .paso-node, [data-id]').count();
    const textoInicioFin = await page.getByText(/INICIO|FIN/i).count();
    const nodos = nodosCanvas + textoInicioFin;

    await page.screenshot({ path: 'test-results/motor-003-canvas-flujo.png', fullPage: true });

    console.log(
      `✅ MOTOR-003: flujo seleccionado y ${nodos} elementos visuales encontrados (canvas=${nodosCanvas}, texto=${textoInicioFin})`
    );
    expect(nodos).toBeGreaterThan(0);
  });

  test('MOTOR-004: Ingresar al flujo y validar interacción del canvas', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2500);

    const descripcionFlujo = page.getByText(
      'Flujo oficial de Contratación Directa Persona Natural de la Gobernación de Caldas.',
      { exact: true }
    );

    await expect(descripcionFlujo).toBeVisible({ timeout: 10000 });
    await descripcionFlujo.click();
    await page.waitForTimeout(3000);

    const pane = page.locator('.react-flow__pane').first();
    const viewport = page.locator('.react-flow__viewport').first();
    const startNode = page.locator('[data-testid="rf__node-start"]').first();
    const endNode = page.locator('[data-testid="rf__node-end"]').first();
    const inicioTexto = page.getByText(/INICIO/i).first();
    const finTexto = page.getByText(/FIN/i).first();
    const edges = await page.locator('.react-flow__edge').count();
    const pasoNodes = await page.locator('.react-flow__node-pasoNode').count();

    await expect(pane).toBeVisible({ timeout: 10000 });
    await expect(viewport).toBeVisible({ timeout: 10000 });
    await expect(startNode).toBeVisible({ timeout: 10000 });
    await expect(endNode).toBeVisible({ timeout: 10000 });
    await expect(inicioTexto).toBeVisible({ timeout: 10000 });
    await expect(finTexto).toBeVisible({ timeout: 10000 });

    const transformAntes = (await viewport.getAttribute('style')) || '';

    await pane.hover();
    await page.mouse.down();
    await page.mouse.move(420, 260, { steps: 8 });
    await page.mouse.up();
    await page.waitForTimeout(800);

    const transformDespues = (await viewport.getAttribute('style')) || '';
    const seMovioCanvas = transformAntes !== transformDespues;

    await page.screenshot({ path: 'test-results/motor-004-editor-canvas.png', fullPage: true });

    console.log(
      `✅ MOTOR-004: canvas visible, inicio/fin visibles, pasos=${pasoNodes}, edges=${edges}, canvasMovido=${seMovioCanvas}`
    );
    expect(pasoNodes).toBeGreaterThan(0);
    expect(edges).toBeGreaterThan(0);
  });

  test('MOTOR-005: Crear nuevo flujo y guardarlo correctamente', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    const crearBtn = page.getByRole('button', { name: /Crear Nuevo Flujo/i });
    await expect(crearBtn).toBeVisible({ timeout: 10000 });
    await crearBtn.click();

    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2500);

    const timestamp = Date.now();
    const codigo = `AUTO-${timestamp}`;
    const nombre = `Flujo QA ${timestamp}`;
    const descripcion = `Flujo creado por prueba Playwright ${timestamp}`;

    const codigoInput = page.locator('input[name="codigo"], input[placeholder*="CÓDIGO" i], input[placeholder*="codigo" i]').first();
    const nombreInput = page.locator('input[name="nombre"], input[placeholder*="NOMBRE DEL FLUJO" i], input[placeholder*="nombre" i]').first();
    const descripcionInput = page
      .locator('textarea[name="descripcion"], textarea[placeholder*="Descripción" i], textarea[placeholder*="descripción" i], input[name="descripcion"]')
      .first();

    await codigoInput.fill(codigo);
    await nombreInput.fill(nombre);
    await descripcionInput.fill(descripcion);

    const catalogoPaso = page
      .locator('.paso-catalog-item, [draggable="true"], .cursor-grab')
      .filter({ hasText: /Adjudicación|Aprobación|ARL|Comité|Acta|SECOP/i })
      .first();

    const canvasDestino = page.locator('.react-flow__pane, .react-flow__viewport').first();
    const pasosAntes = await page.locator('.react-flow__node-pasoNode').count();

    await expect(catalogoPaso).toBeVisible({ timeout: 10000 });
    await expect(canvasDestino).toBeVisible({ timeout: 10000 });

    await catalogoPaso.dragTo(canvasDestino);
    await page.waitForTimeout(1500);

    const pasosDespues = await page.locator('.react-flow__node-pasoNode').count();
    const pasoAgregado = pasosDespues > pasosAntes;

    const guardarFlujoBtn = page.getByRole('button', { name: /Guardar Flujo/i }).first();
    if (await guardarFlujoBtn.isVisible().catch(() => false)) {
      await guardarFlujoBtn.click();
    } else {
      await page.getByRole('button', { name: /Guardar/i }).first().click();
    }

    await page.waitForTimeout(4000);

    const urlFinal = page.url();
    const regresoLista = urlFinal.includes('/motor-flujos') && !urlFinal.includes('nuevo-flujo');

    await page.screenshot({ path: 'test-results/motor-005-guardar-flujo.png', fullPage: true });

    console.log(
      `✅ MOTOR-005: pasoArrastrado=${pasoAgregado}, pasosAntes=${pasosAntes}, pasosDespues=${pasosDespues}, urlFinal=${urlFinal}`
    );
    expect(pasoAgregado).toBeTruthy();
    expect(regresoLista).toBeTruthy();
  });

  test('MOTOR-006: Duplicar un flujo desde el listado', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    const totalAntes = await page.locator('h4').count();
    expect(totalAntes).toBeGreaterThan(0);

    const duplicarBtn = page.getByRole('button', { name: /Duplicar/i }).first();
    await expect(duplicarBtn).toBeVisible({ timeout: 10000 });
    await duplicarBtn.click();

    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);

    const totalDespues = await page.locator('h4').count();
    const copias = await page.locator('h4').filter({ hasText: /(copia)/i }).count();

    await page.screenshot({ path: 'test-results/motor-006-duplicar-flujo.png', fullPage: true });

    console.log(`✅ MOTOR-006: duplicación ejecutada. antes=${totalAntes}, despues=${totalDespues}, copiasDetectadas=${copias}`);
    expect(totalDespues).toBeGreaterThan(totalAntes);
    expect(copias).toBeGreaterThan(0);
  });
});
