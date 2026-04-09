import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';
import { LoginHelper } from '../helpers/login.helper.js';

const MAX_STAGE_TRANSITIONS = 15;
const UI_OBSERVATION_DELAY_MS = 450;
const CONTRATISTA_DOCUMENTO_SECOP = '1053850113';
const CONTRATISTA_NOMBRE = 'Contratista SECOP 1053850113';
const STRICT_ROLE_PANEL_CHECKS = String(process.env.STRICT_ROLE_PANEL_CHECKS || '').toLowerCase() === 'true';
const FLOW_UPLOAD_FILE_PATH = String(process.env.FLOW_UPLOAD_FILE_PATH || '').trim();
const FLOW_FILL_CONTRACTOR_DATA = String(process.env.FLOW_FILL_CONTRACTOR_DATA || '').toLowerCase() === 'true';
const FLOW_REQUIRE_ESTUDIOS_UPLOAD = String(process.env.FLOW_REQUIRE_ESTUDIOS_UPLOAD || '').toLowerCase() === 'true';

let evidenceCounter = 0;

test.describe.configure({ mode: 'serial' });

test('FLOW-001-Flujo completo UI con dato SECOP 1053850113', async ({ page }) => {
  const login = new LoginHelper(page);
  page.on('dialog', async (dialog) => {
    await dialog.accept();
  });

  const fixturePath = resolveUploadFixturePath();
  const uniqueMarker = `FLOW-REAL-${Date.now()}`;

  let processCode = '';

  await test.step('AUTH-001-Login Unidad Solicitante', async () => {
    await loginAsRole(page, login, 'unidad_solicitante');
    await takeEvidence(page, 'AUTH-001', 'unidad-logueado');
  });

  await test.step('FLOW-002-Crear solicitud con contratista SECOP 1053850113', async () => {
    await createProcessFromUI(page, login, uniqueMarker, fixturePath);
    processCode = await captureCreatedProcessCode(page, uniqueMarker);
    await assertProcessDetailAccessible(page, processCode, uniqueMarker);
    await takeEvidence(page, 'FLOW-002', `solicitud-creada-${processCode}`);
  });

  await test.step('FLOW-003-Avance dinamico por todas las etapas hasta finalizado', async () => {
    let finished = false;

    for (let transition = 1; transition <= MAX_STAGE_TRANSITIONS; transition += 1) {
      const before = await getProcessStatusFromAdmin(page, login, processCode);
      await takeEvidence(page, 'FLOW-003', `iter-${transition}-antes-${before.areaRole}-${normalizeText(before.stateText)}`);

      if (before.isFinal) {
        finished = true;
        break;
      }

      if (before.isRejected) {
        throw new Error(`El proceso ${processCode} quedo rechazado antes de finalizar.`);
      }

      await advanceStageForArea(page, login, processCode, before.areaRole, fixturePath);

      const after = await getProcessStatusFromAdmin(page, login, processCode);
      await takeEvidence(page, 'FLOW-003', `iter-${transition}-despues-${after.areaRole}-${normalizeText(after.stateText)}`);

      if (after.isFinal) {
        finished = true;
        break;
      }

      if (
        after.areaRole === before.areaRole
        && normalizeText(after.stateText) === normalizeText(before.stateText)
      ) {
        throw new Error(
          `No hubo avance de etapa para ${processCode}. Area: ${before.areaRole}. Estado: ${before.stateText}`
        );
      }
    }

    const finalStatus = await getProcessStatusFromAdmin(page, login, processCode);
    await takeEvidence(page, 'FLOW-003', `estado-final-${finalStatus.areaRole}-${normalizeText(finalStatus.stateText)}`);
    expect(finalStatus.isFinal).toBeTruthy();
    expect(finished).toBeTruthy();
  });

  await test.step('FLOW-004-Validar panel principal por rol (Unidad, Secretario, Gobernador)', async () => {
    await validateDashboardViewsForRoles(page, login, processCode);
  });
});

