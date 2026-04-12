@echo off
echo ====================================================================
echo  CONFIGURACION INICIAL DEL PROYECTO
echo ====================================================================
echo.

cd /d "%~dp0\..\..\.."

echo [1/4] Instalando dependencias de Composer...
call composer install
if errorlevel 1 (
    echo ERROR: Composer install falló
    pause
    exit /b 1
)

echo.
echo [2/4] Instalando dependencias de NPM...
call npm.cmd --prefix ..\frontend install
if errorlevel 1 (
    echo ERROR: npm install falló
    pause
    exit /b 1
)

echo.
echo [3/4] Copiando archivo .env...
if not exist .env (
    copy .env.example .env
    echo Archivo .env creado
)

echo.
echo [4/5] Generando key de Laravel...
php artisan key:generate

echo.
echo [5/5] Compilando assets frontend (Vite build)...
call npm.cmd --prefix ..\frontend run build
if errorlevel 1 (
    echo ADVERTENCIA: Fallo la compilacion de assets. El sistema iniciara en modo seguro, pero revisa Node/NPM.
)

echo.
echo ====================================================================
echo  PROYECTO CONFIGURADO - Ahora ejecuta: scripts\local\setup\iniciar_servidor.bat
echo ====================================================================
pause
