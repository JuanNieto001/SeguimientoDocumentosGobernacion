@echo off
echo Solucionando autenticacion de MariaDB...
echo.

cd /d C:\xampp\mysql\bin

echo Deteniendo MariaDB...
C:\Windows\System32\taskkill.exe /F /IM mysqld.exe 2>nul
timeout /t 3 /nobreak >nul

echo Iniciando MariaDB en modo seguro (sin autenticacion)...
start /B mysqld.exe --skip-grant-tables --skip-networking

timeout /t 5 /nobreak >nul

echo Conectando y cambiando autenticacion...
echo USE mysql; > fix_temp.sql
echo UPDATE user SET plugin='mysql_native_password' WHERE User='root'; >> fix_temp.sql
echo UPDATE user SET Password=PASSWORD('') WHERE User='root'; >> fix_temp.sql  
echo FLUSH PRIVILEGES; >> fix_temp.sql

mysql.exe -u root --protocol=TCP --port=3306 ^< fix_temp.sql

del fix_temp.sql

echo Deteniendo MariaDB...
C:\Windows\System32\taskkill.exe /F /IM mysqld.exe
timeout /t 2 /nobreak >nul

echo.
echo ==================================================
echo LISTO! Ahora inicia MariaDB desde XAMPP normalmente
echo ==================================================
pause