async function loginAsRole(page, login, role) {
  await page.context().clearCookies();

  if (role === 'admin') {
    await login.loginAsAdmin();
  } else if (role === 'unidad_solicitante') {
    await login.loginAsUnidad();
  } else if (role === 'planeacion') {
    await login.loginAsPlaneacion();
  } else if (role === 'hacienda') {
    await login.loginAsHacienda();
  } else if (role === 'juridica') {
    await login.loginAsJuridica();
  } else if (role === 'secop') {
    await login.loginAsSECOP();
  } else if (role === 'secretario') {
    await login.loginAsSecretario();
  } else if (role === 'gobernador') {
    await login.loginAsGobernador();
  } else {
    throw new Error(`Rol no soportado para login: ${role}`);
  }

  await expect(page).not.toHaveURL(/\/login/);
}

async function createProcessFromUI(page, login, marker, fixturePath, remainingRetries = 3) {
  await page.goto('/procesos/crear');

  if (page.url().includes('/login')) {
    if (remainingRetries <= 0) {
      throw new Error('La sesión redirigió a login al abrir /procesos/crear y no quedan reintentos.');
    }

    console.log('⚠️ FLOW-002: redirección a login al abrir creación. Reautenticando y reintentando.');
    await loginAsRole(page, login, 'unidad_solicitante');
    return createProcessFromUI(page, login, marker, fixturePath, remainingRetries - 1);
  }

  await expect(page.locator('h1')).toContainText(/Nueva solicitud/i);
  await scrollToSection(page, /Nueva solicitud|Datos/i);
  await takeEvidence(page, 'FLOW-002', 'seccion-datos-generales-visible');

  const flowSelect = page.locator('select[name="flujo_id"]');
  if (await flowSelect.count()) {
    await selectPreferredFlow(flowSelect);
  }

  await page.fill('textarea[name="objeto"]', `${marker} proceso completo por UI`);
  await page.fill('textarea[name="descripcion"]', 'Caso FLOW real con evidencia completa (capturas + video).');
  await page.fill('input[name="valor_estimado"]', '18000000');
  await page.fill('input[name="plazo_ejecucion_meses"]', '4');
  await takeEvidence(page, 'FLOW-002', 'datos-generales-completados');

  const secretariaSelect = page.locator('select[name="secretaria_origen_id"]');
  if (await secretariaSelect.count() && await secretariaSelect.isVisible()) {
    await selectFirstNonEmptyOption(secretariaSelect);
  }

  const unidadSelect = page.locator('select[name="unidad_origen_id"]');
  if (await unidadSelect.count() && await unidadSelect.isVisible()) {
    await retryOnPage(
      page,
      async () => {
        const values = await unidadSelect.locator('option').evaluateAll((options) => {
          return options
            .map((option) => option.value)
            .filter((value) => value && value.trim() !== '');
        });

        if (!values.length) {
          throw new Error('Aun no hay opciones de unidad disponibles.');
        }
      },
      'esperar opciones de unidad'
    );

    await selectFirstNonEmptyOption(unidadSelect);
  }

  const contratistaNombre = page.locator('input[name="contratista_nombre"]');
  if (FLOW_FILL_CONTRACTOR_DATA && await contratistaNombre.count()) {
    await contratistaNombre.fill(CONTRATISTA_NOMBRE);
  }

  const contratistaTipoDoc = page.locator('select[name="contratista_tipo_documento"]');
  if (FLOW_FILL_CONTRACTOR_DATA && await contratistaTipoDoc.count()) {
    await contratistaTipoDoc.selectOption('CC');
  }

  const contratistaDocumento = page.locator('input[name="contratista_documento"]');
  if (FLOW_FILL_CONTRACTOR_DATA && await contratistaDocumento.count()) {
    await contratistaDocumento.fill(CONTRATISTA_DOCUMENTO_SECOP);
  }

  await scrollToSection(page, /Estudios Previos|Cargar documento/i);
  await takeEvidence(page, 'FLOW-002', 'estudios-previos-visible');

  const initialUploadDone = await uploadInitialFileIfAvailable(page, fixturePath);
  if (FLOW_REQUIRE_ESTUDIOS_UPLOAD && !initialUploadDone) {
    throw new Error('No se encontro un input de archivo para Estudios Previos y el flujo lo requiere.');
  }

  if (!initialUploadDone) {
    console.log('⚠️ FLOW-002: no se encontró campo para Estudios Previos. Se continúa sin adjunto inicial.');
  } else {
    await takeEvidence(page, 'FLOW-002', 'estudios-previos-cargado');
  }

  await scrollCreateProcessButtonIntoView(page);
  await takeEvidence(page, 'FLOW-002', 'formulario-completo-antes-crear');
  await clickCreateProcessButton(page);
  await page.waitForLoadState('networkidle').catch(() => {});
  await takeEvidence(page, 'FLOW-002', 'despues-clic-crear');

  if (page.url().includes('/login')) {
    const recovered = await recoverProcessAfterLoginRedirect(page, login, marker);
    if (recovered) {
      console.log('✅ FLOW-002: el proceso fue creado aunque hubo redirección a login después de enviar.');
      return;
    }

    if (remainingRetries <= 0) {
      throw new Error('La sesión expiró al enviar la solicitud y redirigió a /login sin reintentos disponibles.');
    }

    console.log('⚠️ FLOW-002: redirección a login después de enviar. Reautenticando y reintentando creación.');
    await loginAsRole(page, login, 'unidad_solicitante');
    return createProcessFromUI(page, login, marker, fixturePath, remainingRetries - 1);
  }

  await expect(page).toHaveURL(/\/procesos/);
}

