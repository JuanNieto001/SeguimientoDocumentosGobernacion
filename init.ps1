# Script de inicialización del proyecto
# Ejecutar desde PowerShell en la raíz del proyecto

Write-Host "================================================" -ForegroundColor Cyan
Write-Host " Inicializando Sistema de Seguimiento" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Ejecutar migraciones
Write-Host "[1/3] Ejecutando migraciones..." -ForegroundColor Yellow
php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error al ejecutar migraciones. Verifica tu configuración de base de datos." -ForegroundColor Red
    exit 1
}

# 2. Ejecutar seeders
Write-Host "[2/3] Ejecutando seeders (roles, usuarios, workflows)..." -ForegroundColor Yellow
php artisan db:seed --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error al ejecutar seeders." -ForegroundColor Red
    exit 1
}

# 3. Crear enlace simbólico para storage
Write-Host "[3/3] Creando enlace simbólico para storage/public..." -ForegroundColor Yellow
php artisan storage:link

if ($LASTEXITCODE -ne 0) {
    Write-Host "Advertencia: El enlace simbólico ya existe o hubo un error." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host " ¡Inicialización completada exitosamente!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Puedes iniciar el servidor con: php artisan serve" -ForegroundColor Cyan
Write-Host ""
