# Revisión de ambigüedad - SPEC-012 — Readaptar consumo SDK a Cloudsmith

| Campo | Valor |
|-------|--------|
| **SPEC** | [`SPEC-012-adopcion-gen-35-cloudsmith.md`](./SPEC-012-adopcion-gen-35-cloudsmith.md) |
| **Paso** | A1 (re-evaluación tras ajuste de alcance) |
| **Fecha** | 2026-10-08 |

## Resultado general

- Estado: **Apto con observaciones**

El SPEC deja explícito el objetivo: **readaptar** install de `laravel-core` y `react-core` de Satis+Verdaccio a Cloudsmith. El instructivo es anexo de pasos, no el único Must.

## Ambigüedades críticas

- Ninguna que impida dos implementaciones funcionalmente distintas del **origen** de paquetes.

## Ambigüedades menores

- La TR aún debe elegir el snippet Forge (`auth.json` vs header `X-API-KEY` en el runner); el SPEC las trata como la misma regla (token no en git).
- Pins congelados a la oleada del anexo; un bump de Cloudsmith antes del merge requiere decisión humana, no cambio silencioso.
- Scripts auxiliares (`refresh-react-core-lock.sh`, `MODO-REPO-LAB.md`) están en impacto técnico; el criterio de éxito es el default de **deploy**, no el modo lab `file:`.

## Supuestos detectados

- No se crean workflows GHA si hoy no hay job de install.
- Tango queda fuera.
- Publicación y Fase 7 Framework quedan fuera.
- `CLOUDSMITH_API_KEY` no se usa en Partes.

## Preguntas para decisión humana

- Ninguna bloqueante. Opcional: confirmar pins si Cloudsmith publica otro prerelease antes del merge.

## Recomendaciones de ajuste del SPEC

- Ninguna bloqueante. La TR puede citar el anexo para YAML/bash sin reabrir alcance.

## Veredicto

- Puede pasar a HU: **Sí**
