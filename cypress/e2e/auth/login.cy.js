/// <reference types="cypress" />

/**
 * PRUEBAS DE AUTENTICACION
 * Sistema de Seguimiento de Documentos Contractuales
 * Gobernacion de Caldas
 */

describe('Modulo de Autenticacion', () => {
    beforeEach(() => {
        cy.cleanSession();
    });

    describe('CP-AUTH-001 a CP-AUTH-010: Login y Logout', () => {

        it('CP-AUTH-001: Login exitoso con credenciales validas', () => {
            cy.visit('/login');
            cy.takeScreenshot('AUTH-001_pagina_login');

            cy.get('input[name="email"]').type(Cypress.env('adminEmail'));
            cy.get('input[name="password"]').type(Cypress.env('adminPassword'));
            cy.takeScreenshot('AUTH-001_credenciales_ingresadas');

            cy.get('button[type="submit"]').click();

            cy.url().should('include', '/dashboard');
            cy.takeScreenshot('AUTH-001_login_exitoso');

            // Verificar elementos del dashboard
            cy.get('body').should('contain', 'Bienvenido');
        });

        it('CP-AUTH-002: Login fallido por email incorrecto', () => {
            cy.visit('/login');

            cy.get('input[name="email"]').type('noexiste@test.com');
            cy.get('input[name="password"]').type('cualquier123');
            cy.get('button[type="submit"]').click();

            cy.url().should('include', '/login');
            cy.get('body').should('contain.text', 'credenciales');
            cy.takeScreenshot('AUTH-002_email_incorrecto');
        });

        it('CP-AUTH-003: Login fallido por password incorrecto', () => {
            cy.visit('/login');

            cy.get('input[name="email"]').type(Cypress.env('adminEmail'));
            cy.get('input[name="password"]').type('passwordincorrecto');
            cy.get('button[type="submit"]').click();

            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-003_password_incorrecto');
        });

        it('CP-AUTH-004: Login fallido por usuario inactivo', () => {
            // Este test requiere un usuario inactivo en la BD
            cy.visit('/login');

            cy.get('input[name="email"]').type('inactivo@test.com');
            cy.get('input[name="password"]').type('Test1234!');
            cy.get('button[type="submit"]').click();

            // Debe permanecer en login o mostrar mensaje de cuenta desactivada
            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-004_usuario_inactivo');
        });

        it('CP-AUTH-005: Login con campos vacios', () => {
            cy.visit('/login');

            cy.get('button[type="submit"]').click();

            // Verificar validacion HTML5 o mensajes de error
            cy.get('input[name="email"]:invalid').should('exist');
            cy.takeScreenshot('AUTH-005_campos_vacios');
        });

        it('CP-AUTH-006: Login con email formato invalido', () => {
            cy.visit('/login');

            cy.get('input[name="email"]').type('emailsinformato');
            cy.get('input[name="password"]').type('Test1234!');
            cy.get('button[type="submit"]').click();

            // Verificar que no avanza o muestra error de formato
            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-006_email_invalido');
        });

        it('CP-AUTH-007: Logout exitoso', () => {
            // Primero hacer login
            cy.loginAsRole('admin');
            cy.visit('/dashboard');

            // Buscar y hacer click en logout
            cy.get('[data-testid="user-menu"], .user-dropdown, [aria-label="Menu usuario"]')
                .first()
                .click({ force: true });

            cy.contains('Cerrar sesion', { matchCase: false }).click({ force: true });

            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-007_logout_exitoso');
        });

        it('CP-AUTH-008: Acceso a ruta protegida sin autenticacion', () => {
            cy.cleanSession();

            // Intentar acceder a rutas protegidas
            cy.visit('/dashboard', { failOnStatusCode: false });
            cy.url().should('include', '/login');

            cy.visit('/procesos', { failOnStatusCode: false });
            cy.url().should('include', '/login');

            cy.visit('/admin/usuarios', { failOnStatusCode: false });
            cy.url().should('include', '/login');

            cy.takeScreenshot('AUTH-008_rutas_protegidas');
        });

        it('CP-AUTH-009: Remember me funcional', () => {
            cy.visit('/login');

            cy.get('input[name="email"]').type(Cypress.env('adminEmail'));
            cy.get('input[name="password"]').type(Cypress.env('adminPassword'));

            // Marcar checkbox "recuerdame" si existe
            cy.get('input[name="remember"]').check({ force: true });

            cy.get('button[type="submit"]').click();
            cy.url().should('include', '/dashboard');

            // Verificar cookie de remember
            cy.getCookie('remember_web_*').should('exist');
            cy.takeScreenshot('AUTH-009_remember_me');
        });

        it('CP-AUTH-010: Redireccion post-login segun rol - Admin', () => {
            cy.loginAsRole('admin');
            cy.visit('/dashboard');
            cy.url().should('include', '/dashboard');
            cy.takeScreenshot('AUTH-010_redireccion_admin');
        });
    });
});
