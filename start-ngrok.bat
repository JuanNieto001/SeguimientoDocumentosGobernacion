@echo off
REM 🌍 NGROK START SCRIPT - Windows
REM Sistema de Seguimiento de Documentos Contractuales
REM Inicia Ngrok para exponer tu API localmente

setlocal enabledelayedexpansion
chcp 65001 > nul

color 0A
cls

echo.
echo ╔════════════════════════════════════════════════════════════════════════╗
echo ║                                                                        ║
echo ║                    🌍 NGROK TUNNEL STARTER                            ║
echo ║              Expone tu API local a través de Internet                 ║
echo ║                                                                        ║
echo ╚════════════════════════════════════════════════════════════════════════╝
echo.

REM Verificar que ngrok está instalado
where ngrok >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    color 0C
    echo ❌ Ngrok no está instalado
    echo.
    echo Instala con:
    echo   npm install -g ngrok
    echo.
    echo O descarga desde:
    echo   https://ngrok.com/download
    echo.
    pause
    exit /b 1
)

color 0A

echo ✅ Ngrok detectado
echo.

REM Verificar autenticación
if not exist "%USERPROFILE%\.ngrok2\ngrok.yml" (
    color 0E
    echo ⚠️  No hay token de autenticación configurado
    echo.
    color 0A
    echo Ejecuta primero:
    echo   ngrok config add-authtoken TU_TOKEN
    echo.
    echo Obtén tu token en:
    echo   https://dashboard.ngrok.com/auth/your-authtoken
    echo.
    pause
    exit /b 1
)

color 0A
echo ✅ Token de autenticación encontrado
echo.

REM Menu de opciones
echo Selecciona qué puerto exponer:
echo.
echo   1) API (8000) - Recomendado
echo   2) Frontend (5173)
echo   3) Puerto personalizado
echo   4) Ver instrucciones
echo.
set /p OPTION="Ingresa opción (1-4): "

echo.
color 0A

if "%OPTION%"=="1" goto api_tunnel
if "%OPTION%"=="2" goto frontend_tunnel
if "%OPTION%"=="3" goto custom_tunnel
if "%OPTION%"=="4" goto instructions
goto invalid_option

:api_tunnel
echo 🚀 Exponiendo API en puerto 8000...
echo.
ngrok http 8000
goto end

:frontend_tunnel
echo 🚀 Exponiendo Frontend en puerto 5173...
echo.
ngrok http 5173
goto end

:custom_tunnel
set /p PORT="Ingresa puerto: "
echo 🚀 Exponiendo puerto %PORT%...
echo.
ngrok http %PORT%
goto end

:instructions
echo.
echo ════════════════════════════════════════════════════════════════════════
echo.
echo 📚 INSTRUCCIONES:
echo.
echo   1. Abre un terminal con Laravel:
echo      php artisan serve
echo.
echo   2. Abre otro terminal y ejecuta:
echo      start-ngrok.bat
echo.
echo   3. Selecciona opción 1 (Puerto 8000)
echo.
echo   4. Verás una URL pública: https://abc123.ngrok.io
echo.
echo   5. Accede desde cualquier máquina:
echo      https://abc123.ngrok.io
echo.
echo   6. Para testing remoto con Cypress:
echo      • Edita cypress.config.js
echo      • Cambia baseUrl a la URL de Ngrok
echo      • Ejecuta: npm run cypress:run
echo.
echo ════════════════════════════════════════════════════════════════════════
echo.
pause
exit /b 0

:invalid_option
color 0C
echo ❌ Opción inválida
color 0A
pause
exit /b 1

:end
echo.
echo ════════════════════════════════════════════════════════════════════════
echo.
echo 💡 TIPS:
echo.
echo   📊 Dashboard: http://localhost:4040
echo   🛑 Para detener: Ctrl + C
echo   🔗 URL pública se muestra arriba
echo.
echo ════════════════════════════════════════════════════════════════════════
echo.
pause
