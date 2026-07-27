# TR-GEN-03-grillas-listados — Estándar DataGrid transversal

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-03-grillas-listados](../../03-historias-usuario/001-Generalidades/HU-GEN-03-grillas-listados.md) |
| **SPEC relacionada** | [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout; TR-GEN-02-login-sesion; TR-GEN-01-idioma; TR-GEN-01-apariencia-temas |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 (cierre manual post-F) |

**Cierre F formal:** [F-GEN-03-cierre-formal](F-GEN-03-cierre-formal.md)

**Origen:** [HU-GEN-03-grillas-listados](../../03-historias-usuario/001-Generalidades/HU-GEN-03-grillas-listados.md)  
**Referencia SPEC:** [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

**Contexto MONO:** [`grillas.md`](../../00-contexto/_mono/03-ui-transversal/grillas.md) · Reglas Cursor: `28-ui-grilla-acciones-iconos-tooltip.md`, `08-devextreme-grid-standards.md` (symlink `mono`).

---

## 1) HU Refinada (resumen)

### Título
Estándar de grillas DevExtreme para listados transversales.

### Narrativa
Como usuario operativo quiero listados tabulares homogéneos (filtros, orden, agrupación, totalización, acciones por fila) para operar procesos con la misma experiencia en todo el portal.

### In scope / Out of scope
- **In scope:** wrapper `DataGridDx`, identificación `proceso` + `gridId` (varias grillas por pantalla/proceso), paginación **nativa DevExtreme** (`Paging`/`Pager` del `DataGrid`), orden, filtros, selector/reorden de columnas, group panel, summary, estados loading/empty/error, columna de acciones por **íconos DX + `hint` i18n** (sin texto visible en el control), migración del demo en dashboard.
- **Out of scope:** layouts persistentes (TR-GEN-03-layouts-grilla), exportación Excel (TR-GEN-03-exportaciones), flujo ABM modal y botón **+** de alta (TR-GEN-03-patron-abm), datos de negocio (SPEC-101).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Componente transversal `DataGridDx` usado en al menos el dashboard de referencia.
- **AC-02**: Paginación y orden por columna habilitados por defecto mediante API DevExtreme del `DataGrid` (sin paginador HTML custom).
- **AC-03**: `FilterRow` visible; búsqueda/filtros operativos según columnas declaradas.
- **AC-04**: Estados loading, vacío y error distinguibles (props o slots).
- **AC-05**: `columnChooser` + reorden de columnas habilitados.
- **AC-06**: `groupPanel` (superior) y `summary` (inferior) siempre presentes en el wrapper.
- **AC-07**: Columna de acciones: controles DevExtreme tipo ícono con `hint` i18n (nombre de la acción); **prohibido** `Button` con texto visible o `<button>` HTML.
- **AC-08**: Props obligatorias `proceso` y `gridId`; una pantalla puede declarar **varias** instancias (`gridId` distintos) para el mismo `proceso`.
- **AC-08b**: Props expuestas al toolbar externo (layouts/export en TR siguientes).
- **AC-09**: `data-testid` en contenedor grilla y toolbar base.
- **AC-10**: Captions y mensajes vía i18n; tema DevExtreme activo sin hardcodes de color.
- **AC-11**: E2E smoke: dashboard muestra grilla con datos tras login.

### Escenarios Gherkin

(Heredados de HU — ver [HU-GEN-03-grillas-listados](../../03-historias-usuario/001-Generalidades/HU-GEN-03-grillas-listados.md).)

---

## 3) Reglas de Negocio

1. **RN-01**: Toda grilla transversal usa `DataGridDx` (DevExtreme `DataGrid`) salvo excepción documentada en HU de proceso.
2. **RN-02**: `proceso` = `pq_menus.procedimiento` del proceso host.
3. **RN-03**: **`gridId`** identifica cada instancia de grilla dentro del proceso. **Puede haber más de una grilla por pantalla y por `proceso`** (ej. `main`, `detalle`, `historial`); layouts, exportación y telemetría se filtran por `proceso` + `gridId` (SPEC-001-03).
4. **RN-04**: **Paginación, orden, filtros, selector de columnas, agrupación y totalización** se implementan con las capacidades **nativas del `DataGrid` DevExtreme** (`paging`, `pager`, `sorting`, `filterRow`, `columnChooser`, `groupPanel`, `summary`). No usar paginadores, tablas HTML ni controles paralelos que dupliquen esas funciones.
5. **RN-05**: **Acciones por fila** (editar, eliminar, ver detalle, etc.): columna `type="buttons"` con ítems DevExtreme que exponen **solo ícono** (`icon`) y **nombre de la acción en `hint`** (i18n). **No** usar `Button` con `text` visible ni botones HTML. Regla: `.cursor/rules/base/20-frontend/28-ui-grilla-acciones-iconos-tooltip.md`.
6. **RN-06**: Barras de agrupación y totalización **no opcionales** en el wrapper transversal.
7. **RN-07**: **Alta de registro (insert)** en procesos ABM: **no** se define en este slice. En ABM, el alta es **siempre** con el botón **«+» nativo de la grilla DevExtreme** (`editing.allowAdding` + ítem de toolbar/`addRowButton` del `DataGrid`), no con un `Button` suelto fuera de la grilla. Ver [TR-GEN-03-patron-abm](TR-GEN-03-patron-abm.md) y `grillas.md` § Procesos ABM.
8. **RN-08**: Sin endpoints nuevos en este slice (solo frontend compartido).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-01)

**Fuentes revisadas:** HU-GEN-03-grillas-listados, SPEC-001-03, `grillas.md`, reglas `28-ui-grilla-acciones-iconos-tooltip.md` y `08-devextreme-grid-standards.md`, TR-GEN-01-shell-layout / idioma / apariencia (dependencias), código `LocaleDemoGrid.tsx`, `DashboardPage.tsx`.

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (aplicar resoluciones §3.2)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **`proceso` demo `pw_dashboard`** | No está documentado en seed menú MVP si existe `procedimiento` equivalente | D1: usar `procedimiento` real del ítem dashboard en `paqsuite_mvp.php` o constante acordada `pw_dashboard` solo en demo con comentario; E2E usa el mismo valor que `DashboardPage`. |
| AMB-C02 | **Slots toolbar vs TR hermanas** | `toolbarStart`/`toolbarEnd` sin orden normativo (layouts, export, + ABM) | D1: convención fija — `toolbarStart`: vacío/reservado; `toolbarEnd`: `[layouts] [export] [slots proceso]`; botón **+** ABM va en **toolbar integrada DX** (no en slot externo). Documentar en JSDoc `DataGridDx`. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | `sorting.mode` | Default transversal: **`single`**; prop opcional `sortingMode` para procesos SPEC-101. |
| AMB-M02 | Escape `enableGrouping` en §9 vs RN-06 | Props de escape **default `true`**; desactivar solo con excepción documentada en HU de proceso. |
| AMB-M03 | Gherkin solo en HU | Tests E2E referencian escenarios HU; TR no duplica Gherkin. |
| AMB-M04 | `demoGrid.*` vs `grid.*` i18n | Unificar bajo prefijo **`grid.*`** al migrar `LocaleDemoGrid`. |
| AMB-M05 | Búsqueda global vs `filterRow` | MVP transversal: solo **`filterRow`**; búsqueda global (`searchPanel`) la activa cada TR de proceso si aplica. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU CA-03 “filtros según proceso” vs TR `filterRow` siempre | Coherente: el **proceso declara columnas**; el wrapper **habilita** `filterRow`. |
| Ninguna otra | — |

### Supuestos detectados

- DevExtreme ya instalado y licencia activa (TR-GEN-01-idioma / shell).
- Dashboard autenticado es pantalla de referencia MVP hasta primera grilla SPEC-101.

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-01)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | `proceso` dashboard | Constante documentada en `DashboardPage` alineada a menú MVP o `pw_dashboard` explícito solo demo. |
| R-C1-02 | Orden toolbar externo | `toolbarEnd`: layouts → export → extras proceso; **+** ABM solo toolbar DX. |
| R-C1-03 | Ordenamiento default | `sorting.mode = 'single'`. |
| R-C1-04 | i18n | Migrar claves a `grid.*`. |

