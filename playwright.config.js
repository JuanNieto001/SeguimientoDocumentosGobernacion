import { defineConfig, devices } from '@playwright/test';

const DEFAULT_SLOW_MO_MS = 180;
const DEMO_SLOW_MO_MS = 700;

const envSlowMo = Number(process.env.PW_SLOWMO_MS);
const resolvedSlowMoMs = Number.isFinite(envSlowMo) && envSlowMo >= 0
  ? envSlowMo
  : process.env.PW_DEMO_MODE === '1'
    ? DEMO_SLOW_MO_MS
    : DEFAULT_SLOW_MO_MS;

const resolvedVideoMode = process.env.PW_VIDEO_MODE || 'on';

export default defineConfig({
  testDir: './tests',
  timeout: 60000,
  fullyParallel: false,  // Correr tests secuencialmente
  workers: 1,            // Solo 1 worker
  
  // 🔥 EVIDENCIAS COMPLETAS
  use: {
    screenshot: 'on',
    video: {
      mode: resolvedVideoMode,
      size: { width: 1366, height: 768 },
    },
    trace: 'on',
    baseURL: 'http://localhost:8000',
    navigationTimeout: 30000,
    actionTimeout: 10000,
    headless: false,  // Abrir navegador para ver qué pasa
    launchOptions: {
      slowMo: resolvedSlowMoMs,
    },
  },

  // Configuración de reportes
  reporter: [
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
    ['json', { outputFile: 'test-results/results.json' }],
    ['junit', { outputFile: 'test-results/junit.xml' }],
    ['list'],
    ['./custom-reporter.js'] // Reporter personalizado para evidencias
  ],

  // Reintentos automáticos
  retries: 2,

  // Navegadores a probar
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
  ],

  // Servidor de desarrollo
  webServer: {
    command: 'php artisan serve',
    url: 'http://localhost:8000',
    reuseExistingServer: true,
    timeout: 120000,
  },
});
