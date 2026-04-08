# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e\auth-casos-ui.spec.js >> Casos de Autenticacion UI >> AUTH-000-Video completo login error y recuperacion
- Location: tests\e2e\auth-casos-ui.spec.js:15:3

# Error details

```
Error: page.waitForTimeout: Target page, context or browser has been closed
```

# Test source

```ts
  79  | 
  80  |     await expect(page).toHaveURL(/\/login/);
  81  |     await expect(page.locator('input[name="email"]')).toBeVisible();
  82  |     await expect(page.locator('input[name="password"]')).toBeVisible();
  83  | 
  84  |     await saveAuthEvidence(page, 'AUTH-001', 'pagina-login-cargada');
  85  |   });
  86  | 
  87  |   test('AUTH-002-Login con usuario no existente', async ({ page }) => {
  88  |     await navigateForAuth(page, '/login');
  89  | 
  90  |     await page.fill('input[name="email"]', 'no.existe.usuario@demo.com');
  91  |     await page.fill('input[name="password"]', 'clave-invalida');
  92  |     await saveAuthEvidence(page, 'AUTH-002', 'antes-intento-login-invalido');
  93  | 
  94  |     await Promise.all([
  95  |       page.waitForLoadState('networkidle'),
  96  |       page.click('button[type="submit"]'),
  97  |     ]);
  98  | 
  99  |     await expect(page).toHaveURL(/\/login/);
  100 | 
  101 |     const hasErrorMessage = await page
  102 |       .locator('[role="alert"], .text-red-500, .text-red-600, .text-red-700')
  103 |       .first()
  104 |       .isVisible()
  105 |       .catch(() => false);
  106 | 
  107 |     expect(hasErrorMessage || page.url().includes('/login')).toBeTruthy();
  108 |     await saveAuthEvidence(page, 'AUTH-002', 'resultado-login-invalido');
  109 |   });
  110 | 
  111 |   test('AUTH-003-Pagina de Recuperacion de Contrasena', async ({ page }) => {
  112 |     await navigateForAuth(page, '/forgot-password');
  113 | 
  114 |     await expect(page).toHaveURL(/\/forgot-password/);
  115 |     await expect(page.locator('input[name="usuario_email"]')).toBeVisible();
  116 |     await expect(page.locator('input[name="correo_destino"]')).toBeVisible();
  117 | 
  118 |     await saveAuthEvidence(page, 'AUTH-003', 'pagina-recuperacion-cargada');
  119 |   });
  120 | 
  121 |   test('AUTH-004-Solicitar enlace de recuperacion', async ({ page }) => {
  122 |     await navigateForAuth(page, '/forgot-password');
  123 | 
  124 |     await page.fill('input[name="usuario_email"]', RESET_USUARIO_EMAIL);
  125 |     await page.fill('input[name="correo_destino"]', RESET_CORREO_DESTINO);
  126 |     await saveAuthEvidence(page, 'AUTH-004', 'antes-enviar-solicitud-recuperacion');
  127 | 
  128 |     await Promise.all([
  129 |       page.waitForLoadState('networkidle'),
  130 |       page.click('button[type="submit"]'),
  131 |     ]);
  132 | 
  133 |     await expect(page.locator('body')).toContainText(/enlace enviado|revisa el correo/i);
  134 |     await saveAuthEvidence(page, 'AUTH-004', 'solicitud-recuperacion-enviada');
  135 |   });
  136 | });
  137 | 
  138 | async function saveAuthEvidence(page, caseId, label) {
  139 |   authEvidenceCounter += 1;
  140 | 
  141 |   const evidenceDir = path.join(process.cwd(), 'test-results', 'evidencias-auth');
  142 |   if (!fs.existsSync(evidenceDir)) {
  143 |     fs.mkdirSync(evidenceDir, { recursive: true });
  144 |   }
  145 | 
  146 |   const safeCase = sanitizeForPath(caseId);
  147 |   const safeLabel = sanitizeForPath(label);
  148 |   const fileName = `${String(authEvidenceCounter).padStart(4, '0')}-${safeCase}-${safeLabel}.png`;
  149 |   const target = path.join(evidenceDir, fileName);
  150 | 
  151 |   await page.screenshot({ path: target, fullPage: true });
  152 | }
  153 | 
  154 | function sanitizeForPath(value) {
  155 |   return String(value)
  156 |     .normalize('NFD')
  157 |     .replace(/[\u0300-\u036f]/g, '')
  158 |     .replace(/[^a-zA-Z0-9-_]+/g, '-')
  159 |     .replace(/-+/g, '-')
  160 |     .replace(/^-|-$/g, '')
  161 |     .toLowerCase();
  162 | }
  163 | 
  164 | async function pauseForVideo(page) {
  165 |   await page.waitForTimeout(VIDEO_STEP_PAUSE_MS);
  166 | }
  167 | 
  168 | async function holdForManualEmailCheck(page, options = {}) {
  169 |   if (AUTH_MANUAL_HOLD_MS <= 0) {
  170 |     return;
  171 |   }
  172 | 
  173 |   const startLabel = options.startLabel || '07-inicio-espera-manual-correo';
  174 |   const endLabel = options.endLabel || '08-fin-espera-manual-correo';
  175 |   const message = options.message || `AUTH-000 manual hold activo por ${AUTH_MANUAL_HOLD_MS} ms para revisar correo.`;
  176 | 
  177 |   console.log(message);
  178 |   await saveAuthEvidence(page, 'AUTH-000', startLabel);
> 179 |   await page.waitForTimeout(AUTH_MANUAL_HOLD_MS);
      |              ^ Error: page.waitForTimeout: Target page, context or browser has been closed
  180 |   await saveAuthEvidence(page, 'AUTH-000', endLabel);
  181 | }
  182 | 
  183 | async function navigateForAuth(page, url) {
  184 |   await page.goto(url, { waitUntil: 'domcontentloaded' });
  185 | }
  186 | 
```