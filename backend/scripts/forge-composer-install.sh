#!/usr/bin/env bash
set -Eeuo pipefail

# Instalación reproducible del backend en Laravel Forge.
# laravel-core desde Cloudsmith (GEN-35); nunca path sibling ni Satis.

backendDirectory="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
lockFile="$backendDirectory/composer.lock"

cd "$backendDirectory"

# Forge Environment vive en .env (enlace del release). No se exporta al bash del deploy.
if [[ -z "${CLOUDSMITH_READ_TOKEN:-}" ]]; then
    for dotenvCandidate in \
        "$backendDirectory/.env" \
        "$backendDirectory/../.env"; do
        if [[ -f "$dotenvCandidate" ]]; then
            CLOUDSMITH_READ_TOKEN="$(
                php -r '
                    if (!is_file($argv[1])) {
                        exit(0);
                    }
                    if (preg_match("/^CLOUDSMITH_READ_TOKEN=(.*)$/m", file_get_contents($argv[1]), $matches)) {
                        echo trim($matches[1], "\"'\'' \r\n");
                    }
                ' "$dotenvCandidate"
            )"
            if [[ -n "${CLOUDSMITH_READ_TOKEN:-}" ]]; then
                export CLOUDSMITH_READ_TOKEN
                break
            fi
        fi
    done
fi

if [[ -z "${CLOUDSMITH_READ_TOKEN:-}" ]]; then
    echo "ERROR: falta CLOUDSMITH_READ_TOKEN (Forge Environment / .env)." >&2
    exit 1
fi

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
        fwrite(STDERR, "ERROR: composer.lock contiene laravel-core como path; publicar lock Cloudsmith.\n");
        exit(2);
    }

    $url = (string) ($package["dist"]["url"] ?? $package["source"]["url"] ?? "");
    if (stripos($url, "cloudsmith.io") === false) {
        fwrite(STDERR, "ERROR: composer.lock no resuelve laravel-core desde Cloudsmith.\n");
        exit(5);
    }

    exit(0);
}

fwrite(STDERR, "ERROR: composer.lock no contiene paqsuite/laravel-core.\n");
exit(3);
' "$lockFile" || lockValidationStatus=$?
if (( lockValidationStatus != 0 )); then
    exit "$lockValidationStatus"
fi

php -r '
$k = getenv("CLOUDSMITH_READ_TOKEN");
file_put_contents("auth.json", json_encode([
    "bearer" => ["composer.cloudsmith.io" => $k],
    "http-basic" => [
        "dl.cloudsmith.io" => ["username" => "token", "password" => $k],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
$j = json_decode(file_get_contents("composer.json"), true, 512, JSON_THROW_ON_ERROR);
$repos = $j["repositories"] ?? [];
if (isset($repos[0]) && is_array($repos[0])) {
    $j["repositories"][0]["options"] = ["http" => ["header" => ["X-API-KEY: ".$k]]];
} elseif (is_array($repos)) {
    foreach ($repos as $key => $repo) {
        $j["repositories"][$key]["options"] = ["http" => ["header" => ["X-API-KEY: ".$k]]];
        break;
    }
}
file_put_contents("composer.json", json_encode($j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n");
'

composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader
