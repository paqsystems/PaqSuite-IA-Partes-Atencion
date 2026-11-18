# SPEC-015-update (oleada 5) — Page header rico vía Smart Capture

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-015-update-oleada-5-page-header |
| Título | Encabezado de página: columnas, empresa, fecha y panel de título |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Especificado** (Parte A, 2026-11-18) |
| SPEC previa | [Oleada 3 apariencia](./SPEC-015-ia-diseno-informes-oleada-3-apariencia-update.md) (Finalizado) · [Oleada 2 B](./SPEC-015-ia-diseno-informes-oleada-2-b-update.md) |
| Plan origen | [oleada-5-page-header-plan](./SPEC-015-ia-diseno-informes-oleada-5-page-header-plan.md) |
| HU | *(Parte B — pendiente)* `HU-015-update-e` |
| TR | *(Parte C — pendiente)* `TR-015-update-e` |
| Contrato Framework | Extender [15-reportes-emisiones-update-4-ia-diseno.md](../../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-update-4-ia-diseno.md) §oleada 5 |
| Matriz CHK | Framework `15-reportes-emisiones-ia-checklist-matriz.json` — **CHK-04, CHK-05, CHK-06, CHK-07** |
| Proceso piloto | `partes.informes.consultaDetallada` |

**Plantilla regla 19:**  
Diseño informes Partes oleada 5: adoptar **GEN-03** + **GEN-15**; nuevas acciones whitelist en sidecar Framework; heurísticas en host Partes. **No** reimplementar apply en PHP. SoT: este SPEC + matriz.

---

## 1. Resumen

- **Problema:** tras CHK-03 existen bandas `PageHeader`/`ReportHeader`, pero el usuario **no** puede pedir en lenguaje natural encabezados de columna, empresa, fecha con formato ni recuadro del título.
- **Resultado:** Smart Capture mapea a acciones **`addPageHeaderColumnLabels`**, **`setPageHeaderEmpresa`**, **`setPageHeaderFecha`**, **`wrapReportTitleInPanel`**; sidecar muta REPX; revisión L-B-10.

---

## 2. GEN adoptados

| GEN | Rol |
|-----|-----|
| GEN-03 | Turno + panel Smart Capture |
| GEN-15 | Diseñador + Save / Descartar |
| GEN-16 | BYOK (LLM opcional; heurísticas MUST) |
| GEN-37 | Resolución semántica → `fieldName` (CHK-04) |

---

## 3. Alcance oleada 5

### 3.1 En alcance (entrega por fases 5.1 → 5.4)

| Orden | CHK | Acción | Banda DX principal |
|-------|-----|--------|---------------------|
| **5.1** | CHK-04 | `addPageHeaderColumnLabels` | `PageHeaderBand` |
| **5.2** | CHK-06 | `setPageHeaderEmpresa` | `PageHeaderBand` |
| **5.3** | CHK-07 | `setPageHeaderFecha` | `PageHeaderBand` |
| **5.4** | CHK-05 | `wrapReportTitleInPanel` | `ReportHeaderBand` |

Prompts canónicos (matriz):

| CHK | Prompt |
|-----|--------|
| CHK-04 | Poné un título encima de cada columna en el encabezado de página |
| CHK-06 | Poné el nombre de la empresa arriba a la izquierda |
| CHK-07 | Agregá la fecha con el mes en letras arriba a la derecha |
| CHK-05 | Encerrá el título en un panel |

**Prerrequisito:** layout con `PageHeaderBand` (CHK-03 / `setReportTitle`). Si falta, el sidecar **debe** crear banda con altura para **tres filas** antes de CHK-04…07 (misma política que oleada 3 + §3.3).

### 3.3 Modelo visual — tres filas en `PageHeaderBand` (decisión producto)

Todo el encabezado “rico” del informe vive en **`PageHeaderBand`** (repite en cada página). Tres filas, de arriba hacia abajo:

| Fila | Contenido | CHK / acción |
|------|-----------|----------------|
| **1** | Empresa arriba **izquierda** · fecha arriba **derecha** | CHK-06 / CHK-07 |
| **2** | **Título del reporte** (`lblReportTitle` o equivalente en page header) | CHK-03 texto previo · CHK-05 panel |
| **3** | **Títulos de columnas** alineados al detalle | CHK-04 |

- **CHK-05** envuelve el título de la **fila 2** en `PageHeaderBand` (no en `ReportHeaderBand`).
- Layouts legacy con `lblReportTitle` solo en `ReportHeaderBand`: los appliers oleada 5 **replican o mueven** el texto a fila 2 y pueden **reducir** `ReportHeaderBand` a altura mínima si queda vacío (detalle TR / idempotencia).
- Altura de banda y `LocationFloat.Y` por fila: constantes en TR (p. ej. fila1 Y=0, fila2 Y=24f, fila3 Y=48f; altura total ≥ 72f).

