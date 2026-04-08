// tests/helpers/login.helper.js
export class LoginHelper {
  constructor(page = null) {
    this.page = page;
  }

  resolvePage(page) {
    const resolvedPage = page || this.page;

    if (!resolvedPage) {
      throw new Error(
        'LoginHelper requiere una instancia de page. Usa new LoginHelper(page) o pasa page al método.'
      );
    }

    return resolvedPage;
  }

  async loginAs(email, password = '12345', page = null) {
    const resolvedPage = this.resolvePage(page);

    await resolvedPage.goto('/login');
    await resolvedPage.waitForLoadState('domcontentloaded');

    // Llenar credenciales
    await resolvedPage.fill('input[name="email"]', email);
    await resolvedPage.fill('input[name="password"]', password);
    await resolvedPage.click('button[type="submit"]');

    // Esperar a que se complete el login (cualquier ruta que no sea login)
    await resolvedPage.waitForTimeout(2000);

    // Verificar que no estamos en login (éxito del login)
    const currentUrl = resolvedPage.url();
    if (currentUrl.includes('/login')) {
      console.log(`⚠️ Login falló para ${email} - aún en página de login`);
    } else {
      console.log(`✅ Login exitoso para ${email} -> ${currentUrl}`);
    }
  }

  // ============ USUARIOS REALES DEL SISTEMA ============

  async loginAsAdmin(page = null) {
    await this.loginAs('admin@demo.com', '12345678', page); // ⭐ Admin tiene password diferente
  }

  async loginAsUnidad(page = null) {
    await this.loginAs('jefe.sistemas@demo.com', '12345', page);
  }

  async loginAsPlaneacion(page = null) {
    await this.loginAs('planeacion@demo.com', '12345', page);
  }

  async loginAsDescentralizacion(page = null) {
    await this.loginAs('descentralizacion@demo.com', '12345', page);
  }

  async loginAsHacienda(page = null) {
    await this.loginAs('hacienda@demo.com', '12345', page);
  }

  async loginAsJuridica(page = null) {
    await this.loginAs('juridica@demo.com', '12345', page);
  }

  async loginAsSECOP(page = null) {
    await this.loginAs('secop@demo.com', '12345', page);
  }

  async loginAsAbogado(page = null) {
    await this.loginAs('abogado.sistemas@demo.com', '12345', page);
  }

  async loginAsSecretarioPlaneacion(page = null) {
    await this.loginAs('secretario.planeacion@demo.com', '12345', page);
  }

  async loginAsRadicacion(page = null) {
    await this.loginAs('radicacion@demo.com', '12345', page);
  }

  async loginAsConsulta(page = null) {
    // Usuario sin permisos especiales
    await this.loginAs('sistemas@demo.com', '12345', page);
  }

  async logout(page = null) {
    const resolvedPage = this.resolvePage(page);
    const userMenu = resolvedPage
      .locator('[data-cy="user-menu"], button:has-text("Cerrar"), .user-menu, #user-dropdown')
      .first();

    if (await userMenu.isVisible({ timeout: 5000 })) {
      await userMenu.click();
      await resolvedPage.click('text=/cerrar.*sesión|logout|salir/i');
    } else {
      await resolvedPage.goto('/logout');
    }

    await resolvedPage.waitForURL('**/login**', { timeout: 10000 });
  }
}
