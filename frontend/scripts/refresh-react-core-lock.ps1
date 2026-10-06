# Regenera package-lock con @paqsuite/react-core@2.4.15 desde Verdaccio.
#   $env:VERDACCIO_AUTH_TOKEN = '…'
#   .\scripts\refresh-react-core-lock.ps1
$ErrorActionPreference = 'Stop'
Set-Location (Join-Path $PSScriptRoot '..')

if (-not $env:VERDACCIO_AUTH_TOKEN) {
  throw 'Define VERDACCIO_AUTH_TOKEN'
}

Write-Host 'refresh-react-core-lock: npm install @paqsuite/react-core@2.4.15 …'
npm install '@paqsuite/react-core@2.4.15' --save-exact
npm list @paqsuite/react-core
