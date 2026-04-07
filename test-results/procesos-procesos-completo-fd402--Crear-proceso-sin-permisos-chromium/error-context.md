# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: procesos\procesos-completo.spec.js >> Gestión de Procesos Contractuales >> PROC-009: Crear proceso sin permisos
- Location: tests\procesos\procesos-completo.spec.js:133:3

# Error details

```
TimeoutError: page.fill: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('input[name="email"]')

```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - generic [ref=e2]:
    - complementary [ref=e3]:
      - generic [ref=e4]:
        - img "Escudo" [ref=e6]
        - generic [ref=e7]:
          - paragraph [ref=e8]: Gobernación
          - paragraph [ref=e9]: Caldas
      - generic [ref=e10]:
        - generic [ref=e11]: JE
        - generic [ref=e12]:
          - paragraph [ref=e13]: Jefe Unidad Sistemas
          - paragraph [ref=e14]: jefe.sistemas@demo.com
      - navigation [ref=e15]:
        - link "Panel principal" [ref=e16] [cursor=pointer]:
          - /url: http://localhost:8000/panel-principal
          - img [ref=e17]
          - text: Panel principal
        - paragraph [ref=e19]: Mi Área
        - link "Mi bandeja" [ref=e20] [cursor=pointer]:
          - /url: http://localhost:8000/unidad
          - img [ref=e21]
          - text: Mi bandeja
        - link "Nueva solicitud" [ref=e23] [cursor=pointer]:
          - /url: http://localhost:8000/procesos/crear
          - img [ref=e24]
          - text: Nueva solicitud
        - link "Consulta SECOP II" [ref=e26] [cursor=pointer]:
          - /url: http://localhost:8000/secop-consulta
          - img [ref=e27]
          - text: Consulta SECOP II
        - link "Notificaciones" [ref=e29] [cursor=pointer]:
          - /url: http://localhost:8000/alertas
          - img [ref=e30]
          - text: Notificaciones
      - button "Cerrar sesión" [ref=e34] [cursor=pointer]:
        - img [ref=e35]
        - text: Cerrar sesión
    - generic [ref=e37]:
      - banner [ref=e38]:
        - generic [ref=e42]:
          - heading "Bienvenido, Jefe 👋" [level=1] [ref=e43]
          - paragraph [ref=e44]: Unidad Solicitante — Gobernación de Caldas
        - generic [ref=e45]:
          - link [ref=e46] [cursor=pointer]:
            - /url: http://localhost:8000/alertas
            - img [ref=e47]
          - generic [ref=e49]:
            - generic [ref=e50]: JE
            - generic [ref=e51]: Jefe
      - main [ref=e52]:
        - generic [ref=e53]:
          - generic [ref=e54]:
            - heading "Resumen — April 2026" [level=2] [ref=e56]
            - generic [ref=e57]:
              - generic [ref=e58]:
                - generic [ref=e59]: 📋
                - generic [ref=e60]:
                  - paragraph [ref=e61]: "0"
                  - paragraph [ref=e62]: Solicitudes creadas este mes
              - generic [ref=e63]:
                - generic [ref=e64]: 🔄
                - generic [ref=e65]:
                  - paragraph [ref=e66]: "0"
                  - paragraph [ref=e67]: Procesos activos actualmente
              - generic [ref=e68]:
                - generic [ref=e69]: ✅
                - generic [ref=e70]:
                  - paragraph [ref=e71]: "0"
                  - paragraph [ref=e72]: Finalizados este mes
              - generic [ref=e73]:
                - generic [ref=e74]: ❌
                - generic [ref=e75]:
                  - paragraph [ref=e76]: "0"
                  - paragraph [ref=e77]: Rechazados este mes
          - generic [ref=e78]:
            - link "Mi bandeja Unidad" [ref=e79] [cursor=pointer]:
              - /url: http://localhost:8000/unidad
              - img [ref=e81]
              - generic [ref=e83]:
                - paragraph [ref=e84]: Mi bandeja
                - paragraph [ref=e85]: Unidad
            - link "Nueva solicitud CD-PN" [ref=e86] [cursor=pointer]:
              - /url: http://localhost:8000/procesos/crear
              - img [ref=e88]
              - generic [ref=e90]:
                - paragraph [ref=e91]: Nueva solicitud
                - paragraph [ref=e92]: CD-PN
            - link "Ver procesos Lista completa" [ref=e93] [cursor=pointer]:
              - /url: http://localhost:8000/procesos
              - img [ref=e95]
              - generic [ref=e97]:
                - paragraph [ref=e98]: Ver procesos
                - paragraph [ref=e99]: Lista completa
          - generic [ref=e100]:
            - generic [ref=e101]:
              - heading "Procesos en curso" [level=2] [ref=e102]
              - link "Ver todos →" [ref=e103] [cursor=pointer]:
                - /url: http://localhost:8000/procesos
            - generic [ref=e104]:
              - img [ref=e105]
              - paragraph [ref=e107]: No tienes procesos en curso actualmente
              - link "+ Crear primera solicitud" [ref=e108] [cursor=pointer]:
                - /url: http://localhost:8000/procesos/crear
  - button "Marsetiv bot" [ref=e110] [cursor=pointer]:
    - img [ref=e112]
