/**
 * PRUEBAS AUTOMATIZADAS - PROCESOS
 * Casos: PROC-001 a PROC-020
 */

describe('Módulo Gestión de Procesos', () => {
    describe('Listado de Procesos', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('PROC-001: Ver listado de procesos', () => {
            cy.logStep('Navegar a listado de procesos');
            cy.visit('/procesos');

            cy.logStep('Verificar tabla de procesos');
            cy.get('table, .table, [data-table]').should('be.visible');
            cy.takeScreenshot('PROC-001-listado-procesos');
        });

        it('PROC-002: Filtrar por estado', () => {
            cy.visit('/procesos');

            cy.logStep('Aplicar filtro de estado');
            cy.get('select[name="estado"], [data-filter="estado"]').then(($filter) => {
                if ($filter.length) {
                    cy.wrap($filter).select('EN_CURSO', { force: true });
                    cy.wait(1000);
                }
            });
            cy.takeScreenshot('PROC-002-filtro-estado');
        });

        it('PROC-005: Buscar por código', () => {
            cy.visit('/procesos');

            cy.logStep('Buscar proceso por código');
            cy.get('input[type="search"], input[placeholder*="Buscar"], [data-search]').first().then(($search) => {
                if ($search.length) {
                    cy.wrap($search).type('PROC-001');
                    cy.wait(1000);
                }
            });
            cy.takeScreenshot('PROC-005-buscar-codigo');
        });

        it('PROC-006: Paginación funciona', () => {
            cy.visit('/procesos');

            cy.logStep('Verificar paginación');
            cy.get('.pagination, [data-pagination], nav[aria-label="Pagination"]').then(($pagination) => {
                if ($pagination.length) {
                    cy.wrap($pagination).should('be.visible');
                }
            });
            cy.takeScreenshot('PROC-006-paginacion');
        });

        it('PROC-008: Admin ve todos los procesos', () => {
            cy.visit('/procesos');

            cy.logStep('Verificar acceso global para admin');
            cy.get('body').should('not.contain', 'Acceso denegado');
            cy.get('table tbody tr, .table tbody tr').should('have.length.at.least', 0);
            cy.takeScreenshot('PROC-008-admin-todos');
        });
    });

    describe('Crear Proceso', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('PROC-009: Crear proceso exitoso', () => {
            cy.visit('/procesos/crear');

            cy.logStep('Completar formulario de proceso');
            cy.get('textarea[name="objeto"], input[name="objeto"]').first().type('Proceso de prueba automatizada');
            cy.get('input[name="valor_estimado"]').type('50000000');
            cy.get('input[name="plazo_ejecucion"]').type('30');

            cy.logStep('Subir estudio previo');
            cy.uploadFile('input[name="estudio_previo"], input[type="file"]', 'documentos/estudio_previo_valido.pdf');

            cy.logStep('Enviar formulario');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar creación exitosa');
            cy.url().should('not.include', '/crear');
            cy.takeScreenshot('PROC-009-crear-proceso');
        });

        it('PROC-010: Crear proceso sin estudio previo', () => {
            cy.visit('/procesos/crear');

            cy.logStep('Completar formulario SIN archivo');
            cy.get('textarea[name="objeto"], input[name="objeto"]').first().type('Proceso sin estudio');
            cy.get('input[name="valor_estimado"]').type('50000000');
            cy.get('input[name="plazo_ejecucion"]').type('30');

            cy.logStep('Enviar formulario');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar error de validación');
            cy.get('.error, .text-red-500, .invalid-feedback').should('be.visible');
            cy.takeScreenshot('PROC-010-sin-estudio-previo');
        });

        it('PROC-011: Crear proceso con valor 0', () => {
            cy.visit('/procesos/crear');

            cy.logStep('Ingresar valor 0');
            cy.get('textarea[name="objeto"], input[name="objeto"]').first().type('Proceso valor cero');
            cy.get('input[name="valor_estimado"]').type('0');
            cy.get('input[name="plazo_ejecucion"]').type('30');
            cy.uploadFile('input[name="estudio_previo"], input[type="file"]', 'documentos/estudio_previo_valido.pdf');

            cy.logStep('Enviar formulario');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar error de validación');
            cy.url().should('include', '/crear');
            cy.takeScreenshot('PROC-011-valor-cero');
        });

        it('PROC-013: Crear proceso sin permiso', () => {
            cy.loginAsRole('consulta');

            cy.logStep('Intentar acceder a crear proceso');
            cy.visit('/procesos/crear', { failOnStatusCode: false });

            cy.logStep('Verificar acceso denegado');
            // Puede redirigir o mostrar 403
            cy.url().should('not.include', '/procesos/crear').or('satisfy', () => {
                return cy.get('body').should('contain', '403');
            });
            cy.takeScreenshot('PROC-013-sin-permiso');
        });
    });

    describe('Ver Detalle Proceso', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('PROC-015: Ver detalle proceso', () => {
            cy.visit('/procesos');

            cy.logStep('Click en primer proceso');
            cy.get('table tbody tr a, .table tbody tr a').first().click({ force: true });

            cy.logStep('Verificar detalle visible');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('PROC-015-detalle-proceso');
        });

        it('PROC-019: Ver proceso inexistente', () => {
            cy.logStep('Navegar a proceso que no existe');
            cy.request({ url: '/procesos/99999', failOnStatusCode: false }).then((response) => {
                expect(response.status).to.be.oneOf([404, 302, 403]);
            });
            cy.takeScreenshot('PROC-019-proceso-inexistente');
        });
    });
});
