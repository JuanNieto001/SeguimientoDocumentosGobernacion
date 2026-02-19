@echo off
echo ================================================
echo  CONFIGURANDO FIREWALL PARA ACCESO EN RED
echo ================================================
echo.

netsh advfirewall firewall delete rule name="Laravel Dev 8000" >nul 2>&1
netsh advfirewall firewall add rule name="Laravel Dev 8000" dir=in action=allow protocol=TCP localport=8000 profile=any enable=yes

if %errorlevel% equ 0 (
    echo.
    echo [OK] Regla de firewall creada exitosamente
    echo.
    echo ================================================
    echo  ACCEDE DESDE OTROS COMPUTADORES USANDO:
    echo  http://10.174.112.27:8000
    echo ================================================
    echo.
) else (
    echo.
    echo [ERROR] No se pudo crear la regla de firewall
    echo Asegurate de ejecutar este archivo como Administrador
    echo.
)

pause
