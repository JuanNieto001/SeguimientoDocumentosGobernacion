@echo off
echo ====================================
echo PLAYWRIGHT - PRUEBAS AUTOMATIZADAS
echo ====================================
echo.
echo Selecciona una opcion:
echo.
echo 1. Interfaz UI (Recomendado)
echo 2. Ejecutar todas las pruebas
echo 3. Solo pruebas de autenticacion
echo 4. Solo pruebas de dashboard
echo 5. Solo pruebas de workflow
echo 6. Ver reporte HTML
echo 7. Debug (paso a paso)
echo.
set /p opcion="Ingresa el numero: "

if "%opcion%"=="1" (
    echo.
    echo Abriendo interfaz UI...
    npm test
)
if "%opcion%"=="2" (
    echo.
    echo Ejecutando todas las pruebas...
    npm run test:run
)
if "%opcion%"=="3" (
    echo.
    echo Ejecutando pruebas de autenticacion...
    npm run test:auth
)
if "%opcion%"=="4" (
    echo.
    echo Ejecutando pruebas de dashboard...
    npm run test:dashboard
)
if "%opcion%"=="5" (
    echo.
    echo Ejecutando pruebas de workflow...
    npm run test:workflow
)
if "%opcion%"=="6" (
    echo.
    echo Abriendo reporte HTML...
    npm run test:report
)
if "%opcion%"=="7" (
    echo.
    echo Iniciando debug...
    npm run test:debug
)

echo.
echo ====================================
echo EVIDENCIAS:
echo - Screenshots y videos: test-results/
echo - Reporte HTML: playwright-report/
echo ====================================
pause
