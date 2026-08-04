#!/usr/bin/env bash
# Vercel Install Command — clona deps git privadas por HTTPS + token (no SSH).
# Requiere env GITHUB_TOKEN o GH_TOKEN con lectura a paqsystems/react-core.
set -euo pipefail

TOKEN="${GITHUB_TOKEN:-${GH_TOKEN:-}}"
if [[ -z "${TOKEN}" ]]; then
  echo "vercel-install: falta GITHUB_TOKEN (o GH_TOKEN) con lectura a paqsystems/react-core" >&2
  exit 1
fi

git config --global url."https://x-access-token:${TOKEN}@github.com/".insteadOf "https://github.com/"
git config --global url."https://x-access-token:${TOKEN}@github.com/".insteadOf "ssh://git@github.com/"
git config --global url."https://x-access-token:${TOKEN}@github.com/".insteadOf "git@github.com:"

echo "vercel-install: git HTTPS con token configurado; npm install…"
npm install
