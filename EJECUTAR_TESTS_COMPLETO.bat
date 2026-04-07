@echo off
echo ════════════════════════════════════════════════════════════════
echo     EJECUTAR TESTS PLAYWRIGHT - CERTIFICACIÓN QA COMPLETA
echo ════════════════════════════════════════════════════════════════
echo.
echo ✅ Configuración:
echo    - Screenshots: ON (siempre)
echo    - Videos: ON (siempre)
echo    - Traces: ON (siempre)
echo    - Modo: Headless (sin abrir navegador)
echo.
echo 📋 Tests disponibles: ~108 tests
echo    - E2E Flujo Completo: 5 tests
echo    - Datos DEMO: 4 tests
echo    - Workflow CD-PN: 35 tests
echo    - Procesos: 24 tests
echo    - Autenticación: 6 tests
echo    - Otros módulos: 34 tests
echo.
echo ⚠️  Dashboard tests: DESHABILITADOS (issues conocidos)
echo.
echo ════════════════════════════════════════════════════════════════
echo.

choice /C 123 /M "Selecciona: [1] TODOS los tests  [2] Solo E2E+DEMO  [3] Cancelar"

if errorlevel 3 goto :eof
if errorlevel 2 goto :e2e
if errorlevel 1 goto :all

:all
echo.
echo 🚀 Ejecutando TODOS los tests...
echo.
npx playwright test --reporter=html,list,./custom-reporter.js
goto :end

:e2e
echo.
echo 🚀 Ejecutando solo tests E2E y DEMO...
echo.
npx playwright test tests/e2e --reporter=html,list,./custom-reporter.js
goto :end

:end
echo.
echo ════════════════════════════════════════════════════════════════
echo     ✅ EJECUCIÓN COMPLETADA
echo ════════════════════════════════════════════════════════════════
echo.
echo 📁 Evidencias guardadas en: test-results\
echo 📄 Reporte CSV: test-results\resultados-certificacion.csv
echo 📄 Reporte MD: test-results\REPORTE_CERTIFICACION.md
echo 📊 Reporte HTML: npx playwright show-report
echo.
echo 🔍 Para ver el reporte HTML ejecuta:
echo    npx playwright show-report
echo.
pause
