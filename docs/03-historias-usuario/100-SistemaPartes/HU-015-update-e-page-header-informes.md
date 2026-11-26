# HU-015-update-e – Encabezado de página rico en diseño de informes (oleada 5)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-e |
| Título | Empresa, fecha, título y títulos de columna en page header vía Smart Capture |
| Épica | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 5) |
| Estado | **Finalizado** (2026-11-20, alineado TR-015-update-e) |
| SPEC | [SPEC-015-update oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) |
| TR | [TR-015-update-e](../../04-tareas/100-SistemaPartes/TR-015-update-e-page-header-informes.md) |
| HU previa | [HU-015-update-c](./HU-015-update-c-apariencia-controles-informes.md) · oleadas 4 (CHK-01…03) |

**Plantilla regla 19:** GEN-03 + GEN-15; apply sidecar Framework; host Partes = whitelist + heurísticas. No apply PHP.

---

## Trazabilidad SPEC

| SPEC (CA / entregable) | HU |
|------------------------|-----|
| CA-B5-01 CHK-04 → planner | AC-01, Gherkin §CHK-04 |
| CA-B5-02 apply columnas fila 3 | AC-02 |
| CA-B5-03 CHK-06 empresa | AC-03 |
| CA-B5-04 CHK-07 fecha XRPageInfo | AC-04 |
| CA-B5-05 CHK-05 panel título fila 2 | AC-05 |
| CA-B5-06 L-B-10 sin regresión | AC-06 |
| §3.3 tres filas PageHeader | AC-07 |
| D-06 noop + mensaje sin columnas | AC-08 |

---

## Narrativa

Como **diseñador** con permiso `emission.design`, quiero pedir en lenguaje natural el **encabezado de página** del informe (empresa, fecha, título y nombres de columna), para obtener un layout profesional repetido en cada página **sin** editar manualmente cada banda y control en DevExpress.

---

## Contexto funcional

Tras oleadas 3–4 el informe puede tener detalle, título (`setReportTitle`) y bandas vacías. La oleada 5 completa el **page header en tres filas** (empresa/fecha · título · encabezados de columna) mediante acciones whitelist en el sidecar y heurísticas en el turno Smart Capture del proceso `partes.informes.consultaDetallada`.

---

## Alcance incluido

| CHK | Intención usuario (prompt canónico) |
|-----|-------------------------------------|
| CHK-04 | Títulos de columna en encabezado de página |
| CHK-06 | Nombre de empresa arriba a la izquierda |
| CHK-07 | Fecha con mes en letras arriba a la derecha |
| CHK-05 | Título del informe dentro de un panel (fila 2) |

Entrega **incremental** por hitos T1→T4 (SPEC §9); cada hito cerrado con F1 antes del siguiente.

---

## Fuera de alcance

- CHK-08-format (estilo título distinto de panel).
- Pies, calculados, imágenes, agrupaciones nuevas, eliminaciones CHK-28…32, comprobantes CHK-23…25.
- Nombre de empresa desde sesión/SP en sidecar v1.
- Mobile.

---

## Reglas de negocio

- R-B5-01…05 del SPEC (una familia de acción por turno preferido; Save GEN-15; filas fijas; CHK-04 no muta detalle).
- CHK-06 sin nombre en prompt (lab DEV): texto **`Empresa demo`**.
- CHK-04 sin columnas en detalle: **mensaje claro al usuario**, sin error técnico opaco.

---

## Criterios de aceptación

1. **AC-01** — Prompt CHK-04 canónico → turno con `addPageHeaderColumnLabels` (payload vacío o `fields` ⊆ schema).
2. **AC-02** — Apply CHK-04 crea/actualiza `lblPageHdr_{campo}` en **fila 3**, alineados al detalle (±2px).
3. **AC-03** — Prompt CHK-06 → `setPageHeaderEmpresa` con `text` extraído o `Empresa demo` en lab.
4. **AC-04** — Prompt CHK-07 → `setPageHeaderFecha`; REPX contiene `pageInfoFecha` con formato `dd, MMMM 'de' yyyy` sin hora.
5. **AC-05** — Prompt CHK-05 → `wrapReportTitleInPanel`; `pnlReportTitle` en **fila 2** del page header.
6. **AC-06** — Tras cada apply con cambios: banner Guardar/Descartar (L-B-10) operativo.
7. **AC-07** — Convivencia visual: fila 1 empresa+fecha, fila 2 título, fila 3 columnas (no solapamiento).
8. **AC-08** — Detalle sin bindings aplicables + CHK-04 → apply noop; respuesta del asistente explica que faltan columnas en detalle (i18n).

---

## Escenarios Gherkin

```gherkin
Feature: Page header rico — consulta detallada
  Background:
    Given un layout abierto con variantes y columnas en detalle para "partes.informes.consultaDetallada"

  Scenario: CHK-04 títulos de columna
    When envío "Poné un título encima de cada columna en el encabezado de página"
    Then el turno incluye la acción "addPageHeaderColumnLabels"
    And tras apply existen labels "lblPageHdr_" en la fila 3 del page header

  Scenario: CHK-04 sin detalle — mensaje
    Given un layout sin campos ligados en detalle
    When envío el prompt canónico CHK-04
    Then el apply no rompe el lienzo
    And el asistente informa que debe haber columnas en el detalle

  Scenario: CHK-06 empresa
    When envío "Poné el nombre de la empresa arriba a la izquierda"
    Then el turno incluye "setPageHeaderEmpresa" con texto no vacío

  Scenario: CHK-07 fecha
    When envío "Agregá la fecha con el mes en letras arriba a la derecha"
    Then el turno incluye "setPageHeaderFecha"
    And el layout muestra fecha de impresión sin hora en "pageInfoFecha"

  Scenario: CHK-05 panel título
    When envío "Encerrá el título en un panel"
    Then el turno incluye "wrapReportTitleInPanel"
    And el título en fila 2 está dentro de "pnlReportTitle"
```

---

## Supuestos

- Sidecar Framework desplegado con la misma oleada de commits que el host.
- El diseñador ya pasó por CHK-03 o equivalente (`PageHeaderBand` presente o creada por apply).
- Captions de columna CHK-04 provienen del schema JSON del `processCode`.

---

## Preguntas abiertas

- Ninguna bloqueante para TR (decisiones D-01…D-08 cerradas en SPEC A1).

---

## Riesgos de ambigüedad

- Layouts legacy con título solo en `ReportHeaderBand`: la TR define sincronización a fila 2; validar en smoke manual tras T2.

---

## Veredicto B1

**Apta para generación de TR** — alcance acotado al SPEC oleada 5; AC trazables a CA-B5 y §3.3.