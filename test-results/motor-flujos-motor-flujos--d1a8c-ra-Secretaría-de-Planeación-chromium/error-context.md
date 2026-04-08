# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: motor-flujos\motor-flujos-completo.spec.js >> Motor de Flujos - Tests Completos >> MOTOR-002: Crear flujo para Secretaría de Planeación
- Location: tests\motor-flujos\motor-flujos-completo.spec.js:31:3

# Error details

```
TimeoutError: page.fill: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('input[name="nombre"], #nombre')

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
          - paragraph [ref=e9]: de Caldas
      - generic [ref=e10]:
        - generic [ref=e11]: AD
        - generic [ref=e12]:
          - paragraph [ref=e13]: admin
          - paragraph [ref=e14]: admin@demo.com
      - navigation [ref=e17]:
        - link "Panel principal" [ref=e18] [cursor=pointer]:
          - /url: http://localhost:8000/panel-principal
          - img [ref=e19]
          - text: Panel principal
        - link "Contratos de aplicaciones" [ref=e21] [cursor=pointer]:
          - /url: http://localhost:8000/contratos-aplicaciones
          - img [ref=e22]
          - text: Contratos de aplicaciones
        - paragraph [ref=e24]: Administración
        - link "Usuarios" [ref=e25] [cursor=pointer]:
          - /url: http://localhost:8000/admin/usuarios
          - img [ref=e26]
          - text: Usuarios
        - link "Roles" [ref=e28] [cursor=pointer]:
          - /url: http://localhost:8000/admin/roles
          - img [ref=e29]
          - text: Roles
        - link "Logs" [ref=e31] [cursor=pointer]:
          - /url: http://localhost:8000/admin/logs
          - img [ref=e32]
          - text: Logs
        - link "Log autenticación" [ref=e34] [cursor=pointer]:
          - /url: http://localhost:8000/admin/auth-events
          - img [ref=e35]
          - text: Log autenticación
        - link "Motor de Flujos" [ref=e37] [cursor=pointer]:
          - /url: http://localhost:8000/motor-flujos
          - img [ref=e38]
          - text: Motor de Flujos
        - link "Guías de Marsetiv" [ref=e40] [cursor=pointer]:
          - /url: http://localhost:8000/admin/estiven-guides
          - img [ref=e41]
          - text: Guías de Marsetiv
        - button "Secretarías 15" [ref=e44] [cursor=pointer]:
          - generic [ref=e45]:
            - img [ref=e46]
            - generic [ref=e48]: Secretarías
          - generic [ref=e49]:
            - generic [ref=e50]: "15"
            - img [ref=e51]
        - paragraph [ref=e53]: Procesos
        - link "Nueva solicitud" [ref=e54] [cursor=pointer]:
          - /url: http://localhost:8000/procesos/crear
          - img [ref=e55]
          - text: Nueva solicitud
        - link "Ver todos" [ref=e57] [cursor=pointer]:
          - /url: http://localhost:8000/procesos
          - img [ref=e58]
          - text: Ver todos
        - paragraph [ref=e60]: Análisis
        - link "Reportes" [ref=e61] [cursor=pointer]:
          - /url: http://localhost:8000/reportes
          - img [ref=e62]
          - text: Reportes
        - link "Consulta SECOP II" [ref=e64] [cursor=pointer]:
          - /url: http://localhost:8000/secop-consulta
          - img [ref=e65]
          - text: Consulta SECOP II
        - link "Notificaciones" [ref=e67] [cursor=pointer]:
          - /url: http://localhost:8000/alertas
          - img [ref=e68]
          - text: Notificaciones
      - button "Cerrar sesión" [ref=e72] [cursor=pointer]:
        - img [ref=e73]
        - text: Cerrar sesión
    - generic [ref=e75]:
      - banner [ref=e76]:
        - generic [ref=e79]:
          - link [ref=e80] [cursor=pointer]:
            - /url: http://localhost:8000/panel-principal
            - img [ref=e81]
          - generic [ref=e83]:
            - heading "Motor de Flujos" [level=1] [ref=e84]
            - paragraph [ref=e85]: Administrar flujos de contratación dinámicos por Secretaría
        - generic [ref=e86]:
          - link [ref=e87] [cursor=pointer]:
            - /url: http://localhost:8000/alertas
            - img [ref=e88]
          - generic [ref=e90]:
            - generic [ref=e91]: AD
            - generic [ref=e92]: admin
      - main [ref=e93]:
        - generic [ref=e95]:
          - generic [ref=e96]:
            - generic [ref=e97]:
              - generic [ref=e98]:
                - button "← Volver" [ref=e99] [cursor=pointer]
                - generic [ref=e100]:
                  - heading "➕ Nuevo Flujo" [level=2] [ref=e101]
                  - paragraph [ref=e102]: Sin nombre · 0 pasos
              - generic [ref=e103]:
                - button "▲ Info" [ref=e104] [cursor=pointer]
                - button "Cancelar" [ref=e105] [cursor=pointer]
                - button "💾 Guardar Flujo" [ref=e106] [cursor=pointer]
            - generic [ref=e108]:
              - generic [ref=e109]:
                - generic [ref=e110]: Código
                - textbox "CD_PN_CULTURA" [ref=e111]
              - generic [ref=e112]:
                - generic [ref=e113]: Nombre del Flujo
                - textbox "Mínima Cuantía – Sec. Cultura" [ref=e114]
              - generic [ref=e115]:
                - generic [ref=e116]: Descripción (opcional)
                - textbox "Descripción breve..." [ref=e117]
          - generic [ref=e119]:
            - generic [ref=e120]:
              - generic [ref=e121]:
                - generic [ref=e122]:
                  - generic [ref=e123]:
                    - heading "📋 Catálogo de Pasos" [level=3] [ref=e124]
                    - paragraph [ref=e125]: Arrastra al canvas para agregar
                  - button "◀" [ref=e126] [cursor=pointer]
                - textbox "🔍 Buscar paso..." [ref=e127]
              - generic [ref=e128]:
                - generic [ref=e129]:
                  - generic [ref=e130]:
                    - generic [ref=e132]: ✅
                    - generic [ref=e133]:
                      - generic [ref=e134]: Adjudicación
                      - generic [ref=e135]:
                        - generic [ref=e136]: secuencial
                        - generic [ref=e137]: ADJUDICACION
                    - generic [ref=e138]: ⋮⋮
                  - paragraph [ref=e139]: Se adjudica el contrato al oferente seleccionado.
                - generic [ref=e140]:
                  - generic [ref=e141]:
                    - generic [ref=e143]: ✅
                    - generic [ref=e144]:
                      - generic [ref=e145]: Aprobación Planeación
                      - generic [ref=e146]:
                        - generic [ref=e147]: secuencial
                        - generic [ref=e148]: APROB_PLANEACION
                    - generic [ref=e149]: ⋮⋮
                  - paragraph [ref=e150]: La Secretaría de Planeación verifica PAA y aprueba.
                - generic [ref=e151]:
                  - generic [ref=e152]:
                    - generic [ref=e154]: ▶️
                    - generic [ref=e155]:
                      - generic [ref=e156]: ARL, Acta de Inicio y SECOP II
                      - generic [ref=e157]:
                        - generic [ref=e158]: secuencial
                        - generic [ref=e159]: ARL_INICIO
                    - generic [ref=e160]: ⋮⋮
                  - paragraph [ref=e161]: Se gestiona ARL, acta de inicio y activación en SECOP II.
                - generic [ref=e162]:
                  - generic [ref=e163]:
                    - generic [ref=e165]: 👥
                    - generic [ref=e166]:
                      - generic [ref=e167]: Comité de Evaluación
                      - generic [ref=e168]:
                        - generic [ref=e169]: secuencial
                        - generic [ref=e170]: COM_EVALUACION
                    - generic [ref=e171]: ⋮⋮
                  - paragraph [ref=e172]: El comité evalúa las propuestas recibidas (usado en licitaciones).
                - generic [ref=e173]:
                  - generic [ref=e174]:
                    - generic [ref=e176]: F
                    - generic [ref=e177]:
                      - generic [ref=e178]: Consolidación Expediente Precontractual
                      - generic [ref=e179]:
                        - generic [ref=e180]: secuencial
                        - generic [ref=e181]: CONSOL_EXP
                    - generic [ref=e182]: ⋮⋮
                  - paragraph [ref=e183]: Se reúne toda la documentación en un expediente para revisión.
                - generic [ref=e184]:
                  - generic [ref=e185]:
                    - generic [ref=e187]: 📄
                    - generic [ref=e188]:
                      - generic [ref=e189]: Definición de la Necesidad
                      - generic [ref=e190]:
                        - generic [ref=e191]: secuencial
                        - generic [ref=e192]: DEF_NECESIDAD
                    - generic [ref=e193]: ⋮⋮
                  - paragraph [ref=e194]: La Unidad identifica la necesidad y elabora estudios previos.
                - generic [ref=e195]:
                  - generic [ref=e196]:
                    - generic [ref=e198]: S
                    - generic [ref=e199]:
                      - generic [ref=e200]: Descentralización - Solicitud Documentos
                      - generic [ref=e201]:
                        - generic [ref=e202]: paralelo
                        - generic [ref=e203]: DESC_DOCS
                    - generic [ref=e204]: ⋮⋮
                  - paragraph [ref=e205]: Descentralización coordina solicitud de documentos a las áreas.
                - generic [ref=e206]:
                  - generic [ref=e207]:
                    - generic [ref=e209]: 📄
                    - generic [ref=e210]:
                      - generic [ref=e211]: Elaboración Documentos Contractuales
                      - generic [ref=e212]:
                        - generic [ref=e213]: secuencial
                        - generic [ref=e214]: ELAB_DOCS
                    - generic [ref=e215]: ⋮⋮
                  - paragraph [ref=e216]: Se elaboran la minuta, estudios previos definitivos y anexos.
                - generic [ref=e217]:
                  - generic [ref=e218]:
                    - generic [ref=e220]: M
                    - generic [ref=e221]:
                      - generic [ref=e222]: Publicación Aviso de Convocatoria
                      - generic [ref=e223]:
                        - generic [ref=e224]: secuencial
                        - generic [ref=e225]: PUB_AVISO
                    - generic [ref=e226]: ⋮⋮
                  - paragraph [ref=e227]: Se publica el aviso de convocatoria en los medios requeridos.
                - generic [ref=e228]:
                  - generic [ref=e229]:
                    - generic [ref=e231]: G
                    - generic [ref=e232]:
                      - generic [ref=e233]: Publicación y Firma SECOP II
                      - generic [ref=e234]:
                        - generic [ref=e235]: secuencial
                        - generic [ref=e236]: PUB_SECOP
                    - generic [ref=e237]: ⋮⋮
                  - paragraph [ref=e238]: Se publica en SECOP II y se gestiona la firma electrónica.
                - generic [ref=e239]:
                  - generic [ref=e240]:
                    - generic [ref=e242]: S
                    - generic [ref=e243]:
                      - generic [ref=e244]: Radicación en Secretaría Jurídica
                      - generic [ref=e245]:
                        - generic [ref=e246]: secuencial
                        - generic [ref=e247]: RAD_JURIDICA
                    - generic [ref=e248]: ⋮⋮
                  - paragraph [ref=e249]: El expediente se radica en Jurídica para verificación de ajustado a derecho.
                - generic [ref=e250]:
                  - generic [ref=e251]:
                    - generic [ref=e253]: A
                    - generic [ref=e254]:
                      - generic [ref=e255]: Radicación Final y Número de Contrato
                      - generic [ref=e256]:
                        - generic [ref=e257]: secuencial
                        - generic [ref=e258]: RAD_FINAL
                    - generic [ref=e259]: ⋮⋮
                  - paragraph [ref=e260]: Jurídica asigna número de contrato y radica definitivamente.
                - generic [ref=e261]:
                  - generic [ref=e262]:
                    - generic [ref=e264]: I
                    - generic [ref=e265]:
                      - generic [ref=e266]: Recepción de Propuestas
                      - generic [ref=e267]:
                        - generic [ref=e268]: secuencial
                        - generic [ref=e269]: RECEP_PROPUESTAS
                    - generic [ref=e270]: ⋮⋮
                  - paragraph [ref=e271]: Se reciben y registran las propuestas de los oferentes.
                - generic [ref=e272]:
                  - generic [ref=e273]:
                    - generic [ref=e275]: ✅
                    - generic [ref=e276]:
                      - generic [ref=e277]: Revisión Jurídica Adicional
                      - generic [ref=e278]:
                        - generic [ref=e279]: condicional
                        - generic [ref=e280]: REV_JURIDICA
                    - generic [ref=e281]: ⋮⋮
                  - paragraph [ref=e282]: Revisión jurídica especial para montos altos o contratos complejos.
                - generic [ref=e283]:
                  - generic [ref=e284]:
                    - generic [ref=e286]: D
                    - generic [ref=e287]:
                      - generic [ref=e288]: Solicitud de CDP
                      - generic [ref=e289]:
                        - generic [ref=e290]: secuencial
                        - generic [ref=e291]: SOL_CDP
                    - generic [ref=e292]: ⋮⋮
                  - paragraph [ref=e293]: Se solicita el Certificado de Disponibilidad Presupuestal a Hacienda.
                - generic [ref=e294]:
                  - generic [ref=e295]:
                    - generic [ref=e297]: R
                    - generic [ref=e298]:
                      - generic [ref=e299]: Solicitud de RPC
                      - generic [ref=e300]:
                        - generic [ref=e301]: secuencial
                        - generic [ref=e302]: SOL_RPC
                    - generic [ref=e303]: ⋮⋮
                  - paragraph [ref=e304]: Se solicita el Registro Presupuestal del Compromiso a Hacienda.
                - generic [ref=e305]:
                  - generic [ref=e306]:
                    - generic [ref=e308]: ✅
                    - generic [ref=e309]:
                      - generic [ref=e310]: Validación del Contratista
                      - generic [ref=e311]:
                        - generic [ref=e312]: secuencial
                        - generic [ref=e313]: VAL_CONTRATISTA
                    - generic [ref=e314]: ⋮⋮
                  - paragraph [ref=e315]: Se verifican antecedentes, idoneidad y documentos del contratista.
                - generic [ref=e316]:
                  - generic [ref=e317]:
                    - generic [ref=e319]: T
                    - generic [ref=e320]:
                      - generic [ref=e321]: Viabilidad Económica
                      - generic [ref=e322]:
                        - generic [ref=e323]: secuencial
                        - generic [ref=e324]: VIAB_ECONOMICA
                    - generic [ref=e325]: ⋮⋮
                  - paragraph [ref=e326]: Análisis de viabilidad económica del proyecto.
                - generic [ref=e328] [cursor=pointer]:
                  - generic [ref=e329]: ✚
                  - text: Paso personalizado
            - application [ref=e331]:
              - generic [ref=e333]:
                - generic:
                  - generic:
                    - img
                    - img:
                      - group "Edge from start to end"
                  - generic:
                    - group [ref=e334]:
                      - generic [ref=e335] [cursor=pointer]:
                        - generic [ref=e336]: ▶
                        - text: INICIO
                    - group [ref=e339]:
                      - generic [ref=e340] [cursor=pointer]:
                        - generic [ref=e344]: ■
                        - text: FIN
              - generic "Control Panel" [ref=e345]:
                - button "Zoom In" [disabled]:
                  - img
                - button "Zoom Out" [ref=e346] [cursor=pointer]:
                  - img [ref=e347]
                - button "Fit View" [ref=e349] [cursor=pointer]:
                  - img [ref=e350]
                - button "Toggle Interactivity" [ref=e352] [cursor=pointer]:
                  - img [ref=e353]
              - img "Mini Map" [ref=e356]
              - img
              - generic [ref=e360]:
                - generic [ref=e361]: 🎨
                - heading "Comience a construir su flujo" [level=3] [ref=e362]
                - paragraph [ref=e363]: Arrastre pasos del catálogo (izquierda) al canvas, o haga click en un paso del catálogo para agregarlo.
  - button "Marsetiv bot" [ref=e366] [cursor=pointer]:
    - img [ref=e368]
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | import { LoginHelper } from '../helpers/login.helper.js';
  3   | 
  4   | /**
  5   |  * TESTS MOTOR DE FLUJOS - Verificación completa
  6   |  * 1. Verificar flujos existentes 
  7   |  * 2. Crear flujo CD-PJ si falta
  8   |  * 3. Verificar que aparecen en dropdown de solicitudes
  9   |  */
  10  | 
  11  | test.describe('Motor de Flujos - Tests Completos', () => {
  12  |   
  13  |   let login;
  14  | 
  15  |   test.beforeEach(async ({ page }) => {
  16  |     login = new LoginHelper();
  17  |     await login.loginAsAdmin(page);
  18  |   });
  19  | 
  20  |   test('MOTOR-001: Verificar que flujo CD-PN existe', async ({ page }) => {
  21  |     await page.goto('/motor-flujos');
  22  |     
  23  |     // Buscar flujo CD-PN por nombre
  24  |     const flujoCDPN = page.locator('text=/Persona Natural/i').first();
  25  |     await expect(flujoCDPN).toBeVisible({ timeout: 10000 });
  26  |     
  27  |     console.log('✅ MOTOR-001: Flujo CD-PN encontrado');
  28  |     await page.screenshot({ path: 'test-results/motor-001-cdpn-existe.png', fullPage: true });
  29  |   });
  30  | 
  31  |   test('MOTOR-002: Crear flujo para Secretaría de Planeación', async ({ page }) => {
  32  |     await page.goto('/motor-flujos');
  33  |     await page.waitForTimeout(2000);
  34  |     
  35  |     // Buscar botón crear/nuevo flujo
  36  |     const crearButton = page.locator('button:has-text("Crear"), button:has-text("Nuevo"), a:has-text("Crear"), .btn:has-text("Nuevo")').first();
  37  |     
  38  |     if (await crearButton.isVisible({ timeout: 5000 })) {
  39  |       await crearButton.click();
  40  |       await page.waitForTimeout(2000);
  41  |       
  42  |       // Llenar formulario específico para Planeación - Sistemas
> 43  |       await page.fill('input[name="nombre"], #nombre', 'Flujo Secretaría de Planeación - Sistemas');
      |                  ^ TimeoutError: page.fill: Timeout 10000ms exceeded.
  44  |       await page.fill('textarea[name="descripcion"], #descripcion', 'Flujo específico para la Secretaría de Planeación, Unidad de Sistemas');
  45  |       
  46  |       // Seleccionar Secretaría de Planeación
  47  |       const secretariaSelect = page.locator('select[name="secretaria"], select[name="secretaria_id"], #secretaria').first();
  48  |       if (await secretariaSelect.isVisible({ timeout: 3000 })) {
  49  |         // Buscar específicamente Planeación
  50  |         const planeacionOption = secretariaSelect.locator('option:has-text("Planeación"), option:has-text("PLANEACION")');
  51  |         if (await planeacionOption.first().isVisible({ timeout: 2000 })) {
  52  |           await planeacionOption.first().click();
  53  |         } else {
  54  |           await secretariaSelect.selectOption({ index: 2 }); // Fallback a segunda opción
  55  |         }
  56  |       }
  57  |       
  58  |       // Guardar con timeout más corto
  59  |       await page.click('button[type="submit"], button:has-text("Guardar")');
  60  |       
  61  |       // Solo esperar 5 segundos máximo
  62  |       await page.waitForTimeout(5000);
  63  |       
  64  |       console.log('✅ MOTOR-002: Flujo para Planeación-Sistemas iniciado');
  65  |     } else {
  66  |       console.log('⚠️ MOTOR-002: No se encontró botón crear');
  67  |     }
  68  |     
  69  |     await page.screenshot({ path: 'test-results/motor-002-planeacion-sistemas.png', fullPage: true });
  70  |   });
  71  | 
  72  |   test('MOTOR-005: Verificar que nodos se pueden arrastrar en canvas', async ({ page }) => {
  73  |     await page.goto('/motor-flujos');
  74  |     await page.waitForTimeout(2000);
  75  |     
  76  |     // Abrir flujo CD-PN existente
  77  |     const flujoCDPN = page.locator('text=/Persona Natural/i').first();
  78  |     await flujoCDPN.click();
  79  |     await page.waitForTimeout(5000);
  80  |     
  81  |     // Buscar botón editar/modo edición
  82  |     const editButton = page.locator('button:has-text("Editar"), button:has-text("Edit"), .btn-edit, #edit-mode').first();
  83  |     if (await editButton.isVisible({ timeout: 3000 })) {
  84  |       await editButton.click();
  85  |       await page.waitForTimeout(2000);
  86  |     }
  87  |     
  88  |     // Verificar que hay nodos visibles
  89  |     const startNode = page.locator('.react-flow__node, .workflow-node').first();
  90  |     await expect(startNode).toBeVisible({ timeout: 10000 });
  91  |     
  92  |     // Obtener posición inicial del nodo
  93  |     const initialBox = await startNode.boundingBox();
  94  |     
  95  |     if (initialBox) {
  96  |       console.log(`📍 Posición inicial: x=${initialBox.x}, y=${initialBox.y}`);
  97  |       
  98  |       // Intentar arrastrar el nodo (drag & drop)
  99  |       await startNode.hover();
  100 |       await page.mouse.down();
  101 |       await page.mouse.move(initialBox.x + 100, initialBox.y + 50, { steps: 5 });
  102 |       await page.mouse.up();
  103 |       
  104 |       await page.waitForTimeout(1000);
  105 |       
  106 |       // Verificar nueva posición
  107 |       const finalBox = await startNode.boundingBox();
  108 |       if (finalBox) {
  109 |         const moved = Math.abs(finalBox.x - initialBox.x) > 10 || Math.abs(finalBox.y - initialBox.y) > 10;
  110 |         
  111 |         console.log(`📍 Posición final: x=${finalBox.x}, y=${finalBox.y}`);
  112 |         console.log(`🔄 Nodo se movió: ${moved ? 'SÍ' : 'NO'}`);
  113 |         
  114 |         if (moved) {
  115 |           console.log('✅ MOTOR-005: Drag & drop funcional');
  116 |         } else {
  117 |           console.log('⚠️ MOTOR-005: Nodo no se movió, verificar modo edición');
  118 |         }
  119 |       }
  120 |     }
  121 |     
  122 |     await page.screenshot({ path: 'test-results/motor-005-drag-drop-test.png', fullPage: true });
  123 |   });
  124 | 
  125 |   test('MOTOR-003: Verificar que nodos se pueden arrastrar en canvas', async ({ page }) => {
  126 |     await page.goto('/motor-flujos');
  127 |     await page.waitForTimeout(2000);
  128 |     
  129 |     // Abrir flujo CD-PN existente
  130 |     const flujoCDPN = page.locator('text=/Persona Natural/i').first();
  131 |     await flujoCDPN.click();
  132 |     await page.waitForTimeout(5000);
  133 |     
  134 |     // Buscar botón editar/modo edición
  135 |     const editButton = page.locator('button:has-text("Editar"), button:has-text("Edit"), .btn-edit, #edit-mode').first();
  136 |     if (await editButton.isVisible({ timeout: 3000 })) {
  137 |       await editButton.click();
  138 |       await page.waitForTimeout(2000);
  139 |     }
  140 |     
  141 |     // Verificar que hay nodos visibles
  142 |     const startNode = page.locator('.react-flow__node, .workflow-node').first();
  143 |     await expect(startNode).toBeVisible({ timeout: 10000 });
```