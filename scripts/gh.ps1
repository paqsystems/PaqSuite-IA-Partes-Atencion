# GitHub CLI — wrapper para Windows (Cursor / agente sin PATH de máquina).
# Uso: powershell -NoProfile -File scripts/gh.ps1 pr create --base develop --head MI-RAMA ...
param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$GhArgs
)

$Candidates = @(
    Join-Path ${env:ProgramFiles} 'GitHub CLI\gh.exe'
    Join-Path ${env:LocalAppData} 'Programs\gh\bin\gh.exe'
    'gh'
)

foreach ($candidate in $Candidates) {
    if ($candidate -eq 'gh') {
        $cmd = Get-Command gh -ErrorAction SilentlyContinue
        if ($cmd) {
            & gh @GhArgs
            exit $LASTEXITCODE
        }
        continue
    }
    if (Test-Path -LiteralPath $candidate) {
        & $candidate @GhArgs
        exit $LASTEXITCODE
    }
}

Write-Error 'No se encontró gh. Instalar: winget install GitHub.cli o choco install gh; luego reiniciar Cursor.'
exit 1
