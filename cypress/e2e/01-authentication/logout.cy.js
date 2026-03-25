// cypress/e2e/01-authentication/logout.cy.js
/**
 * Tests de autenticación - Logout
 *
 * Pruebas de cierre de sesión del sistema
 * Gobernación de Caldas - Sistema de Seguimiento Contractual
 */

describe('Sistema de Autenticación - Logout', () => {
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
    // Autenticarse antes de cada test de logout
    cy.loginAs('admin');
    cy.get(testConfig.selectors.dashboard.container).should('be.visible');
  });

  context('LOGOUT EXITOSO', () => {
    it('LOGOUT_001: Logout desde menú de usuario', () => {
      // When: Hace logout desde el menú
      cy.get(testConfig.selectors.auth.user_menu).click();
      cy.get(testConfig.selectors.auth.logout_button).click();

      // Then: Redirige a página de login
      cy.url().should('include', '/login');

      // Verificar que el dashboard no es accesible
      cy.visit('/dashboard', { failOnStatusCode: false });
      cy.url().should('include', '/login');
    });

    it('LOGOUT_002: Logout con confirmación', () => {
      cy.get(testConfig.selectors.auth.user_menu).click();
      cy.get(testConfig.selectors.auth.logout_button).click();

      // Si hay modal de confirmación
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="logout-confirm-modal"]').length > 0) {
          cy.get('[data-cy="logout-confirm-yes"]').click();
        }
      });

      cy.url().should('include', '/login');
      cy.get('[data-cy="logout-success-message"]')
        .should('be.visible')
        .should('contain', 'Sesión cerrada exitosamente');
    });

    it('LOGOUT_003: Limpieza completa de sesión', () => {
      // Verificar que existen datos de sesión
      cy.getCookie('laravel_session').should('exist');
      cy.window().its('localStorage').should('not.be.empty');

      // When: Hace logout
      cy.logout();

      // Then: Todos los datos de sesión son eliminados
      cy.getCookie('laravel_session').should('not.exist');
      cy.getCookie('remember_token').should('not.exist');
      cy.window().its('localStorage.length').should('eq', 0);
      cy.window().its('sessionStorage.length').should('eq', 0);
    });
  });

  context('LOGOUT AUTOMÁTICO', () => {
    it('LOGOUT_004: Logout automático por inactividad', () => {
      // Simular inactividad prolongada
      cy.wait(2000); // Usar tiempo corto para testing

      // Simular que la sesión expiró
      cy.intercept('GET', '/api/auth/me', {
        statusCode: 401,
        body: { message: 'Sesión expirada' }
      }).as('sessionExpired');

      // Intentar acceder a una página protegida
      cy.visit('/dashboard');

      // Verificar redirección automática al login
      cy.url().should('include', '/login');
      cy.get('[data-cy="session-expired-message"]')
        .should('be.visible')
        .should('contain', 'Su sesión ha expirado');
    });

    it('LOGOUT_005: Logout por token inválido', () => {
      // Corromper el token de sesión
      cy.window().then((win) => {
        win.localStorage.setItem('auth_token', 'token_invalido');
      });

      // Refreshar página
      cy.reload();

      // Verificar redirección al login
      cy.url().should('include', '/login');
    });

    it('LOGOUT_006: Logout por sesión concurrente', () => {
      // Simular login desde otro dispositivo
      cy.intercept('POST', '/api/auth/verify-session', {
        statusCode: 409,
        body: { message: 'Sesión iniciada en otro dispositivo' }
      }).as('concurrentSession');

      // Navegar a dashboard
      cy.visit('/dashboard');

      // Verificar mensaje de sesión concurrente
      cy.get('[data-cy="concurrent-session-message"]')
        .should('be.visible')
        .should('contain', 'Sesión iniciada en otro dispositivo');
    });
  });

  context('COMPORTAMIENTOS ESPECÍFICOS', () => {
    it('LOGOUT_007: Logout cancela operaciones en progreso', () => {
      // Iniciar una operación que toma tiempo
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="create-process-btn"]').click();

      // Llenar formulario parcialmente
      cy.get('[data-cy="objeto-input"]').type('Proceso de prueba');

      // Hacer logout durante la operación
      cy.get(testConfig.selectors.auth.user_menu).click();
      cy.get(testConfig.selectors.auth.logout_button).click();

      // Verificar que se cancela la operación
      cy.url().should('include', '/login');

      // Al hacer login nuevamente, no debe existir el proceso parcial
      cy.loginAs('admin');
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="process-list"]').should('not.contain', 'Proceso de prueba');
    });

    it('LOGOUT_008: Logout preserva configuraciones de usuario', () => {
      // Cambiar configuraciones del dashboard
      cy.goToDashboard();
      cy.get('[data-cy="dashboard-settings"]').click();
      cy.get('[data-cy="theme-selector"]').select('tema-azul');
      cy.get('[data-cy="save-settings"]').click();

      // Hacer logout
      cy.logout();

      // Hacer login nuevamente
      cy.loginAs('admin');
      cy.goToDashboard();

      // Verificar que las configuraciones se mantienen
      cy.get('[data-cy="dashboard-settings"]').click();
      cy.get('[data-cy="theme-selector"]').should('have.value', 'tema-azul');
    });

    it('LOGOUT_009: Logout seguro con datos sensibles', () => {
      // Navegar a página con datos sensibles
      cy.get('[data-cy="nav-users"]').click();
      cy.get('[data-cy="user-details"]').first().click();

      // Verificar que los datos están visibles
      cy.get('[data-cy="user-email"]').should('be.visible');

      // Hacer logout
      cy.logout();

      // Intentar navegar directamente a la página sensible
      cy.visit('/users/1', { failOnStatusCode: false });

      // Verificar redirección al login
      cy.url().should('include', '/login');
    });
  });

  context('CASOS EDGE Y ERRORES', () => {
    it('LOGOUT_010: Logout cuando el servidor no está disponible', () => {
      // Simular servidor no disponible
      cy.intercept('POST', '/api/auth/logout', {
        statusCode: 500,
        body: { message: 'Error interno del servidor' }
      }).as('serverError');

      cy.get(testConfig.selectors.auth.user_menu).click();
      cy.get(testConfig.selectors.auth.logout_button).click();

      // Verificar que aún así se limpia la sesión local
      cy.url().should('include', '/login');

      // Intentar acceder a Dashboard
      cy.visit('/dashboard', { failOnStatusCode: false });
      cy.url().should('include', '/login');
    });

    it('LOGOUT_011: Logout múltiple rápido', () => {
      // Hacer logout múltiples veces rápidamente
      cy.get(testConfig.selectors.auth.user_menu).click();
      cy.get(testConfig.selectors.auth.logout_button).click();
      cy.get(testConfig.selectors.auth.logout_button).click();
      cy.get(testConfig.selectors.auth.logout_button).click();

      // Verificar que no hay errores y se redirige correctamente
      cy.url().should('include', '/login');
    });

    it('LOGOUT_012: Logout durante proceso crítico', () => {
      // Iniciar proceso de subida de documento
      cy.get('[data-cy="nav-processes"]').click();
      cy.get('[data-cy="process-item"]').first().click();
      cy.get('[data-cy="upload-document"]').click();

      // Simular subida en progreso
      cy.intercept('POST', '/api/documents/upload', {
        delay: 5000, // 5 segundos de delay
        statusCode: 200,
        body: { success: true }
      }).as('uploadInProgress');

      cy.uploadTestDocument('estudios_previos');

      // Hacer logout durante la subida
      cy.get(testConfig.selectors.auth.user_menu).click();
      cy.get(testConfig.selectors.auth.logout_button).click();

      // Verificar logout exitoso
      cy.url().should('include', '/login');
    });
  });

  context('INTEGRACIÓN CON APIs', () => {
    it('LOGOUT_013: Logout invalida tokens de API', () => {
      // Obtener token actual
      cy.window().then((win) => {
        const token = win.localStorage.getItem('auth_token');
        expect(token).to.exist;

        // Hacer logout
        cy.logout();

        // Intentar usar el token después del logout
        cy.request({
          method: 'GET',
          url: '/api/user',
          headers: {
            'Authorization': `Bearer ${token}`
          },
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).to.eq(401);
        });
      });
    });

    it('LOGOUT_014: Logout notifica a otros servicios', () => {
      // Interceptar llamadas a servicios externos
      cy.intercept('POST', '/api/audit/logout', {
        statusCode: 200,
        body: { logged: true }
      }).as('auditNotification');

      cy.intercept('POST', '/api/websocket/disconnect', {
        statusCode: 200,
        body: { disconnected: true }
      }).as('websocketDisconnect');

      // Hacer logout
      cy.logout();

      // Verificar que se notificaron los servicios
      cy.wait('@auditNotification');
      cy.wait('@websocketDisconnect');
    });
  });

  after(() => {
    cy.task('log', 'Logout tests completed');
  });
});