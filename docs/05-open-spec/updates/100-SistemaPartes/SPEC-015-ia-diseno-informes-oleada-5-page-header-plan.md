# SPEC-015-update (oleada 5) — Page header rico — PLAN (previo a SPEC definitivo)

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-5-page-header |
| Estado | **Reemplazado por SPEC formal** — ver [oleada-5-page-header-update](./SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| Depende | Oleada 3 cerrada · CHK-03 (`PageHeaderBand` scaffolding) |
| CHK | **CHK-04**, **CHK-05**, **CHK-06**, **CHK-07** (orden de prioridad producto) |
| Fuera | CHK-28…32 y CHK-23…25 → `futuro_producto` (misma prioridad SPEC) |

---

## 1. Objetivo oleada 5

Completar el **page header** operativo del piloto Consulta detallada: encabezados de columna alineados al detalle, recuadro de título si aplica, **empresa** arriba izquierda, **fecha** (mes en letras) arriba derecha — vía Smart Capture + apply sidecar (camino B GEN-03/15).

---

## 2. Orden de desarrollo (obligatorio)

| Fase | CHK | Tema | Entrega incremental |
|------|-----|------|---------------------|
| **5.1** | CHK-04 | Títulos por columna en page header | Labels en `PageHeaderBand` alineados a bindings del `DetailBand` |
| **5.2** | CHK-06 | Empresa sup. izquierda | Literal o binding parametrizado empresa instalación |
| **5.3** | CHK-07 | Fecha sup. derecha (mes en letras) | `PageInfo` o label con formato acordado |
| **5.4** | CHK-05 | Título en panel | `XRPanel` (o equivalente DX) alrededor de título existente |

Cada fase = mini SDD (HU/TR) **o** TR única con hitos T1–T4; no mezclar CHK en un solo apply sin criterio de aceptación por ítem.

---

## 3. Acciones whitelist candidatas (Framework)

| Acción propuesta | CHK | Notas sidecar |
|------------------|-----|---------------|
| `addPageHeaderColumnLabels` | 04 | Lee schema + posiciones detalle; no inventar campos |
| `setPageHeaderEmpresa` | 06 | Texto / parámetro; MONO: nombre empresa sesión vs literal lab |
| `setPageHeaderFecha` | 07 | Formato ES mes en letras; sin hora |
| `wrapReportTitleInPanel` | 05 | Tras `lblReportTitle` o título en report header — definir frontera CHK-03 vs 05 en SPEC A1 |

**Partes:** heurísticas por prompt canónico de matriz; sin forks de apply PHP.

---

## 4. Riesgos / decisiones (resolver en SPEC A)

| # | Tema |
|---|------|
| D-01 | CHK-04: ¿mapeo automático columna↔label desde schema o solo campos visibles en detalle? |
| D-02 | CHK-06: fuente empresa (parámetro, sesión, literal demo DEV) |
| D-03 | CHK-07: `SystemTime`/`PageInfo` vs expresión fija en diseño |
| D-04 | CHK-05: panel en `ReportHeader` vs `PageHeader` |
| D-05 | Idempotencia y noop si ya existe layout manual |

---

## 5. Pruebas (objetivo oleada 5)

| Capa | Qué |
|------|-----|
| PHPUnit | Matriz: CHK-04…07 `plannerExpect: actions` cuando corresponda |
| .NET | Unit apply por acción + fixture `partes_consultaDetallada_principal` |
| Script | Extender o crear `eval-emission-ia-oleada5-apply.ps1` |
| Manual | Guía smoke oleada 5 (4 prompts) + L-B-10 |

---

## 6. Ritual SDD inmediato (próximos pasos)

1. **Parte A** — Expandir este plan a SPEC formal (`SPEC-015-update-oleada-5-page-header.md`) con payloads y reglas R-B5-xx.
2. **B / C** — HU-015-update-e + TR-015-update-e (o TR por CHK si el alcance se divide).
3. **C1** — Revisión ambigüedad TR.
4. **D1 / D** — Implementar **solo CHK-04** primero; matriz mvp; cierre como oleada 3.
5. Repetir **5.2 → 5.4**.

---

## 7. Backlog misma prioridad que comprobantes (`futuro_producto`)

No forman parte de oleada 5:

| CHK | Grupo |
|-----|--------|
| CHK-23 … CHK-25 | Comprobantes / ventas |
| CHK-28 … CHK-32 | Eliminaciones (`remove*`) |

Solo **A0 / SPEC backlog** cuando producto lo autorice; sin implementación en piloto Partes hasta entonces.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-18 | Plan oleada 5 redefinido: page header CHK-04…07; eliminaciones → futuro_producto |