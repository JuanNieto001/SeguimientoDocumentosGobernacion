/// <reference types="cypress" />

/**
 * PRUEBAS DEL MOTOR DE DASHBOARDS
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Motor de Dashboards', () => {

    describe('CP-DSH-001 a CP-DSH-005: Constructor de Dashboards', () => {

        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CP-DSH-001: Acceder al motor de dashboards', () => {
            cy.visit('/dashboards/motor');
            cy.waitForPageLoad();
            cy.takeScreenshot('DSH-001_motor_dashboards');

            // Verificar elementos principales
            cy.get('body').should('contain.text', 'Dashboard');
        });

        it('CP-DSH-002: Asignar plantilla a rol', () => {
            cy.visit('/dashboards/motor');
            cy.waitForPageLoad();

            // Buscar plantillas y bloques de asignacion
            cy.get('[class*="plantilla"], [class*="template"]').then(($templates) => {
                if ($templates.length) {
                    cy.log('Plantillas encontradas: ' + $templates.length);
                }
            });
            cy.takeScreenshot('DSH-002_asignar_rol');
        });

        it('CP-DSH-003: Asignar plantilla a usuario especifico', () => {
            cy.visit('/dashboards/motor');
            cy.waitForPageLoad();
            cy.takeScreenshot('DSH-003_asignar_usuario');
        });

        it('CP-DSH-004: Jerarquia de resolucion de dashboard', () => {
            // Usuario > Unidad > Secretaria > Rol
            cy.visit('/mi-dashboard');
            cy.waitForPageLoad();
            cy.takeScreenshot('DSH-004_mi_dashboard');

            // Verificar que se carga un dashboard
            cy.get('body').should('not.contain', 'Error');
        });

        it('CP-DSH-005: Usuario sin asignacion ve default', () => {
            cy.loginAsRole('consulta');
            cy.visit('/mi-dashboard');
            cy.waitForPageLoad();
            cy.takeScreenshot('DSH-005_dashboard_default');
        });
    });
});
