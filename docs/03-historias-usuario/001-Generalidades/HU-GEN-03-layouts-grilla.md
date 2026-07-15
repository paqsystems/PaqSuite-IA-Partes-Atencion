# HU-GEN-03-layouts-grilla — Layouts persistentes de grilla

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-03-layouts-grilla |
| **SPEC origen** | [SPEC-001-03-ui-transversal.md](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001 — Generalidades / UI transversal |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #2 y #3) |
| **B1** | Enriquecida (2026-06-01) |
| **TR relacionada** | [TR-GEN-03-layouts-grilla](../../04-tareas/001-Generalidades/TR-GEN-03-layouts-grilla.md) |
| **Dependencias** | HU-GEN-03-grillas-listados; HU-GEN-02-login-sesion |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| § Layouts: claves `proceso`, `grid_id`, `layout_name` | RN-01 |
| Operaciones Guardar / Guardar como / Cargar / Eliminar | CA-01 a CA-07 |
| Reglas creador vs lectores | RN-03, RN-04, CA-05 |
| Plantilla sistema → Guardar como | RN-05, Gherkin |
| Último layout al reabrir | CA-06 |
| Toolbar superior inmediata | CA-08, RN-07 |
| `pq_grid_layouts` | Supuesto TR |
| Unicidad nombre por proceso+grilla | RN-02, CA-09 |
| Sin límite de cantidad | RN-08 |
| Checklist SPEC ítem layouts | Conjunto de CA |
| Producto §17.8 condicional a infra | Supuesto degradación TR |

## Narrativa

Como **usuario del portal**,  
quiero **guardar y recuperar formatos personalizados de las grillas**,  
para **reutilizar columnas, filtros y orden sin reconfigurar cada vez y compartir formatos con mi equipo**.

## Contexto funcional

SPEC-001-03 normativiza layouts persistentes: estado de grilla (columnas, filtros, agrupaciones, orden, totalizadores, anchos, etc.) almacenado en **`pq_grid_layouts`**. Los layouts son visibles y aplicables por todos los usuarios del mismo `proceso` + `grid_id`; solo el **creador** puede actualizar o eliminar el suyo.

## Alcance incluido

- Controles **Guardar**, **Guardar como**, **Cargar** (selector) y **Eliminar** en toolbar superior de la grilla.
- API CRUD sobre `pq_grid_layouts` con filtro por `proceso`, `grid_id`, usuario creador y reglas del SPEC.
- Listado de layouts del proceso+grilla para aplicar.
- Restauración del **último layout usado** al entrar a la pantalla.
- Validación de **nombre único** por `proceso` + `grid_id`.
- Sin tope de cantidad de layouts por usuario en MVP.

## Fuera de alcance

- Exportación Excel (`HU-GEN-03-exportaciones`; usa layout activo).
- Layouts PivotGrid (SPEC-001-08).
- Shell / menú (SPEC-001-01).
- Tipologías de pantalla (`plantillas.md`).

## Reglas de negocio

1. Claves: `proceso` (`pq_menus.procedimiento`), `grid_id`, `layout_name`.
2. **`layout_name` único** dentro del par `proceso` + `grid_id` (un solo registro por nombre; el creador queda en el registro).
3. Cualquier usuario autenticado puede **cargar/aplicar** layouts del mismo `proceso` + `grid_id`.
4. Solo el **creador** puede **Guardar** (actualizar) o **Eliminar** ese registro.
5. Cualquier usuario puede **Guardar como** (nuevo nombre); si el nombre ya existe en proceso+grilla → error i18n (no sobrescribir ajeno).
6. Con **plantilla original del sistema** activa, **Guardar** equivale a **Guardar como** (no existe fila en `pq_grid_layouts` para sobrescribir).
7. Al montar la pantalla, aplicar **último layout usado** por el usuario si existe.
8. Controles en la **misma franja superior** que exportar y acciones globales.
9. **Sin límite** de layouts guardados por usuario en MVP.
10. Layouts **propios** (`isOwner`) se distinguen en el selector con sufijo visual **` (*)`** (`gridLayout.ownerMarker` i18n); no se persiste en `layout_name`.
11. **Plantilla del sistema** restaura la grilla original del proceso; cualquier cambio sobre ella hace que **Guardar** abra **Guardar como** (no altera la plantilla base).

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Límite de layouts por usuario | **No** |
| Unicidad de `layout_name` | **Único por `proceso` + `grid_id`** (global en esa grilla) |

## Criterios de aceptación

- [x] **CA-01:** Guardar como crea layout nuevo con nombre no duplicado en proceso+grilla.
- [x] **CA-02:** Guardar actualiza solo layout propio seleccionado.
- [x] **CA-03:** Cargar aplica columnas/filtros/orden/totalizadores guardados.
- [x] **CA-04:** Guardar como desde layout ajeno crea registro propio sin modificar el original.
- [x] **CA-05:** Usuario no creador no puede Guardar ni Eliminar layout ajeno.
- [x] **CA-06:** Al reingresar, se restaura último layout usado si existe.
- [x] **CA-07:** Eliminar solo disponible para el creador del layout.
- [x] **CA-08:** Controles en toolbar superior; i18n; `data-testid` en acciones de layout.
- [x] **CA-09:** Intento de Guardar como con nombre existente en proceso+grilla → error claro, sin pérdida de vista actual.
- [x] **CA-10:** Con plantilla sistema, Guardar solicita nombre (Guardar como).
- [x] **CA-11:** E2E: guardar layout → salir → volver → formato restaurado (proceso referencia MVP).
- [x] **CA-12:** Layouts propios visibles con sufijo ` (*)` en el selector.
- [x] **CA-13:** Al elegir plantilla del sistema, la grilla vuelve al formato por defecto del proceso.

## Escenarios Gherkin

```gherkin
Feature: Layouts persistentes de grilla (SPEC-001-03)

  Scenario: Guardar como con nombre único
    Given proceso "PED_ING" y grid_id "main"
    When el usuario guarda como "Mi vista ventas"
    Then existe un layout con ese nombre para PED_ING/main
    And el creador es ese usuario

  Scenario: Nombre duplicado en la misma grilla
    Given ya existe layout "Supervisor" en proceso "PED_ING" y grid_id "main"
    When otro usuario intenta Guardar como "Supervisor"
    Then la operación falla con mensaje i18n
    And no se sobrescribe el layout existente

  Scenario: Aplicar layout ajeno sin modificarlo
    Given layout "Supervisor" creado por usuario A
    When usuario B carga "Supervisor"
    Then la grilla muestra el formato guardado
    And Guardar no actualiza el registro de A

  Scenario: Plantilla sistema
    Given la grilla en plantilla original del sistema
    When el usuario pulsa Guardar
    Then se solicita nombre para Guardar como
```

## Supuestos explícitos

- Migración y API `pq_grid_layouts`: TR (contrato envelope, 401/403).
- Estado serializado: capacidades DevExtreme DataGrid (detalle TR).
- Sin infra en un slice: degradar ocultando/deshabilitando controles sin bloquear listado (TR, producto §17.8).

## Preguntas abiertas

(Ninguna — cerradas en B1.)

## Riesgos de ambigüedad

- Tamaño del payload JSON de estado en grillas muy filtradas.
- Diferenciar “plantilla sistema” del estado DX por defecto: definir flag o ausencia de `layout_id` activo en TR.

## Veredicto B1

**Lista para TR:** Sí

---

## CC PQ #3 (09/06/2026) — Totalizadores del pie en layouts

### Reglas de negocio (ampliadas)

12. El JSON de estado persistido en `pq_grid_layouts` debe incluir **totalizadores del pie** (`Summary` DevExtreme) coherentes con lo visible al guardar.
13. Al **cargar** un layout, deben restaurarse los totalizadores definidos en ese layout.

### Criterios de aceptación CC #3

- [x] **CA-CC3-01:** Totalizadores en pie → Guardar → recargar → Cargar layout → totalizadores restaurados.
- [x] **CA-CC3-02:** Guardar como conserva totalizadores del estado actual.
- [x] **CA-CC3-03:** Plantilla del sistema no alterada (coherente con RN-05 / CC #2).
- [x] **CA-CC3-04:** Regla en `08-devextreme-grid-standards.md` §1.11.










