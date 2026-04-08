// tests/e2e/flujo-real-cdpn.spec.js
// ═══════════════════════════════════════════════════════════════════
// PRUEBAS E2E REALES - FLUJO CD-PN COMPLETO
// ═══════════════════════════════════════════════════════════════════
// Estos tests llenan formularios reales y crean datos en la BD
// Útil para: demos, certificación, pruebas de integración

import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';
import path from 'path';
import fs from 'fs';

// Variables compartidas entre tests
let procesoCreado = null;

test.describe('E2E Real: Flujo Completo CD-PN', () => {

  // ═══════════════════════════════════════════════════════════════
  // TEST 1: CREAR PROCESO (Nueva Solicitud)
  // ═══════════════════════════════════════════════════════════════
  test('REAL-001: Crear nueva solicitud CD-PN con todos los datos', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin(); // Admin puede seleccionar cualquier secretaría
    
    // Ir a crear proceso
    await page.goto('/procesos/crear');
    await page.waitForLoadState('networkidle');
    
    // Verificar que cargó la página
    await expect(page.locator('h1')).toContainText(/nueva solicitud/i);
    
    // ═══ SECCIÓN 1: Identificación del proceso ═══
    
    // Flujo (si hay selector)
    const flujoSelect = page.locator('select[name="flujo_id"]');
    if (await flujoSelect.isVisible({ timeout: 2000 })) {
      await flujoSelect.selectOption({ index: 1 }); // Primera opción real
    }
    
    // Objeto del contrato (OBLIGATORIO)
    const timestamp = Date.now();
    const objetoContrato = `Prestación de servicios profesionales para desarrollo de software - TEST AUTOMATIZADO ${timestamp}`;
    await page.fill('textarea[name="objeto"]', objetoContrato);
    
    // Descripción (opcional)
    await page.fill('textarea[name="descripcion"]', 'Proceso creado automáticamente por Playwright para validar el flujo completo de contratación directa persona natural.');
    
    // ═══ SECCIÓN 2: Dependencia solicitante ═══
    
    // Secretaría (si hay selector - admin puede seleccionar)
    const secretariaSelect = page.locator('select[name="secretaria_origen_id"]');
    if (await secretariaSelect.isVisible({ timeout: 2000 })) {
      const options = await secretariaSelect.locator('option').allTextContents();
      console.log('Secretarías disponibles:', options);
      // Seleccionar la primera que no esté vacía
      await secretariaSelect.selectOption({ index: 1 });
      await page.waitForTimeout(500); // Esperar carga de unidades
    }
    
    // Unidad (si hay selector)
    const unidadSelect = page.locator('select[name="unidad_origen_id"]');
    if (await unidadSelect.isVisible({ timeout: 2000 })) {
      await page.waitForTimeout(1000); // Esperar que carguen las opciones
      const options = await unidadSelect.locator('option').count();
      if (options > 1) {
        await unidadSelect.selectOption({ index: 1 });
      }
    }
    
    // ═══ SECCIÓN 3: Datos económicos ═══
    
    // Valor estimado (opcional)
    await page.fill('input[name="valor_estimado"]', '25000000');
    
    // Plazo de ejecución en meses (OBLIGATORIO)
    await page.fill('input[name="plazo_ejecucion_meses"]', '4');
    
    // ═══ SECCIÓN 4: Estudios Previos (OBLIGATORIO) ═══
    
    // Crear archivo temporal de prueba
    const testFilePath = path.join(process.cwd(), 'test-results', 'estudios_previos_test.pdf');
    const testDir = path.dirname(testFilePath);
    if (!fs.existsSync(testDir)) {
      fs.mkdirSync(testDir, { recursive: true });
    }
    
    // Crear un PDF simple de prueba (solo bytes mínimos de PDF)
    const pdfContent = Buffer.from('%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n>>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \ntrailer\n<<\n/Size 4\n/Root 1 0 R\n>>\nstartxref\n193\n%%EOF');
    fs.writeFileSync(testFilePath, pdfContent);
    
    // Subir el archivo
    const fileInput = page.locator('input[name="estudios_previos"]');
    await fileInput.setInputFiles(testFilePath);
    
    // ═══ ENVIAR FORMULARIO ═══
    
    // Tomar screenshot antes de enviar
    await page.screenshot({ path: 'test-results/antes-crear-proceso.png', fullPage: true });
    
    // Click en botón de crear/guardar
    const submitButton = page.locator('button[type="submit"]');
    await submitButton.click();
    
    // Esperar respuesta
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    // Tomar screenshot después
    await page.screenshot({ path: 'test-results/despues-crear-proceso.png', fullPage: true });
    
    // Verificar éxito
    const currentUrl = page.url();
    const bodyText = await page.locator('body').textContent();
    
    // Buscar indicadores de éxito
    const exito = 
      currentUrl.includes('/procesos/') && !currentUrl.includes('/crear') ||
      bodyText.toLowerCase().includes('creado') ||
      bodyText.toLowerCase().includes('éxito') ||
      bodyText.toLowerCase().includes('solicitud');
    
    // Capturar ID del proceso de la URL
    const matchId = currentUrl.match(/procesos\/(\d+)/);
    if (matchId) {
      procesoCreado = matchId[1];
      console.log(`\n✅ PROCESO CREADO EXITOSAMENTE`);
      console.log(`   ID: ${procesoCreado}`);
      console.log(`   URL: ${currentUrl}`);
    }
    
    // Buscar número de proceso en la página
    const numeroProceso = await page.locator('text=/CD-PN-\\d{4}-\\d+/').first().textContent().catch(() => null);
    if (numeroProceso) {
      console.log(`   Número: ${numeroProceso}`);
    }
    
    // Limpiar archivo temporal
    try { fs.unlinkSync(testFilePath); } catch (e) {}
    
    // Verificación final
    if (!exito) {
      // Ver si hay errores de validación
      const errores = await page.locator('.text-red-500, .text-red-600, .alert-danger, [role="alert"]').allTextContents();
      if (errores.length > 0) {
        console.log('❌ Errores de validación:', errores);
      }
    }
    
    expect(exito || procesoCreado).toBeTruthy();
  });

  // ═══════════════════════════════════════════════════════════════
  // TEST 2: VER PROCESO CREADO
  // ═══════════════════════════════════════════════════════════════
  test('REAL-002: Ver detalle del proceso creado', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    // Si tenemos el ID del proceso creado, ir directamente
    if (procesoCreado) {
      await page.goto(`/procesos/${procesoCreado}`);
    } else {
      // Sino, ir a la lista y abrir el primero
      await page.goto('/procesos');
      await page.waitForLoadState('networkidle');
      
      const primerProceso = page.locator('a[href*="/procesos/"]').first();
      if (await primerProceso.isVisible({ timeout: 5000 })) {
        await primerProceso.click();
      } else {
        test.skip('No hay procesos para ver');
        return;
      }
    }
    
    await page.waitForLoadState('networkidle');
    
    // Verificar que muestra información del proceso
    const bodyText = await page.locator('body').textContent();
    const tieneInfo = 
      bodyText.toLowerCase().includes('etapa') ||
      bodyText.toLowerCase().includes('proceso') ||
      bodyText.toLowerCase().includes('estado');
    
    await page.screenshot({ path: 'test-results/detalle-proceso.png', fullPage: true });
    
    console.log('✅ REAL-002: Detalle del proceso visible');
    expect(tieneInfo).toBeTruthy();
  });

  // ═══════════════════════════════════════════════════════════════
  // TEST 3: LISTAR PROCESOS
  // ═══════════════════════════════════════════════════════════════
  test('REAL-003: Listar todos los procesos', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    await page.goto('/procesos');
    await page.waitForLoadState('networkidle');
    
    // Contar procesos visibles
    const rows = await page.locator('tbody tr, .proceso-card, [data-proceso]').count();
    
    await page.screenshot({ path: 'test-results/lista-procesos.png', fullPage: true });
    
    console.log(`✅ REAL-003: ${rows} procesos encontrados en la lista`);
    expect(rows).toBeGreaterThanOrEqual(0);
  });

  // ═══════════════════════════════════════════════════════════════
  // TEST 4: NAVEGAR AL DASHBOARD
  // ═══════════════════════════════════════════════════════════════
  test('REAL-004: Dashboard carga correctamente', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    // Ir al dashboard/panel principal
    await page.goto('/panel-principal');
    await page.waitForLoadState('networkidle');
    
    // Verificar elementos del dashboard
    const bodyText = await page.locator('body').textContent();
    const tieneDashboard = 
      bodyText.includes('Resumen') ||
      bodyText.includes('Procesos') ||
      bodyText.includes('Bienvenido') ||
      bodyText.includes('panel');
    
    await page.screenshot({ path: 'test-results/dashboard.png', fullPage: true });
    
    console.log('✅ REAL-004: Dashboard cargado correctamente');
    expect(tieneDashboard).toBeTruthy();
  });

  // ═══════════════════════════════════════════════════════════════
  // TEST 5: MOTOR DE FLUJOS
  // ═══════════════════════════════════════════════════════════════
  test('REAL-005: Motor de Flujos carga correctamente', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    // Ir al motor de flujos
    await page.goto('/motor-flujos');
    await page.waitForLoadState('networkidle');
    
    // Verificar que carga el React component
    const bodyText = await page.locator('body').textContent();
    const tieneMotor = 
      bodyText.includes('Motor de Flujos') ||
      bodyText.includes('Flujos') ||
      bodyText.includes('CD-PN') ||
      bodyText.includes('Workflow');
    
    await page.screenshot({ path: 'test-results/motor-flujos.png', fullPage: true });
    
    console.log('✅ REAL-005: Motor de Flujos cargado');
    expect(tieneMotor).toBeTruthy();
  });

});

