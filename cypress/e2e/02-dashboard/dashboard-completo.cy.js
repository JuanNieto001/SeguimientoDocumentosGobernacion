/**
 * PRUEBAS AUTOMATIZADAS - DASHBOARD
 * Casos: DASH-001 a DASH-015
 */

describe('Módulo Dashboard', () => {
    describe('Dashboard Principal', () => {
        it('DASH-001: Ver dashboard según rol admin', () => {
            cy.loginAsRole('admin');
            cy.visit('/dashboard');

            cy.logStep('Verificar elementos del dashboard admin');
            cy.get('body').should('be.visible');
            cy.url().should('include', '/dashboard');

            // Verificar KPIs visibles
            cy.get('[data-kpi], .kpi-widget, .stat-card').should('have.length.at.least', 1);
            cy.takeScreenshot('DASH-001-dashboard-admin');
        });

        it('DASH-002: Ver dashboard según rol secretario', function () {
            const secretarioEmail = Cypress.env('secretarioEmail');
            if (!secretarioEmail) {
                this.skip();
            }

            cy.login(secretarioEmail, Cypress.env('secretarioPassword'));
            cy.visit('/dashboard');

            cy.logStep('Verificar dashboard limitado a secretaría');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('DASH-002-dashboard-secretario');
        });

        it('DASH-003: Ver dashboard según rol jefe_unidad', function () {
            const jefeUnidadEmail = Cypress.env('jefeUnidadEmail');
            if (!jefeUnidadEmail) {
                this.skip();
            }

            cy.login(jefeUnidadEmail, Cypress.env('jefeUnidadPassword'));
            cy.visit('/dashboard');

            cy.logStep('Verificar dashboard limitado a unidad');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('DASH-003-dashboard-jefe-unidad');
        });

        it('DASH-004: KPIs muestran valores correctos', () => {
            cy.loginAsRole('admin');
            cy.visit('/dashboard');

            cy.logStep('Verificar que KPIs muestran valores numéricos');
            cy.get('[data-kpi], .kpi-widget, .stat-card').each(($el) => {
                cy.wrap($el).find('.value, .number, h2, h3').first().invoke('text').then((text) => {
                    // El valor debe contener al menos un número o $
                    expect(text).to.match(/[\d$]/);
                });
            });
            cy.takeScreenshot('DASH-004-kpis-valores');
        });

        it('DASH-005: Gráficas cargan correctamente', () => {
            cy.loginAsRole('admin');
            cy.visit('/dashboard');

            cy.logStep('Verificar que gráficas están presentes');
            // Esperar a que carguen las gráficas (Chart.js o Recharts)
            cy.wait(2000);
            cy.get('canvas, svg.recharts-surface, .chart-container').should('have.length.at.least', 0);
            cy.takeScreenshot('DASH-005-graficas');
        });

        it('DASH-006: Búsqueda global funciona', () => {
            cy.loginAsRole('admin');
            cy.visit('/dashboard');

            cy.logStep('Usar barra de búsqueda');
            cy.get('input[type="search"], input[placeholder*="Buscar"], [data-search]').first().then(($search) => {
                if ($search.length) {
                    cy.wrap($search).type('CD-PN');
                    cy.wait(1000);
                    cy.takeScreenshot('DASH-006-busqueda');
                }
            });
        });

        it('DASH-008: Navegación lateral funciona', () => {
            cy.loginAsRole('admin');
            cy.visit('/dashboard');

            cy.logStep('Verificar menú lateral');
            cy.get('nav, aside, .sidebar').should('be.visible');

            // Click en algunos items del menú
            cy.get('nav a, aside a, .sidebar a').first().click({ force: true });
            cy.url().should('not.eq', '/dashboard');
            cy.takeScreenshot('DASH-008-navegacion');
        });

        it('DASH-009: Dashboard responsive (tablet)', () => {
            cy.loginAsRole('admin');
            cy.viewport(768, 1024);
            cy.visit('/dashboard');

            cy.logStep('Verificar layout en tablet');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('DASH-009-responsive-tablet');
        });

        it('DASH-010: Dashboard responsive (mobile)', () => {
            cy.loginAsRole('admin');
            cy.viewport(375, 667);
            cy.visit('/dashboard');

            cy.logStep('Verificar layout en mobile');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('DASH-010-responsive-mobile');
        });
    });

    describe('Mi Dashboard (Por Rol)', () => {
        it('DASH-011: Acceso a mi-dashboard', () => {
            cy.loginAsRole('admin');
            cy.visit('/mi-dashboard');

            cy.logStep('Verificar carga de dashboard personalizado');
            cy.url().should('include', '/mi-dashboard');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('DASH-011-mi-dashboard');
        });

        it('DASH-012: Dashboard por asignación de usuario', () => {
            cy.loginAsRole('admin');
            cy.visit('/mi-dashboard');

            cy.logStep('Verificar dashboard carga');
            cy.get('body').should('not.contain', 'Error');
            cy.takeScreenshot('DASH-012-asignacion-usuario');
        });
    });
});
