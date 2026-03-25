// cypress/support/commands.js
// ***********************************************
// This file contains custom commands for Cypress
// that can be used throughout the test suite
// ***********************************************

// ========================================
// AUTHENTICATION COMMANDS
// ========================================

/**
 * Login with different user types
 * @param {string} userType - admin, coordinador, secretario, gobernador, etc.
 */
Cypress.Commands.add('loginAs', (userType) => {
  cy.fixture('users').then((users) => {
    const user = users[userType];
    if (!user) {
      throw new Error(`User type "${userType}" not found in fixtures`);
    }

    cy.visit('/login');

    // Fill login form
    cy.get('[data-cy="email-input"]', { timeout: 10000 })
      .should('be.visible')
      .clear()
      .type(user.email);

    cy.get('[data-cy="password-input"]')
      .should('be.visible')
      .clear()
      .type(user.password);

    // Submit form
    cy.get('[data-cy="login-button"]').click();

    // Verify successful login (should not be on login page)
    cy.url().should('not.include', '/login');

    // Wait for dashboard to load
    cy.get('[data-cy="dashboard-container"]', { timeout: 15000 }).should('be.visible');

    // Store user info for later use
    cy.wrap(user).as('currentUser');
  });
});

/**
 * Logout current user
 */
Cypress.Commands.add('logout', () => {
  cy.get('[data-cy="user-menu-button"]').click();
  cy.get('[data-cy="logout-button"]').click();
  cy.url().should('include', '/login');
});

/**
 * Login with custom credentials
 */
Cypress.Commands.add('loginWith', (email, password) => {
  cy.visit('/login');
  cy.get('[data-cy="email-input"]').type(email);
  cy.get('[data-cy="password-input"]').type(password);
  cy.get('[data-cy="login-button"]').click();
});

// ========================================
// PROCESS MANAGEMENT COMMANDS
// ========================================

/**
 * Create a test process of specific type
 * @param {string} processType - cd_pn_basico, cd_pn_complejo, etc.
 */
Cypress.Commands.add('createTestProcess', (processType = 'cd_pn_basico') => {
  cy.fixture('procesos').then((procesos) => {
    const proceso = procesos[processType];
    if (!proceso) {
      throw new Error(`Process type "${processType}" not found in fixtures`);
    }

    // Navigate to create process page
    cy.visit('/procesos/crear');

    // Fill process form
    cy.get('[data-cy="workflow-select"]').select('CD_PN_OPTIMIZED');
    cy.get('[data-cy="codigo-input"]').type(proceso.codigo);
    cy.get('[data-cy="objeto-input"]').type(proceso.objeto);
    cy.get('[data-cy="descripcion-textarea"]').type(proceso.descripcion);
    cy.get('[data-cy="valor-input"]').type(proceso.valor_estimado.toString());
    cy.get('[data-cy="plazo-input"]').type(proceso.plazo_ejecucion);

    // Contractor information
    if (proceso.contratista_nombre) {
      cy.get('[data-cy="contratista-nombre-input"]').type(proceso.contratista_nombre);
      cy.get('[data-cy="contratista-documento-input"]').type(proceso.contratista_documento);
    }

    // Submit form
    cy.get('[data-cy="crear-proceso-btn"]').click();

    // Verify creation
    cy.get('[data-cy="proceso-creado-mensaje"]', { timeout: 10000 }).should('be.visible');

    // Return process data for use in test
    cy.wrap(proceso).as('createdProcess');
  });
});

/**
 * Navigate to a specific process
 * @param {string} processCode - Process code to navigate to
 */
Cypress.Commands.add('goToProcess', (processCode) => {
  cy.visit(`/procesos/${processCode}`);
  cy.get('[data-cy="proceso-header"]').should('contain', processCode);
});

/**
 * Advance process to next stage
 * @param {object} options - Configuration for stage advancement
 */
Cypress.Commands.add('advanceProcessStage', (options = {}) => {
  const { validateDocuments = true, addComments = false } = options;

  if (validateDocuments) {
    // Mark all required documents as complete
    cy.get('[data-cy="documento-checkbox"]').each(($checkbox) => {
      cy.wrap($checkbox).check();
    });
  }

  if (addComments) {
    cy.get('[data-cy="stage-comments-textarea"]').type('Proceso avanzado por testing automatizado');
  }

  // Click advance button
  cy.get('[data-cy="avanzar-etapa-btn"]').click();

  // Confirm advancement if modal appears
  cy.get('body').then(($body) => {
    if ($body.find('[data-cy="confirm-advance-btn"]').length > 0) {
      cy.get('[data-cy="confirm-advance-btn"]').click();
    }
  });

  // Wait for stage change
  cy.get('[data-cy="etapa-actualizada-mensaje"]', { timeout: 10000 }).should('be.visible');
});

