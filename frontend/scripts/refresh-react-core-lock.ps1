# Regenera package-lock con @paqsuite/react-core@2.4.17 desde Verdaccio.
#   $env:VERDACCIO_AUTH_TOKEN = '<secret de Vercel Settings → Environment Variables>'
#   .\scripts\refresh-react-core-lock.ps1
$ErrorActionPreference = 'Stop'
Set-Location (Join-Path $PSScriptRoot '..')

if (-not $env:VERDACCIO_AUTH_TOKEN) {
  throw 'Define VERDACCIO_AUTH_TOKEN (mismo valor que en Vercel; no uses el texto de ejemplo de la doc).'
}

$token = $env:VERDACCIO_AUTH_TOKEN.Trim()
if ($token -match '^(?i)bearer\s+') {
  $token = ($token -replace '^(?i)bearer\s+', '').Trim()
}
if ($token -match '^[.…]+$' -or $token.Length -lt 12) {
  throw 'VERDACCIO_AUTH_TOKEN inválido o placeholder (…). Copiá el secret real desde Vercel o el runbook de credenciales SDK.'
}
$env:VERDACCIO_AUTH_TOKEN = $token

$registry = if ($env:PAQSUITE_NPM_REGISTRY) { $env:PAQSUITE_NPM_REGISTRY } else { 'https://srv-pq.tail6726a3.ts.net' }
$registryHost = $registry -replace '^https?://', '' -replace '/$', ''

Write-Host "refresh-react-core-lock: registry $registry"
npm config set '@paqsuite:registry' $registry
npm config set "//${registryHost}/:_authToken" $token

$reactCoreVersion = if ($env:PAQSUITE_REACT_CORE_VERSION) { $env:PAQSUITE_REACT_CORE_VERSION } else { '2.4.17' }
Write-Host "refresh-react-core-lock: npm install @paqsuite/react-core@${reactCoreVersion} …"
npm install "@paqsuite/react-core@${reactCoreVersion}" --save-exact
npm list @paqsuite/react-core
