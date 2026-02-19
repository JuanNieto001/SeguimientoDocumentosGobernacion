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
echo  REACTIVANDO FIREWALL DE WINDOWS
echo ================================================
echo.

netsh advfirewall set allprofiles state on

if %errorlevel% equ 0 (
    echo [OK] Firewall reactivado correctamente
) else (
    echo [ERROR] No se pudo reactivar el firewall
)

echo.
pause
