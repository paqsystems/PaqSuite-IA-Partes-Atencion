#!/usr/bin/env bash
# Regenera package-lock con @paqsuite/react-core@2.4.16 desde Verdaccio (Tailscale o Funnel).
# Uso (PC con acceso a srv-pq):
#   export VERDACCIO_AUTH_TOKEN='…'
#   bash scripts/refresh-react-core-lock.sh
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ -z "${VERDACCIO_AUTH_TOKEN:-}" ]]; then
  echo "refresh-react-core-lock: define VERDACCIO_AUTH_TOKEN" >&2
  exit 1
fi

echo "refresh-react-core-lock: npm install @paqsuite/react-core@2.4.16 …"
npm install @paqsuite/react-core@2.4.16 --save-exact
npm list @paqsuite/react-core
