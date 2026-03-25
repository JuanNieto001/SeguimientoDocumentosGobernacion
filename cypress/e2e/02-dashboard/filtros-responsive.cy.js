// cypress/e2e/02-dashboard/filtros-responsive.cy.js
/**
 * Tests de Dashboard - Filtros y Responsive Design
 *
 * Pruebas específicas de filtros dinámicos, responsive design
 * y personalización avanzada del dashboard
 * Gobernación de Caldas - Sistema de Seguimiento Contractual
 */

describe('Dashboard - Filtros y Responsive Design', () => {
  let users;
  let testConfig;
  let dashboardData;

  before(() => {
    cy.fixture('users').then((data) => { users = data; });
    cy.fixture('test-config').then((data) => { testConfig = data; });
    cy.fixture('dashboard-data').then((data) => { dashboardData = data; });

    cy.task('db:refresh');
    cy.seedDatabase('TestingSeederStructure');
  });

  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  context('FILTROS AVANZADOS', () => {
    it('FILT_001: Filtro de rango de fechas personalizado', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/dashboard/data**').as('customDateFilter');

      // Abrir selector de fechas personalizado
      cy.get('[data-cy="date-filter"]').click();
      cy.get('[data-cy="date-custom"]').click();

      // Configurar fechas personalizadas
      cy.get('[data-cy="date-from"]').type('2026-01-01');
      cy.get('[data-cy="date-to"]').type('2026-03-24');
      cy.get('[data-cy="apply-custom-dates"]').click();

      cy.wait('@customDateFilter');

      // Verificar filtro aplicado
      cy.get('[data-cy="active-filters"]')
        .should('contain', '01/01/2026 - 24/03/2026');

      // Verificar que los datos del widget cambian
      cy.get('[data-cy="widget-procesos-globales"]').within(() => {
        cy.get('[data-cy="date-range-label"]')
          .should('contain', 'Período: 01/01/2026 - 24/03/2026');
      });
    });

    it('FILT_002: Filtro combinado: Secretaría + Estado + Fecha', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/dashboard/data**').as('combinedFilter');

      // Aplicar filtros múltiples
      cy.get('[data-cy="filters-panel"]').within(() => {
        // Filtro de secretaría
        cy.get('[data-cy="secretaria-filter"]').select('planeacion');

        // Filtro de estado
        cy.get('[data-cy="estado-filter"]').select('etapa_3');

        // Filtro de fecha
        cy.get('[data-cy="date-filter"]').click();
        cy.get('[data-cy="date-this-month"]').click();

        // Aplicar todos
        cy.get('[data-cy="apply-filters-btn"]').click();
      });

      cy.wait('@combinedFilter');

      // Verificar filtros activos
      cy.get('[data-cy="active-filters"]').within(() => {
        cy.should('contain', 'Secretaría: Planeación');
        cy.should('contain', 'Estado: Revisión Presupuestal');
        cy.should('contain', 'Período: Este mes');
      });

      // Verificar URL con parámetros
      cy.url().should('include', 'secretaria=planeacion');
      cy.url().should('include', 'estado=etapa_3');
      cy.url().should('include', 'fecha=this-month');
    });

    it('FILT_003: Guardado y carga de filtros favoritos', () => {
      cy.loginAs('secretario_planeacion');
      cy.visit('/dashboard');

      // Configurar filtros específicos
      cy.applyDashboardFilters({
        dateRange: 'last-30-days',
        secretaria: 'planeacion',
        status: 'criticos'
      });

      // Guardar como filtro favorito
      cy.get('[data-cy="save-filter-btn"]').click();
      cy.get('[data-cy="filter-name-input"]').type('Mi Vista Mensual');
      cy.get('[data-cy="save-favorite-filter"]').click();

      cy.get('[data-cy="filter-saved-message"]')
        .should('contain', 'Filtro guardado como favorito');

      // Limpiar filtros
      cy.get('[data-cy="clear-all-filters"]').click();

      // Aplicar filtro favorito
      cy.get('[data-cy="favorite-filters"]').click();
      cy.get('[data-cy="favorite-filter"]')
        .contains('Mi Vista Mensual')
        .click();

      // Verificar que los filtros se aplicaron
      cy.get('[data-cy="active-filters"]').within(() => {
        cy.should('contain', 'Últimos 30 días');
        cy.should('contain', 'Planeación');
        cy.should('contain', 'Críticos');
      });
    });

    it('FILT_004: Filtros por valor presupuestal', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/dashboard/data**').as('budgetFilter');

      // Abrir filtros avanzados
      cy.get('[data-cy="advanced-filters-btn"]').click();

      cy.get('[data-cy="budget-filter-section"]').within(() => {
        // Filtro por rango de valor
        cy.get('[data-cy="valor-min"]').type('50000000'); // 50M
        cy.get('[data-cy="valor-max"]').type('200000000'); // 200M

        // Filtro por tipo de presupuesto
        cy.get('[data-cy="budget-type"]').select('inversion');

        cy.get('[data-cy="apply-budget-filter"]').click();
      });

      cy.wait('@budgetFilter');

      // Verificar filtro de presupuesto
      cy.get('[data-cy="active-filters"]')
        .should('contain', 'Valor: $50M - $200M')
        .should('contain', 'Tipo: Inversión');

      // Verificar que los procesos mostrados cumplen el filtro
      cy.get('[data-cy="widget-procesos-globales"]').within(() => {
        cy.get('[data-cy="proceso-item"]').each(($proceso) => {
          cy.wrap($proceso).find('[data-cy="proceso-valor"]').then($valor => {
            const valor = parseInt($valor.text().replace(/[^0-9]/g, ''));
            expect(valor).to.be.gte(50000000);
            expect(valor).to.be.lte(200000000);
          });
        });
      });
    });

    it('FILT_005: Auto-completado en filtros de búsqueda', () => {
      cy.loginAs('coord_contratacion');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/search/suggestions**').as('suggestions');

      // Test búsqueda de procesos
      cy.get('[data-cy="search-processes"]').click();
      cy.get('[data-cy="process-search-input"]').type('CD-PN');

      cy.wait('@suggestions');

      // Verificar sugerencias
      cy.get('[data-cy="search-suggestions"]').should('be.visible');
      cy.get('[data-cy="suggestion-item"]').should('have.length.gte', 3);

      // Seleccionar sugerencia
      cy.get('[data-cy="suggestion-item"]').first().click();

      // Verificar que se aplica el filtro
      cy.get('[data-cy="search-filter-applied"]').should('be.visible');
      cy.get('[data-cy="active-filters"]').should('contain', 'Búsqueda:');
    });
  });

  context('RESPONSIVE DESIGN DETALLADO', () => {
    it('RESP_001: Navegación mobile con menú hamburguesa', () => {
      cy.viewport(375, 667); // Mobile
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // En mobile debe aparecer menú hamburguesa
      cy.get('[data-cy="mobile-menu-toggle"]').should('be.visible');
      cy.get('[data-cy="desktop-navigation"]').should('not.be.visible');

      // Abrir menú mobile
      cy.get('[data-cy="mobile-menu-toggle"]').click();
      cy.get('[data-cy="mobile-menu"]').should('be.visible');

      // Verificar enlaces del menú
      cy.get('[data-cy="mobile-menu"]').within(() => {
        cy.get('[data-cy="nav-dashboard"]').should('be.visible');
        cy.get('[data-cy="nav-processes"]').should('be.visible');
        cy.get('[data-cy="nav-reports"]').should('be.visible');
      });

      // Cerrar menú
      cy.get('[data-cy="mobile-menu-overlay"]').click();
      cy.get('[data-cy="mobile-menu"]').should('not.be.visible');
    });

    it('RESP_002: Widgets se reorganizan en tablet', () => {
      cy.viewport(768, 1024); // Tablet
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // En tablet debe usar layout de 2 columnas
      cy.get('[data-cy="dashboard-grid"]')
        .should('have.class', 'tablet-layout');

      // Verificar que widgets grandes ocupan toda la fila
      cy.get('[data-cy="widget-procesos-globales"]')
        .should('have.class', 'tablet-full-width');

      // Widgets pequeños deben agruparse
      cy.get('[data-cy="widget-usuarios-activos"]')
        .should('have.class', 'tablet-half-width');

      cy.get('[data-cy="widget-alertas-sistema"]')
        .should('have.class', 'tablet-half-width');
    });

    it('RESP_003: Filtros se adaptan en móvil', () => {
      cy.viewport(375, 667);
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Panel de filtros debe ser modal en móvil
      cy.get('[data-cy="filters-desktop"]').should('not.be.visible');
      cy.get('[data-cy="filters-mobile-trigger"]').should('be.visible');

      // Abrir filtros móvil
      cy.get('[data-cy="filters-mobile-trigger"]').click();
      cy.get('[data-cy="filters-modal"]').should('be.visible');

      // Aplicar filtro en móvil
      cy.get('[data-cy="filters-modal"]').within(() => {
        cy.get('[data-cy="secretaria-filter"]').select('planeacion');
        cy.get('[data-cy="apply-mobile-filters"]').click();
      });

      // Modal se debe cerrar
      cy.get('[data-cy="filters-modal"]').should('not.be.visible');

      // Filtro activo debe mostrarse compacto
      cy.get('[data-cy="mobile-active-filters"]')
        .should('contain', 'Planeación');
    });

    it('RESP_004: Gráficos se adaptan al tamaño de pantalla', () => {
      const viewports = [
        { width: 375, height: 667, name: 'mobile' },
        { width: 768, height: 1024, name: 'tablet' },
        { width: 1366, height: 768, name: 'desktop' }
      ];

      cy.loginAs('admin');
      cy.visit('/dashboard');

      viewports.forEach(viewport => {
        cy.viewport(viewport.width, viewport.height);

        // Verificar que los gráficos se redimensionan
        cy.get('[data-cy="widget-procesos-globales"]').within(() => {
          cy.get('[data-cy="chart-container"]').then($chart => {
            const width = $chart.width();

            if (viewport.name === 'mobile') {
              expect(width).to.be.lessThan(350);
            } else if (viewport.name === 'tablet') {
              expect(width).to.be.lessThan(700);
            } else {
              expect(width).to.be.greaterThan(400);
            }
          });
        });

        // Verificar que texto es legible
        cy.get('[data-cy="widget-title"]').each($title => {
          cy.wrap($title).should('have.css', 'font-size');
        });

        cy.screenshotWithName(`dashboard-responsive-${viewport.name}`);
      });
    });

    it('RESP_005: Touch gestures en dispositivos táctiles', () => {
      cy.viewport(375, 667);
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Simular swipe para cambiar entre widgets
      cy.get('[data-cy="widget-container"]')
        .trigger('touchstart', { touches: [{ clientX: 200, clientY: 100 }] })
        .trigger('touchmove', { touches: [{ clientX: 50, clientY: 100 }] })
        .trigger('touchend');

      // En móvil debe permitir scroll horizontal de widgets
      cy.get('[data-cy="horizontal-scroll-indicator"]').should('be.visible');

      // Test de pull-to-refresh
      cy.get('[data-cy="dashboard-container"]')
        .trigger('touchstart', { touches: [{ clientX: 100, clientY: 50 }] })
        .trigger('touchmove', { touches: [{ clientX: 100, clientY: 150 }] })
        .trigger('touchend');

      cy.get('[data-cy="refresh-indicator"]').should('have.been.visible');
    });
  });

  context('PERSONALIZACIÓN AVANZADA', () => {
    it('PERS_001: Temas claro/oscuro', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Verificar tema por defecto (claro)
      cy.get('body').should('have.class', 'theme-light');

      // Cambiar a tema oscuro
      cy.get('[data-cy="theme-toggle"]').click();
      cy.get('[data-cy="theme-dark"]').click();

      // Verificar cambio de tema
      cy.get('body').should('have.class', 'theme-dark');

      // Verificar que widgets se adaptan al tema
      cy.get('[data-cy="widget-container"]').should('have.class', 'dark-mode');

      // Verificar persistencia
      cy.reload();
      cy.get('body').should('have.class', 'theme-dark');
    });

    it('PERS_002: Configuración de densidad visual', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Abrir configuración visual
      cy.get('[data-cy="visual-settings"]').click();

      // Cambiar densidad a compacta
      cy.get('[data-cy="density-compact"]').click();
      cy.get('[data-cy="apply-visual-settings"]').click();

      // Verificar cambios visuales
      cy.get('[data-cy="widget-container"]').should('have.class', 'density-compact');
      cy.get('[data-cy="widget-padding"]').should('have.css', 'padding', '8px');

      // Cambiar a densidad cómoda
      cy.get('[data-cy="visual-settings"]').click();
      cy.get('[data-cy="density-comfortable"]').click();
      cy.get('[data-cy="apply-visual-settings"]').click();

      cy.get('[data-cy="widget-container"]').should('have.class', 'density-comfortable');
    });

    it('PERS_003: Widgets personalizables por usuario', () => {
      cy.loginAs('coord_contratacion');
      cy.visit('/dashboard');

      // Acceder a personalización de widgets
      cy.get('[data-cy="customize-widgets"]').click();

      cy.get('[data-cy="widget-customization-panel"]').within(() => {
        // Añadir widget personalizado
        cy.get('[data-cy="add-widget-btn"]').click();
        cy.get('[data-cy="widget-type"]').select('custom-chart');
        cy.get('[data-cy="widget-title"]').type('Mi Widget Personalizado');
        cy.get('[data-cy="widget-data-source"]').select('procesos-asignados');

        // Configurar visualización
        cy.get('[data-cy="chart-type"]').select('pie');
        cy.get('[data-cy="chart-colors"]').select('custom');

        cy.get('[data-cy="save-custom-widget"]').click();
      });

      // Verificar widget añadido
      cy.get('[data-cy="widget-mi-widget-personalizado"]').should('be.visible');

      // Editar widget existente
      cy.get('[data-cy="widget-procesos-asignados"]').within(() => {
        cy.get('[data-cy="widget-settings"]').click();
      });

      cy.get('[data-cy="widget-config-modal"]').within(() => {
        cy.get('[data-cy="widget-refresh-interval"]').select('5'); // 5 minutos
        cy.get('[data-cy="widget-show-numbers"]').uncheck();
        cy.get('[data-cy="save-widget-config"]').click();
      });

      // Verificar cambios aplicados
      cy.get('[data-cy="widget-procesos-asignados"]')
        .should('have.attr', 'data-refresh-interval', '5');
    });

    it('PERS_004: Configuración de notificaciones del dashboard', () => {
      cy.loginAs('revisor_juridico');
      cy.visit('/dashboard');

      // Acceder a configuración de notificaciones
      cy.get('[data-cy="notification-settings"]').click();

      cy.get('[data-cy="notification-config"]').within(() => {
        // Configurar alertas por email
        cy.get('[data-cy="email-notifications"]').check();
        cy.get('[data-cy="email-frequency"]').select('daily');

        // Configurar alertas en pantalla
        cy.get('[data-cy="browser-notifications"]').check();
        cy.get('[data-cy="notification-sound"]').check();

        // Configurar tipos de alertas
        cy.get('[data-cy="alert-procesos-criticos"]').check();
        cy.get('[data-cy="alert-documentos-pendientes"]').check();
        cy.get('[data-cy="alert-vencimientos"]').check();

        cy.get('[data-cy="save-notification-settings"]').click();
      });

      // Simular llegada de notificación
      cy.get('[data-cy="simulate-notification"]').click();

      // Verificar notificación mostrada
      cy.get('[data-cy="browser-notification"]')
        .should('be.visible')
        .should('contain', 'Nuevo proceso requiere revisión jurídica');
    });
  });

  context('PERFORMANCE Y OPTIMIZACIÓN', () => {
    it('PERF_001: Lazy loading de widgets pesados', () => {
      cy.loginAs('admin');

      // Interceptar carga de widgets
      cy.intercept('GET', '/api/dashboard/widgets/procesos-globales').as('heavyWidget');
      cy.intercept('GET', '/api/dashboard/widgets/usuarios-activos').as('lightWidget');

      cy.visit('/dashboard');

      // Widget ligero debe cargar primero
      cy.wait('@lightWidget');
      cy.get('[data-cy="widget-usuarios-activos"]')
        .should('not.have.class', 'loading');

      // Widget pesado debe mostrar skeleton mientras carga
      cy.get('[data-cy="widget-procesos-globales"]')
        .should('have.class', 'loading');

      cy.wait('@heavyWidget');
      cy.get('[data-cy="widget-procesos-globales"]')
        .should('not.have.class', 'loading');
    });

    it('PERF_002: Cache inteligente de datos', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/dashboard/data**').as('initialLoad');
      cy.wait('@initialLoad');

      // Cambiar filtro que no debería invalidar caché
      cy.get('[data-cy="date-filter"]').click();
      cy.get('[data-cy="date-today"]').click();

      // No debe hacer nueva llamada API para datos estáticos
      cy.get('[data-cy="widget-usuarios-activos"]')
        .should('not.have.class', 'loading');

      // Aplicar filtro que sí invalida caché
      cy.applyDashboardFilters({ secretaria: 'planeacion' });

      cy.intercept('GET', '/api/dashboard/data**').as('filteredLoad');
      cy.wait('@filteredLoad');
    });

    it('PERF_003: Debounce en filtros de búsqueda', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      cy.intercept('GET', '/api/search/suggestions**').as('searchAPI');

      // Escribir rápidamente en búsqueda
      cy.get('[data-cy="search-input"]')
        .type('C', { delay: 50 })
        .type('D', { delay: 50 })
        .type('-', { delay: 50 })
        .type('P', { delay: 50 })
        .type('N', { delay: 50 });

      // Solo debe hacer una llamada después del debounce
      cy.wait('@searchAPI');
      cy.get('@searchAPI.all').should('have.length', 1);
    });

    it('PERF_004: Paginación virtual en listas grandes', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Widget con lista grande de procesos
      cy.get('[data-cy="widget-procesos-globales"]').within(() => {
        cy.get('[data-cy="proceso-list"]').should('be.visible');

        // Solo debe renderizar elementos visibles inicialmente
        cy.get('[data-cy="proceso-item"]').should('have.length.lte', 10);

        // Al hacer scroll debe cargar más
        cy.get('[data-cy="proceso-list"]').scrollTo('bottom');

        cy.get('[data-cy="loading-more-indicator"]').should('be.visible');
        cy.get('[data-cy="proceso-item"]').should('have.length.gte', 15);
      });
    });
  });

  context('ACCESIBILIDAD', () => {
    it('ACC_001: Navegación por teclado', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Verificar que widgets son accesibles por teclado
      cy.get('body').tab();
      cy.focused().should('have.attr', 'data-cy', 'skip-to-content');

      cy.tab();
      cy.focused().should('have.attr', 'data-cy', 'widget-procesos-globales');

      cy.tab();
      cy.focused().should('have.attr', 'data-cy', 'widget-presupuesto-total');

      // Enter debe abrir configuración de widget
      cy.focused().type('{enter}');
      cy.get('[data-cy="widget-config-modal"]').should('be.visible');

      // Escape debe cerrar modal
      cy.focused().type('{esc}');
      cy.get('[data-cy="widget-config-modal"]').should('not.exist');
    });

    it('ACC_002: Lectores de pantalla (ARIA)', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Verificar atributos ARIA
      cy.get('[data-cy="dashboard-container"]')
        .should('have.attr', 'role', 'main')
        .should('have.attr', 'aria-label', 'Panel de control principal');

      cy.get('[data-cy="widget-procesos-globales"]')
        .should('have.attr', 'role', 'region')
        .should('have.attr', 'aria-labelledby');

      // Verificar labels descriptivos
      cy.get('[data-cy="filter-panel"]')
        .should('have.attr', 'aria-label', 'Panel de filtros del dashboard');

      // Verificar live regions para actualizaciones
      cy.get('[data-cy="status-updates"]')
        .should('have.attr', 'aria-live', 'polite');
    });

    it('ACC_003: Contraste y legibilidad', () => {
      cy.loginAs('admin');
      cy.visit('/dashboard');

      // Verificar contrastes mínimos en elementos importantes
      cy.get('[data-cy="widget-title"]').each($title => {
        cy.wrap($title).should('have.css', 'color').then(color => {
          // TODO: Implementar verificación de contraste programática
          expect(color).to.not.equal('rgb(128, 128, 128)'); // Gris muy claro
        });
      });

      // Verificar tamaños de fuente mínimos
      cy.get('[data-cy="widget-content"]').each($content => {
        cy.wrap($content).should('have.css', 'font-size').then(fontSize => {
          const size = parseInt(fontSize.replace('px', ''));
          expect(size).to.be.gte(14); // Mínimo 14px
        });
      });
    });
  });

  after(() => {
    cy.task('log', 'Dashboard filters and responsive tests completed');
  });
});