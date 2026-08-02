# SPEC-001-08 - Pivots

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-GEN-08-motor-metadata-pivots](../../03-historias-usuario/001-Generalidades/HU-GEN-08-motor-metadata-pivots.md), [HU-GEN-08-pivotgrid-visualizacion](../../03-historias-usuario/001-Generalidades/HU-GEN-08-pivotgrid-visualizacion.md), [HU-GEN-08-layouts-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-layouts-pivot.md), [HU-GEN-08-exportacion-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-exportacion-pivot.md) |
| **Estado** | Especificado |
| **Revisión A1** | Apto con observaciones (2026-06-10) — decisiones §6 cerradas parcialmente |

## Objetivo

Definir la base conceptual y técnica de pivots (consultas pivotables, plantillas y motor de validaciones) para adopción progresiva.

## Estado de ejecución

**Pendiente de programación (2026-06-11)** — 4 HU + 4 TR con código en repo. Epic **fuera del release MVP portal** hasta activar `PIVOTS_ENABLED` / `PIVOT_LAYOUTS_ENABLED` y migraciones en tenant.

Informe cierre: [F-GEN-08-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-08-cierre-formal.md).

## Entradas requeridas

- Documentación de pivots en `docs/00-contexto/_mono/pivots/`.
- Paridad UI transversal: `SPEC-001-03`, `HU-GEN-03-layouts-grilla`.

## Fuentes

Subcarpeta: `docs/00-contexto/_mono/pivots/`

- `arquitectura_motor_pivots_y_flujo.md`
- `catalogo_campos_pivotables.md`
- `catalogo_plantillas_globales_pivots.md`
- `catalogo_validaciones_pivots.md`
- `especificacion_tecnica_consultas_pivotables.md`
- `frontend-pivotgrid-devextreme-agregaciones-y-menu.md`
- `patron-i18n-pivot-devextreme.md`
- `modelo_datos_pivots_personalizados.md`
- `modelo_datos_pivots_y_catalogo.md`
- `norma_conceptual_pivots.md`

Transversal: `docs/00-contexto/_mono/03-ui-transversal/pivots.md`

## Decisiones humanas (cerradas parcialmente)

| ID | Tema | Decisión |
|----|------|----------|
| **AMB-P08-01** | Diseños propios en selector | Sufijo visual **` (*)`** vía i18n `pivotLayout.ownerMarker`; no persiste en `nombre` |
| **AMB-P08-02** | Plantilla inicial | `configId: null` → **pivot vacía** (field panel sin campos asignados); i18n `pivotLayout.initialTemplate` |
| **AMB-P08-03** | Guardar con plantilla inicial | **Guardar** abre flujo **Guardar como** (POST); no altera pivot base ni diseños ajenos |
| **AMB-P08-04** | Actualizar datos | Ícono `refresh` en toolbar; tooltip `pivot.refresh`; `data-testid="pivotRefresh"`; re-fetch servidor |
| **AMB-P08-05** | i18n PivotGrid | Patrón obligatorio en `patron-i18n-pivot-devextreme.md` (5 idiomas) |
| **AMB-P08-06** | Primera apertura sin último diseño | Aplicar **pivot base** de metadata (consulta útil); plantilla inicial vacía solo al elegirla en selector |
| **AMB-P08-07** | Unicidad de nombre guardado | **Paridad grilla:** único por `consulta_id` (un registro por nombre en la consulta); error i18n si duplicado en Guardar como |
| **AMB-Q-P08-01** | Drill-down obligatorio | **Cerrada (2026-06-11):** solo si `admite_drilldown = 1` por consulta; no obligatorio en todas |
| **AMB-Q-P08-02** | Límite diseños por consulta | **Cerrada (2026-06-11):** sin límite por `consulta_id` (paridad layouts grilla) |
| **AMB-P08-08** | Convivencia grilla/pivot | `tipo_proceso` **informe** en menú (`pq_menus`) **o** `mostrarGrillaYPivot = true` explícito en metadata de consulta |

Detalle normativo: `especificacion_tecnica_consultas_pivotables.md` §17–17.1, `frontend-pivotgrid-devextreme-agregaciones-y-menu.md` §6–8.

