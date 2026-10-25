# SPEC-013 – Carga de paquetes de horas (compras / créditos)

Jira: PIAPARTES-43

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-013 |
| Título | Carga de paquetes de horas — ABM de compras (`es_tarea = false`) |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Especificado |
| Última actualización | 2026-10-20 |
| **A1** | [`SPEC-013-carga-paquetes-horas-A1-ambiguity-review.md`](./SPEC-013-carga-paquetes-horas-A1-ambiguity-review.md) — **Apto con observaciones** (2026-10-20) |
| HU relacionada(s) | [HU-013-carga-paquetes-horas](../../03-historias-usuario/100-SistemaPartes/HU-013-carga-paquetes-horas.md) |
| TR relacionada(s) | [TR-013-carga-paquetes-horas](../../04-tareas/100-SistemaPartes/TR-013-carga-paquetes-horas.md) |
| Depende de | [SPEC-001](./SPEC-001-modelo-datos-modulo.md) (R-MD-12 `es_tarea`), [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md) (supervisor), [SPEC-003](./SPEC-003-maestros-y-catalogos.md) (clientes usables), [SPEC-004](./SPEC-004-operacion-carga-diaria.md) (tramo duración, fecha funcional, patrones grilla/modal), [SPEC-006](./SPEC-006-consultas-dashboard-navegacion.md) (informe Paquete de Horas — signos; **no** redefine informe), [SPEC-008](./SPEC-008-asistente-ia-chat-documental.md) (BYOK / timeout LLM), [SPEC-010](./SPEC-010-smart-capture-carga-diaria.md) (patrón GEN-03; contrato de turno base) |
| Fuentes | [`16-carga-paquetes-horas.md`](../../02-producto/Sistema-Partes-IA/16-carga-paquetes-horas.md) (D-PH-01…11); [`14-smart-capture.md`](../../02-producto/Sistema-Partes-IA/14-smart-capture.md) (D-SC-01…20, superficie paquete); [`06-consultas-dashboard-y-navegacion.md`](../../02-producto/Sistema-Partes-IA/06-consultas-dashboard-y-navegacion.md) (§ informe cuenta corriente) |

**Adopción Framework (dominio Partes — no hay GEN de paquetes):** GEN-01 `ProcessPageLayout`, GEN-11 `ProcessDataGrid`, GEN-07 menú, GEN-20 envelope, GEN-03 `SmartCapturePanel` (**Must** en modal), GEN-16 BYOK. Persistencia negocio por **SP** (regla 74). **No** GEN-14 (Excel paquetes), **no** GEN-15 en esta pantalla v1.

---

## 1. Resumen ejecutivo

- **Problema:** el informe **Paquete de Horas** (SPEC-006) muestra consumo (tareas) y créditos (compras), pero hoy no existe un proceso operativo para registrar **movimientos de paquete** (`es_tarea = false`); el saldo solo refleja consumo hasta que haya créditos cargados.
- **Resultado esperado:** proceso web **propio** (menú hermano de Carga diaria) con ABM **grilla + modal** sobre `PQ_PARTES_REGISTRO_TAREA`, listando y persistiendo **solo** paquetes (`es_tarea = false`), **solo supervisor**, con **Smart Capture** en el modal (mismo patrón que SPEC-010, contrato de campos de paquete), sin alterar informes ni carga diaria.

---

## 2. Alcance

### 2.1 En alcance

