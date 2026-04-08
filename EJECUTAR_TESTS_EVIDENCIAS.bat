@echo off
echo.
echo ================================================
echo   EJECUTANDO TESTS SMOKE CON EVIDENCIAS
echo ================================================
echo.
echo 🎬 Configuración de evidencias:
echo    ✅ Videos: ACTIVADOS
echo    ✅ Screenshots: ACTIVADOS  
echo    ✅ Traces: ACTIVADOS
echo    ✅ Modo visual: ACTIVADO
echo.
echo 📂 Las evidencias se guardan en:
echo    - test-results\       (Videos .webm, screenshots .png)
echo    - playwright-report\  (Reporte HTML interactivo)
echo.

cd /d "%~dp0"

echo 🚀 Ejecutando tests smoke...
npx playwright test tests/smoke/smoke-tests.spec.js --workers=1

echo.
echo ================================================
echo   TESTS COMPLETADOS
echo ================================================
echo.
echo 📖 Para ver el reporte completo:
echo    1. Abre: playwright-report\index.html
echo    2. O ejecuta: npx playwright show-report
echo.
echo 🎥 Videos disponibles en: test-results\
echo.
pause