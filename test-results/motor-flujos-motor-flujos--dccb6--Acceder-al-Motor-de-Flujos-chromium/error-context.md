# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: motor-flujos\motor-flujos.spec.js >> Motor de Flujos - Tests Funcionales >> MOTOR-001: Acceder al Motor de Flujos
- Location: tests\motor-flujos\motor-flujos.spec.js:17:3

# Error details

```
TypeError: Cannot read properties of undefined (reading 'goto')
```

# Test source

```ts
  1  | // tests/helpers/login.helper.js
  2  | export class LoginHelper {
  3  |   
  4  |   async loginAs(page, email, password = '12345') {
> 5  |     await page.goto('/login');
     |                ^ TypeError: Cannot read properties of undefined (reading 'goto')
  6  |     await page.waitForLoadState('domcontentloaded');
  7  |     
  8  |     // Llenar credenciales
  9  |     await page.fill('input[name="email"]', email);
  10 |     await page.fill('input[name="password"]', password);
  11 |     await page.click('button[type="submit"]');
  12 |     
  13 |     // Esperar a que se complete el login (cualquier ruta que no sea login)
  14 |     await page.waitForTimeout(2000);
  15 |     
  16 |     // Verificar que no estamos en login (éxito del login)
  17 |     const currentUrl = page.url();
  18 |     if (currentUrl.includes('/login')) {
  19 |       console.log(`⚠️ Login falló para ${email} - aún en página de login`);
  20 |     } else {
  21 |       console.log(`✅ Login exitoso para ${email} -> ${currentUrl}`);
  22 |     }
  23 |   }
  24 | 
  25 |   // ============ USUARIOS REALES DEL SISTEMA ============
  26 |   
  27 |   async loginAsAdmin(page) {
  28 |     await this.loginAs(page, 'admin@demo.com', '12345678'); // ⭐ Admin tiene password diferente
  29 |   }
  30 | 
  31 |   async loginAsUnidad(page) {
  32 |     await this.loginAs(page, 'jefe.sistemas@demo.com', '12345');
  33 |   }
  34 | 
  35 |   async loginAsPlaneacion(page) {
  36 |     await this.loginAs(page, 'planeacion@demo.com', '12345');
  37 |   }
  38 | 
  39 |   async loginAsDescentralizacion(page) {
  40 |     await this.loginAs(page, 'descentralizacion@demo.com', '12345');
  41 |   }
  42 | 
  43 |   async loginAsHacienda(page) {
  44 |     await this.loginAs(page, 'hacienda@demo.com', '12345');
  45 |   }
  46 | 
  47 |   async loginAsJuridica(page) {
  48 |     await this.loginAs(page, 'juridica@demo.com', '12345');
  49 |   }
  50 | 
  51 |   async loginAsSECOP(page) {
  52 |     await this.loginAs(page, 'secop@demo.com', '12345');
  53 |   }
  54 | 
  55 |   async loginAsAbogado(page) {
  56 |     await this.loginAs(page, 'abogado.sistemas@demo.com', '12345');
  57 |   }
  58 | 
  59 |   async loginAsSecretarioPlaneacion(page) {
  60 |     await this.loginAs(page, 'secretario.planeacion@demo.com', '12345');
  61 |   }
  62 | 
  63 |   async loginAsRadicacion(page) {
  64 |     await this.loginAs(page, 'radicacion@demo.com', '12345');
  65 |   }
  66 | 
  67 |   async loginAsConsulta(page) {
  68 |     // Usuario sin permisos especiales
  69 |     await this.loginAs(page, 'sistemas@demo.com', '12345');
  70 |   }
  71 | 
  72 |   async logout(page) {
  73 |     const userMenu = page.locator('[data-cy="user-menu"], button:has-text("Cerrar"), .user-menu, #user-dropdown').first();
  74 |     
  75 |     if (await userMenu.isVisible({ timeout: 5000 })) {
  76 |       await userMenu.click();
  77 |       await page.click('text=/cerrar.*sesión|logout|salir/i');
  78 |     } else {
  79 |       await page.goto('/logout');
  80 |     }
  81 |     
  82 |     await page.waitForURL('**/login**', { timeout: 10000 });
  83 |   }
  84 | }
  85 | 
```