#!/usr/bin/env bash
# GEN-34 fase 5 — Forge post-deploy (Q6-A). Ajustar DEPLOY_PATH al site.
set -euo pipefail

scriptDir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_PATH="${DEPLOY_PATH:-$(cd "${scriptDir}/../../backend" && pwd)}"
cd "${DEPLOY_PATH}"

# Forge exporta pocas vars al shell del deploy; cliente/proyecto suelen vivir solo en .env enlazado.
read_dotenv_var() {
  local key="$1"
  local file
  for file in "${DEPLOY_PATH}/.env" "${FORGE_SITE_PATH:-}/.env"; do
    [[ -f "$file" ]] || continue
    local line
    line="$(grep -E "^${key}=" "$file" | tail -n 1 || true)"
    [[ -n "$line" ]] || continue
    local val="${line#*=}"
    val="${val%$'\r'}"
    val="${val#\"}"; val="${val%\"}"
    val="${val#\'}"; val="${val%\'}"
    printf '%s' "$val"
    return 0
  done
  return 1
}

if [[ -z "${PAQSUITE_CLIENTE:-}" ]]; then
  PAQSUITE_CLIENTE="$(read_dotenv_var PAQSUITE_CLIENTE || true)"
fi
if [[ -z "${PAQSUITE_PROYECTO:-}" ]]; then
  PAQSUITE_PROYECTO="$(read_dotenv_var PAQSUITE_PROYECTO || true)"
fi

export PAQSUITE_CLIENTE PAQSUITE_PROYECTO

if [[ -z "${PAQSUITE_CLIENTE:-}" || -z "${PAQSUITE_PROYECTO:-}" ]]; then
  echo "PAQSUITE_CLIENTE y PAQSUITE_PROYECTO deben estar en el .env del sitio Forge o exportados en el deploy." >&2
  exit 1
fi

# Un solo proceso PHP: tenant EMPRESAS_CONEXION + migrate + deploy-sp + installation:update
php scripts/forge/run-post-deploy-backend.php
