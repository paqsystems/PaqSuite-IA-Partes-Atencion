# TR-GEN-08-pivotgrid-visualizacion — PivotGrid DevExtreme y visualización analítica

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-08-pivotgrid-visualizacion](../../03-historias-usuario/001-Generalidades/HU-GEN-08-pivotgrid-visualizacion.md) |
| **SPEC relacionada** | [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Could |
| **Dependencias** | TR-GEN-08-motor-metadata-pivots; TR-GEN-03-grillas-listados |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-11 (D1 pivotgrid) |

**Origen:** [HU-GEN-08-pivotgrid-visualizacion](../../03-historias-usuario/001-Generalidades/HU-GEN-08-pivotgrid-visualizacion.md)  
**Referencia SPEC:** [SPEC-001-08-pivots](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md)  
**MONO:** [frontend-pivotgrid-devextreme-agregaciones-y-menu.md](../../00-contexto/_mono/pivots/frontend-pivotgrid-devextreme-agregaciones-y-menu.md), [patron-i18n-pivot-devextreme.md](../../00-contexto/_mono/pivots/patron-i18n-pivot-devextreme.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Bloque transversal PivotGrid DevExtreme con alternancia grilla/pivot, agregaciones e i18n.

### In scope / Out of scope
- **In scope:** `PivotGridBlock`, toggle grilla/pivot, `PivotGridDataSource`, menú contextual agregaciones, field panel/chooser, drill-down condicional, i18n 5 idiomas, integración en página consulta piloto.
- **Out of scope:** toolbar layouts (`TR-GEN-08-layouts-pivot`), export (`TR-GEN-08-exportacion-pivot`), API metadata (TR anterior).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Toggle grilla/pivot visible si `tipoProceso` menú = **informe** (legacy `pq_menus.tipo_proceso`, comparación case-insensitive) **o** `mostrarGrillaYPivot = true` en metadata; vista inicial **grilla**.
- **AC-02**: Field panel permite arrastrar campos a filas, columnas, valores y filtros.
- **AC-03**: Menú contextual en área valores ofrece agregaciones según `tipoDato` / catálogo.
- **AC-04**: Cambio agregación actualiza celdas sin perder otros campos.
- **AC-05**: Primera apertura pivot (sin diseño previo) aplica `pivotBase` de metadata.
- **AC-06**: Drill-down solo si `admiteDrilldown = true` y campo `drillable`.
- **AC-07**: Cambio idioma remonta PivotGrid con textos actualizados (`patron-i18n-pivot-devextreme.md`).
- **AC-08**: Subtotales y totales según flags metadata.
- **AC-09**: `data-testid` con `testIdPrefix` configurable.
- **AC-10**: E2E: informe piloto alterna grilla → pivot y ve datos agregados.

### Escenarios Gherkin

(Heredados de HU — incluyen informe, diseño explícito, sin convivencia, drill-down.)

---

## 3) Reglas de Negocio

1. **RN-01**: Convivencia grilla/pivot si `tipo_proceso` **informe** en menú **o** `mostrarGrillaYPivot` explícito (AMB-P08-08).
2. **RN-02**: Vista inicial siempre **grilla** cuando hay convivencia.
3. **RN-03**: Agregación por campo vía `onContextMenuPreparing` (no selector global).
4. **RN-04**: `onFieldsPrepared` reconcilia `dataType` sin pisar `summaryType` usuario.
5. **RN-05**: `fieldChooser.onContextMenuPreparing` anidado en props `PivotGrid` (tipos DX).
6. **RN-06**: Primera apertura: `pivotBase` salvo último diseño (TR layouts).
7. **RN-07**: Drill-down según `admite_drilldown` (AMB-Q-P08-01 cerrada).
8. **RN-08**: Totalizadores por tipo de dato (norma conceptual § Totalizadores).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-11)

**Fuentes revisadas:** HU-GEN-08-pivotgrid-visualizacion, SPEC-001-08 § AMB-P08-08, `frontend-pivotgrid-devextreme-agregaciones-y-menu.md`, `patron-i18n-pivot-devextreme.md`, TR-GEN-08-motor-metadata-pivots §3.2, TR-GEN-03-grillas-listados, `DataGridDx`, seed menú MVP (`tipoProceso`).

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (depende de TR-GEN-08-motor-metadata-pivots en runtime)

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Qué hacer (D1 / código) |
|----|------|--------|--------|-------------------------|
| AMB-C01 | **`tipo_proceso` informe** | Seed MVP usa `P` en ítems operativos; HU exige literal **`informe`** | **Cerrado** (R-C1-01) | Gate: `tipoProceso` normalizado === **`informe`** (case-insensitive) **o** `mostrarGrillaYPivot`. Al activar epic: actualizar seed ítems Informes pivotables a `tipo_proceso = 'informe'` (o alias documentado en seed). |
| AMB-C02 | **Integración `DataGridDx`** | Romper grillas MVP sin convivencia | **Cerrado** (R-C1-02) | Props opcionales `consultaId`, `pivotConsultaEnabled`; toggle solo si gate true **y** `pivotsEnabled` config. |
| AMB-C03 | **Drill-down navegación** | UX inconsistente entre consultas | **Cerrado** (R-C1-03) | MVP: volver a **grilla** en misma página con filtros de celda aplicados; sin ruta nueva en v1. |
| AMB-C04 | **Orden carga: metadata vs diseño** | Parpadeo pivotBase vs último diseño | **Cerrado** (R-C1-04) | Montar pivot tras `GET metadata` + `GET pivot-configs/active` (TR layouts); si `configId` null → `pivotBase`. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M01 | `resolvePivotDataFieldIndex` | Portar helper MONO. |
| AMB-M02 | Remount locale | `key` incluye `locale` + `consultaId`. |
| AMB-M03 | `pivotsEnabled` | `GET /config/public` → `pivotsEnabled: boolean` default `false` hasta activar epic; oculta todo bloque pivot. |
| AMB-M04 | `data-testid` toggle | Estable: `pivotViewToggle` además de prefijo configurable. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU RN-01 informe vs seed `tipoProceso: 'P'` | C1 cierra: epic pivots ajusta seed Informes a `informe` o página pasa `mostrarGrillaYPivot` en metadata. |
| Primera apertura pivotBase vs último diseño (TR layouts) | TR layouts gana si `restaurarUltimoDiseno` y hay active; si no → `pivotBase`. |
| Solo pivot sin grilla | Si no hay convivencia pero `pivotHabilitado`, mostrar solo pivot (sin toggle). |

### Supuestos detectados

- `TR-GEN-08-motor-metadata-pivots` entrega metadata estable antes de montar UI.
- DevExtreme PivotGrid licenciado (`VITE_DEVEXTREME_LICENSE`).
- Página piloto: primera integración en consulta Informes acordada en seed.

### Preguntas para decisión humana

(Ninguna bloqueante — AMB-Q-P08-01 cerrada en HU.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-11)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Convivencia grilla/pivot | `tipoProceso === 'informe'` **o** `mostrarGrillaYPivot`; vista inicial **grilla**. |
| R-C1-02 | Integración | Props opcionales en `DataGridDx`; `PivotGridBlock` separado montado por página consulta. |
| R-C1-03 | Drill-down MVP | Filtros celda → grilla detalle misma página; solo si `admiteDrilldown`. |
| R-C1-04 | Secuencia montaje | metadata → active design → render pivot. |
| R-C1-05 | Flag infra | `pivotsEnabled` en `config/public`; default **false** hasta deploy epic. |
| R-C1-06 | Solo pivot | Sin toggle si no hay convivencia; pantalla pivot directa si metadata lo indica. |

---

## 4) Impacto en Datos

Sin tablas nuevas. Consume API de **TR-GEN-08-motor-metadata-pivots**.

---

## 5) Contratos de API y OpenAPI

Reutiliza endpoints de TR-GEN-08-motor-metadata-pivots. Sin endpoints nuevos en este slice.

---

## 6) Cambios Frontend

### Componentes

```text
frontend/src/shared/components/PivotGridBlock.tsx
frontend/src/shared/services/pivotApi.ts          # tipos campo guardado / runtime
frontend/src/shared/hooks/usePivotDataSource.ts
frontend/src/shared/utils/resolvePivotDataFieldIndex.ts
```

### Integración

- Página piloto (ej. consulta Informes) monta `DataGridDx` + `PivotGridBlock` con `consultaId`.
- Props: `consultaId`, `testIdPrefix`, `tipoProceso` (desde menú/ruta), `onRefreshRequest`.

### i18n

- Claves `pivot.*`, `pivot.dx.*` en 5 locales.
- `syncDevExtremeLocale` con overrides `dxPivotGrid-*` sin doble anidación (`patron-i18n-pivot-devextreme.md`).

### data-testid

| Control | id |
|---------|-----|
| Toggle grilla/pivot | `{prefix}.viewToggle` |
| PivotGrid root | `{prefix}.pivotGrid` |

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | `PivotGridBlock` + DataSource | AC-02–AC-04 |
| T2 | Frontend | Menú agregación contextual | Paridad MONO §3.1 |
| T3 | Frontend | Toggle grilla/pivot + gate informe | AC-01 |
| T4 | Frontend | i18n pivot 5 idiomas | AC-07 |
| T5 | Frontend | Drill-down MVP | AC-06 |
| T6 | Frontend | Página piloto Informes | E2E AC-10 |
| T7 | Tests | E2E Playwright alternancia | AC-10 |

---

## 8) Estrategia de Tests

- **Unit:** `resolvePivotDataFieldIndex`, mapeo metadata → fields DX.
- **E2E:** informe piloto grilla → pivot → agregación contextual.

---

## 9) Riesgos y Edge Cases

- `summaryType` default `count` de DX si metadata no fija agregación en métricas.
- Proceso operativo sin `mostrarGrillaYPivot` no debe mostrar toggle (regresión).
- Dataset grande: loading state en pivot mientras POST data.

---

## 10) Checklist final

- [x] AC cumplidos (toggle, pivotBase, agregación contextual, drill-down MVP, i18n)
- [x] i18n 5 idiomas (`pivot.*`, `pivot.dx.*` en locales es/en/pt/fr/it)
- [x] DevExtreme obligatorio (ButtonGroup, PivotGrid, FieldPanel, FieldChooser)

---

## Archivos creados/modificados (D1)

### Frontend
- `frontend/src/shared/pivot/components/PivotGridBlock.tsx`
- `frontend/src/shared/pivot/components/ConsultaGrillaPivotShell.tsx`
- `frontend/src/shared/pivot/components/PivotViewToggle.tsx`
- `frontend/src/shared/pivot/hooks/usePivotDataSource.ts`
- `frontend/src/shared/pivot/hooks/usePivotDevExtremeTexts.ts`
- `frontend/src/shared/pivot/services/pivotApi.ts`
- `frontend/src/shared/pivot/utils/*` (coexistencia, pivotBase, agregaciones)
- `frontend/src/features/config/api/publicConfigApi.ts`
- `frontend/src/features/i18n/pivotDevExtremeMessages.ts`
- `frontend/src/features/consultas/pages/HistorialVentasPage.tsx` (piloto)
- `frontend/tests/unit/applyPivotBaseToFields.test.ts`
- `frontend/tests/unit/resolvePivotCoexistence.test.ts`
- `frontend/tests/unit/resolvePivotDataFieldIndex.test.ts`
- `frontend/tests/e2e/pivot-historial.spec.ts`

### Backend (seed menú)
- `backend/config/paqsuite_mvp.php` — `historialVentas.tipoProceso = informe`










