#!/usr/bin/env bash
set -Eeuo pipefail

# Instalación reproducible del backend en Laravel Forge.
# El paquete laravel-core debe provenir de Satis; nunca de un checkout sibling.

backendDirectory="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
satisUrl="http://100.110.69.93/satis"
lockFile="$backendDirectory/composer.lock"

cd "$backendDirectory"

if [[ ! -f "$lockFile" ]]; then
    echo "ERROR: no existe backend/composer.lock; no se ejecuta Composer en Forge." >&2
    exit 1
fi

lockValidationStatus=0
php -r '
$lock = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
foreach (array_merge($lock["packages"] ?? [], $lock["packages-dev"] ?? []) as $package) {
    if (($package["name"] ?? null) !== "paqsuite/laravel-core") {
        continue;
    }

    if (($package["dist"]["type"] ?? null) === "path") {
        fwrite(STDERR, "ERROR: composer.lock contiene laravel-core como path; publicar una release nueva con el lock de Satis.\n");
        exit(2);
    }

    exit(0);
}

fwrite(STDERR, "ERROR: composer.lock no contiene paqsuite/laravel-core.\n");
exit(3);
' "$lockFile" || lockValidationStatus=$?
if (( lockValidationStatus != 0 )); then
    exit "$lockValidationStatus"
fi

if ! curl --fail --silent --show-error --max-time 15 "$satisUrl/packages.json" >/dev/null; then
    cat >&2 <<EOF
ERROR: Forge no puede alcanzar Satis en $satisUrl.
Verificar la ruta Tailscale/VPN del servidor Forge y volver a ejecutar el deploy.
EOF
    exit 4
fi

composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader
