# D-VERIFICACION — CC PQ #17 GEN-37 (2026-11-14)

| Campo | Valor |
|-------|--------|
| Control | CC #17 · `00-ControlCalidad-PQ` · 14/11/2026 |
| TR | [TR-011-update GEN-37](./TR-011-update-cc-pq-17-2026-11-14-gen37.md) · [TR-015-update GEN-37](./TR-015-update-cc-pq-17-2026-11-14-gen37.md) |
| Estado D | Implementado (host + parche mínimo Framework validator) |

## Evidencia código

| Ítem | Evidencia |
|------|-----------|
| Schemas enriquecidos | `backend/database/emission-designer-schemas/partes.informes.consultaDetallada.json` · `paqueteHoras.json` |
| port-names | Sin cambio de `fieldNames` (wire intacto) |
| Manifiesto | `partesatencion-1.3.18-beta.34.json` regenerado (checksums schemas) |
| Planner IA | `DesignSemanticCatalog` + prompt en `DesignLayoutActionPlanner` |
| Framework | `EmissionDesignerSchemaCanonicalValidator` preserva `label`/`description`/`synonyms` + reject ambigüedad |

## Tests (E)

```text
php vendor/bin/phpunit --filter=EmissionDesignerSchemaCanonicalJsonTest  → OK (3)
php vendor/bin/phpunit --filter=DesignLayoutActionPlanner               → OK (7)
# Framework (path-lab junction)
phpunit --filter=EmissionDesignerSchemaCanonicalValidatorTest           → OK (10)
```

## Smoke manual (pendiente operador)

1. `paqsuite:installation:update` (cliente/proyecto local) para materializar DIC.
2. Diseñador Consulta detallada / Paquete de horas: Field List con captions humanas (tras DIC + sidecar que lea API con labels; fallback local sidecar ya tenía labels legacy).
3. IA SC: «sumá las horas» → `duracionMinutos`.

## Observaciones

- `validate-installation-manifest.php` falla si falta `framework-1.3.18-beta.34.json` en laravel-core (preexistente pin vs manifiestos Framework publicados en repo). Host manifesto sí regenerado.
- captionKey reutiliza `partes.informe.field.*` (5 locales ya existentes; no Excel GEN-14).
- SPEC-001-37 Framework sigue Pendiente A1; parche validator es mínimo para no perder semántica en DIC.
