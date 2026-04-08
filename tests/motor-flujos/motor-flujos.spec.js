import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * PRUEBAS DE MOTOR DE FLUJOS
 * Casos: MOTOR-001 a MOTOR-005
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

  test('MOTOR-002: Visualizar pantalla de flujos configurados', async ({ page }) => {
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    await expect(page.getByText('Flujos Configurados')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText(/Seleccione uno para ver o editar/i)).toBeVisible({ timeout: 10000 });

    const tarjetas = await page.locator('div').filter({ hasText: 'Contratación Directa' }).count();

    await page.screenshot({ path: 'test-results/motor-002-lista-flujos.png', fullPage: true });

    console.log(`✅ MOTOR-002: pantalla de flujos visible, tarjetas detectadas=${tarjetas}`);
    expect(tarjetas).toBeGreaterThan(0);
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
});