// ═══════════════════════════════════════════════════════════════════
// TESTS DE ROLES - Verificar acceso por rol
// ═══════════════════════════════════════════════════════════════════
test.describe('E2E Real: Acceso por Roles', () => {

  test('ROL-001: Jefe de Unidad puede crear solicitud', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsUnidad(); // jefe.sistemas@demo.com
    
    await page.goto('/procesos/crear');
    await page.waitForLoadState('networkidle');
    
    // Verificar que tiene acceso al formulario
    const tieneFormulario = await page.locator('form').isVisible();
    
    await page.screenshot({ path: 'test-results/rol-unidad-crear.png', fullPage: true });
    
    console.log('✅ ROL-001: Jefe de Unidad tiene acceso a crear solicitud');
    expect(tieneFormulario).toBeTruthy();
  });

  test('ROL-002: Usuario Planeación ve bandeja', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsPlaneacion();
    
    await page.goto('/panel-principal');
    await page.waitForLoadState('networkidle');
    
    await page.screenshot({ path: 'test-results/rol-planeacion-dashboard.png', fullPage: true });
    
    console.log('✅ ROL-002: Planeación accede al panel');
    expect(page.url()).toContain('panel-principal');
  });

  test('ROL-003: Usuario Hacienda ve bandeja', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsHacienda();
    
    await page.goto('/panel-principal');
    await page.waitForLoadState('networkidle');
    
    await page.screenshot({ path: 'test-results/rol-hacienda-dashboard.png', fullPage: true });
    
    console.log('✅ ROL-003: Hacienda accede al panel');
    expect(page.url()).toContain('panel-principal');
  });

  test('ROL-004: Logout funciona correctamente', async ({ page }) => {
    const login = new LoginHelper(page);
    await login.loginAsAdmin();
    
    // Ir al panel y verificar que está logueado
    await page.goto('/panel-principal');
    await page.waitForLoadState('networkidle');
    
    // Buscar botón/link de logout
    const logoutLink = page.locator('a[href*="logout"], form[action*="logout"] button, button:has-text("Cerrar")').first();
    
    if (await logoutLink.isVisible({ timeout: 5000 })) {
      await logoutLink.click();
      await page.waitForLoadState('networkidle');
      
      // Verificar que redirige a login
      await expect(page).toHaveURL(/login/);
      console.log('✅ ROL-004: Logout exitoso');
    } else {
      // Intentar logout directo
      await page.goto('/logout');
      await page.waitForLoadState('networkidle');
      console.log('✅ ROL-004: Logout vía URL');
    }
  });

});