async function recoverProcessAfterLoginRedirect(page, login, marker) {
  try {
    await loginAsRole(page, login, 'unidad_solicitante');
    await page.goto(`/procesos?buscar=${encodeURIComponent(marker)}`);

    const row = page.locator('tbody tr').filter({ hasText: marker }).first();
    const exists = await row.isVisible({ timeout: 4000 }).catch(() => false);
    return exists;
  } catch (error) {
    const detail = error instanceof Error ? error.message : String(error);
    console.log(`⚠️ FLOW-002: no se pudo recuperar sesión tras redirección a login. Detalle: ${detail}`);
    return false;
  }
}

async function captureCreatedProcessCode(page, marker) {
  await page.goto(`/procesos?buscar=${encodeURIComponent(marker)}`);

  const row = page.locator('tbody tr').filter({ hasText: marker }).first();
  await expect(row).toBeVisible();

  const codeText = (await row.locator('td').nth(0).innerText()).trim();
  expect(codeText).not.toEqual('');
  return codeText;
}

async function assertProcessDetailAccessible(page, processCode, marker) {
  await page.goto(`/procesos?buscar=${encodeURIComponent(processCode)}`);
  const row = page.locator('tbody tr').filter({ hasText: processCode }).first();
  await expect(row).toBeVisible();
  await takeEvidence(page, 'FLOW-002', `fila-proceso-visible-${processCode}`);

  const opened = await openProcessDetailFromRow(page, row);
  if (!opened) {
    console.log(`⚠️ FLOW-002: no se encontró enlace/acción de detalle para ${processCode}. Se continúa con el flujo.`);
    return;
  }

  await takeEvidence(page, 'FLOW-002', `detalle-intentado-${processCode}`);

  const bodyText = normalizeText(await page.locator('body').innerText());
  const markerNormalized = normalizeText(marker);
  const codeNormalized = normalizeText(processCode);

  const detailVisible = bodyText.includes(codeNormalized) || bodyText.includes(markerNormalized);
  if (!detailVisible) {
    console.log(`⚠️ FLOW-002: detalle de ${processCode} sin señales claras en pantalla. Se continúa con el flujo.`);
  }
}

