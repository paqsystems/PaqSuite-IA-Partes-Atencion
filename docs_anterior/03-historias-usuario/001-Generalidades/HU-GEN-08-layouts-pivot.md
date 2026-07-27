# HU-GEN-08-layouts-pivot — Diseños guardados y toolbar del pivot

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-08-layouts-pivot |
| **SPEC origen** | [SPEC-001-08-pivots.md](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **MONO** | [especificacion_tecnica_consultas_pivotables.md](../../00-contexto/_mono/pivots/especificacion_tecnica_consultas_pivotables.md) §17–17.1; [frontend-pivotgrid-devextreme-agregaciones-y-menu.md](../../00-contexto/_mono/pivots/frontend-pivotgrid-devextreme-agregaciones-y-menu.md) §6–7 |
| **Épica** | 001 — Generalidades / Pivots |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **TR relacionada** | [TR-GEN-08-layouts-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-layouts-pivot.md) |
| **Última actualización** | 2026-06-11 |
| **Dependencias** | HU-GEN-08-pivotgrid-visualizacion; HU-GEN-03-layouts-grilla (paridad funcional) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Paridad GEN-03: diseños guardados | RN conjunto |
| AMB-P08-01 sufijo ` (*)` propios | RN-01, CA-02 |
| AMB-P08-02 plantilla inicial vacía | RN-02, CA-03 |
| AMB-P08-03 Guardar = Guardar como desde plantilla | RN-03, CA-04 |
| AMB-P08-04 ícono Actualizar | RN-04, CA-05 |
| AMB-P08-07 unicidad nombre por consulta | RN-05, CA-06 |
| Restaurar último diseño | RN-06, CA-07 |
| Visibilidad global / solo creador modifica | RN-07–RN-09 |

## Narrativa

Como **usuario que analiza datos en pivot**,  
quiero **guardar, cargar y compartir diseños de pivot con mi equipo**,  
para **reutilizar estructuras analíticas sin reconfigurar campos cada vez**.

## Contexto funcional

Paridad funcional con `HU-GEN-03-layouts-grilla` adaptada a `pq_pivots_config`. El toolbar del bloque pivot incluye: **Actualizar**, selector de diseños, **Guardar**, **Guardar como**, **Eliminar**. Los diseños son visibles para todos los usuarios autorizados a la consulta; solo el **creador** actualiza o elimina. La opción **Plantilla inicial** (`configId: null`) resetea pivot vacía sin tocar `pivotBase` de metadata ni diseños ajenos.

## Alcance incluido

- Toolbar superior del bloque pivot (misma franja que exportación).
- Selector de diseños (`pivotLayoutSelect`) con listado desde `pq_pivots_config` por `consulta_id`.
- Opción fija **Plantilla inicial** (`configId: null`, i18n `pivotLayout.initialTemplate`).
- Operaciones **Guardar** (PUT), **Guardar como** (POST), **Cargar**, **Eliminar** (borrado lógico).
- Sufijo visual **` (*)`** en diseños propios (`isOwner`, i18n `pivotLayout.ownerMarker`); no persiste en `nombre`.
- Con plantilla inicial activa: **Guardar** abre flujo **Guardar como**.
- Restauración **último diseño usado** al montar (`pq_pivots_config_last_used` o equivalente).
- Ícono **Actualizar** (`pivot.refresh`, `data-testid="pivotRefresh"`): re-fetch servidor con filtros vigentes.
- Orden toolbar: `[actualizar] → [diseños] → [export] → [extras]`.
- Validación nombre único por `consulta_id`; duplicado → i18n `pivotLayout.duplicateName`.
- Persistencia JSON: disposición campos, filtros internos, agregaciones, subtotales.

## Fuera de alcance

- Metadata y catálogos (`HU-GEN-08-motor-metadata-pivots`).
- Renderizado agregaciones / field panel (`HU-GEN-08-pivotgrid-visualizacion`).
- Exportación Excel (`HU-GEN-08-exportacion-pivot`).
- Layouts de grilla tabular (`HU-GEN-03-layouts-grilla`).
- MVP portal SistemaPartes.

## Reglas de negocio

1. Claves: `consulta_id`, `nombre` (único por consulta), `configuracion_json`.
2. **`nombre` único** por `consulta_id`; duplicado en Guardar como → error i18n, sin pérdida de vista.
3. Todos los diseños activos visibles y aplicables por usuarios con permiso a la consulta.
4. Solo **creador** puede **Guardar** (actualizar) o **Eliminar**.
5. Cualquier usuario puede **Guardar como** (nuevo registro propio).
6. **Plantilla inicial** (`configId: null`): pivot vacía (sin campos en áreas); **Guardar** = **Guardar como**.
7. **Guardar** con `configId` válido y `isOwner`: PUT del JSON actual.
8. Al montar, si `restaurarUltimoDiseno` y existe último usado → cargarlo; si no → `pivotBase` (HU visualización).
9. **Actualizar** re-ejecuta carga de datos API, no solo `dataSource.reload()` local.
10. Diseños propios muestran **` (*)`** en selector; sufijo no se guarda en BD.
11. **Sin límite** de diseños guardados por `consulta_id` (AMB-Q-P08-02 cerrada; paridad layouts grilla).

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Sufijo propios | ` (*)` vía `pivotLayout.ownerMarker` (AMB-P08-01) |
| Plantilla inicial | `configId: null` → pivot vacía (AMB-P08-02) |
| Guardar desde plantilla | Siempre Guardar como (AMB-P08-03) |
| Actualizar | Re-fetch servidor (AMB-P08-04) |
| Unicidad nombre | Por `consulta_id` (AMB-P08-07) |
| Límite diseños (AMB-Q-P08-02) | **Cerrada:** sin límite de diseños por `consulta_id` |
| MVP portal | **Fuera** |

## Criterios de aceptación

- [ ] **CA-01:** Toolbar muestra selector, Guardar, Guardar como, Eliminar según flags `persistencia` de metadata.
- [ ] **CA-02:** Diseños propios listados con sufijo ` (*)`; ajenos sin sufijo.
- [ ] **CA-03:** Plantilla inicial resetea pivot vacía (field panel sin campos asignados).
- [ ] **CA-04:** Con plantilla inicial, Guardar abre diálogo Guardar como (POST).
- [ ] **CA-05:** Actualizar re-obtiene dataset del servidor; `data-testid="pivotRefresh"`.
- [ ] **CA-06:** Guardar como con nombre duplicado en consulta → error i18n sin alterar vista.
- [ ] **CA-07:** Al reingresar, restaura último diseño usado si existe.
- [ ] **CA-08:** Usuario no creador no puede Guardar ni Eliminar diseño ajeno.
- [ ] **CA-09:** `data-testid`: `pivotLayoutSelect`, `pivotLayoutSave`, `pivotLayoutSaveAs`, `pivotLayoutDelete`, `pivotLayoutSaveAsDialog`.

## Escenarios Gherkin

```gherkin
Feature: Diseños guardados de pivot

  Scenario: Guardar diseño propio
    Given un usuario con diseño pivot propio cargado
    When modifica campos y pulsa Guardar
    Then se actualiza pq_pivots_config sin crear registro nuevo

  Scenario: Guardar como desde plantilla inicial
    Given plantilla inicial activa con campos asignados
    When pulsa Guardar
    Then se abre diálogo Guardar como
    And al confirmar nombre único se crea registro en pq_pivots_config

  Scenario: Diseño ajeno solo lectura para modificar
    Given un diseño creado por otro usuario
    When el usuario actual intenta Guardar o Eliminar
    Then las acciones están deshabilitadas o rechazadas

  Scenario: Actualizar datos
    Given un pivot con filtros generales aplicados
    When pulsa Actualizar
    Then se vuelve a consultar el dataset al servidor
    And el pivot refleja datos actualizados
```

## Supuestos explícitos

- Paridad implementación: `GridLayoutToolbar` / `useGridLayouts` adaptados a pivot.
- Tabla `pq_pivots_config` según `modelo_datos_pivots_y_catalogo.md`.
- DDL `pq_pivots_config_last_used` en TR (AMB-M-P08-02).

## Preguntas abiertas

(Ninguna — AMB-Q-P08-02 cerrada en decisiones.)

## Veredicto B1

**Lista para TR** — epic posterior al MVP portal. Resolver AMB-M-P08-02 (DDL último diseño) en TR.

## Veredicto D1 (2026-06-11)

**Pendiente** — ver [TR-GEN-08-layouts-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-layouts-pivot.md) y [F-GEN-08-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-08-cierre-formal.md).










