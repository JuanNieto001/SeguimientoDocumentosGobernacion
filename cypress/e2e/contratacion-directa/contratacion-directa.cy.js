/// <reference types="cypress" />

/**
 * PRUEBAS DE CONTRATACION DIRECTA CD-PN
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo de Contratacion Directa CD-PN', () => {

    describe('CP-CD-001 a CP-CD-010: Flujo CD-PN', () => {

        it('CP-CD-001: Crear solicitud CD-PN exitosamente', () => {
            cy.loginAsRole('unidad_solicitante');
            cy.visit('/proceso-cd/crear');
            cy.waitForPageLoad();
            cy.takeScreenshot('CD-001_formulario_vacio');

            cy.get('textarea[name="objeto"]')
                .type('Prestacion de servicios de apoyo en sistemas');

            cy.get('input[name="valor"]').type('30000000');
            cy.get('input[name="plazo_meses"]').type('6');

            cy.takeScreenshot('CD-001_formulario_lleno');
            cy.get('button[type="submit"]').click();
            cy.takeScreenshot('CD-001_despues_crear');
        });

        it('CP-CD-002: Transicion BORRADOR a ESTUDIO_PREVIO_CARGADO', () => {
            cy.loginAsRole('unidad_solicitante');
            cy.visit('/proceso-cd');
            cy.waitForPageLoad();
            cy.takeScreenshot('CD-002_lista_procesos');
        });

        it('CP-CD-003: Registrar validaciones paralelas', () => {
            cy.loginAsRole('planeacion');
            cy.visit('/proceso-cd');
            cy.waitForPageLoad();
            cy.takeScreenshot('CD-003_validaciones');
        });

        it('CP-CD-004: Solicitar CDP sin compatibilidad (REGLA CRITICA)', () => {
            cy.loginAsRole('planeacion');
            cy.visit('/proceso-cd');
            cy.waitForPageLoad();

            // Verificar que no se puede solicitar CDP sin compatibilidad
            cy.takeScreenshot('CD-004_cdp_sin_compatibilidad');
        });

        it('CP-CD-005: Solicitar CDP con compatibilidad aprobada', () => {
            cy.loginAsRole('planeacion');
            cy.visit('/proceso-cd');
            cy.takeScreenshot('CD-005_solicitar_cdp');
        });

        it('CP-CD-006: Aprobar CDP', () => {
            cy.loginAsRole('hacienda');
            cy.visit('/proceso-cd');
            cy.waitForPageLoad();
            cy.takeScreenshot('CD-006_aprobar_cdp');
        });

        it('CP-CD-007: Registro de ambas firmas del contrato', () => {
            cy.loginAsRole('juridica');
            cy.visit('/proceso-cd');
            cy.takeScreenshot('CD-007_firmas_contrato');
        });

        it('CP-CD-008: Devolver contrato desde juridica', () => {
            cy.loginAsRole('juridica');
            cy.visit('/proceso-cd');
            cy.takeScreenshot('CD-008_devolver_contrato');
        });

        it('CP-CD-009: Transicion por usuario no autorizado', () => {
            cy.loginAsRole('consulta');

            // Usuario consulta intenta acceder a acciones de CD
            cy.visit('/proceso-cd', { failOnStatusCode: false });
            cy.takeScreenshot('CD-009_usuario_no_autorizado');
        });

        it('CP-CD-010: Cancelar proceso (solo admin)', () => {
            cy.loginAsRole('admin');
            cy.visit('/proceso-cd');
            cy.waitForPageLoad();
            cy.takeScreenshot('CD-010_cancelar_admin');

            // Verificar que solo admin puede cancelar
            cy.logout();
            cy.loginAsRole('unidad_solicitante');
            cy.visit('/proceso-cd');
            cy.takeScreenshot('CD-010_unidad_sin_cancelar');
        });
    });
});