- Pantalla web **Paquetes de horas** / **Carga de paquetes**: `ProcessPageLayout` + filtros de contexto + `ProcessDataGrid` + alta/edición en **Popup** (regla ABM modal §30).
- Filtros **Must** antes/durante listado: **fecha desde / fecha hasta** (periodo de la compra) y **cliente** (selector catálogo usable).
- Listado implícito **`es_tarea = false`**; alta/edición desde este proceso **fuerza** `es_tarea = false` (D-PH-01).
- Campos visibles de captura: **cliente**, **fecha**, **duración** (horas enteras de 8 a 240 inclusive), **observación** (obligatoria).
- Campos técnicos completados por el sistema al grabar (D-PH-02): `tipo_tarea_id` = tipo con `is_default`; `usuario_id` = asistente funcional del **supervisor que registra**; `sin_cargo`, `presencial`, `cerrado` = `false`.
- Alta, edición y eliminación con confirmación en baja; **sin** circuito `cerrado` que bloquee edición (D-PH-10).
- Errores de guardado **dentro del Popup** (regla 36 / SPEC-001 UI errores modal).
- **Smart Capture** (GEN-03) **Must** en modal alta/edición, debajo del formulario (D-PH-11): campos SC = cliente, fecha, duración, observación; sin tipo/asistente/bits/cerrado; ambigüedad cliente numerada; fecha futura confirmada antes de aplicar al draft; `save` → mismo API que Guardar.
- Gate de rol: **solo supervisor** (D-PH-05); permiso de menú **propio** (no reutilizar Carga diaria) (D-PH-06).
- Ítem menú en rama **Carga de Partes**, hermano de Carga diaria; `labelKey` + `id` jerárquico `pq_menus` (regla 76).
- i18n (5 locales) + `data-testid` estables; hint SC específico de paquete (no hint de tarea).
- Delimitación API: rechazar operaciones si el registro no es paquete o si el actor no es supervisor (D-PH-04).

### 2.2 Fuera de alcance

- DDL nuevo de saldos; el saldo es **derivado** del informe (SPEC-006), no tabla acumulada.
- Facturación, costeo, órdenes de compra ERP.
- Importación Excel de paquetes (GEN-14 / SPEC-009 aplica solo tareas).
- Proceso masivo (SPEC-005), Carga diaria (SPEC-004), informes/pivot (SPEC-006) — salvo consumo de datos ya cargados.
- Emitir PDF/mail del paquete (GEN-15 / SPEC-011).
- **Mobile** v1 (D-PH-08 / SPEC-007): el informe paquete sí en mobile; la **carga** no.
- Chat documental (SPEC-008) para grabar paquetes.
- SC en la grilla del listado (solo modal).
- Asistente no supervisor y cliente como operadores de este proceso.

---

## 3. Actores y contexto

| Actor | Carga de paquetes |
|-------|-------------------|
| Supervisor (`esSupervisor = true`) | **Sí** — alta, edición, eliminación, SC en modal |
| Asistente (`esSupervisor = false`) | **No** — menú/API deniegan |
| Cliente funcional | **No** — solo consulta efecto en informe si su perfil lo permite |

**Precondiciones:** sesión Partes (SPEC-002); catálogo de clientes usables (SPEC-003); tipo de tarea default existente en maestros.

---

## 4. Comportamiento funcional

### 4.1 Forma del proceso (web — D-PH-07)

1. El supervisor abre **Carga de Partes → Paquetes de horas**.
2. Aplica filtros **fecha desde / hasta** y opcionalmente acota **cliente**.
3. La grilla lista solo registros con `es_tarea = false` ∩ filtros ∩ universo permitido (supervisor: clientes/asistentes del universo SPEC-002; en la práctica operación centrada en cliente del filtro).
4. Desde la grilla: **Nuevo**, **Editar**, **Eliminar** (confirmación explícita en baja).
5. Alta/edición en **Popup** sobre el listado; formulario = fuente de verdad; SC debajo (§4.8).

**Anti-patrones (Must no):**

- Alta de paquete desde Carga diaria (`es_tarea = true` forzado allí).
- Listado mezclado con tareas.
- SC montado en la grilla o en el informe Paquete de Horas.

### 4.2 Filtros y listado

| Filtro | Obligatoriedad | Notas |
|--------|----------------|-------|
| `fechaDesde`, `fechaHasta` | **Must** acotar periodo de la compra | Sin rango aplicado, no listar universo histórico completo (mensaje i18n claro, patrón SPEC-004). **Default al abrir (A1):** `fechaDesde` = 1.er día del **mes calendario en curso** del sistema; `fechaHasta` = último día de ese mismo mes. |
| Cliente | Opcional en filtro | Vacío = todos los clientes **usables** del universo **supervisor** (SPEC-002). Con valor = acota al cliente. Solo clientes habilitados para **nuevas** compras en el selector de alta; movimientos históricos de cliente inhabilitado siguen visibles en listado si el filtro lo incluye. |

