// cypress/support/e2e.js
// ***********************************************************
// This file is loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js
import './commands'

// Import additional plugins
import 'cypress-file-upload'
import 'cypress-drag-drop'
import 'cypress-real-events/support'

// Global configuration
Cypress.on('uncaught:exception', (err, runnable) => {
  // Returning false here prevents Cypress from failing the test
  // for certain expected errors

  // Laravel CSRF token errors in testing environment
  if (err.message.includes('CSRF token mismatch') && Cypress.env('NODE_ENV') === 'testing') {
    return false
  }

  // React development warnings
  if (err.message.includes('Warning:') && err.message.includes('React')) {
    return false
  }

  // Don't fail on unhandled promise rejections in testing
  if (err.message.includes('ResizeObserver loop limit exceeded')) {
    return false
  }

  return true
})

// Global hooks
beforeEach(() => {
  // Clear localStorage and sessionStorage before each test
  cy.clearLocalStorage()
  cy.clearCookies()

  // Set default viewport for consistent testing
  cy.viewport(1366, 768)

  // Configure interceptors for common API calls
  cy.intercept('GET', '/api/user', { fixture: 'user/current-user.json' }).as('getCurrentUser')
  cy.intercept('GET', '/api/dashboard/**', { fixture: 'dashboard/default-config.json' }).as('getDashboardConfig')
})

// Custom configuration for test data
Cypress.env('testUsers', {
  admin: {
    email: 'admin.sistema@gobernacion-caldas.gov.co',
    password: 'TestingPassword123!'
  },
  coordinador: {
    email: 'coord.testing@gobernacion-caldas.gov.co',
    password: 'TestingPassword123!'
  },
  secretario: {
    email: 'secretario.testing@gobernacion-caldas.gov.co',
    password: 'TestingPassword123!'
  }
});