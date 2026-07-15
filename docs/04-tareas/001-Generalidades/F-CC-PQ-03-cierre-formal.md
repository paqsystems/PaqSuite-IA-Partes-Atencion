# Cierre F — CC PQ #3 (09/06/2026) — Listas, layouts, carga, parámetros

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


## Alcance

Verificación **F1 + F** (openspec-05) sobre correcciones derivadas del Control de Calidad #3:

| TR-update | HU-update | SPEC-update |
|-----------|-----------|-------------|
| [TR-GEN-01-shell-layout-update](../updates/001-Generalidades/TR-GEN-01-shell-layout-update.md) | [HU-GEN-01-shell-layout-update](../../03-historias-usuario/updates/001-Generalidades/HU-GEN-01-shell-layout-update.md) | [SPEC-001-01-experiencia-base-update](../../05-open-spec/updates/001-Generalidades/SPEC-001-01-experiencia-base-update.md) |
| [TR-GEN-03-layouts-grilla-update](../updates/001-Generalidades/TR-GEN-03-layouts-grilla-update.md) | [HU-GEN-03-layouts-grilla-update](../../03-historias-usuario/updates/001-Generalidades/HU-GEN-03-layouts-grilla-update.md) | [SPEC-001-03-ui-transversal-update](../../05-open-spec/updates/001-Generalidades/SPEC-001-03-ui-transversal-update.md) |
| [TR-GEN-04-consulta-parametros-update](../updates/001-Generalidades/TR-GEN-04-consulta-parametros-update.md) | [HU-GEN-04-consulta-parametros-update](../../03-historias-usuario/updates/001-Generalidades/HU-GEN-04-consulta-parametros-update.md) | [SPEC-001-04-configuracion-global-update](../../05-open-spec/updates/001-Generalidades/SPEC-001-04-configuracion-global-update.md) |
| [TR-SPEC-101-10-pantalla-carga-update](../../02-producto/SistemaPartes/INDICE-Sistema-Partes.md) | [HU-101-005-inicializacion-cabecera-update](../../02-producto/SistemaPartes/INDICE-Sistema-Partes.md) | [SPEC-101-10-pantalla-carga-update](../../02-producto/SistemaPartes/INDICE-Sistema-Partes.md) |

**Fecha verificación:** 09/06/2026  
**Parte E:** [E-CC-PQ-03-tests.md](E-CC-PQ-03-tests.md)  
**Rama / build:** `v1.1.0` (working tree local)

---

## F1 — Verificación del agente (código + tests)

**Resultado F1:** **Aprobado con observaciones**

### TR-GEN-01 — Shell / listas DevExtreme

| AC / T | Evidencia código | Estado |
|--------|------------------|--------|
| T1 `SelectBoxDx` + loading | `frontend/src/shared/ui/controls/SelectBoxDx.tsx`, `useDxSelectBoxLoadState.ts` | OK |
| T1 auto-match único | `tryAutoSelectSingleMatch.ts` + `minSearchLength` en handler | OK |
| T1 i18n 5 locales | `selectBox.loading` en `es/en/pt/fr/it.json` | OK |
| T2 integración smoke | `PedidosCargaPage.tsx` — `SelectBoxDx` cliente + artículo | OK |
| T2 JSDoc contrato 101 | `SelectBoxDx.tsx` header | OK |
| T3 Vitest | `tryAutoSelectSingleMatch.test.ts`, `useDxSelectBoxLoadState.test.ts` | OK |
| OpenAPI sin cambios | Sin diff en rutas `api.php` / controllers por este slice | OK |

### TR-GEN-03 — Layouts + totalizadores pie

| AC / T | Evidencia código | Estado |
|--------|------------------|--------|
| T2 `captureState` / `applyState` | `DataGridDx.tsx` + `PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY` | OK |
| T2 excluye placeholder | `filterRealSummaryItems` en capture | OK |
| T3 regla mono §1.11 | `08-devextreme-grid-standards.md` ampliado | OK |
| T4 tests | `dataGridDxLayoutState.test.ts` | OK (unitario) |
| CA-CC3-01 E2E guardar/recargar footer | No automatizado end-to-end | Observación |

