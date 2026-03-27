// COMANDOS PERSONALIZADOS - CYPRESS
// Sistema de Seguimiento de Documentos Contractuales

// ========== AUTENTICACION ==========

Cypress.Commands.add('login', (email, password) => {
    cy.session([email, password], () => {
        cy.visit('/login');
        cy.get('input[name="email"]').clear().type(email);
        cy.get('input[name="password"]').clear().type(password);
        cy.get('button[type="submit"]').click();
        cy.url().should('not.include', '/login');
    });
});

Cypress.Commands.add('loginApi', (email, password) => {
    cy.session([email, password], () => {
        cy.request('GET', '/sanctum/csrf-cookie').then(() => {
            cy.request({
                method: 'POST',
                url: '/login',
                body: { email, password },
                failOnStatusCode: false,
            });
        });
    });
});

Cypress.Commands.add('loginAsRole', (role) => {
    const creds = {
        admin: { email: Cypress.env('adminEmail'), password: Cypress.env('adminPassword') },
        unidad_solicitante: { email: Cypress.env('unidadEmail'), password: Cypress.env('unidadPassword') },
        planeacion: { email: Cypress.env('planeacionEmail'), password: Cypress.env('planeacionPassword') },
        hacienda: { email: Cypress.env('haciendaEmail'), password: Cypress.env('haciendaPassword') },
        juridica: { email: Cypress.env('juridicaEmail'), password: Cypress.env('juridicaPassword') },
        secop: { email: Cypress.env('secopEmail'), password: Cypress.env('secopPassword') },
        gobernador: { email: Cypress.env('gobernadorEmail'), password: Cypress.env('gobernadorPassword') },
        consulta: { email: Cypress.env('consultaEmail'), password: Cypress.env('consultaPassword') },
    }[role];
    if (!creds) throw new Error(`Rol no configurado: ${role}`);
    cy.loginApi(creds.email, creds.password);
});

Cypress.Commands.add('logout', () => {
    cy.request({ method: 'POST', url: '/logout', failOnStatusCode: false });
    Cypress.session.clearAllSavedSessions();
});

// ========== NAVEGACION ==========

Cypress.Commands.add('goToDashboard', () => {
    cy.visit('/dashboard');
    cy.url().should('include', '/dashboard');
});

Cypress.Commands.add('goToProcesos', () => {
    cy.visit('/procesos');
});

Cypress.Commands.add('goToCrearProceso', () => {
    cy.visit('/procesos/crear');
});

Cypress.Commands.add('goToProcesoCD', () => {
    cy.visit('/proceso-cd');
});

// ========== FORMULARIOS ==========

Cypress.Commands.add('fillField', (selector, value) => {
    cy.get(selector).clear().type(value);
});

Cypress.Commands.add('selectOption', (selector, value) => {
    cy.get(selector).select(value);
});

Cypress.Commands.add('uploadFile', (selector, filePath, mimeType = 'application/pdf') => {
    cy.get(selector).selectFile(`cypress/fixtures/${filePath}`, { force: true, mimeType });
});

Cypress.Commands.add('checkFieldError', (fieldName, errorMessage) => {
    cy.get(`[data-error="${fieldName}"], .error-${fieldName}, #${fieldName}-error, .text-red-500`)
        .should('contain', errorMessage);
});

// ========== PROCESOS ==========

Cypress.Commands.add('crearProceso', (data) => {
    cy.goToCrearProceso();
    if (data.flujoId) cy.get('select[name="flujo_id"]').select(data.flujoId);
    cy.get('textarea[name="objeto"], input[name="objeto"]').clear().type(data.objeto);
    if (data.descripcion) cy.get('textarea[name="descripcion"]').clear().type(data.descripcion);
    cy.get('input[name="valor_estimado"]').clear().type(data.valorEstimado.toString());
    cy.get('input[name="plazo_ejecucion"]').clear().type(data.plazoEjecucion.toString());
    if (data.estudioPrevio) cy.uploadFile('input[name="estudio_previo"]', data.estudioPrevio);
    if (data.secretariaId) cy.get('select[name="secretaria_id"]').select(data.secretariaId.toString());
    if (data.unidadId) cy.get('select[name="unidad_id"]').select(data.unidadId.toString());
    cy.get('button[type="submit"]').click();
});

Cypress.Commands.add('crearProcesoCD', (data) => {
    cy.visit('/proceso-cd/crear');
    cy.get('textarea[name="objeto"]').clear().type(data.objeto);
    cy.get('input[name="valor"]').clear().type(data.valor.toString());
    cy.get('input[name="plazo_meses"]').clear().type(data.plazoMeses.toString());
    if (data.estudioPrevio) cy.uploadFile('input[name="estudio_previo"]', data.estudioPrevio);
    if (data.secretariaId) cy.get('select[name="secretaria_id"]').select(data.secretariaId.toString());
    if (data.unidadId) cy.get('select[name="unidad_id"]').select(data.unidadId.toString());
    cy.get('button[type="submit"]').click();
});

// ========== VERIFICACION ==========

Cypress.Commands.add('checkAccessDenied', (url) => {
    cy.request({ url, failOnStatusCode: false }).then((res) => {
        expect(res.status).to.eq(403);
    });
});

Cypress.Commands.add('checkRedirectToLogin', (url) => {
    cy.visit(url);
    cy.url().should('include', '/login');
});

Cypress.Commands.add('takeScreenshot', (name) => {
    const ts = new Date().toISOString().replace(/[:.]/g, '-');
    cy.screenshot(`${name}_${ts}`);
});

Cypress.Commands.add('checkSuccessToast', (message) => {
    cy.get('.toast-success, [data-toast="success"], .alert-success').should('contain', message);
});

Cypress.Commands.add('checkErrorToast', (message) => {
    cy.get('.toast-error, [data-toast="error"], .alert-danger').should('contain', message);
});

// ========== API ==========

Cypress.Commands.add('apiGet', (url) => {
    return cy.request({ method: 'GET', url: `${Cypress.env('apiUrl')}${url}` });
});

Cypress.Commands.add('apiPost', (url, body) => {
    return cy.request({ method: 'POST', url: `${Cypress.env('apiUrl')}${url}`, body });
});

// ========== UTILIDADES ==========

Cypress.Commands.add('waitForPageLoad', () => {
    cy.get('body').should('be.visible');
    cy.window().its('document.readyState').should('eq', 'complete');
});

Cypress.Commands.add('cleanSession', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    Cypress.session.clearAllSavedSessions();
});

Cypress.Commands.add('logStep', (message) => {
    cy.log(`**PASO**: ${message}`);
    cy.task('log', `PASO: ${message}`);
});
