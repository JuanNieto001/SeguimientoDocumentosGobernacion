@echo off
echo Deteniendo MariaDB...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 3 /nobreak >nul

echo Iniciando MariaDB en modo seguro...
cd /d C:\xampp\mysql\bin
start /B mysqld.exe --skip-grant-tables --console

timeout /t 5 /nobreak >nul

echo Cambiando método de autenticación...
mysql.exe -u root --protocol=TCP -e "FLUSH PRIVILEGES; ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password; FLUSH PRIVILEGES;"

echo Deteniendo MariaDB temporal...
taskkill /F /IM mysqld.exe
timeout /t 2 /nobreak >nul

echo.
echo Listo! Ahora inicia MariaDB normalmente desde XAMPP
pause
