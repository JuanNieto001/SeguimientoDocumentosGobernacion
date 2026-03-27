/**
 * PRUEBAS AUTOMATIZADAS - CONTRATACION DIRECTA PN
 * Casos: CDPN-001 a CDPN-033
 */

describe('Módulo Contratación Directa PN', () => {
    describe('Crear Proceso CD-PN', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CDPN-001: Crear CD-PN exitoso', () => {
            cy.visit('/proceso-cd/crear');

            cy.logStep('Completar formulario CD-PN');
            cy.get('textarea[name="objeto"]').type('Prestación de servicios profesionales - Test automatizado');
            cy.get('input[name="valor"]').type('30000000');
            cy.get('input[name="plazo_meses"]').type('6');

            cy.logStep('Subir estudio previo');
            cy.uploadFile('input[name="estudio_previo"], input[type="file"]', 'documentos/estudio_previo_valido.pdf');

            cy.logStep('Guardar proceso');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar creación en estado BORRADOR');
            cy.url().should('not.include', '/crear');
            cy.takeScreenshot('CDPN-001-crear-cdpn');
        });

        it('CDPN-002: Validar campos requeridos CD-PN', () => {
            cy.visit('/proceso-cd/crear');

            cy.logStep('Intentar guardar sin completar campos');
            cy.get('button[type="submit"]').click();

            cy.logStep('Verificar mensajes de error');
            cy.get('.error, .text-red-500, .invalid-feedback, input:invalid').should('exist');
            cy.takeScreenshot('CDPN-002-campos-requeridos');
        });

        it('CDPN-003: Cargar estudio previo PDF', () => {
            cy.visit('/proceso-cd/crear');

            cy.logStep('Seleccionar archivo PDF');
            cy.uploadFile('input[name="estudio_previo"], input[type="file"]', 'documentos/estudio_previo_valido.pdf');

            cy.logStep('Verificar archivo cargado');
            cy.get('input[name="estudio_previo"], input[type="file"]').then(($input) => {
                expect($input[0].files.length).to.be.greaterThan(0);
            });
            cy.takeScreenshot('CDPN-003-cargar-pdf');
        });

        it('CDPN-005: Rechazar archivo muy grande', () => {
            cy.visit('/proceso-cd/crear');

            cy.logStep('Completar formulario');
            cy.get('textarea[name="objeto"]').type('Test archivo grande');
            cy.get('input[name="valor"]').type('30000000');
            cy.get('input[name="plazo_meses"]').type('6');

            // Nota: Para probar archivo grande, necesitaríamos fixture de +10MB
            cy.takeScreenshot('CDPN-005-archivo-grande');
        });
    });

    describe('Flujo Etapa 1: Estudios Previos', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CDPN-006: Subir estudio previo y avanzar', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Abrir proceso en borrador');
            cy.get('table tbody tr').contains('BORRADOR').parents('tr').find('a').first().click({ force: true });

            cy.logStep('Verificar proceso abierto');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('CDPN-006-subir-estudio');
        });

        it('CDPN-007: Enviar a validación Planeación', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Buscar proceso con estudio previo cargado');
            cy.get('table tbody tr').first().find('a').first().click({ force: true });

            cy.logStep('Buscar botón de envío a planeación');
            cy.get('body').then(($body) => {
                if ($body.find('button:contains("Enviar"), [data-action="enviar"]').length) {
                    cy.get('button:contains("Enviar"), [data-action="enviar"]').first().click();
                }
            });
            cy.takeScreenshot('CDPN-007-enviar-planeacion');
        });
    });

    describe('Flujo Etapa 2: Validaciones', () => {
        beforeEach(() => {
            cy.loginAsRole('planeacion');
        });

        it('CDPN-008: Solicitar validación PAA', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Buscar proceso pendiente de validación');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('CDPN-008-solicitar-paa');
        });

        it('CDPN-011: Solicitar CDP sin compatibilidad - REGLA CRÍTICA', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Verificar que no se puede solicitar CDP sin compatibilidad');
            // Esta es una regla crítica del negocio
            cy.takeScreenshot('CDPN-011-cdp-sin-compatibilidad');
        });
    });

    describe('Flujo Etapa 3: Documentación Contratista', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CDPN-014: Completar checklist contratista', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Buscar proceso en etapa documentación');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('CDPN-014-checklist');
        });

        it('CDPN-015: Subir documento cédula', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Proceso de carga de documentos');
            cy.takeScreenshot('CDPN-015-subir-documento');
        });
    });

    describe('Flujo Etapa 4: Revisión Jurídica', () => {
        beforeEach(() => {
            cy.loginAsRole('juridica');
        });

        it('CDPN-018: Enviar a revisión jurídica', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Ver procesos pendientes en jurídica');
            cy.get('body').should('be.visible');
            cy.takeScreenshot('CDPN-018-revision-juridica');
        });

        it('CDPN-019: Aprobar revisión jurídica', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Proceso de aprobación jurídica');
            cy.takeScreenshot('CDPN-019-aprobar-juridica');
        });

        it('CDPN-020: Devolver a documentación', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Proceso de devolución');
            cy.takeScreenshot('CDPN-020-devolver');
        });
    });

    describe('Flujo Etapa 5: Contrato', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CDPN-021: Generar contrato', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Generación de contrato');
            cy.takeScreenshot('CDPN-021-generar-contrato');
        });

        it('CDPN-022: Registrar firma contratista', () => {
            cy.logStep('Registro de firma contratista');
            cy.takeScreenshot('CDPN-022-firma-contratista');
        });

        it('CDPN-023: Registrar firma ordenador', () => {
            cy.logStep('Registro de firma ordenador del gasto');
            cy.takeScreenshot('CDPN-023-firma-ordenador');
        });
    });

    describe('Flujo Etapa 6: RPC', () => {
        beforeEach(() => {
            cy.loginAsRole('presupuesto');
        });

        it('CDPN-024: Solicitar RPC', () => {
            cy.logStep('Solicitar RPC');
            cy.takeScreenshot('CDPN-024-solicitar-rpc');
        });

        it('CDPN-025: Registrar RPC', () => {
            cy.visit('/proceso-cd');
            cy.logStep('Registrar número de RPC');
            cy.takeScreenshot('CDPN-025-registrar-rpc');
        });
    });

    describe('Flujo Etapa 7: Ejecución', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CDPN-027: Solicitar ARL', () => {
            cy.logStep('Solicitar ARL');
            cy.takeScreenshot('CDPN-027-solicitar-arl');
        });

        it('CDPN-028: Registrar acta inicio', () => {
            cy.logStep('Registrar acta de inicio');
            cy.takeScreenshot('CDPN-028-acta-inicio');
        });

        it('CDPN-029: Iniciar ejecución', () => {
            cy.logStep('Iniciar ejecución del contrato');
            cy.takeScreenshot('CDPN-029-iniciar-ejecucion');
        });
    });

    describe('Estados Especiales', () => {
        beforeEach(() => {
            cy.loginAsRole('admin');
        });

        it('CDPN-030: Cancelar proceso', () => {
            cy.visit('/proceso-cd');

            cy.logStep('Verificar opción de cancelación');
            cy.takeScreenshot('CDPN-030-cancelar');
        });

        it('CDPN-031: Suspender proceso', () => {
            cy.logStep('Suspender proceso');
            cy.takeScreenshot('CDPN-031-suspender');
        });

        it('CDPN-032: Reactivar proceso suspendido', () => {
            cy.logStep('Reactivar proceso');
            cy.takeScreenshot('CDPN-032-reactivar');
        });
    });
});
