#!/usr/bin/env bash
# GEN-34 fase 5 — Forge post-deploy (Q6-A). Ajustar DEPLOY_PATH al site.
set -euo pipefail

scriptDir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_PATH="${DEPLOY_PATH:-$(cd "${scriptDir}/../../backend" && pwd)}"
cd "${DEPLOY_PATH}"

if [[ -z "${PAQSUITE_CLIENTE:-}" || -z "${PAQSUITE_PROYECTO:-}" ]]; then
  echo "PAQSUITE_CLIENTE y PAQSUITE_PROYECTO deben estar definidos en el Environment Forge." >&2
  exit 1
fi

php artisan migrate --force

php artisan paqsuite:installation:update \
  --cliente="${PAQSUITE_CLIENTE}" \
  --proyecto="${PAQSUITE_PROYECTO}" \
  --non-interactive

# GEN-13: SP host (override SDK) sobre la BD del tenant resuelto en runtime.
php scripts/deploy-sp.php
