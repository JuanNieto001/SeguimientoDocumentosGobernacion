// cypress/e2e/01-authentication/login.cy.js
/**
 * Tests de autenticación - Login
 *
 * Pruebas críticas de inicio de sesión del sistema
 * Gobernación de Caldas - Sistema de Seguimiento Contractual
 */

describe('Sistema de Autenticación - Login', () => {
  let users;
  let testConfig;

  before(() => {
    // Cargar configuraciones y datos de prueba
    cy.fixture('users').then((usersData) => {
      users = usersData;
    });

    cy.fixture('test-config').then((config) => {
      testConfig = config;
    });

    // Preparar base de datos para testing
    cy.seedDatabase('TestingSeederStructure');
  });

  beforeEach(() => {
    // Limpiar estado antes de cada test
    cy.clearLocalStorage();
    cy.clearCookies();

    // Visitar página de login
    cy.visit('/login');
    cy.waitForPageLoad();
  });

  context('LOGIN EXITOSO', () => {
    it('LOGIN_001: Login exitoso con usuario administrador', () => {
      // Given: Usuario administrador válido
      const adminUser = users.admin;

      // When: Ingresa credenciales correctas
      cy.get(testConfig.selectors.auth.email_input)
        .should('be.visible')
        .type(adminUser.email);

      cy.get(testConfig.selectors.auth.password_input)
        .should('be.visible')
        .type(adminUser.password);

      cy.get(testConfig.selectors.auth.login_button)
        .should('be.enabled')
        .click();

      // Then: Redirige a dashboard ejecutivo
      cy.url().should('not.include', '/login');
      cy.get(testConfig.selectors.dashboard.container, {
        timeout: testConfig.test_timeouts.long
      }).should('be.visible');

      // Verificar mensaje de bienvenida
      cy.get('[data-cy="welcome-message"]')
        .should('contain', adminUser.name);

      // Verificar menú de usuario visible
      cy.get(testConfig.selectors.auth.user_menu)
        .should('be.visible')
        .should('contain', adminUser.name);
    });

    it('LOGIN_002: Login exitoso con coordinador de contratación', () => {
      // Given: Usuario coordinador válido
      const coordUser = users.coord_contratacion;

      // When: Hace login
      cy.loginWith(coordUser.email, coordUser.password);

      // Then: Ve dashboard de gestión correspondiente
      cy.url().should('include', '/dashboard');
      cy.get('[data-cy="dashboard-tipo"]').should('contain', 'Gestión');

      // Verificar widgets específicos para coordinador
      cy.verifyDashboardWidgets([
        'widget-carga-trabajo',
        'widget-procesos-asignados'
      ]);
    });

    it('LOGIN_003: Login exitoso con secretario', () => {
      // Given: Usuario secretario válido
      const secretarioUser = users.secretario_planeacion;

      // When: Hace login
      cy.loginWith(secretarioUser.email, secretarioUser.password);

      // Then: Ve dashboard secretarial con filtros de secretaría
      cy.url().should('include', '/dashboard');
      cy.get('[data-cy="dashboard-tipo"]').should('contain', 'Secretarial');

      // Verificar filtro automático por secretaría
      cy.get('[data-cy="secretaria-filter"]')
        .should('contain', 'Planeación');
    });

    it('LOGIN_004: Login con recordar sesión', () => {
      const adminUser = users.admin;

      // When: Hace login con "recordar sesión"
      cy.get(testConfig.selectors.auth.email_input).type(adminUser.email);
      cy.get(testConfig.selectors.auth.password_input).type(adminUser.password);
      cy.get('[data-cy="remember-me-checkbox"]').check();
      cy.get(testConfig.selectors.auth.login_button).click();

      // Then: Login exitoso y cookie persistente creada
      cy.getCookie('remember_token').should('exist');
      cy.get(testConfig.selectors.dashboard.container).should('be.visible');
    });
  });

  context('LOGIN FALLIDO', () => {
    it('LOGIN_005: Login fallido con email inválido', () => {
      // Given: Email que no existe en el sistema
      const invalidEmail = 'usuario.inexistente@testing.com';

      // When: Intenta hacer login
      cy.get(testConfig.selectors.auth.email_input).type(invalidEmail);
      cy.get(testConfig.selectors.auth.password_input).type('cualquier_password');
      cy.get(testConfig.selectors.auth.login_button).click();

      // Then: Muestra mensaje de error
      cy.get('[data-cy="login-error-message"]')
        .should('be.visible')
        .should('contain', testConfig.validation_messages.login.invalid_credentials);

      // Permanece en página de login
      cy.url().should('include', '/login');
    });

    it('LOGIN_006: Login fallido con contraseña incorrecta', () => {
      const adminUser = users.admin;

      // When: Usa email válido pero contraseña incorrecta
      cy.get(testConfig.selectors.auth.email_input).type(adminUser.email);
      cy.get(testConfig.selectors.auth.password_input).type('contraseña_incorrecta');
      cy.get(testConfig.selectors.auth.login_button).click();

      // Then: Error de credenciales inválidas
      cy.get('[data-cy="login-error-message"]')
        .should('contain', testConfig.validation_messages.login.invalid_credentials);
    });

    it('LOGIN_007: Validación de campos obligatorios', () => {
      // When: Intenta hacer login sin completar campos
      cy.get(testConfig.selectors.auth.login_button).click();

      // Then: Muestra errores de validación
      cy.get('[data-cy="email-error"]')
        .should('be.visible')
        .should('contain', 'El email es obligatorio');

      cy.get('[data-cy="password-error"]')
        .should('be.visible')
        .should('contain', 'La contraseña es obligatoria');
    });

    it('LOGIN_008: Login con email con formato inválido', () => {
      // When: Usa formato de email inválido
      cy.get(testConfig.selectors.auth.email_input).type('email_sin_formato_valido');
      cy.get(testConfig.selectors.auth.password_input).type('password123');
      cy.get(testConfig.selectors.auth.login_button).click();

      // Then: Error de formato de email
      cy.get('[data-cy="email-error"]')
        .should('contain', 'El formato del email no es válido');
    });
  });

  context('COMPORTAMIENTOS ESPECÍFICOS', () => {
    it('LOGIN_009: Redirección después de login según rol', () => {
      const testCases = [
        { user: users.gobernador, expectedUrl: '/dashboard', expectedType: 'Ejecutivo' },
        { user: users.secretario_planeacion, expectedUrl: '/dashboard', expectedType: 'Secretarial' },
        { user: users.coord_contratacion, expectedUrl: '/dashboard', expectedType: 'Gestión' }
      ];

      testCases.forEach((testCase, index) => {
        // Hacer logout si no es la primera iteración
        if (index > 0) {
          cy.visit('/login');
        }

        cy.loginWith(testCase.user.email, testCase.user.password);
        cy.url().should('include', testCase.expectedUrl);
        cy.get('[data-cy="dashboard-tipo"]').should('contain', testCase.expectedType);
      });
    });

    it('LOGIN_010: Bloqueo de cuenta después de múltiples intentos fallidos', () => {
      const adminUser = users.admin;
      const maxAttempts = 5;

      // When: Realiza múltiples intentos fallidos
      for (let i = 0; i < maxAttempts; i++) {
        cy.get(testConfig.selectors.auth.email_input)
          .clear()
          .type(adminUser.email);
        cy.get(testConfig.selectors.auth.password_input)
          .clear()
          .type('contraseña_incorrecta');
        cy.get(testConfig.selectors.auth.login_button).click();

        if (i < maxAttempts - 1) {
          cy.get('[data-cy="login-error-message"]').should('be.visible');
        }
      }

      // Then: Cuenta bloqueada temporalmente
      cy.get('[data-cy="account-locked-message"]')
        .should('be.visible')
        .should('contain', 'Cuenta bloqueada temporalmente');
    });

    it('LOGIN_011: Verificación de sesión persistente', () => {
      const adminUser = users.admin;

      // When: Hace login exitoso
      cy.loginWith(adminUser.email, adminUser.password);
      cy.get(testConfig.selectors.dashboard.container).should('be.visible');

      // Y refresca la página
      cy.reload();

      // Then: Sesión se mantiene activa
      cy.get(testConfig.selectors.dashboard.container, {
        timeout: testConfig.test_timeouts.long
      }).should('be.visible');
      cy.url().should('not.include', '/login');
    });
  });

  context('CASOS EDGE', () => {
    it('LOGIN_012: Login con caracteres especiales en contraseña', () => {
      // Crear usuario temporal con contraseña con caracteres especiales
      const specialUser = {
        email: 'special.user@testing.com',
        password: 'P@ssw0rd!#$%&*()[]{}|;:,.<>?'
      };

      // Simular que existe temporalmente
      cy.intercept('POST', '/api/auth/login', {
        statusCode: 200,
        body: { token: 'test_token', user: { id: 999, name: 'Special User' } }
      }).as('specialLogin');

      cy.get(testConfig.selectors.auth.email_input).type(specialUser.email);
      cy.get(testConfig.selectors.auth.password_input).type(specialUser.password);
      cy.get(testConfig.selectors.auth.login_button).click();

      cy.wait('@specialLogin');
    });

    it('LOGIN_013: Login durante mantenimiento del sistema', () => {
      // Simular modo mantenimiento
      cy.intercept('GET', '/login', {
        statusCode: 503,
        body: { message: 'Sistema en mantenimiento' }
      }).as('maintenanceMode');

      cy.visit('/login', { failOnStatusCode: false });

      // Verificar mensaje de mantenimiento
      cy.get('[data-cy="maintenance-message"]')
        .should('be.visible')
        .should('contain', 'Sistema en mantenimiento');
    });
  });

  after(() => {
    // Cleanup después de todos los tests
    cy.task('log', 'Authentication tests completed');
  });
});