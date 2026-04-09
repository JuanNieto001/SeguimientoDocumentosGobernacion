# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e\flujo-ui-desde-cero.spec.js >> FLOW-001-Flujo completo UI con dato SECOP 1053850113
- Location: tests\e2e\flujo-ui-desde-cero.spec.js:19:1

# Error details

```
Test timeout of 60000ms exceeded.
```

```
Error: No se encontro un boton habilitado para avanzar la etapa.
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
        - generic [ref=e11]: JE
        - generic [ref=e12]:
          - paragraph [ref=e13]: Jefe Unidad Sistemas
          - paragraph [ref=e14]: jefe.sistemas@demo.com
      - navigation [ref=e17]:
        - link "Panel principal" [ref=e18] [cursor=pointer]:
          - /url: http://localhost:8000/panel-principal
          - img [ref=e19]
          - text: Panel principal
        - paragraph [ref=e21]: Mi Área
        - link "Mi bandeja" [ref=e22] [cursor=pointer]:
          - /url: http://localhost:8000/unidad
          - img [ref=e23]
          - text: Mi bandeja
        - link "Nueva solicitud" [ref=e25] [cursor=pointer]:
          - /url: http://localhost:8000/procesos/crear
          - img [ref=e26]
          - text: Nueva solicitud
        - link "Consulta SECOP II" [ref=e28] [cursor=pointer]:
          - /url: http://localhost:8000/secop-consulta
          - img [ref=e29]
          - text: Consulta SECOP II
        - link "Notificaciones" [ref=e31] [cursor=pointer]:
          - /url: http://localhost:8000/alertas
          - img [ref=e32]
          - text: Notificaciones
      - button "Cerrar sesión" [ref=e36] [cursor=pointer]:
        - img [ref=e37]
        - text: Cerrar sesión
    - generic [ref=e39]:
      - banner [ref=e40]:
        - generic [ref=e43]:
          - generic [ref=e44]:
            - generic [ref=e45]:
              - link "Unidad Solicitante" [ref=e46] [cursor=pointer]:
                - /url: http://localhost:8000/unidad
              - generic [ref=e47]: /
              - generic [ref=e48]: CD_PN-2026-0003
            - heading "Validación de Documentos del Contratista" [level=1] [ref=e49]
          - link "Volver" [ref=e50] [cursor=pointer]:
            - /url: http://localhost:8000/unidad
            - img [ref=e51]
            - text: Volver
        - generic [ref=e53]:
          - link "1" [ref=e54] [cursor=pointer]:
            - /url: http://localhost:8000/alertas
            - img [ref=e55]
            - generic [ref=e57]: "1"
          - generic [ref=e58]:
            - generic [ref=e59]: JE
            - generic [ref=e60]: Jefe
      - main [ref=e61]:
        - generic [ref=e62]:
          - generic [ref=e63]:
            - img [ref=e64]
            - text: Documento marcado como recibido.
          - generic [ref=e66]:
            - generic [ref=e67]:
              - generic [ref=e68]:
                - heading "CD_PN-2026-0003" [level=2] [ref=e69]
                - paragraph [ref=e70]: FLOW-REAL-1775680644755 proceso completo por UI
              - generic [ref=e71]: Paso 3
            - generic [ref=e72]:
              - generic [ref=e73]:
                - paragraph [ref=e74]: Tipo
                - paragraph [ref=e75]: Contratación Directa - Persona Natural
              - generic [ref=e76]:
                - paragraph [ref=e77]: Valor estimado
                - paragraph [ref=e78]: $ 18.000.000
              - generic [ref=e79]:
                - paragraph [ref=e80]: Contratista
                - paragraph [ref=e81]: Sin asignar
              - generic [ref=e82]:
                - paragraph [ref=e83]: Creado por
                - paragraph [ref=e84]: Jefe Unidad Sistemas
          - generic [ref=e85]:
            - generic [ref=e86]:
              - paragraph [ref=e87]: 📥 Recepción del proceso
              - generic [ref=e88]:
                - generic [ref=e89]: ✅
                - generic [ref=e90]: Proceso recibido
              - paragraph [ref=e91]: 08/04/2026 15:38
            - generic [ref=e92]:
              - paragraph [ref=e93]: 📊 Progreso de documentos
              - generic [ref=e94]:
                - generic [ref=e95]:
                  - paragraph [ref=e96]: 0/6
                  - paragraph [ref=e97]: Recibidos físicamente
                - generic [ref=e98]:
                  - paragraph [ref=e99]: 0/6
                  - paragraph [ref=e100]: Digitalizados
                - generic [ref=e101]:
                  - paragraph [ref=e102]: ⏳
                  - paragraph [ref=e103]: Pendiente
              - paragraph [ref=e105]: 0% completado
          - generic [ref=e106]:
            - generic [ref=e107]:
              - generic [ref=e108]:
                - heading "📋 Documentos del Contratista" [level=3] [ref=e109]
                - generic [ref=e110]: 0/6 físicos · 0/6 digitales
              - generic [ref=e111]:
                - generic [ref=e112]: "#"
                - generic [ref=e113]: Documento
                - generic [ref=e114]: Físico recibido
                - generic [ref=e115]: Archivo digital
            - generic [ref=e116]:
              - generic [ref=e118]:
                - generic [ref=e119]: "1"
                - generic [ref=e120]:
                  - generic:
                    - paragraph: Hoja de Vida SIGEP
                    - generic [ref=e121]: Requerido
                  - button "Marcar recibido" [ref=e124] [cursor=pointer]:
                    - img [ref=e125]
                    - text: Marcar recibido
                  - generic [ref=e128]:
                    - img [ref=e129]
                    - text: Recibe físico antes
              - generic [ref=e132]:
                - generic [ref=e133]: "2"
                - generic [ref=e134]:
                  - generic:
                    - paragraph: Certificados de Experiencia
                    - generic [ref=e135]: Requerido
                  - button "Marcar recibido" [ref=e138] [cursor=pointer]:
                    - img [ref=e139]
                    - text: Marcar recibido
                  - generic [ref=e142]:
                    - img [ref=e143]
                    - text: Recibe físico antes
              - generic [ref=e146]:
                - generic [ref=e147]: "3"
                - generic [ref=e148]:
                  - generic:
                    - paragraph: Antecedentes Disciplinarios
                    - generic [ref=e149]: Requerido
                  - button "Marcar recibido" [ref=e152] [cursor=pointer]:
                    - img [ref=e153]
                    - text: Marcar recibido
                  - generic [ref=e156]:
                    - img [ref=e157]
                    - text: Recibe físico antes
              - generic [ref=e160]:
                - generic [ref=e161]: "4"
                - generic [ref=e162]:
                  - generic:
                    - paragraph: Antecedentes Fiscales
                    - generic [ref=e163]: Requerido
                  - button "Marcar recibido" [ref=e166] [cursor=pointer]:
                    - img [ref=e167]
                    - text: Marcar recibido
                  - generic [ref=e170]:
                    - img [ref=e171]
                    - text: Recibe físico antes
              - generic [ref=e174]:
                - generic [ref=e175]: "5"
                - generic [ref=e176]:
                  - generic:
                    - paragraph: Antecedentes Judiciales
                    - generic [ref=e177]: Requerido
                  - button "Marcar recibido" [ref=e180] [cursor=pointer]:
                    - img [ref=e181]
                    - text: Marcar recibido
                  - generic [ref=e184]:
                    - img [ref=e185]
                    - text: Recibe físico antes
              - generic [ref=e188]:
                - generic [ref=e189]: "6"
                - generic [ref=e190]:
                  - generic:
                    - paragraph: RUT Actualizado
                    - generic [ref=e191]: Requerido
                  - button "Marcar recibido" [ref=e194] [cursor=pointer]:
                    - img [ref=e195]
                    - text: Marcar recibido
                  - generic [ref=e198]:
                    - img [ref=e199]
                    - text: Recibe físico antes
          - generic [ref=e202]:
            - generic [ref=e203]:
              - paragraph [ref=e204]: 🔒 Completa todos los documentos requeridos para poder enviar
              - paragraph [ref=e205]: 6 pendientes de recibir físicamente · 6 sin digitalizar
            - button "Enviar a siguiente etapa" [disabled] [ref=e207]:
              - img [ref=e208]
              - text: Enviar a siguiente etapa
  - button "Marsetiv bot" [ref=e211] [cursor=pointer]:
    - img [ref=e213]
```