## Alcance

- Arquitectura del motor de pivots.
- Catálogos necesarios (campos, plantillas, validaciones).
- Reglas frontend base para pivotgrid.
- Paridad UI con grillas (GEN-03): diseños guardados, plantilla inicial, Actualizar, i18n.

## Fuera de alcance

- Implementación completa de pivots avanzados en el MVP core.
- TR e implementación (partes C y D) hasta priorizar epic pivots.
- HU/TR de ejecución en release MVP portal (`INDICE-Sistema-Partes.md`).

## Inventario de tablas y entidades

| Objeto | Rol |
|--------|-----|
| `pq_pivots_consultas` | Catálogo de consultas pivotables |
| `pq_pivots_campos` | Campos, dimensiones, métricas y agregaciones por consulta |
| `pq_pivots_plantillas` / `pq_pivots_plantillas_det` | Plantillas globales reutilizables |
| `pq_pivots_validaciones` | Restricciones y reglas por consulta |
| `pq_pivots_config` | Diseños guardados (JSON de configuración pivot) |
| `pq_pivots_config_last_used` | Último diseño usado por usuario/consulta (DDL en TR) |
| `pq_pivots_aud` | Auditoría opcional de diseños |

Diagrama y detalle: `modelo_datos_pivots_y_catalogo.md`, `arquitectura_motor_pivots_y_flujo.md`.

## Flujo extremo a extremo (documental)

1. Usuario abre **consulta** con `pivot_habilitado` (`HU-GEN-08-motor-metadata-pivots`).
2. Motor resuelve metadata, valida filtros y obtiene dataset base.
3. Vista inicial **grilla**; usuario alterna a **pivot** si aplica (`HU-GEN-08-pivotgrid-visualizacion`).
4. Primera apertura pivot: `pivotBase` o último diseño guardado.
5. Usuario reorganiza campos, agregaciones y drill-down (si habilitado).
6. **Actualizar**, **Guardar** / **Guardar como** / **Eliminar** diseños (`HU-GEN-08-layouts-pivot`).
7. **Exportar** básico o tabla dinámica (`HU-GEN-08-exportacion-pivot`).

## Trazabilidad HU (parte B)

| HU | TR | Foco | Orden |
|----|-----|------|-------|
| [HU-GEN-08-motor-metadata-pivots](../../03-historias-usuario/001-Generalidades/HU-GEN-08-motor-metadata-pivots.md) | [TR-GEN-08-motor-metadata-pivots](../../04-tareas/001-Generalidades/TR-GEN-08-motor-metadata-pivots.md) | Catálogos, API metadata, validaciones, dataset | 1 |
| [HU-GEN-08-pivotgrid-visualizacion](../../03-historias-usuario/001-Generalidades/HU-GEN-08-pivotgrid-visualizacion.md) | [TR-GEN-08-pivotgrid-visualizacion](../../04-tareas/001-Generalidades/TR-GEN-08-pivotgrid-visualizacion.md) | PivotGrid, agregaciones, alternancia grilla/pivot, i18n | 2 |
| [HU-GEN-08-layouts-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-layouts-pivot.md) | [TR-GEN-08-layouts-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-layouts-pivot.md) | Diseños guardados, plantilla inicial, Actualizar | 3 |
| [HU-GEN-08-exportacion-pivot](../../03-historias-usuario/001-Generalidades/HU-GEN-08-exportacion-pivot.md) | [TR-GEN-08-exportacion-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-exportacion-pivot.md) | Export Excel básico y tabla dinámica | 4 |

## Entregables verificables

- Definición técnica de catálogo de campos, plantillas y validaciones.
- Criterios frontend para pivotgrid documentados (agregaciones, toolbar, i18n).
- Inventario de fuentes en `docs/00-contexto/_mono/pivots/` trazable desde este SPEC.
- **4 HU** enriquecidas en `docs/03-historias-usuario/001-Generalidades/`.

## Criterios de aceptación medibles

