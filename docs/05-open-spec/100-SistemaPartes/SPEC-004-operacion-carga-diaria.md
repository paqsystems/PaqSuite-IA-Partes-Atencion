# SPEC-004 – Operación / carga diaria de tareas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-004 |
| Título | Operación / carga diaria de tareas |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Finalizado |
| Última actualización | 2026-08-01 |
| HU relacionada(s) | [HU-004-operacion-carga-diaria](../../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) |
| TR relacionada(s) | [TR-004-operacion-carga-diaria](../../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) |
| Depende de | [SPEC-001](./SPEC-001-modelo-datos-modulo.md), [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md), [SPEC-003](./SPEC-003-maestros-y-catalogos.md) (§4.7 universo tipos) |
| Fuentes | [`05-operacion-diaria-y-supervision.md`](../../02-producto/Sistema-Partes-IA/05-operacion-diaria-y-supervision.md), [`01-vision-y-alcance.md`](../../02-producto/Sistema-Partes-IA/01-vision-y-alcance.md), [`09-modelo-datos-tecnico.md`](../../02-producto/Sistema-Partes-IA/09-modelo-datos-tecnico.md) |

---

## 1. Resumen ejecutivo

- **Problema:** el valor del módulo está en registrar dedicación real con baja fricción y reglas claras (propiedad, duración, cerrado, catálogos usables).
- **Resultado esperado:** proceso web de **carga diaria desde grilla previamente filtrada** (alta / edición / baja) sobre `PQ_PARTES_REGISTRO_TAREA`, coherente con identidad (SPEC-002) y catálogos (SPEC-003), sin el proceso masivo de supervisión (SPEC-005).

---

## 2. Alcance

### 2.1 En alcance

- Pantalla web de carga diaria: **DataGrid de trabajo** + filtros previos obligatorios.
- Insertar, editar y eliminar registros de tarea según rol y estado `cerrado`.
- Campos de negocio: `fecha`, `cliente_id`, `tipo_tarea_id`, `duracion_minutos`, `observacion`, `sin_cargo`, `presencial`, `usuario_id` (propietario), `cerrado` (con reglas §4.6).
- Delimitación implícita por `es_tarea = true`: la carga diaria solo lista y solo persiste registros de tarea (no compras/movimientos de paquete de horas, SPEC-001 R-MD-12).
- Validaciones de captura (fecha, duración múltiplo del tramo parametrizado — default 15 —, observación no vacía, catálogos usables, universo de tipos por cliente).
- Delimitación de filas visibles según `tipoFuncional` / `esSupervisor` (SPEC-002).
- Selección de asistente propietario cuando opera un **supervisor**.
- Advertencia (no bloqueo) ante fecha futura.
- **Complemento IA fuera del MVP** de carga diaria (solo carga manual); evolutivo posterior sin sustituir confirmación ni validaciones.
- i18n + `data-testid`; patrón DevExtreme (grilla + editores).

### 2.2 Fuera de alcance

- DDL (SPEC-001), gate login (SPEC-002), ABM maestros (SPEC-003).
- **Proceso masivo** cerrar/reabrir conjunto (SPEC-005).
- Consultas agrupadas, dashboard, pivots (SPEC-006).
- Carga **mobile** individual / kardex (SPEC-007); este SPEC es **web**.
- Cliente funcional: no carga tareas.
- Facturación, aprobación formal, automatizaciones IA (incluida ayuda en pantalla de carga en este MVP).
- **Importación masiva desde Excel** → [SPEC-009](./SPEC-009-importacion-partes-excel.md) (no forma parte de este SPEC).
- Exportación Excel como Must del MVP de carga.

---

## 3. Actores y contexto

| Actor | Carga diaria |
|-------|----------------|
| Asistente (`esSupervisor = false`) | Sí: solo sus tareas (`usuario_id = asistenteId`) |
| Supervisor (`esSupervisor = true`) | Sí: puede ver/filtrar terceros y elegir propietario |
| Cliente | No (ruta/menú no expuestos; API deniega) |

Precondiciones: sesión con `resultado.partes`; catálogos usables disponibles.

---

## 4. Comportamiento funcional

### 4.1 Forma del proceso (web)

1. El usuario abre la pantalla de carga diaria.
2. **Aplica filtros** (mínimo §4.2) y confirma / refresca el contexto.
3. La grilla muestra solo tareas del universo permitido ∩ filtros.
4. Desde la grilla: insertar, editar, eliminar (según permisos de fila).
5. No es un formulario unitario aislado como único modo de carga web.

### 4.2 Filtros previos (mínimo)

