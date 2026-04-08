// tests/helpers/login.helper.js
export class LoginHelper {
  
  async loginAs(page, email, password = '12345') {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    
    // Llenar credenciales
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', password);
    await page.click('button[type="submit"]');
    
    // Esperar a que se complete el login (cualquier ruta que no sea login)
    await page.waitForTimeout(2000);
    
    // Verificar que no estamos en login (éxito del login)
    const currentUrl = page.url();
    if (currentUrl.includes('/login')) {
      console.log(`⚠️ Login falló para ${email} - aún en página de login`);
    } else {
      console.log(`✅ Login exitoso para ${email} -> ${currentUrl}`);
    }
  }

  // ============ USUARIOS REALES DEL SISTEMA ============
  
  async loginAsAdmin(page) {
    await this.loginAs(page, 'admin@demo.com', '12345678'); // ⭐ Admin tiene password diferente
  }

  async loginAsUnidad(page) {
    await this.loginAs(page, 'jefe.sistemas@demo.com', '12345');
  }

  async loginAsPlaneacion(page) {
    await this.loginAs(page, 'planeacion@demo.com', '12345');
  }

  async loginAsDescentralizacion(page) {
    await this.loginAs(page, 'descentralizacion@demo.com', '12345');
  }

  async loginAsHacienda(page) {
    await this.loginAs(page, 'hacienda@demo.com', '12345');
  }

  async loginAsJuridica(page) {
    await this.loginAs(page, 'juridica@demo.com', '12345');
  }

  async loginAsSECOP(page) {
    await this.loginAs(page, 'secop@demo.com', '12345');
  }

  async loginAsAbogado(page) {
    await this.loginAs(page, 'abogado.sistemas@demo.com', '12345');
  }

  async loginAsSecretarioPlaneacion(page) {
    await this.loginAs(page, 'secretario.planeacion@demo.com', '12345');
  }

  async loginAsRadicacion(page) {
    await this.loginAs(page, 'radicacion@demo.com', '12345');
  }

  async loginAsConsulta(page) {
    // Usuario sin permisos especiales
    await this.loginAs(page, 'sistemas@demo.com', '12345');
  }

  async logout(page) {
    const userMenu = page.locator('[data-cy="user-menu"], button:has-text("Cerrar"), .user-menu, #user-dropdown').first();
    
    if (await userMenu.isVisible({ timeout: 5000 })) {
      await userMenu.click();
      await page.click('text=/cerrar.*sesión|logout|salir/i');
    } else {
      await page.goto('/logout');
    }
    
    await page.waitForURL('**/login**', { timeout: 10000 });
  }
}