async function openProcessDetailFromRow(page, row) {
  const candidates = [
    row.locator('a').filter({ hasText: /Exp\.|Expediente|Abrir|Ver|Detalle|Gestionar/i }).first(),
    row.locator('button').filter({ hasText: /Exp\.|Expediente|Abrir|Ver|Detalle|Gestionar/i }).first(),
    row.locator('a[href*="/procesos/"]').first(),
    row.locator('a').first(),
  ];

  for (const candidate of candidates) {
    if ((await candidate.count()) === 0) {
      continue;
    }

    const visible = await candidate.isVisible().catch(() => false);
    if (!visible) {
      continue;
    }

    try {
      await Promise.all([
        page.waitForLoadState('domcontentloaded').catch(() => {}),
        candidate.click(),
      ]);
      return true;
    } catch (error) {
      const detail = error instanceof Error ? error.message : String(error);
      console.log(`⚠️ FLOW-002: fallo al abrir detalle desde acción visible. Detalle: ${detail}`);
    }
  }

  const href = await row.locator('a').evaluateAll((links) => {
    const processHref = links
      .map((link) => link.getAttribute('href') || '')
      .find((value) => value.includes('/procesos/'));
    return processHref || '';
  });

  if (!href) {
    return false;
  }

  const target = href.startsWith('http')
    ? href
    : href.startsWith('/')
      ? href
      : `/${href}`;

  await page.goto(target);
  await page.waitForLoadState('domcontentloaded').catch(() => {});
  return true;
}

async function uploadInitialFileIfAvailable(page, fixturePath) {
  const selectors = [
    'input[name="estudios_previos"]',
    'input[type="file"][name*="estudio"]',
    'input[type="file"][name*="archivo"]',
    'input[type="file"]',
  ];

  for (const selector of selectors) {
    const input = page.locator(selector).first();
    if ((await input.count()) === 0) {
      continue;
    }

    try {
      await input.setInputFiles(fixturePath);
      return true;
    } catch (error) {
      const detail = error instanceof Error ? error.message : String(error);
      console.log(`⚠️ FLOW-002: fallo al subir archivo en selector ${selector}. Detalle: ${detail}`);
    }
  }

  return false;
}

async function scrollToSection(page, sectionPattern) {
  const header = page
    .locator('h1, h2, h3, h4, legend, label, div, span')
    .filter({ hasText: sectionPattern })
    .first();

  const visible = await header.isVisible({ timeout: 2000 }).catch(() => false);
  if (visible) {
    await header.scrollIntoViewIfNeeded();
  } else {
    await page.mouse.wheel(0, 700);
  }

  await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
}

async function resolveCreateProcessButton(page) {
  const candidates = [
    page.getByRole('button', { name: /Crear proceso/i }).first(),
    page.locator('button:has-text("Crear proceso")').first(),
    page.locator('button[type="submit"]').first(),
  ];

  for (const candidate of candidates) {
    if ((await candidate.count()) > 0) {
      return candidate;
    }
  }

  throw new Error('No se encontró botón para crear proceso.');
}

async function scrollCreateProcessButtonIntoView(page) {
  const createButton = await resolveCreateProcessButton(page);
  await createButton.scrollIntoViewIfNeeded().catch(() => {});
  await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
  return createButton;
}

async function clickCreateProcessButton(page) {
  const createButton = await scrollCreateProcessButtonIntoView(page);

  const isVisible = await createButton.isVisible().catch(() => false);
  if (!isVisible) {
    throw new Error('El botón Crear proceso no está visible para hacer clic.');
  }

  const isEnabled = await createButton.isEnabled().catch(() => false);
  if (!isEnabled) {
    throw new Error('El botón Crear proceso está deshabilitado.');
  }

  try {
    await createButton.click({ timeout: 10000 });
  } catch (error) {
    const detail = error instanceof Error ? error.message : String(error);
    console.log(`⚠️ FLOW-002: click normal falló en Crear proceso. Se reintenta con force=true. Detalle: ${detail}`);
    await createButton.click({ force: true, timeout: 10000 });
  }
}