| Filtro | Asistente | Supervisor | Notas |
|--------|-----------|------------|-------|
| Rango de fechas (`fechaDesde`, `fechaHasta`) o día único | Obligatorio acotar | Obligatorio acotar | **Default al abrir:** día del sistema (`fechaDesde` = `fechaHasta` = hoy). El usuario puede ampliar el rango. |
| Cliente | Opcional | Opcional | Solo clientes usables |
| Asistente propietario | N/A (fijo = yo) | Opcional | Vacío = todos los asistentes usables del universo supervisor |
| Solo abiertas / solo cerradas / todas | Opcional | Opcional | **Default al abrir: todas.** Las cerradas se listan en solo lectura (no editar/eliminar en flujo ordinario). |

Sin filtros de fecha aplicados, la grilla **no** carga el universo completo histórico: exige contexto acotado (mensaje i18n si intenta buscar sin fechas).

### 4.3 Propiedad (`usuario_id`) y columna Asistente

| Quién opera | Columna / campo Asistente en grilla | Alta | Edición de propietario |
|-------------|--------------------------------------|------|------------------------|
| Asistente | Visible; valor = su código/nombre; **no editable** (solo su `asistenteId`) | `usuario_id` = su `asistenteId` (fijo) | No cambia propietario |
| Supervisor | Visible y **editable**; puede elegir **cualquier asistente activo** (usable: `activo` y no `inhabilitado`) | Default = él mismo; puede asignar otro asistente usable | Puede reasignar a otro asistente usable |

- En UI de supervisor el selector muestra código + nombre (norma catálogos).
- Intentar persistir otro `usuario_id` siendo asistente no supervisor → 403 en API.

### 4.4 Campos y validaciones de captura

| Campo | Regla |
|-------|--------|
| `fecha` | Obligatoria; fecha de **negocio** (no solo `created_at`). Presentación amigable (locale). Si `fecha` > fecha del sistema → **advertencia** confirmable; **no** bloqueo duro. |
| `cliente_id` | Obligatorio; solo cliente usable (SPEC-003). Al cambiar cliente, recalcular tipos disponibles; si el `tipo_tarea_id` actual no pertenece al nuevo universo → **limpiar** `tipo_tarea_id` (queda vacío hasta nueva elección). |
| `tipo_tarea_id` | Obligatorio (no nulo/vacío al grabar); ∈ universo SPEC-003 §4.7 para ese cliente. Default sugerido al alta: tipo con `is_default = 1` si está en el universo. Tras limpiar por cambio de cliente, no se puede grabar hasta elegir tipo válido. |
| `duracion_minutos` | Entero; obligatorio; `> 0`; **múltiplo del tramo** parametrizado en `PQ_PARAMETROS_GRAL` (clave p. ej. `PartesDuracionTramoMin`; **default 15**); máximo **1440** (24 h). Persiste minutos. **UI captura:** selector de tramos en formato **`hh:mm`** (value interno = minutos). |
| `observacion` | Obligatoria; no vacía ni solo whitespace. |
| `sin_cargo` | Bit; **default `0` (false)** en alta y en UI nueva fila. No es “dato faltante”: siempre tiene valor. Visible como columna en grilla (column chooser). |
| `presencial` | Bit; **default `0` (false)** en alta y en UI nueva fila. No es “dato faltante”: siempre tiene valor. Visible como columna en grilla (column chooser). |
| `usuario_id` | Obligatorio; propietario (asistente fijo / supervisor elige). |
| `cerrado` | Ver §4.6. Alta ordinaria → `0`. |
| `es_tarea` | Implícito; no es captura visible del usuario. Alta/edición desde esta pantalla **siempre** persiste `es_tarea = 1` (no editable en este proceso). |

**Grabación:** no se puede insertar/actualizar si falta **cualquiera** de los datos solicitados obligatorios (`fecha`, `cliente_id`, `tipo_tarea_id`, `duracion_minutos`, `observacion`, `usuario_id`) o si fallan las reglas de dominio anteriores. Validación en UI y en API/SP.

Mensajes: envelope + claves i18n `partes.tarea.*` (validación / permiso / cerrado).

### 4.5 Listado, edición y eliminación

- **Listar:** API paginada/filtrada; solo filas del universo del actor (§3) ∩ filtros ∩ `es_tarea = 1`. **UI web:** paginación estándar DevExtreme (no virtual scroll como modo principal del MVP).
- **Columnas de presentación (grilla carga diaria):**
  - **Cliente:** descripción (`clienteNombre`); código disponible vía column chooser (`clienteCode`).
  - **Tipo de tarea:** descripción (`tipoTareaDescripcion`); código disponible vía column chooser (`tipoTareaCode`).
  - **Sin cargo** y **Presencial:** columnas booleanas disponibles (visibles por defecto; ocultables con column chooser).
  - **Duración:** celdas en **`hh:mm`**; exposición numérica en grilla como **horas decimales** (`duracionHoras = duracion_minutos / 60`) para permitir **sumatoria** DevExtreme (pie / menú contextual). Persistencia y API siguen en minutos. Columna técnica `duracionMinutos` oculta por defecto (chooser).
