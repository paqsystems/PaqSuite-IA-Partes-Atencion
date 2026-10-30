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
$composerJson = json_decode(file_get_contents($argv[2]), true, 512, JSON_THROW_ON_ERROR);
foreach ($composerJson["repositories"] ?? [] as $repo) {
    if (!is_array($repo)) {
        continue;
    }
    if (($repo["type"] ?? "") === "path") {
        fwrite(STDERR, "ERROR: composer.json contiene un repo path; no se instala en Forge.\n");
        exit(4);
    }
    $repoUrl = (string) ($repo["url"] ?? "");
    if (stripos($repoUrl, "satis") !== false || stripos($repoUrl, "100.110.69.93") !== false) {
        fwrite(STDERR, "ERROR: composer.json apunta a Satis; usar Cloudsmith.\n");
        exit(6);
    }
}

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
    if (stripos($url, "satis") !== false || stripos($url, "100.110.69.93") !== false) {
        fwrite(STDERR, "ERROR: composer.lock resuelve laravel-core desde Satis.\n");
        exit(6);
    }

    exit(0);
}

fwrite(STDERR, "ERROR: composer.lock no contiene paqsuite/laravel-core.\n");
exit(3);
' "$lockFile" "$backendDirectory/composer.json" || lockValidationStatus=$?
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
$rewritten = false;
foreach ($repos as $key => $repo) {
    if (!is_array($repo)) {
        continue;
    }
    $repoUrl = (string) ($repo["url"] ?? "");
    $isCloudsmith = stripos($repoUrl, "composer.cloudsmith.io") !== false
        || stripos($repoUrl, "dl.cloudsmith.io") !== false;
    if (!$isCloudsmith) {
        continue;
    }
    $j["repositories"][$key] = [
        "type" => "composer",
        "url" => "https://dl.cloudsmith.io/".$k."/paqsystems/paqsuite-sdk/composer/",
    ];
    $rewritten = true;
    break;
}
if (!$rewritten) {
    fwrite(STDERR, "ERROR: composer.json no tiene repo Cloudsmith para inyectar el token.\n");
    exit(7);
}
file_put_contents("composer.json", json_encode($j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n");

$lockPath = "composer.lock";
$lock = json_decode(file_get_contents($lockPath), true, 512, JSON_THROW_ON_ERROR);
$basicPrefix = "https://dl.cloudsmith.io/basic/";
$entitlementPrefix = "https://dl.cloudsmith.io/".$k."/";
foreach (["packages", "packages-dev"] as $section) {
    if (! isset($lock[$section]) || ! is_array($lock[$section])) {
        continue;
    }
    foreach ($lock[$section] as $index => $package) {
        if (! is_array($package)) {
            continue;
        }
        $distUrl = (string) ($package["dist"]["url"] ?? "");
        if ($distUrl !== "" && str_starts_with($distUrl, $basicPrefix)) {
            $lock[$section][$index]["dist"]["url"] = $entitlementPrefix.substr($distUrl, strlen($basicPrefix));
        }
    }
}
file_put_contents($lockPath, json_encode($lock, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n");
'

composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader
