# HU-GEN-03-grillas-listados — Estándar de grillas y listados

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-03-grillas-listados |
| **SPEC origen** | [SPEC-001-03-ui-transversal.md](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001 — Generalidades / UI transversal |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 (cierre manual post-F) |
| **B1** | Enriquecida (2026-06-01) |
| **TR relacionada** | [TR-GEN-03-grillas-listados](../../04-tareas/001-Generalidades/TR-GEN-03-grillas-listados.md) |
| **Cierre F** | [F-GEN-03-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-03-cierre-formal.md) |
| **Dependencias** | HU-GEN-01-shell-layout; HU-GEN-02-login-sesion; HU-GEN-02-autorizacion-menu-api |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Checklist: grilla DX, paginación, orden | CA-01, CA-02 |
| Filtros y búsqueda | CA-03 |
| Estados loading / empty / error | CA-04 |
| Selector columnas, reorden | CA-05 |
| Agrupación y totalización **siempre** (decisión SPEC) | CA-06, RN-06 |
| Acciones por fila: íconos + tooltip i18n | CA-07, RN-03 |
| `data-testid` acciones principales | CA-08 |
| i18n (SPEC-001-01) | CA-09 |
| Tema activo (SPEC-001-01) | CA-10 |
| Identificación `proceso` + `grid_id` | RN-02 |
| Producto §17.8 (filtros, búsqueda, orden, paginación) | Contexto y CA |
| Pivots fuera de MVP | Fuera de alcance |

## Narrativa

Como **usuario operativo del portal**,  
quiero **trabajar con listados en grillas DevExtreme homogéneas**,  
para **filtrar, ordenar, agrupar y totalizar datos con la misma experiencia en todos los procesos**.

## Contexto funcional

SPEC-001-03 fija el estándar transversal del **DataGrid** para SistemaPartes MVP: capacidades obligatorias de columnas, filtros, paginación, barras de agrupación y totalización, estados de carga y acciones por fila como íconos con tooltip. Esta HU **no** cubre layouts guardados, exportación Excel ni flujo ABM (otras HU-GEN-03).

## Alcance incluido

- Grilla DevExtreme tabular en web.
- Paginación y orden por columna.
- Filtros y búsqueda según el proceso.
- Selector de columnas y reorden de columnas.
- **Barra superior de agrupación** y **barra inferior de totalización** habilitadas en todo listado transversal.
- Estados loading, vacío y error distinguibles.
- Columna de acciones por fila: íconos DevExtreme + `hint` i18n (sin texto visible en botones de fila).
- Claves `proceso` (`pq_menus.procedimiento`) y `grid_id` por instancia de grilla.

## Fuera de alcance

- Layouts persistentes (`HU-GEN-03-layouts-grilla`).
- Exportación Excel (`HU-GEN-03-exportaciones`).
- ABM modal y baja (`HU-GEN-03-patron-abm`).
- Pantallas de negocio (SPEC-101).
- PivotGrid (SPEC-001-08; solo referencia documental en MVP).

## Reglas de negocio

1. Toda pantalla tabular del estándar transversal usa DataGrid DevExtreme.
2. `proceso` y `grid_id` identifican la instancia para layouts y exportación en otras HU.
3. Acciones por fila: solo íconos con tooltip i18n; visibles con scroll horizontal.
4. Textos de columnas, tooltips y mensajes vía i18n activo.
5. Apariencia coherente con tema DevExtreme (SPEC-001-01).
6. **Barras de agrupación (superior) y totalización (inferior) presentes** en listados que adoptan este estándar; las funciones de total dependen del tipo de dato de cada columna.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Eximir agrupación/totalización en algún listado MVP | **No** — deben figurar siempre en el estándar transversal |

## Criterios de aceptación

- [x] **CA-01:** Listados MVP usan DataGrid con paginación.
- [x] **CA-02:** Orden por columna operativo.
- [x] **CA-03:** Filtros y búsqueda según definición del proceso.
- [x] **CA-04:** Estados loading, vacío y error claramente diferenciados.
- [x] **CA-05:** Usuario puede mostrar/ocultar y reordenar columnas.
- [x] **CA-06:** Barras de agrupación y totalización visibles y usables.
- [x] **CA-07:** Acciones por fila son íconos con tooltip; ocultas o deshabilitadas sin permiso.
- [x] **CA-08:** `data-testid` estables en toolbar y acciones de fila principales.
- [x] **CA-09:** Cambio de idioma actualiza captions visibles del listado (QA manual 2026-06-01).
- [x] **CA-10:** Grilla respeta tema activo sin colores fijos ajenos al tema.
- [x] **CA-11:** E2E smoke: listado MVP con datos tras login.

## Escenarios Gherkin

```gherkin
Feature: Grilla estándar transversal (SPEC-001-03)

  Scenario: Listado con datos y orden
    Given un usuario autenticado con permiso al proceso
    When abre un listado con grilla transversal
    Then ve columnas del proceso
    And puede ordenar por una columna
    And ve barra de agrupación y barra de totalización

  Scenario: Listado vacío
    Given un proceso sin registros para el filtro actual
    When abre el listado
    Then ve estado vacío identificable
    And no ve error genérico de sistema

  Scenario: Acción por fila con tooltip
    Given un usuario con permiso de modificación
    When enfoca el ícono de editar en una fila
    Then ve tooltip en el idioma activo

  Scenario: Error de carga
    Given falla la consulta del listado
    When la grilla intenta cargar datos
    Then ve estado de error distinguible del estado vacío
```

## Supuestos explícitos

- Definición de columnas y filtros por proceso: HU/TR SPEC-101.
- Regla Cursor `28-ui-grilla-acciones-iconos-tooltip.md` y estándar MONO `08-devextreme-grid-standards.md` aplican en TR.

## Preguntas abiertas

(Ninguna — cerradas en B1.)

## Riesgos de ambigüedad

- Componente compartido `DataGridDX` (o equivalente) debe centralizar agrupación/totalización para no omitirlas por pantalla.

## Veredicto B1

**Lista para TR:** Sí










