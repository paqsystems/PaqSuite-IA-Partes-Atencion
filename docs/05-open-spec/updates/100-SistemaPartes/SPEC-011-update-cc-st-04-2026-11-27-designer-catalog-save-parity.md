# SPEC-011-update — Paridad catálogo shell vs Save DX + hydrate BD→sidecar

Jira: [PIAPARTES-82](https://paqsystems.atlassian.net/browse/PIAPARTES-82) (Epic CC-ST #4)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | SPEC-011-update-cc-st-04-catalog-save |
| Título | Diseñador GEN-15 — paridad Save/Save As con catálogo + sincronización layout al abrir variante |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Pendiente** |
| Última actualización | 27/11/2026 |
| SPEC base | [SPEC-011-reportes-emisiones](../../100-SistemaPartes/SPEC-011-reportes-emisiones.md) |
| Origen | `00-ControlCalidad-ST` · CC #4 · **27/11/2026** |
| GEN | **GEN-15** (diseñador / catálogo) · sin reimplementar motor |
| HU / TR | [HU-011-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-011-reportes-emisiones-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) · [TR-011-update](../../../04-tareas/updates/100-SistemaPartes/TR-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) |
| Mapa rutas | [mapa-rutas-disenador-emisiones-gen15.md](../../../06-operacion/mapa-rutas-disenador-emisiones-gen15.md) |
| Relacionado | CC ST #3 (schema Dataset — **épica aparte**); [persistencia-layouts](../../06-operacion/persistencia-layouts-emisiones-dx.md) |
| Revisión A1 | **Apto con observaciones** — [A1 CC-ST-4](./SPEC-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity-A1-ambiguity-review.md) (2026-11-27) |

**Plantilla regla 19:**  
Diseñador emisiones Partes: adoptar **GEN-15** (`EmissionReportDesignerPage`, callbacks Save, APIs `/emissions/design/*`). No segundo diseñador ni catálogo paralelo. SoT Framework SPEC-001-15. Host: hydrate layout BD→sidecar + alinear listas Save con catálogo del proceso.

---

## 1. Problema

En `/emisiones/disenador` el **SelectBox del shell** lista variantes desde catálogo Laravel (`GET …/design/processes/{processCode}/reports`) con nombres legibles y cardinalidad N.

El diálogo nativo **Guardar / Guardar como** de DevExpress usa `FileReportStorage.GetUrls()` en el sidecar: solo archivos `*.repx` existentes en disco **intersectados** con URLs del endpoint `report-storage-urls`. En lab, N del shell >> ítems en Save (evidencia CC #4).

Consecuencias:

- El operador no puede **sobrescribir** variantes que ve en el combo pero no aparecen en Save.
- Variantes solo en BD (`layout_definition`) sin `.repx` en sidecar abren layout incoherente o vacío.
- Convivencia histórica `partes.consultaDetallada.principal` vs `partes_consultaDetallada_principal` confunde diagnóstico.

## 2. Cambio de alcance

| Antes | Ahora (este update) |
|-------|---------------------|
| Catálogo shell y Save DX son independientes en la práctica | **Must:** misma cobertura de variantes del proceso en SelectBox y en diálogo Save/Save As (tras normalización de código) |
| Abrir variante asume `.repx` previo en sidecar | **Must:** **hydrate batch** al **confirmar proceso** (D-ST4-A1-01-A) + hydrate puntual al cambiar variante si aún falta `.repx` |
| Mapa de rutas disperso en TR/README | Documento operativo **mapa rutas** + checklist diagnóstico en runbook |

## 2.1 Decisión de diseño (A1 cerrada)

| ID | Decisión | Estado |
|----|----------|--------|
| **D-ST4-A1-01** | Estrategia paridad Save / catálogo | **Cerrada — opción A** (27/11/2026): tras **confirmar proceso** en el diseñador, el host ejecuta **hydrate batch idempotente** de todas las variantes activas del catálogo diseño con `layout_definition` XML en BD y sin `.repx` canónico en sidecar. **No** se modifica `GetUrls` en Framework en este hito (opción B fuera). |
| **D-ST4-A1-02** | `is_standard = 1` | **Cerrada — opción B acotada** (27/11/2026): solo proceso **`partes.informes.consultaDetallada`**, fila estándar sin REPX en BD → hydrate desde plantilla host `resources/emissions/partes_consultaDetallada_principal.repx` (misma fuente que fallback emisión). Resto de procesos: **R-ST4-06** (omitir). Reversible en SPEC-update futuro. |

## 3. Reglas de negocio nuevas

**R-ST4-01.** Para el `processCode` confirmado, el conjunto de códigos de reporte elegibles en Save/Save As del sidecar debe corresponder al catálogo Laravel del mismo proceso (no mezclar otros procesos — ya D-EM-13; reforzar con tests). **K** = filas de `reportsForDesignCatalog` (activas, mismo proceso), deduplicadas por clave canónica de `report_code` (`EmissionReportDxStorageUrl` / `EmissionReportUrlNormalizer`).

**R-ST4-02.** **Batch (opción A):** Inmediatamente después de que el usuario **confirma el proceso** emisible en el shell GEN-15, el host **debe** invocar hydrate para cada variante del catálogo diseño que tenga XML válido en `layout_definition` y **no** tenga ya un `.repx` en el sidecar para su `reportUrl` canónico. Errores parciales: informar al operador; no bloquear silenciosamente si el sidecar no respondió.

**R-ST4-02b.** **Puntual:** Al cambiar la variante en el shell, si aún falta `.repx` pese al batch (variante nueva, fallo previo o race), repetir hydrate de **esa** variante antes de montar el lienzo DX.

**R-ST4-03.** Los captions visibles del shell (`name` catálogo) pueden seguir distintos de los nombres de archivo sidecar; el **código de reporte** canónico es la clave de unión (una convención por variante documentada en TR).

**R-ST4-04.** Save DX sigue disparando callbacks GEN-15 (PUT layout / POST report); hydrate **no** sustituye guardar en BD.

**R-ST4-05.** Si ya existe `.repx` en sidecar para la clave canónica, **no** sobrescribir desde BD en hydrate (sidecar = copia de trabajo en sesión; el usuario puede haber editado sin Guardar en catálogo). Excepción documentada en TR solo con flag explícito «forzar desde catálogo» — **fuera** de v1.

**R-ST4-06.** Variantes `is_standard = 1` **sin** `layout_definition` REPX válido en BD: el batch **omite** hydrate desde BD (siguen dependiendo de `.repx` en sidecar o seed operativo).

**R-ST4-06b (excepción D-ST4-A1-02).** Solo **`processCode = partes.informes.consultaDetallada`**: la variante estándar (`is_standard = 1`, típicamente «&lt;Original&gt;`) **sí** entra en hydrate si falta `.repx` en sidecar, usando XML del archivo host **`backend/resources/emissions/partes_consultaDetallada_principal.repx`** (equivalente a `SpEmissionRepository::fallbackConsultaDetalladaLayout`). **No** escribe en BD (`R-ST4-04` / semántica estándar intacta). Otros procesos no tienen esta excepción hasta un SPEC-update explícito.

## 4. Fuera de alcance

- CC ST #3 (`processCode` en GetDesignerModel, ocultar Dataset placeholder).
- Unificar naming masivo en BD sin plan de migración.
- Cambiar semántica `is_standard = 1` (layout solo sidecar).
- SPEC-015 Smart Capture / apply (solo mencionar coherencia de `reportUrl`).

## 5. Criterios de aceptación

- [ ] **CA-ST4-01** Con proceso **Consulta detallada**, **K** = variantes activas en `GET …/design/processes/{processCode}/reports` (dedup canónico). **Sin abrir cada variante en el combo**, tras **confirmar proceso**, diálogo **Save** y **Save As** listan **K** destinos sobrescribibles del proceso (intersección disco ∩ `report-storage-urls` = K).
- [ ] **CA-ST4-06** Tras confirmar proceso, el batch hydrate completó (o informó error) antes de que el usuario abra el primer Save; evidencia en log/bitácora de operación opcional.
- [ ] **CA-ST4-02** Variante solo en BD con XML: al elegirla en shell, diseñador muestra layout de BD (no lienzo vacío ni REPX ajeno).
- [ ] **CA-ST4-03** `report-storage-urls` y catálogo shell devuelven conjuntos coherentes en lab (`cliente=DEV`).
- [ ] **CA-ST4-04** Save / Save As tras editar persisten BD vía callbacks existentes; emisión sin regresión TR-011.
- [ ] **CA-ST4-05** Checklist [mapa-rutas](../../../06-operacion/mapa-rutas-disenador-emisiones-gen15.md) + evidencia F1 manual (capturas CC #4).

## 6. Historial

| Fecha | Cambio |
|-------|--------|
| 27/11/2026 | Parte G CC ST #4 — SPEC-update inicial |
| 27/11/2026 | A1 — D-ST4-A1-01 cerrada (opción A batch al confirmar proceso) |
| 27/11/2026 | D-ST4-A1-02 cerrada — excepción B solo Consulta detallada (R-ST4-06b) |