---

## 3.3) Plan D1 — Implementación (2026-06-01)

### Alcance entendido

Crear el **wrapper transversal `DataGridDx`** (DevExtreme) con paginación, orden, filtros, column chooser, agrupación, totalización, estados loading/vacío/error, columna de acciones por ícono + `hint`, props `proceso` + `gridId`, slots de toolbar para TRs hermanas, y **referencia en dashboard** reemplazando `LocaleDemoGrid`. Sin backend ni layouts/export/ABM en este slice.

### Fuentes leídas

- SPEC-001-03, HU-GEN-03-grillas-listados, esta TR §3.1–3.2
- `grillas.md`, reglas `28-ui-grilla-acciones-iconos-tooltip.md`, `08-devextreme-grid-standards.md`
- Código: `LocaleDemoGrid.tsx`, `DashboardPage.tsx`, `locale.spec.ts` (E2E grilla demo)
- `backend/config/paqsuite_mvp.php` → `procedimiento` **`pw_dashboard`**

### Decisiones D1 (cierran C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | `proceso` dashboard | **`pw_dashboard`** (alineado a `paqsuite_mvp.php`); `gridId="main"`. |
| D1-2 | Orden toolbar externo | `toolbarEnd`: reservado para layouts → export (TRs 2 y 4); vacío en este slice. |
| D1-3 | Ordenamiento | `sorting.mode = 'single'` por defecto. |
| D1-4 | Demo datos | Mismo dataset que `LocaleDemoGrid` (2 filas) + columna acción mock **editar** (ícono + hint) para validar AC-07. |
| D1-5 | `LocaleDemoGrid` | **Eliminar** componente; lógica absorbida por `DashboardPage` + `DataGridDx`. |
| D1-6 | testids | `dataGridDx-main`, `dataGridDxToolbar-main`, `dataGridRowAction-edit` (patrón TR §6). |