### 3.2 Fuera de alcance

- CHK-08 (título mayúsculas/subrayado central proceso en page header) — distinto de CHK-05.
- Report footer, calculados, imágenes, agrupación nueva, eliminaciones (**CHK-28…32**, `futuro_producto`).
- Comprobantes **CHK-23…25**.
- Mobile; Prompt-to-Report nativo DX; auto-save.
- Recalcular layout completo tipo “diseñador humano”; solo mutaciones whitelist.

---

## 4. Contrato de acciones (sidecar Framework)

Todas via `POST /api/layout/apply`, envelope host existente. Tipos en whitelist `LayoutApplyService`.

### 4.1 `addPageHeaderColumnLabels` (CHK-04)

**Comportamiento**

1. Asegurar `PageHeaderBand` con espacio para **fila 3** (§3.3); respetar filas 1–2 si ya existen (`lblPageHdr_Empresa`, `pageInfoFecha`, `lblReportTitle`).
2. Recorrer **solo** `XRLabel` en `DetailBand` con `ExpressionBindings` a campos del **schema** del `processCode`.
3. Por cada campo distinto (orden visual: `LocationFloat.X` ascendente): crear o actualizar `XRLabel` en page header **fila 3** con:
   - `Name`: `lblPageHdr_{fieldName}` (sanitizado),
   - `Text`: caption desde schema (`displayName` / label catálogo); si no hay, `fieldName`,
   - **Sin** binding en header (texto de encabezado fijo en diseño; datos siguen en detalle),
   - `LocationFloat.X` / `Width` alineados al control de detalle del mismo campo (tolerancia ±2px; si no hay posición, distribución horizontal equitativa en ancho imprimible),
   - `LocationFloat.Y` = Y de fila 3 (convención TR).
4. **No** añadir columnas para campos que no están en detalle.
5. **Idempotencia:** si ya existe `lblPageHdr_{field}` con mismo texto y posición ±2px → **noop** para ese campo.

**Payload**

| Campo | Tipo | Obligatorio | Notas |
|-------|------|-------------|--------|
| `fields` | `string[]` | No | Vacío u omitido = todos los campos ligados en detalle |
| `style` | object | No | v1: ignorado (formato → `setControlAppearance` en turno aparte) |

**Sin efecto en layout (mensaje al usuario, no excepción HTTP)**

- Sin `DetailBand` o sin ningún binding de schema aplicable → respuesta apply **`ok: true`**, `applied: 0`, `details` con clave explicativa (p. ej. `noop:addPageHeaderColumnLabels:sin_columnas_detalle`); el **turno Smart Capture** (planner/servicio host) **MUST** incluir texto traducido al usuario (i18n) indicando que primero debe haber columnas en el detalle.
- `fields[]` con ítems fuera de schema → omitir; si **ningún** ítem queda válido → mismo patrón (noop + mensaje turno, no 500 del sidecar).

### 4.2 `setPageHeaderEmpresa` (CHK-06)

**Comportamiento**

1. Label `lblPageHdr_Empresa` (o actualizar si existe) en `PageHeaderBand` **fila 1**, anclado **arriba izquierda** (márgenes del informe).
2. Texto = `payload.text` literal (1..200).

**Payload**

| Campo | Tipo | Obligatorio |
|-------|------|-------------|
| `text` | string | Sí |

**Host Partes (planner):** extraer nombre del prompt si el usuario lo nombra; si no, literal **`"Empresa demo"`** en tenant/lab DEV (aceptado producto) — **no** leer sesión en sidecar v1.

### 4.3 `setPageHeaderFecha` (CHK-07)

**Comportamiento**

1. Control **`XRPageInfo`** en `PageHeaderBand` **fila 1**, **arriba derecha**; `PageInfo = DateTime` (fecha de impresión/ejecución, **sin hora** visible).
2. Formato de impresión: patrón **`dd, MMMM 'de' yyyy`** (ejemplo visible: `18, noviembre de 2026`). Locale español en diseño o `FormatString` equivalente DevExpress.
3. Nombre control canónico: **`pageInfoFecha`** (no usar label estático v1).

**Payload**

| Campo | Tipo | Obligatorio | Default |
|-------|------|-------------|---------|
| `format` | string | No | `dd, MMMM 'de' yyyy` |

### 4.4 `wrapReportTitleInPanel` (CHK-05)

**Comportamiento**

