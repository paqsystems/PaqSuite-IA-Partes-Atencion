#!/usr/bin/env bash
# Regenera package-lock con @paqsuite/react-core desde Cloudsmith.
#   export CLOUDSMITH_READ_TOKEN='…'
#   bash scripts/refresh-react-core-lock.sh
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ -z "${CLOUDSMITH_READ_TOKEN:-}" ]]; then
  echo "refresh-react-core-lock: define CLOUDSMITH_READ_TOKEN" >&2
  exit 1
fi

reactCoreVersion="${PAQSUITE_REACT_CORE_VERSION:-2.4.24-beta.1}"
echo "refresh-react-core-lock: npm install @paqsuite/react-core@${reactCoreVersion} …"
npm install "@paqsuite/react-core@${reactCoreVersion}" --save-exact
npm list @paqsuite/react-core