# Test source

```ts
  542 |   await uploadAllDigitalDocuments(page, fixturePath, caseId);
  543 |   await clickAdvanceAction(page, caseId);
  544 |   await takeEvidence(page, caseId, 'fin-unidad-abogado');
  545 | }
  546 | 
  547 | async function markProcessAsReceivedIfNeeded(page, caseId) {
  548 |   const receiveButton = page
  549 |     .getByRole('button', { name: /Marcar como recibido|Confirmar Recepci[o\u00f3]n del Documento/i })
  550 |     .first();
  551 | 
  552 |   if (await isVisibleAndEnabled(receiveButton)) {
  553 |     await takeEvidence(page, caseId, 'antes-marcar-recibido');
  554 |     await Promise.all([
  555 |       page.waitForLoadState('networkidle'),
  556 |       receiveButton.click(),
  557 |     ]);
  558 |     await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
  559 |     await takeEvidence(page, caseId, 'despues-marcar-recibido');
  560 |   }
  561 | }
  562 | 
  563 | async function completeChecklistItems(page, caseId) {
  564 |   for (let i = 1; i <= 80; i += 1) {
  565 |     const unchecked = page.locator(
  566 |       'form[action*="/checks/"] button:has-text("⬜"), form[action*="/checks/"] button:has-text("☐")'
  567 |     ).first();
  568 | 
  569 |     if (!(await isVisibleAndEnabled(unchecked))) {
  570 |       break;
  571 |     }
  572 | 
  573 |     await takeEvidence(page, caseId, `check-${String(i).padStart(2, '0')}-antes`);
  574 |     await Promise.all([
  575 |       page.waitForLoadState('networkidle'),
  576 |       unchecked.click(),
  577 |     ]);
  578 |     await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
  579 |     await takeEvidence(page, caseId, `check-${String(i).padStart(2, '0')}-despues`);
  580 |   }
  581 | }
  582 | 
  583 | async function markAllPhysicalDocuments(page, caseId) {
  584 |   for (let i = 1; i <= 120; i += 1) {
  585 |     const markPhysicalButton = page
  586 |       .locator('form[action*="/recibido-fisico"] button:has-text("Marcar recibido")')
  587 |       .first();
  588 | 
  589 |     if (!(await isVisibleAndEnabled(markPhysicalButton))) {
  590 |       break;
  591 |     }
  592 | 
  593 |     await takeEvidence(page, caseId, `fisico-${String(i).padStart(2, '0')}-antes`);
  594 |     await Promise.all([
  595 |       page.waitForLoadState('networkidle'),
  596 |       markPhysicalButton.click(),
  597 |     ]);
  598 |     await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
  599 |     await takeEvidence(page, caseId, `fisico-${String(i).padStart(2, '0')}-despues`);
  600 |   }
  601 | }
  602 | 
  603 | async function uploadAllDigitalDocuments(page, fixturePath, caseId) {
  604 |   for (let i = 1; i <= 120; i += 1) {
  605 |     const pendingUploadInput = page
  606 |       .locator('label:has-text("Subir digital") input[type="file"][name="archivo"]')
  607 |       .first();
  608 | 
  609 |     if ((await pendingUploadInput.count()) === 0) {
  610 |       break;
  611 |     }
  612 | 
  613 |     await takeEvidence(page, caseId, `subida-${String(i).padStart(2, '0')}-antes`);
  614 |     await pendingUploadInput.setInputFiles(fixturePath);
  615 |     await page.waitForLoadState('networkidle');
  616 |     await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
  617 |     await takeEvidence(page, caseId, `subida-${String(i).padStart(2, '0')}-despues`);
  618 |   }
  619 | }
  620 | 
  621 | async function clickAdvanceAction(page, caseId) {
  622 |   const candidates = [
  623 |     /Aprobar y enviar a siguiente etapa|Aprobar y enviar a la siguiente etapa|Aprobar y solicitar documentos/i,
  624 |     /Enviar a la siguiente etapa|Enviar a siguiente etapa|Enviar a la siguiente secretaria|Enviar a siguiente secretar/i,
  625 |     /Finalizar proceso/i,
  626 |   ];
  627 | 
  628 |   for (const candidate of candidates) {
  629 |     const button = page.getByRole('button', { name: candidate }).first();
  630 |     if (await isVisibleAndEnabled(button)) {
  631 |       await takeEvidence(page, caseId, 'antes-avanzar-etapa');
  632 |       await Promise.all([
  633 |         page.waitForLoadState('networkidle'),
  634 |         button.click(),
  635 |       ]);
  636 |       await page.waitForTimeout(UI_OBSERVATION_DELAY_MS);
  637 |       await takeEvidence(page, caseId, 'despues-avanzar-etapa');
  638 |       return;
  639 |     }
  640 |   }
  641 | 
> 642 |   throw new Error('No se encontro un boton habilitado para avanzar la etapa.');
      |         ^ Error: No se encontro un boton habilitado para avanzar la etapa.
  643 | }
  644 | 
  645 | async function validateDashboardViewsForRoles(page, login, processCode) {
  646 |   const rolesToValidate = [
  647 |     { role: 'unidad_solicitante', caseId: 'PANEL-001', required: true },
  648 |     { role: 'secretario', caseId: 'PANEL-002', required: false },
  649 |     { role: 'gobernador', caseId: 'PANEL-003', required: false },
  650 |   ];
  651 | 
  652 |   for (const roleConfig of rolesToValidate) {
  653 |     await validateDashboardViewForRole(page, login, processCode, roleConfig);
  654 |   }
  655 | }
  656 | 
  657 | async function validateDashboardViewForRole(page, login, processCode, roleConfig) {
  658 |   const { role, caseId, required } = roleConfig;
  659 | 
  660 |   try {
  661 |     await loginAsRole(page, login, role);
  662 |   } catch (error) {
  663 |     const detail = error instanceof Error ? error.message : String(error);
  664 | 
  665 |     if (required || STRICT_ROLE_PANEL_CHECKS) {
  666 |       throw new Error(`${caseId}: no fue posible autenticar el rol ${role}. Detalle: ${detail}`);
  667 |     }
  668 | 
  669 |     console.log(`⚠️ ${caseId}: rol ${role} no disponible en este ambiente. Se omite validación de panel. Detalle=${detail}`);
  670 |     return;
  671 |   }
  672 | 
  673 |   await openMainPanel(page);
  674 |   await assertMainPanelLoaded(page, caseId, role);
  675 |   await assertProcessVisibilityFromPanel(page, processCode, caseId, role, { requireVisible: required });
  676 | }
  677 | 
  678 | async function openMainPanel(page) {
  679 |   await retryOnPage(
  680 |     page,
  681 |     async () => {
  682 |       await page.goto('/panel-principal');
  683 |       await expect(page).not.toHaveURL(/\/login/);
  684 |       await page.waitForLoadState('networkidle');
  685 |     },
  686 |     'abrir panel principal'
  687 |   );
  688 | }
  689 | 
  690 | async function assertMainPanelLoaded(page, caseId, role) {
  691 |   const bodyText = normalizeText(await page.locator('body').innerText());
  692 |   const hasDashboardSignal =
  693 |     bodyText.includes('panel de control')
  694 |     || bodyText.includes('bienvenido')
  695 |     || bodyText.includes('mi dashboard')
  696 |     || bodyText.includes('procesos en curso')
  697 |     || bodyText.includes('resumen');
  698 | 
  699 |   await takeEvidence(page, caseId, `${role}-panel-principal`);
  700 |   expect(hasDashboardSignal).toBeTruthy();
  701 | }
  702 | 
  703 | async function assertProcessVisibilityFromPanel(page, processCode, caseId, role, { requireVisible = false } = {}) {
  704 |   await page.goto(`/procesos?buscar=${encodeURIComponent(processCode)}`);
  705 |   await page.waitForLoadState('networkidle');
  706 | 
  707 |   const row = page.locator('tbody tr').filter({ hasText: processCode }).first();
  708 |   const isVisible = await row.isVisible({ timeout: 3000 }).catch(() => false);
  709 | 
  710 |   await takeEvidence(page, caseId, `${role}-consulta-proceso-${isVisible ? 'visible' : 'sin-registro'}`);
  711 | 
  712 |   if (requireVisible) {
  713 |     expect(isVisible).toBeTruthy();
  714 |   }
  715 | }
  716 | 
  717 | async function takeEvidence(page, caseId, label) {
  718 |   evidenceCounter += 1;
  719 |   const evidenceDir = path.join(process.cwd(), 'test-results', 'evidencias-ui');
  720 |   if (!fs.existsSync(evidenceDir)) {
  721 |     fs.mkdirSync(evidenceDir, { recursive: true });
  722 |   }
  723 | 
  724 |   const safeCase = sanitizeForPath(caseId);
  725 |   const safeLabel = sanitizeForPath(label);
  726 |   const fileName = `${String(evidenceCounter).padStart(4, '0')}-${safeCase}-${safeLabel}.png`;
  727 |   const target = path.join(evidenceDir, fileName);
  728 | 
  729 |   await page.screenshot({ path: target, fullPage: true });
  730 | }
  731 | 
  732 | function sanitizeForPath(value) {
  733 |   return String(value)
  734 |     .normalize('NFD')
  735 |     .replace(/[\u0300-\u036f]/g, '')
  736 |     .replace(/[^a-zA-Z0-9-_]+/g, '-')
  737 |     .replace(/-+/g, '-')
  738 |     .replace(/^-|-$/g, '')
  739 |     .toLowerCase();
  740 | }
  741 | 
  742 | async function selectPreferredFlow(flowSelect) {
```