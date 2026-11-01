# SPEC-005-update – CC PQ #11 (01/11/2026) — Paginación grilla Proceso masivo

## Metadatos

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-005-update-cc-pq-11-2026-11-01-masivo-paginacion |
| **Título** | Proceso masivo — paginación alineada al total filtrado del API |
| **Épica / carpeta** | `100-SistemaPartes` |
| **Estado** | Pendiente |
| **Última actualización** | 01/11/2026 |
| **SPEC base** | [SPEC-005-supervision-proceso-masivo](../../100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) §4.3, §4.3b |
| **HU-update** | [HU-005-update-cc-pq-11-2026-11-01-masivo-paginacion](../../../03-historias-usuario/updates/100-SistemaPartes/HU-005-update-cc-pq-11-2026-11-01-masivo-paginacion.md) |
| **TR-update** | [TR-005-update-cc-pq-11-2026-11-01-masivo-paginacion](../../../04-tareas/updates/100-SistemaPartes/TR-005-update-cc-pq-11-2026-11-01-masivo-paginacion.md) |
| **Origen** | `00-ControlCalidad-PQ` · CC **#11** · 01/11/2026 |

---

## 1. Resumen ejecutivo

- **Problema:** en **Proceso masivo**, la leyenda **Total filtro** muestra el `total` del API (p. ej. 418) pero el paginador DevExtreme solo ofrece la página **1**, porque la grilla pagina en cliente sobre la **primera página** ya cargada.
- **Resultado esperado:** el supervisor puede **navegar todas las páginas** del universo filtrado; cada cambio de página solicita al API la página correspondiente; el paginador refleja `total` del envelope.

---

## 2. Alcance

### 2.1 En alcance

- Pantalla **Proceso masivo** web (`partes.masivo`, `/partes/proceso-masivo`).
- Listado `GET /api/v1/partes/tareas` con query `page` + `pageSize` sincronizados con el pager DevExtreme (**paginación remota** en la grilla `ProcessDataGrid`).
- Leyenda `partes.common.totalFiltro` sigue mostrando el **`total`** global del filtro (coherente con paginador).
- Tamaños de página del framework (10 / 20 / 40) recalculan páginas vía API.
- Tras cambio de filtros (fechas, cliente, asistente, estado): volver a página 1, limpiar selección (comportamiento actual).
- Selección múltiple, «seleccionar todos del filtro» (`listTareaIds`), cierre/aplicar campos: sin cambio de contrato (CA-11-05).
- Agrupación / column chooser / layouts GEN: sobre datos de la **página actual** (limitación DevExtreme con paging remoto; no regresión respecto a mostrar solo página 1 con total engañoso).

### 2.2 Fuera de alcance

- Consulta detallada (ya carga universo con `pageSize: 0`).
- Carga diaria (otro flujo de listado).
- Cambios de contrato API / SP del listado (paginación server-side **ya existe**).
- Mobile (masivo excluido).
- Totalización de duración sobre **todo** el universo en pie de grilla con paging remoto (mejora futura si producto lo pide).

---

## 3. Comportamiento funcional

1. Supervisor aplica filtros; la grilla carga página 1 con `pageSize` por defecto (20).
2. Si `total > pageSize`, el paginador muestra páginas **1 … N**; al ir a página *k*, el host llama `listTareas` con `page = k` y el `pageSize` activo.
3. **Total filtro** = `total` del último listado exitoso del filtro actual.
4. Cambio de `pageSize` en el pager → nueva petición con `page` recalculada (DevExtreme reinicia o mantiene índice según comportamiento nativo; Must no quedar con una sola página cuando `total` lo permite).

---

## 4. Criterios verificables

- [ ] **CA-11-01** Paginador con `total > pageSize` permite llegar a la última página y los ids de filas difieren de la página 1.
- [ ] **CA-11-02** «Total filtro» coincide con `total` del API para el filtro vigente.
- [ ] **CA-11-03** Cambiar 10 / 20 / 40 dispara requests con `pageSize` acorde y páginas visibles coherentes.
- [ ] **CA-11-04** Agrupación por columna no produce error de red ni pantalla vacía sin mensaje.
- [ ] **CA-11-05** Selección y acciones masivas operativas en páginas distintas de la primera.

---

## 5. Trazabilidad SPEC-005 base

| SPEC-005 | Este update |
|----------|-------------|
| §4.3 descubrimiento + listado acotado | Refuerza que el **listado** debe ser navegable por páginas |
| §4.3 «todas las páginas» en seleccionar todos | Complementa: navegación manual página a página |
| §4.3b grilla Framework | Mantiene `ProcessDataGrid`; paging remoto es patrón DevExtreme permitido |
