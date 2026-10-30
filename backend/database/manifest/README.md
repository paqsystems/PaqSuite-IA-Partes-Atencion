# Manifiesto host Partes (GEN-34)

- Archivo: `partesatencion-{semver}.json` (semver alineado al pin `paqsuite/laravel-core`).
- Regenerar: `php tools/build-installation-manifest.php --semver=…` (incluye `database/sp/*.sql` y `scripts/sql/partes/*.sql`, excluye `*-REFERENCE*`)
- Validar merge: `php tools/validate-installation-manifest.php --semver=…` (requiere core ≥ 1.3.17-beta.1 con GEN-34).

SoT release: [`docs/06-operacion/RELEASE-PARTES.md`](../../../docs/06-operacion/RELEASE-PARTES.md).
