@echo off
color 0A
echo ========================================
echo EJECUCION DE PRUEBAS QA - CERTIFICACION
echo ========================================
echo.
echo INSTRUCCIONES:
echo 1. El navegador se abrira automaticamente
echo 2. Veras cada prueba ejecutandose en vivo
echo 3. Los resultados se guardan en test-results/
echo 4. Al final se genera reporte HTML
echo.
echo Presiona cualquier tecla para iniciar...
pause > nul

echo.
echo [INFO] Verificando servidor...
curl -s http://localhost:8000 > nul
if %errorlevel% neq 0 (
    echo [ERROR] Servidor no esta corriendo en localhost:8000
    echo [INFO] Inicia el servidor primero: php artisan serve
    pause
    exit /b 1
)

echo [OK] Servidor corriendo
echo.
echo [INFO] Iniciando pruebas...
echo.

REM Ejecutar todas las pruebas con UI
npx playwright test --headed --workers=1 --reporter=html,list

echo.
echo ========================================
echo PRUEBAS COMPLETADAS
echo ========================================
echo.
echo Evidencias guardadas en:
echo - test-results/         (Screenshots y Videos)
echo - playwright-report/    (Reporte HTML)
echo.
echo Para ver el reporte:
echo   npm run test:report
echo.
pause
