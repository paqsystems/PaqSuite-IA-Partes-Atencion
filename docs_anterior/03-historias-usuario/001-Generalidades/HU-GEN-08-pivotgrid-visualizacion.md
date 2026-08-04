# HU-GEN-08-pivotgrid-visualizacion — PivotGrid DevExtreme y visualización analítica

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-08-pivotgrid-visualizacion |
| **SPEC origen** | [SPEC-001-08-pivots.md](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **MONO** | [frontend-pivotgrid-devextreme-agregaciones-y-menu.md](../../00-contexto/_mono/pivots/frontend-pivotgrid-devextreme-agregaciones-y-menu.md); [patron-i18n-pivot-devextreme.md](../../00-contexto/_mono/pivots/patron-i18n-pivot-devextreme.md) |
| **Épica** | 001 — Generalidades / Pivots |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **TR relacionada** | [TR-GEN-08-pivotgrid-visualizacion](../../04-tareas/001-Generalidades/TR-GEN-08-pivotgrid-visualizacion.md) |
| **Última actualización** | 2026-06-11 (decisión AMB-Q-P08-01; RN-01 convivencia informe) |
| **Dependencias** | HU-GEN-08-motor-metadata-pivots; HU-GEN-03-grillas-listados |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Reglas frontend base pivotgrid | CA conjunto |
| Agregaciones y menú contextual | RN-01–RN-03, CA-02–CA-04 |
| Alternancia grilla / pivot | RN-04, CA-01 |
| Primera apertura → pivot base (AMB-P08-06) | RN-05, CA-05 |
| i18n PivotGrid 5 idiomas (AMB-P08-05) | RN-06, CA-07 |
| Drill-down según consulta | RN-07, CA-06 |
| Paridad visual con proceso base | RN-08 |

## Narrativa

Como **usuario analítico del portal**,  
quiero **visualizar y reorganizar datos en un PivotGrid DevExtreme con agregaciones y filtros**,  
para **analizar tendencias y concentraciones sin salir del informe**.

## Contexto funcional

Tras cargar metadata del motor, el bloque transversal `PivotGridBlock` renderiza DevExtreme `PivotGrid` + `FieldPanel` + `FieldChooser`. El usuario arrastra campos a filas, columnas, valores y filtros; cambia agregación por menú contextual; alterna con grilla de detalle cuando el ítem de menú es de tipo **informe** o la metadata de la consulta lo habilita explícitamente. La **primera apertura** aplica `pivotBase` de metadata (consulta útil) salvo último diseño guardado; la **plantilla inicial** (selector, otra HU) restablece pivot vacía bajo demanda.

## Alcance incluido

- Componente transversal `PivotGridBlock` (wrapper DevExtreme).
- Alternancia **grilla / pivot** cuando `tipo_proceso` del menú es **informe** o `mostrarGrillaYPivot = true` en metadata de la consulta; vista inicial **grilla** (norma conceptual §3).
- `PivotGridDataSource` con campos desde metadata API.
- Menú contextual de agregación (`onContextMenuPreparing`): Sumar, Promediar, Mínimo, Máximo, Contar según `tipoDato` / catálogo.
- Reconciliación `dataType` (`onFieldsPrepared`) sin pisar `summaryType` elegido por usuario.
- Field panel y Field Chooser con búsqueda.
- Subtotales y totales generales según configuración y tipo de dato.
- Formato numérico/moneda/fecha según metadata de campo.
- Drill-down al detalle cuando `admite_drilldown = 1` y campo `drillable`.
- i18n completo: `pivot.*`, `pivot.dx.*`; `syncDevExtremeLocale`; remount con `key` al cambiar idioma.
- `data-testid` con prefijo configurable (`testIdPrefix`).

## Fuera de alcance

- Diseños guardados y toolbar layouts (`HU-GEN-08-layouts-pivot`).
- Exportación Excel (`HU-GEN-08-exportacion-pivot`).
- Resolución metadata y validaciones backend (`HU-GEN-08-motor-metadata-pivots`).
- MVP portal SistemaPartes.

## Reglas de negocio

1. Grilla de detalle y pivot **conviven con alternancia** cuando el ítem de menú tiene `tipo_proceso` de **informe** (opciones legacy en `pq_menus`) **o** cuando el diseño de la consulta lo declara explícitamente (`mostrarGrillaYPivot = true` en metadata). Fuera de esos casos, aplica solo el modo definido por la consulta (grilla o pivot según metadata).
2. Vista inicial al abrir consulta con convivencia: **siempre grilla**; usuario cambia a pivot si tiene permiso.
3. Agregación es **por campo de valor**, vía menú contextual (no selector global único).
4. `summaryType` default DevExtreme es `count`; metadata debe fijar agregación explícita en campos métrica.
5. Primera apertura pivot (sin último diseño): aplicar **`pivotBase`** de metadata (AMB-P08-06).
6. Plantilla inicial (`configId: null`) → pivot vacía; ver HU layouts.
7. Textos visibles solo vía i18n activo (5 idiomas).
8. Tema DevExtreme coherente con SPEC-001-01.
9. Drill-down solo si `admite_drilldown = 1` en la consulta y el campo es `drillable` (AMB-Q-P08-01 cerrada).
10. Totalizadores compatibles con tipo: numéricos (suma, promedio, máx, mín, contar); string (contar, máx, mín); fecha (máx, mín, contar).

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Primera apertura sin último diseño | **pivotBase** de metadata (AMB-P08-06) |
| Plantilla inicial | Pivot vacía solo al elegirla en selector |
| i18n DevExtreme | Patrón obligatorio 5 idiomas (`patron-i18n-pivot-devextreme.md`) |
| Vista inicial informe | **Grilla** (norma conceptual) |
| Convivencia grilla/pivot | `tipo_proceso` **informe** en menú **o** `mostrarGrillaYPivot` explícito en metadata |
| Drill-down (AMB-Q-P08-01) | Solo si `admite_drilldown = 1` por consulta; no obligatorio en todas |
| MVP portal | **Fuera** |

## Criterios de aceptación

- [ ] **CA-01:** Consulta con `tipo_proceso` informe o `mostrarGrillaYPivot = true` muestra toggle grilla/pivot; inicia en grilla.
- [ ] **CA-02:** Usuario puede arrastrar campos a filas, columnas, valores y filtros en field panel.
- [ ] **CA-03:** Clic derecho en campo de valores ofrece agregaciones permitidas por tipo.
- [ ] **CA-04:** Cambio de agregación actualiza celdas sin perder otros campos configurados.
- [ ] **CA-05:** Primera apertura pivot sin diseño previo muestra `pivotBase` de metadata.
- [ ] **CA-06:** Con `admite_drilldown`, acción drill-down navega al detalle según reglas de consulta.
- [ ] **CA-07:** Cambio de idioma actualiza captions del PivotGrid y field panel.
- [ ] **CA-08:** Subtotales y totales visibles según flags de configuración.
- [ ] **CA-09:** `data-testid` estables en bloque pivot (prefijo configurable).

## Escenarios Gherkin

```gherkin
Feature: Visualización PivotGrid DevExtreme

  Scenario: Alternar de grilla a pivot en proceso informe
    Given un ítem de menú con tipo_proceso informe
    And la consulta tiene pivot_habilitado = 1
    When el usuario abre la pantalla
    Then ve la grilla de detalle
    When cambia a vista pivot
    Then ve el PivotGrid con pivotBase aplicada

  Scenario: Convivencia por diseño explícito
    Given un proceso no informe con mostrarGrillaYPivot = true en metadata
    When el usuario abre la consulta
    Then puede alternar entre grilla y pivot

  Scenario: Sin convivencia fuera de informe ni diseño explícito
    Given un proceso operativo sin mostrarGrillaYPivot
    When el usuario abre la pantalla
    Then no ve toggle de alternancia grilla/pivot

  Scenario: Cambiar agregación por menú contextual
    Given un campo ImporteNeto en el área Valores
    When el usuario abre menú contextual y elige Promediar
    Then las celdas muestran promedio
    And el summaryType del campo queda en promedio

  Scenario: i18n al cambiar idioma
    Given un pivot visible en español
    When el usuario cambia idioma a inglés
    Then los textos del field panel y encabezados pivot están en inglés

  Scenario: Sin drill-down
    Given una consulta con admite_drilldown = 0
    When el usuario interactúa con celdas del pivot
    Then no se ofrece navegación al detalle
```

## Supuestos explícitos

- Referencia implementación: `PivotGridBlock.tsx`, `pivotApi.ts` (otro repo MONO).
- Metadata API provista por `HU-GEN-08-motor-metadata-pivots`.
- Informe piloto puede vivir en módulo Informes (TR dedicada).

## Preguntas abiertas

(Ninguna — AMB-Q-P08-01 cerrada en decisiones.)

## Veredicto B1

**Lista para TR** — epic posterior al MVP portal.

## Veredicto D1 (2026-06-11)

**Pendiente** — ver [TR-GEN-08-pivotgrid-visualizacion](../../04-tareas/001-Generalidades/TR-GEN-08-pivotgrid-visualizacion.md) y [F-GEN-08-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-08-cierre-formal.md).










