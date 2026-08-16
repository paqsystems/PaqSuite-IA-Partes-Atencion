#!/usr/bin/env bash
# Install FE — @paqsuite/* desde Verdaccio (srv-pq).
# Requiere red a http://100.110.69.93:4873 (Tailscale / builder con acceso).
# El contrato del producto es registry, no git+https.
set -euo pipefail

echo "paqsuite-install: npm install (Verdaccio @paqsuite)…"
npm install
