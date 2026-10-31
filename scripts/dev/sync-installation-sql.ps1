# GEN-34 — migrate + installation:update + SP host (desarrollo local, PARTE O)
# Uso (raíz del repo):
#   .\scripts\dev\sync-installation-sql.ps1
#   .\scripts\dev\sync-installation-sql.ps1 -SkipHostSp

param(
    [switch]$SkipHostSp
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'Start-LaravelDevQueueWorker.ps1')

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$backend = Join-Path $repoRoot 'backend'
$php = Get-PhpExecutable
$script = Join-Path $backend 'scripts\dev\run-local-gen34-sync.php'

if (-not (Test-Path $script)) {
    throw "No se encontró $script"
}

$args = @($script)
if ($SkipHostSp) {
    $args += '--skip-host-sp'
}

Write-Host "[Partes] GEN-34 sync (PAQSUITE_CLIENTE/PROYECTO + tenant sqlsrv desde backend/.env)..."
& $php @args
# 0 = OK · 1 = error · 2 = gateway (sin SQL local)
exit $LASTEXITCODE
