# Script para abrir puerto 8000 con auto-elevacion a administrador
if (-NOT ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "Requiere permisos de administrador. Elevando..." -ForegroundColor Yellow
    Start-Process powershell.exe "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host " CONFIGURANDO FIREWALL PARA PUERTO 8000" -ForegroundColor Yellow
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

try {
    # Eliminar regla existente si existe
    netsh advfirewall firewall delete rule name="Laravel Dev 8000" >$null 2>&1
    
    # Crear nueva regla
    netsh advfirewall firewall add rule name="Laravel Dev 8000" dir=in action=allow protocol=TCP localport=8000 profile=any enable=yes | Out-Null

    $ipv4 = Get-NetIPAddress -AddressFamily IPv4 |
        Where-Object {
            $_.IPAddress -notlike '127.*' -and
            $_.IPAddress -notlike '169.254*' -and
            $_.InterfaceAlias -notmatch 'Loopback|vEthernet|WSL|VirtualBox|Hyper-V'
        } |
        Select-Object -ExpandProperty IPAddress -First 1

    if (-not $ipv4) {
        $ipv4 = 'IP_LOCAL_NO_DETECTADA'
    }
    
    Write-Host "[OK] Regla de firewall creada exitosamente" -ForegroundColor Green
    Write-Host ""
    Write-Host "================================================" -ForegroundColor Cyan
    Write-Host " ACCEDE DESDE OTROS COMPUTADORES USANDO:" -ForegroundColor Yellow
    Write-Host " http://$ipv4`:8000" -ForegroundColor White -BackgroundColor Blue
    Write-Host "================================================" -ForegroundColor Cyan
    Write-Host ""
    
    # Verificar que el servidor está corriendo
    $serverRunning = netstat -ano | findstr ":8000"
    if ($serverRunning) {
        Write-Host "[OK] Servidor Laravel corriendo correctamente" -ForegroundColor Green
    } else {
        Write-Host "[ADVERTENCIA] El servidor no está corriendo" -ForegroundColor Yellow
        Write-Host "Ejecuta en otra terminal: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor White
    }
} catch {
    Write-Host "[ERROR] No se pudo configurar el firewall" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host ""
Write-Host "Presiona cualquier tecla para salir..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
