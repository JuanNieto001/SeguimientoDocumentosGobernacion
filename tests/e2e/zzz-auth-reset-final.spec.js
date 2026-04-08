import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

let finalEvidenceCounter = 0;

const RESET_USUARIO_EMAIL = process.env.RESET_USUARIO_EMAIL || 'jefe.sistemas@demo.com';
const RESET_LINK = process.env.RESET_LINK || '';
const NEW_PASSWORD_FINAL = process.env.NEW_PASSWORD_FINAL || 'ClaveNueva#2026';

test.describe.configure({ mode: 'serial' });

test.describe('Reset final de contraseña (ejecutar al final)', () => {
  test('AUTH-099-Restablecer contrasena desde enlace del correo', async ({ page }) => {
    test.skip(!RESET_LINK, 'Falta RESET_LINK. Copia el enlace del correo y ejecútalo con esa variable.');

    await page.goto(RESET_LINK);
    await expect(page).toHaveURL(/reset-password\//);

    const emailInput = page.locator('input[name="email"]');
    await expect(emailInput).toBeVisible();

    const currentEmailValue = await emailInput.inputValue();
    if (!currentEmailValue || currentEmailValue.trim() === '') {
      await emailInput.fill(RESET_USUARIO_EMAIL);
    }

    await page.fill('input[name="password"]', NEW_PASSWORD_FINAL);
    await page.fill('input[name="password_confirmation"]', NEW_PASSWORD_FINAL);
    await saveFinalEvidence(page, 'AUTH-099', 'antes-enviar-reset-final');

    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.click('button[type="submit"]'),
    ]);

    await expect(page).toHaveURL(/\/login/);
    await saveFinalEvidence(page, 'AUTH-099', 'despues-reset-redireccion-login');

    await page.fill('input[name="email"]', RESET_USUARIO_EMAIL);
    await page.fill('input[name="password"]', NEW_PASSWORD_FINAL);

    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.click('button[type="submit"]'),
    ]);

    await expect(page).not.toHaveURL(/\/login/);
    await saveFinalEvidence(page, 'AUTH-099', 'login-exitoso-con-nueva-clave');
  });
});

async function saveFinalEvidence(page, caseId, label) {
  finalEvidenceCounter += 1;

  const evidenceDir = path.join(process.cwd(), 'test-results', 'evidencias-auth-final');
  if (!fs.existsSync(evidenceDir)) {
    fs.mkdirSync(evidenceDir, { recursive: true });
  }

  const safeCase = sanitizeForPath(caseId);
  const safeLabel = sanitizeForPath(label);
  const fileName = `${String(finalEvidenceCounter).padStart(4, '0')}-${safeCase}-${safeLabel}.png`;
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
