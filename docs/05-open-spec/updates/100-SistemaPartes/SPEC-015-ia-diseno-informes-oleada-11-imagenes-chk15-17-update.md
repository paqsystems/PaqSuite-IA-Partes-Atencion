# SPEC-015-update (oleada 11) — Imágenes, logo y QR (CHK-15, CHK-16, CHK-17)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-11-imagenes-chk15-17 |
| Título | Imagen de fondo, logo de empresa y código QR en detalle vía Smart Capture |
| Estado | **Finalizado** (F 2026-11-25; smoke OK) |
| Jira | Épica [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) · Story [PIAPARTES-78](https://paqsystems.atlassian.net/browse/PIAPARTES-78) · Task [PIAPARTES-79](https://paqsystems.atlassian.net/browse/PIAPARTES-79) |
| HU | [HU-015-update-m](../../../03-historias-usuario/100-SistemaPartes/HU-015-update-m-imagenes-chk15-17.md) |
| TR | [TR-015-update-m](../../../04-tareas/100-SistemaPartes/TR-015-update-m-imagenes-chk15-17.md) |
| Prompts nuevo hilo | [prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md](../../../06-operacion/prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md) |
| SPEC previa | [Oleada 10 CHK-11..14](./SPEC-015-ia-diseno-informes-oleada-10-calculados-chk11-14-update.md) |
| Matriz | Framework [`15-reportes-emisiones-ia-checklist-matriz.json`](../../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json) |
| Proceso piloto | `partes.informes.consultaDetallada` |

**GEN-03 + GEN-15** — mutación REPX en sidecar `dx-reporting`; heurísticas y whitelist en Partes. No reimplementar apply en PHP (regla 19).

---

## 1. Resumen

Prioridad producto: cerrar el hueco **L-B-02** (imágenes / QR) con tres acciones whitelist nuevas. El resto de ítems aún sin oleada dedicada permanecen **`diferido`** en matriz (p. ej. CHK-22 columnas detalle); CHK-26 orientación conserva código lab pero **sin prioridad** hasta cerrar esta oleada.

---

## 2. Esquema de prompts (canónico)

| CHK | Prompt canónico (pegar en SC) | Intención usuario |
|-----|------------------------------|-------------------|
| **CHK-15** | `Agregá una imagen de fondo al informe` | Marca de agua / fondo suave en todo el informe (no sustituye el detalle). |
| **CHK-16** | `Insertá el logo de la empresa` | Logo institucional visible en encabezado de página (convive con CHK-06 empresa como texto si ya existe). |
| **CHK-17** | `Mostrá clienteCode como código QR` | Control de código de barras QR en **Detail** enlazado al campo `clienteCode`. |

### 2.1 Alias aceptados (heurística v1 — no sustituyen el canónico en matriz)

| CHK | Variantes (español rioplatense) |
|-----|----------------------------------|
| 15 | `Poné una imagen de fondo`, `Watermark en el informe`, `Fondo con imagen` |
| 16 | `Agregá el logo de la empresa`, `Logo arriba a la izquierda`, `Poné el logo` |
| 17 | `Código QR del cliente`, `QR con el código de cliente`, `clienteCode en QR` |

### 2.2 Respuesta SC cuando aún no hay apply (hasta D)

Mensaje claro tipo *«Todavía no puedo aplicar imágenes/QR en este informe»* → clave i18n `partes.emissionDesign.images.notYetMapped` (hasta implementar acciones).

---

## 3. Alcance técnico (contrato acciones)

| CHK | Acción whitelist | Payload mínimo | Assert REPX (lab) | Banda / control |
|-----|------------------|----------------|-------------------|-----------------|
| **15** | `setReportWatermarkImage` | `{ "source": "default" }` — v1 solo asset empaquetado sidecar `watermark-pilot.png`; sin upload usuario en v1 | `Watermark` o `Watermark.Image` | Informe (watermark DX) |
| **16** | `setPageHeaderLogo` | `{ "source": "empresa" }` — v1 URL/logo desde parámetro o placeholder lab `logo-pilot.png` | `imgPageHdr_Logo`, `PictureBox`, fila 1 | **Page Header** sup. izq. (ajustar X si CHK-06 ya colocó empresa) |
| **17** | `addDetailBarCode` | `{ "field": "clienteCode", "symbology": "QRCode" }` | `XRBarCode`, `clienteCode`, `Symbology=QRCode` | **Detail** (junto o debajo de columna existente) |

Fuera de alcance oleada 11:

- Upload de imagen arbitraria desde SC (L-B-08).
- QR sobre campos distintos de `clienteCode` sin ampliar schema.
- CHK-22, columnas multi-layout detalle.
- Familia `remove*` (CHK-28…32).

---

## 4. Criterios de aceptación

| ID | Criterio |
|----|----------|
| CA-B11-01 | Planner matriz CHK-15/16/17 → acciones §3 (PHPUnit `DesignLayoutChecklistPlannerTest`). |
| CA-B11-02 | Sidecar aplica sin error HTTP; backup L-B-10; banner Guardar/Descartar. |
| CA-B11-03 | Script `eval-emission-ia-oleada11-chk15-17-apply.ps1` PASS los tres ítems. |
| CA-B11-04 | Re-aplicar mismo prompt → noop con mensaje i18n (`noop:setReportWatermarkImage:ya_existe`, `noop:setPageHeaderLogo:ya_colocados`, `noop:addDetailBarCode:ya_existe`) donde aplique. |
| CA-B11-05 | Sin regresión CHK-06/07 page header y CHK-01 detalle. |

---

## 5. Smoke manual (orden sugerido)

1. Variante **principal** Consulta detallada; sidecar `:5055`; BYOK SC habilitado.
2. **CHK-16** → **CHK-15** → **CHK-17** (logo antes de fondo para validar convivencia visual; QR al final porque requiere columna `clienteCode` en layout).
3. Tras cada apply: preview / explorer; **Descartar** entre ítems salvo que se acumule a propósito.

| CHK | Éxito mínimo manual |
|-----|---------------------|
| 15 | Watermark visible en preview (opacidad acordada TR). |
| 16 | PictureBox con logo en page header; no solapa ilegible con fecha (CHK-07). |
| 17 | QR escaneable con valor de `clienteCode` de una fila de ejemplo. |

---

## 6. Trazabilidad GEN

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno Smart Capture bajo diseñador |
| GEN-15 | Diseñador + catálogo reportes; apply sidecar |
| GEN-37 | Campo `clienteCode` en schema autorizado |

Plantilla regla 19: `[Proceso] Partes consulta detallada: adoptar apply sidecar Framework. UI/motor = whitelist + appliers C#. Host: planner + heurísticas CHK-15..17.`