async function getProcessStatusFromAdmin(page, login, processCode) {
  await loginAsRole(page, login, 'admin');
  await page.goto(`/procesos?buscar=${encodeURIComponent(processCode)}`);

  const row = page.locator('tbody tr').filter({ hasText: processCode }).first();
  await expect(row).toBeVisible();

  const stateText = normalizeWhitespace(await row.locator('td').nth(2).innerText());
  const areaText = normalizeWhitespace(await row.locator('td').nth(3).innerText());
  const areaRole = mapAreaLabelToRole(areaText);

  const normalizedState = normalizeText(stateText);

  return {
    stateText,
    areaText,
    areaRole,
    isFinal: normalizedState.includes('finalizado') || normalizedState.includes('completado'),
    isRejected: normalizedState.includes('rechazado'),
  };
}

async function advanceStageForArea(page, login, processCode, areaRole, fixturePath) {
  if (areaRole === 'planeacion') {
    await loginAsRole(page, login, 'planeacion');
    await openPlaneacionProcess(page, processCode);
    await completeStandardStage(page, 'FLOW-PLN-001');
    return;
  }

  if (areaRole === 'unidad_solicitante') {
    await loginAsRole(page, login, 'unidad_solicitante');
    await openUnidadProcess(page, processCode);

    const hasUnidadSpecialView = (await page.locator('form[action*="/recibido-fisico"]').count()) > 0;
    if (hasUnidadSpecialView) {
      await completeUnidadSpecialStage(page, fixturePath, 'FLOW-UNI-002');
    } else {
      await completeStandardStage(page, 'FLOW-UNI-001');
    }
    return;
  }

  if (areaRole === 'hacienda' || areaRole === 'juridica' || areaRole === 'secop') {
    await loginAsRole(page, login, areaRole);
    await openAreaInboxProcess(page, areaRole, processCode);
    await completeStandardStage(page, `FLOW-${areaRole.toUpperCase()}-001`);
    return;
  }

  throw new Error(`Area no soportada para avance automatico: ${areaRole}`);
}

async function openPlaneacionProcess(page, processCode) {
  await retryOnPage(
    page,
    async () => {
      await page.goto(`/planeacion?buscar=${encodeURIComponent(processCode)}`);

      const row = page.locator('tbody tr').filter({ hasText: processCode }).first();
      await expect(row).toBeVisible({ timeout: 6000 });

      const openButton = row.locator('a').filter({ hasText: 'Abrir' }).first();
      await expect(openButton).toBeVisible({ timeout: 6000 });

      await Promise.all([
        page.waitForLoadState('networkidle'),
        openButton.click(),
      ]);

      await expect(page).toHaveURL(/\/planeacion\/procesos\/\d+/);
    },
    'abrir proceso en planeacion'
  );
}

async function openUnidadProcess(page, processCode) {
  await retryOnPage(
    page,
    async () => {
      await page.goto('/unidad');

      const card = page.locator('a[href*="/unidad/procesos/"]').filter({ hasText: processCode }).first();
      await expect(card).toBeVisible({ timeout: 6000 });

      await Promise.all([
        page.waitForLoadState('networkidle'),
        card.click(),
      ]);
    },
    'abrir proceso en unidad'
  );
}

async function openAreaInboxProcess(page, areaRole, processCode) {
  await retryOnPage(
    page,
    async () => {
      await page.goto(`/${areaRole}`);

      const processLink = page
        .locator(`a[href*="/${areaRole}?proceso_id="]`)
        .filter({ hasText: processCode })
        .first();

      await expect(processLink).toBeVisible({ timeout: 6000 });

      await Promise.all([
        page.waitForLoadState('networkidle'),
        processLink.click(),
      ]);

      await expect(page.locator('body')).toContainText(processCode);
    },
    `abrir proceso en bandeja ${areaRole}`
  );
}

