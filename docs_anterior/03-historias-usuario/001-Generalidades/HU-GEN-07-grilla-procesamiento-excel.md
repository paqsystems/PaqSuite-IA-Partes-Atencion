# HU-GEN-07-grilla-procesamiento-excel — Grilla de staging y procesamiento

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-07-grilla-procesamiento-excel |
| **SPEC origen** | [SPEC-001-07-importar-excel.md](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **TR** | [TR-GEN-07-grilla-procesamiento-excel.md](../../04-tareas/001-Generalidades/TR-GEN-07-grilla-procesamiento-excel.md) |
| **MONO** | [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §6.1, §8 |
| **Épica** | 001 — Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **Última actualización** | 2026-06-11 |
| **Dependencias** | HU-GEN-07-carga-staging-excel; HU-GEN-03-grillas-listados |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Presentación DevExtreme DataGrid (§8) | CA-01, CA-02 |
| Columna fija de errores concatenados | CA-02 |
| Política `PermiteProcesamientoParcial` | RN-01–RN-04, CA-03–CA-06 |
| Casos borde §6.1 (todas error, cero error, mezcla) | RN-02–RN-04 |
| Procesamiento final y auditoría lote | RN-05, CA-07 |
| `FilaAjustadaAutomaticamente` sin UI | Fuera de alcance UI |

## Narrativa

Como **usuario que revisa una importación cargada**,  
quiero **ver las filas en una grilla con sus errores y confirmar el procesamiento cuando corresponda**,  
para **aplicar solo los registros válidos según la política del proceso**.

## Contexto funcional

Tras la carga en staging, el usuario ve un `DataGrid` DevExtreme con columnas del proceso y una columna fija **Errores** (mensajes concatenados). Filas con error tienen fondo suave y tooltip con el detalle. La acción **Procesar** / **Confirmar** depende de `PermiteProcesamientoParcial` y de los contadores del lote. El `HandlerBackend` aplica la lógica al destino solo sobre filas habilitadas.

## Alcance incluido

- Grilla DevExtreme de filas del lote (`PQ_EXCEL_IMPORTACIONES_FILAS`).
- Columna fija **Errores** con mensajes concatenados por fila.
- Resaltado visual de filas con error (fondo suave) + tooltip.
- Acciones **Procesar** / **Confirmar** y **Cancelar** según estado del lote.
- Lógica UI de `PermiteProcesamientoParcial` (mensajes i18n explícitos).
- Invocación de `HandlerBackend` para procesamiento final.
- Actualización de contadores: `CantidadFilasConError`, `CantidadFilasProcesadas`, `CantidadFilasValidas`.
- Estados finales: `procesada`, `procesada_parcial`, `procesando`.
- Notificaciones internas (`PQ_EXCEL_IMPORTACIONES_NOTIFICACIONES`: toast/bandeja/resultado) al completar asíncrono.
- Paridad transversal: i18n, `data-testid`, controles DevExtreme.

## Fuera de alcance

- Carga del archivo y parser (`HU-GEN-07-carga-staging-excel`).
- Pantalla de historial (`HU-GEN-07-historial-importaciones`).
- Mostrar `FilaAjustadaAutomaticamente` al usuario.
- ABM de definición de procesos.
- MVP portal SistemaPartes.

## Reglas de negocio

1. **`PermiteProcesamientoParcial = false`:** si ≥ 1 fila con error → **Procesar** deshabilitado; mensaje «Existen filas con error; corrija el archivo antes de procesar» (i18n).
2. **`PermiteProcesamientoParcial = true` y mezcla error + válidas:** **Procesar** habilitado; solo filas válidas al destino; estado final **`procesada_parcial`**; informar cantidad omitida.
3. **`PermiteProcesamientoParcial = true` y todas con error:** **Procesar** deshabilitado (sin filas válidas).
4. **Cero filas con error:** procesar todo; estado **`procesada`** (no `procesada_parcial`).
5. Errores **estructurales** no llegan a esta pantalla con lote procesable.
6. `PermiteSoloValidar = 1`: no mostrar **Procesar**; solo revisión de validación.
7. Tras procesamiento, filas aplicadas → `EstadoFila = procesada`; auditoría completa del lote.
8. Cancelación solo si `PuedeCancelar = 1` y antes de `procesando`/`procesada*`.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Todas las filas con error | No habilitar procesamiento (parcial true o false) |
| Cero filas con error | Estado `procesada` |
| Mezcla con parcial true | Solo válidas → `procesada_parcial` |
| Fila ajustada | Sin columna ni ícono en grilla |
| Grilla estándar | `DataGridDx` / patrón `HU-GEN-03-grillas-listados` |

## Criterios de aceptación

- [ ] **CA-01:** Grilla DevExtreme muestra columnas del proceso + columna Errores fija.
- [ ] **CA-02:** Fila con error: fondo suave, errores concatenados en columna, tooltip con detalle.
- [ ] **CA-03:** `PermiteProcesamientoParcial = false` y ≥ 1 error → Procesar deshabilitado + mensaje i18n.
- [ ] **CA-04:** `PermiteProcesamientoParcial = true`, mezcla error/válidas → Procesar habilitado; informa filas omitidas.
- [ ] **CA-05:** Todas las filas con error → Procesar deshabilitado aunque parcial = true.
- [ ] **CA-06:** Sin filas con error → procesamiento total y estado `procesada`.
- [ ] **CA-07:** Tras procesar, contadores y estado del lote coherentes con BD.
- [ ] **CA-08:** Procesamiento asíncrono notifica al usuario al finalizar.
- [ ] **CA-09:** `data-testid`: `excelStagingGrid`, `excelProcessConfirm`, `excelImportCancel`.

## Escenarios Gherkin

```gherkin
Feature: Grilla y procesamiento de importación Excel

  Scenario: Bloqueo por errores sin procesamiento parcial
    Given un lote con PermiteProcesamientoParcial = false
    And al menos una fila con error
    When el usuario revisa la grilla
    Then la acción Procesar está deshabilitada
    And ve un mensaje indicando filas con error

  Scenario: Procesamiento parcial
    Given un lote con PermiteProcesamientoParcial = true
    And filas válidas y filas con error
    When confirma el procesamiento
    Then solo las filas válidas se aplican al destino
    And el lote queda en estado procesada_parcial

  Scenario: Procesamiento total sin errores
    Given un lote sin filas con error
    When confirma el procesamiento
    Then todas las filas válidas se procesan
    And el lote queda en estado procesada

  Scenario: Todas las filas con error
    Given un lote donde todas las filas tienen error
    When el usuario revisa la grilla
    Then Procesar está deshabilitado
```

## Supuestos explícitos

- API de staging paginada para grillas grandes.
- `HandlerBackend` es plug-in por `CodigoProceso` (convención `Importacion.{Modulo}.{Accion}Handler`).

## Preguntas abiertas

| ID | Pregunta | Propuesta default |
|----|----------|-------------------|
| AMB-Q-07-03 | ¿Reprocesar filas válidas tras corregir Excel en mismo lote? | No en primera versión; nueva importación |

## Veredicto B1

**Lista para TR** — epic posterior al MVP portal. Resolver AMB-Q-07-03 en TR.










