/**
 * PRUEBAS AUTOMATIZADAS - AUTENTICACION
 * Casos: AUTH-001 a AUTH-011
 */

describe('Módulo Autenticación', () => {
    beforeEach(() => {
        cy.cleanSession();
    });

    describe('Login', () => {
        beforeEach(() => {
            cy.visit('/login');
        });

        it('AUTH-001: Login exitoso con credenciales válidas', () => {
            cy.logStep('Ingresar credenciales válidas');
            cy.get('input[name="email"]').type(Cypress.env('adminEmail'));
            cy.get('input[name="password"]').type(Cypress.env('adminPassword'));

            cy.logStep('Click en Iniciar Sesión');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar redirección a dashboard');
            cy.url().should('include', '/dashboard');
            cy.takeScreenshot('AUTH-001-login-exitoso');
        });

        it('AUTH-002: Login fallido con email incorrecto', () => {
            cy.logStep('Ingresar email inexistente');
            cy.get('input[name="email"]').type('noexiste@test.com');
            cy.get('input[name="password"]').type('password123');

            cy.logStep('Click en Iniciar Sesión');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar mensaje de error');
            cy.url().should('include', '/login');
            cy.get('body').should('contain.text', 'credenciales');
            cy.takeScreenshot('AUTH-002-email-incorrecto');
        });

        it('AUTH-003: Login fallido con contraseña incorrecta', () => {
            cy.logStep('Ingresar contraseña incorrecta');
            cy.get('input[name="email"]').type(Cypress.env('adminEmail'));
            cy.get('input[name="password"]').type('wrongpassword');

            cy.logStep('Click en Iniciar Sesión');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar permanece en login');
            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-003-password-incorrecto');
        });

        it('AUTH-004: Login fallido con campos vacíos', () => {
            cy.logStep('Intentar login sin completar campos');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar validación HTML5');
            cy.get('input[name="email"]:invalid').should('exist');
            cy.takeScreenshot('AUTH-004-campos-vacios');
        });

        it('AUTH-005: Login fallido con usuario inactivo', () => {
            cy.logStep('Intentar login con usuario inactivo');
            cy.get('input[name="email"]').type(Cypress.env('inactivoEmail') || 'inactivo@test.com');
            cy.get('input[name="password"]').type('password123');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar mensaje de usuario desactivado');
            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-005-usuario-inactivo');
        });

        it('AUTH-006: Login con Recordarme', () => {
            cy.logStep('Marcar checkbox Recordarme');
            cy.get('input[name="email"]').type(Cypress.env('adminEmail'));
            cy.get('input[name="password"]').type(Cypress.env('adminPassword'));
            cy.get('input[name="remember"]').check({ force: true });

            cy.logStep('Iniciar sesión');
            cy.get('button[type="submit"]').click();
            cy.url().should('include', '/dashboard');
            cy.takeScreenshot('AUTH-006-recordarme');
        });

        it('AUTH-007: Redirección según rol (planeación)', function () {
            const planeacionEmail = Cypress.env('planeacionEmail');
            if (!planeacionEmail) {
                this.skip();
            }

            cy.logStep('Login como usuario planeación');
            cy.get('input[name="email"]').type(planeacionEmail);
            cy.get('input[name="password"]').type(Cypress.env('planeacionPassword'));
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar redirección');
            cy.url().should('satisfy', (url) => {
                return url.includes('/planeacion') || url.includes('/dashboard');
            });
            cy.takeScreenshot('AUTH-007-redireccion-rol');
        });

        it('AUTH-008: Formato email inválido', () => {
            cy.logStep('Ingresar email con formato inválido');
            cy.get('input[name="email"]').type('emailsinformato');
            cy.get('input[name="password"]').type('password123');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar validación de formato');
            cy.get('input[name="email"]:invalid').should('exist');
            cy.takeScreenshot('AUTH-008-email-invalido');
        });
    });

    describe('Logout', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
            cy.visit('/dashboard');
        });

        it('AUTH-009: Logout exitoso', () => {
            cy.logStep('Hacer click en logout');
            cy.get('[data-logout], a[href*="logout"], form[action*="logout"] button').first().click({ force: true });

            cy.logStep('Verificar redirección a login');
            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-009-logout-exitoso');
        });

        it('AUTH-010: Acceso ruta protegida sin sesión', () => {
            cy.logStep('Cerrar sesión');
            cy.logout();

            cy.logStep('Intentar acceder a dashboard');
            cy.visit('/dashboard', { failOnStatusCode: false });

            cy.logStep('Verificar redirección a login');
            cy.url().should('include', '/login');
            cy.takeScreenshot('AUTH-010-ruta-protegida');
        });

        it('AUTH-011: Regeneración token sesión', () => {
            cy.logStep('Obtener session ID antes de logout y re-login');
            cy.getCookie('laravel_session').then((cookie1) => {
                const sessionId1 = cookie1?.value;

                cy.logout();
                cy.loginAsRole('admin');
                cy.visit('/dashboard');

                cy.getCookie('laravel_session').then((cookie2) => {
                    const sessionId2 = cookie2?.value;
                    expect(sessionId2).to.not.equal(sessionId1);
                });
            });
            cy.takeScreenshot('AUTH-011-regeneracion-token');
        });
    });
});
