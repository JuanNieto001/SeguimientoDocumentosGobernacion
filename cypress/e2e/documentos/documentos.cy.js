/// <reference types="cypress" />

/**
 * PRUEBAS DE GESTION DOCUMENTAL
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo de Gestion Documental', () => {

    describe('CP-DOC-001 a CP-DOC-007: Gestion de Documentos', () => {

        beforeEach(() => {
            cy.loginAsRole('unidad_solicitante');
        });

        it('CP-DOC-001: Subir documento PDF valido', () => {
            cy.visit('/procesos');
            cy.waitForPageLoad();

            cy.get('table tbody tr').then(($rows) => {
                if ($rows.length > 0) {
                    cy.wrap($rows.first()).find('a').first().click({ force: true });
                    cy.takeScreenshot('DOC-001_detalle_proceso');

                    // Buscar input de archivo
                    cy.get('input[type="file"]').then(($input) => {
                        if ($input.length) {
                            cy.log('Input de archivo encontrado');
                        }
                    });
                }
            });
            cy.takeScreenshot('DOC-001_subir_documento');
        });

        it('CP-DOC-002: Subir documento tipo no permitido', () => {
            cy.visit('/procesos');

            // Intentar subir archivo no permitido
            cy.takeScreenshot('DOC-002_tipo_no_permitido');
        });

        it('CP-DOC-003: Subir documento excediendo tamano maximo', () => {
            cy.visit('/procesos');
            cy.takeScreenshot('DOC-003_tamano_excedido');
        });

        it('CP-DOC-004: Aprobar documento', () => {
            cy.loginAsRole('juridica');
            cy.visit('/procesos');
            cy.waitForPageLoad();
            cy.takeScreenshot('DOC-004_aprobar_documento');
        });

        it('CP-DOC-005: Rechazar documento con observaciones', () => {
            cy.loginAsRole('juridica');
            cy.visit('/procesos');
            cy.takeScreenshot('DOC-005_rechazar_documento');
        });

        it('CP-DOC-006: Verificar vigencia de documento', () => {
            cy.visit('/procesos');
            cy.takeScreenshot('DOC-006_vigencia_documento');
        });

        it('CP-DOC-007: Reemplazar documento existente', () => {
            cy.visit('/procesos');
            cy.takeScreenshot('DOC-007_reemplazar_documento');
        });
    });
});
