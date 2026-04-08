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
    const isLoggedIn = !currentUrl.includes('/login');

    if (!isLoggedIn) {
      console.log(`⚠️ Login falló para ${email} - aún en página de login`);
    } else {
      console.log(`✅ Login exitoso para ${email} -> ${currentUrl}`);
    }

    return isLoggedIn;
  }

  normalizeCandidates(values = []) {
    return [...new Set(
      values
        .map((value) => (typeof value === 'string' ? value.trim() : ''))
        .filter((value) => value.length > 0)
    )];
  }

  async loginAsWithFallback(email, passwords = [], page = null) {
    const resolvedPage = this.resolvePage(page);
    const passwordCandidates = this.normalizeCandidates(passwords);

    for (const candidatePassword of passwordCandidates) {
      await resolvedPage.context().clearCookies();
      const loggedIn = await this.loginAs(email, candidatePassword, resolvedPage);

      if (loggedIn) {
        return { email, password: candidatePassword };
      }
    }

    throw new Error(
      `No se pudo iniciar sesión para ${email} con ${passwordCandidates.length} contraseña(s) candidata(s).`
    );
  }

  async loginAsAny(emails = [], passwords = [], page = null) {
    const resolvedPage = this.resolvePage(page);
    const emailCandidates = this.normalizeCandidates(emails);
    const passwordCandidates = this.normalizeCandidates(passwords);

    for (const candidateEmail of emailCandidates) {
      for (const candidatePassword of passwordCandidates) {
        await resolvedPage.context().clearCookies();
        const loggedIn = await this.loginAs(candidateEmail, candidatePassword, resolvedPage);

        if (loggedIn) {
          return { email: candidateEmail, password: candidatePassword };
        }
      }
    }

    throw new Error(
      `No se pudo iniciar sesión con ${emailCandidates.length} email(s) y ${passwordCandidates.length} contraseña(s).`
    );
  }

  // ============ USUARIOS REALES DEL SISTEMA ============

  async loginAsAdmin(page = null) {
    await this.loginAsWithFallback('admin@demo.com', [
      process.env.ADMIN_PASSWORD,
      '12345678',
      '12345',
    ], page); // ⭐ Admin suele tener password diferente
  }

  async loginAsUnidad(page = null) {
    await this.loginAsWithFallback('jefe.sistemas@demo.com', [
      process.env.UNIDAD_PASSWORD,
      '12345',
      '12345678',
    ], page);
  }

  async loginAsPlaneacion(page = null) {
    await this.loginAsWithFallback('planeacion@demo.com', [
      process.env.PLANEACION_PASSWORD,
      '12345',
      '12345678',
    ], page);
  }

  async loginAsDescentralizacion(page = null) {
    await this.loginAsWithFallback('descentralizacion@demo.com', [
      process.env.DESCENTRALIZACION_PASSWORD,
      '12345',
      '12345678',
    ], page);
  }

  async loginAsHacienda(page = null) {
    await this.loginAsWithFallback('hacienda@demo.com', [
      process.env.HACIENDA_PASSWORD,
      '12345',
      '12345678',
    ], page);
  }

  async loginAsJuridica(page = null) {
    await this.loginAsWithFallback('juridica@demo.com', [
      process.env.JURIDICA_PASSWORD,
      '12345',
      '12345678',
    ], page);
  }

  async loginAsSECOP(page = null) {
    await this.loginAsWithFallback('secop@demo.com', [
      process.env.SECOP_PASSWORD,
      '12345',
      '12345678',
    ], page);
  }

  async loginAsAbogado(page = null) {
    await this.loginAsWithFallback('abogado.sistemas@demo.com', [
      process.env.ABOGADO_PASSWORD,
      '12345',
      '12345678',
    ], page);
  }

  async loginAsSecretarioPlaneacion(page = null) {
    await this.loginAsWithFallback('secretario.planeacion@demo.com', [
      process.env.SECRETARIO_PLANEACION_PASSWORD,
      process.env.SECRETARIO_PASSWORD,
      '12345',
      '12345678',
      'TestingPassword123!',
    ], page);
  }

  async loginAsSecretario(page = null) {
    await this.loginAsAny([
      process.env.SECRETARIO_EMAIL,
      'secretario.planeacion@demo.com',
      'secretario.planeacion@gobernacion-caldas.gov.co',
      'secretario.hacienda@gobernacion-caldas.gov.co',
    ], [
      process.env.SECRETARIO_PASSWORD,
      '12345',
      '12345678',
      'TestingPassword123!',
    ], page);
  }

  async loginAsGobernador(page = null) {
    await this.loginAsAny([
      process.env.GOBERNADOR_EMAIL,
      'gobernador@gobernacion-caldas.gov.co',
      'gobernador@demo.com',
    ], [
      process.env.GOBERNADOR_PASSWORD,
      '12345',
      '12345678',
      'TestingPassword123!',
    ], page);
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
