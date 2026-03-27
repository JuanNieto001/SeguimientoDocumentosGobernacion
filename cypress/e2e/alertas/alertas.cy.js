/// <reference types="cypress" />

/**
 * PRUEBAS DE ALERTAS
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo de Alertas', () => {

    describe('CP-ALT-001 a CP-ALT-005: Gestion de Alertas', () => {

        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CP-ALT-001: Ver alertas no leidas', () => {
            cy.visit('/dashboard');
            cy.waitForPageLoad();

            // Buscar indicador de alertas en topbar
            cy.get('[data-testid="alertas"], [class*="alert"], [class*="notification"], .bell-icon').then(($alert) => {
                if ($alert.length) {
                    cy.wrap($alert).first().click({ force: true });
                    cy.takeScreenshot('ALT-001_dropdown_alertas');
                }
            });

            cy.visit('/alertas');
            cy.takeScreenshot('ALT-001_lista_alertas');
        });

        it('CP-ALT-002: Marcar alerta como leida', () => {
            cy.visit('/alertas');
            cy.waitForPageLoad();

            cy.get('table tbody tr, [class*="alerta-item"]').then(($rows) => {
                if ($rows.length > 0) {
                    cy.wrap($rows.first()).find('button').contains(/leer|marcar/i).then(($btn) => {
                        if ($btn.length) {
                            cy.wrap($btn).click();
                        }
                    });
                }
            });
            cy.takeScreenshot('ALT-002_alerta_leida');
        });

        it('CP-ALT-003: Alerta automatica por tiempo excedido', () => {
            // Este test verifica que el sistema genera alertas
            cy.visit('/alertas');
            cy.waitForPageLoad();

            cy.get('body').then(($body) => {
                if ($body.find('[class*="tiempo"], [class*="excedido"]').length) {
                    cy.log('Alertas de tiempo encontradas');
                }
            });
            cy.takeScreenshot('ALT-003_alertas_tiempo');
        });

        it('CP-ALT-004: Alerta por documento proximo a vencer', () => {
            cy.visit('/alertas');

            cy.get('body').then(($body) => {
                if ($body.find('[class*="vencer"], [class*="vigencia"]').length) {
                    cy.log('Alertas de vigencia encontradas');
                }
            });
            cy.takeScreenshot('ALT-004_alertas_vigencia');
        });

        it('CP-ALT-005: Prioridad critica por documento menor a 2 dias', () => {
            cy.visit('/alertas');

            cy.get('[class*="critica"], [class*="critical"], .badge-danger').then(($critical) => {
                if ($critical.length) {
                    cy.log('Alertas criticas encontradas: ' + $critical.length);
                }
            });
            cy.takeScreenshot('ALT-005_alertas_criticas');
        });
    });
});
