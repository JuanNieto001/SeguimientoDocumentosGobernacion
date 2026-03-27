@echo off
REM ============================================================================
REM SCRIPT DE EJECUCION DE PRUEBAS CYPRESS - FASE 3 (Windows)
REM Sistema de Seguimiento de Documentos Contractuales - Gobernación de Caldas
REM ============================================================================

setlocal enabledelayedexpansion
chcp 65001 > nul

color 0A
cls

echo.
echo ╔════════════════════════════════════════════════════════════════════════════╗
echo ║  FASE 3 - AUTOMATIZACIÓN CON CYPRESS                                      ║
echo ║  Sistema de Seguimiento de Documentos Contractuales                        ║
echo ╚════════════════════════════════════════════════════════════════════════════╝
echo.

REM Crear directorios
if not exist "cypress\reports" mkdir cypress\reports
if not exist "cypress\screenshots" mkdir cypress\screenshots
if not exist "cypress\videos" mkdir cypress\videos

REM Verificar package.json
if not exist "package.json" (
    color 0C
    echo ✗ package.json no encontrado
    pause
    exit /b 1
)

echo ✓ Configuración del proyecto verificada
echo.

REM Menú principal
echo.
echo Selecciona el tipo de ejecución:
echo.
echo   1) Ejecutar TODOS los tests (modo headless)
echo   2) Ejecutar módulo específico
echo   3) Modo interactivo (Cypress UI)
echo   4) Ejecutar con video y reporte
echo   5) Ejecutar autenticación (AUTH-001 a AUTH-011)
echo   6) Ejecutar dashboard (DASH-001 a DASH-015)
echo   7) Ejecutar procesos (PROC-001 a PROC-020)
echo   8) Ejecutar contratación directa (CDPN-001 a CDPN-033)
echo   9) Ejecutar dashboard builder (BUILD-001 a BUILD-040)
echo  10) Ejecutar seguridad (SEC-001 a SEC-008)
echo.
set /p OPTION="Ingresa opción (1-10): "

color 0A

if "%OPTION%"=="1" goto run_all
if "%OPTION%"=="2" goto run_module
if "%OPTION%"=="3" goto run_interactive
if "%OPTION%"=="4" goto run_with_video
if "%OPTION%"=="5" goto run_auth
if "%OPTION%"=="6" goto run_dashboard
if "%OPTION%"=="7" goto run_procesos
if "%OPTION%"=="8" goto run_cdpn
if "%OPTION%"=="9" goto run_builder
if "%OPTION%"=="10" goto run_security
goto invalid_option

:run_all
echo.
echo Ejecutando TODOS los tests...
echo.
call npm run cypress:run
goto completion

:run_module
echo.
set /p MODULE="Ingresa ruta del módulo (ej: 01-authentication): "
echo Ejecutando tests de %MODULE%...
echo.
call npx cypress run --spec "cypress/e2e/%MODULE%/*.cy.js"
goto completion

:run_interactive
echo.
echo Abriendo Cypress en modo interactivo...
echo.
call npx cypress open
goto completion

:run_with_video
echo.
echo Ejecutando con video y reporte...
echo.
call npx cypress run --spec "cypress/e2e/**/*.cy.js" --record
goto completion

:run_auth
echo.
echo Ejecutando TESTS DE AUTENTICACION (AUTH-001 a AUTH-011)
echo.
call npx cypress run --spec "cypress/e2e/01-authentication/auth-completo.cy.js"
goto completion

:run_dashboard
echo.
echo Ejecutando TESTS DE DASHBOARD (DASH-001 a DASH-015)
echo.
call npx cypress run --spec "cypress/e2e/02-dashboard/dashboard-completo.cy.js"
goto completion

:run_procesos
echo.
echo Ejecutando TESTS DE PROCESOS (PROC-001 a PROC-020)
echo.
call npx cypress run --spec "cypress/e2e/03-procesos/procesos-completo.cy.js"
goto completion

:run_cdpn
echo.
echo Ejecutando TESTS CONTRATACION DIRECTA (CDPN-001 a CDPN-033)
echo.
call npx cypress run --spec "cypress/e2e/04-contratacion-directa/cdpn-completo.cy.js"
goto completion

:run_builder
echo.
echo Ejecutando TESTS DASHBOARD BUILDER (BUILD-001 a BUILD-040)
echo.
call npx cypress run --spec "cypress/e2e/05-dashboard-builder/dashboard-builder.cy.js"
goto completion

:run_security
echo.
echo Ejecutando TESTS DE SEGURIDAD (SEC-001 a SEC-008)
echo.
call npx cypress run --spec "cypress/e2e/06-seguridad-rendimiento/seguridad-rendimiento.cy.js"
goto completion

:invalid_option
color 0C
echo.
echo ✗ Opción inválida
color 0A
pause
exit /b 1

:completion
echo.
echo ════════════════════════════════════════════════════════════════════════════
echo ✓ FASE 3 completada exitosamente
echo.
echo 📊 Resultados guardados en:
echo    - Screenshots: cypress\screenshots
echo    - Videos: cypress\videos
echo    - Reportes: cypress\reports
echo.
echo Abre cualquiera de estas carpetas para ver la evidencia
echo ════════════════════════════════════════════════════════════════════════════
echo.
pause
