# Aplica la nueva version de CafeQR sobre TU proyecto Laravel existente.
# Uso (en PowerShell, estando dentro de la carpeta de tu proyecto):
#     powershell -ExecutionPolicy Bypass -File "C:\ruta\a\cafeqr-nuevo\aplicar-cambios.ps1"
# NO toca tu .env (Gmail, APP_KEY), ni vendor, ni tus fotos/QR de cobro.
param([string]$Proyecto = (Get-Location).Path)

$ErrorActionPreference = 'Stop'
$origen = Split-Path -Parent $MyInvocation.MyCommand.Path

if (-not (Test-Path (Join-Path $Proyecto 'artisan'))) { throw "No encuentro 'artisan' en $Proyecto. Ejecuta esto dentro de tu proyecto Laravel." }
if ((Resolve-Path $Proyecto).Path -eq (Resolve-Path $origen).Path) { throw "Ejecuta el script desde tu proyecto viejo, no desde la carpeta nueva." }

Write-Host "1) Copia de seguridad..." -ForegroundColor Cyan
$backup = Join-Path (Split-Path $Proyecto -Parent) ("backup-cafeqr-" + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
foreach ($d in 'app','config','database','resources','routes','public','tests','bootstrap') {
    if (Test-Path (Join-Path $Proyecto $d)) { Copy-Item (Join-Path $Proyecto $d) (Join-Path $backup $d) -Recurse -Force }
}
if (Test-Path (Join-Path $Proyecto '.env')) { Copy-Item (Join-Path $Proyecto '.env') (Join-Path $backup '.env') }
Write-Host "   Backup en: $backup"

Write-Host "2) Reemplazando codigo..." -ForegroundColor Cyan
foreach ($d in 'app','resources','routes','tests') {
    $dest = Join-Path $Proyecto $d
    if (Test-Path $dest) { Remove-Item $dest -Recurse -Force }
    Copy-Item (Join-Path $origen $d) $dest -Recurse -Force
}
# base de datos: migraciones y seeders viejos fuera
foreach ($d in 'migrations','seeders','factories') {
    $dest = Join-Path $Proyecto "database\$d"
    if (Test-Path $dest) { Remove-Item $dest -Recurse -Force }
    Copy-Item (Join-Path $origen "database\$d") $dest -Recurse -Force
}
Copy-Item (Join-Path $origen 'config\*') (Join-Path $Proyecto 'config') -Recurse -Force
Copy-Item (Join-Path $origen 'bootstrap\app.php') (Join-Path $Proyecto 'bootstrap\app.php') -Force
Copy-Item (Join-Path $origen 'bootstrap\providers.php') (Join-Path $Proyecto 'bootstrap\providers.php') -Force
Copy-Item (Join-Path $origen 'phpunit.xml') (Join-Path $Proyecto 'phpunit.xml') -Force
Copy-Item (Join-Path $origen 'public\css') (Join-Path $Proyecto 'public') -Recurse -Force
Copy-Item (Join-Path $origen 'iniciar-cafeqr.bat') $Proyecto -Force
Copy-Item (Join-Path $origen 'permitir-firewall.bat') $Proyecto -Force
Copy-Item (Join-Path $origen '.env.example') (Join-Path $Proyecto '.env.example') -Force
foreach ($d in 'productos','fotos','img') { New-Item -ItemType Directory -Force -Path (Join-Path $Proyecto "public\$d") | Out-Null }
Copy-Item (Join-Path $origen 'public\productos\*') (Join-Path $Proyecto 'public\productos') -Recurse -Force -ErrorAction SilentlyContinue

Write-Host "3) Revisando .env..." -ForegroundColor Cyan
$envPath = Join-Path $Proyecto '.env'
if (-not (Test-Path $envPath)) { Copy-Item (Join-Path $Proyecto '.env.example') $envPath }
$envTxt = Get-Content $envPath -Raw
$nuevas = @{
  'APP_TIMEZONE'='America/La_Paz'; 'APP_LOCALE'='es'; 'APP_FALLBACK_LOCALE'='es';
  'DOMINIO_INSTITUCIONAL'='univalle.edu'; 'PAGO_MODO'='demo'; 'PAGO_MINUTOS'='30';
  'TIEMPO_PREPARACION'='10'; 'ADMIN_NOMBRE'='"Administrador CafeQR"';
  'ADMIN_EMAIL'='bmj2031702@est.univalle.edu'; 'ADMIN_PASSWORD'='Admin12345'
}
foreach ($k in $nuevas.Keys) {
    if ($envTxt -notmatch "(?m)^$k=") { Add-Content $envPath ("`n$k=" + $nuevas[$k]) }
}
# el .env viejo puede usar SESSION_DRIVER=database: sigue funcionando (la tabla 'sessions' esta en las migraciones)

Write-Host "4) Instalando libreria del QR..." -ForegroundColor Cyan
Push-Location $Proyecto
try {
    composer require "chillerlan/php-qrcode:^6.0" --no-interaction
    composer dump-autoload

    Write-Host "5) Preparando base de datos (se reinicia: tablas nuevas + administrador + menu)..." -ForegroundColor Cyan
    if (-not (Test-Path 'database\database.sqlite')) { New-Item 'database\database.sqlite' -ItemType File | Out-Null }
    php artisan optimize:clear
    php artisan migrate:fresh --seed --force

    Write-Host "6) Pruebas automaticas..." -ForegroundColor Cyan
    php artisan test
}
finally { Pop-Location }

Write-Host ""
Write-Host "LISTO. Inicia con:  .\iniciar-cafeqr.bat" -ForegroundColor Green
Write-Host "Administrador: bmj2031702@est.univalle.edu  /  Admin12345  (cambiala en Mi perfil)" -ForegroundColor Green
