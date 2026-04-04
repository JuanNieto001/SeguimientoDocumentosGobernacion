@echo off
chcp 65001 > nul
cls
echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║     DASHBOARD BUILDER - PASO 1: PREPARACIÓN                ║
echo ╚════════════════════════════════════════════════════════════╝
echo.

cd /d "%~dp0"

echo [PASO 1/4] Instalando librerías npm...
echo ────────────────────────────────────────────────────────────
call npm install recharts @tanstack/react-query zustand @dnd-kit/core @dnd-kit/utilities react-grid-layout nanoid --save
if errorlevel 1 (
    echo.
    echo ❌ ERROR: No se pudieron instalar las librerías npm
    echo Por favor, verifica que npm está instalado correctamente
    pause
    exit /b 1
)
echo ✅ Librerías npm instaladas correctamente
echo.

echo [PASO 2/4] Ejecutando migración: scope_level...
echo ────────────────────────────────────────────────────────────
php artisan migrate --path=database/migrations/2026_04_04_150001_add_scope_level_to_roles_table.php --force
if errorlevel 1 (
    echo.
    echo ❌ ERROR: No se pudo ejecutar la migración
    echo Verifica la conexión a la base de datos
    pause
    exit /b 1
)
echo ✅ Migración ejecutada correctamente
echo.

echo [PASO 3/4] Ejecutando seeder: RoleScopeLevelSeeder...
echo ────────────────────────────────────────────────────────────
php artisan db:seed --class=RoleScopeLevelSeeder --force
if errorlevel 1 (
    echo.
    echo ❌ ERROR: No se pudo ejecutar RoleScopeLevelSeeder
    pause
    exit /b 1
)
echo ✅ RoleScopeLevelSeeder ejecutado correctamente
echo.

echo Ejecutando seeder: DashboardBuilderPermissionSeeder...
echo ────────────────────────────────────────────────────────────
php artisan db:seed --class=DashboardBuilderPermissionSeeder --force
if errorlevel 1 (
    echo.
    echo ❌ ERROR: No se pudo ejecutar DashboardBuilderPermissionSeeder
    pause
    exit /b 1
)
echo ✅ DashboardBuilderPermissionSeeder ejecutado correctamente
echo.

echo [PASO 4/4] Limpiando caché de Laravel...
echo ────────────────────────────────────────────────────────────
php artisan config:clear
php artisan cache:clear
php artisan permission:cache-reset
echo ✅ Caché limpiado correctamente
echo.

echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║         ✅ PASO 1 COMPLETADO EXITOSAMENTE                  ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo Siguiente paso: Continuar con PASO 2 (Backend Laravel)
echo.
pause
