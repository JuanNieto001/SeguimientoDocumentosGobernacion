import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

const usuariosFlujo = [
  {
    nombre: 'Unidad solicitante',
    login: async (login) => login.loginAsUnidad(),
    rutaPrincipal: '/unidad',
  },
  {
    nombre: 'Planeacion',
    login: async (login) => login.loginAsPlaneacion(),
    rutaPrincipal: '/planeacion',
  },
  {
    nombre: 'Hacienda',
    login: async (login) => login.loginAsHacienda(),
    rutaPrincipal: '/hacienda',
  },
  {
    nombre: 'Juridica',
    login: async (login) => login.loginAsJuridica(),
    rutaPrincipal: '/juridica',
  },
  {
    nombre: 'SECOP',
    login: async (login) => login.loginAsSECOP(),
    rutaPrincipal: '/secop',
  },
];

const erroresCriticos = /Whoops|Server Error|SQLSTATE|Class\s+.*\s+not found|View\s+\[.*\]\s+not found|419\s*\|\s*Page Expired|CSRF token mismatch/i;

async function validarCargaSinErrores(page) {
  await page.waitForLoadState('domcontentloaded');
  await expect(page.locator('body')).toBeVisible();
  await expect(page.locator('body')).not.toContainText(erroresCriticos);

  const texto = await page.locator('body').innerText();
  expect(texto.trim().length).toBeGreaterThan(80);
}

test.describe('Smoke de usuarios principales del flujo', () => {
  for (const usuario of usuariosFlujo) {
    test(`USU-FLUJO: ${usuario.nombre} inicia sesion y carga vistas clave`, async ({ page }) => {
      const login = new LoginHelper(page);

      await usuario.login(login);
      expect(page.url()).not.toContain('/login');

      const respuestaPrincipal = await page.goto(usuario.rutaPrincipal, { waitUntil: 'domcontentloaded' });
      expect(respuestaPrincipal).not.toBeNull();
      expect(respuestaPrincipal.status()).toBeLessThan(500);
      await validarCargaSinErrores(page);

      const respuestaProcesos = await page.goto('/procesos', { waitUntil: 'domcontentloaded' });
      expect(respuestaProcesos).not.toBeNull();
      expect(respuestaProcesos.status()).toBeLessThan(500);
      await validarCargaSinErrores(page);
    });
  }
});
