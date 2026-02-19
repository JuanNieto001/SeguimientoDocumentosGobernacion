@echo off
:: Solicitar permisos de administrador
>nul 2>&1 "%SYSTEMROOT%\system32\cacls.exe" "%SYSTEMROOT%\system32\config\system"
if '%errorlevel%' NEQ '0' (
    echo Solicitando permisos de administrador...
    goto UACPrompt
) else ( goto gotAdmin )

:UACPrompt
    echo Set UAC = CreateObject^("Shell.Application"^) > "%temp%\getadmin.vbs"
    echo UAC.ShellExecute "%~s0", "", "", "runas", 1 >> "%temp%\getadmin.vbs"
    "%temp%\getadmin.vbs"
    exit /B

:gotAdmin
    if exist "%temp%\getadmin.vbs" ( del "%temp%\getadmin.vbs" )
    pushd "%CD%"
    CD /D "%~dp0"

echo ================================================
echo  DESACTIVANDO FIREWALL TEMPORALMENTE
echo ================================================
echo.

netsh advfirewall set allprofiles state off

if %errorlevel% equ 0 (
    echo [OK] Firewall desactivado correctamente
    echo.
    echo AHORA INICIA EL SERVIDOR CON: iniciar_servidor.bat
    echo.
    echo Para reactivar el firewall despues, ejecuta:
    echo reactivar_firewall_admin.bat
) else (
    echo [ERROR] No se pudo desactivar el firewall
)

echo.
pause