**Columnas orientativas (grilla):** cliente (descripción; código en column chooser), fecha, duración **`hh:mm`** (sin columna decimal Must en v1; ver A1), observación, quién registró (asistente del `usuario_id` persistido), auditoría de alta. **No** exponer `es_tarea` ni tipo de tarea.

**Paginación:** estándar DevExtreme (`ProcessDataGrid`), coherente con Carga diaria web.

### 4.3 Captura en modal (campos visibles)

| Campo | Regla |
|-------|--------|
| Cliente | Obligatorio; selector código + descripción. **Alta:** solo cliente usable. **Edición:** puede conservar `cliente_id` actual aunque el cliente esté inhabilitado; si cambia cliente, solo usables (A1). |
| Fecha | Obligatoria; fecha **funcional** del movimiento (no solo timestamp técnico). Si `fecha` > hoy → **advertencia** confirmable; **no** bloqueo duro en v1 (alineado producto §4.3; SC aplica confirmación antes de draft — §4.8) |
| Duración | Obligatoria; cantidad entera de horas entre **8 y 240 inclusive**; persistida como minutos equivalentes (`480` a `14400`), en múltiplos de 60; UI selector de horas enteras |
| Observación | Obligatoria; no vacía ni solo whitespace |

**No** se muestran ni capturan: tipo de tarea, asistente propietario, sin cargo, presencial, cerrado.

### 4.4 Persistencia y valores forzados (D-PH-01, D-PH-02, D-PH-03)

| Campo | Valor al grabar desde este proceso |
|-------|-------------------------------------|
| `es_tarea` | **Siempre `false` (`0`)** |
| `tipo_tarea_id` | Tipo de tarea con **`is_default`** (integridad FK; no negocio del paquete) |
| `usuario_id` | **Alta:** `asistenteId` del supervisor autenticado (auditoría «quién cargó»). **Update:** **no** recalcular; conservar el `usuario_id` ya persistido (A1). |
| `sin_cargo` | `false` |
| `presencial` | `false` |
| `cerrado` | `false` |

- **Update/delete:** solo si el registro existente tiene `es_tarea = false`; si no → rechazo API (404/422 según contrato TR).
- **Concurrencia:** optimistic lock `row_version` (mismo patrón SPEC-004); conflicto → **409** + refrescar.
- Acceso de negocio vía **SP** (MUST regla 74).

### 4.5 Edición, eliminación y validación en modal (D-PH-04, D-PH-10)

| Acción | Condición |
|--------|-----------|
| Editar | Supervisor; registro es paquete |
| Eliminar | Supervisor; confirmación explícita; registro es paquete |

No hay estado `cerrado` que impida corregir una carga errónea. El informe refleja cambios al reconsultar (SPEC-006 sin modificación).

Errores al **Guardar** con modal abierto → alerta **dentro** del Popup (`{prefijo}FormError`); al cancelar/cerrar → limpiar error de formulario.

### 4.6 Relación con informe Paquete de Horas (D-PH-03)

1. Supervisor carga/edita/elimina paquete en este proceso.
2. Usuario autorizado abre **Informes → Paquete de horas** (SPEC-006 §4.3b).
3. El informe **no** filtra por `es_tarea`; mezcla tareas y paquetes.
4. Convención vigente: tarea **suma** minutos; paquete **resta** minutos; fila Saldo inicial + columna Saldo en grilla; pivot **sin** Saldo.

Este SPEC **no** cambia columnas, filtros ni signos del informe.

### 4.7 Delimitación API (capa servidor)

- Cliente funcional y asistente no supervisor: **403** en endpoints de este proceso.
- List/get filtrado: `es_tarea = false` siempre.
- Insert/update: forzar `es_tarea = false` y valores §4.4 aunque el cliente envíe otros flags.
- Operar por `id` sobre registro con `es_tarea = true` → **422** con clave envelope (p. ej. `partes.paquete.notPaquete`); no mutar la tarea (A1).

### 4.8 Smart Capture en modal de paquete (Must — D-PH-11)

Adopta **GEN-03** y reutiliza el contrato de turno / keywords / BYOK / modalidades de SPEC-010, con la especialización siguiente:

