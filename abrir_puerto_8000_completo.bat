@echo off
echo ================================================
echo   CONFIGURACION FIREWALL LARAVEL SERVER
echo ================================================
echo.
echo Eliminando reglas duplicadas...
powershell -Command "Remove-NetFirewallRule -DisplayName 'Laravel Dev Server' -ErrorAction SilentlyContinue"
powershell -Command "Remove-NetFirewallRule -DisplayName 'PHP Laravel' -ErrorAction SilentlyContinue"
echo.
echo Creando regla para puerto 8000...
powershell -Command "New-NetFirewallRule -DisplayName 'Laravel Dev Server' -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -Profile Domain,Private,Public"
echo.
echo Creando regla para PHP...
powershell -Command "New-NetFirewallRule -DisplayName 'PHP Laravel' -Direction Inbound -Program 'C:\xampp\php\php.exe' -Action Allow -Profile Domain,Private,Public"
echo.
echo ================================================
echo   CONFIGURACION COMPLETADA
echo ================================================
echo.
echo Ahora los otros PCs deben poder conectarse a:
echo   http://192.168.231.9:8000
echo.
pause
