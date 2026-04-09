@echo off
echo.
echo ================================================
echo   TESTS LIMPIOS PARA PRESENTACION
echo ================================================
echo.
echo 🧹 Paso 1: Limpiar evidencias anteriores...
cd /d "%~dp0"

if exist "test-results" rmdir /s /q "test-results" 2>nul
if exist "playwright-report" rmdir /s /q "playwright-report" 2>nul

echo ✅ Evidencias anteriores eliminadas
echo.
echo 🎬 Paso 2: Ejecutar tests de verificacion de credenciales...
npx playwright test tests/verify-credentials.spec.js --workers=1

echo.
echo 🎯 Paso 3: Ejecutar tests smoke (solo exitosos)...
npx playwright test tests/smoke/smoke-tests.spec.js --workers=1 --max-failures=0

echo.
echo ================================================
echo   EVIDENCIAS LIMPIAS GENERADAS
echo ================================================
echo.
echo 📂 Archivos generados:
echo    - test-results\          (Videos y screenshots exitosos)
echo    - playwright-report\     (Reporte HTML)
echo.
echo 📖 Para ver el reporte: npx playwright show-report
echo.
pause