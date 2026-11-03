# Copia schemas de emisión del host al sidecar DX (Field List del diseñador).
# Idempotente; el sidecar lee JSON en cada GetDesignerModel (no hace falta reiniciar).
param(
    [string]$TargetDir = $env:PAQSUITE_DX_SCHEMAS_DIR
)

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$sourceDir = Join-Path $repoRoot 'backend\resources\emissions\schemas'

if (-not (Test-Path -LiteralPath $sourceDir)) {
    Write-Host "sync-dx-reporting-schemas: sin carpeta $sourceDir; omitido."
    exit 0
}

$jsonFiles = Get-ChildItem -LiteralPath $sourceDir -Filter '*.json' -File
if ($jsonFiles.Count -eq 0) {
    Write-Host "sync-dx-reporting-schemas: no hay *.json en $sourceDir; omitido."
    exit 0
}

if ([string]::IsNullOrWhiteSpace($TargetDir)) {
    $frameworkSidecar = Join-Path $repoRoot '..\PaqSuite-IA-FRAMEWORK\services\dx-reporting\src\PaqSuite.DxReporting\data\schemas'
    $resolved = Resolve-Path -LiteralPath $frameworkSidecar -ErrorAction SilentlyContinue
    if ($resolved) {
        $TargetDir = $resolved.Path
    } else {
        $TargetDir = $frameworkSidecar
    }
}

if (-not (Test-Path -LiteralPath $TargetDir)) {
    Write-Warning "sync-dx-reporting-schemas: destino inexistente $TargetDir — definí PAQSUITE_DX_SCHEMAS_DIR."
    exit 1
}

New-Item -ItemType Directory -Force -Path $TargetDir | Out-Null
foreach ($file in $jsonFiles) {
    Copy-Item -LiteralPath $file.FullName -Destination (Join-Path $TargetDir $file.Name) -Force
}

Write-Host "sync-dx-reporting-schemas: $($jsonFiles.Count) archivo(s) -> $TargetDir"
