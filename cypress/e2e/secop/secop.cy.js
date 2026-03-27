/// <reference types="cypress" />

/**
 * PRUEBAS DE INTEGRACION SECOP
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo SECOP', () => {

    describe('CP-SEC-001 a CP-SEC-003: Consulta SECOP II', () => {

        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CP-SEC-001: Buscar contrato por referencia', () => {
            cy.visit('/secop');
            cy.waitForPageLoad();
            cy.takeScreenshot('SEC-001_pagina_secop');

            // Buscar campo de busqueda
            cy.get('input[type="search"], input[name="buscar"], input[placeholder*="buscar"]').then(($input) => {
                if ($input.length) {
                    cy.wrap($input).type('CD-001-2026');
                    cy.get('button').contains(/buscar/i).click();
                    cy.takeScreenshot('SEC-001_resultados_busqueda');
                }
            });
        });

        it('CP-SEC-002: Ver estadisticas SECOP', () => {
            cy.visit('/secop');
            cy.waitForPageLoad();

            cy.get('button, a').contains(/estadisticas/i).then(($btn) => {
                if ($btn.length) {
                    cy.wrap($btn).click();
                    cy.takeScreenshot('SEC-002_estadisticas');
                }
            });
        });

        it('CP-SEC-003: Manejar timeout de API SECOP', () => {
            // Interceptar llamada a SECOP para simular timeout
            cy.intercept('GET', '**/api/secop/**', {
                statusCode: 504,
                body: { error: 'Gateway Timeout' }
            }).as('secopTimeout');

            cy.visit('/secop');
            cy.takeScreenshot('SEC-003_manejo_timeout');
        });
    });
});
