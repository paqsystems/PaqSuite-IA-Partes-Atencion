# Snippet estándar Forge (zero-downtime) — insertar después de migrate y antes de $ACTIVATE_RELEASE().
# SoT: docs/_base/00-deploy-github-vercel-route53-forge.md · skill vercel-github-deployment-standard.
if [ -f "$FORGE_RELEASE_DIRECTORY/scripts/forge/post-deploy.sh" ]; then
  bash "$FORGE_RELEASE_DIRECTORY/scripts/forge/post-deploy.sh"
else
  if $FORGE_PHP artisan list --raw 2>/dev/null | grep -q '^paqsuite:installation:update'; then
    $FORGE_PHP artisan paqsuite:installation:update \
      --cliente="${PAQSUITE_CLIENTE}" \
      --proyecto="${PAQSUITE_PROYECTO}" \
      --non-interactive
  else
    echo "paqsuite:installation:update no disponible; omito GEN-34 CLI."
  fi
  $FORGE_PHP scripts/deploy-sp.php || true
fi
