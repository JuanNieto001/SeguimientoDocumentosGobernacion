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
$manifestPath = Join-Path (Get-Location) 'public\build\manifest.json'
$hotFilePath = Join-Path (Get-Location) 'public\hot'
$frontendPath = Join-Path (Get-Location) '..\frontend'

# 1. Ejecutar migraciones
Write-Host "[1/5] Ejecutando migraciones..." -ForegroundColor Yellow
& $php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error al ejecutar migraciones. Verifica tu configuración de base de datos." -ForegroundColor Red
    exit 1
}

# 2. Ejecutar seeders
Write-Host "[2/5] Ejecutando seeders (roles, usuarios, workflows)..." -ForegroundColor Yellow
& $php artisan db:seed --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error al ejecutar seeders." -ForegroundColor Red
    exit 1
}

# 3. Crear enlace simbólico para storage
Write-Host "[3/5] Creando enlace simbólico para storage/public..." -ForegroundColor Yellow
& $php artisan storage:link

if ($LASTEXITCODE -ne 0) {
    Write-Host "Advertencia: El enlace simbólico ya existe o hubo un error." -ForegroundColor Yellow
}
# 4. Compilar assets frontend si no existen
Write-Host "[4/5] Verificando assets frontend (Vite manifest/hot)..." -ForegroundColor Yellow
if ((Test-Path $manifestPath) -or (Test-Path $hotFilePath)) {
    Write-Host "Assets detectados. No se requiere compilacion adicional." -ForegroundColor Green
} else {
    if (-not (Test-Path $frontendPath)) {
        Write-Host "Advertencia: no se encontro la carpeta frontend. Continua en modo seguro sin assets compilados." -ForegroundColor Yellow
    } else {
        $npm = $null
        if (Get-Command npm.cmd -ErrorAction SilentlyContinue) {
            $npm = 'npm.cmd'
        } elseif (Get-Command npm -ErrorAction SilentlyContinue) {
            $npm = 'npm'
        }

        if (-not $npm) {
            Write-Host "Advertencia: npm no esta disponible. Continua en modo seguro sin assets compilados." -ForegroundColor Yellow
        } else {
            Write-Host "Compilando frontend con vite build..." -ForegroundColor Cyan
            & $npm --prefix ..\frontend run build

            if ($LASTEXITCODE -ne 0) {
                Write-Host "Advertencia: fallo la compilacion del frontend. Revisa Node/NPM para evitar modo degradado." -ForegroundColor Yellow
            } else {
                Write-Host "Assets frontend compilados correctamente." -ForegroundColor Green
            }
        }
    }
}

# 5. Limpiar caches para evitar residuos en el arranque
Write-Host "[5/5] Limpiando cache de Laravel..." -ForegroundColor Yellow
& $php artisan optimize:clear
if ($LASTEXITCODE -ne 0) {
    Write-Host "Advertencia: no se pudo limpiar cache completamente." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host " ¡Inicialización completada exitosamente!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Puedes iniciar el servidor con: .\scripts\local\setup\iniciar_servidor.bat" -ForegroundColor Cyan
Write-Host ""
