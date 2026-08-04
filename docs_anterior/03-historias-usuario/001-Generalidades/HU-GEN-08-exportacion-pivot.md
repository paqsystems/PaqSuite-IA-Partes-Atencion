# HU-GEN-08-exportacion-pivot — Exportación Excel desde pivot

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-08-exportacion-pivot |
| **SPEC origen** | [SPEC-001-08-pivots.md](../../05-open-spec/001-Generalidades/SPEC-001-08-pivots.md) |
| **MONO** | [pivots.md](../../00-contexto/_mono/03-ui-transversal/pivots.md) § Exportación; [especificacion_tecnica_consultas_pivotables.md](../../00-contexto/_mono/pivots/especificacion_tecnica_consultas_pivotables.md) §16 |
| **Épica** | 001 — Generalidades / Pivots |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **TR relacionada** | [TR-GEN-08-exportacion-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-exportacion-pivot.md) |
| **Última actualización** | 2026-06-11 |
| **Dependencias** | HU-GEN-08-pivotgrid-visualizacion; HU-GEN-08-layouts-pivot; HU-GEN-03-exportaciones (paridad transversal) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Exportación pivot documentada en contexto transversal | Alcance |
| Modalidades básica y tabla dinámica | RN-01–RN-02, CA-02–CA-03 |
| Flags metadata `exportacion` | RN-03, CA-04 |
| Toolbar superior junto layouts | RN-04, CA-01 |
| Permisos = pantalla | RN-05 |
| Paridad límites exportación general | RN-06 |

## Narrativa

Como **usuario que analiza un pivot**,  
quiero **exportar los resultados a Excel en formato básico o como tabla dinámica**,  
para **compartir o seguir analizando fuera del sistema con la estructura pivot cuando corresponda**.

## Contexto funcional

La exportación pivot convive en la toolbar superior del bloque analítico, después de Actualizar y diseños guardados. Metadata por consulta define `excelBasicoHabilitado`, `excelFormateadoHabilitado` (tabla dinámica), `incluirFiltrosAplicados` e `incluirMetadatos`. Solo se ofrece **tabla dinámica** cuando la pantalla está efectivamente en **modo pivot**. Paridad de permisos y límites con `HU-GEN-03-exportaciones`.

## Alcance incluido

- Acción **Exportar** en toolbar del bloque pivot (orden: después de diseños).
- Modalidad **básica**: matriz visible con encabezados y totales; datos sin estructura interactiva completa.
- Modalidad **tabla dinámica**: estructura pivot XLSX (jerarquía filas/columnas, subtotales) cuando tecnología lo soporte.
- Exporta vista actual con filtros generales e internos aplicados.
- Metadatos opcionales: filtros, usuario, fecha, consulta, diseño pivot usado.
- Botón deshabilitado sin datos exportables + mensaje i18n.
- Mismos permisos que ver/ejecutar la consulta.
- Nombre archivo sugerido: consulta + fecha (detalle TR).
- PDF fuera de alcance (flag `pdfHabilitado` ignorado en v1).

## Fuera de alcance

- Exportación desde grilla tabular (`HU-GEN-03-exportaciones`).
- Exportación PDF (SPEC-001-06 emisión).
- Jobs batch/programados.
- MVP portal SistemaPartes.

## Reglas de negocio

1. Exportación solo disponible en **modo pivot** activo para modalidad tabla dinámica.
2. Modalidad **básica**: prioriza contenido de datos sobre estructura interactiva.
3. Modalidad **tabla dinámica**: preserva jerarquía y subtotales del pivot visible.
4. Flags metadata deshabilitan modalidades no permitidas (`excelBasicoHabilitado`, `excelFormateadoHabilitado`).
5. Permisos idénticos a la consulta en pantalla.
6. Sin datos exportables → botón deshabilitado + aviso i18n (paridad GEN-03).
7. `incluirFiltrosAplicados` agrega hoja o sección con filtros vigentes.
8. `incluirMetadatos` agrega usuario, timestamp, `consulta_id`, nombre diseño pivot.
9. Mismo criterio de límites de volumen que exportación general transversal.
10. Controles en franja superior inmediata del bloque pivot.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| PDF pivot | **Fuera** v1 — `pdfHabilitado` no implementar |
| Tabla dinámica | Solo en modo pivot activo |
| Paridad permisos | Igual que consulta visible |
| MVP portal | **Fuera** |

## Criterios de aceptación

- [ ] **CA-01:** Bloque pivot en modo pivot muestra Exportar en toolbar superior.
- [ ] **CA-02:** Modalidad básica genera XLSX con matriz visible y totales.
- [ ] **CA-03:** Modalidad tabla dinámica genera XLSX con estructura pivot cuando está habilitada en metadata.
- [ ] **CA-04:** Consulta con `excelFormateadoHabilitado = false` no ofrece tabla dinámica.
- [ ] **CA-05:** Sin datos → Exportar deshabilitado y mensaje i18n.
- [ ] **CA-06:** Exportación respeta filtros generales vigentes.
- [ ] **CA-07:** Con `incluirMetadatos`, archivo incluye consulta y diseño usado.
- [ ] **CA-08:** Usuario sin permiso de consulta no exporta (403 o acción oculta).
- [ ] **CA-09:** `data-testid` estable en acción export (`pivotExport` o prefijo proceso).

## Escenarios Gherkin

```gherkin
Feature: Exportación Excel desde pivot

  Scenario: Exportación básica con datos
    Given un pivot visible con datos y excelBasicoHabilitado = true
    When el usuario exporta en modalidad básica
    Then descarga un archivo xlsx con la matriz visible

  Scenario: Tabla dinámica en modo pivot
    Given la pantalla en modo pivot
    And excelFormateadoHabilitado = true
    When exporta como tabla dinámica
    Then el archivo conserva jerarquía de filas y columnas pivot

  Scenario: Tabla dinámica no disponible en grilla
    Given la pantalla en modo grilla
    When el usuario busca exportar como tabla dinámica
    Then la opción no está disponible

  Scenario: Sin datos
    Given un pivot sin celdas con datos
    When intenta exportar
    Then Exportar está deshabilitado
    And ve mensaje i18n indicando ausencia de datos
```

## Supuestos explícitos

- Librería exportación reutiliza patrones de `HU-GEN-03-exportaciones` donde aplique.
- DevExtreme export pivot o librería complementaria según TR.
- Metadata `exportacion` provista por motor-metadata.

## Preguntas abiertas

(Ninguna bloqueante para TR.)

## Veredicto B1

**Lista para TR** — epic posterior al MVP portal.

## Veredicto D1 (2026-06-11)

**Pendiente** — ver [TR-GEN-08-exportacion-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-exportacion-pivot.md) y [F-GEN-08-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-08-cierre-formal.md).










