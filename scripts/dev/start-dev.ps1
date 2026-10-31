# Host PaqSuite — desarrollo local (Windows)
# Uso en la raíz del repo:
#   .\scripts\dev\start-dev.ps1              → solo worker de cola
#   .\scripts\dev\start-dev.ps1 -All         → GEN-34 sync + worker + backend + frontend
#   .\scripts\dev\start-dev.ps1 -All -SkipSqlSync  → solo levantar (PARTE O «sin tocar BD»)

param(
    [switch]$All,
    [switch]$SkipSqlSync,
    [int]$BackendPort = 8010,
    [int]$FrontendPort = 3000,
    [string]$TenantCliente = 'DEV'
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'Start-LaravelDevQueueWorker.ps1')

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$backend = Join-Path $repoRoot 'backend'
$frontend = Join-Path $repoRoot 'frontend'
$projectLabel = Split-Path $repoRoot -Leaf
$php = Get-PhpExecutable

function Test-ProcessCommandLineMatch {
    param([string]$Pattern)
    Get-CimInstance Win32_Process -Filter "name='php.exe'" -ErrorAction SilentlyContinue |
        Where-Object { $_.CommandLine -match $Pattern } |
        Select-Object -First 1
}

Start-LaravelDevQueueWorker -BackendPath $backend -ProjectLabel $projectLabel

if (-not $All) {
    Write-Host ''
    Write-Host "Solo cola para $projectLabel. Todos los hosts: .\scripts\dev\start-all-hosts-workers.ps1"
    Write-Host "Stack completo: .\scripts\dev\start-dev.ps1 -All"
    return
}

if (-not $SkipSqlSync) {
    $syncScript = Join-Path $PSScriptRoot 'sync-installation-sql.ps1'
    Write-Host "[$projectLabel] GEN-34 sync antes de levantar stack (-SkipSqlSync para omitir)..."
    & $syncScript
    if ($LASTEXITCODE -eq 2) {
        Write-Host "[$projectLabel] Instalación gateway: se omitió SQL local; levantando stack igual (API vía agente en runtime)."
    }
    elseif ($LASTEXITCODE -ne 0) {
        Write-Host "[$projectLabel] GEN-34 sync falló (exit=$LASTEXITCODE). Corregí el error o usá -SkipSqlSync solo para depurar."
        exit $LASTEXITCODE
    }
}

if (-not (Test-Path $frontend)) {
    Write-Host "[$projectLabel] Sin carpeta frontend; omitiendo Vite."
}
else {
    $viteListening = Get-NetTCPConnection -LocalPort $FrontendPort -State Listen -ErrorAction SilentlyContinue
    if ($null -ne $viteListening) {
        Write-Host "[$projectLabel] Frontend ya en :$FrontendPort."
    }
    else {
        $viteEnv = "set PAQ_REPO_LAB=1&& set VITE_TENANT_DEFAULT_CLIENT=$TenantCliente&& npm run dev"
        Start-Process -FilePath 'cmd.exe' -ArgumentList @('/c', $viteEnv) -WorkingDirectory $frontend -WindowStyle Minimized
        Write-Host "[$projectLabel] Frontend http://localhost:$FrontendPort (tenant $TenantCliente)"
    }
}

$servePattern = "artisan serve.*$BackendPort"
if ($null -ne (Test-ProcessCommandLineMatch -Pattern $servePattern)) {
    Write-Host "[$projectLabel] Backend ya en :$BackendPort."
}
else {
    Start-Process -FilePath $php -ArgumentList @(
        (Join-Path $backend 'artisan'), 'serve', '--host=127.0.0.1', "--port=$BackendPort"
    ) -WorkingDirectory $backend -WindowStyle Minimized
    Write-Host "[$projectLabel] Backend http://127.0.0.1:$BackendPort"
}

Write-Host ''
Write-Host "Entorno ${projectLabel}:"
Write-Host "  Frontend  http://localhost:$FrontendPort"
Write-Host "  Backend   http://127.0.0.1:$BackendPort"
Write-Host "  OpenAPI   http://127.0.0.1:$BackendPort/api/documentation"
if ($All) {
    Write-Host "  Tenant    $TenantCliente (VITE_TENANT_DEFAULT_CLIENT; lab SQL _dev)"
    Write-Host "  Login     http://localhost:$FrontendPort/login?cliente=$TenantCliente"
}
