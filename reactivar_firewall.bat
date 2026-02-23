@echo off
echo ================================================
echo  REACTIVANDO FIREWALL DE WINDOWS
echo ================================================
echo.

netsh advfirewall set allprofiles state on

if %errorlevel% equ 0 (
    echo.
    echo [OK] Firewall reactivado correctamente
    echo.
) else (
    echo.
    echo [ERROR] No se pudo reactivar el firewall
    echo Ejecuta este archivo como Administrador
    echo.
)

pause