- **Insertar / actualizar:** validar §4.4; persistir vía SP (MUST). En **update**, el cliente envía `rowVersion` leído en el listado; si no coincide → **409** (conflicto); UI invita a refrescar.
- **Eliminar:** solo si `cerrado = 0` y el actor tiene derecho sobre la fila (propia o supervisor); también con `rowVersion` (mismo criterio 409).
- Tarea `cerrado = 1`: **no** editar campos de negocio ni eliminar en el flujo ordinario (lectura en grilla permitida).
- **Duración en UI:** selector de tramos etiquetados en **`hh:mm`**; validación contra el tramo de `PQ_PARAMETROS_GRAL`.
- **Concurrencia:** optimistic lock vía `PQ_PARTES_REGISTRO_TAREA.row_version` (SPEC-001 / SPEC-005).

### 4.6 Estado `cerrado` (circuito ordinario)

| Acción | Asistente | Supervisor |
|--------|-----------|------------|
| Editar / eliminar si `cerrado = 0` | Solo propias | Propias y de terceros (universo supervisor) |
| Editar / eliminar si `cerrado = 1` | No | No (salvo proceso masivo SPEC-005) |
| Marcar `cerrado = 1` (fila individual) | No en MVP de este SPEC | Sí (acción explícita) |
| Reabrir `cerrado = 0` (fila individual) | No | Sí (acción explícita) |

El **proceso masivo** sobre selección múltiple → **SPEC-005** (misma semántica de `cerrado`, otra UX).

### 4.7 Delimitación API (capa 1)

- Asistente no supervisor: toda lectura/escritura fuerza `usuario_id = asistenteId` de sesión; intentar otra propiedad → 403.
- Supervisor: puede operar cualquier `usuario_id` de asistente usable; no inventar ids inexistentes/inhabilitados.
- Cliente: 403 en endpoints de carga.
- Backend **no** confía solo en filtros de UI.

### 4.8 Complemento IA

- **Fuera del MVP de este SPEC:** no hay integración IA en la pantalla de carga diaria (ni botón/panel productivo, ni endpoint de propuesta).
- La carga **manual** es el único circuito del MVP; no se bloquea por ausencia de proveedor IA.
- Evolutivo posterior: proponer/completar campos con confirmación del usuario (misma semántica que validaciones §4.4), sin sustituir el circuito normal.

### 4.9 Reglas numeradas

| ID | Regla |
|----|--------|
| R-OP-01 | Carga web = grilla filtrada; no formulario unitario como único modo. |
| R-OP-02 | Cliente funcional no carga; API deniega. |
| R-OP-03 | Asistente solo opera tareas propias; columna Asistente fija a su código. Supervisor: columna Asistente editable con cualquier asistente activo/usable. |
| R-OP-04 | Filtro de fechas obligatorio antes de listar; **default al abrir = día del sistema**. |
| R-OP-05 | `duracion_minutos` > 0, múltiplo del tramo (`PQ_PARAMETROS_GRAL`, default **15**), ≤ 1440. UI captura = selector de tramos en **`hh:mm`**. |
| R-OP-05b | Listado carga diaria: paginación estándar DevExtreme. |
| R-OP-05c | Grilla: Cliente y Tipo de tarea muestran **descripción**; códigos opcionales vía column chooser. |
| R-OP-05d | Grilla: columnas **Sin cargo** y **Presencial** disponibles. |
| R-OP-05e | Grilla: duración visible en **`hh:mm`**; sumatoria sobre horas decimales (`duracionHoras`); API/DB en minutos. |
| R-OP-06 | `observacion` obligatoria (no blank). |
| R-OP-07 | Cliente/tipo usables; tipo ∈ universo del cliente (SPEC-003). Al cambiar cliente con tipo fuera de universo → limpiar tipo; grabar con tipo vacío → rechazo. |
| R-OP-07b | Alta/edición rechazada si falta cualquier campo obligatorio de captura (§4.4). `sin_cargo` / `presencial` default `0`. |
| R-OP-08 | Fecha futura → advertencia, no bloqueo. |
| R-OP-09 | `cerrado = 1` → sin edición/eliminación ordinaria. |
| R-OP-10 | Cerrar/reabrir individual: solo supervisor (MVP); masivo → SPEC-005. |
| R-OP-11 | Acceso de negocio vía SP (MUST). |
| R-OP-11b | Update/delete: optimistic lock `row_version`; conflicto → 409 + refrescar. |
| R-OP-12 | IA **fuera del MVP** de carga diaria; no bloquea ni es requisito. Evolutivo: complementar sin sustituir confirmación ni validaciones. |
| R-OP-13 | Listado de carga diaria filtra implícitamente `es_tarea = 1`; no muestra compras/movimientos de paquete de horas. |
| R-OP-14 | Alta/edición desde carga diaria persiste siempre `es_tarea = 1` (no editable por el usuario en este proceso). |

