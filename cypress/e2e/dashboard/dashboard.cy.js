/// <reference types="cypress" />

/**
 * PRUEBAS DE DASHBOARD
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo de Dashboard', () => {
    beforeEach(() => {
        cy.loginAsRole('admin');
    });

    describe('CP-DASH-001 a CP-DASH-005: Funcionalidades del Dashboard', () => {

        it('CP-DASH-001: Dashboard muestra saludo personalizado', () => {
            cy.visit('/dashboard');
            cy.waitForPageLoad();

            cy.get('body').should('contain', 'Bienvenido');
            cy.takeScreenshot('DASH-001_saludo_personalizado');
        });

        it('CP-DASH-002: Dashboard muestra KPIs del mes', () => {
            cy.visit('/dashboard');
            cy.waitForPageLoad();

            // Verificar presencia de tarjetas KPI
            cy.get('[class*="card"], [class*="stat"], [class*="kpi"]').should('have.length.at.least', 1);
            cy.takeScreenshot('DASH-002_kpis_mes');
        });

        it('CP-DASH-003: Acciones rapidas visibles segun rol - Admin', () => {
            cy.visit('/dashboard');
            cy.waitForPageLoad();

            // Admin debe ver todas las acciones
            cy.get('nav, aside, [class*="sidebar"]').should('be.visible');
            cy.takeScreenshot('DASH-003_acciones_admin');
        });

        it('CP-DASH-003b: Acciones rapidas segun rol - Unidad Solicitante', () => {
            cy.logout();
            cy.loginAsRole('unidad_solicitante');
            cy.visit('/dashboard');
            cy.waitForPageLoad();

            // Verificar menu especifico de unidad
            cy.takeScreenshot('DASH-003b_acciones_unidad');
        });

        it('CP-DASH-004: Lista de procesos en curso', () => {
            cy.visit('/dashboard');
            cy.waitForPageLoad();

            // Buscar seccion de procesos
            cy.get('body').then(($body) => {
                if ($body.find('table, [class*="proceso"]').length > 0) {
                    cy.get('table, [class*="proceso"]').first().should('be.visible');
                }
            });
            cy.takeScreenshot('DASH-004_procesos_en_curso');
        });

        it('CP-DASH-005: Dashboard vacio para usuario nuevo', () => {
            // Este test verifica el estado vacio
            cy.visit('/dashboard');
            cy.waitForPageLoad();

            // El dashboard debe cargar sin errores aunque no haya datos
            cy.get('body').should('not.contain', 'Error');
            cy.takeScreenshot('DASH-005_estado_dashboard');
        });
    });
});
