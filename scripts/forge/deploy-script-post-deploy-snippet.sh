# Snippet estándar Forge (zero-downtime) — insertar después de migrate y antes de $ACTIVATE_RELEASE().
# SoT: docs/_base/00-deploy-github-vercel-route53-forge.md · skill vercel-github-deployment-standard.
if [ -f "$FORGE_RELEASE_DIRECTORY/scripts/forge/post-deploy.sh" ]; then
  bash "$FORGE_RELEASE_DIRECTORY/scripts/forge/post-deploy.sh"
else
  $FORGE_PHP artisan paqsuite:installation:update \
    --cliente="${PAQSUITE_CLIENTE}" \
    --proyecto="${PAQSUITE_PROYECTO}" \
    --non-interactive || true
  $FORGE_PHP scripts/deploy-sp.php || true
fi
