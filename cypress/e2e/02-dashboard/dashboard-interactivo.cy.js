// cypress/e2e/02-dashboard/dashboard-interactivo.cy.js
/**
 * Tests de Dashboard Interactivo - BI-Style
 *
 * Pruebas del dashboard con widgets arrastrables, filtros dinámicos
 * y visualizaciones interactivas por rol
 * Gobernación de Caldas - Sistema de Seguimiento Contractual
 */

describe('Dashboard Interactivo - BI Style', () => {
  let users;
  let testConfig;
  let dashboardData;

  before(() => {
    cy.fixture('users').then((data) => { users = data; });
    cy.fixture('test-config').then((data) => { testConfig = data; });
    cy.fixture('dashboard-data').then((data) => { dashboardData = data; });

    // Preparar base de datos con datos de dashboard
    cy.task('db:refresh');
    cy.seedDatabase('TestingSeederStructure');
  });

  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  context('CARGA Y CONFIGURACIÓN INICIAL', () => {
    it('DASH_001: Dashboard carga correctamente para Super Admin', () => {
      cy.loginAs('admin');

      // Interceptar llamadas del dashboard
      cy.intercept('GET', '/api/dashboard/config').as('dashboardConfig');
      cy.intercept('GET', '/api/dashboard/data**').as('dashboardData');
      cy.intercept('GET', '/api/dashboard/widgets**').as('dashboardWidgets');

      cy.visit('/dashboard');

      // Verificar que se cargan las APIs necesarias
      cy.wait('@dashboardConfig');
      cy.wait('@dashboardData');
      cy.wait('@dashboardWidgets');

      // Verificar elementos básicos del dashboard
      cy.get('[data-cy="dashboard-container"]', { timeout: testConfig.test_timeouts.long })
        .should('be.visible');

      cy.get('[data-cy="dashboard-tipo"]')
        .should('contain', 'Administrativo');

      // Verificar widgets principales para admin
      const adminWidgets = [
        'widget-procesos-globales',
        'widget-presupuesto-total',
        'widget-usuarios-activos',
        'widget-secretarias-overview',
        'widget-rendimiento-sistema'
      ];

      cy.verifyDashboardWidgets(adminWidgets);

      // Verificar controles de administrador
      cy.get('[data-cy="admin-controls"]').should('be.visible');
      cy.get('[data-cy="system-metrics"]').should('be.visible');
    });

    it('DASH_002: Dashboard ejecutivo para Gobernador', () => {
      cy.loginAs('gobernador');

      cy.intercept('GET', '/api/dashboard/data**').as('executiveData');

      cy.visit('/dashboard');
      cy.wait('@executiveData');

      // Verificar dashboard ejecutivo
      cy.get('[data-cy="dashboard-tipo"]')
        .should('contain', 'Ejecutivo');

      // Widgets específicos del gobernador
      const executiveWidgets = [
        'widget-resumen-ejecutivo',
        'widget-procesos-criticos',
        'widget-presupuesto-global',
        'widget-indicadores-clave',
        'widget-alertas-ejecutivas'
      ];

      cy.verifyDashboardWidgets(executiveWidgets);

      // No debe ver controles administrativos
      cy.get('[data-cy="admin-controls"]').should('not.exist');

      // Debe ver métricas ejecutivas
      cy.get('[data-cy="executive-metrics"]').should('be.visible');
    });

    it('DASH_003: Dashboard secretarial filtrado por secretaría', () => {
      cy.loginAs('secretario_planeacion');

      cy.visit('/dashboard');

      // Verificar filtro automático por secretaría
      cy.get('[data-cy="dashboard-tipo"]')
        .should('contain', 'Secretarial');

      cy.get('[data-cy="secretaria-filter"]')
        .should('contain', 'Planeación');

      // Widgets específicos de secretaría
      const secretarialWidgets = [
        'widget-procesos-secretaria',
        'widget-presupuesto-secretaria',
        'widget-equipo-trabajo',
        'widget-metas-secretaria'
      ];

      cy.verifyDashboardWidgets(secretarialWidgets);

      // Verificar que no ve información de otras secretarías
      cy.get('[data-cy="widget-procesos-secretaria"]').within(() => {
        cy.get('[data-cy="proceso-item"]').each(($el) => {
          cy.wrap($el).should('not.contain', 'Hacienda');
          cy.wrap($el).should('not.contain', 'Gobierno');
        });
      });
    });

    it('DASH_004: Dashboard operacional para roles específicos', () => {
      // Test coordinador de contratación
      cy.loginAs('coord_contratacion');
      cy.visit('/dashboard');

      cy.get('[data-cy="dashboard-tipo"]')
        .should('contain', 'Operacional');

      const operationalWidgets = [
        'widget-carga-trabajo',
        'widget-procesos-asignados',
        'widget-documentos-pendientes',
        'widget-etapas-criticas'
      ];

      cy.verifyDashboardWidgets(operationalWidgets);

      // Cambiar a revisor jurídico
      cy.logout();
      cy.loginAs('revisor_juridico');
      cy.visit('/dashboard');

      // Widget específico jurídico
      cy.get('[data-cy="widget-conceptos-pendientes"]').should('be.visible');
      cy.get('[data-cy="widget-procesos-juridicos"]').should('be.visible');
    });
  });

  context('FUNCIONALIDAD DRAG & DROP', () => {
    it('DASH_005: Arrastrar widgets cambia el layout', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Interceptar guardado de layout
      cy.intercept('POST', '/api/dashboard/save-layout').as('saveLayout');

      // Verificar posición inicial
      cy.get('[data-cy="widget-procesos-globales"]')
        .should('have.attr', 'data-position', '0');

      cy.get('[data-cy="widget-presupuesto-total"]')
        .should('have.attr', 'data-position', '1');

      // Arrastrar widget
      cy.get('[data-cy="widget-procesos-globales"]')
        .trigger('dragstart');

      cy.get('[data-cy="drop-zone-2"]')
        .trigger('dragover')
        .trigger('drop');

      // Verificar que el layout se guarda
      cy.wait('@saveLayout').then((interception) => {
        expect(interception.request.body).to.have.property('layout');
        expect(interception.response.statusCode).to.equal(200);
      });

      // Verificar nueva posición
      cy.get('[data-cy="layout-saved-message"]')
        .should('be.visible')
        .should('contain', 'Layout guardado exitosamente');

      cy.get('[data-cy="widget-procesos-globales"]')
        .should('have.attr', 'data-position', '2');
    });

    it('DASH_006: Layout persiste después de logout/login', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Cambiar layout
      cy.dragWidget('[data-cy="widget-usuarios-activos"]', '[data-cy="drop-zone-0"]');

      // Hacer logout y login nuevamente
      cy.logout();
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Verificar que el layout persiste
      cy.get('[data-cy="widget-usuarios-activos"]')
        .should('have.attr', 'data-position', '0');
    });

    it('DASH_007: Widgets no se pueden arrastrar para roles sin permisos', () => {
      cy.loginAs('consulta_ciudadana');
      cy.visit('/dashboard');

      // Los widgets no deben ser draggables
      cy.get('[data-cy="widget-procesos-publicos"]')
        .should('not.have.class', 'draggable')
        .should('not.have.attr', 'draggable', 'true');

      // No debe existir el botón de personalizar
      cy.get('[data-cy="customize-dashboard"]').should('not.exist');
    });
  });

  context('FILTROS DINÁMICOS', () => {
    it('DASH_008: Filtros por rango de fechas actualizan widgets', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Intercept para filtros
      cy.intercept('GET', '/api/dashboard/data**').as('filteredData');

      // Aplicar filtro de fechas
      cy.applyDashboardFilters({
        dateRange: 'last-30-days'
      });

      cy.wait('@filteredData');

      // Verificar que los widgets se actualizan
      cy.get('[data-cy="widget-procesos-globales"]').within(() => {
        cy.get('[data-cy="filter-applied-badge"]')
          .should('contain', 'Últimos 30 días');
      });

      // Verificar que los números cambian
      cy.get('[data-cy="total-procesos-count"]')
        .invoke('text')
        .should('not.be.empty');
    });

    it('DASH_009: Filtro por secretaría para roles autorizados', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/dashboard/data**').as('secretariaData');

      // El admin puede ver todas las secretarías
      cy.get('[data-cy="secretaria-filter"]').click();
      cy.get('[data-cy="secretaria-option"]').should('have.length.gte', 5);

      // Filtrar por secretaría específica
      cy.get('[data-cy="secretaria-option"]')
        .contains('Planeación')
        .click();

      cy.get('[data-cy="apply-filters-btn"]').click();
      cy.wait('@secretariaData');

      // Verificar filtro aplicado
      cy.get('[data-cy="active-filters"]')
        .should('contain', 'Secretaría: Planeación');
    });

    it('DASH_010: Filtro por unidad solo para jefes de unidad', () => {
      cy.loginAs('jefe_unidad_sistemas');
      cy.visit('/dashboard');

      // Debe ver filtro de unidad pre-seleccionado
      cy.get('[data-cy="unidad-filter"]')
        .should('contain', 'Sistemas');

      // No puede cambiar a otras unidades
      cy.get('[data-cy="unidad-filter"]').click();
      cy.get('[data-cy="unidad-option"]').should('have.length', 1);
    });

    it('DASH_011: Combinación de múltiples filtros', () => {
      cy.loginAs('secretario_hacienda');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/dashboard/data**').as('multiFilterData');

      // Aplicar múltiples filtros
      cy.applyDashboardFilters({
        dateRange: 'this-quarter',
        secretaria: 'hacienda',
        status: 'en_proceso'
      });

      cy.wait('@multiFilterData');

      // Verificar que todos los filtros se aplican
      cy.get('[data-cy="active-filters"]').within(() => {
        cy.should('contain', 'Este trimestre');
        cy.should('contain', 'Secretaría: Hacienda');
        cy.should('contain', 'Estado: En Proceso');
      });

      // Limpiar filtros
      cy.get('[data-cy="clear-filters-btn"]').click();
      cy.get('[data-cy="active-filters"]').should('not.exist');
    });
  });

  context('WIDGETS INTERACTIVOS', () => {
    it('DASH_012: Widget de presupuesto muestra drill-down', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.get('[data-cy="widget-presupuesto-total"]').within(() => {
        // Verificar datos principales
        cy.get('[data-cy="presupuesto-total-amount"]').should('be.visible');
        cy.get('[data-cy="presupuesto-ejecutado"]').should('be.visible');
        cy.get('[data-cy="presupuesto-disponible"]').should('be.visible');

        // Click en el widget para drill-down
        cy.get('[data-cy="presupuesto-drill-down"]').click();
      });

      // Verificar modal de detalle
      cy.get('[data-cy="presupuesto-detail-modal"]').should('be.visible');
      cy.get('[data-cy="presupuesto-by-secretaria"]').should('be.visible');
      cy.get('[data-cy="presupuesto-timeline"]').should('be.visible');

      // Cerrar modal
      cy.get('[data-cy="close-modal-btn"]').click();
      cy.get('[data-cy="presupuesto-detail-modal"]').should('not.exist');
    });

    it('DASH_013: Widget de procesos permite navegación directa', () => {
      cy.loginAs('coord_contratacion');
      cy.visit('/dashboard');

      cy.get('[data-cy="widget-procesos-asignados"]').within(() => {
        // Debe mostrar procesos asignados al usuario
        cy.get('[data-cy="proceso-item"]').should('have.length.gte', 1);

        // Click en un proceso específico
        cy.get('[data-cy="proceso-item"]').first().within(() => {
          cy.get('[data-cy="proceso-codigo"]').invoke('text').as('procesoCode');
          cy.get('[data-cy="ver-proceso-btn"]').click();
        });
      });

      // Verificar navegación al proceso
      cy.get('@procesoCode').then((codigo) => {
        cy.url().should('include', `/procesos/${codigo}`);
        cy.get('[data-cy="proceso-header"]').should('contain', codigo);
      });
    });

    it('DASH_014: Widget de alertas muestra notificaciones críticas', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.get('[data-cy="widget-alertas-sistema"]').within(() => {
        // Verificar tipos de alertas
        cy.get('[data-cy="alertas-criticas"]').should('be.visible');
        cy.get('[data-cy="alertas-warnings"]').should('be.visible');

        // Click en alerta crítica
        cy.get('[data-cy="alerta-item"]').first().within(() => {
          cy.get('[data-cy="alerta-severity"]').should('contain', 'Crítica');
          cy.get('[data-cy="alerta-action-btn"]').click();
        });
      });

      // Verificar navegación a la acción correspondiente
      cy.get('[data-cy="alert-action-modal"]').should('be.visible');
      cy.get('[data-cy="resolve-alert-btn"]').should('be.visible');
    });
  });

  context('RESPONSIVE Y PERFORMANCE', () => {
    it('DASH_015: Dashboard es responsive en diferentes tamaños', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Test en diferentes viewports
      const viewports = [
        { width: 375, height: 667, name: 'mobile' },
        { width: 768, height: 1024, name: 'tablet' },
        { width: 1366, height: 768, name: 'desktop' },
        { width: 1920, height: 1080, name: 'full-hd' }
      ];

      viewports.forEach(viewport => {
        cy.viewport(viewport.width, viewport.height);

        // Verificar que el dashboard se adapta
        cy.get('[data-cy="dashboard-container"]').should('be.visible');

        if (viewport.width < 768) {
          // Mobile: widgets en columna única
          cy.get('[data-cy="dashboard-grid"]')
            .should('have.class', 'mobile-layout');

          // Menú colapsado en mobile
          cy.get('[data-cy="mobile-menu-toggle"]').should('be.visible');
        } else {
          // Desktop: grid completo
          cy.get('[data-cy="dashboard-grid"]')
            .should('have.class', 'desktop-layout');
        }

        cy.screenshotWithName(`dashboard_${viewport.name}`);
      });
    });

    it('DASH_016: Dashboard carga en tiempo aceptable', () => {
      cy.loginAs('admin');

      const startTime = Date.now();
      cy.visit('/dashboard');

      cy.get('[data-cy="dashboard-container"]').should('be.visible');

      cy.then(() => {
        const loadTime = Date.now() - startTime;
        cy.task('log', `Dashboard load time: ${loadTime}ms`);

        // Verificar que carga en menos de 3 segundos
        expect(loadTime).to.be.lessThan(testConfig.performance_thresholds.dashboard_render_time);
      });
    });

    it('DASH_017: Widgets cargan de forma progresiva', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Verificar loading states
      cy.get('[data-cy="widget-skeleton"]').should('exist');

      // Los widgets deben cargar uno por uno
      cy.get('[data-cy="widget-procesos-globales"]')
        .should('have.class', 'loading')
        .should('not.have.class', 'loading', { timeout: 10000 });

      cy.get('[data-cy="widget-presupuesto-total"]')
        .should('have.class', 'loading')
        .should('not.have.class', 'loading', { timeout: 10000 });

      // Al final, no debe haber skeletons
      cy.get('[data-cy="widget-skeleton"]').should('not.exist');
    });
  });

  context('PERSONALIZACIÓN Y CONFIGURACIÓN', () => {
    it('DASH_018: Admin puede configurar widgets disponibles', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Abrir configuración de widgets
      cy.get('[data-cy="configure-widgets-btn"]').click();
      cy.get('[data-cy="widget-config-modal"]').should('be.visible');

      // Verificar lista de widgets disponibles
      cy.get('[data-cy="available-widgets-list"]').within(() => {
        cy.get('[data-cy="widget-toggle"]').should('have.length.gte', 8);

        // Desactivar un widget
        cy.get('[data-cy="widget-toggle-usuarios"]').uncheck();
      });

      // Guardar configuración
      cy.get('[data-cy="save-widget-config"]').click();
      cy.get('[data-cy="config-saved-message"]').should('be.visible');

      // Verificar que el widget se oculta
      cy.get('[data-cy="widget-usuarios-activos"]').should('not.exist');
    });

    it('DASH_019: Configuración de refresh automático', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/dashboard/data**').as('autoRefresh');

      // Configurar auto-refresh
      cy.get('[data-cy="auto-refresh-toggle"]').check();
      cy.get('[data-cy="refresh-interval"]').select('30'); // 30 segundos

      // Esperar auto-refresh simulated behavior is difficult
      // pero podemos verificar que la opción está disponible
      cy.get('[data-cy="auto-refresh-status"]')
        .should('contain', 'Actualización automática: Activa');
    });

    it('DASH_020: Export de datos del dashboard', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Test export a Excel
      cy.get('[data-cy="export-dashboard-btn"]').click();
      cy.get('[data-cy="export-format-xlsx"]').click();

      // Verificar descarga (en test environment, verificamos que se inicia)
      cy.get('[data-cy="download-started-message"]')
        .should('contain', 'Descarga iniciada');

      // Test export a PDF
      cy.get('[data-cy="export-dashboard-btn"]').click();
      cy.get('[data-cy="export-format-pdf"]').click();

      cy.get('[data-cy="export-options"]').within(() => {
        cy.get('[data-cy="include-charts"]').check();
        cy.get('[data-cy="include-filters"]').check();
        cy.get('[data-cy="export-confirm-btn"]').click();
      });

      cy.get('[data-cy="download-started-message"]').should('be.visible');
    });
  });

  context('CASOS EDGE Y MANEJO DE ERRORES', () => {
    it('DASH_021: Manejo de errores en carga de widgets', () => {
      cy.loginAs('admin');

      // Simular error en API de dashboard
      cy.intercept('GET', '/api/dashboard/data**', {
        statusCode: 500,
        body: { message: 'Error interno del servidor' }
      }).as('dashboardError');

      cy.visit('/dashboard');
      cy.wait('@dashboardError');

      // Verificar manejo del error
      cy.get('[data-cy="dashboard-error"]')
        .should('be.visible')
        .should('contain', 'Error al cargar el dashboard');

      cy.get('[data-cy="retry-dashboard-btn"]').should('be.visible');

      // Test de retry
      cy.intercept('GET', '/api/dashboard/data**', { fixture: 'dashboard-data' }).as('dashboardRetry');
      cy.get('[data-cy="retry-dashboard-btn"]').click();
      cy.wait('@dashboardRetry');

      cy.get('[data-cy="dashboard-container"]').should('be.visible');
    });

    it('DASH_022: Datos faltantes no rompen el dashboard', () => {
      cy.loginAs('admin');

      // Simular respuesta con datos parciales
      cy.intercept('GET', '/api/dashboard/data**', {
        body: {
          procesos: [], // Sin datos de procesos
          presupuesto: null, // Sin datos de presupuesto
          usuarios: { total: 0 }
        }
      }).as('emptyData');

      cy.visit('/dashboard');
      cy.wait('@emptyData');

      // Los widgets deben mostrar estados vacíos, no errores
      cy.get('[data-cy="widget-procesos-globales"]').within(() => {
        cy.get('[data-cy="no-data-message"]').should('contain', 'No hay datos disponibles');
      });

      cy.get('[data-cy="widget-presupuesto-total"]').within(() => {
        cy.get('[data-cy="no-data-message"]').should('be.visible');
      });
    });

    it('DASH_023: Timeout en carga de widgets específicos', () => {
      cy.loginAs('admin');

      // Simular timeout en widget específico
      cy.intercept('GET', '/api/dashboard/widgets/presupuesto', {
        delay: 35000 // Más que el timeout
      }).as('widgetTimeout');

      cy.visit('/dashboard');

      // Otros widgets deben cargar normalmente
      cy.get('[data-cy="widget-procesos-globales"]')
        .should('not.have.class', 'loading');

      // Widget con timeout debe mostrar error específico
      cy.get('[data-cy="widget-presupuesto-total"]').within(() => {
        cy.get('[data-cy="widget-timeout-error"]', { timeout: 40000 })
          .should('contain', 'Tiempo de espera agotado');

        cy.get('[data-cy="retry-widget-btn"]').should('be.visible');
      });
    });
  });

  after(() => {
    cy.task('log', 'Interactive dashboard tests completed');
  });
});