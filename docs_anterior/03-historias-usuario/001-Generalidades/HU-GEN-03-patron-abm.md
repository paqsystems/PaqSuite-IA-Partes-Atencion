# HU-GEN-03-patron-abm — Patrón ABM transversal sobre grilla

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-03-patron-abm |
| **SPEC origen** | [SPEC-001-03-ui-transversal.md](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001 — Generalidades / UI transversal |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 (cierre manual post-F) |
| **B1** | Enriquecida (2026-06-01) |
| **TR relacionada** | [TR-GEN-03-patron-abm](../../04-tareas/001-Generalidades/TR-GEN-03-patron-abm.md) |
| **Última actualización** | 2026-06-01 |
| **Dependencias** | HU-GEN-03-grillas-listados; HU-GEN-02-modelo-roles-permisos-seed |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Patrón ABM por defecto | Narrativa, RN-01 |
| Permisos `Permiso_Alta/Modi/Baja/Repo` | CA-01, CA-02 |
| Modal ABM (regla Cursor 24) | RN-05, CA-03 |
| Botón Agregar en grilla ABM | CA-04 |
| Baja con confirmación | CA-05, CA-06 |
| Acciones fila íconos + tooltip | CA-07 |
| Checklist SPEC acciones ABM | CA-01 a CA-07 |
| Decisión: siempre modal salvo HU negocio | RN-06, Decisiones cerradas |

## Narrativa

Como **usuario con permisos de mantenimiento**,  
quiero **dar de alta, editar y eliminar registros desde el mismo listado en un modal**,  
para **no perder contexto de búsqueda, orden ni layout al operar el ABM**.

## Contexto funcional

SPEC-001-03 adopta ABM transversal: **grilla → modal** (alta/edición) → vuelta al listado; baja con confirmación. Es el patrón obligatorio del bloque GEN-03; formularios en ruta dedicada solo si una HU de negocio (SPEC-101) lo declara explícitamente.

## Alcance incluido

- Botón **+** nativo del `DataGrid` DevExtreme para alta (`Permiso_Alta`; no botón Agregar externo).
- Alta y edición en **modal DevExtreme** (portal a `document.body`), misma ruta del listado.
- Acciones por fila: editar, eliminar, detalle solo lectura (íconos + tooltip).
- Confirmación explícita antes de eliminar, identificando el registro.
- Refresco de grilla tras guardar/eliminar, conservando filtros/orden/layout activo salvo cambio explícito de layout.

## Fuera de alcance

- Formularios full-page salvo excepción documentada en HU SPEC-101.
- Reglas de negocio por entidad (SPEC-101).
- Layouts y exportación (otras HU-GEN-03).

## Reglas de negocio

1. Flujo estándar: grilla → modal → listado.
2. Eliminar solo tras confirmación con identificación del registro.
3. Sin `Permiso_Baja`: acción eliminar oculta o deshabilitada.
4. Sin `Permiso_Alta` / `Permiso_Modi`: agregar/editar no disponibles.
5. Modal real (overlay pantalla completa); no panel recortado por scroll del shell.
6. **Siempre modal** en transversal GEN-03; excepción únicamente por HU de negocio explícita.
7. Validaciones cliente y servidor; errores visibles en modal o notificación según TR.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Modal vs ruta dedicada en MVP transversal | **Siempre modal**; excepción solo si HU de negocio (SPEC-101) lo indica |

## Criterios de aceptación

- [x] **CA-01:** Agregar visible solo con `Permiso_Alta` (demo con permisos mock).
- [x] **CA-02:** Editar/eliminar según `Permiso_Modi` / `Permiso_Baja`.
- [x] **CA-03:** Alta y edición abren modal DX; éxito cierra modal y refresca grilla.
- [x] **CA-04:** El botón **+** de la grilla abre modal vacío con validaciones del recurso.
- [x] **CA-05:** Eliminar muestra diálogo de confirmación.
- [x] **CA-06:** Tras eliminar confirmado, grilla actualizada según política del recurso.
- [x] **CA-07:** Acciones de fila: íconos + tooltip i18n.
- [x] **CA-08:** `data-testid` en Agregar, confirmar baja y acciones de fila.
- [x] **CA-09:** Tras guardar, filtros/orden/layout visibles se mantienen salvo recarga de layout.
- [x] **CA-10:** E2E smoke en ABM de referencia MVP (`/demo/abm`).

## Escenarios Gherkin

```gherkin
Feature: Patrón ABM transversal (SPEC-001-03)

  Scenario: Alta desde grilla en modal
    Given un usuario con Permiso_Alta
    When pulsa el botón + de la grilla
    Then se abre modal de alta sobre el listado
    And al guardar con éxito el listado muestra el nuevo registro

  Scenario: Sin permiso de baja
    Given un usuario sin Permiso_Baja
    When ve la grilla ABM
    Then no puede ejecutar eliminar

  Scenario: Confirmar eliminación
    Given un usuario con Permiso_Baja
    When pulsa eliminar en una fila
    Then ve confirmación con datos del registro
    And al confirmar el registro deja de listarse según política del recurso

  Scenario: Edición en modal
    Given un usuario con Permiso_Modi
    When pulsa editar en una fila
    Then se abre modal con datos del registro
```

## Supuestos explícitos

- Al menos un ABM MVP usará este patrón como referencia en TR.
- Baja lógica vs física: HU de negocio.
- Regla `.cursor/rules/base/20-frontend/24-ui-abm-grilla-alta-edicion-modal.md` aplica en implementación.

## Preguntas abiertas

(Ninguna — cerradas en B1.)

## Riesgos de ambigüedad

- Procesos SPEC-101 muy largos que requieran excepción full-page deben declararlo en su HU para no violar esta norma transversal.

## Veredicto B1

**Lista para TR:** Sí










