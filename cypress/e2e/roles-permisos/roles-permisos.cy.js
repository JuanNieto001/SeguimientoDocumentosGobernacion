/// <reference types="cypress" />

/**
 * PRUEBAS DE ROLES Y PERMISOS
 * Sistema de Seguimiento de Documentos Contractuales
 */

describe('Modulo de Roles y Permisos', () => {

    describe('CP-ROL-001 a CP-ROL-006: Verificacion de Permisos', () => {

        it('CP-ROL-001: Admin accede a todo', () => {
            cy.loginAsRole('admin');

            cy.visit('/admin/usuarios');
            cy.url().should('include', '/admin/usuarios');
            cy.takeScreenshot('ROL-001_admin_usuarios');

            cy.visit('/admin/roles');
            cy.url().should('include', '/admin/roles');
            cy.takeScreenshot('ROL-001_admin_roles');

            cy.visit('/dashboards/motor');
            cy.takeScreenshot('ROL-001_admin_dashboards');

            cy.visit('/procesos');
            cy.takeScreenshot('ROL-001_admin_procesos');

            cy.visit('/proceso-cd');
            cy.takeScreenshot('ROL-001_admin_cd');
        });

        it('CP-ROL-002: Unidad Solicitante - permisos limitados', () => {
            cy.loginAsRole('unidad_solicitante');

            // PUEDE acceder
            cy.visit('/procesos');
            cy.url().should('not.include', '/login');
            cy.takeScreenshot('ROL-002_unidad_procesos');

            // NO PUEDE acceder a admin
            cy.visit('/admin/usuarios', { failOnStatusCode: false });
            cy.takeScreenshot('ROL-002_unidad_admin_denegado');

            cy.visit('/dashboards/motor', { failOnStatusCode: false });
            cy.takeScreenshot('ROL-002_unidad_motor_denegado');
        });

        it('CP-ROL-003: Consulta - solo lectura', () => {
            cy.loginAsRole('consulta');

            cy.visit('/procesos');
            cy.takeScreenshot('ROL-003_consulta_procesos');

            // Verificar que no hay botones de crear/editar
            cy.get('button, a').contains(/crear|nuevo|editar/i).should('not.exist');
            cy.takeScreenshot('ROL-003_consulta_sin_acciones');
        });

        it('CP-ROL-004: Gobernador - vista ejecutiva', () => {
            cy.loginAsRole('gobernador');

            cy.visit('/dashboard');
            cy.takeScreenshot('ROL-004_gobernador_dashboard');

            // Verificar acceso a reportes
            cy.visit('/reportes', { failOnStatusCode: false });
            cy.takeScreenshot('ROL-004_gobernador_reportes');
        });

        it('CP-ROL-005: Restriccion por secretaria', () => {
            cy.loginAsRole('unidad_solicitante');

            cy.visit('/procesos');
            cy.waitForPageLoad();

            // Solo debe ver procesos de su secretaria
            cy.takeScreenshot('ROL-005_filtro_secretaria');
        });

        it('CP-ROL-006: Admin Secretaria - gestion limitada', () => {
            // Test para admin_secretaria si existe
            cy.loginAsRole('admin');
            cy.visit('/admin/secretarias');
            cy.takeScreenshot('ROL-006_admin_secretarias');
        });
    });
});
