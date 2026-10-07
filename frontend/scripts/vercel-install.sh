#!/usr/bin/env bash
# Install FE — npmjs + @paqsuite/* desde Verdaccio (HTTPS Funnel / registry alcanzable por el builder).
# Vercel: Environment Variable VERDACCIO_AUTH_TOKEN (y opcional PAQSUITE_NPM_REGISTRY).
set -euo pipefail

if [[ -z "${VERDACCIO_AUTH_TOKEN:-}" ]]; then
  echo "vercel-install: falta VERDACCIO_AUTH_TOKEN en el proyecto Vercel (Settings → Environment Variables)." >&2
  exit 1
fi

registry="${PAQSUITE_NPM_REGISTRY:-https://srv-pq.tail6726a3.ts.net}"
registryHost="${registry#https://}"
registryHost="${registryHost#http://}"
registryHost="${registryHost%/}"

reactCoreVersion="${PAQSUITE_REACT_CORE_VERSION:-2.4.17}"

echo "vercel-install: npm install (@paqsuite → ${registry})…"
npm config set "@paqsuite:registry" "${registry}"
npm config set "//${registryHost}/:_authToken" "${VERDACCIO_AUTH_TOKEN}"
# Resolver @paqsuite desde registry (evita EINTEGRITY por lock generado con tarball local).
npm install "@paqsuite/react-core@${reactCoreVersion}" --save-exact
npm install
