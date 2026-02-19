@echo off
echo ================================================
echo  DESACTIVANDO FIREWALL TEMPORALMENTE
echo  (para probar si ese es el problema)
echo ================================================
echo.

netsh advfirewall set allprofiles state off

if %errorlevel% equ 0 (
    echo.
    echo [OK] Firewall desactivado temporalmente
    echo.
    echo ================================================
    echo  PRUEBA AHORA DESDE OTRO COMPUTADOR:
    echo  http://10.174.112.27:8000
    echo ================================================
    echo.
    echo IMPORTANTE: Para reactivar el firewall despues,
    echo ejecuta: reactivar_firewall.bat
    echo.
) else (
    echo.
    echo [ERROR] No se pudo desactivar el firewall
    echo Ejecuta este archivo como Administrador
    echo.
)

pause
