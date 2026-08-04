# Parte E — CC PQ #3 (09/06/2026) — Ejecución de tests

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


> **Cierre global:** Parte I completada — ver [I-CC-PQ-03-cierre-formal.md](I-CC-PQ-03-cierre-formal.md).

## Alcance

Validación automatizada tras **Parte D** (TR-updates CC #3):

| TR-update | Foco |
|-----------|------|
| TR-GEN-01-shell-layout-update | SelectBoxDx, loading, auto-match |
| TR-GEN-03-layouts-grilla-update | `paqSummaryTotalItems` en layouts |
| TR-GEN-04-consulta-parametros-update | Columna Valor centrada |
| TR-SPEC-101-10-pantalla-carga-update | Carga cliente/artículos, display código |

**Fecha ejecución:** 09/06/2026  
**Rama / build:** `v1.1.0` (working tree local)

---

## Backend — PHPUnit

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=SistemaPartes` | **81 passed**, 60 skipped (sin SQL Server / tablas ERP en entorno local) |
| `php artisan test --filter="ParametrosConsulta\|ArticuloCarga\|PqPartesTipoTarea"` | **3 passed**, 3 skipped |

**Nota:** Feature `ArticuloCargaIndexTest` skipped — tabla `PQ_PARTES_TIPOS_TAREA` no disponible en `.env` actual (esperado en Ankas_del_sur compartida).

---

## Frontend — Vitest

| Comando | Resultado |
|---------|-----------|
| `npm run test` | **98 passed** (34 archivos) |

### Tests nuevos / tocados (CC #3)

| Archivo | Tests |
|---------|-------|
| `shared/ui/controls/tryAutoSelectSingleMatch.test.ts` | 2 |
| `shared/ui/controls/useDxSelectBoxLoadState.test.ts` | 2 |
| `shared/ui/grids/dataGridDxLayoutState.test.ts` | 2 |
| `features/config/pages/ParametrosConsultaPage.test.tsx` | 1 |
| `features/pedidos/utils/cargaCatalogos.test.ts` | +1 (display código) |
| `features/pedidos/utils/actualizarPreciosRenglones.test.ts` | 2 (batch precios) |

**Ajuste:** `syncDevExtremeLocale.test.ts` — timeout 15s (import dinámico DX lento en local).

---

## Frontend — Playwright E2E

| Comando | Resultado |
|---------|-----------|
| `npm run test:e2e` — `mvp-section9.spec.ts`, `consultas-d1.spec.ts`, `grid-layouts.spec.ts` | **11 passed** |

### Cobertura CC #3 en E2E

| Ítem CC | Spec / escenario |
|---------|------------------|
| Carga pedido (cliente + artículo + grabar) | `mvp-section9` — grabar pedido |
| Consulta parámetros | `consultas-d1` — listado solo lectura |
| Layouts (toolbar, guardar como, sufijo propio) | `grid-layouts` — 3 tests |

**Ajuste E2E:** `agregarArticuloDemo` adaptado a `minSearchLength=2` y auto-match (`pressSequentially('AR')`, fallback list-item).

---

## Correcciones detectadas en Parte E

| Problema | Fix |
|----------|-----|
| SelectBox artículos quedaba `disabled` tras búsqueda | `useArticulosCargaDataSource` → `onLoadingChanged` del DataSource DX |
| Auto-match antes de `minSearchLength` | `createSelectBoxAutoMatchInputHandler` respeta `minSearchLength` |
| E2E timeout en combobox artículos | Actualizado `mvp-section9.spec.ts` |

---

## Veredicto Parte E

**Resultado:** **Aprobado**

**Parte F:** [F-CC-PQ-03-cierre-formal.md](F-CC-PQ-03-cierre-formal.md) — **Aprobado con observaciones** (09/06/2026).






