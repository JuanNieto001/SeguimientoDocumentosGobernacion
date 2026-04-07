// tests/helpers/login.helper.js
export class LoginHelper {
  constructor(page) {
    this.page = page;
  }

  async loginAs(email, password = '12345') {
    await this.page.goto('/login');
    await this.page.waitForLoadState('domcontentloaded');
    await this.page.fill('input[name="email"]', email);
    await this.page.fill('input[name="password"]', password);
    await this.page.click('button[type="submit"]');
    
    try {
      await this.page.waitForURL('**/panel-principal**', { timeout: 15000 });
    } catch (e) {
      console.log('⚠️ No redirigió a panel-principal - verificar credenciales');
    }
  }

  // ============ USUARIOS REALES DEL SISTEMA ============
  
  async loginAsAdmin() {
    await this.loginAs('admin@demo.com', '12345678'); // ⭐ Admin tiene password diferente
  }

  async loginAsUnidad() {
    await this.loginAs('jefe.sistemas@demo.com', '12345');
  }

  async loginAsPlaneacion() {
    await this.loginAs('planeacion@demo.com', '12345');
  }

  async loginAsDescentralizacion() {
    await this.loginAs('descentralizacion@demo.com', '12345');
  }

  async loginAsHacienda() {
    await this.loginAs('hacienda@demo.com', '12345');
  }

  async loginAsJuridica() {
    await this.loginAs('juridica@demo.com', '12345');
  }

  async loginAsSECOP() {
    await this.loginAs('secop@demo.com', '12345');
  }

  async loginAsAbogado() {
    await this.loginAs('abogado.sistemas@demo.com', '12345');
  }

  async loginAsSecretarioPlaneacion() {
    await this.loginAs('secretario.planeacion@demo.com', '12345');
  }

  async loginAsRadicacion() {
    await this.loginAs('radicacion@demo.com', '12345');
  }

  async loginAsConsulta() {
    // Usuario sin permisos especiales
    await this.loginAs('sistemas@demo.com', '12345');
  }

  async logout() {
    const userMenu = this.page.locator('[data-cy="user-menu"], button:has-text("Cerrar"), .user-menu, #user-dropdown').first();
    
    if (await userMenu.isVisible({ timeout: 5000 })) {
      await userMenu.click();
      await this.page.click('text=/cerrar.*sesión|logout|salir/i');
    } else {
      await this.page.goto('/logout');
    }
    
    await this.page.waitForURL('**/login**', { timeout: 10000 });
  }
}