### Impacto esperado

| Capa | Cambios |
|------|---------|
| DB | Ninguno |
| Backend | Ninguno |
| Frontend | Nuevo `shared/ui/grids/*`; `DashboardPage`; locales `grid.*`; deprecar `demoGrid.*` migrando claves |
| Tests | Vitest render smoke; E2E `grid-transversal.spec.ts`; actualizar `locale.spec.ts` si apunta a `localeDemoGrid` |
| Docs | Esta TR §3.3; HU estado al cerrar D |
| DevOps | Ninguno |

### Orden de trabajo

| Paso | Tarea | Archivos principales |
|------|-------|----------------------|
| 1 | Tipos y props `DataGridDx` | `dataGridDxTypes.ts` |
| 2 | Wrapper DX con defaults SPEC (paging, filterRow, groupPanel, summary, columnChooser) | `DataGridDx.tsx`, `dataGridDx.css` |
| 3 | Estados loading / empty / error | `useDataGridDxState.ts` o props + overlay/template DX |
| 4 | Helper columna acciones ícono + hint | función en `DataGridDx` o `buildRowActionsColumn.ts` |
| 5 | Toolbar contenedor + slots `toolbarStart`/`toolbarEnd` | `DataGridDx.tsx` |
| 6 | i18n `grid.*` (5 locales) | `frontend/src/locales/*.json` |
| 7 | Integrar dashboard | `DashboardPage.tsx` |
| 8 | Eliminar `LocaleDemoGrid` | borrar archivo; ajustar imports |
| 9 | Unit Vitest | `DataGridDx.test.tsx` (render + testids) |
| 10 | E2E smoke | `tests/e2e/grid-transversal.spec.ts` |
| 11 | Ajustar E2E existentes | `locale.spec.ts` → `dataGridDx-main` si aplica |
| 12 | Cierre TR | Estado Implementada; checklist §10 |

### Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Tests E2E frágiles con DOM interno DX | Assert por `data-testid` en contenedor y captions i18n, no clases DX |
| `summary` sin columnas numéricas en demo | Definir al menos una columna con `summaryType` en demo o summary vacío aceptable |
| Regla MONO § selección múltiple | **Fuera de alcance** esta TR (no está en AC HU); no agregar `selection` salvo HU futura |

### Tests a ejecutar

