import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

let authEvidenceCounter = 0;
const RESET_USUARIO_EMAIL = process.env.RESET_USUARIO_EMAIL || 'jefe.sistemas@demo.com';
const RESET_CORREO_DESTINO = process.env.RESET_CORREO_DESTINO || 'marianasuarezj33@gmail.com';
const AUTH_INVALID_PASSWORD = process.env.AUTH_INVALID_PASSWORD || 'ClaveIncorrecta#2026';
const VIDEO_STEP_PAUSE_MS = Number(process.env.VIDEO_STEP_PAUSE_MS || 900);
const AUTH_MANUAL_HOLD_MS = Number(process.env.AUTH_MANUAL_HOLD_MS || 900000);

test.describe.configure({ mode: 'serial' });

test.describe('Casos de Autenticacion UI', () => {
  test('AUTH-000-Video completo login error y recuperacion', async ({ page }) => {
    // Este caso deja tiempo de evidencia manual después de enviar el enlace.
    test.setTimeout(Math.max(180000, AUTH_MANUAL_HOLD_MS + 180000));

    await navigateForAuth(page, '/login');
    await expect(page).toHaveURL(/\/login/);
    await saveAuthEvidence(page, 'AUTH-000', '01-login-cargado');
    await pauseForVideo(page);

    await page.fill('input[name="email"]', RESET_USUARIO_EMAIL);
    await page.fill('input[name="password"]', AUTH_INVALID_PASSWORD);
    await saveAuthEvidence(page, 'AUTH-000', '02-login-invalido-antes-enviar');

    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.click('button[type="submit"]'),
    ]);

    await expect(page).toHaveURL(/\/login/);
    await saveAuthEvidence(page, 'AUTH-000', '03-login-invalido-resultado');
    await pauseForVideo(page);

    const forgotLink = page
      .locator('a[href*="forgot-password"], a:has-text("Olvid"), a:has-text("contraseña")')
      .first();

    if (await forgotLink.count() && await forgotLink.isVisible()) {
      await Promise.all([
        page.waitForLoadState('networkidle'),
        forgotLink.click(),
      ]);
    } else {
      await navigateForAuth(page, '/forgot-password');
    }

    await expect(page).toHaveURL(/\/forgot-password/);
    await saveAuthEvidence(page, 'AUTH-000', '04-form-recuperacion-cargado');
    await pauseForVideo(page);

    await page.fill('input[name="usuario_email"]', RESET_USUARIO_EMAIL);
    await page.fill('input[name="correo_destino"]', RESET_CORREO_DESTINO);
    await saveAuthEvidence(page, 'AUTH-000', '05-recuperacion-datos-completos');

    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded').catch(() => {});
    await page.waitForTimeout(VIDEO_STEP_PAUSE_MS);

    const bodyText = ((await page.locator('body').textContent()) || '').toLowerCase();
    const envioDetectado = /enlace enviado|revisa el correo|correo enviado|solicitud enviada|hemos enviado/.test(bodyText);

    if (!envioDetectado) {
      console.log('⚠️ AUTH-000: no se detectó mensaje explícito de envío, se mantiene pausa manual para validar en UI.');
    }

    await saveAuthEvidence(page, 'AUTH-000', '06-recuperacion-enviada');
    await pauseForVideo(page);

    await holdForManualEmailCheck(page, {
      message: `AUTH-000 listo para evidencia manual por ${AUTH_MANUAL_HOLD_MS} ms después de enviar enlace.`,
    });
  });

  test('AUTH-001-Pagina de Login', async ({ page }) => {
    await navigateForAuth(page, '/login');

    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();

    await saveAuthEvidence(page, 'AUTH-001', 'pagina-login-cargada');
  });

  test('AUTH-002-Login con usuario no existente', async ({ page }) => {
    await navigateForAuth(page, '/login');

    await page.fill('input[name="email"]', 'no.existe.usuario@demo.com');
    await page.fill('input[name="password"]', 'clave-invalida');
    await saveAuthEvidence(page, 'AUTH-002', 'antes-intento-login-invalido');

    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.click('button[type="submit"]'),
    ]);

    await expect(page).toHaveURL(/\/login/);

    const hasErrorMessage = await page
      .locator('[role="alert"], .text-red-500, .text-red-600, .text-red-700')
      .first()
      .isVisible()
      .catch(() => false);

    expect(hasErrorMessage || page.url().includes('/login')).toBeTruthy();
    await saveAuthEvidence(page, 'AUTH-002', 'resultado-login-invalido');
  });

  test('AUTH-003-Pagina de Recuperacion de Contrasena', async ({ page }) => {
    await navigateForAuth(page, '/forgot-password');

    await expect(page).toHaveURL(/\/forgot-password/);
    await expect(page.locator('input[name="usuario_email"]')).toBeVisible();
    await expect(page.locator('input[name="correo_destino"]')).toBeVisible();

    await saveAuthEvidence(page, 'AUTH-003', 'pagina-recuperacion-cargada');
  });

  test('AUTH-004-Solicitar enlace de recuperacion', async ({ page }) => {
    await navigateForAuth(page, '/forgot-password');

    await page.fill('input[name="usuario_email"]', RESET_USUARIO_EMAIL);
    await page.fill('input[name="correo_destino"]', RESET_CORREO_DESTINO);
    await saveAuthEvidence(page, 'AUTH-004', 'antes-enviar-solicitud-recuperacion');

    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.click('button[type="submit"]'),
    ]);

    await expect(page.locator('body')).toContainText(/enlace enviado|revisa el correo/i);
    await saveAuthEvidence(page, 'AUTH-004', 'solicitud-recuperacion-enviada');
  });
});

async function saveAuthEvidence(page, caseId, label) {
  authEvidenceCounter += 1;

  const evidenceDir = path.join(process.cwd(), 'test-results', 'evidencias-auth');
  if (!fs.existsSync(evidenceDir)) {
    fs.mkdirSync(evidenceDir, { recursive: true });
  }

  const safeCase = sanitizeForPath(caseId);
  const safeLabel = sanitizeForPath(label);
  const fileName = `${String(authEvidenceCounter).padStart(4, '0')}-${safeCase}-${safeLabel}.png`;
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

async function pauseForVideo(page) {
  await page.waitForTimeout(VIDEO_STEP_PAUSE_MS);
}

async function holdForManualEmailCheck(page, options = {}) {
  if (AUTH_MANUAL_HOLD_MS <= 0) {
    return;
  }

  const startLabel = options.startLabel || '07-inicio-espera-manual-correo';
  const endLabel = options.endLabel || '08-fin-espera-manual-correo';
  const message = options.message || `AUTH-000 manual hold activo por ${AUTH_MANUAL_HOLD_MS} ms para revisar correo.`;

  console.log(message);
  await saveAuthEvidence(page, 'AUTH-000', startLabel);
  await page.waitForTimeout(AUTH_MANUAL_HOLD_MS);
  await saveAuthEvidence(page, 'AUTH-000', endLabel);
}

async function navigateForAuth(page, url) {
  await page.goto(url, { waitUntil: 'domcontentloaded' });
}
