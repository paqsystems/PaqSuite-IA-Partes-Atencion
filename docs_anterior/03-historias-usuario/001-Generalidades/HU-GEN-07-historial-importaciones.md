# HU-GEN-07-historial-importaciones — Historial de importaciones

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-07-historial-importaciones |
| **SPEC origen** | [SPEC-001-07-importar-excel.md](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **TR** | [TR-GEN-07-historial-importaciones.md](../../04-tareas/001-Generalidades/TR-GEN-07-historial-importaciones.md) |
| **MONO** | [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §10 |
| **Épica** | 001 — Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **Última actualización** | 2026-06-11 |
| **Dependencias** | HU-GEN-07-carga-staging-excel; HU-GEN-03-grillas-listados; HU-GEN-02-autorizacion-menu-api |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Historial desde primera etapa (§10) | Narrativa, alcance |
| Campos mínimos del historial | RN-01, CA-01 |
| Vista `PQ_EXCEL_VW_HISTORIAL_IMPORTACIONES` | Alcance técnico |
| Multiusuario / auditoría | RN-02 |
| Fuera de alcance MVP | Metadatos |

## Narrativa

Como **usuario supervisor u operador**,  
quiero **consultar el historial de importaciones Excel ejecutadas**,  
para **auditar resultados, estados y volúmenes procesados por proceso y usuario**.

## Contexto funcional

Toda ejecución de importación genera un registro en `PQ_EXCEL_IMPORTACIONES`. La pantalla de historial consulta la vista `PQ_EXCEL_VW_HISTORIAL_IMPORTACIONES` (o API equivalente) con grilla DevExtreme de solo lectura. Permite filtrar por proceso, estado, usuario y rango de fechas. Desde una fila se puede abrir el detalle del lote (grilla de staging en modo lectura si el lote aún existe).

## Alcance incluido

- Pantalla **Historial de importaciones** (ruta y menú definidos en TR).
- Grilla DevExtreme solo lectura con columnas mínimas:
  - fecha/hora inicio (y fin si aplica)
  - usuario
  - proceso (`CodigoProceso` / `NombreProceso`)
  - archivo original
  - hoja seleccionada
  - estado del lote
  - filas leídas, válidas, con error, procesadas, descartadas
- Filtros por proceso, estado, usuario, rango de fechas.
- Orden default: fecha inicio descendente.
- Acción ver detalle del lote (navegación a grilla staging en lectura).
- i18n de estados (`procesada`, `procesada_parcial`, `cancelada`, etc.).
- `data-testid` estables (`excelHistoryGrid`, `excelHistoryDetail`).

## Fuera de alcance

- Reprocesamiento o cancelación retroactiva desde historial.
- Purga/archivado por antigüedad (evolución futura §9 SQL).
- Edición de lotes históricos.
- MVP portal SistemaPartes.

## Reglas de negocio

1. Solo lectura: ninguna acción modifica lotes desde historial en esta HU.
2. Visibilidad: usuario ve importaciones según permiso del proceso (mismo criterio que ejecutar importación; supervisor puede ver más según TR).
3. Lotes de otros usuarios no se mezclan en staging activo; historial es consulta transversal autorizada.
4. Estados mostrados con etiqueta i18n legible, no código crudo.
5. Contadores deben coincidir con `PQ_EXCEL_IMPORTACIONES` al momento de la consulta.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Fuente de datos | Vista `PQ_EXCEL_VW_HISTORIAL_IMPORTACIONES` + API paginada |
| Detalle | Navegación a grilla staging del lote en **solo lectura** |
| Exportar historial | Opcional Should en TR (paridad `HU-GEN-03-exportaciones`) |
| MVP portal | **Fuera** |

## Criterios de aceptación

- [ ] **CA-01:** Grilla muestra columnas mínimas del §10 conceptual (fecha, usuario, proceso, archivo, hoja, estado, contadores).
- [ ] **CA-02:** Filtros por proceso, estado y rango de fechas funcionan.
- [ ] **CA-03:** Orden default por fecha inicio descendente.
- [ ] **CA-04:** Usuario sin permiso no accede al historial del proceso.
- [ ] **CA-05:** Acción ver detalle abre grilla del lote en modo lectura.
- [ ] **CA-06:** Estados con etiquetas i18n (`excelImport.status.*`).
- [ ] **CA-07:** `data-testid` en grilla y acción detalle.

## Escenarios Gherkin

```gherkin
Feature: Historial de importaciones Excel

  Scenario: Listado de importaciones
    Given un usuario con permiso de consulta de importaciones
    When abre Historial de importaciones
    Then ve una grilla con lotes ordenados por fecha descendente
    And cada fila muestra proceso, archivo, estado y contadores

  Scenario: Filtrar por proceso
    Given importaciones de distintos procesos
    When filtra por CodigoProceso ARTICULOS_ALTA
    Then solo ve lotes de ese proceso

  Scenario: Ver detalle de lote
    Given un lote en estado procesada_parcial
    When selecciona ver detalle
    Then navega a la grilla de staging en solo lectura
    And no puede reprocesar desde esa vista

  Scenario: Sin permiso
    Given un usuario sin permiso sobre el proceso
    When intenta acceder al historial filtrado por ese proceso
    Then no obtiene datos o recibe HTTP 403 según API
```

## Supuestos explícitos

- Vista SQL creada según `PQ_EXCEL_SQL_Server_Tablas_y_Create.md` §6.
- Detalle de lote reutiliza componentes de `HU-GEN-07-grilla-procesamiento-excel` en modo lectura.

## Preguntas abiertas

(Ninguna bloqueante para TR — export Excel del historial es opcional Should.)

## Veredicto B1

**Lista para TR** — epic posterior al MVP portal.










