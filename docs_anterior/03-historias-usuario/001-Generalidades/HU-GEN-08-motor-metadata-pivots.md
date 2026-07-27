# HU-GEN-08-motor-metadata-pivots — Motor y catálogo de consultas pivotables

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-08-motor-metadata-pivots |
| **SPEC origen** | [SPEC-001-08-pivots.md](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **MONO** | [arquitectura_motor_pivots_y_flujo.md](../../00-contexto/_mono/pivots/arquitectura_motor_pivots_y_flujo.md) §4–§7; [especificacion_tecnica_consultas_pivotables.md](../../00-contexto/_mono/pivots/especificacion_tecnica_consultas_pivotables.md) |
| **Épica** | 001 — Generalidades / Pivots |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **TR relacionada** | [TR-GEN-08-motor-metadata-pivots](../../04-tareas/001-Generalidades/TR-GEN-08-motor-metadata-pivots.md) |
| **Última actualización** | 2026-06-11 |
| **Dependencias** | HU-GEN-02-autorizacion-menu-api; tablas `pq_pivots_*` en Dictionary DB |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Arquitectura del motor de pivots | Narrativa, alcance |
| Catálogos campos, plantillas, validaciones | RN-01–RN-04, CA-01 |
| Resolución definición efectiva | RN-05, CA-02 |
| Validaciones pre-ejecución (metadata, filtros, estructura, volumen) | RN-06–RN-09, CA-03–CA-05 |
| Inventario tablas `pq_pivots_*` | Alcance |
| Fuera de alcance MVP portal | Metadatos, veredicto B1 |

## Narrativa

Como **desarrollador o analista que configura informes pivotables**,  
quiero **que cada consulta tenga metadata formal en Dictionary DB con campos, plantillas y validaciones**,  
para **que el motor resuelva una definición efectiva coherente sin hardcodear pivots en código**.

## Contexto funcional

El motor de pivots separa **definición estructural** (metadata en `pq_pivots_consultas`, `pq_pivots_campos`, `pq_pivots_plantillas`, `pq_pivots_plantillas_det`, `pq_pivots_validaciones`) de **ejecución dinámica** controlada por reglas. Al abrir una consulta, el backend resuelve la definición efectiva (herencia plantilla global + overrides locales), valida integridad y expone metadata al frontend. Tabla canónica de diseños guardados: `pq_pivots_config` (no `PQ_PIVOTS` legacy).

## Alcance incluido

- Catálogo `pq_pivots_consultas`: consultas con `pivot_habilitado`, fuente técnica, versión, flag `admite_drilldown`.
- Catálogo `pq_pivots_campos`: dimensiones, métricas, roles permitidos, agregaciones, formatos, `nombreVisible` (sin exponer nombres técnicos en UI).
- Catálogos `pq_pivots_plantillas` / `pq_pivots_plantillas_det`: comportamientos estándar reutilizables.
- Catálogo `pq_pivots_validaciones`: restricciones por consulta (filtros obligatorios, límites dimensiones/métricas, combinaciones prohibidas).
- API metadata por `consulta_id`: definición efectiva, `pivotBase`, campos, filtros generales, restricciones, flags de exportación y persistencia.
- Capas de validación: metadata (§7.1), filtros (§7.2), estructura pivot (§7.3), volumen (§7.4).
- Ejecución dataset base: view/procedure/API según consulta, respetando límites (`maximoRegistrosBase`, `requiereFiltroPrevio`).
- Auditoría opcional `pq_pivots_aud` (creación/modificación/eliminación de diseños).
- Envelope API MONO en endpoints `/api/v1/*`.

## Fuera de alcance

- Renderizado PivotGrid DevExtreme (`HU-GEN-08-pivotgrid-visualizacion`).
- Diseños guardados UI (`HU-GEN-08-layouts-pivot`).
- Exportación Excel pivot (`HU-GEN-08-exportacion-pivot`).
- ABM web de catálogos pivot (configuración vía seeds/SQL en v1).
- MVP portal SistemaPartes (`INDICE-Sistema-Partes.md`).

## Reglas de negocio

1. Toda consulta pivotable requiere definición formal con **exactamente una** `pivotBase` en metadata.
2. Campos referenciados en `pivotBase` deben existir en `pq_pivots_campos` activos.
3. Métricas declaran `agregacionDefault` y `agregacionesPermitidas`; solo agregaciones compatibles con `tipoDato`.
4. Herencia: plantilla global aporta defaults; campo local sobrescribe solo valores informados.
5. El usuario **nunca** ve `nombreTecnico` en UI; solo `nombreVisible`.
6. Validaciones mínimas obligatorias (especificación §19): `consultaId` único, `pivotHabilitado`, campos coherentes, filtros obligatorios declarados.
7. Si `pivot_habilitado = 0`, la consulta no expone alternancia pivot (solo grilla).
8. Restricciones de volumen bloquean o advierten antes de ejecutar según `bloquearSiExcedeVolumen`.
9. Filtros generales afectan grilla y pivot por igual; validación previa a dataset.
10. Versión de definición compatible con diseños guardados (`pq_pivots_config`).

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Tabla canónica diseños | `pq_pivots_config` (AMB-M-P08-01) |
| `configId` frontend vs `pivot_id` BD | Mapeo 1:1; documentar en TR (AMB-M-P08-03) |
| `pq_pivots_config_last_used` | DDL en TR al implementar (AMB-M-P08-02) |
| Drill-down (AMB-Q-P08-01) | **Cerrada:** solo si `admite_drilldown = 1` por consulta; no obligatorio en todas |
| MVP portal | **Fuera** — epic posterior |

## Criterios de aceptación

- [ ] **CA-01:** API metadata devuelve definición efectiva completa para `consulta_id` activo con `pivot_habilitado = 1`.
- [ ] **CA-02:** `pivotBase`, campos, filtros generales y restricciones son coherentes con catálogos BD.
- [ ] **CA-03:** Consulta con metadata inválida (campo faltante en pivotBase) → error explícito sin dataset.
- [ ] **CA-04:** Filtros obligatorios no informados bloquean ejecución con mensaje i18n.
- [ ] **CA-05:** Exceso de `maximoRegistrosBase` respeta `bloquearSiExcedeVolumen`.
- [ ] **CA-06:** Validaciones de estructura pivot (máx. filas/columnas/métricas) impiden combinaciones inválidas.
- [ ] **CA-07:** Nombres visibles en metadata no exponen nombres técnicos de BD.
- [ ] **CA-08:** Consulta sin pivot habilitado no incluye bloque pivot en metadata.

## Escenarios Gherkin

```gherkin
Feature: Motor y metadata de consultas pivotables

  Scenario: Metadata válida para consulta pivotable
    Given una consulta activa con pivot_habilitado = 1
    And catálogos de campos y pivotBase coherentes
    When el frontend solicita metadata por consulta_id
    Then recibe pivotBase, campos, filtros y restricciones
    And todos los nombreVisible son legibles para el usuario

  Scenario: Campo inexistente en pivotBase
    Given pivotBase referencia un campoId no definido en pq_pivots_campos
    When se resuelve la definición efectiva
    Then el motor rechaza la configuración con error de integridad

  Scenario: Filtro obligatorio faltante
    Given una consulta con filtro Empresa obligatorio
    When el usuario intenta ejecutar sin seleccionar Empresa
    Then no se obtiene dataset base
    And ve mensaje indicando filtro requerido

  Scenario: Consulta sin pivot
    Given una consulta con pivot_habilitado = 0
    When se carga la pantalla
    Then metadata indica solo modo grilla
```

## Supuestos explícitos

- Tablas `pq_pivots_*` en Dictionary DB según `modelo_datos_pivots_y_catalogo.md`.
- Seeds o scripts SQL cargan catálogos iniciales por módulo (Informes, etc.).
- Contrato API detallado se define en TR (AMB A1: APIs pendientes en SPEC).

## Preguntas abiertas

(Ninguna — AMB-Q-P08-01 cerrada en decisiones.)

## Veredicto B1

**Lista para TR** — epic pivots posterior al MVP portal. Resolver AMB-M-P08-* en TR.

## Veredicto D1 (2026-06-11)

**Pendiente** — ver [TR-GEN-08-motor-metadata-pivots](../../04-tareas/001-Generalidades/TR-GEN-08-motor-metadata-pivots.md) y [F-GEN-08-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-08-cierre-formal.md).










