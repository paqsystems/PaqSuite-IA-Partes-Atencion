# Abre PR hacia develop si no existe uno para la rama head.
# Uso: powershell -NoProfile -File scripts/new-pr-to-develop.ps1 -Title "..." [-BodyFile path.md] [-Head rama]
param(
    [Parameter(Mandatory = $true)]
    [string]$Title,
    [string]$BodyFile,
    [string]$Head,
    [string]$Base = 'develop',
    [string]$Repo = 'paqsystems/PaqSuite-IA-Partes-Atencion'
)

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$Gh = Join-Path $ScriptDir 'gh.ps1'

if (-not $Head) {
    $gitArgs = @('-c', "safe.directory=$((Resolve-Path (Join-Path $ScriptDir '..')).Path)", 'branch', '--show-current')
    $Head = & git @gitArgs 2>$null
    if (-not $Head) {
        Write-Error 'No se pudo detectar rama actual (git). Pasá -Head explícito.'
        exit 1
    }
}

$existingJson = & powershell -NoProfile -File $Gh pr list --repo $Repo --head $Head --base $Base --json url --jq '.[0].url' 2>$null
if ($existingJson -and $existingJson.Trim()) {
    Write-Output $existingJson.Trim()
    exit 0
}

$createArgs = @(
    'pr', 'create',
    '--repo', $Repo,
    '--base', $Base,
    '--head', $Head,
    '--title', $Title
)
if ($BodyFile) {
    if (-not (Test-Path -LiteralPath $BodyFile)) {
        Write-Error "BodyFile no existe: $BodyFile"
        exit 1
    }
    $createArgs += @('--body-file', (Resolve-Path $BodyFile).Path)
}

& powershell -NoProfile -File $Gh @createArgs
exit $LASTEXITCODE