```

# Test source

```ts
  1  | // tests/helpers/login.helper.js
  2  | export class LoginHelper {
  3  |   constructor(page) {
  4  |     this.page = page;
  5  |   }
  6  | 
  7  |   async loginAs(email, password = '12345') {
  8  |     await this.page.goto('/login');
  9  |     await this.page.waitForLoadState('domcontentloaded');
> 10 |     await this.page.fill('input[name="email"]', email);
     |                     ^ TimeoutError: page.fill: Timeout 10000ms exceeded.
  11 |     await this.page.fill('input[name="password"]', password);
  12 |     await this.page.click('button[type="submit"]');
  13 |     
  14 |     try {
  15 |       await this.page.waitForURL('**/panel-principal**', { timeout: 15000 });
  16 |     } catch (e) {
  17 |       console.log('⚠️ No redirigió a panel-principal - verificar credenciales');
  18 |     }
  19 |   }
  20 | 
  21 |   // ============ USUARIOS REALES DEL SISTEMA ============
  22 |   
  23 |   async loginAsAdmin() {
  24 |     await this.loginAs('admin@demo.com', '12345678'); // ⭐ Admin tiene password diferente
  25 |   }
  26 | 
  27 |   async loginAsUnidad() {
  28 |     await this.loginAs('jefe.sistemas@demo.com', '12345');
  29 |   }
  30 | 
  31 |   async loginAsPlaneacion() {
  32 |     await this.loginAs('planeacion@demo.com', '12345');
  33 |   }
  34 | 
  35 |   async loginAsDescentralizacion() {
  36 |     await this.loginAs('descentralizacion@demo.com', '12345');
  37 |   }
  38 | 
  39 |   async loginAsHacienda() {
  40 |     await this.loginAs('hacienda@demo.com', '12345');
  41 |   }
  42 | 
  43 |   async loginAsJuridica() {
  44 |     await this.loginAs('juridica@demo.com', '12345');
  45 |   }
  46 | 
  47 |   async loginAsSECOP() {
  48 |     await this.loginAs('secop@demo.com', '12345');
  49 |   }
  50 | 
  51 |   async loginAsAbogado() {
  52 |     await this.loginAs('abogado.sistemas@demo.com', '12345');
  53 |   }
  54 | 
  55 |   async loginAsSecretarioPlaneacion() {
  56 |     await this.loginAs('secretario.planeacion@demo.com', '12345');
  57 |   }
  58 | 
  59 |   async loginAsRadicacion() {
  60 |     await this.loginAs('radicacion@demo.com', '12345');
  61 |   }
  62 | 
  63 |   async loginAsConsulta() {
  64 |     // Usuario sin permisos especiales
  65 |     await this.loginAs('sistemas@demo.com', '12345');
  66 |   }
  67 | 
  68 |   async logout() {
  69 |     const userMenu = this.page.locator('[data-cy="user-menu"], button:has-text("Cerrar"), .user-menu, #user-dropdown').first();
  70 |     
  71 |     if (await userMenu.isVisible({ timeout: 5000 })) {
  72 |       await userMenu.click();
  73 |       await this.page.click('text=/cerrar.*sesión|logout|salir/i');
  74 |     } else {
  75 |       await this.page.goto('/logout');
  76 |     }
  77 |     
  78 |     await this.page.waitForURL('**/login**', { timeout: 10000 });
  79 |   }
  80 | }
  81 | 
```