- `npm run test` (Vitest) — nuevo test `DataGridDx`
- `npm run test:e2e` — `grid-transversal.spec.ts` + regresión `locale.spec.ts`, `smoke.spec.ts`
- Manual: login → `/dashboard` → group panel visible, paginador DX, filtro en columna

### Dudas / bloqueos

- Ninguno (C1 cerrado; `pw_dashboard` confirmado en config).

### Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR:** **Sí** — no layouts, export, ABM, ni selección múltiple no pedida en HU.

---

## 4) Impacto en Datos

### Tablas afectadas
- Ninguna (frontend transversal).

### Seed mínimo para tests
- Reutilizar datos demo del dashboard (`LocaleDemoGrid` → `DataGridDx`) o fixture estático en E2E.

---

## 5) Contratos de API y OpenAPI

Este slice **no introduce endpoints**. Las grillas de negocio consumirán APIs definidas en SPEC-101.

- [ ] N/A — sin cambios OpenAPI en TR-GEN-03-grillas-listados.

---

## 6) Cambios Frontend

### Estructura sugerida

```text
frontend/src/shared/ui/grids/
  DataGridDx.tsx              # wrapper principal
  dataGridDx.css              # overrides mínimos tema
  types/
    dataGridDxTypes.ts        # props, RowAction, GridState
  hooks/
    useDataGridDxState.ts     # loading / error / empty helpers
```

### API del componente (borrador)

| Prop | Tipo | Obligatorio | Notas |
|------|------|-------------|-------|
| `proceso` | `string` | Sí | `procedimiento` menú |
| `gridId` | `string` | Sí | Id lógico en pantalla |
| `columns` | `Column[]` / config | Sí | Definición declarativa |
| `dataSource` | `array` \| `CustomStore` | Sí | |
| `rowActions` | `RowAction[]` | No | Íconos + permiso + `hint` i18n |
| `toolbarStart` / `toolbarEnd` | `ReactNode` | No | Slots para layouts/export/ABM (TR siguientes) |
| `isLoading` | `boolean` | No | Estado loading |
| `loadError` | `string` \| null | No | Estado error |
| `emptyMessageKey` | `string` | No | i18n vacío |

### Comportamiento DevExtreme (obligatorio en wrapper)

| Capacidad | Config DX |
|-----------|-----------|
| Paginación | `paging: { enabled: true }` + `pager: { visible: true, showPageSizeSelector: true }` (componentes DX; sin UI custom) |
| Orden | `sorting.mode = 'single'` o `multiple` según TR de proceso |
| Filtros | `filterRow.visible = true` |
| Columnas | `columnChooser.enabled`, `allowColumnReordering` |
| Agrupación | `groupPanel.visible = true` |
| Totalización | `summary` con fila de totales habilitada; totalizadores **por columna** (menú contextual en pie); ver [patron-i18n-grilla-devextreme](../../00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md) §5 |
| i18n grilla | Props explícitas + overrides `grid.dx.*` + `syncDevExtremeLocale`; menú encabezado y pie documentados en el patrón MONO §4–5 |
| Acciones fila | `Column type="buttons"` → `Button` con **`icon` + `hint`**; sin `text` / `stylingMode` que muestre caption |
| Alta ABM (otra TR) | `editing.allowAdding` + botón **+** en toolbar integrada del `DataGrid` — ver TR-GEN-03-patron-abm |

### Pantallas / componentes a tocar

- `frontend/src/shared/ui/grids/DataGridDx.tsx` (nuevo).
- `frontend/src/features/i18n/components/LocaleDemoGrid.tsx`: **reemplazar** por uso de `DataGridDx` o eliminar y usar solo en `DashboardPage`.
- `frontend/src/features/shell/pages/DashboardPage.tsx`: grilla de referencia con `proceso="pw_dashboard"`, `gridId="main"`.
- `frontend/src/locales/*.json`: claves `grid.*`, `demoGrid.*` (unificar prefijo `grid.`).

### data-testid sugeridos

