import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    // URL base de la aplicación
    baseUrl: 'http://localhost:8000',

    // Configuración de viewport por defecto
    viewportWidth: 1366,
    viewportHeight: 768,

    // Configuración de grabación y screenshots
    video: false,
    screenshotOnRunFailure: true,
    screenshotsFolder: 'cypress/screenshots',
    videosFolder: 'cypress/videos',

    // Timeouts para comandos y requests
    defaultCommandTimeout: 10000,
    requestTimeout: 15000,
    responseTimeout: 15000,
    pageLoadTimeout: 30000,

    // Variables de entorno para testing
    env: {
      // Credenciales de testing
      admin_email: 'admin.sistema@gobernacion-caldas.gov.co',
      admin_password: 'TestingPassword123!',

      // URLs de APIs
      api_base_url: 'http://localhost:8000/api',

      // Configuraciones de testing
      test_timeout: 10000,
      upload_timeout: 30000,

      // Feature flags para tests
      enable_drag_drop_tests: true,
      enable_responsive_tests: true,
      enable_performance_tests: false
    },

    // Configuración de archivos y patrones
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js',
    fixturesFolder: 'cypress/fixtures',
    downloadsFolder: 'cypress/downloads',

    // Configuración de navegador y comportamiento
    chromeWebSecurity: false,
    experimentalStudio: true,
    experimentalWebKitSupport: false,

    // Configuración de reportes
    reporter: 'cypress-multi-reporters',
    reporterOptions: {
      configFile: 'cypress-reporter-config.json'
    },

    setupNodeEvents(on, config) {
      // Configuración de plugins y eventos

      // Plugin para manejo de archivos
      on('file:preprocessor', require('@cypress/webpack-preprocessor')({
        webpackOptions: {
          resolve: {
            extensions: ['.ts', '.js', '.tsx', '.jsx']
          },
          module: {
            rules: [
              {
                test: /\.tsx?$/,
                use: 'ts-loader',
                exclude: [/node_modules/]
              }
            ]
          }
        }
      }));

      // Task personalizada para limpiar base de datos
      on('task', {
        'db:seed'(seederClass = 'TestingSeederStructure') {
          return new Promise((resolve) => {
            const { exec } = require('child_process');
            exec(`php artisan db:seed --class=${seederClass}`, (error, stdout, stderr) => {
              if (error) {
                console.error(`Error: ${error}`);
                resolve(false);
              } else {
                console.log(`Seeder executed: ${stdout}`);
                resolve(true);
              }
            });
          });
        },

        'db:refresh'() {
          return new Promise((resolve) => {
            const { exec } = require('child_process');
            exec('php artisan migrate:refresh', (error, stdout, stderr) => {
              if (error) {
                console.error(`Error: ${error}`);
                resolve(false);
              } else {
                console.log(`Database refreshed: ${stdout}`);
                resolve(true);
              }
            });
          });
        },

        'generate:testdata'(type = 'full') {
          return new Promise((resolve) => {
            const { exec } = require('child_process');
            exec(`php artisan test:generate-data --type=${type}`, (error, stdout, stderr) => {
              if (error) {
                console.error(`Error: ${error}`);
                resolve(false);
              } else {
                console.log(`Test data generated: ${stdout}`);
                resolve(true);
              }
            });
          });
        },

        'log'(message) {
          console.log('Cypress Task Log:', message);
          return null;
        }
      });

      // Configuración específica por entorno
      if (config.env.NODE_ENV === 'ci') {
        config.video = true;
        config.screenshotOnRunFailure = true;
        config.viewportWidth = 1920;
        config.viewportHeight = 1080;
      }

      // Configuración de retry para CI
      if (config.env.CI) {
        config.retries = {
          runMode: 2,
          openMode: 0
        };
      }

      return config;
    }
  },

  // Configuración para component testing (futuro)
  component: {
    devServer: {
      framework: 'react',
      bundler: 'vite',
    },
    specPattern: 'cypress/component/**/*.cy.{js,jsx,ts,tsx}',
    viewportWidth: 1000,
    viewportHeight: 660
  }
});