| Tema | En paquetes |
|------|-------------|
| Ubicación | Solo Popup alta/edición; **debajo** del formulario |
| Quién | **Solo supervisor** (mismo gate que el proceso) |
| Campos que SC puede completar | Cliente, fecha, duración, observación |
| Campos que SC **no** pide ni pisa | Tipo, asistente, sin cargo, presencial, cerrado (§4.4 los fija el sistema al grabar) |
| Ambigüedad cliente | Lista numerada si >1 coincidencia (D-SC-05); **sin** lookup asistente/tipo |
| Fecha futura | Confirmación en hilo **antes** de aplicar al draft (D-SC-07 / D-SC-16) |
| Hint | Clave i18n **de paquete** (`partes.paquete.smartCapture.*` o equivalente TR) |
| Grabación | Action `save` → **mismo** POST/PUT que botón Guardar del modal de paquetes |
| Sin BYOK | Gate «Ir a Preferencias»; ABM manual sigue disponible |
| Endpoint turno | **Mismo** que SPEC-010 (ruta Partes existente); BE discrimina paquete vs tarea vía contexto en `draftContext` (p. ej. `processKind = 'paquete'` — nombre exacto en TR) |

**No** aplica R-SC-04 (cerrado): paquetes no usan circuito de cierre de tareas.

Reglas numeradas Smart Capture paquete (especialización; el resto hereda SPEC-010):

| ID | Regla |
|----|--------|
| R-PH-SC-01 | Panel SC solo en modal de paquete; no en grilla ni informes. |
| R-PH-SC-02 | Solo supervisor; cliente/asistente no supervisor sin panel usable. |
| R-PH-SC-03 | Campos SC limitados a §4.8; no proponer tipo/asistente/bits. |
| R-PH-SC-04 | Hint i18n distinto del de tarea (D-PH-11). |
| R-PH-SC-05 | Persistencia vía mismo API que Guardar; validación §4.3–4.4 sobre draft completo. |

### 4.9 Reglas numeradas (proceso)

| ID | Regla |
|----|--------|
| R-PH-01 | Alta/edición desde este proceso fuerza `es_tarea = false`. |
| R-PH-02 | `tipo_tarea_id` = default sistema; no UI. |
| R-PH-03 | Duración = horas compradas; informe conserva signos SPEC-006. |
| R-PH-04 | Solo opera registros-paquete; no toca tareas. |
| R-PH-05 | Solo supervisor opera el proceso. |
| R-PH-06 | Menú propio en Carga de Partes; permiso distinto de Carga diaria. |
| R-PH-07 | ABM grilla + modal; filtros periodo y cliente. |
| R-PH-08 | Fuera de mobile v1. |
| R-PH-09 | Sin importación Excel de paquetes. |
| R-PH-10 | Sin `cerrado` bloqueante; edición/baja con confirmación en eliminar. |
| R-PH-11 | Smart Capture Must en modal (GEN-03); contrato §4.8. |
| R-PH-12 | Listado filtra `es_tarea = false`; carga diaria no muestra ni crea paquetes (coherencia SPEC-004 R-OP-13/14). |
| R-PH-13 | Errores de guardado en contexto modal (regla 36). |
| R-PH-14 | Acceso de negocio vía SP; envelope GEN-20. |
| R-PH-15…16 | Layouts GEN-11 compartidos; export Excel no Must v1 (§4.10). |
| R-PH-17 | Default filtros fecha = mes calendario en curso al abrir (§4.2). |
| R-PH-18 | `usuario_id` solo en alta; update conserva registrador original (§4.4). |
| R-PH-19 | Id de tarea en API paquete → 422 `notPaquete` (§4.7). |

### 4.10 Plantillas de grilla (GEN-11 — A1)

Carga de paquetes: adoptar GEN-11 como Carga diaria (SPEC-004 §4.10). UI/motor = `ProcessDataGrid` + `GridLayoutToolbar`. No reimplementar.

| ID | Norma |
|----|--------|
| R-PH-15 | Par `proceso` + `gridId` propios del proceso (p. ej. `partes.carga.paquetes` / `cargaPaquetes`); plantillas **compartidas** entre usuarios del mismo par (mismo contrato GEN-11 que SPEC-004 R-OP-15…18). |
| R-PH-16 | Export Excel del listado: **no Must** en v1; si `canExport` está deshabilitado, no viola este SPEC. |

