const { defineConfig } = require('cypress');

module.exports = defineConfig({
    e2e: {
        baseUrl: 'http://localhost:8000',
        specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
        supportFile: 'cypress/support/e2e.js',
        viewportWidth: 1280,
        viewportHeight: 720,
        video: true,
        videoCompression: 32,
        screenshotOnRunFailure: true,
        screenshotsFolder: 'cypress/screenshots',
        videosFolder: 'cypress/videos',
        downloadsFolder: 'cypress/downloads',
        trashAssetsBeforeRuns: false,
        defaultCommandTimeout: 10000,
        requestTimeout: 10000,
        responseTimeout: 30000,
        pageLoadTimeout: 60000,
        retries: {
            runMode: 2,
            openMode: 0,
        },
        env: {
            apiUrl: 'http://localhost:8000/api',
            adminEmail: 'admin@test.com',
            adminPassword: 'Test1234!',
            unidadEmail: 'unidad@test.com',
            unidadPassword: 'Test1234!',
            planeacionEmail: 'planeacion@test.com',
            planeacionPassword: 'Test1234!',
            haciendaEmail: 'hacienda@test.com',
            haciendaPassword: 'Test1234!',
            juridicaEmail: 'juridica@test.com',
            juridicaPassword: 'Test1234!',
            secopEmail: 'secop@test.com',
            secopPassword: 'Test1234!',
            gobernadorEmail: 'gobernador@test.com',
            gobernadorPassword: 'Test1234!',
            consultaEmail: 'consulta@test.com',
            consultaPassword: 'Test1234!',
        },
        setupNodeEvents(on, config) {
            on('task', {
                log(message) {
                    console.log(message);
                    return null;
                },
            });
            return config;
        },
    },
});
