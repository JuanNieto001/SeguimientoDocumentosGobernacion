@echo off
echo Creando estructura de carpetas para Dashboard Builder...

cd /d "%~dp0"

mkdir "App\Services\Dashboard" 2>nul
mkdir "App\Http\Controllers\Dashboard" 2>nul
mkdir "resources\views\dashboard" 2>nul

echo Carpetas creadas exitosamente.
echo.
echo Estructura creada:
echo - App\Services\Dashboard\
echo - App\Http\Controllers\Dashboard\
echo - resources\views\dashboard\
echo.
pause