- Existen lineamientos únicos para modelado de pivots.
- Se identifican claramente las etapas para SPEC funcional de pivots.
- Queda documentada la paridad con mejoras de grillas:
  1. Diseños propios identificados con ` (*)` (`pivotLayout.ownerMarker`).
  2. Plantilla inicial restablece pivot vacía (`configId: null`).
  3. Guardar desde plantilla inicial equivale a Guardar como.
  4. Ícono Actualizar con re-fetch (`pivot.refresh`, `pivotRefresh`).
  5. i18n DevExtreme del pivot en 5 idiomas (`patron-i18n-pivot-devextreme.md`).
- HU derivadas con criterios de aceptación y Gherkin trazables al contexto `_mono/pivots/`.

---

## Revisión A1 — cierre (2026-06-10)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a HU (MVP portal)** | **No** — bloque documental; sin HU en primer release |
| **Puede abrir epic / HU futura** | **Sí** — tras cerrar pendientes §6.1 en TR dedicada |

### Checklist A1 (resumen)

| Área | Estado | Notas |
|------|--------|-------|
| Alcance / fuera de alcance | OK | Documental MVP; motor completo fuera de portal MVP |
| Actores / permisos | OK | Creador modifica/elimina; visibilidad global por consulta |
| Flujo guardado | OK | Guardar / Guardar como / Cargar / Eliminar + plantilla inicial |
| Reglas de negocio | OK | Paridad GEN-03 cerrada en §6 |
| Datos | Obs. | Tabla canónica `pq_pivots_config`; alias legacy `PQ_PIVOTS` en doc antiguo |
| UI / i18n | OK | Patrón pivot + 5 mejoras grillas documentadas |
| APIs | Pendiente TR | Contrato CRUD no definido a nivel SPEC (vive en arquitectura + TR futura) |
| Criterios aceptación | OK | Medibles y trazables a contexto `_mono/pivots/` |

### Ambigüedades críticas

Ninguna bloqueante para el **estado documental** del SPEC (no hay HU MVP que implementar).

### Ambigüedades menores

| ID | Tema | Resolución |
|----|------|------------|
| AMB-M-P08-01 | `PQ_PIVOTS` vs `pq_pivots_config` | **Cerrado (2026-06-11):** canónico `pq_pivots_config` (`modelo_datos_pivots_y_catalogo.md`); `personalizados.md` = reglas funcionales + boceto `PQ_PIVOTS` (patrón PaqSuite-IA-Tango / TR-020) |
| AMB-M-P08-02 | Tabla `pq_pivots_config_last_used` | Mencionada en arquitectura; **definir DDL en TR** al implementar persistencia |
| AMB-M-P08-03 | `configId` (frontend) vs `pivot_id` (BD) | Mapeo 1:1; documentar en contrato API de la TR |
| AMB-M-P08-04 | `pivots.md` § capacidades vs plantilla inicial | **Alineado** en A1: primera apertura → pivot base; plantilla inicial → vacía bajo demanda (§6 AMB-P08-06) |

### Supuestos detectados

- DevExtreme `PivotGridBlock` será el wrapper transversal (referencia en `frontend-pivotgrid-devextreme-agregaciones-y-menu.md`).
- Dictionary DB con prefijo `pq_pivots_*` según catálogos.
- En MVP portal solo referencia desde `SPEC-001-03`; implementación en módulo Informes u otro epic posterior.

### Preguntas para decisión humana

| ID | Tema | Decisión |
|----|------|----------|
| AMB-Q-P08-01 | Drill-down obligatorio | **Cerrada (2026-06-11):** solo si `admite_drilldown = 1` por consulta |
| AMB-Q-P08-02 | Límite diseños guardados | **Cerrada (2026-06-11):** sin límite por `consulta_id` |

### Recomendaciones de ajuste del SPEC

- [x] Incorporar §6 decisiones humanas (paridad grillas).
- [x] Alinear `pivots.md` transversal con plantilla inicial y toolbar.
- [x] Al generar HU futura: referenciar este SPEC + `especificacion_tecnica` §17.1 como CA.
- [x] Nomenclatura `PQ_PIVOTS` vs `pq_pivots_config`: jerarquía documental (Tango) — canónico en `y_catalogo`; nota de mapeo en `personalizados.md` § cabecera y §5.1.

