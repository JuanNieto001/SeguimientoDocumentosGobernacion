@echo off
chcp 65001 > nul
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║   DASHBOARD BUILDER - INSTALACIÓN COMPLETA (PASOS 1, 2 y 3) ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

cd /d "%~dp0"

REM ═══════════════════════════════════════════════════════════════
REM  PASO 1: PREPARACIÓN
REM ═══════════════════════════════════════════════════════════════

echo ┌─────────────────────────────────────────────────────────────┐
echo │ PASO 1/7: Instalando librerías npm...                       │
echo └─────────────────────────────────────────────────────────────┘
call npm install recharts @tanstack/react-query zustand @dnd-kit/core @dnd-kit/utilities react-grid-layout nanoid --save
if errorlevel 1 (
    echo ❌ ERROR en npm install
    pause
    exit /b 1
)
echo ✅ Librerías instaladas
echo.

echo ┌─────────────────────────────────────────────────────────────┐
echo │ PASO 2/7: Ejecutando migración scope_level...               │
echo └─────────────────────────────────────────────────────────────┘
php artisan migrate --path=database/migrations/2026_04_04_150001_add_scope_level_to_roles_table.php --force
if errorlevel 1 (
    echo ❌ ERROR en migración
    pause
    exit /b 1
)
echo ✅ Migración ejecutada
echo.

echo ┌─────────────────────────────────────────────────────────────┐
echo │ PASO 3/7: Ejecutando seeders...                             │
echo └─────────────────────────────────────────────────────────────┘
php artisan db:seed --class=RoleScopeLevelSeeder --force
if errorlevel 1 (
    echo ❌ ERROR en RoleScopeLevelSeeder
    pause
    exit /b 1
)
echo ✅ RoleScopeLevelSeeder ejecutado
echo.

php artisan db:seed --class=DashboardBuilderPermissionSeeder --force
if errorlevel 1 (
    echo ❌ ERROR en DashboardBuilderPermissionSeeder
    pause
    exit /b 1
)
echo ✅ DashboardBuilderPermissionSeeder ejecutado
echo.

echo ┌─────────────────────────────────────────────────────────────┐
echo │ PASO 4/7: Creando estructura de carpetas...                 │
echo └─────────────────────────────────────────────────────────────┘
mkdir "App\Services\Dashboard" 2>nul
mkdir "App\Http\Controllers\Dashboard" 2>nul
mkdir "resources\views\dashboard" 2>nul
mkdir "resources\js\modules\dashboard\components\widgets" 2>nul
mkdir "resources\js\modules\dashboard\hooks" 2>nul
mkdir "resources\js\modules\dashboard\store" 2>nul
mkdir "resources\js\modules\dashboard\types" 2>nul
echo ✅ Carpetas creadas
echo.

echo ┌─────────────────────────────────────────────────────────────┐
echo │ PASO 5/7: Limpiando caché de Laravel...                     │
echo └─────────────────────────────────────────────────────────────┘
php artisan config:clear
php artisan cache:clear
php artisan permission:cache-reset 2>nul
echo ✅ Caché limpiado
echo.

echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║              ✅ PASO 1 COMPLETADO                            ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Ahora necesitas copiar los archivos del backend y frontend
echo que están en las carpetas temporales.
echo.
echo Ver: INSTRUCCIONES_COPIAR_ARCHIVOS.md
echo.
pause
