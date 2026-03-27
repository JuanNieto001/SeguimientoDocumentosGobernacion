/// <reference types="cypress" />

/**
 * PRUEBAS DE PROCESOS CONTRACTUALES
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo de Procesos Contractuales', () => {

    describe('CP-PROC-001 a CP-PROC-010: Gestion de Procesos', () => {

        beforeEach(() => {
            cy.loginAsRole('unidad_solicitante');
        });

        it('CP-PROC-001: Crear proceso exitosamente', () => {
            cy.visit('/procesos/crear');
            cy.waitForPageLoad();
            cy.takeScreenshot('PROC-001_formulario_vacio');

            cy.get('textarea[name="objeto"], input[name="objeto"]')
                .clear()
                .type('Prestacion de servicios profesionales para asesoria juridica');

            cy.get('input[name="valor_estimado"]').clear().type('25000000');
            cy.get('input[name="plazo_ejecucion"]').clear().type('6');

            cy.takeScreenshot('PROC-001_formulario_lleno');
            cy.get('button[type="submit"]').click();
            cy.takeScreenshot('PROC-001_despues_enviar');
        });

        it('CP-PROC-002: Crear proceso sin estudio previo', () => {
            cy.visit('/procesos/crear');

            cy.get('textarea[name="objeto"], input[name="objeto"]')
                .type('Proceso de prueba sin estudio previo');
            cy.get('input[name="valor_estimado"]').type('10000000');
            cy.get('input[name="plazo_ejecucion"]').type('3');

            cy.get('button[type="submit"]').click();
            cy.takeScreenshot('PROC-002_sin_estudio_previo');
        });

        it('CP-PROC-003: Crear proceso con valor negativo', () => {
            cy.visit('/procesos/crear');

            cy.get('textarea[name="objeto"], input[name="objeto"]')
                .type('Proceso con valor invalido');
            cy.get('input[name="valor_estimado"]').type('-5000000');
            cy.get('input[name="plazo_ejecucion"]').type('3');

            cy.get('button[type="submit"]').click();
            cy.takeScreenshot('PROC-003_valor_negativo');
        });

        it('CP-PROC-004: Crear proceso con objeto muy corto', () => {
            cy.visit('/procesos/crear');

            cy.get('textarea[name="objeto"], input[name="objeto"]').type('Test');
            cy.get('input[name="valor_estimado"]').type('10000000');
            cy.get('input[name="plazo_ejecucion"]').type('3');

            cy.get('button[type="submit"]').click();
            cy.takeScreenshot('PROC-004_objeto_corto');
        });

        it('CP-PROC-005: Ver detalle de proceso', () => {
            cy.visit('/procesos');
            cy.waitForPageLoad();

            cy.get('table tbody tr, [class*="proceso-item"]').then(($rows) => {
                if ($rows.length > 0) {
                    cy.wrap($rows.first()).find('a, button').first().click({ force: true });
                    cy.takeScreenshot('PROC-005_detalle_proceso');
                } else {
                    cy.log('No hay procesos para ver detalle');
                    cy.takeScreenshot('PROC-005_sin_procesos');
                }
            });
        });

        it('CP-PROC-006: Recibir proceso en area', () => {
            cy.loginAsRole('planeacion');
            cy.visit('/procesos');
            cy.waitForPageLoad();
            cy.takeScreenshot('PROC-006_bandeja_planeacion');
        });

        it('CP-PROC-007: Enviar proceso a siguiente etapa', () => {
            cy.loginAsRole('planeacion');
            cy.visit('/procesos');
            cy.waitForPageLoad();
            cy.takeScreenshot('PROC-007_lista_procesos');
        });

        it('CP-PROC-008: Enviar proceso sin completar checks', () => {
            cy.loginAsRole('planeacion');
            cy.visit('/procesos');
            cy.takeScreenshot('PROC-008_sin_completar');
        });

        it('CP-PROC-009: Devolver proceso a etapa anterior', () => {
            cy.loginAsRole('juridica');
            cy.visit('/procesos');
            cy.waitForPageLoad();
            cy.takeScreenshot('PROC-009_bandeja_juridica');
        });

        it('CP-PROC-010: Devolver proceso sin motivo', () => {
            cy.loginAsRole('juridica');
            cy.visit('/procesos');
            cy.takeScreenshot('PROC-010_verificacion');
        });
    });
});
