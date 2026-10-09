# Regenera package-lock con @paqsuite/react-core desde Cloudsmith.
#   $env:CLOUDSMITH_READ_TOKEN = '<secret>'
#   .\scripts\refresh-react-core-lock.ps1
$ErrorActionPreference = 'Stop'
Set-Location (Join-Path $PSScriptRoot '..')

if (-not $env:CLOUDSMITH_READ_TOKEN) {
  throw 'Define CLOUDSMITH_READ_TOKEN (mismo valor que en Vercel/Forge; no uses placeholder).'
}

$token = $env:CLOUDSMITH_READ_TOKEN.Trim()
if ($token -match '^(?i)bearer\s+') {
  $token = ($token -replace '^(?i)bearer\s+', '').Trim()
}
if ($token -match '^[.…]+$' -or $token.Length -lt 12) {
  throw 'CLOUDSMITH_READ_TOKEN inválido o placeholder.'
}
$env:CLOUDSMITH_READ_TOKEN = $token

$registry = if ($env:PAQSUITE_NPM_REGISTRY) { $env:PAQSUITE_NPM_REGISTRY } else { 'https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/' }
$registryHost = $registry -replace '^https?://', '' -replace '/$', ''

Write-Host "refresh-react-core-lock: registry $registry"
npm config set '@paqsuite:registry' $registry
npm config set "//${registryHost}/:_authToken" $token

$reactCoreVersion = if ($env:PAQSUITE_REACT_CORE_VERSION) { $env:PAQSUITE_REACT_CORE_VERSION } else { '2.4.24-beta.1' }
Write-Host "refresh-react-core-lock: npm install @paqsuite/react-core@${reactCoreVersion} …"
npm install "@paqsuite/react-core@${reactCoreVersion}" --save-exact
npm list @paqsuite/react-core
