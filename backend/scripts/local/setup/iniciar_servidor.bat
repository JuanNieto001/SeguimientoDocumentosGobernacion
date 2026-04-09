@echo off
set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%\..\..\..") do set "BACKEND_DIR=%%~fI"
set "PUBLIC_DIR=%BACKEND_DIR%\public"
set "ROUTER_FILE=%PUBLIC_DIR%\router.php"

cd /d "%BACKEND_DIR%"

set "PHP_CMD=php"
if exist "C:\xampp\php\php.exe" set "PHP_CMD=C:\xampp\php\php.exe"

if not exist "%ROUTER_FILE%" (
	echo [ERROR] No se encontro el router de Laravel en:
	echo         %ROUTER_FILE%
	echo.
	echo Verifica que estas en la estructura nueva con carpeta backend/.
	pause
	exit /b 1
)

echo ================================================
echo  INICIANDO SERVIDOR LARAVEL EN RED LOCAL
echo ================================================
echo.

REM Detener procesos PHP anteriores
taskkill /F /IM php.exe >nul 2>&1

echo [OK] Limpiando procesos anteriores...
timeout /t 2 /nobreak >nul

echo [OK] Iniciando servidor en 0.0.0.0:8000...
echo.
echo ================================================
echo  ACCESO DESDE ESTE EQUIPO:
echo  http://localhost:8000
echo.
echo  ACCESO DESDE OTROS EQUIPOS EN LA RED:
echo  Usa la IP local de este equipo (ej: http://192.168.x.x:8000)
echo ================================================
echo.
echo IMPORTANTE: NO CIERRES ESTA VENTANA
echo El servidor se detendrá si cierras esta ventana
echo.
echo Para detener el servidor presiona Ctrl+C
echo ================================================
echo.

REM Iniciar servidor (router.php maneja URLs con puntos como CO1.PCCNTR.xxx)
"%PHP_CMD%" -S 0.0.0.0:8000 -t "%PUBLIC_DIR%" "%ROUTER_FILE%"

pause
