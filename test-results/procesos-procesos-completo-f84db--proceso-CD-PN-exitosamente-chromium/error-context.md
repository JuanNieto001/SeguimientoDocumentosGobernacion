# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: procesos\procesos-completo.spec.js >> Gestión de Procesos Contractuales >> PROC-001: Crear proceso CD-PN exitosamente
- Location: tests\procesos\procesos-completo.spec.js:21:3

# Error details

```
TimeoutError: page.fill: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('input[name="nombre"]')

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
        - generic [ref=e41]:
          - link [ref=e42] [cursor=pointer]:
            - /url: http://localhost:8000/procesos
            - img [ref=e43]
          - generic [ref=e45]:
            - heading "Nueva solicitud" [level=1] [ref=e46]
            - paragraph [ref=e47]: Registrar un nuevo proceso de contratación
        - generic [ref=e48]:
          - link [ref=e49] [cursor=pointer]:
            - /url: http://localhost:8000/alertas
            - img [ref=e50]
          - generic [ref=e52]:
            - generic [ref=e53]: JE
            - generic [ref=e54]: Jefe
      - main [ref=e55]:
        - generic [ref=e58]:
          - generic [ref=e59]:
            - generic [ref=e60]:
              - generic [ref=e61]: "1"
              - heading "Identificación del proceso" [level=2] [ref=e62]
            - generic [ref=e63]:
              - generic [ref=e64]:
                - generic [ref=e65]: Flujo de contratación *
                - generic [ref=e66]:
                  - img [ref=e67]
                  - generic [ref=e69]:
                    - text: "Flujo:"
                    - strong [ref=e70]: Contratación Directa - Persona Natural (CD_PN)
              - generic [ref=e71]:
                - generic [ref=e72]: Objeto del contrato *
                - 'textbox "Ej: Prestación de servicios profesionales para apoyar la gestión de..." [ref=e73]'
              - generic [ref=e74]:
                - generic [ref=e75]: Descripción (opcional)
                - textbox "Detalle adicional del proceso..." [ref=e76]
          - generic [ref=e77]:
            - generic [ref=e78]:
              - generic [ref=e79]: "2"
              - heading "Dependencia solicitante" [level=2] [ref=e80]
              - generic [ref=e81]:
                - img [ref=e82]
                - text: Asignada a tu perfil
            - generic [ref=e84]:
              - generic [ref=e85]:
                - generic [ref=e86]: Secretaría
                - generic [ref=e87]:
                  - img [ref=e88]
                  - text: Secretaría de Planeación
              - generic [ref=e90]:
                - generic [ref=e91]: Unidad
                - generic [ref=e92]:
                  - img [ref=e93]
                  - text: Unidad de Sistemas
          - generic [ref=e95]:
            - generic [ref=e96]:
              - generic [ref=e97]: "3"
              - heading "Datos económicos" [level=2] [ref=e98]
            - generic [ref=e99]:
              - generic [ref=e100]:
                - generic [ref=e101]: Valor estimado (COP) (opcional)
                - generic [ref=e102]:
                  - generic [ref=e103]: $
                  - spinbutton [ref=e104]
              - generic [ref=e105]:
                - generic [ref=e106]: Plazo de ejecución (meses) *
                - spinbutton [ref=e107]
                - paragraph [ref=e108]: Ingrese solo el número de meses (1-60)
          - generic [ref=e109]:
            - generic [ref=e110]:
              - generic [ref=e111]: "4"
              - heading "Estudios Previos *" [level=2] [ref=e112]
            - generic [ref=e114]:
              - generic [ref=e115]: Cargar documento de Estudios Previos *
              - button "Choose File" [ref=e116] [cursor=pointer]
              - paragraph [ref=e117]: "Formatos permitidos: PDF, DOC, DOCX. Este archivo es obligatorio para crear la solicitud."
          - button "5 Datos del contratista (opcional — se puede completar después)" [ref=e119] [cursor=pointer]:
            - generic [ref=e120]:
              - generic [ref=e121]: "5"
              - generic [ref=e122]: Datos del contratista
              - generic [ref=e123]: (opcional — se puede completar después)
            - img [ref=e124]
          - generic [ref=e126]:
            - button "Crear proceso" [ref=e127] [cursor=pointer]:
              - img [ref=e128]
              - text: Crear proceso
            - link "Cancelar" [ref=e130] [cursor=pointer]:
              - /url: http://localhost:8000/procesos
  - button "Marsetiv bot" [ref=e132] [cursor=pointer]:
    - img [ref=e134]
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | import { LoginHelper } from '../helpers/login.helper.js';
  3   | 
  4   | /**
  5   |  * MÓDULO 3: GESTIÓN DE PROCESOS CONTRACTUALES
  6   |  * Casos: PROC-001 a PROC-020
  7   |  * CRÍTICO - CERTIFICACIÓN
  8   |  */
  9   | 
  10  | test.describe('Gestión de Procesos Contractuales', () => {
  11  |   
  12  |   let login;
  13  | 
  14  |   test.beforeEach(async ({ page }) => {
  15  |     login = new LoginHelper(page);
  16  |     await login.loginAsUnidad();
  17  |   });
  18  | 
  19  |   // ============ CASOS POSITIVOS ============
  20  |   
  21  |   test('PROC-001: Crear proceso CD-PN exitosamente', async ({ page }) => {
  22  |     await page.goto('/procesos/crear');
  23  |     
> 24  |     await page.fill('input[name="nombre"]', `Proceso QA ${Date.now()}`);
      |                ^ TimeoutError: page.fill: Timeout 10000ms exceeded.
  25  |     await page.fill('textarea[name="descripcion"]', 'Proceso de certificación QA');
  26  |     await page.fill('input[name="objeto"]', 'Contratación para pruebas QA');
  27  |     await page.fill('input[name="valor"]', '5000000');
  28  |     
  29  |     const tipoSelect = page.locator('select[name="tipo_proceso"], select[name="workflow_id"]');
  30  |     if (await tipoSelect.isVisible({ timeout: 3000 })) {
  31  |       await tipoSelect.selectOption({ label: /CD-PN|Contratación Directa/i });
  32  |     }
  33  |     
  34  |     await page.click('button[type="submit"]');
  35  |     
  36  |     await expect(page.locator('body')).toContainText(/creado|exitoso|éxito/i, { timeout: 10000 });
  37  |     console.log('✅ PROC-001: Proceso creado exitosamente');
  38  |   });
  39  | 
  40  |   test('PROC-002: Listar procesos propios', async ({ page }) => {
  41  |     await page.goto('/procesos');
  42  |     
  43  |     await expect(page.locator('h1, h2')).toContainText(/procesos/i);
  44  |     
  45  |     const rows = page.locator('tbody tr, .proceso-card');
  46  |     const count = await rows.count();
  47  |     
  48  |     console.log(`✅ PROC-002: ${count} procesos listados`);
  49  |     expect(count).toBeGreaterThanOrEqual(0);
  50  |   });
  51  | 
  52  |   test('PROC-003: Ver detalle de proceso', async ({ page }) => {
  53  |     await page.goto('/procesos');
  54  |     
  55  |     const firstProcess = page.locator('tbody tr a, .proceso-card a').first();
  56  |     if (await firstProcess.isVisible({ timeout: 5000 })) {
  57  |       await firstProcess.click();
  58  |       
  59  |       await expect(page.locator('body')).toContainText(/etapa|estado|proceso/i);
  60  |       console.log('✅ PROC-003: Detalle visible');
  61  |     } else {
  62  |       console.log('⚠️ PROC-003: No hay procesos para ver');
  63  |     }
  64  |   });
  65  | 
  66  |   test('PROC-004: Editar proceso en estado BORRADOR', async ({ page }) => {
  67  |     await page.goto('/procesos');
  68  |     
  69  |     const editButton = page.locator('a:has-text("Editar"), button:has-text("Editar")').first();
  70  |     if (await editButton.isVisible({ timeout: 5000 })) {
  71  |       await editButton.click();
  72  |       
  73  |       await page.fill('input[name="nombre"]', 'Proceso Editado QA');
  74  |       await page.click('button[type="submit"]');
  75  |       
  76  |       await expect(page.locator('body')).toContainText(/actualizado|guardado/i);
  77  |       console.log('✅ PROC-004: Proceso editado');
  78  |     }
  79  |   });
  80  | 
  81  |   test('PROC-005: Filtrar procesos por estado', async ({ page }) => {
  82  |     await page.goto('/procesos');
  83  |     
  84  |     const filterSelect = page.locator('select[name="estado"], #estado-filter');
  85  |     if (await filterSelect.isVisible({ timeout: 3000 })) {
  86  |       await filterSelect.selectOption({ index: 1 });
  87  |       await page.waitForTimeout(1000);
  88  |       
  89  |       console.log('✅ PROC-005: Filtro aplicado');
  90  |     }
  91  |   });
  92  | 
  93  |   test('PROC-006: Buscar proceso por código', async ({ page }) => {
  94  |     await page.goto('/procesos');
  95  |     
  96  |     const searchInput = page.locator('input[type="search"], input[name="search"]');
  97  |     if (await searchInput.isVisible({ timeout: 3000 })) {
  98  |       await searchInput.fill('CD-PN');
  99  |       await page.waitForTimeout(1000);
  100 |       
  101 |       console.log('✅ PROC-006: Búsqueda ejecutada');
  102 |     }
  103 |   });
  104 | 
  105 |   test('PROC-007: Avanzar proceso a siguiente etapa', async ({ page }) => {
  106 |     await page.goto('/procesos');
  107 |     
  108 |     const firstProcess = page.locator('tbody tr a').first();
  109 |     if (await firstProcess.isVisible({ timeout: 5000 })) {
  110 |       await firstProcess.click();
  111 |       
  112 |       const avanzarButton = page.locator('button:has-text("Avanzar"), button:has-text("Enviar")');
  113 |       if (await avanzarButton.isVisible({ timeout: 3000 })) {
  114 |         await avanzarButton.click();
  115 |         console.log('✅ PROC-007: Proceso avanzado');
  116 |       }
  117 |     }
  118 |   });
  119 | 
  120 |   // ============ CASOS NEGATIVOS ============
  121 | 
  122 |   test('PROC-008: Crear proceso sin nombre (validación)', async ({ page }) => {
  123 |     await page.goto('/procesos/crear');
  124 |     
```