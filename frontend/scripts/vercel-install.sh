#!/usr/bin/env bash
# Install FE — npmjs + @paqsuite/* desde Cloudsmith (GEN-35).
# Vercel: Environment Variable CLOUDSMITH_READ_TOKEN (y opcional PAQSUITE_NPM_REGISTRY).
set -euo pipefail

if [[ -z "${CLOUDSMITH_READ_TOKEN:-}" ]]; then
  echo "vercel-install: falta CLOUDSMITH_READ_TOKEN en el proyecto Vercel (Settings → Environment Variables)." >&2
  exit 1
fi

registry="${PAQSUITE_NPM_REGISTRY:-https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/}"
registryHost="${registry#https://}"
registryHost="${registryHost#http://}"
registryHost="${registryHost%/}"

reactCoreVersion="${PAQSUITE_REACT_CORE_VERSION:-2.4.24-beta.1}"

echo "vercel-install: npm install (@paqsuite → ${registry})…"
npm config set "@paqsuite:registry" "${registry}"
npm config set "//${registryHost}/:_authToken" "${CLOUDSMITH_READ_TOKEN}"
npm install "@paqsuite/react-core@${reactCoreVersion}" --save-exact
npm install
