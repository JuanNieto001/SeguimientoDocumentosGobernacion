// cypress/e2e/03-flujo-cdpn/flujo-completo.cy.js
/**
 * Tests de Flujo CD-PN - Flujo Completo
 *
 * Pruebas del flujo optimizado de Contratación Directa Persona Natural
 * 10 etapas (0-9) con validaciones y documentos específicos
 * Gobernación de Caldas - Sistema de Seguimiento Contractual
 */

describe('Flujo CD-PN Completo - 10 Etapas Optimizado', () => {
  let users;
  let procesos;
  let documentos;
  let testConfig;
  let currentProcess;

  before(() => {
    // Cargar fixtures
    cy.fixture('users').then((data) => { users = data; });
    cy.fixture('procesos').then((data) => { procesos = data; });
    cy.fixture('documentos').then((data) => { documentos = data; });
    cy.fixture('test-config').then((data) => { testConfig = data; });

    // Preparar base de datos con seeders de testing
    cy.task('db:refresh');
    cy.seedDatabase('TestingSeederStructure');
  });

  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  context('FLUJO COMPLETO EXITOSO', () => {
    it('CDPN_001: Flujo completo básico - 10 etapas', () => {
      // ===========================================
      // SETUP: Autenticación y datos iniciales
      // ===========================================
      cy.loginAs('coord_contratacion');

      // Interceptar llamadas API críticas
      cy.intercept('POST', '/api/procesos').as('createProcess');
      cy.intercept('POST', '/api/procesos/*/advance-stage').as('advanceStage');
      cy.intercept('POST', '/api/documents/upload').as('uploadDocument');

      // ===========================================
      // ETAPA 0: IDENTIFICACIÓN DE LA NECESIDAD
      // ===========================================
      cy.log('🎯 ETAPA 0: Identificación de la Necesidad');

      cy.createTestProcess('cd_pn_basico');
      cy.wait('@createProcess');

      // Verificar proceso creado en etapa 0
      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 0')
        .should('contain', 'Identificación de la Necesidad');

      // Verificar responsable actual
      cy.get('[data-cy="current-responsible"]')
        .should('contain', 'unidad_solicitante');

      // Verificar documentos requeridos etapa 0
      cy.get('[data-cy="required-documents"]')
        .should('contain', 'Solicitud de proceso')
        .should('contain', 'Verificación PAA');

      // Subir documentos etapa 0
      cy.uploadStageDocuments(['solicitud_proceso', 'verificacion_paa']);

      // Avanzar a etapa 1
      cy.advanceProcessStage({ validateDocuments: true, addComments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 1: ELABORACIÓN DE ESTUDIOS PREVIOS
      // ===========================================
      cy.log('📋 ETAPA 1: Elaboración de Estudios Previos');

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 1')
        .should('contain', 'Elaboración de Estudios Previos');

      // Verificar tiempo estimado
      cy.get('[data-cy="estimated-time"]')
        .should('contain', '5 días hábiles');

      // Subir documentos requeridos etapa 1
      cy.uploadTestDocument('estudios_previos');
      cy.wait('@uploadDocument');

      cy.uploadTestDocument('matriz_riesgos');
      cy.wait('@uploadDocument');

      cy.uploadTestDocument('especificaciones_tecnicas');
      cy.wait('@uploadDocument');

      // Verificar validaciones automáticas
      cy.get('[data-cy="validation-estudios-completos"]')
        .should('have.class', 'validation-passed');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 2: VALIDACIÓN DEL CONTRATISTA
      // ===========================================
      cy.log('👤 ETAPA 2: Validación del Contratista');

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 2')
        .should('contain', 'Validación del Contratista');

      // Subir documentos del contratista
      cy.uploadTestDocument('hoja_vida_sigep');
      cy.uploadTestDocument('cedula_contratista');
      cy.uploadTestDocument('rut_contratista');
      cy.uploadTestDocument('certificacion_experiencia');
      cy.uploadTestDocument('diplomas_acreditacion');
      cy.uploadTestDocument('antecedentes');

      // Verificar validaciones automáticas del contratista
      cy.get('[data-cy="validation-sigep-actualizado"]')
        .should('have.class', 'validation-passed');

      cy.get('[data-cy="validation-antecedentes-limpios"]')
        .should('have.class', 'validation-passed');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 3: REVISIÓN PRESUPUESTAL
      // ===========================================
      cy.log('💰 ETAPA 3: Revisión Presupuestal');

      // Cambiar a usuario revisor presupuestal
      cy.logout();
      cy.loginAs('revisor_presupuestal');

      // Navegar al proceso
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="budgetary-processes"]').click();
      cy.get('[data-cy="process-item"]').contains('TEST-CD-PN-001-2026').click();

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 3')
        .should('contain', 'Revisión Presupuestal');

      // Subir documentos presupuestales
      cy.uploadTestDocument('solicitud_cdp');
      cy.uploadTestDocument('formato_presupuestal');
      cy.uploadTestDocument('justificacion_valor');

      // Generar CDP
      cy.get('[data-cy="generate-cdp-btn"]').click();
      cy.get('[data-cy="cdp-number"]').should('be.visible');

      // Verificar validaciones presupuestales
      cy.get('[data-cy="validation-disponibilidad-confirmada"]')
        .should('have.class', 'validation-passed');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 4: CONSOLIDACIÓN EXPEDIENTE
      // ===========================================
      cy.log('📁 ETAPA 4: Consolidación Expediente Precontractual');

      // Volver a coordinador de contratación
      cy.logout();
      cy.loginAs('coord_contratacion');

      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="process-item"]').contains('TEST-CD-PN-001-2026').click();

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 4')
        .should('contain', 'Consolidación Expediente');

      // Generar documentos precontractuales
      cy.uploadTestDocument('minuta_contrato');
      cy.uploadTestDocument('cronograma_actividades');

      // Verificar consolidación del expediente
      cy.get('[data-cy="expediente-completado"]')
        .should('have.class', 'status-complete');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 5: REVISIÓN JURÍDICA
      // ===========================================
      cy.log('⚖️ ETAPA 5: Revisión Jurídica');

      // Cambiar a revisor jurídico
      cy.logout();
      cy.loginAs('revisor_juridico');

      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="juridical-processes"]').click();
      cy.get('[data-cy="process-item"]').contains('TEST-CD-PN-001-2026').click();

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 5')
        .should('contain', 'Revisión Jurídica');

      // Emitir concepto jurídico
      cy.get('[data-cy="emit-concept-btn"]').click();
      cy.get('[data-cy="concept-text"]').type('El proceso cumple con todos los requisitos legales para contratación directa.');
      cy.get('[data-cy="concept-viable"]').check();
      cy.get('[data-cy="save-concept"]').click();

      // Verificar concepto jurídico generado
      cy.get('[data-cy="juridical-concept"]')
        .should('contain', 'Concepto Favorable');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 6: PUBLICACIÓN SECOP II
      // ===========================================
      cy.log('🌐 ETAPA 6: Publicación SECOP II');

      // Cambiar a operador SECOP
      cy.logout();
      cy.loginAs('operador_secop');

      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="secop-ready-processes"]').click();
      cy.get('[data-cy="process-item"]').contains('TEST-CD-PN-001-2026').click();

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 6')
        .should('contain', 'Publicación SECOP II');

      // Simular publicación en SECOP II
      cy.get('[data-cy="publish-secop-btn"]').click();
      cy.get('[data-cy="secop-process-number"]').should('be.visible');

      // Verificar publicación exitosa
      cy.get('[data-cy="secop-status"]')
        .should('contain', 'Publicado Exitosamente');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 7: SOLICITUD DE RPC
      // ===========================================
      cy.log('💳 ETAPA 7: Solicitud de RPC');

      // Cambiar a secretario de planeación
      cy.logout();
      cy.loginAs('secretario_planeacion');

      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="process-item"]').contains('TEST-CD-PN-001-2026').click();

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 7')
        .should('contain', 'Solicitud de RPC');

      // Solicitar RPC
      cy.get('[data-cy="request-rpc-btn"]').click();
      cy.get('[data-cy="rpc-generated"]').should('be.visible');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 8: SUSCRIPCIÓN DEL CONTRATO
      // ===========================================
      cy.log('✍️ ETAPA 8: Suscripción del Contrato');

      // Cambiar a revisor jurídico para firma
      cy.logout();
      cy.loginAs('revisor_juridico');

      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="process-item"]').contains('TEST-CD-PN-001-2026').click();

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 8')
        .should('contain', 'Suscripción del Contrato');

      // Subir garantías del contratista
      cy.uploadTestDocument('polizas_seguros');

      // Generar número de contrato
      cy.get('[data-cy="assign-contract-number"]').click();
      cy.get('[data-cy="contract-number"]').should('be.visible');

      cy.advanceProcessStage({ validateDocuments: true });
      cy.wait('@advanceStage');

      // ===========================================
      // ETAPA 9: INICIO DE EJECUCIÓN
      // ===========================================
      cy.log('🚀 ETAPA 9: Inicio de Ejecución');

      // Volver a coordinador para finalizar
      cy.logout();
      cy.loginAs('coord_contratacion');

      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="process-item"]').contains('TEST-CD-PN-001-2026').click();

      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 9')
        .should('contain', 'Inicio de Ejecución');

      // Subir documentos finales
      cy.uploadTestDocument('afiliacion_arl');
      cy.uploadTestDocument('acta_inicio');

      // Designar supervisor
      cy.get('[data-cy="designate-supervisor"]').click();
      cy.get('[data-cy="supervisor-select"]').select(users.profesional_contratacion.email);
      cy.get('[data-cy="save-supervisor"]').click();

      // Finalizar proceso
      cy.get('[data-cy="finalize-process-btn"]').click();
      cy.get('[data-cy="confirm-finalize"]').click();

      // ===========================================
      // VERIFICACIÓN FINAL
      // ===========================================
      cy.log('✅ Verificación Final del Proceso');

      // Verificar estado final
      cy.get('[data-cy="process-status"]')
        .should('contain', 'COMPLETADO');

      cy.get('[data-cy="completion-date"]').should('be.visible');

      // Verificar todos los documentos están presentes
      cy.get('[data-cy="process-documents"]').click();
      cy.get('[data-cy="document-count"]').should('contain', 'completados');

      // Verificar trazabilidad completa
      cy.get('[data-cy="process-timeline"]').click();
      cy.get('[data-cy="timeline-stage"]').should('have.length', 10);

      // Verificar que el contrato está activo
      cy.get('[data-cy="contract-status"]')
        .should('contain', 'ACTIVO');

      cy.log('🎉 Flujo CD-PN completado exitosamente en 10 etapas');
    });

    it('CDPN_002: Flujo con proceso complejo - Validaciones avanzadas', () => {
      cy.loginAs('coord_contratacion');

      // Usar proceso más complejo
      cy.createTestProcess('cd_pn_complejo');

      // Este test incluirá todos los documentos opcionales
      // y validaciones especiales definidas en el fixture
      cy.get('@createdProcess').then((proceso) => {
        expect(proceso.validaciones_especiales).to.exist;

        // Verificar validaciones especiales se ejecutan
        cy.uploadStageDocuments(['estudios_previos', 'matriz_riesgos']);

        cy.get('[data-cy="special-validation-experiencia"]')
          .should('have.class', 'validation-passed');
      });
    });
  });

  context('VALIDACIONES POR ETAPA', () => {
    it('CDPN_003: Validaciones obligatorias impiden avance', () => {
      cy.loginAs('coord_contratacion');
      cy.createTestProcess('cd_pn_basico');

      // Intentar avanzar sin documentos requeridos
      cy.get('[data-cy="advance-stage-btn"]').click();

      // Debe mostrar errores de validación
      cy.get('[data-cy="validation-errors"]')
        .should('be.visible')
        .should('contain', 'Documentos obligatorios faltantes');

      // El proceso no debe avanzar
      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 0');
    });

    it('CDPN_004: Validación de valor máximo CD-PN', () => {
      cy.loginAs('coord_contratacion');

      // Intentar crear proceso que excede límite CD-PN
      cy.createTestProcess('cd_pn_rechazado'); // Este tiene valor > 50M

      // Debe mostrar error de modalidad
      cy.get('[data-cy="modalidad-error"]')
        .should('contain', 'El valor excede el límite para Contratación Directa');
    });
  });

  context('CASOS EDGE Y ERRORES', () => {
    it('CDPN_005: Manejo de errores de red durante avance', () => {
      cy.loginAs('coord_contratacion');
      cy.createTestProcess('cd_pn_basico');

      // Simular error de red
      cy.intercept('POST', '/api/procesos/*/advance-stage', {
        statusCode: 500,
        body: { message: 'Error interno del servidor' }
      }).as('networkError');

      cy.uploadStageDocuments(['solicitud_proceso', 'verificacion_paa']);

      cy.get('[data-cy="advance-stage-btn"]').click();
      cy.wait('@networkError');

      // Verificar manejo del error
      cy.get('[data-cy="error-message"]')
        .should('contain', 'Error al avanzar el proceso');

      // El proceso debe mantenerse en la etapa actual
      cy.get('[data-cy="stage-indicator"]')
        .should('contain', 'Etapa 0');
    });

    it('CDPN_006: Recuperación después de error de subida', () => {
      cy.loginAs('coord_contratacion');
      cy.createTestProcess('cd_pn_basico');

      // Simular error en subida de documentos
      cy.intercept('POST', '/api/documents/upload', {
        statusCode: 413,
        body: { message: 'Archivo demasiado grande' }
      }).as('uploadError');

      cy.uploadTestDocument('estudios_previos');
      cy.wait('@uploadError');

      // Verificar mensaje de error
      cy.get('[data-cy="upload-error"]')
        .should('contain', 'Archivo demasiado grande');

      // Retry con archivo correcto
      cy.intercept('POST', '/api/documents/upload', {
        statusCode: 200,
        body: { success: true, document_id: 123 }
      }).as('uploadSuccess');

      cy.uploadTestDocument('estudios_previos');
      cy.wait('@uploadSuccess');

      cy.get('[data-cy="upload-success"]')
        .should('be.visible');
    });
  });

  after(() => {
    cy.task('log', 'CD-PN workflow tests completed');
  });
});