- `dataGridDx-{gridId}` en contenedor (ej. `dataGridDx-main`).
- `dataGridDxToolbar-{gridId}`.
- `dataGridRowAction-{actionKey}` por acción de fila.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Crear `DataGridDx` con capacidades obligatorias SPEC | AC-02 a AC-06 |
| T2 | Frontend | Columna acciones íconos + tooltip i18n | AC-07, regla 28 |
| T3 | Frontend | Estados loading / empty / error | AC-04 |
| T4 | Frontend | Integrar en `DashboardPage` (referencia MVP) | AC-01, AC-11 |
| T5 | i18n | Claves `grid.*` en 5 locales | AC-10 |
| T6 | Tests | Vitest smoke render + E2E `grid-transversal.spec.ts` | AC-11 |

---

## 8) Estrategia de Tests

- **Unit:** render mínimo de `DataGridDx` con columnas y dataSource mock.
- **Integration:** N/A backend.
- **E2E:** login → dashboard → grilla visible, ordenar columna, ver group panel; opcional tooltip en acción mock.

---

## 9) Riesgos y Edge Cases

- Wrapper demasiado rígido para procesos SPEC-101 con necesidades distintas → exponer props de escape documentadas (`enableGrouping`, etc.) con default `true`.
- Scroll horizontal + columna acciones fija: validar `columnFixing` en DX si hace falta.
- i18n DevExtreme: seguir **[`patron-i18n-grilla-devextreme.md`](../../00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md)** (checklist QA, `loadMessages`, menús DX).

---

## 10) Verificación manual — correcciones i18n y ABM (2026-06-01)

Hallazgos en QA manual del dashboard; corrección documentada antes de cerrar el bloque GEN-03.

| # | Hallazgo | Causa / decisión | Corrección |
|---|----------|------------------|------------|
| 1 | Filtros (Contains, Starts with…) en inglés | `locale()` DX no aplicado al montar grilla o mensajes Fluent sin override | `FilterRow.operationDescriptions` + `grid.dx.filter.*` + `syncDevExtremeLocale` con overrides |
| 2 | Footer fijo «N registros» | TotalItem `count` fijo en wrapper | Eliminado; **clic derecho en celda de pie** → menú totalizadores por tipo de dato (`grid.summary.*`) |
| 3 | Panel agrupación en inglés | Igual que (1) | `GroupPanel.emptyPanelText` + `grid.dx.groupPanelEmpty` |
| 4 | No aparece botón **+** ABM | **Esperado:** dashboard es **grilla de consulta** sin `abm.enabled`; el **+** solo en pantallas ABM (ej. `/demo/abm`) | Texto i18n `grid.consulta.noAbmHint` en dashboard |
| 5 | Column Chooser en inglés / ítem vacío | Título DX sin override; columna acciones sin `caption` | `ColumnChooser.title` + `grid.column.actions` |
| 6 | Menú encabezado (Sort Ascending…) en inglés | `loadMessages({ es: esMessages })` anidaba mal el locale | `loadMessages(esMessages)` + claves `grid.dx.sort/group/column` |
| 7 | Pie parecía un solo totalizador | Sin líneas verticales entre columnas en la fila de totales | CSS `dataGridDx` + `showColumnLines`; lógica ya es por columna |

**Documentación normativa para futuros proyectos:** [`patron-i18n-grilla-devextreme.md`](../../00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md).

**QA manual (2026-06-01):** realizado — ítems **21–28** [TR-GEN-01-idioma](TR-GEN-01-idioma.md); menú encabezado, filtros, group panel, column chooser, totalizadores por columna en `es`; `/demo/abm` con botón **+**.

---

## 11) Verificación F formal (2026-06-01)

- **F1:** Aprobado con observaciones (ver [F-GEN-03-cierre-formal](F-GEN-03-cierre-formal.md))
- **Tests:** Vitest (incl. `DataGridDx.test.tsx` 4 casos); E2E `grid-transversal.spec.ts` 2 OK; `npm run build` OK
- **QA manual:** §10 validado

---

## 12) Checklist final

### Checklist del slice
- [x] AC-01 a AC-11 cumplidos
- [x] `DataGridDx` documentado en JSDoc (SPEC-001-03 + patrón i18n grilla)

### Checklist normas transversales
- [x] Sin endpoints → matriz sin cambios en este slice
- [x] Sin ampliación de alcance fuera de SPEC/HU

---

## Orden en bloque GEN-03

**1/4** — Base obligatoria antes de layouts, ABM y exportación.










