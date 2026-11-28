# D-VERIFICACION — CC PQ #17 GEN-37 (2026-11-14)

| Campo | Valor |
|-------|--------|
| Control | CC #17 · `00-ControlCalidad-PQ` · 14/11/2026 |
| TR | [TR-011 §20](../../100-SistemaPartes/TR-011-reportes-emisiones.md) · [TR-015-update-b](../../100-SistemaPartes/TR-015-update-b-smart-capture-diseno-informes.md) (CC #17) |
| Estado D | Implementado (host + parche mínimo Framework validator) |

## Evidencia código

| Ítem | Evidencia |
|------|-----------|
| Schemas enriquecidos | `backend/database/emission-designer-schemas/partes.informes.consultaDetallada.json` · `paqueteHoras.json` |
| port-names | Sin cambio de `fieldNames` (wire intacto) |
| Manifiesto | `partesatencion-1.3.18-beta.34.json` regenerado (checksums schemas) |
| Planner IA | `DesignSemanticCatalog` + prompt en `DesignLayoutActionPlanner` |
| Sidecar lab (sin fork) | `EmissionDesignerSchemaDxSidecarExport` + `scripts/dev/export-emission-designer-schemas-for-dx-sidecar.php` |
| Framework | `EmissionDesignerSchemaCanonicalValidator` preserva `label`/`description`/`synonyms` + reject ambigüedad |

## Tests (E)

```text
php vendor/bin/phpunit --filter=EmissionDesignerSchemaCanonicalJsonTest  → OK (4, incl. anti-drift sidecar)
php vendor/bin/phpunit --filter=DesignLayoutActionPlanner               → OK (7)
# Framework (path-lab junction)
phpunit --filter=EmissionDesignerSchemaCanonicalValidatorTest           → OK (10)
```

## Deploy Forge (preview + productivo)

| Sitio | Rama | `post-deploy.sh` → `installation:update` |
|-------|------|------------------------------------------|
| `partesatencion-paqsystems-dev.on-forge.com` | `develop` | Sí (`run-post-deploy-backend.php` tras migrate) |
| `partesatencion-paqsystems.on-forge.com` | `main` | Idem (mismo script de deploy) |

Vercel preview/prod **no** ejecuta `installation:update` (solo FE); la materialización GEN-37 en DIC es responsabilidad del backend Forge en cada deploy.

Post-deploy (2026-11-16): export GEN-37 → sidecar + sync `resources/emissions/schemas` y `scripts/dx-reporting/schemas`.

## Smoke manual

1. Tras deploy Forge, confirmar en log `>>> php artisan paqsuite:installation:update` exit 0 por tenant; o local: `paqsuite:installation:update` para materializar DIC.
2. Diseñador Consulta detallada / Paquete de horas: Field List con captions humanas (tras DIC + sidecar que lea API con labels; fallback local sidecar ya tenía labels legacy).
3. IA SC: «sumá las horas» → `duracionMinutos`.

## F1 — Verificación del agente (18/11/2026)

| CA | Resultado | Notas |
|----|-----------|-------|
| CA-37-01 | OK | `EmissionDesignerSchemaCanonicalJsonTest` — `label` en cada field |
| CA-37-02 | OK | `DesignSemanticCatalog` + planner tests |
| CA-37-03 | OK | `captionKey` → `partes.informe.field.*` |
| CA-37-04 | OK | port-names / wire sin drift |
| CA-37-05 | OK | Manifiesto regenerado (beta.34+) |
| CA-37-06 | OK (código) | Prompt vocabulary desde JSON; smoke operador diferido a preview |
| CA-37-07 | OK | Sidecar export desde canónico; sin hardcode C# Partes |

**Veredicto F1:** Aprobado con observaciones (smoke UI opcional post-deploy; validator Framework en path-lab).

## F — Cierre (18/11/2026)

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Aprobado** — usuario autoriza **Finalizado** + Parte I |
| **Parte I** | 18/11/2026 — SPEC-011 / HU-011 / TR-011 · SPEC oleada 2 B + HU-015-update / TR-015-update-b (GEN-37); updates CC #17 eliminados |

## Observaciones

- `validate-installation-manifest.php` falla si falta `framework-1.3.18-beta.34.json` en laravel-core (preexistente pin vs manifiestos Framework publicados en repo). Host manifesto sí regenerado.
- captionKey reutiliza `partes.informe.field.*` (5 locales ya existentes; no Excel GEN-14).
- SPEC-001-37 Framework sigue Pendiente A1; parche validator es mínimo para no perder semántica en DIC.
