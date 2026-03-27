/// <reference types="cypress" />

/**
 * PRUEBAS DE REPORTES
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo de Reportes', () => {

    describe('CP-REP-001 a CP-REP-003: Generacion de Reportes', () => {

        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CP-REP-001: Generar reporte de estado general', () => {
            cy.visit('/reportes');
            cy.waitForPageLoad();
            cy.takeScreenshot('REP-001_pagina_reportes');

            // Buscar opcion de reporte
            cy.get('select, [class*="reporte-tipo"]').then(($select) => {
                if ($select.length) {
                    cy.wrap($select).first().select('estado_general');
                }
            });

            cy.get('button').contains(/generar/i).then(($btn) => {
                if ($btn.length) {
                    cy.wrap($btn).click();
                    cy.takeScreenshot('REP-001_reporte_generado');
                }
            });
        });

        it('CP-REP-002: Exportar reporte a Excel', () => {
            cy.visit('/reportes');
            cy.waitForPageLoad();

            cy.get('button, a').contains(/excel|exportar/i).then(($btn) => {
                if ($btn.length) {
                    // Interceptar descarga
                    cy.intercept('GET', '**/exportar**').as('exportar');
                    cy.wrap($btn).click();
                    cy.takeScreenshot('REP-002_exportar_excel');
                }
            });
        });

        it('CP-REP-003: Reporte vacio sin datos', () => {
            cy.visit('/reportes');
            cy.waitForPageLoad();

            // Aplicar filtros muy restrictivos
            cy.get('input[type="date"]').first().then(($date) => {
                if ($date.length) {
                    cy.wrap($date).type('1990-01-01');
                }
            });

            cy.get('button').contains(/generar/i).then(($btn) => {
                if ($btn.length) {
                    cy.wrap($btn).click();

                    // Verificar mensaje de sin datos
                    cy.get('body').should('contain.text', /sin datos|no hay|vacio/i);
                    cy.takeScreenshot('REP-003_sin_datos');
                }
            });
        });
    });
});
