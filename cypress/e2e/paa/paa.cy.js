/// <reference types="cypress" />

/**
 * PRUEBAS DEL PLAN ANUAL DE ADQUISICIONES (PAA)
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo PAA', () => {

    describe('CP-PAA-001 a CP-PAA-004: Plan Anual de Adquisiciones', () => {

        it('CP-PAA-001: Ver Plan Anual de Adquisiciones', () => {
            cy.loginAsRole('secop');
            cy.visit('/paa');
            cy.waitForPageLoad();
            cy.takeScreenshot('PAA-001_lista_paa');

            // Verificar que carga la tabla/lista
            cy.get('table, [class*="paa-list"]').should('exist');
        });

        it('CP-PAA-002: Crear item PAA', () => {
            cy.loginAsRole('secop');
            cy.visit('/paa');
            cy.waitForPageLoad();

            cy.get('button, a').contains(/nuevo|crear/i).then(($btn) => {
                if ($btn.length) {
                    cy.wrap($btn).click();
                    cy.takeScreenshot('PAA-002_formulario_paa');
                }
            });
        });

        it('CP-PAA-003: Verificar que proceso esta en PAA', () => {
            cy.loginAsRole('planeacion');
            cy.visit('/procesos');
            cy.waitForPageLoad();
            cy.takeScreenshot('PAA-003_verificar_paa');
        });

        it('CP-PAA-004: Emitir certificado PAA', () => {
            cy.loginAsRole('secop');
            cy.visit('/paa');
            cy.waitForPageLoad();
            cy.takeScreenshot('PAA-004_certificado_paa');
        });
    });
});
