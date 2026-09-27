#!/usr/bin/env bash
set -Eeuo pipefail

# Entry point para Forge cuando el Deploy Script se ejecuta desde la raíz
# del release. La implementación vive junto al backend.
repositoryDirectory="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"

exec bash "$repositoryDirectory/backend/scripts/forge-composer-install.sh" "$@"