---

## 5. Criterios verificables

- [ ] Asistente lista solo sus tareas; columna Asistente fija a su código; no puede crear con otro `usuario_id`.
- [ ] Supervisor ve columna Asistente editable y puede asignar cualquier asistente activo/usable.
- [ ] Cliente autenticado recibe 403 en APIs de carga / no ve menú de carga.
- [ ] Alta rechaza duración no múltiplo del tramo (default 15), 0 y >1440; acepta valores válidos (p. ej. 15, 60, 1440 con tramo 15).
- [ ] UI duración = selector tramos en **`hh:mm`**; grilla con paginación DevExtreme.
- [ ] Grilla muestra descripción de Cliente y Tipo de tarea; columnas Sin cargo y Presencial disponibles; duración en hh:mm con sumatoria en horas decimales.
- [ ] Alta rechaza observación vacía; rechaza cliente/tipo inhabilitados o tipo fuera de universo.
- [ ] Fecha futura muestra advertencia y permite confirmar.
- [ ] Tarea cerrada no se edita ni elimina en flujo ordinario; supervisor puede cerrar/reabrir una fila.
- [ ] Al abrir carga diaria, filtros de fecha precargados con el día del sistema.
- [ ] Sin filtro de fechas, la UI no dispara listado amplio (mensaje claro).
- [ ] Al cambiar cliente en edición, si el tipo no está en el nuevo universo se limpia; grabar con tipo vacío se rechaza.
- [ ] Alta/edición rechaza si falta cualquier obligatorio; `sin_cargo` y `presencial` default `0` en alta.
- [ ] i18n + `data-testid` en pantalla de carga.
- [x] Tras crear/editar desde carga, `esTarea` queda `true`; un registro con `es_tarea = 0` no aparece en el listado (mismo filtro de fechas).

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | SP list/filter/upsert/delete/setCerrado; validación servidor espejo de §4.4–4.7 |
| Frontend | Feature carga diaria (filtros + DataGrid paginado DX + editores; duración hh:mm; columnas descripción + bits; sumatoria horas decimales); selectores catálogo SPEC-003; leer tramo desde param GRAL |
| Menú | Ítem “Carga diaria” para asistente/supervisor |
| Tests | Feature API por rol; Vitest validadores duración/fecha; E2E humo alta tarea |
| Params | Seed `PartesDuracionTramoMin` (programa `Partes`) default `15` |
| IA | **Fuera del MVP** (sin hook/UI/endpoint); evolutivo |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Default exacto de rango de fechas al abrir | **Cerrado:** día del sistema. |
| Asistente cierra/reabre tareas | **Cerrado:** no; solo supervisor (individual y masivo). |
| Atomicidad masiva | SPEC-005 |
| Mobile | Misma reglas de dominio; otra UX en SPEC-007 |
| Layouts persistentes de grilla | Framework GEN si aplica; no bloquea |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A) — carga diaria grilla; masivo diferido a SPEC-005. |
| 2026-07-30 | A1: apto con observaciones (default rango fechas; cierre individual solo supervisor). |
| 2026-07-30 | A1 cierre: default fechas carga = día del sistema. |
| 2026-07-30 | A1 cierre: cerrar/reabrir individual y masivo = solo supervisor. |
| 2026-07-30 | A1 cierre: columna Asistente en grilla (fija vs editable según rol). |
| 2026-07-30 | Batch HU: filtro estado default = todas; cerradas no editables. |
| 2026-07-30 | Batch HU: cambio cliente limpia tipo; no grabar sin obligatorios; bits default 0. |
| 2026-07-30 | Batch HU: complemento IA fuera del MVP de carga diaria. |
| 2026-07-30 | Batch HU: duración = tramos+editable; tramo en `PQ_PARAMETROS_GRAL` default 15; paginación DX. |
| 2026-07-30 | Batch HU: optimistic lock `row_version` en update/delete (409). |
| 2026-07-30 | Parte C+C1: enlazada [TR-004](../../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md). |
| 2026-07-31 | Presentación grilla: Cliente/Tipo = descripción; bits Sin cargo/Presencial; duración UI `hh:mm` + sumatoria horas decimales (persistencia minutos). |
| 2026-07-31 | CC-PQ #1 (31/07/2026): carga diaria filtra `es_tarea = 1` en listado y fuerza `es_tarea = 1` en alta/edición (R-OP-13/14). |
| 2026-08-01 | Parte I: fusionado SPEC-004-update (CC-PQ #1, 31/07) en este original; update eliminado. Estado → Finalizado. |

---

**Trazabilidad:** HU/TR en Partes B/C. Siguiente previsto: **SPEC-005 supervisión (terceros / proceso masivo)**.
