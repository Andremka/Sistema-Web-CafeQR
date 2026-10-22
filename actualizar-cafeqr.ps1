# ACTUALIZA tu CafeQR sin borrar nada tuyo (usuarios, pedidos, .env, fotos y QR se conservan).
# Uso: clic derecho en este archivo > "Ejecutar con PowerShell"  (o desde PowerShell: .\actualizar-cafeqr.ps1)
param([string]$Proyecto = "$env:USERPROFILE\Desktop\MisProyectos\mi-proyecto")
$ErrorActionPreference = 'Stop'
$origen = Split-Path -Parent $MyInvocation.MyCommand.Path

try {
    if (-not (Test-Path (Join-Path $Proyecto 'artisan'))) { throw "No encuentro tu proyecto en: $Proyecto`nEscribe la ruta correcta: .\actualizar-cafeqr.ps1 -Proyecto 'C:\ruta\mi-proyecto'" }
    if ((Resolve-Path $Proyecto).Path -eq (Resolve-Path $origen).Path) { throw "Ejecuta este archivo desde la carpeta descomprimida, no desde mi-proyecto." }

    Write-Host "1) Copia de seguridad (incluye base de datos y .env)..." -ForegroundColor Cyan
    $backup = Join-Path (Split-Path $Proyecto -Parent) ("respaldo-cafeqr-" + (Get-Date -Format 'yyyyMMdd-HHmmss'))
    New-Item -ItemType Directory -Path $backup | Out-Null
    Get-ChildItem $Proyecto -Force | Where-Object { $_.Name -notin 'vendor','node_modules' } | ForEach-Object { Copy-Item $_.FullName $backup -Recurse -Force }
    Write-Host "   Respaldo en: $backup"

    Write-Host "2) Copiando archivos nuevos..." -ForegroundColor Cyan
    foreach ($d in 'app','resources','routes','tests','config') {
        $dest = Join-Path $Proyecto $d
        if (Test-Path $dest) { Remove-Item $dest -Recurse -Force }
        Copy-Item (Join-Path $origen $d) $dest -Recurse -Force
    }
    foreach ($d in 'migrations','seeders','factories') {
        $dest = Join-Path $Proyecto "database\$d"
        if (Test-Path $dest) { Remove-Item $dest -Recurse -Force }
        Copy-Item (Join-Path $origen "database\$d") $dest -Recurse -Force
    }
    Copy-Item (Join-Path $origen 'public\css') (Join-Path $Proyecto 'public') -Recurse -Force
    Copy-Item (Join-Path $origen 'public\index.php') (Join-Path $Proyecto 'public\index.php') -Force
    foreach ($d in 'productos','fotos','img') { New-Item -ItemType Directory -Force -Path (Join-Path $Proyecto "public\$d") | Out-Null }
    foreach ($f in 'bootstrap\app.php','bootstrap\providers.php','phpunit.xml','iniciar-cafeqr.bat','permitir-firewall.bat','.env.example','composer.json','LEEME.txt','ACTUALIZAR.txt','.gitignore') {
        Copy-Item (Join-Path $origen $f) (Join-Path $Proyecto $f) -Force
    }

    Write-Host "3) Actualizando el sistema (sin borrar datos)..." -ForegroundColor Cyan
    Push-Location $Proyecto
    try {
        if (-not (Test-Path 'vendor\chillerlan')) { composer require "chillerlan/php-qrcode:^6.0" --no-interaction }
        composer dump-autoload
        php artisan optimize:clear
        php artisan migrate --force
        php artisan db:seed --class=ProductoSeeder --force
        php artisan db:seed --class=CuponSeeder --force
    }
    finally { Pop-Location }

    Write-Host ""
    Write-Host "Administrador: admi7@est.univalle.edu  /  Admi1234@" -ForegroundColor Green
    Write-Host "LISTO. Inicia con:  .\iniciar-cafeqr.bat   y refresca el navegador con Ctrl+F5" -ForegroundColor Green
    Write-Host "Si algo sale mal, tu respaldo esta en: $backup" -ForegroundColor Green
}
catch {
    Write-Host ""
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "No se borro nada de tus datos. Toma una captura de este mensaje y mandamela." -ForegroundColor Yellow
}
Read-Host "Presiona Enter para cerrar"