### Veredicto

**Apto con observaciones** para cierre **A1 documental**.

---

## Parte B — cierre (2026-06-11)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto B1** | **Cerrado** — 4 HU enriquecidas |
| **¿Puede pasar a parte C (TR) en MVP portal?** | **No** — epic posterior; sin prioridad en release MVP |
| **¿Listo para parte C cuando se priorice epic?** | **Sí** — resolver AMB-M-P08-* en TR (AMB-Q-P08-* cerradas) |

### Entregables parte B

| Entregable | Estado |
|------------|--------|
| `HU-GEN-08-motor-metadata-pivots` | Enriquecida |
| `HU-GEN-08-pivotgrid-visualizacion` | Enriquecida |
| `HU-GEN-08-layouts-pivot` | Enriquecida |
| `HU-GEN-08-exportacion-pivot` | Enriquecida |
| Índice HU README 001-Generalidades | Actualizado |

### Decisiones producto cerradas post-B1 (2026-06-11)

| ID | Tema | Decisión |
|----|------|----------|
| AMB-Q-P08-01 | Drill-down | Solo si `admite_drilldown = 1` por consulta |
| AMB-Q-P08-02 | Límite diseños | Sin límite por `consulta_id` |
| AMB-P08-08 | Convivencia grilla/pivot | `tipo_proceso` informe en menú o `mostrarGrillaYPivot` explícito en metadata |

### Veredicto

**B1 cerrado** para SPEC-001-08. **C generada (2026-06-11).** No avanzar a **parte D** en MVP portal. Al priorizar el epic pivots, implementar en orden motor-metadata → pivotgrid → layouts → exportación.

---

## Parte C — cierre (2026-06-11)

### Entregables parte C

| Entregable | Estado |
|------------|--------|
| `TR-GEN-08-motor-metadata-pivots` | Generada |
| `TR-GEN-08-pivotgrid-visualizacion` | Generada |
| `TR-GEN-08-layouts-pivot` | Generada |
| `TR-GEN-08-exportacion-pivot` | Generada |
| Índice TR README 001-Generalidades | Actualizado |

### Revisión C1 (2026-06-11)

| TR | Veredicto C1 |
|----|----------------|
| TR-GEN-08-motor-metadata-pivots | Apto con observaciones |
| TR-GEN-08-pivotgrid-visualizacion | Apto con observaciones |
| TR-GEN-08-layouts-pivot | Apto con observaciones |
| TR-GEN-08-exportacion-pivot | Apto con observaciones |

Informe epic: [F-GEN-08-cierre-c1](../../04-tareas/001-Generalidades/F-GEN-08-cierre-c1.md)

### Próximo paso

~~**D1** implementación~~ **Completado (2026-06-11).** Ver [F-GEN-08-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-08-cierre-formal.md). Siguiente: activación deploy + QA manual en tenant.

---

## Parte D — cierre (2026-06-11)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto D1** | **Cerrado** — 4 TR implementadas |
| **Veredicto F formal** | **Aprobado con observaciones** |
| **Release MVP portal** | **No incluido** — flags default `false` |

### Entregables parte D

| Entregable | Estado |
|------------|--------|
| `TR-GEN-08-motor-metadata-pivots` | Pendiente de programación |
| `TR-GEN-08-pivotgrid-visualizacion` | Pendiente de programación |
| `TR-GEN-08-layouts-pivot` | Pendiente de programación |
| `TR-GEN-08-exportacion-pivot` | Pendiente de programación |
| Matriz permisos § Pivots | Actualizada |
| Informe F | [F-GEN-08-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-08-cierre-formal.md) |

### Piloto integrado

- Consulta: `CONSULTA_PILOTO_PIVOT` (historial ventas, `pw_historialventas`).
- UI: `HistorialVentasPage` + `ConsultaGrillaPivotShell`.
- Convivencia grilla/pivot: `tipoProceso=informe` + `mostrarGrillaYPivot`.

### Veredicto

**Epic SPEC-001-08 cerrado en documentación e implementación D1.** Activación productiva = decisión de release aparte del MVP portal.