// ========================================
// DOCUMENT MANAGEMENT COMMANDS
// ========================================

/**
 * Upload a test document
 * @param {string} documentType - Type from fixtures/documentos.json
 * @param {string} selector - CSS selector for file input
 */
Cypress.Commands.add('uploadTestDocument', (documentType, selector = '[data-cy="file-upload"]') => {
  cy.fixture('documentos').then((docs) => {
    const doc = docs[documentType];
    if (!doc) {
      throw new Error(`Document type "${documentType}" not found in fixtures`);
    }

    // Create file with appropriate size and type
    cy.get(selector).selectFile({
      contents: Cypress.Buffer.alloc(doc.size),
      fileName: doc.filename,
      mimeType: doc.tipo,
      lastModified: Date.now(),
    }, { force: true });

    // Verify upload success
    cy.get('[data-cy="upload-success-message"]', { timeout: 15000 }).should('be.visible');
  });
});

/**
 * Upload multiple documents for current stage
 * @param {Array} documentTypes - Array of document types to upload
 */
Cypress.Commands.add('uploadStageDocuments', (documentTypes) => {
  documentTypes.forEach((docType, index) => {
    cy.get(`[data-cy="upload-${docType}"]`).then(($el) => {
      if ($el.length > 0) {
        cy.uploadTestDocument(docType, `[data-cy="upload-${docType}"] input[type="file"]`);
      }
    });
  });
});

// ========================================
// DASHBOARD COMMANDS
// ========================================

/**
 * Navigate to dashboard and verify it loads
 */
Cypress.Commands.add('goToDashboard', () => {
  cy.visit('/dashboard');
  cy.get('[data-cy="dashboard-container"]', { timeout: 15000 }).should('be.visible');
});

/**
 * Verify dashboard contains expected widgets for role
 * @param {Array} expectedWidgets - Array of widget data-cy selectors
 */
Cypress.Commands.add('verifyDashboardWidgets', (expectedWidgets) => {
  cy.get('[data-cy="dashboard-container"]').within(() => {
    expectedWidgets.forEach(widget => {
      cy.get(`[data-cy="${widget}"]`).should('be.visible');
    });
  });
});

/**
 * Drag widget to new position
 * @param {string} widgetSelector - Widget to drag
 * @param {string} targetSelector - Drop target
 */
Cypress.Commands.add('dragWidget', (widgetSelector, targetSelector) => {
  cy.get(widgetSelector).drag(targetSelector);

  // Verify position was saved
  cy.get('[data-cy="layout-saved-message"]', { timeout: 5000 }).should('be.visible');
});

/**
 * Apply dashboard filters
 * @param {object} filters - Filter configuration
 */
Cypress.Commands.add('applyDashboardFilters', (filters) => {
  cy.get('[data-cy="filters-panel"]').within(() => {
    if (filters.dateRange) {
      cy.get('[data-cy="date-filter"]').click();
      cy.get(`[data-cy="date-${filters.dateRange}"]`).click();
    }

    if (filters.secretaria) {
      cy.get('[data-cy="secretaria-filter"]').select(filters.secretaria);
    }

    if (filters.unidad) {
      cy.get('[data-cy="unidad-filter"]').select(filters.unidad);
    }

    // Apply filters
    cy.get('[data-cy="apply-filters-btn"]').click();
  });

  // Wait for data to update
  cy.get('[data-cy="dashboard-loading"]').should('not.exist');
});

// ========================================
// UTILITY COMMANDS
// ========================================

/**
 * Wait for page to fully load
 */
Cypress.Commands.add('waitForPageLoad', () => {
  cy.get('[data-cy="page-loading"]').should('not.exist');
  cy.get('body').should('be.visible');
});

/**
 * Seed database with test data
 * @param {string} seederClass - Laravel seeder class name
 */
Cypress.Commands.add('seedDatabase', (seederClass = 'TestingSeederStructure') => {
  cy.task('db:seed', seederClass).then((result) => {
    expect(result).to.be.true;
  });
});

