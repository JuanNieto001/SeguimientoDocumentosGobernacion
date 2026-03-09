@echo off
cd /d "%~dp0"

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
echo  http://10.174.112.27:8000
echo ================================================
echo.
echo IMPORTANTE: NO CIERRES ESTA VENTANA
echo El servidor se detendrá si cierras esta ventana
echo.
echo Para detener el servidor presiona Ctrl+C
echo ================================================
echo.

REM Iniciar servidor (router.php maneja URLs con puntos como CO1.PCCNTR.xxx)
php -S 0.0.0.0:8000 -t public public/router.php

pause
