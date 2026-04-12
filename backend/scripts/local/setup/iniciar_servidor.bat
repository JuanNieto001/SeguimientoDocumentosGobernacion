@echo off
set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%\..\..\..") do set "BACKEND_DIR=%%~fI"
set "PUBLIC_DIR=%BACKEND_DIR%\public"
set "ROUTER_FILE=%PUBLIC_DIR%\router.php"
set "FRONTEND_DIR=%BACKEND_DIR%\..\frontend"
set "MANIFEST_FILE=%PUBLIC_DIR%\build\manifest.json"
set "HOT_FILE=%PUBLIC_DIR%\hot"

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

if exist "%HOT_FILE%" goto assets_ready
if exist "%MANIFEST_FILE%" goto assets_ready

echo [WARN] No se encontraron assets Vite (public\build\manifest.json ni public\hot).
if exist "%FRONTEND_DIR%\package.json" (
	where npm >nul 2>&1
	if errorlevel 1 (
		echo [WARN] npm no esta disponible. Se iniciara en modo seguro sin assets compilados.
	) else (
		echo [OK] Compilando frontend con Vite...
		call npm --prefix "%FRONTEND_DIR%" run build
		if errorlevel 1 (
			echo [WARN] Fallo la compilacion de frontend. Se iniciara en modo seguro.
		)
	)
) else (
	echo [WARN] No se encontro carpeta frontend para compilar assets.
)

:assets_ready
if not exist "%HOT_FILE%" if not exist "%MANIFEST_FILE%" (
	echo [WARN] Continuando sin assets compilados. El sistema no debe caerse, pero puede verse sin estilos/JS.
	echo [WARN] Ejecuta: npm --prefix ..\frontend run build
)

echo ================================================
echo  INICIANDO SERVIDOR LARAVEL EN RED LOCAL
echo ================================================
echo.

REM Detener procesos PHP anteriores
taskkill /F /IM php.exe >nul 2>&1

echo [OK] Limpiando procesos anteriores...
ping 127.0.0.1 -n 3 >nul

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
