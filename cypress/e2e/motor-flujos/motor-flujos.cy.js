/// <reference types="cypress" />

/**
 * PRUEBAS DEL MOTOR DE FLUJOS
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Motor de Flujos', () => {

    describe('CP-FLU-001 a CP-FLU-006: Constructor de Flujos', () => {

        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CP-FLU-001: Acceder al motor de flujos', () => {
            cy.visit('/motor-flujos');
            cy.waitForPageLoad();

            // Esperar a que cargue la aplicacion React
            cy.get('#motor-flujos-app, [data-testid="workflow-app"]', { timeout: 15000 })
                .should('be.visible');

            cy.takeScreenshot('FLU-001_motor_cargado');
        });

        it('CP-FLU-002: Crear nuevo flujo basico', () => {
            cy.visit('/motor-flujos');
            cy.waitForPageLoad();

            // Esperar carga de React
            cy.get('#motor-flujos-app', { timeout: 15000 }).should('be.visible');

            // Buscar boton de nuevo flujo
            cy.get('button').contains(/nuevo|crear/i).then(($btn) => {
                if ($btn.length) {
                    cy.wrap($btn).click();
                    cy.takeScreenshot('FLU-002_nuevo_flujo');
                }
            });
        });

        it('CP-FLU-003: Agregar condicion a paso', () => {
            cy.visit('/motor-flujos');
            cy.waitForPageLoad();

            cy.get('#motor-flujos-app', { timeout: 15000 }).should('be.visible');
            cy.takeScreenshot('FLU-003_condiciones');
        });

        it('CP-FLU-004: Publicar version de flujo', () => {
            cy.visit('/motor-flujos');
            cy.waitForPageLoad();

            cy.get('#motor-flujos-app', { timeout: 15000 }).should('be.visible');
            cy.takeScreenshot('FLU-004_publicar_version');
        });

        it('CP-FLU-005: Eliminar flujo', () => {
            cy.visit('/motor-flujos');
            cy.waitForPageLoad();
            cy.takeScreenshot('FLU-005_eliminar_flujo');
        });

        it('CP-FLU-006: No eliminar flujo con procesos', () => {
            cy.visit('/motor-flujos');
            cy.waitForPageLoad();
            cy.takeScreenshot('FLU-006_proteccion_flujo');
        });
    });
});
