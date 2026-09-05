#!/usr/bin/env bash
# Vercel no resuelve Tailscale MagicDNS ni 100.110.69.93.
# Paquetes públicos → npmjs. @paqsuite/react-core → tarball vendored (misma 2.4.4 del lock).
set -euo pipefail

unset NPM_CONFIG_REGISTRY || true
unset npm_config_registry || true

tarball="vendor/paqsuite-react-core-2.4.4.tgz"
if [[ ! -f "${tarball}" ]]; then
  echo "vercel-install: falta ${tarball} (pack local: npm pack @paqsuite/react-core)" >&2
  exit 1
fi

node <<'NODE'
const fs = require('fs')
const pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'))
pkg.dependencies['@paqsuite/react-core'] = 'file:vendor/paqsuite-react-core-2.4.4.tgz'
fs.writeFileSync('package.json', `${JSON.stringify(pkg, null, 2)}\n`)
NODE

cat > .npmrc <<'EOF'
registry=https://registry.npmjs.org/
replace-registry-host=never
EOF

export NPM_CONFIG_REGISTRY="https://registry.npmjs.org/"
echo "vercel-install: npmjs + react-core vendored; npm install…"
npm install
