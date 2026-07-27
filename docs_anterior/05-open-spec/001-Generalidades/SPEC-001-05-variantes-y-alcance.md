# SPEC-001-05 - Variantes y alcance

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | `HU-GEN-05-*` (a generar; prioridad Fase 0) |
| **Estado** | Especificado |
| **Revisión A1** | Apto con observaciones (2026-05-28) |

## Objetivo

Formalizar límites de alcance del producto en modo MONO y la relación con variantes MULTI para evitar ambigüedades de implementación.

## Estado de ejecución

Implementable en MVP como regla de gobernanza de alcance.

## Decisiones humanas

| Tema | Decisión |
|------|----------|
| Tenant desarrollo | **`cliente = desarrollo`** en middleware local y fila `EMPRESAS_CONEXION` (producto alineado en `INDICE-Sistema-Partes.md` §3) |
| Matriz MONO/MULTI | Incorporada en este SPEC (abajo) |

## Fuente de verdad de producto

- `docs/02-producto/SistemaPartes/PROD-Sistema-Partes.md` — §5 MONO
- `docs/02-producto/SistemaPartes/INDICE-Sistema-Partes.md` — §3 tenancy
- `docs/_base/resolucion-host-cliente-sql-mono.md`

## Fuentes (contexto MONO)

`docs/00-contexto/_mono/05-variantes-y-alcance/mono-vs-multi-referencias.md`

## Alcance

- Delimitar qué aplica a MONO SistemaPartes y qué no.
- Referencias cruzadas permitidas con material MULTI (solo documental).
- Criterio ante dudas: prevalece MONO + producto §5; no implementar patrones MULTI en runtime.

## Fuera de alcance

- Migración del producto a MULTI.
- Runtime dual MONO/MULTI simultáneo.

## Constantes SistemaPartes MONO (producto)

| Concepto | Valor |
|----------|--------|
| `{proyecto}` | `SistemaPartes` |
| Entrada | `https://{cliente}.SistemaPartes.paqsystems.com` |
| Frontend | `https://frontend.SistemaPartes.paqsystems.com` |
| Backend | `https://backend.SistemaPartes.paqsystems.com` |
| Header tenant API | `X-Paq-Cliente: {cliente}` |
| Base SQL | `pq_SistemaPartes_{cliente}` |
| Desarrollo local | `X-Paq-Cliente: desarrollo` + `EMPRESAS_CONEXION.CODIGO_TENANT = desarrollo` |

## Matriz MONO vs MULTI (aplica en SistemaPartes MVP)

| Tema | MONO (SistemaPartes) | MULTI (no implementar) |
|------|-------------------|-------------------------|
| Header tenant | `X-Paq-Cliente` | `X-Company-Id` |
| Selector empresa en UI | No | Sí |
| Empresa activa en sesión | No (tenant único por deploy) | Sí |
| Tema por empresa | No; tema por usuario (avatar) | Sí |
| `Pq_Permiso` por empresa | Una asignación típica por usuario | Varias filas por usuario |
| Recarga menú al cambiar empresa | No aplica | Sí |
| `EMPRESAS_CONEXION` | Sí, por `CODIGO_TENANT` | Patrón análogo |

## Entregables verificables

- Esta matriz + constantes en SPEC y producto §5 sin contradicción.
- Reglas de infra en `resolucion-host-cliente-sql-mono.md` aplicadas en `SPEC-101-01`.

## Criterios de aceptación medibles

- [ ] Queda explícito que **`X-Company-Id` no aplica** en SistemaPartes MONO.
- [ ] Tenancy y `desarrollo` documentados sin contradicción entre SPEC-001-05, producto §5 y SPEC MVP §3.
- [ ] Ningún flujo MVP exige selector de empresa.

## Trazabilidad HU

| HU | Tema SPEC (a generar) |
|----|------------------------|
| HU-GEN-05-tenancy-mono | Headers, `EMPRESAS_CONEXION`, `desarrollo` |
| HU-GEN-05-matriz-mono-multi | Gobernanza alcance (documental + AC en código) |








