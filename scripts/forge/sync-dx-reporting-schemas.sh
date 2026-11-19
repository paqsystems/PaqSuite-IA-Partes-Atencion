#!/usr/bin/env bash
# Copia schemas de emisión del release al sidecar DX en el mismo servidor Forge.
# Idempotente; no reinicia el sidecar (lee JSON en cada GetDesignerModel).
set -euo pipefail

scriptDir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
repoRoot="$(cd "${scriptDir}/../.." && pwd)"
sourceDirs=(
  "${repoRoot}/backend/resources/emissions/schemas"
  "${repoRoot}/backend/scripts/dx-reporting/schemas"
)

defaultTarget="/home/forge/paqsuite-dx-reporting/data/schemas"
targetDir="${PAQSUITE_DX_SCHEMAS_DIR:-${PAQSUITE_DX_SCHEMA_STORAGE_PATH:-$defaultTarget}}"

shopt -s nullglob
jsonFiles=()
for sourceDir in "${sourceDirs[@]}"; do
  if [[ ! -d "$sourceDir" ]]; then
    continue
  fi
  for file in "${sourceDir}"/*.json; do
    jsonFiles+=("$file")
  done
done

if [[ ${#jsonFiles[@]} -eq 0 ]]; then
  echo "sync-dx-reporting-schemas: sin JSON en resources/emissions/schemas ni scripts/dx-reporting/schemas; omitido."
  exit 0
fi

if [[ ! -d "$targetDir" ]]; then
  echo "sync-dx-reporting-schemas: destino inexistente ${targetDir}; omitido (solo aplica en Forge con sidecar)." >&2
  exit 0
fi

mkdir -p "$targetDir"
for file in "${jsonFiles[@]}"; do
  cp -f "$file" "${targetDir}/$(basename "$file")"
done

echo "sync-dx-reporting-schemas: ${#jsonFiles[@]} archivo(s) → ${targetDir}"
