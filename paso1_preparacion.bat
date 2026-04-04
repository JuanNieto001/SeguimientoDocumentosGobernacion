@echo off
echo ========================================
echo PASO 1 - PREPARACION: Dashboard Builder
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Instalando librerias npm...
call npm install recharts @tanstack/react-query zustand @dnd-kit/core @dnd-kit/utilities react-grid-layout nanoid --save
if errorlevel 1 (
    echo ERROR: No se pudieron instalar las librerias npm
    pause
    exit /b 1
)
echo.

echo [2/3] Ejecutando migracion: scope_level en roles...
php artisan migrate --path=database/migrations/2026_04_04_150001_add_scope_level_to_roles_table.php
if errorlevel 1 (
    echo ERROR: No se pudo ejecutar la migracion
    pause
    exit /b 1
)
echo.

echo [3/3] Ejecutando seeders...
php artisan db:seed --class=RoleScopeLevelSeeder
php artisan db:seed --class=DashboardBuilderPermissionSeeder
if errorlevel 1 (
    echo ERROR: No se pudieron ejecutar los seeders
    pause
    exit /b 1
)
echo.

echo ========================================
echo PASO 1 COMPLETADO EXITOSAMENTE
echo ========================================
echo.
echo Siguiente paso: Ejecutar PASO 2 (Backend Laravel)
echo.
pause
