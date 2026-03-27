// CONFIGURACION GLOBAL E2E - CYPRESS
import './commands';

beforeEach(() => {
    Cypress.Cookies.defaults({
        preserve: ['XSRF-TOKEN', 'laravel_session', 'remember_web_*'],
    });
});

afterEach(function () {
    if (this.currentTest && this.currentTest.state === 'failed') {
        const testName = this.currentTest.title.replace(/\s+/g, '_');
        cy.screenshot(`FAILED_${testName}`, { capture: 'fullPage' });
    }
});

Cypress.on('uncaught:exception', (err) => {
    if (err.message.includes('ResizeObserver loop limit exceeded')) return false;
    if (err.message.includes('Non-Error promise rejection')) return false;
    if (err.message.includes('hydration')) return false;
    return true;
});

before(() => {
    cy.log('**Sistema de Pruebas E2E - Gobernacion de Caldas**');
});
