# HU-GEN-03-exportaciones — Exportación desde grillas

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-03-exportaciones |
| **SPEC origen** | [SPEC-001-03-ui-transversal.md](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001 — Generalidades / UI transversal |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #2) |
| **B1** | Enriquecida (2026-06-01) |
| **TR relacionada** | [TR-GEN-03-exportaciones](../../04-tareas/001-Generalidades/TR-GEN-03-exportaciones.md) |
| **Dependencias** | HU-GEN-03-grillas-listados; HU-GEN-03-layouts-grilla (vista vigente) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Checklist: exportación según `exportaciones.md` | CA conjunto |
| Exportación Excel (SPEC; PDF fuera) | Alcance, fuera de alcance |
| Toolbar superior inmediata | CA-01, RN-01 |
| Modalidades básica y formateada | CA-03, RN-04 |
| Vista vigente (filtros, orden, layout) | RN-02, CA-04 |
| Sin datos → deshabilitado + mensaje | CA-02 |
| Permisos = consulta en pantalla | RN-05 |
| Producto §17.8 Excel | In scope |
| PDF | Fuera de alcance → SPEC-001-06 |

## Narrativa

Como **usuario del portal**,  
quiero **exportar a Excel el contenido de la grilla según la vista que estoy viendo**,  
para **analizar o compartir datos con el mismo criterio de columnas y filtros que uso en pantalla**.

## Contexto funcional

SPEC-001-03 limita la exportación transversal del MVP a **Excel** desde grillas. La exportación toma la **vista vigente** (columnas, filtros, orden, agrupaciones y **layout activo**). PDF y jobs batch quedan fuera; PDF se abordará en **SPEC-001-06** (emisión), no en esta HU.

## Alcance incluido

- Acción **Exportar a Excel** en toolbar superior (junto a layouts y acciones globales).
- Modalidades **básica** y **formateada**; default **formateada** salvo que HU de proceso indique otra cosa.
- Botón deshabilitado sin datos exportables + mensaje i18n.
- Mismos permisos que la consulta visible.
- Nombre **sugerido** (`proceso` + fecha); diálogo Guardar si el navegador lo permite; si no, descarga silenciosa con ese nombre (detalle TR).
- Exportación coherente con layout cargado (`HU-GEN-03-layouts-grilla`).

## Fuera de alcance

- **Exportación PDF** (SPEC-001-06 emisión, etapa posterior).
- Exportación batch/programada.
- PivotGrid avanzado (SPEC-001-08).
- Pantallas no tabulares.

## Reglas de negocio

1. Acción en franja superior inmediata de la grilla.
2. **Grilla vacía** → botón Exportar deshabilitado + aviso i18n.
3. Exporta filtros, orden y agrupación visibles; columnas según **layout activo**.
4. Default modalidad **formateada** para grillas transversales.
5. Permisos idénticos a los de ver la grilla del proceso.
6. Modalidad **formateada** aplica formato Excel visible respecto de la básica: fechas según locale, enteros sin decimales, decimales según campo (fallback 2), booleanos VERDADERO/FALSO (i18n), encabezados con negrita y fondo gris, totalizadores de pie.
7. Modalidad **básica** exporta valores sin formato avanzado (limpia estilos que DevExtreme aplica por defecto).

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| PDF en MVP transversal GEN-03 | **No** — previsto en **SPEC-001-06-emision** más adelante |
| Guardado sin diálogo (AMB-C01) | **Descarga silenciosa** del navegador con nombre sugerido `proceso` + fecha |
| Grilla vacía (AMB-C02) | **Inhabilitar** Exportar |

## Criterios de aceptación

- [x] **CA-01:** Grillas exportables muestran Exportar a Excel en toolbar superior.
- [x] **CA-02:** Grilla vacía → Exportar deshabilitado y mensaje i18n.
- [x] **CA-03:** Usuario puede elegir básica o formateada cuando el proceso lo soporte.
- [x] **CA-04:** Archivo refleja columnas/filtros del layout activo.
- [x] **CA-05:** Default formateada si el proceso no define otra modalidad.
- [x] **CA-06:** Con diálogo del sistema disponible, el usuario elige carpeta y nombre; nombre sugerido `proceso` + fecha.
- [x] **CA-07:** Sin diálogo posible → descarga silenciosa del navegador con el nombre sugerido por defecto.
- [x] **CA-08:** Usuario sin permiso al proceso no exporta (prop `exportEnabled` en integración).
- [x] **CA-09:** i18n y `data-testid` en acción exportar.
- [x] **CA-10:** E2E smoke del flujo exportar + guardar (o stub del picker en CI).
- [x] **CA-11:** Formateada se distingue de básica (formatos por tipo, encabezados gris, totales pie, booleanos i18n).

## Escenarios Gherkin

```gherkin
Feature: Exportación de grillas (SPEC-001-03)

  Scenario: Exportar formateada con layout activo
    Given una grilla con registros visibles
    And un layout activo que oculta columnas
    When exporta en modalidad formateada
    Then obtiene archivo Excel
    And el contenido respeta columnas y filtros visibles

  Scenario: Sin datos exportables
    Given una grilla sin registros
    When ve la toolbar
    Then Exportar está deshabilitado
    And ve mensaje informativo

  Scenario: Sin permiso al proceso
    Given un usuario sin permiso de consulta al proceso
    When intenta acceder al listado
    Then no puede exportar

  Scenario: Modalidad básica
    Given una grilla con datos
    When el usuario elige exportación básica
    Then el archivo contiene datos sin formato Excel avanzado
```

## Supuestos explícitos

- Mecanismo Excel (cliente o backend): TR.
- Alcance página actual vs dataset completo: TR según `grillas.md` y performance.

## Preguntas abiertas

(Ninguna — cerradas en B1.)

## Riesgos de ambigüedad

- Exportar dataset completo en consultas grandes: límites y UX en TR.

## Veredicto B1

**Lista para TR:** Sí










