#!/usr/bin/env bash
# Install FE — npmjs + @paqsuite/* desde Verdaccio (HTTPS Funnel / registry alcanzable por el builder).
# Vercel: Environment Variable VERDACCIO_AUTH_TOKEN (y opcional PAQSUITE_NPM_REGISTRY).
set -euo pipefail

if [[ -z "${VERDACCIO_AUTH_TOKEN:-}" ]]; then
  echo "vercel-install: falta VERDACCIO_AUTH_TOKEN en el proyecto Vercel (Settings → Environment Variables)." >&2
  exit 1
fi

registry="${PAQSUITE_NPM_REGISTRY:-https://srv-pq.tail6726a3.ts.net}"
echo "vercel-install: npm install (@paqsuite → ${registry})…"
npm install
