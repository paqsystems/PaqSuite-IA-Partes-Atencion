#!/usr/bin/env bash
# SoT deploy Forge — backend Partes/Tango (zero-downtime). Copiar a Forge site script.
# Orden: composer → optimize/storage → post-deploy (apply tenant DB + migrate + SP + installation:update).

$CREATE_RELEASE()

cd "$FORGE_RELEASE_DIRECTORY/backend"

if [[ -z "${CLOUDSMITH_READ_TOKEN:-}" ]]; then
  export CLOUDSMITH_READ_TOKEN="$($FORGE_PHP -r 'foreach (array_slice($argv, 1) as $f) { if (!is_file($f)) { continue; } if (preg_match("/^CLOUDSMITH_READ_TOKEN=(.*)$/m", file_get_contents($f), $m)) { echo trim($m[1], "\x22\x27 \r\n"); exit(0); } }' "$FORGE_RELEASE_DIRECTORY/.env" "$FORGE_SITE_PATH/.env" ".env")"
fi

if [[ -f scripts/forge-composer-install.sh ]]; then
  bash scripts/forge-composer-install.sh
else
  echo "ERROR: falta scripts/forge-composer-install.sh" >&2
  exit 1
fi

$FORGE_PHP artisan optimize
$FORGE_PHP artisan storage:link

if [[ -f "$FORGE_RELEASE_DIRECTORY/scripts/forge/post-deploy.sh" ]]; then
  bash "$FORGE_RELEASE_DIRECTORY/scripts/forge/post-deploy.sh"
else
  echo "ERROR: falta scripts/forge/post-deploy.sh (apply tenant + migrate + GEN-34)" >&2
  exit 1
fi

$ACTIVATE_RELEASE()

$RESTART_QUEUES()