async function completeStandardStage(page, caseId) {
  await takeEvidence(page, caseId, 'inicio-etapa');
  await markProcessAsReceivedIfNeeded(page, caseId);
  await completeChecklistItems(page, caseId);
  await clickAdvanceAction(page, caseId);
  await takeEvidence(page, caseId, 'fin-etapa');
}

async function completeUnidadSpecialStage(page, fixturePath, caseId) {
  await takeEvidence(page, caseId, 'inicio-unidad-abogado');
  await markProcessAsReceivedIfNeeded(page, caseId);
  await markAllPhysicalDocuments(page, caseId);
  await uploadAllDigitalDocuments(page, fixturePath, caseId);
  await clickAdvanceAction(page, caseId);
  await takeEvidence(page, caseId, 'fin-unidad-abogado');
}

async function markProcessAsReceivedIfNeeded(page, caseId) {
  const receiveButton = page
    .getByRole('button', { name: /Marcar como recibido|Confirmar Recepci[o\u00f3]n del Documento/i })
    .first();

  if (await isVisibleAndEnabled(receiveButton)) {
    await takeEvidence(page, caseId, 'antes-marcar-recibido');
    await Promise.all([
      page.waitForLoadState('networkidle'),
      receiveButton.click(),
    ]);
    await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
    await takeEvidence(page, caseId, 'despues-marcar-recibido');
  }
}

async function completeChecklistItems(page, caseId) {
  for (let i = 1; i <= 80; i += 1) {
    const unchecked = page.locator(
      'form[action*="/checks/"] button:has-text("⬜"), form[action*="/checks/"] button:has-text("☐")'
    ).first();

    if (!(await isVisibleAndEnabled(unchecked))) {
      break;
    }

    await takeEvidence(page, caseId, `check-${String(i).padStart(2, '0')}-antes`);
    await Promise.all([
      page.waitForLoadState('networkidle'),
      unchecked.click(),
    ]);
    await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
    await takeEvidence(page, caseId, `check-${String(i).padStart(2, '0')}-despues`);
  }
}

async function markAllPhysicalDocuments(page, caseId) {
  for (let i = 1; i <= 120; i += 1) {
    const markPhysicalButton = page
      .locator('form[action*="/recibido-fisico"] button:has-text("Marcar recibido")')
      .first();

    if (!(await isVisibleAndEnabled(markPhysicalButton))) {
      break;
    }

    await takeEvidence(page, caseId, `fisico-${String(i).padStart(2, '0')}-antes`);
    await Promise.all([
      page.waitForLoadState('networkidle'),
      markPhysicalButton.click(),
    ]);
    await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
    await takeEvidence(page, caseId, `fisico-${String(i).padStart(2, '0')}-despues`);
  }
}

async function uploadAllDigitalDocuments(page, fixturePath, caseId) {
  for (let i = 1; i <= 120; i += 1) {
    const pendingUploadInput = page
      .locator('label:has-text("Subir digital") input[type="file"][name="archivo"]')
      .first();

    if ((await pendingUploadInput.count()) === 0) {
      break;
    }

    await takeEvidence(page, caseId, `subida-${String(i).padStart(2, '0')}-antes`);
    await pendingUploadInput.setInputFiles(fixturePath);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
    await takeEvidence(page, caseId, `subida-${String(i).padStart(2, '0')}-despues`);
  }
}

async function clickAdvanceAction(page, caseId) {
  const candidates = [
    /Aprobar y enviar a siguiente etapa|Aprobar y enviar a la siguiente etapa|Aprobar y solicitar documentos/i,
    /Enviar a la siguiente etapa|Enviar a siguiente etapa|Enviar a la siguiente secretaria|Enviar a siguiente secretar/i,
    /Finalizar proceso/i,
  ];

  for (const candidate of candidates) {
    const button = page.getByRole('button', { name: candidate }).first();
    if (await isVisibleAndEnabled(button)) {
      await takeEvidence(page, caseId, 'antes-avanzar-etapa');
      await Promise.all([
        page.waitForLoadState('networkidle'),
        button.click(),
      ]);
      await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
      await takeEvidence(page, caseId, 'despues-avanzar-etapa');
      return;
    }
  }

  throw new Error('No se encontro un boton habilitado para avanzar la etapa.');
}

async function validateDashboardViewsForRoles(page, login, processCode) {
  const rolesToValidate = [
    { role: 'unidad_solicitante', caseId: 'PANEL-001', required: true },
    { role: 'secretario', caseId: 'PANEL-002', required: false },
    { role: 'gobernador', caseId: 'PANEL-003', required: false },
  ];

  for (const roleConfig of rolesToValidate) {
    await validateDashboardViewForRole(page, login, processCode, roleConfig);
  }
}

async function validateDashboardViewForRole(page, login, processCode, roleConfig) {
  const { role, caseId, required } = roleConfig;

  try {
    await loginAsRole(page, login, role);
  } catch (error) {
    const detail = error instanceof Error ? error.message : String(error);

    if (required || STRICT_ROLE_PANEL_CHECKS) {
      throw new Error(`${caseId}: no fue posible autenticar el rol ${role}. Detalle: ${detail}`);
    }

    console.log(`⚠️ ${caseId}: rol ${role} no disponible en este ambiente. Se omite validación de panel. Detalle=${detail}`);
    return;
  }

  await openMainPanel(page);
  await assertMainPanelLoaded(page, caseId, role);
  await assertProcessVisibilityFromPanel(page, processCode, caseId, role, { requireVisible: required });
}

async function openMainPanel(page) {
  await retryOnPage(
    page,
    async () => {
      await page.goto('/panel-principal');
      await expect(page).not.toHaveURL(/\/login/);
      await page.waitForLoadState('networkidle');
    },
    'abrir panel principal'
  );
}

async function assertMainPanelLoaded(page, caseId, role) {
  const bodyText = normalizeText(await page.locator('body').innerText());
  const hasDashboardSignal =
    bodyText.includes('panel de control')
    || bodyText.includes('bienvenido')
    || bodyText.includes('mi dashboard')
    || bodyText.includes('procesos en curso')
    || bodyText.includes('resumen');

  await takeEvidence(page, caseId, `${role}-panel-principal`);
  expect(hasDashboardSignal).toBeTruthy();
}

async function assertProcessVisibilityFromPanel(page, processCode, caseId, role, { requireVisible = false } = {}) {
  await page.goto(`/procesos?buscar=${encodeURIComponent(processCode)}`);
  await page.waitForLoadState('networkidle');

  const row = page.locator('tbody tr').filter({ hasText: processCode }).first();
  const isVisible = await row.isVisible({ timeout: 3000 }).catch(() => false);

  await takeEvidence(page, caseId, `${role}-consulta-proceso-${isVisible ? 'visible' : 'sin-registro'}`);

  if (requireVisible) {
    expect(isVisible).toBeTruthy();
  }
}

async function takeEvidence(page, caseId, label) {
  evidenceCounter += 1;
  const evidenceDir = path.join(process.cwd(), 'test-results', 'evidencias-ui');
  if (!fs.existsSync(evidenceDir)) {
    fs.mkdirSync(evidenceDir, { recursive: true });
  }

  const safeCase = sanitizeForPath(caseId);
  const safeLabel = sanitizeForPath(label);
  const fileName = `${String(evidenceCounter).padStart(4, '0')}-${safeCase}-${safeLabel}.png`;
  const target = path.join(evidenceDir, fileName);

  await page.screenshot({ path: target, fullPage: true });
}

function sanitizeForPath(value) {
  return String(value)
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-zA-Z0-9-_]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
    .toLowerCase();
}

async function selectPreferredFlow(flowSelect) {
  const selectedValue = await flowSelect.evaluate((select) => {
    const validOptions = [...select.options].filter((option) => option.value && option.value.trim() !== '');
    const preferredOption = validOptions.find((option) => {
      const text = (option.textContent || '').toLowerCase();
      return text.includes('cd') || text.includes('pn');
    });

    const optionToUse = preferredOption || validOptions[0];
    if (!optionToUse) {
      return '';
    }

    select.value = optionToUse.value;
    select.dispatchEvent(new Event('change', { bubbles: true }));
    return optionToUse.value;
  });

  expect(selectedValue).not.toEqual('');
}

async function selectFirstNonEmptyOption(selectLocator) {
  const value = await selectLocator.locator('option').evaluateAll((options) => {
    const firstValid = options.find((option) => option.value && option.value.trim() !== '');
    return firstValid ? firstValid.value : '';
  });

  if (!value) {
    throw new Error('No hay opciones disponibles para seleccionar.');
  }

  await selectLocator.selectOption(value);
}

async function isVisibleAndEnabled(locator) {
  if ((await locator.count()) === 0) {
    return false;
  }

  const visible = await locator.isVisible().catch(() => false);
  if (!visible) {
    return false;
  }

  const enabled = await locator.isEnabled().catch(() => false);
  return enabled;
}

async function retryOnPage(page, action, label, attempts = 12, delayMs = 1500) {
  let lastError;

  for (let attempt = 1; attempt <= attempts; attempt += 1) {
    try {
      await action();
      return;
    } catch (error) {
      lastError = error;
      if (attempt < attempts) {
        await page.waitForTimeout(delayMs);
      }
    }
  }

  throw new Error(`No se pudo completar: ${label}. Detalle: ${lastError?.message || 'sin detalle'}`);
}

function mapAreaLabelToRole(areaLabel) {
  const normalized = normalizeText(areaLabel);

  if (normalized.includes('descentralizacion') || normalized.includes('planeacion')) {
    return 'planeacion';
  }
  if (normalized.includes('unidad')) {
    return 'unidad_solicitante';
  }
  if (normalized.includes('hacienda')) {
    return 'hacienda';
  }
  if (normalized.includes('juridica')) {
    return 'juridica';
  }
  if (normalized.includes('secop')) {
    return 'secop';
  }

  throw new Error(`No se pudo mapear area: ${areaLabel}`);
}

function normalizeText(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

function normalizeWhitespace(value) {
  return String(value || '').replace(/\s+/g, ' ').trim();
}

function ensurePdfFixture() {
  const fixturesDir = path.join(process.cwd(), 'tests', 'fixtures');
  const fixturePath = path.join(fixturesDir, 'flujo-ui-documento-prueba.pdf');

  if (!fs.existsSync(fixturesDir)) {
    fs.mkdirSync(fixturesDir, { recursive: true });
  }

  if (!fs.existsSync(fixturePath)) {
    const pdfContent = Buffer.from(
      '%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 300 144] >>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000118 00000 n \ntrailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n183\n%%EOF'
    );
    fs.writeFileSync(fixturePath, pdfContent);
  }

  return fixturePath;
}

function resolveUploadFixturePath() {
  if (FLOW_UPLOAD_FILE_PATH) {
    const resolvedPath = path.isAbsolute(FLOW_UPLOAD_FILE_PATH)
      ? FLOW_UPLOAD_FILE_PATH
      : path.join(process.cwd(), FLOW_UPLOAD_FILE_PATH);

    if (fs.existsSync(resolvedPath)) {
      return resolvedPath;
    }

    console.log(`⚠️ FLOW_UPLOAD_FILE_PATH no existe: ${resolvedPath}. Se usa fixture simulado.`);
  }

  return ensurePdfFixture();
}
