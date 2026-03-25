// cypress/e2e/01-authentication/roles.cy.js
/**
 * Tests de autenticación - Roles y Permisos
 *
 * Pruebas del sistema de roles y control de acceso
 * Gobernación de Caldas - Sistema de Seguimiento Contractual
 */

describe('Sistema de Roles y Permisos', () => {
  let users;
  let testConfig;

  before(() => {
    cy.fixture('users').then((usersData) => {
      users = usersData;
    });

    cy.fixture('test-config').then((config) => {
      testConfig = config;
    });

    cy.seedDatabase('TestingSeederStructure');
  });

  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  context('ACCESO POR ROL', () => {
    it('ROLE_001: Acceso Super Admin - Puede acceder a todo', () => {
      // Given: Usuario super administrador
      cy.loginAs('admin');

      // When/Then: Puede acceder a todas las secciones
      const adminSections = [
        { path: '/dashboard', selector: '[data-cy="dashboard-container"]' },
        { path: '/users', selector: '[data-cy="users-list"]' },
        { path: '/procesos', selector: '[data-cy="processes-list"]' },
        { path: '/reports', selector: '[data-cy="reports-container"]' },
        { path: '/settings', selector: '[data-cy="settings-panel"]' }
      ];

      adminSections.forEach(section => {
        cy.visit(section.path);
        cy.get(section.selector, { timeout: testConfig.test_timeouts.long })
          .should('be.visible');
      });

      // Verificar acciones administrativas disponibles
      cy.visit('/users');
      cy.get('[data-cy="create-user-btn"]').should('be.visible');
      cy.get('[data-cy="delete-user-btn"]').should('be.visible');
      cy.get('[data-cy="edit-roles-btn"]').should('be.visible');
    });

    it('ROLE_002: Acceso Gobernador - Vista ejecutiva', () => {
      // Given: Usuario con rol gobernador
      cy.loginAs('gobernador');

      // When/Then: Ve dashboard ejecutivo
      cy.url().should('include', '/dashboard');
      cy.get('[data-cy="dashboard-tipo"]').should('contain', 'Ejecutivo');

      // Puede ver procesos globales
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="processes-global-view"]').should('be.visible');

      // Puede acceder a reportes ejecutivos
      cy.get('[data-cy="nav-reports"]').click();
      cy.get('[data-cy="executive-reports"]').should('be.visible');

      // NO puede crear usuarios
      cy.visit('/users', { failOnStatusCode: false });
      cy.url().should('not.include', '/users');
      // Debería redirigir o mostrar error 403
    });

    it('ROLE_003: Acceso Secretario - Vista secretarial específica', () => {
      // Given: Usuario secretario de planeación
      cy.loginAs('secretario_planeacion');

      // When/Then: Ve dashboard secretarial filtrado
      cy.get('[data-cy="dashboard-tipo"]').should('contain', 'Secretarial');
      cy.get('[data-cy="secretaria-filter"]').should('contain', 'Planeación');

      // Ve solo procesos de su secretaría
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="processes-secretaria-filter"]')
        .should('have.value', '1'); // ID secretaría planeación

      // Puede aprobar procesos de su secretaría
      cy.get('[data-cy="process-item"]').first().click();
      cy.get('[data-cy="approve-process-btn"]').should('be.visible');

      // NO puede ver todas las secretarías
      cy.get('[data-cy="secretaria-filter"]').click();
      cy.get('[data-cy="secretaria-option"]').should('have.length.lessThan', 5);
    });

    it('ROLE_004: Acceso Coordinador Contratación - Gestión completa de procesos', () => {
      cy.loginAs('coord_contratacion');

      // Puede crear procesos
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="create-process-btn"]').should('be.visible').click();

      // Puede gestionar todas las etapas
      cy.get('[data-cy="workflow-select"]').should('be.visible');
      cy.get('[data-cy="assign-process-btn"]').should('be.visible');

      // Dashboard de gestión con carga de trabajo
      cy.get('[data-cy="nav-dashboard"]').click();
      cy.get('[data-cy="widget-carga-trabajo"]').should('be.visible');
      cy.get('[data-cy="widget-procesos-asignados"]').should('be.visible');
    });

    it('ROLE_005: Acceso Profesional Contratación - Ejecución limitada', () => {
      cy.loginAs('profesional_contratacion');

      // Puede ejecutar procesos asignados
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="my-processes-filter"]').should('be.checked');

      // Puede subir documentos
      cy.get('[data-cy="process-item"]').first().click();
      cy.get('[data-cy="upload-document"]').should('be.visible');

      // NO puede asignar procesos a otros
      cy.get('[data-cy="assign-to-user"]').should('not.exist');

      // NO puede aprobar procesos
      cy.get('[data-cy="approve-process-btn"]').should('not.exist');
    });
  });

  context('CONTROL DE ACCESO ESPECÍFICO', () => {
    it('ROLE_006: Revisor Jurídico - Solo revisión jurídica', () => {
      cy.loginAs('revisor_juridico');

      // Ve solo procesos en etapa jurídica
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="juridical-processes"]').should('be.visible');

      // Puede emitir conceptos
      cy.get('[data-cy="process-juridical"]').first().click();
      cy.get('[data-cy="emit-concept-btn"]').should('be.visible');
      cy.get('[data-cy="juridical-approval-btn"]').should('be.visible');

      // NO puede ver procesos de otras etapas
      cy.get('[data-cy="all-processes-tab"]').click();
      cy.get('[data-cy="process-item"]').should('have.length.lessThan', 10);
    });

    it('ROLE_007: Revisor Presupuestal - Solo gestión presupuestal', () => {
      cy.loginAs('revisor_presupuestal');

      // Ve procesos que requieren CDP/RPC
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="budgetary-processes"]').should('be.visible');

      // Puede expedir CDP y RPC
      cy.get('[data-cy="process-budgetary"]').first().click();
      cy.get('[data-cy="issue-cdp-btn"]').should('be.visible');
      cy.get('[data-cy="issue-rpc-btn"]').should('be.visible');

      // Tiene dashboard específico
      cy.get('[data-cy="nav-dashboard"]').click();
      cy.get('[data-cy="budget-dashboard"]').should('be.visible');
    });

    it('ROLE_008: Operador SECOP - Solo gestión SECOP II', () => {
      cy.loginAs('operador_secop');

      // Ve procesos para publicación
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="secop-ready-processes"]').should('be.visible');

      // Puede publicar en SECOP
      cy.get('[data-cy="process-secop"]').first().click();
      cy.get('[data-cy="publish-secop-btn"]').should('be.visible');
      cy.get('[data-cy="manage-secop-btn"]').should('be.visible');

      // Dashboard SECOP específico
      cy.get('[data-cy="nav-dashboard"]').click();
      cy.get('[data-cy="secop-dashboard"]').should('be.visible');
    });

    it('ROLE_009: Consulta Ciudadana - Solo información pública', () => {
      cy.loginAs('consulta_ciudadana');

      // Solo ve información pública
      cy.get('[data-cy="nav-public-processes"]').click();
      cy.get('[data-cy="public-contracts"]').should('be.visible');

      // NO ve información sensible
      cy.get('[data-cy="process-details"]').first().click();
      cy.get('[data-cy="internal-documents"]').should('not.exist');
      cy.get('[data-cy="contractor-private-info"]').should('not.exist');

      // Solo dashboard público
      cy.visit('/dashboard');
      cy.get('[data-cy="public-statistics"]').should('be.visible');
    });
  });

  context('FILTROS POR UNIDAD Y SECRETARÍA', () => {
    it('ROLE_010: Jefe Unidad - Solo ve su unidad', () => {
      cy.loginAs('jefe_unidad_sistemas');

      // Dashboard filtrado por unidad
      cy.get('[data-cy="unidad-filter"]').should('contain', 'Sistemas');

      // Procesos solo de su unidad
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="unit-filter"]').should('have.value', '5'); // ID unidad sistemas

      // Puede gestionar su equipo
      cy.get('[data-cy="team-management"]').should('be.visible');
      cy.get('[data-cy="assign-to-team"]').should('be.visible');

      // NO ve otras unidades
      cy.get('[data-cy="all-units-selector"]').should('not.exist');
    });

    it('ROLE_011: Filtros automáticos por secretaría', () => {
      // Test con secretario de hacienda
      cy.loginAs('secretario_hacienda');

      cy.get('[data-cy="secretaria-filter"]').should('contain', 'Hacienda');

      // Cambiar a secretario de planeación
      cy.logout();
      cy.loginAs('secretario_planeacion');

      cy.get('[data-cy="secretaria-filter"]').should('contain', 'Planeación');

      // Los filtros son diferentes para cada secretaría
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="secretaria-processes"]')
        .should('not.contain', 'Hacienda');
    });
  });

  context('PERMISOS ESPECÍFICOS', () => {
    it('ROLE_012: Verificar permisos granulares de creación', () => {
      const permissionTests = [
        {
          user: 'coord_contratacion',
          canCreate: ['procesos', 'documentos'],
          cannotCreate: ['usuarios', 'secretarias']
        },
        {
          user: 'profesional_contratacion',
          canCreate: ['documentos'],
          cannotCreate: ['procesos', 'usuarios']
        },
        {
          user: 'revisor_juridico',
          canCreate: ['conceptos_juridicos'],
          cannotCreate: ['procesos', 'usuarios']
        }
      ];

      permissionTests.forEach(test => {
        cy.logout();
        cy.loginAs(test.user);

        // Verificar permisos de creación
        test.canCreate.forEach(entity => {
          cy.get(`[data-cy="create-${entity}-btn"]`)
            .should('be.visible');
        });

        // Verificar restricciones
        test.cannotCreate.forEach(entity => {
          cy.get(`[data-cy="create-${entity}-btn"]`)
            .should('not.exist');
        });
      });
    });

    it('ROLE_013: Verificar permisos de edición y eliminación', () => {
      // Admin puede todo
      cy.loginAs('admin');
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="process-item"]').first().click();
      cy.get('[data-cy="edit-process-btn"]').should('be.visible');
      cy.get('[data-cy="delete-process-btn"]').should('be.visible');

      // Profesional solo puede editar procesos asignados
      cy.logout();
      cy.loginAs('profesional_contratacion');
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="my-process"]').first().click();
      cy.get('[data-cy="edit-process-btn"]').should('be.visible');
      cy.get('[data-cy="delete-process-btn"]').should('not.exist');

      // No puede editar procesos de otros
      cy.get('[data-cy="all-processes"]').click();
      cy.get('[data-cy="other-process"]').first().click();
      cy.get('[data-cy="edit-process-btn"]').should('not.exist');
    });

    it('ROLE_014: Verificar acceso a reportes por rol', () => {
      const roleReports = [
        {
          user: 'gobernador',
          reports: ['executive-summary', 'global-metrics', 'budget-overview'],
          noAccess: ['detailed-user-logs', 'system-diagnostics']
        },
        {
          user: 'secretario_planeacion',
          reports: ['secretaria-metrics', 'process-status', 'team-performance'],
          noAccess: ['global-metrics', 'other-secretaria-data']
        },
        {
          user: 'revisor_juridico',
          reports: ['juridical-processes', 'concept-history'],
          noAccess: ['budget-reports', 'global-metrics']
        }
      ];

      roleReports.forEach(test => {
        cy.logout();
        cy.loginAs(test.user);
        cy.get('[data-cy="nav-reports"]').click();

        // Verificar reportes accesibles
        test.reports.forEach(report => {
          cy.get(`[data-cy="report-${report}"]`)
            .should('be.visible')
            .should('not.be.disabled');
        });

        // Verificar reportes restringidos
        test.noAccess.forEach(report => {
          cy.get(`[data-cy="report-${report}"]`)
            .should('not.exist');
        });
      });
    });
  });

  context('CASOS EDGE Y SEGURIDAD', () => {
    it('ROLE_015: Intento de escalación de privilegios', () => {
      cy.loginAs('profesional_contratacion');

      // Intentar acceder directamente a funciones de admin
      cy.visit('/admin/users', { failOnStatusCode: false });
      cy.url().should('not.include', '/admin');

      // Intentar modificar rol via API
      cy.request({
        method: 'POST',
        url: '/api/users/role',
        body: { role: 'super_admin' },
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.be.oneOf([401, 403]);
      });
    });

    it('ROLE_016: Verificar aislamiento entre secretarías', () => {
      cy.loginAs('secretario_planeacion');

      // No puede ver procesos de otras secretarías
      cy.get('[data-cy="nav-processes"]').click();

      // Intentar acceder a proceso de otra secretaría via URL
      cy.visit('/procesos/secretaria-hacienda-001', { failOnStatusCode: false });
      cy.get('[data-cy="access-denied"]').should('be.visible');

      // Verificar en API
      cy.request({
        method: 'GET',
        url: '/api/procesos/secretaria-hacienda-001',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.be.oneOf([403, 404]);
      });
    });

    it('ROLE_017: Manejo de roles múltiples o cambios de rol', () => {
      // Simular usuario con múltiples roles
      cy.loginAs('admin');

      // Cambiar rol temporalmente
      cy.get('[data-cy="role-selector"]').select('secretario');
      cy.get('[data-cy="apply-role"]').click();

      // Verificar que la vista cambia
      cy.get('[data-cy="dashboard-tipo"]').should('contain', 'Secretarial');

      // Volver al rol admin
      cy.get('[data-cy="role-selector"]').select('super_admin');
      cy.get('[data-cy="apply-role"]').click();

      // Verificar que recupera permisos completos
      cy.get('[data-cy="admin-functions"]').should('be.visible');
    });
  });

  after(() => {
    cy.task('log', 'Roles and permissions tests completed');
  });
});