### TR-GEN-04 — Consulta parámetros

| AC / T | Evidencia código | Estado |
|--------|------------------|--------|
| T1 `alignment="center"` Valor | `ParametrosConsultaPage.tsx` | OK |
| T2 Vitest | `ParametrosConsultaPage.test.tsx` | OK |
| T2 E2E smoke | `consultas-d1.spec.ts` — listado parámetros | OK |

### TR-SPEC-101-10 — Pantalla carga

| AC / T | Evidencia código | Estado |
|--------|------------------|--------|
| T1 cliente loading + auto-match | `PedidosCargaPage.tsx` `SelectBoxDx` + `clientesLoading` | OK |
| T1 cache clientes sesión | `comprobanteApi.ts` `cachedClientes` | OK |
| T2 display `codigo - descripcion` | `cargaCatalogos.ts` + i18n `articuloDisplay*` | OK |
| T2 optimización artículos | `minSearchLength={2}`, `onLoadingChanged` en `useArticulosCargaDataSource` | OK |
| T2 exclusión BASE | `PqPartesTipoTareaScopeTest` (unit); feature skipped sin tabla | OK |
| T3 batch precios | `actualizarPreciosRenglonesPorLista` + test existente | OK |
| T3 indicador progreso >500ms | No implementado (opcional en TR) | Observación |
| T4 E2E carga grabar | `mvp-section9.spec.ts` | OK |
| Ubicación selector cliente | En `PedidosCargaPage`, no `ComprobanteCabeceraForm` | OK (equivalente funcional) |

---

## F — Verificación documental (TR ↔ SPEC ↔ HU ↔ producto)

**Resultado F:** **Aprobado con observaciones**

### Coherencia docs ↔ implementación

| Documento | Alineado | Nota |
|-----------|----------|------|
| TR-updates (4) | Sí | T1–T4 marcados; criterios OpenAPI/manual pendientes Parte I |
| SPEC-updates (4) | Sí | Alcance cubierto en código |
| HU-updates (4) | Parcial | CA performance (02/03) sin benchmark formal |
| `pantalla-carga-comprobante-ui.md` §3 | **Corregido en F** | Plantilla con `{{codigo}}`; `minSearchLength=2` |
| `08-devextreme-grid-standards.md` §1.11 | Sí | Totalizadores en layouts |
| `00-ControlCalidad-PQ.md` #3 | Sí | Ítems *Procesado*; entorno/build actualizados |
| `docs/99-manual-usuario/SistemaPartes.md` § carga | No | **Pendiente Parte I** (criterio TR-101-10) |

### Evidencia tests (Parte E)

Ver [E-CC-PQ-03-tests.md](E-CC-PQ-03-tests.md): Vitest **98/98**, E2E CC3 **11/11**, PHPUnit SistemaPartes **81 passed** (+ skips entorno).

### Observaciones no bloqueantes

| Ítem | Motivo | Destino |
|------|--------|---------|
| Benchmark formal tiempos clientes/artículos | Cache + `minSearchLength` sin medición documentada | QA manual PQ o Parte I |
| E2E layout con totalizador pie persistido | Cubierto por unit `dataGridDxLayoutState` | QA manual PQ recomendado |
| Manual usuario § carga CC3 | TR marca Parte I | Parte I |
| `Finalizado` en metadatos HU/TR-update | Manual PQ post-revisión | Parte I |

---

## Veredicto final

| Slice CC #3 | F1 | F |
|---------------|----|---|
| GEN-01 listas | Aprobado | Aprobado |
| GEN-03 layouts footer | Aprobado con observaciones | Aprobado con observaciones |
| GEN-04 parámetros | Aprobado | Aprobado |
| 101-10 pantalla carga | Aprobado con observaciones | Aprobado con observaciones |

**Estado CC #3:** **Finalizado (Parte I 09/06/2026)** — Partes **D + E + F + I** cerradas.  
**Unificación:** [I-CC-PQ-03-cierre-formal.md](I-CC-PQ-03-cierre-formal.md) — QA manual PQ aprobado.






