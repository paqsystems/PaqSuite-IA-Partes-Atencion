#!/usr/bin/env bash
# Asegura que Composer encuentre paqsuite/laravel-core vía path relativo
# (backend/composer.json → ../../PaqSuite-IA-FRAMEWORK/packages/php/laravel-core).
#
# Uso en Laravel Forge — pegar ANTES de `composer install` (desde el root del release):
#   bash backend/scripts/forge-ensure-framework.sh
#
# Requiere: deploy key / acceso SSH del server al repo PaqSuite-IA-FRAMEWORK.
set -euo pipefail

FRAMEWORK_REPO="${PAQSUITE_FRAMEWORK_REPO:-git@github.com:paqsystems/PaqSuite-IA-FRAMEWORK.git}"
FRAMEWORK_REF="${PAQSUITE_FRAMEWORK_REF:-main}"
FRAMEWORK_HOME="${PAQSUITE_FRAMEWORK_HOME:-${HOME}/PaqSuite-IA-FRAMEWORK}"

# Este script vive en backend/scripts/ → root del release = ../..
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RELEASE_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
# Desde backend/, ../../PaqSuite-IA-FRAMEWORK = <releases>/PaqSuite-IA-FRAMEWORK
RELEASES_DIR="$(cd "${RELEASE_ROOT}/.." && pwd)"
FRAMEWORK_LINK="${RELEASES_DIR}/PaqSuite-IA-FRAMEWORK"

echo "forge-ensure-framework: home=${FRAMEWORK_HOME} link=${FRAMEWORK_LINK} ref=${FRAMEWORK_REF}"

if [[ ! -d "${FRAMEWORK_HOME}/.git" ]]; then
  git clone "${FRAMEWORK_REPO}" "${FRAMEWORK_HOME}"
fi

git -C "${FRAMEWORK_HOME}" fetch origin
git -C "${FRAMEWORK_HOME}" checkout "${FRAMEWORK_REF}"
git -C "${FRAMEWORK_HOME}" reset --hard "origin/${FRAMEWORK_REF}"

if [[ ! -f "${FRAMEWORK_HOME}/packages/php/laravel-core/composer.json" ]]; then
  echo "forge-ensure-framework: ERROR — no existe packages/php/laravel-core en el Framework" >&2
  exit 1
fi

ln -sfn "${FRAMEWORK_HOME}" "${FRAMEWORK_LINK}"
echo "forge-ensure-framework: OK → ${FRAMEWORK_LINK}/packages/php/laravel-core"
