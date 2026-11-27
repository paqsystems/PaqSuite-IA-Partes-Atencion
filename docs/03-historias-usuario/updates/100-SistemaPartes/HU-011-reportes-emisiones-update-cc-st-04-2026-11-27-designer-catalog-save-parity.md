# HU-011-update — Paridad catálogo diseñador vs Save DX

Jira: [PIAPARTES-82](https://paqsystems.atlassian.net/browse/PIAPARTES-82)

## Origen

| Campo | Valor |
|-------|--------|
| Control | `00-ControlCalidad-ST` · CC #4 · **27/11/2026** |
| SPEC-update | [SPEC-011-update CC-ST-04](../../../05-open-spec/updates/100-SistemaPartes/SPEC-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md) |
| HU base | [HU-011](../../100-SistemaPartes/HU-011-reportes-emisiones.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-011-update-cc-st-04-catalog-save |
| Título | Como diseñador quiero guardar sobre cualquier variante del proceso que veo en el selector |
| Estado | Pendiente |
| Clasificación | MUST-HAVE |

*Procesado*

## Narrativa

Como usuario con permiso `emission.design`, al diseñar informes de **Consulta detallada** en `/emisiones/disenador`, quiero que el combo de variantes del shell, el lienzo DevExpress y los diálogos **Guardar / Guardar como** hablen del **mismo catálogo** del proceso. Al **confirmar el proceso**, quiero que el sistema prepare en el sidecar las copias de trabajo (`.repx`) de las variantes que están en catálogo con layout guardado, para poder **Guardar** sobre cualquiera sin abrir cada una antes. Si una variante sigue sin copia local, al elegirla el sistema debe hidratarla o avisar con error claro.

## Criterios de aceptación

1. **CA-ST4-01** Tras confirmar proceso, el número de variantes en el SelectBox coincide con las opciones de sobrescritura en Save **sin** tener que abrir cada variante antes (mismo proceso, mismo tenant).
2. **CA-ST4-02** Al seleccionar una variante con layout en catálogo y sin `.repx` local, el diseñador carga el XML de catálogo o muestra error traducido (`emission.design.*`); no abre un informe arbitrario.
3. **CA-ST4-06** El batch al confirmar proceso no sobrescribe `.repx` ya existentes en sidecar (copia de trabajo en curso).
4. **CA-ST4-03** Tras editar y **Guardar**, la variante sigue apareciendo en el combo y en Emitir (SPEC-011).
5. **CA-ST4-04** Save As crea variante nueva visible en catálogo y en el diálogo Save del proceso (flujo existente GEN-15).
6. **CA-ST4-05** Sin regresión: permiso `emission.design`, menú `partes_disenador_emisiones`, exclusión mobile.

## Fuera de alcance

- Field List / Dataset (CC ST #3).
- Asistencia IA diseño (SPEC-015).

## Gherkin (resumen)

```gherkin
Feature: Paridad catálogo y Save DX

  Scenario: Guardar sobre variante solo en catálogo BD
    Given catálogo con variante "Pirulin" con layout_definition en BD
    And no existe Pirulin.repx en sidecar
    When confirmo proceso Consulta detallada y elijo "Pirulin"
    Then el diseñador muestra el layout de Pirulin
    And Guardar lista "Pirulin" o su código canónico

  Scenario: Cardinalidad shell vs Save tras confirmar proceso
    Given K variantes del proceso en GET design/reports con layout en BD
    When confirmo proceso Consulta detallada
    And el sistema completa hydrate batch
    When abro Guardar en el diseñador DX sin cambiar de variante
    Then veo K destinos del proceso (no solo principal)
```