---

## 5. Criterios verificables

- [ ] Asistente no supervisor no ve menú / recibe 403 en API de paquetes.
- [ ] Cliente funcional no opera este proceso (403).
- [ ] Al abrir, filtros de fecha precargados con el **mes calendario en curso**.
- [ ] Grilla lista solo `es_tarea = false` con filtros de fecha (y cliente si aplica); cliente vacío = todos los usables del universo supervisor.
- [ ] Alta persiste `es_tarea = false`, tipo default, `usuario_id` = asistente del supervisor, bits en false.
- [ ] Editar/eliminar rechazados para `id` de tarea (`es_tarea = true`) con **422** envelope.
- [ ] Tras editar paquete, `usuario_id` del registrador original **no** cambia.
- [ ] Campos obligatorios: cliente, fecha, duración válida, observación no vacía.
- [ ] Fecha futura: advertencia en UI manual; SC confirma antes de aplicar al draft.
- [ ] Error de guardado visible **dentro** del Popup; se limpia al cancelar.
- [ ] Tras alta, el informe Paquete de Horas muestra movimiento que **resta** minutos (misma convención SPEC-006).
- [ ] SC visible en modal para supervisor; hint de paquete; sin SC en grilla.
- [ ] SC `save` y Guardar usan el mismo endpoint y validaciones.
- [ ] Sin BYOK: gate Preferencias; formulario manual usable.
- [ ] i18n + `data-testid` en pantalla y formulario modal.
- [ ] Carga diaria no lista registros creados como paquete (mismo periodo/filtros).

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | SP list/filter/upsert/delete paquetes (`es_tarea = 0`); validación espejo §4.3–4.7; gate supervisor; turno SC = **mismo** endpoint SPEC-010 + rama `processKind` paquete en servicio de turno |
| Frontend | Feature `cargaPaquetes` (filtros + `ProcessDataGrid` + Popup); validador propio de horas enteras 8–240 y fecha; `SmartCapturePanel` + draft mapper paquete; ruta p. ej. `/partes/carga-paquetes` |
| Menú | Nuevo ítem bajo Carga de Partes; `labelKey` p. ej. `menu.partes.cargaPaquetes`; `proceso` p. ej. `partes.carga.paquetes`, `gridId` p. ej. `cargaPaquetes` |
| Permisos | Matriz menú/rol distinta de `partes.carga.diaria` |
| Tests | Feature API supervisor vs asistente; Vitest validación formulario; E2E humo alta paquete; SC opcional con mock turno |
| Mobile | **No** montar ruta ni policy mobile (SPEC-007) |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Turno SC paquete vs tarea | **Cerrado A1:** mismo endpoint; discriminador en `draftContext` |
| Default filtros fecha al abrir | **Cerrado A1:** mes calendario en curso (§4.2) |
| Export Excel / columna decimal | **Cerrado A1:** no Must v1 (§4.10) |
| Tipo default inexistente | Error de instalación; seed maestros debe garantizar `is_default` (SPEC-003) |
| Código menú / permiso seed | TR-013 (regla 76); no bloquea HU |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-20 | Versión inicial (Parte A) — desde `16-carga-paquetes-horas.md` (D-PH-01…11) + adopción GEN-01/07/11/03/16 y SP. |
| 2026-10-20 | Parte A1: revisión ambigüedad — **Apto con observaciones**; cierres default mes, cliente filtro, `usuario_id` en update, 422 notPaquete, GEN-11, SC mismo endpoint. |
| 2026-10-21 | Parte B+B1: enlazada [HU-013](../../03-historias-usuario/100-SistemaPartes/HU-013-carga-paquetes-horas.md). |
| 2026-10-21 | Parte C+C1: enlazada [TR-013](../../04-tareas/100-SistemaPartes/TR-013-carga-paquetes-horas.md). Estado → Especificado. |

---

**Trazabilidad:** siguiente paso SDD **Parte D1/D** (implementar TR-013). Informe cuenta corriente: SPEC-006 (sin cambios). Smart Capture genérico: SPEC-010 + Framework GEN-03.
