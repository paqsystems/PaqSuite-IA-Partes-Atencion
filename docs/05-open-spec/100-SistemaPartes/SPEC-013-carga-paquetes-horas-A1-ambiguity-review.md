# Revisión de ambigüedad — SPEC-013 — Carga de paquetes de horas

| Campo | Valor |
|-------|--------|
| **SPEC** | [`SPEC-013-carga-paquetes-horas.md`](./SPEC-013-carga-paquetes-horas.md) |
| **Paso** | A1 |
| **Fecha** | 2026-10-20 |
| **Origen** | Parte A 2026-10-20 · producto [`16-carga-paquetes-horas.md`](../../02-producto/Sistema-Partes-IA/16-carga-paquetes-horas.md) |

## Resultado general

- Estado: **Apto con observaciones**

El SPEC es implementable sin bifurcar el dominio (`es_tarea = false` vs carga diaria) ni el informe (SPEC-006). Las observaciones menores se **cerraron en el SPEC** (§4.2, §4.4, §4.10, §6) salvo detalle de naming de menú/permiso (TR).

## Ambigüedades críticas

- Ninguna que impida dos ABM funcionalmente distintos (mismo menú, mismos filtros, misma persistencia forzada).

## Ambigüedades menores (cerradas en SPEC)

| Tema | Riesgo | Cierre A1 (2026-10-20) |
|------|--------|-------------------------|
| Default **fecha desde / hasta** al abrir | Listado vacío vs mes completo vs solo hoy | **Mes calendario en curso:** `fechaDesde` = 1.er día del mes del sistema; `fechaHasta` = último día del mismo mes. El usuario puede acotar o ampliar. (Distinto de carga diaria = solo hoy; coherente con compras comerciales por periodo.) |
| **Cliente** vacío en filtro de grilla | ¿Un solo cliente obligatorio? | **Opcional:** vacío = todos los clientes **usables** del universo supervisor (SPEC-002); con valor = acota al cliente elegido. |
| **`usuario_id` en edición** | ¿Se pisa con el supervisor actual? | **Solo en alta** se setea al `asistenteId` del supervisor que registra. En **update** se **conserva** el `usuario_id` existente (auditoría del registrador original). |
| Cliente **inhabilitado** en edición | ¿Se puede guardar sin cambiar cliente? | **Alta:** solo clientes usables. **Edición:** se permite mantener el `cliente_id` actual aunque el cliente esté inhabilitado; si el usuario cambia cliente, solo usables. |
| Operar `id` de **tarea** (`es_tarea = true`) | 404 vs 422 | **422** con clave envelope de negocio (p. ej. `partes.paquete.notPaquete`); no mutar la tarea. |
| **Export Excel** en grilla | Paridad con carga diaria | **No Must** v1; TR puede omitir `canExport` o dejarlo deshabilitado sin violar producto. |
| Columna **duración decimal** | Paridad SPEC-004 | **No Must** en paquetes v1; grilla presenta **`hh:mm`** (producto §5.1). |
| **Plantillas GEN-11** | ¿Grilla sin layouts? | **Sí** comparten contrato GEN-11 (`proceso` + `gridId` propios; listado compartido como SPEC-004 §4.10). |
| **Turno Smart Capture** | Endpoint duplicado vs discriminador | **Mismo** endpoint de turno Partes que SPEC-010; el BE discrimina con campo de contexto (p. ej. `draftContext.processKind = 'paquete'`) y prompt/acciones sin tipo/asistente. TR fija el nombre exacto del campo. |
| Keywords SC «guardar **tarea**» | Wording en paquete | Hereda SPEC-010; sinónimos de grabación aplican igual; el `save` del FE llama API de paquete. Opcional i18n de reply; no bloquea. |

## Supuestos detectados

- Supervisor ve paquetes de **todo** el universo supervisor (no hay filtro por «solo mis cargas»).
- No hay `cerrado` ni edición masiva; coherente con D-PH-10.
- `tipo_tarea_id` default debe existir (SPEC-003); si falta, error de instalación, no UI.
- Mobile no monta ruta ni SC (D-PH-08).

## Preguntas para decisión humana

- Ninguna **bloqueante** tras los cierres de la tabla anterior.
- **Opcional (no bloquea B):** código estable de ítem menú / permiso (`menu_codigo`, seed `pq_menus`) — lo fija TR-013 con regla 76.

## Recomendaciones de ajuste del SPEC

- Aplicadas en el SPEC principal (§4.2 defaults, §4.3 edición cliente, §4.4 `usuario_id`, §4.7 422, §4.10 layouts, §6 SC discriminator, §7 riesgos).

## Veredicto

- Puede pasar a **HU (Parte B):** **Sí**
