# Partes Flutter nativo — emulador Android (app productiva del repo, no smoke-flutter).
# Uso:
#   .\scripts\dev\run-mobile-emulador-partes.ps1
#   .\scripts\dev\run-mobile-emulador-partes.ps1 -Mode debug
#   .\scripts\dev\run-mobile-emulador-partes.ps1 -Detach

param(
    [ValidateSet('release', 'debug', 'profile')]
    [string]$Mode = 'release',
    [string]$EmulatorId = 'Pixel_6',
    [string]$DeviceId = '',
    [int]$BackendPort = 8010,
    [string]$Tenant = 'DEV',
    [switch]$SkipBackendCheck,
    [switch]$NoEmulatorLaunch,
    [switch]$Detach
)

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$mobileDir = Join-Path $repoRoot 'mobile'
$adb = Join-Path $env:LOCALAPPDATA 'Android\Sdk\platform-tools\adb.exe'
$partesPackage = 'com.paqsystems.partes_atencion_mobile'
$smokePackage = 'com.example.smoke_flutter'
# Capacitor web shell — no usar en este script (confunde con Flutter en el emulador).
$capacitorPackage = 'com.paqsystems.partesatencion'

function Resolve-FlutterDevice {
    param([string]$PreferredId)
    if ($PreferredId) { return $PreferredId }
    $lines = flutter devices 2>&1 | Out-String
    if ($lines -match '(emulator-\d+)') {
        return $Matches[1]
    }
    return $null
}

function Wait-AdbDevice {
    param([int]$TimeoutSec = 120)
    if (-not (Test-Path $adb)) {
        throw "No se encontró adb en $adb"
    }
    $deadline = (Get-Date).AddSeconds($TimeoutSec)
    do {
        $list = & $adb devices 2>&1 | Out-String
        if ($list -match 'emulator-\d+\s+device') { return $Matches[0].Split()[0] }
        Start-Sleep -Seconds 3
    } while ((Get-Date) -lt $deadline)
    throw 'Timeout esperando emulador (adb devices).'
}

if (-not (Test-Path (Join-Path $mobileDir 'pubspec.yaml'))) {
    throw "No está mobile/ en $repoRoot"
}

if (-not $NoEmulatorLaunch) {
    $current = Resolve-FlutterDevice -PreferredId $DeviceId
    if (-not $current) {
        Write-Host "Iniciando emulador $EmulatorId..."
        flutter emulators --launch $EmulatorId | Out-Null
        $DeviceId = Wait-AdbDevice
    } else {
        $DeviceId = $current
    }
} elseif (-not $DeviceId) {
    $DeviceId = Resolve-FlutterDevice -PreferredId ''
    if (-not $DeviceId) { throw 'No hay emulador conectado. Quitá -NoEmulatorLaunch o lanzá Pixel_6.' }
}

Write-Host "Dispositivo: $DeviceId"

if (Test-Path $adb) {
    foreach ($pkg in @($smokePackage, $capacitorPackage)) {
        & $adb -s $DeviceId uninstall $pkg 2>$null | Out-Null
        Write-Host "Desinstalado si existía: $pkg"
    }
}

if (-not $SkipBackendCheck) {
    $healthUrl = "http://127.0.0.1:$BackendPort/api/v1/health"
    try {
        $r = Invoke-WebRequest -Uri $healthUrl -Headers @{ 'X-Paq-Cliente' = $Tenant } -UseBasicParsing -TimeoutSec 5
        Write-Host "Backend OK ($healthUrl) HTTP $($r.StatusCode)."
    } catch {
        Write-Host "AVISO: backend no responde en $healthUrl — levantá: .\scripts\dev\start-dev.ps1 -All -SkipSqlSync" -ForegroundColor Yellow
    }
}

Write-Host @"

App: Partes-Atención ($partesPackage)
Modo: $Mode
En el emulador configurá URL: http://10.0.2.2:$BackendPort
Tenant login: $Tenant

"@

Push-Location $mobileDir
try {
    flutter pub get | Out-Null
    $flutterArgs = @('run', '-d', $DeviceId)
    switch ($Mode) {
        'release' { $flutterArgs += '--release' }
        'profile' { $flutterArgs += '--profile' }
    }
    if ($Detach) { $flutterArgs += '--no-resident' }

    & flutter @flutterArgs
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    if (Test-Path $adb) {
        & $adb -s $DeviceId shell am start -n "$partesPackage/.MainActivity" | Out-Null
    }
} finally {
    Pop-Location
}