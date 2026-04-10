# Script de inicialización del proyecto
# Puede ejecutarse desde cualquier ruta; el script se posiciona en backend/

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location (Resolve-Path "$scriptRoot\..\..\..")

$php = 'php'
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    $xamppPhp = 'C:\xampp\php\php.exe'
    if (Test-Path $xamppPhp) {
        $php = $xamppPhp
    }
}

$envPath = Join-Path (Get-Location) '.env'
$envExamplePath = Join-Path (Get-Location) '.env.example'
$createdEnv = $false

if (-not (Test-Path $envPath)) {
    if (Test-Path $envExamplePath) {
        Copy-Item $envExamplePath $envPath
        $createdEnv = $true
        Write-Host "Archivo .env creado desde .env.example." -ForegroundColor Green
    } else {
        Write-Host "No se encontro .env ni .env.example. Crea el .env manualmente." -ForegroundColor Red
        exit 1
    }
}

$needsKey = $false
try {
    $envContent = Get-Content $envPath -ErrorAction Stop
} catch {
    $envContent = @()
}
$appKeyLine = $envContent | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -First 1
if (-not $appKeyLine) {
    $needsKey = $true
} elseif ($appKeyLine -match '^APP_KEY=$' -or $appKeyLine -match '^APP_KEY=""$' -or $appKeyLine -match "^APP_KEY=''$") {
    $needsKey = $true
}

if ($createdEnv -or $needsKey) {
    Write-Host "Generando APP_KEY..." -ForegroundColor Yellow
    & $php artisan key:generate --force

    if ($LASTEXITCODE -ne 0) {
        Write-Host "Error al generar APP_KEY." -ForegroundColor Red
        exit 1
    }
}

Write-Host "================================================" -ForegroundColor Cyan
Write-Host " Inicializando Sistema de Seguimiento" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Ejecutar migraciones
Write-Host "[1/3] Ejecutando migraciones..." -ForegroundColor Yellow
& $php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error al ejecutar migraciones. Verifica tu configuración de base de datos." -ForegroundColor Red
    exit 1
}

# 2. Ejecutar seeders
Write-Host "[2/3] Ejecutando seeders (roles, usuarios, workflows)..." -ForegroundColor Yellow
& $php artisan db:seed --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error al ejecutar seeders." -ForegroundColor Red
    exit 1
}

# 3. Crear enlace simbólico para storage
Write-Host "[3/3] Creando enlace simbólico para storage/public..." -ForegroundColor Yellow
& $php artisan storage:link

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
