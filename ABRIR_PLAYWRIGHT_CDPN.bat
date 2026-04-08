@echo off
echo.
echo ==================================================================
echo      PRUEBAS E2E - FLUJO CD-PN CON SECOP
echo ==================================================================
echo.
echo Limpiando evidencias antiguas...
if exist test-results\*.png del /Q test-results\*.png
if exist test-results\*.webm del /Q test-results\*.webm
if exist playwright-report rmdir /S /Q playwright-report
echo.
echo Abriendo Playwright UI para pruebas CD-PN...
echo.
echo IMPORTANTE:
echo - La cedula SECOP de prueba es: 1053850113
echo - Se ejecutaran 5 tests:
echo   * CDPN-001: Crear proceso
echo   * CDPN-002: Gestion archivos
echo   * CDPN-003: Consulta SECOP
echo   * CDPN-004: Permisos Admin
echo   * CDPN-005: Flujo completo E2E
echo.
echo ==================================================================
echo.
npx playwright test tests/e2e/flujo-cdpn-completo.spec.js --ui
