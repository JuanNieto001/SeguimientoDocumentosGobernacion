@echo off
echo.
echo ================================================
echo   ABRIENDO PLAYWRIGHT UI INTERACTIVO
echo ================================================
echo.
echo 🎭 Esto abre la interfaz visual de Playwright donde puedes:
echo    ✅ Ver todos los tests disponibles
echo    ✅ Ejecutar tests individuales
echo    ✅ Ver videos y screenshots en tiempo real
echo    ✅ Debug step-by-step
echo    ✅ Inspeccionar elementos
echo.
echo 📂 Los resultados se guardan en:
echo    - test-results/      (Videos, screenshots)
echo    - playwright-report/ (Reportes HTML)
echo.

cd /d "%~dp0"
npx playwright test --ui

echo.
echo ================================================
echo   PLAYWRIGHT UI CERRADO
echo ================================================
pause