# HU-005-update — Proceso masivo: paginación del listado filtrado

## Metadatos

| Campo | Valor |
|-------|--------|
| **ID** | HU-005-update-cc-pq-11-2026-11-01-masivo-paginacion |
| **Título** | Corregir paginación de la grilla en Proceso masivo |
| **Épica / carpeta** | `100-SistemaPartes` |
| **Clasificación** | MUST-HAVE (CC PQ #11) |
| **Estado** | Pendiente |
| **Última actualización** | 01/11/2026 |
| **HU base** | [HU-005-supervision-proceso-masivo](../../100-SistemaPartes/HU-005-supervision-proceso-masivo.md) |
| **SPEC origen** | [SPEC-005-update-cc-pq-11-2026-11-01-masivo-paginacion](../../../05-open-spec/updates/100-SistemaPartes/SPEC-005-update-cc-pq-11-2026-11-01-masivo-paginacion.md) |
| **TR relacionada** | [TR-005-update-cc-pq-11-2026-11-01-masivo-paginacion](../../../04-tareas/updates/100-SistemaPartes/TR-005-update-cc-pq-11-2026-11-01-masivo-paginacion.md) |
| **Origen** | CC PQ **#11** · 01/11/2026 · `00-ControlCalidad-PQ` |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

---

## Narrativa

Como **supervisor** en Proceso masivo  
quiero **pasar de página** en la grilla cuando el filtro devuelve más tareas que caben en una hoja  
para **revisar y seleccionar** tareas en todo el período filtrado, no solo en los primeros registros.

---

## Alcance incluido

- Paginación **remota** en `ProcesoMasivoPage` (`CustomStore` / `remoteOperations.paging`) contra `GET /api/v1/partes/tareas`.
- Sincronizar `page` y `pageSize` del query con el pager DevExtreme.
- Mantener leyenda **Total filtro** con `total` del envelope.
- Al cambiar filtros de pantalla: recargar desde página 1 y limpiar selección.
- Tests unitarios del mapeo página/`skip`/`take`; E2E humo opcional si ya existe spec masivo.

## Fuera de alcance

- Otros informes o cargas diarias.
- Totales de sumario sobre universo completo en pie de grilla.

---

## Criterios de aceptación

- [ ] **CA-11-01** Con más de una página de resultados, el paginador muestra **> 1** página y los datos cambian al avanzar.
- [ ] **CA-11-02** «Total filtro» refleja el `total` del API del filtro actual.
- [ ] **CA-11-03** Selector 10 / 20 / 40 actualiza el listado vía API.
- [ ] **CA-11-04** Agrupación por columnas no rompe la carga de datos.
- [ ] **CA-11-05** Tildar filas, cerrar/reabrir y aplicar campos siguen funcionando en páginas distintas de la primera.
