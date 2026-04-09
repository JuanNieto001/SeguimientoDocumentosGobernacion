$ErrorActionPreference = 'Stop'
Set-Location (Resolve-Path "$PSScriptRoot\..\..")

function Get-PhpCommand {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCmd) { return 'php' }

    $xamppPhp = 'C:\xampp\php\php.exe'
    if (Test-Path $xamppPhp) { return $xamppPhp }

    throw 'No se encontro PHP. Instala PHP o XAMPP antes de ejecutar este script.'
}

$php = Get-PhpCommand

Write-Host '[1/10] Instalando dependencias PHP (produccion)...' -ForegroundColor Cyan
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

Write-Host '[2/10] Instalando dependencias Node...' -ForegroundColor Cyan
if (Test-Path '..\frontend\package-lock.json') {
    npm.cmd --prefix ..\frontend ci --no-audit --no-fund
} else {
    npm.cmd --prefix ..\frontend install --no-audit --no-fund
}

Write-Host '[3/10] Compilando assets frontend...' -ForegroundColor Cyan
npm.cmd --prefix ..\frontend run build

Write-Host '[4/10] Preparando .env...' -ForegroundColor Cyan
if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
    Write-Host '  - .env creado desde .env.example' -ForegroundColor Yellow
}

Write-Host '[5/10] Generando APP_KEY si hace falta...' -ForegroundColor Cyan
$envContent = Get-Content '.env' -Raw
if ($envContent -notmatch 'APP_KEY=base64:') {
    & $php artisan key:generate --force
}

Write-Host '[6/10] Ejecutando migraciones...' -ForegroundColor Cyan
& $php artisan migrate --force

Write-Host '[7/10] Sincronizando roles y permisos...' -ForegroundColor Cyan
& $php artisan db:seed --class=RolesAndPermissionsSeeder --force

Write-Host '[8/10] Verificando link de storage...' -ForegroundColor Cyan
try {
    & $php artisan storage:link
} catch {
    Write-Host '  - storage:link omitido (ya existe o no aplica)' -ForegroundColor Yellow
}

Write-Host '[9/10] Limpiando y optimizando cache segura...' -ForegroundColor Cyan
& $php artisan config:clear
& $php artisan cache:clear
& $php artisan view:clear
& $php artisan config:cache
& $php artisan view:cache

Write-Host '[10/10] Intentando cache de rutas (opcional)...' -ForegroundColor Cyan
try {
    & $php artisan route:list *> $null
    & $php artisan route:cache
    Write-Host '  - route:cache aplicado' -ForegroundColor Green
} catch {
    Write-Host '  - ADVERTENCIA: route:list fallo, se omite route:cache para no bloquear deploy' -ForegroundColor Yellow
}

Write-Host 'Deploy completado.' -ForegroundColor Green
