# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: responsive\responsive.spec.js >> Responsive Design >> RESP-002: Dashboard responsive
- Location: tests\responsive\responsive.spec.js:30:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('h1, h2')
Expected: visible
Error: strict mode violation: locator('h1, h2') resolved to 6 elements:
    1) <h1 class="text-lg font-black text-gray-900 leading-none">Panel de Control</h1> aka getByRole('heading', { name: 'Panel de Control' })
    2) <h2 class="text-sm font-black text-gray-800">Procesos por mes</h2> aka getByRole('heading', { name: 'Procesos por mes' })
    3) <h2 class="text-sm font-black text-gray-800">Tendencia de procesos</h2> aka getByRole('heading', { name: 'Tendencia de procesos' })
    4) <h2 class="text-sm font-black text-gray-800">Por modalidad</h2> aka getByRole('heading', { name: 'Por modalidad' })
    5) <h2 class="text-sm font-black text-gray-800">Procesos recientes</h2> aka getByRole('heading', { name: 'Procesos recientes' })
    6) <h2 class="text-sm font-black text-gray-800">Seguimiento en tiempo real</h2> aka getByRole('heading', { name: 'Seguimiento en tiempo real' })

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for locator('h1, h2')

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
        - generic [ref=e11]: AD
        - generic [ref=e12]:
          - paragraph [ref=e13]: Administrador
          - paragraph [ref=e14]: admin@demo.com
      - navigation [ref=e15]:
        - link "Panel principal" [ref=e16] [cursor=pointer]:
          - /url: http://localhost:8000/panel-principal
          - img [ref=e17]
          - text: Panel principal
        - link "Contratos de aplicaciones" [ref=e19] [cursor=pointer]:
          - /url: http://localhost:8000/contratos-aplicaciones
          - img [ref=e20]
          - text: Contratos de aplicaciones
        - paragraph [ref=e22]: Administración
        - link "Usuarios" [ref=e23] [cursor=pointer]:
          - /url: http://localhost:8000/admin/usuarios
          - img [ref=e24]
          - text: Usuarios
        - link "Roles" [ref=e26] [cursor=pointer]:
          - /url: http://localhost:8000/admin/roles
          - img [ref=e27]
          - text: Roles
        - link "Logs" [ref=e29] [cursor=pointer]:
          - /url: http://localhost:8000/admin/logs
          - img [ref=e30]
          - text: Logs
        - link "Log autenticación" [ref=e32] [cursor=pointer]:
          - /url: http://localhost:8000/admin/auth-events
          - img [ref=e33]
          - text: Log autenticación
        - link "Motor de Flujos" [ref=e35] [cursor=pointer]:
          - /url: http://localhost:8000/motor-flujos
          - img [ref=e36]
          - text: Motor de Flujos
        - link "Guías de Marsetiv" [ref=e38] [cursor=pointer]:
          - /url: http://localhost:8000/admin/estiven-guides
          - img [ref=e39]
          - text: Guías de Marsetiv
        - button "Secretarías 16" [ref=e41] [cursor=pointer]:
          - generic [ref=e42]:
            - img [ref=e43]
            - generic [ref=e45]: Secretarías
          - generic [ref=e46]:
            - generic [ref=e47]: "16"
            - img [ref=e48]
        - paragraph [ref=e50]: Procesos
        - link "Nueva solicitud" [ref=e51] [cursor=pointer]:
          - /url: http://localhost:8000/procesos/crear
          - img [ref=e52]
          - text: Nueva solicitud
        - link "Ver todos" [ref=e54] [cursor=pointer]:
          - /url: http://localhost:8000/procesos
          - img [ref=e55]
          - text: Ver todos
        - paragraph [ref=e57]: Análisis
        - link "Reportes" [ref=e58] [cursor=pointer]:
          - /url: http://localhost:8000/reportes
          - img [ref=e59]
          - text: Reportes
        - link "Consulta SECOP II" [ref=e61] [cursor=pointer]:
          - /url: http://localhost:8000/secop-consulta
          - img [ref=e62]
          - text: Consulta SECOP II
        - link "Notificaciones" [ref=e64] [cursor=pointer]:
          - /url: http://localhost:8000/alertas
          - img [ref=e65]
          - text: Notificaciones
      - button "Cerrar sesión" [ref=e68] [cursor=pointer]:
        - img [ref=e69]
        - text: Cerrar sesión
    - generic [ref=e71]:
      - banner [ref=e72]:
        - generic [ref=e73]:
          - button [ref=e74] [cursor=pointer]:
            - img [ref=e75]
          - generic [ref=e78]:
            - generic [ref=e79]:
              - generic [ref=e81]: VISTA GLOBAL
              - heading "Panel de Control" [level=1] [ref=e83]
              - paragraph [ref=e84]: Gobernación de Caldas — April 2026
            - generic [ref=e85]:
              - link "Nueva solicitud" [ref=e86] [cursor=pointer]:
                - /url: http://localhost:8000/procesos/crear
                - img [ref=e87]
                - text: Nueva solicitud
              - link "Ver todos" [ref=e89] [cursor=pointer]:
                - /url: http://localhost:8000/procesos
                - img [ref=e90]
                - text: Ver todos
        - generic [ref=e92]:
          - link [ref=e93] [cursor=pointer]:
            - /url: http://localhost:8000/alertas
            - img [ref=e94]
          - generic [ref=e97]: AD
      - main [ref=e98]:
        - generic [ref=e99]:
          - generic [ref=e100]:
            - generic [ref=e101]:
              - img [ref=e104]
              - paragraph [ref=e106]: "0"
              - paragraph [ref=e107]: Total procesos
              - paragraph [ref=e108]: Registrados
            - generic [ref=e109]:
              - img [ref=e112]
              - paragraph [ref=e114]: "0"
              - paragraph [ref=e115]: En curso
              - paragraph [ref=e116]: Procesos activos
            - generic [ref=e117]:
              - img [ref=e120]
              - paragraph [ref=e122]: "0"
              - paragraph [ref=e123]: Finalizados
              - paragraph [ref=e124]: Completados
            - generic [ref=e125]:
              - img [ref=e128]
              - paragraph [ref=e130]: "0"
              - paragraph [ref=e131]: Rechazados
              - paragraph [ref=e132]: Total histórico
            - generic [ref=e133]:
              - img [ref=e136]
              - paragraph [ref=e138]: "0"
              - paragraph [ref=e139]: Creados hoy
              - paragraph [ref=e140]: April
            - generic [ref=e141]:
              - img [ref=e144]
              - paragraph [ref=e146]: "0"
              - paragraph [ref=e147]: Alertas críticas
              - paragraph [ref=e148]: Alta prioridad
          - generic [ref=e149]:
            - generic [ref=e150]:
              - generic [ref=e151]:
                - generic [ref=e152]:
                  - paragraph [ref=e153]: Secretarías
                  - paragraph [ref=e154]: Procesos activos por dependencia
                - generic [ref=e155]: "16"
              - generic [ref=e156]:
                - generic [ref=e157]:
                  - generic [ref=e158]:
                    - paragraph [ref=e159]: Despacho del Gobernador
                    - generic [ref=e160]: "0"
                  - generic [ref=e162]: 0 activos
                - generic [ref=e163]:
                  - generic [ref=e164]:
                    - paragraph [ref=e165]: SecretarÃa General
                    - generic [ref=e166]: "0"
                  - generic [ref=e168]: 0 activos
                - generic [ref=e169]:
                  - generic [ref=e170]:
                    - paragraph [ref=e171]: Secretaría de Agricultura y Desarrollo Rural
                    - generic [ref=e172]: "0"
                  - generic [ref=e174]: 0 activos
                - generic [ref=e175]:
                  - generic [ref=e176]:
                    - paragraph [ref=e177]: Secretaría de Cultura
                    - generic [ref=e178]: "0"
                  - generic [ref=e180]: 0 activos
                - generic [ref=e181]:
                  - generic [ref=e182]:
                    - paragraph [ref=e183]: Secretaría de Deporte, Recreación y Actividad Física
                    - generic [ref=e184]: "0"
                  - generic [ref=e186]: 0 activos
                - generic [ref=e187]:
                  - generic [ref=e188]:
                    - paragraph [ref=e189]: Secretaría de Desarrollo, Empleo e Innovación
                    - generic [ref=e190]: "0"
                  - generic [ref=e192]: 0 activos
                - generic [ref=e193]:
                  - generic [ref=e194]:
                    - paragraph [ref=e195]: Secretaría de Gobierno
                    - generic [ref=e196]: "0"
                  - generic [ref=e198]: 0 activos
                - generic [ref=e199]:
                  - generic [ref=e200]:
                    - paragraph [ref=e201]: Secretaría de Hacienda
                    - generic [ref=e202]: "0"
                  - generic [ref=e204]: 0 activos
                - generic [ref=e205]:
                  - generic [ref=e206]:
                    - paragraph [ref=e207]: Secretaría de Infraestructura
                    - generic [ref=e208]: "0"
                  - generic [ref=e210]: 0 activos
                - generic [ref=e211]:
                  - generic [ref=e212]:
                    - paragraph [ref=e213]: Secretaría de Integración y Desarrollo Social
                    - generic [ref=e214]: "0"
                  - generic [ref=e216]: 0 activos
                - generic [ref=e217]:
                  - generic [ref=e218]:
                    - paragraph [ref=e219]: Secretaría de Planeación
                    - generic [ref=e220]: "0"
                  - generic [ref=e222]: 0 activos
                - generic [ref=e223]:
                  - generic [ref=e224]:
                    - paragraph [ref=e225]: Secretaría de Vivienda y Territorio
                    - generic [ref=e226]: "0"
                  - generic [ref=e228]: 0 activos
                - generic [ref=e229]:
                  - generic [ref=e230]:
                    - paragraph [ref=e231]: Secretaría del Medio Ambiente
                    - generic [ref=e232]: "0"
                  - generic [ref=e234]: 0 activos
                - generic [ref=e235]:
                  - generic [ref=e236]:
                    - paragraph [ref=e237]: Secretaría General
                    - generic [ref=e238]: "0"
                  - generic [ref=e240]: 0 activos
                - generic [ref=e241]:
                  - generic [ref=e242]:
                    - paragraph [ref=e243]: Secretaría Jurídica
                    - generic [ref=e244]: "0"
                  - generic [ref=e246]: 0 activos
                - generic [ref=e247]:
                  - generic [ref=e248]:
                    - paragraph [ref=e249]: Secretaría Privada
                    - generic [ref=e250]: "0"
                  - generic [ref=e252]: 0 activos
              - generic [ref=e254]:
                - generic [ref=e255]: Total procesos
                - generic [ref=e256]: "0"
            - generic [ref=e258]:
              - generic [ref=e259]:
                - heading "Procesos por mes" [level=2] [ref=e260]
                - paragraph [ref=e261]: Últimos 6 meses — creados, finalizados y rechazados
              - generic [ref=e262]: Tiempo real
            - generic [ref=e265]:
              - generic [ref=e266]:
                - paragraph [ref=e267]: Por área
                - paragraph [ref=e268]: Procesos EN CURSO por área actual
              - generic [ref=e269]:
                - generic [ref=e271]:
                  - link "Unidad Solicitante" [ref=e272] [cursor=pointer]:
                    - /url: /unidad
                  - generic [ref=e273]: "0"
                - generic [ref=e276]:
                  - link "Planeación" [ref=e277] [cursor=pointer]:
                    - /url: /planeacion
                  - generic [ref=e278]: "0"
                - generic [ref=e281]:
                  - link "Hacienda" [ref=e282] [cursor=pointer]:
                    - /url: /hacienda
                  - generic [ref=e283]: "0"
                - generic [ref=e286]:
                  - link "Jurídica" [ref=e287] [cursor=pointer]:
                    - /url: /juridica
                  - generic [ref=e288]: "0"
                - generic [ref=e291]:
                  - link "SECOP" [ref=e292] [cursor=pointer]:
                    - /url: /secop
                  - generic [ref=e293]: "0"
              - generic [ref=e296]:
                - generic:
                  - generic: "0"
                  - generic: activos
          - generic [ref=e298]:
            - generic [ref=e300]:
              - heading "Tendencia de procesos" [level=2] [ref=e301]
              - paragraph [ref=e302]: Evolución mensual por estado
            - generic [ref=e305]:
              - generic [ref=e306]:
                - heading "Por modalidad" [level=2] [ref=e307]
                - paragraph [ref=e308]: Tipos de contratación
              - generic [ref=e310]:
                - img [ref=e311]
                - paragraph [ref=e313]: Sin datos de modalidades
              - generic [ref=e314]:
                - paragraph [ref=e315]: Centro de alertas
                - generic [ref=e316]:
                  - generic [ref=e317]: Con retraso
                  - generic [ref=e318]: "0"
                - generic [ref=e319]:
                  - generic [ref=e320]: Docs. rechazados
                  - generic [ref=e321]: "0"
                - generic [ref=e322]:
                  - generic [ref=e323]: Sin actividad
                  - generic [ref=e324]: "0"
                - generic [ref=e325]:
                  - generic [ref=e326]: Cert. por vencer
                  - generic [ref=e327]: "0"
            - generic [ref=e328]:
              - generic [ref=e329]:
                - generic [ref=e330]:
                  - heading "Procesos recientes" [level=2] [ref=e331]
                  - paragraph [ref=e332]: Últimos registros del sistema
                - link "Ver todos →" [ref=e333] [cursor=pointer]:
                  - /url: http://localhost:8000/procesos
              - generic [ref=e335]:
                - img [ref=e336]
                - paragraph [ref=e338]: Sin procesos registrados
                - link "Crear primera solicitud" [ref=e339] [cursor=pointer]:
                  - /url: http://localhost:8000/procesos/crear
          - generic [ref=e340]:
            - generic [ref=e341]:
              - generic [ref=e342]:
                - heading "Seguimiento en tiempo real" [level=2] [ref=e343]
                - paragraph [ref=e344]: Ubicación y estado actual de cada proceso en el flujo contractual
              - generic [ref=e345]:
                - generic [ref=e346]: 0 procesos
                - link "Ver todos" [ref=e347] [cursor=pointer]:
                  - /url: http://localhost:8000/procesos
                  - text: Ver todos
                  - img [ref=e348]
            - table [ref=e351]:
              - rowgroup [ref=e352]:
                - row "No hay procesos registrados Crear primera solicitud →" [ref=e353]:
                  - cell "No hay procesos registrados Crear primera solicitud →" [ref=e354]:
                    - generic [ref=e355]:
                      - img [ref=e357]
                      - paragraph [ref=e359]: No hay procesos registrados
                      - link "Crear primera solicitud →" [ref=e360] [cursor=pointer]:
                        - /url: http://localhost:8000/procesos/crear
  - generic [ref=e361]:
    - generic [ref=e362]:
      - generic [ref=e363]:
        - generic [ref=e364]:
          - img [ref=e366]
          - generic [ref=e379]:
            - paragraph [ref=e380]: Marsetiv bot
            - paragraph [ref=e381]: En línea · Listo para ayudarte
        - button [ref=e383] [cursor=pointer]:
          - img [ref=e384]
      - generic [ref=e387]:
        - img [ref=e389]
        - generic [ref=e394]:
          - paragraph [ref=e395]:
            - text: ¡Hola,
            - strong [ref=e396]: Administrador
            - text: "! Soy"
            - strong [ref=e397]: Marsetiv bot
            - text: ", tu asistente. ¿En qué te ayudo hoy?"
          - paragraph [ref=e398]:
            - img [ref=e399]
            - text: "Tu rol:"
            - strong [ref=e401]: Administrador
      - generic [ref=e403]:
        - paragraph [ref=e404]: Guías disponibles
        - button "🆕 Cómo crear un proceso" [ref=e405] [cursor=pointer]:
          - generic [ref=e406]: 🆕
          - generic [ref=e407]: Cómo crear un proceso
          - img [ref=e408]
        - button "🏢 Asignar dependencias o secretarías" [ref=e410] [cursor=pointer]:
          - generic [ref=e411]: 🏢
          - generic [ref=e412]: Asignar dependencias o secretarías
          - img [ref=e413]
        - button "📎 Cómo cargar documentos requeridos" [ref=e415] [cursor=pointer]:
          - generic [ref=e416]: 📎
          - generic [ref=e417]: Cómo cargar documentos requeridos
          - img [ref=e418]
        - button "🔄 Seguimiento de un flujo de contratación" [ref=e420] [cursor=pointer]:
          - generic [ref=e421]: 🔄
          - generic [ref=e422]: Seguimiento de un flujo de contratación
          - img [ref=e423]
        - button "⚙️ Configurar flujos en el Motor de Flujos" [ref=e425] [cursor=pointer]:
          - generic [ref=e426]: ⚙️
          - generic [ref=e427]: Configurar flujos en el Motor de Flujos
          - img [ref=e428]
        - button "👥 Gestionar usuarios y roles" [ref=e430] [cursor=pointer]:
          - generic [ref=e431]: 👥
          - generic [ref=e432]: Gestionar usuarios y roles
          - img [ref=e433]
        - button "📊 Ver reportes y estadísticas" [ref=e435] [cursor=pointer]:
          - generic [ref=e436]: 📊
          - generic [ref=e437]: Ver reportes y estadísticas
          - img [ref=e438]
        - button "📋 Ver mis tareas pendientes" [ref=e440] [cursor=pointer]:
          - generic [ref=e441]: 📋
          - generic [ref=e442]: Ver mis tareas pendientes
          - img [ref=e443]
        - button "🔔 Revisar notificaciones" [ref=e445] [cursor=pointer]:
          - generic [ref=e446]: 🔔
          - generic [ref=e447]: Revisar notificaciones
          - img [ref=e448]
        - button "🔒 Restablecer mi contraseña" [ref=e450] [cursor=pointer]:
          - generic [ref=e451]: 🔒
          - generic [ref=e452]: Restablecer mi contraseña
          - img [ref=e453]
        - button "👁️ Previsualizar documentos" [ref=e455] [cursor=pointer]:
          - generic [ref=e456]: 👁️
          - generic [ref=e457]: Previsualizar documentos
          - img [ref=e458]
        - button "🔄 Reemplazar un documento" [ref=e460] [cursor=pointer]:
          - generic [ref=e461]: 🔄
          - generic [ref=e462]: Reemplazar un documento
          - img [ref=e463]
        - button "📌 Ver versiones de un documento" [ref=e465] [cursor=pointer]:
          - generic [ref=e466]: 📌
          - generic [ref=e467]: Ver versiones de un documento
          - img [ref=e468]
        - button "📧 Cómo funcionan las alertas por correo" [ref=e470] [cursor=pointer]:
          - generic [ref=e471]: 📧
          - generic [ref=e472]: Cómo funcionan las alertas por correo
          - img [ref=e473]
        - button "¿Necesitas más ayuda? Escríbenos" [ref=e476] [cursor=pointer]:
          - img [ref=e478]
          - generic [ref=e480]: ¿Necesitas más ayuda? Escríbenos
          - img [ref=e481]
      - generic [ref=e483]:
        - img [ref=e484]
        - text: Marsetiv bot · Asistente de Gobernación de Caldas
    - button "Marsetiv bot" [ref=e486] [cursor=pointer]:
      - img [ref=e488]
      - img [ref=e503]
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { LoginHelper } from '../helpers/login.helper.js';
  3  | 
  4  | /**
  5  |  * PRUEBAS DE RESPONSIVE DESIGN
  6  |  * Casos: RESP-001, RESP-002
  7  |  */
  8  | 
  9  | test.describe('Responsive Design', () => {
  10 |   
  11 |   test('RESP-001: Login en móvil', async ({ page }) => {
  12 |     console.log('✅ Configurar viewport móvil (iPhone 12)');
  13 |     await page.setViewportSize({ width: 390, height: 844 });
  14 |     
  15 |     await page.goto('/login');
  16 |     
  17 |     // Verificar que los elementos son visibles
  18 |     await expect(page.locator('input[name="email"]')).toBeVisible();
  19 |     await expect(page.locator('input[name="password"]')).toBeVisible();
  20 |     await expect(page.locator('button[type="submit"]')).toBeVisible();
  21 |     
  22 |     console.log('✅ Hacer login en móvil');
  23 |     await page.fill('input[name="email"]', 'admin@test.com');
  24 |     await page.fill('input[name="password"]', 'Test1234!');
  25 |     await page.click('button[type="submit"]');
  26 |     
  27 |     await expect(page).toHaveURL(/.*dashboard/);
  28 |   });
  29 | 
  30 |   test('RESP-002: Dashboard responsive', async ({ page }) => {
  31 |     const login = new LoginHelper(page);
  32 |     await login.loginAsAdmin();
  33 |     
  34 |     console.log('✅ Probar diferentes tamaños de pantalla');
  35 |     
  36 |     // Móvil pequeño (iPhone SE)
  37 |     await page.setViewportSize({ width: 375, height: 667 });
> 38 |     await expect(page.locator('h1, h2')).toBeVisible();
     |                                          ^ Error: expect(locator).toBeVisible() failed
  39 |     
  40 |     // Tablet
  41 |     await page.setViewportSize({ width: 768, height: 1024 });
  42 |     await expect(page.locator('h1, h2')).toBeVisible();
  43 |     
  44 |     // Desktop
  45 |     await page.setViewportSize({ width: 1920, height: 1080 });
  46 |     await expect(page.locator('h1, h2')).toBeVisible();
  47 |     
  48 |     console.log('✅ Dashboard responsive en todos los tamaños');
  49 |   });
  50 | 
  51 |   test('RESP-003: Menú hamburguesa en móvil', async ({ page }) => {
  52 |     const login = new LoginHelper(page);
  53 |     await login.loginAsAdmin();
  54 |     
  55 |     await page.setViewportSize({ width: 375, height: 667 });
  56 |     
  57 |     // Buscar menú hamburguesa
  58 |     const menuButton = page.locator('button:has-text("☰"), .menu-toggle, [aria-label="Menu"]');
  59 |     
  60 |     if (await menuButton.isVisible({ timeout: 3000 })) {
  61 |       console.log('✅ Menú hamburguesa encontrado');
  62 |       await menuButton.click();
  63 |       
  64 |       // Verificar que se abre el menú
  65 |       await page.waitForTimeout(500);
  66 |       console.log('✅ Menú desplegado');
  67 |     }
  68 |   });
  69 | 
  70 |   test('RESP-004: Tabla responsive con scroll', async ({ page }) => {
  71 |     const login = new LoginHelper(page);
  72 |     await login.loginAsAdmin();
  73 |     
  74 |     await page.setViewportSize({ width: 375, height: 667 });
  75 |     await page.goto('/procesos');
  76 |     
  77 |     // Verificar que la tabla es scrolleable en móvil
  78 |     const table = page.locator('table, .table-container');
  79 |     
  80 |     if (await table.isVisible({ timeout: 5000 })) {
  81 |       const hasScroll = await page.evaluate(() => {
  82 |         const el = document.querySelector('table, .table-container');
  83 |         return el ? el.scrollWidth > el.clientWidth : false;
  84 |       });
  85 |       
  86 |       console.log(`✅ Tabla tiene scroll horizontal: ${hasScroll}`);
  87 |     }
  88 |   });
  89 | });
  90 | 
```