/**
 * Reset database to clean state
 */
Cypress.Commands.add('resetDatabase', () => {
  cy.task('db:refresh').then((result) => {
    expect(result).to.be.true;
  });
});

/**
 * Generate test data
 * @param {string} type - Type of test data to generate
 */
Cypress.Commands.add('generateTestData', (type = 'full') => {
  cy.task('generate:testdata', type).then((result) => {
    expect(result).to.be.true;
  });
});

/**
 * Take screenshot with custom name
 * @param {string} name - Screenshot name
 */
Cypress.Commands.add('screenshotWithName', (name) => {
  const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
  cy.screenshot(`${name}_${timestamp}`);
});

/**
 * Verify no console errors (except known ones)
 */
Cypress.Commands.add('verifyNoConsoleErrors', () => {
  cy.window().then((win) => {
    const originalError = win.console.error;
    let errorMessages = [];

    win.console.error = function(...args) {
      errorMessages.push(args.join(' '));
      originalError.apply(win.console, args);
    };

    // After test actions, check for errors
    cy.then(() => {
      const criticalErrors = errorMessages.filter(msg =>
        !msg.includes('Warning:') &&
        !msg.includes('ResizeObserver') &&
        !msg.includes('[HMR]')
      );

      expect(criticalErrors).to.have.length(0);
    });
  });
});

/**
 * Wait for API call to complete
 * @param {string} aliasName - Cypress alias for the API call
 */
Cypress.Commands.add('waitForAPI', (aliasName) => {
  cy.wait(`@${aliasName}`).then((interception) => {
    expect(interception.response.statusCode).to.be.oneOf([200, 201, 204]);
  });
});

// ========================================
// RESPONSIVE TESTING COMMANDS
// ========================================

/**
 * Test component in different viewport sizes
 * @param {string} selector - Component selector
 * @param {Array} viewports - Array of viewport configurations
 */
Cypress.Commands.add('testResponsive', (selector, viewports = [
  { width: 375, height: 667, name: 'mobile' },
  { width: 768, height: 1024, name: 'tablet' },
  { width: 1366, height: 768, name: 'desktop' }
]) => {
  viewports.forEach(viewport => {
    cy.viewport(viewport.width, viewport.height);
    cy.get(selector).should('be.visible');
    cy.screenshotWithName(`${selector.replace(/[\[\]]/g, '')}_${viewport.name}`);
  });
});

// ========================================
// PERFORMANCE TESTING COMMANDS
// ========================================

/**
 * Measure page load time
 * @param {string} url - URL to measure
 */
Cypress.Commands.add('measurePageLoad', (url) => {
  cy.visit(url);

  cy.window().then((win) => {
    const performance = win.performance;
    const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;

    // Log performance metrics
    cy.task('log', `Page load time for ${url}: ${loadTime}ms`);

    // Assert reasonable load time (adjust threshold as needed)
    expect(loadTime).to.be.lessThan(5000); // 5 seconds max

    return cy.wrap(loadTime);
  });
});

// Add TypeScript declarations if using TypeScript
declare global {
  namespace Cypress {
    interface Chainable {
      loginAs(userType: string): Chainable<void>
      logout(): Chainable<void>
      loginWith(email: string, password: string): Chainable<void>
      createTestProcess(processType?: string): Chainable<void>
      goToProcess(processCode: string): Chainable<void>
      advanceProcessStage(options?: object): Chainable<void>
      uploadTestDocument(documentType: string, selector?: string): Chainable<void>
      uploadStageDocuments(documentTypes: string[]): Chainable<void>
      goToDashboard(): Chainable<void>
      verifyDashboardWidgets(expectedWidgets: string[]): Chainable<void>
      dragWidget(widgetSelector: string, targetSelector: string): Chainable<void>
      applyDashboardFilters(filters: object): Chainable<void>
      waitForPageLoad(): Chainable<void>
      seedDatabase(seederClass?: string): Chainable<void>
      resetDatabase(): Chainable<void>
      generateTestData(type?: string): Chainable<void>
      screenshotWithName(name: string): Chainable<void>
      verifyNoConsoleErrors(): Chainable<void>
      waitForAPI(aliasName: string): Chainable<void>
      testResponsive(selector: string, viewports?: Array<{width: number, height: number, name: string}>): Chainable<void>
      measurePageLoad(url: string): Chainable<number>
    }
  }
}