#!/usr/bin/env bash
# LEGADO — no usar en Deploy Script.
# El host consume paqsuite/laravel-core vía Satis (composer), no path al monorepo Framework.
# Ver: docs/01-arquitectura/deploy-sdk-package-repos.md
echo "forge-ensure-framework.sh está deprecado. Usá composer install con Satis (paqsuite/laravel-core)." >&2
exit 1