1. Objetivo: `lblReportTitle` en **`PageHeaderBand` fila 2** (§3.3). Si el título solo existe en `ReportHeaderBand`, copiar texto a fila 2 antes de envolver.
2. Si no existe título, crear scaffolding (texto desde `DisplayName` del informe o último `setReportTitle`).
3. Envolver `lblReportTitle` de fila 2 en `XRPanel` `pnlReportTitle` con borde visible (estilo stock DX v1).
4. **Idempotencia:** si ya está dentro de `pnlReportTitle` con borde en fila 2 → noop.

**Payload**

| Campo | Tipo | Obligatorio |
|-------|------|-------------|
| *(vacío)* | — | Sí aceptable |

---

## 5. Decisiones de diseño (A1)

| ID | Decisión |
|----|----------|
| **D-01** | CHK-04: universo = campos con binding en **DetailBand** visibles en layout actual; captions desde **schema** `processCode`. |
| **D-02** | CHK-06 v1: literal en payload; planner Partes extrae texto del prompt; **sin** SP de empresa en sidecar. |
| **D-03** | CHK-07: **`XRPageInfo`** DateTime; formato default `dd, MMMM 'de' yyyy`; sin hora. |
| **D-04** | CHK-05: panel en **PageHeader fila 2** alrededor de `lblReportTitle`. |
| **D-05** | Cada acción: noop parcial permitido por control; apply cuenta `applied` si ≥1 cambio. |
| **D-06** | CHK-04 sin columnas en detalle: apply noop + **mensaje al usuario** en turno (no fallo HTTP sidecar). |
| **D-07** | CHK-06 sin nombre en prompt (lab DEV): payload `text` = **`Empresa demo`**. |
| **D-08** | Encabezado unificado: **tres filas** en `PageHeaderBand` (empresa/fecha · título · columnas). |

---

## 6. Reglas de negocio (R-B5)

| ID | Regla |
|----|--------|
| R-B5-01 | Solo acciones whitelist oleada 5; rechazar tipos desconocidos. |
| R-B5-02 | Un turno Smart Capture preferido con **una** familia de acción (alineado L-B-04). |
| R-B5-03 | Persistencia solo Save GEN-15; L-B-10 sin cambios. |
| R-B5-04 | CHK-04 no borra ni reordena controles de detalle. |
| R-B5-05 | Fila 1 = empresa + fecha; fila 2 = título; fila 3 = `lblPageHdr_*` columnas; ninguna acción oleada 5 reubica controles de otra fila salvo sincronizar título legacy → fila 2. |

---

## 7. Criterios de aceptación verificables

| ID | Criterio | Capa |
|----|----------|------|
| CA-B5-01 | CHK-04 prompt → planner `addPageHeaderColumnLabels` | PHPUnit matriz |
| CA-B5-02 | Apply crea labels en page header alineados a detalle principal | .NET + script apply |
| CA-B5-03 | CHK-06 → `setPageHeaderEmpresa` + texto en REPX | Idem |
| CA-B5-04 | CHK-07 → fecha mes en letras, sin hora visible | Manual + REPX |
| CA-B5-05 | CHK-05 → `pnlReportTitle` + borde | Manual |
| CA-B5-06 | Cada fase: banner Guardar/Descartar sin regresión | Smoke UI |

---

## 8. Host Partes

- Ampliar `DesignLayoutWhitelist.php` con las cuatro acciones.
- Heurísticas dedicadas (o módulo `DesignLayoutPageHeaderHeuristics.php`) por CHK; **sin** mapper LLM que invente SQL/REPX.
- Sanitize: validar `fields[]` ⊆ schema.
- Catálogo semántico existente (GEN-37) para CHK-04 si el prompt nombra columnas explícitas.

---

## 9. Implementación incremental (vincula TR)

| Hito | CHK | Entrada D |
|------|-----|-----------|
| T1 | CHK-04 | Sidecar applier + planner + matriz `mvp` |
| T2 | CHK-06 | |
| T3 | CHK-07 | |
| T4 | CHK-05 | |

No mergear hitos en un solo PR sin CA del hito anterior verificado (F1).

---

## 10. SDD

| Parte | Estado |
|-------|--------|
| **A (este doc)** | **Hecho** 2026-11-18 |
| A1 | §5 + ajuste contratos 2026-11-18 (D-06…D-08, tres filas) |
| B HU | Pendiente |
| C TR | Pendiente |
| D/E | Pendiente |

---

## 11. Trazabilidad matriz

Tras cada hito: actualizar `estado` CHK → `mvp` / `mvp_parcial`, `expectedActions`, `applyAssert`, `testLayers`.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-18 | Parte A — SPEC formal oleada 5 page header |
| 2026-11-18 | Contratos: noop+mensaje CHK-04; Empresa demo; XRPageInfo `dd, MMMM 'de' yyyy`; tres filas PageHeader |