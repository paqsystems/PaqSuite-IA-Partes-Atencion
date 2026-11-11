# SPEC-005 – Supervisión (terceros y proceso masivo)

Jira: PIAPARTES-5


## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-005 |
| Título | Supervisión: terceros y proceso masivo sobre tareas |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Finalizado |
| Última actualización | 09/11/2026 (sort/HeaderFilter remotos + plantillas GEN-11) |
| HU relacionada(s) | [HU-005-supervision-proceso-masivo](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) |
| TR relacionada(s) | [TR-005-supervision-proceso-masivo](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |
| Depende de | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md), [SPEC-004](./SPEC-004-operacion-carga-diaria.md) |
| Fuentes | [`05-operacion-diaria-y-supervision.md`](../../02-producto/Sistema-Partes-IA/05-operacion-diaria-y-supervision.md), [`01-vision-y-alcance.md`](../../02-producto/Sistema-Partes-IA/01-vision-y-alcance.md), [`07-fuera-de-alcance-y-evolucion.md`](../../02-producto/Sistema-Partes-IA/07-fuera-de-alcance-y-evolucion.md), [`11-checklist-temas-definidos-del-modulo.md`](../../02-producto/Sistema-Partes-IA/11-checklist-temas-definidos-del-modulo.md) |

---

## 1. Resumen ejecutivo

- **Problema:** el supervisor necesita localizar un conjunto de tareas, seleccionarlas y aplicar **cambios en lote** (atributos de negocio permitidos y/o estado `cerrado`) sin resultados parciales confusos, sobre una grilla con las capacidades comunes del framework.
- **Resultado esperado:** contrato de **supervisión** coherente con la carga diaria (SPEC-004) más un **proceso masivo web** con: filtros + selección; grilla `ProcessDataGrid` (filtro por columna, totales, column chooser, plantillas, export Excel); actualización masiva de atributos (**Must:** tipo de tarea, sin cargo; **Should:** presencial, asistente, fecha); y cerrar/reabrir atómico.

---

## 2. Alcance

### 2.1 En alcance

- Capacidades de supervisión sobre el mismo dominio de tareas (no módulo aislado):
  - vista / filtrado de tareas de terceros (universo supervisor);
  - creación / edición / eliminación de tareas de terceros **no cerradas** (detalle operativo en SPEC-004; este SPEC lo reafirma);
  - cierre / reapertura **individual** (SPEC-004 §4.6) y **masiva** (este SPEC).
- Proceso masivo web (solo `esSupervisor = true`):
  - **descubrimiento:** filtros previos (§4.4) + listado + selección explícita (incl. «seleccionar todos» del resultado filtrado) + confirmación;
  - **grilla Framework** (§4.3b): fila de filtro bajo títulos, totalización, selección de campos, plantillas, exportación a Excel;
  - **acciones de lote:**
    - **Must — actualizar atributos:** `tipoTarea` y `sinCargo` (§4.6);
    - **Should — mismos atributos factibles:** `presencial`, `asistente` (propietario), `fecha` (§4.6);
    - **Must — estado:** cerrar / reabrir (`cerrado`) (§4.5–4.5b);
  - rechazo si selección vacía o inválida; atomicidad del lote (§4.5);
  - actualización de atributos **permitida también sobre tareas cerradas** (el masivo es el circuito supervisor; no exige reabrir primero).
  - **Informar a clientes (CC #7 / #15):** ícono mail; aviso i18n a usuarios de clientes del **universo filtrado** (todas las páginas; sin exigir tilde ni modificación); URL `https://{tenant}.partesatencion.paqsystems.com` + código usuario; sin password; guard al salir con cambios pendientes (§4.7).
- Delimitación implícita por `es_tarea = true`: listado, `list_ids`/«seleccionar todos» y acciones de lote (cerrar/reabrir, atributos) operan **únicamente** sobre registros de tarea (SPEC-001 R-MD-12); las compras/movimientos de paquete de horas no participan del masivo.
- Delimitación API: no confiar solo en UI; denegar a asistente no supervisor y a cliente.
- i18n + `data-testid`; UI web DevExtreme.

### 2.2 Fuera de alcance

- Redefinir validaciones de captura de alta/edición individual (SPEC-004).
- Consultas agrupadas / dashboard / pivots de informes (SPEC-006).
- Mobile del proceso masivo (excluido; SPEC-007).
- Atributos **excluidos** de edición masiva: **cliente**, **duración/minutos**, **descripción/observación**.
- “Auditoría de partes” con **importación Excel** y **mails selectivos** (`07-fuera-de-alcance`) — evolución distinta (no confundir con export Excel de la grilla del masivo, con la edición masiva de atributos, ni con **Informar a clientes** §4.7).
- Editor / catálogo de **plantillas de mail** predefinidas (evolución; v1 hardcode i18n).
- Aprobación formal / workflow de estados más allá de `cerrado`.
- ABM maestros (SPEC-003).

---

## 3. Actores y contexto

| Actor | Supervisión / masivo |
|-------|----------------------|
| Supervisor (`resultado.partes.esSupervisor = true`) | Sí |
| Asistente no supervisor | No (403 / sin menú) |
| Cliente | No |

Universo de datos supervisor: tareas del módulo sin filtro por propietario propio (capa 1 SPEC-002), acotado por filtros del proceso.

Precondiciones: SPEC-004 desplegable; flag `supervisor` en dominio (SPEC-001/002).

---

## 4. Comportamiento funcional

### 4.1 Relación con carga diaria (SPEC-004)

| Capacidad | Dónde vive |
|-----------|------------|
| Grilla de carga, validaciones, propietario | SPEC-004 |
| Ver/editar terceros no cerrados, elegir propietario | SPEC-004 (actor supervisor) |
| Cerrar/reabrir **una** fila | SPEC-004 |
| Cerrar/reabrir **N** filas (lote) | **Este SPEC** |
| Actualizar atributos en lote | **Este SPEC** |

La supervisión **reutiliza** las mismas reglas de `cerrado`, tipos, marcas e integridad; no inventa otro modelo de tarea.

### 4.2 Vista de terceros y composición UI

- El supervisor puede listar tareas de cualquier asistente usable aplicando filtros (fechas, cliente, asistente, estado cerrado).
- **Proceso masivo:** pantalla dedicada “Proceso masivo” vía **menú Partes** (solo supervisor). Desde carga diaria: **atajo mínimo** (link) que navega a esa pantalla **sin** transferir filtros ni selección; el supervisor aplica filtros en el masivo.
- La grilla de carga diaria (SPEC-004) expone columna **Asistente** según SPEC-004 §4.3.

### 4.3 Proceso masivo — flujo (descubrimiento y selección)

1. Solo supervisor entra a la pantalla/proceso.
2. Aplica **filtros previos** (mínimo §4.4) y obtiene un listado acotado.
   - Si el **`total`** del listado supera el tamaño de página, la grilla usa **paginación remota** (CC-PQ #11): cada cambio de página o de tamaño (**20 / 40 / 80**) solicita al API `page` + `pageSize`; el paginador DevExtreme refleja todas las páginas; la leyenda **Total filtro** muestra el `total` global del filtro. **Remoto:** `paging`, `filtering` (FilterRow contains — CC-PQ #13 — y HeaderFilter por igualdad) y `sorting` (universo filtrado, no solo la página visible). **En cliente:** `grouping` y `summary` (totalizadores de pie de duración). Must: summary **no** remoto, para no bloquear el pager. Agrupación y column chooser operan sobre la **página cargada**.
3. Marca una **selección explícita** de filas. No se procesa “todo el filtro” implícitamente sin selección. La UI ofrece **«Seleccionar todos los del resultado actual»** = **todo el resultado filtrado** (todas las páginas), con `id` + `rowVersion` de cada fila.
   - Si esa selección abarca **más de una página** → modal «Afectará a N partes. ¿Confirma?». Si cancela, no se arma esa selección masiva.
4. Elige **una acción de lote** (§4.5b / §4.6): cerrar, reabrir, o actualizar atributo(s) permitidos.
5. Confirmación con: acción, cantidad, valores a aplicar (si hay), y resumen visible.
6. Confirma → API de lote → resultado reflejado de inmediato (refresh grilla).

### 4.7 Informar novedades a clientes (CC #7 / #15) — Must

1. Toolbar: ícono mail + tooltip i18n `partes.masivo.informarClientes` + `data-testid` `partesMasivoInformarClientes` (solo supervisor; excluido native).
2. Destinatarios: `users.id` únicos de clientes del resultado filtrado (`es_tarea = 1`) con `PQ_PARTES_CLIENTES.user_id` → `USERS` usable (`activo`, no inhabilitado) y **`users.email`** no vacío. To del mail = **`users.email`** (**prohibido** `PQ_PARTES_CLIENTES.email`). Recolección en servidor (SP JOIN; sin `whereIn` N); sin exigir selección ni modificación de filas.
3. Mail v1 (Mail Engine GEN / futuro GEN-30): empresa emisora; período filtros; URL portal tenant; `users.usuario` / código; forgot GEN-04; **sin** contraseña; asunto/cuerpo en `users.locale` (5 locales host).
4. Confirmación previa (`partesMasivoInformarConfirmDialog`) **antes** de enviar. Must de UX (adopta GEN-01 §17):
   - Al abrir el diálogo se **reconsulta** el preview con los **mismos filtros** de la grilla (no un recuento stale de cuando se cargó la pantalla).
   - Copy de negocio: **prohibido** jerga técnica (`users.email`, nombres de tablas/columnas).
   - Encabezado con **N avisos por correo**; período del filtro según locale i18n (`dd/MM/yyyy` en es/pt/fr/it; `MM/dd/yyyy` en en).
   - Lista **completa** de destinatarios en **tarjetas** (iniciales, nombre del cliente, mail). El conjunto es el universo filtrado (todas las páginas; sin exigir tilde).
   - Viewport de la lista: **máximo 5 tarjetas visibles**. Si hay más, **scroll** en el recuadro de destinatarios (no paginar; no recortar con «Y N más»). Título del Popup y pie **Cancelar / Enviar correos** permanecen fijos.
   - 422 sin destinatarios / tope.
5. **Guard salida:** si hay cambios/selección pendientes y el supervisor abandona (menú / pestaña / `beforeunload`), diálogo i18n ofrece Enviar correos y salir / Salir sin enviar / Cancelar.

### 4.3b Grilla del proceso (capacidades Framework) — Must

La grilla del masivo **debe** usar el patrón común de grilla del framework (`ProcessDataGrid` / plantillas GEN), no un `DataGrid` incompleto a medida.

| Capacidad | Requisito |
|-----------|-----------|
| Fila de filtrado bajo títulos | Sí (`filterRow`); contains remoto al API (CC-PQ #13) |
| Filtro de encabezado (embudo) | Sí (`HeaderFilter`); lista valores únicos del **universo filtrado**; aplicar un valor (o varios) filtra por igualdad |
| Orden por columna | Sí — clic en el título reordena el **universo filtrado** vía API, no solo la página visible |
| Totalización | Sí — al menos sumatoria útil de **duración** cuando la columna esté presente (presentación alineada a SPEC-004/006: UI `hh:mm` / horas decimales en pie según norma de grilla del módulo). Pie **local** (no summary remoto) |
| Selección de campos | Sí — column chooser (sobre la página cargada) |
| Plantillas de layout | Sí — toolbar GEN-11 guardar / aplicar / último usado (`proceso` + `gridId` del masivo); mismo contrato que carga diaria; **sin** opt-out |
| Exportación a Excel | Sí — del conjunto presentado en la grilla (capacidad framework; distinta de importación Excel fuera de alcance) |
| Paginación alineada al total filtrado | Sí — remota vía API cuando `total > pageSize` (CC-PQ #11); tamaños 20/40/80; paging + filtro + sort remotos; agrupación sobre la **página cargada** |

La implementación concreta sigue las normas del framework; este SPEC solo exige su **uso** en el proceso.

### 4.4 Filtros previos del masivo (mínimo)

| Filtro | Obligatorio | Notas |
|--------|-------------|-------|
| Rango de fechas (periodo) | Sí | No universo histórico sin acotar |
| Cliente | No | |
| Asistente propietario | No | |
| Estado (`cerrado`) | Recomendado | Ayuda a acotar antes de cerrar/reabrir o de corregir atributos |

### 4.5 Atomicidad y semántica del lote (común)

**Entrada API (conceptual — cerrar/reabrir):** `accion` ∈ {`cerrar`, `reabrir`}, `items` = lista no vacía de `{ id, rowVersion }`.

**Entrada API (conceptual — actualizar atributos):** `campos` = objeto con **al menos un** atributo permitido (§4.6), `items` = misma forma.

| Condición | Resultado |
|-----------|-----------|
| `items` vacío | 422; no cambia nada |
| Algún `id` inexistente | **Fallo total**; nada persistido |
| Algún ítem no legible / fuera de universo | **Fallo total** |
| Algún `id` con `es_tarea = 0` (compra) | **Fallo total** (422); el masivo solo opera sobre `es_tarea = 1` |
| Asistente o cliente invocan | 403; nada persistido |
| Validación de negocio falla en **alguna** fila (p. ej. tipo inválido para el cliente de esa tarea) | **Fallo total**; cero cambios; mensaje claro (sin éxito aparente parcial) |
| Lote válido | **Transacción única**: todos los items reciben el cambio |

**Idempotencia (cerrar/reabrir):** si una fila ya está en el estado objetivo **y** la versión coincide, cuenta como éxito.

**Idempotencia (atributos):** si el valor objetivo ya coincide en la fila y la versión es válida, cuenta como éxito para esa fila.

**Optimistic lock:** cada ítem incluye `rowVersion`. Si **cualquier** id está desactualizado → **409**; **fallo total**.

Límite de tamaño: parámetro `PQ_PARAMETROS_GRAL` clave **`PartesMasivoMaxIds`** (programa `Partes`).

| Valor | Efecto |
|-------|--------|
| `0` (default) | Sin tope de negocio (solo límite técnico request/infra) |
| `N > 0` | Máximo `N` ids; exceso → 422, sin procesar |

### 4.5b Efecto de cerrar / reabrir

- **Cerrar:** `cerrado = 1` → sale del circuito ordinario de edición/eliminación (SPEC-004).
- **Reabrir:** `cerrado = 0` → vuelve editable/eliminable según SPEC-004.

### 4.6 Actualización masiva de atributos

Aplica el **mismo valor** elegido a **todos** los registros seleccionados, solo para atributos de la lista blanca.

#### Must (prioridad inmediata)

| Atributo | Campo conceptual | Validación |
|----------|------------------|------------|
| Tipo de tarea | `tipoTareaId` (o código acordado en TR) | Tipo válido/habilitado; **compatible con el cliente de cada fila** (tipos genéricos + asignados al cliente). Si alguna fila no admite ese tipo → fallo total del lote. |
| Sin cargo | `sinCargo` (bool) | Marca de dominio. |

#### Should (mismo circuito, siguientes)

| Atributo | Campo conceptual | Validación |
|----------|------------------|------------|
| Presencial | `presencial` (bool) | Marca de dominio. |
| Asistente | `usuarioId` / propietario | Asistente usable; solo supervisor. |
| Fecha | `fecha` (fecha de proceso) | Fecha de proceso válida según reglas SPEC-004 aplicables. |

#### Excluidos (nunca en masivo)

| Atributo | Motivo |
|----------|--------|
| Cliente | Cambia significado y validez del tipo |
| Minutos / duración | Ajuste individual |
| Descripción / observación | Texto libre por tarea |

La UI debe dejar claro **qué atributo(s)** y **qué valor** se aplicarán antes de confirmar. En una corrida Must, el usuario puede aplicar **uno o ambos** atributos Must en la misma acción (si ambos vienen en `campos`) o en acciones sucesivas; TR fija el contrato exacto del payload.

### 4.7 Mensajes y UX

- Confirmación antes de ejecutar (cantidad + acción + valores).
- Éxito: toast/mensaje i18n con cantidad procesada.
- Error de lote inválido / validación: mensaje claro; grilla sin cambios sorprendentes.
- Claves: `partes.masivo.*` (confirm, emptySelection, success, failedAtomic, forbidden, conflictoVersion, atributoInválido, etc.).

### 4.8 Reglas numeradas

| ID | Regla |
|----|--------|
| R-SU-01 | Solo `esSupervisor`; asistente/cliente denegados. |
| R-SU-02 | Supervisión amplía el mismo dominio de tareas; no duplica entidades. |
| R-SU-03 | Masivo incluye: (a) set de `cerrado`; (b) actualización de atributos en lista blanca (§4.6). |
| R-SU-03b | Must atributos: tipo de tarea + sin cargo. Should: presencial, asistente, fecha. Excluidos: cliente, duración, descripción. |
| R-SU-03c | Grilla masivo Must: ProcessDataGrid con filter row, totales, column chooser, plantillas, export Excel. |
| R-SU-04 | Exige filtros de fecha + selección explícita no vacía. «Seleccionar todos» = resultado filtrado completo; si >1 página → modal N. |
| R-SU-05 | Confirmación visible antes de ejecutar (acción, cantidad, valores si aplica). |
| R-SU-06 | Lote atómico: inválido → cero cambios; válido → todos. |
| R-SU-06b | Tope de lote = parámetro general (default 0); exceso → 422. |
| R-SU-06c | Optimistic lock: `rowVersion`; desactualizado → 409 y cero cambios. |
| R-SU-07 | Resultado visible de inmediato tras éxito (refresh). |
| R-SU-08 | Acceso vía SP (MUST). |
| R-SU-09 | Fuera de alcance: importación Excel, mails, atributos excluidos §4.6. Export Excel de grilla **sí** está en alcance (R-SU-03c). |
| R-SU-10 | Actualización masiva de atributos permitida sobre tareas cerradas (circuito supervisor). |
| R-SU-11 | Listado y `list_ids` del masivo filtran implícitamente `es_tarea = 1`. |
| R-SU-12 | Lotes (cerrar/reabrir/atributos) rechazan de forma atómica cualquier id con `es_tarea = 0`. |

---

## 5. Criterios verificables

- [ ] Asistente no supervisor y cliente no acceden al proceso masivo (UI + 403 API).
- [ ] Supervisor lista tareas de terceros con filtros de fecha (+ opcionales).
- [ ] Sin selección / selección vacía → no ejecuta; mensaje claro.
- [ ] Cerrar lote de N abiertas → las N quedan `cerrado = 1`.
- [ ] Reabrir lote de N cerradas → quedan `cerrado = 0`.
- [ ] Actualizar `sinCargo` en lote → las N reflejan el valor; incl. filas ya cerradas.
- [ ] Actualizar `tipoTarea` en lote homogéneo (mismo cliente / tipo válido) → las N quedan con ese tipo.
- [ ] Actualizar `tipoTarea` incompatible con el cliente de alguna fila → 422/error de negocio; **cero** cambios.
- [ ] Incluir un id inexistente → ninguna fila del lote cambia.
- [ ] Filas ya en estado/valor objetivo no rompen el lote (idempotencia).
- [ ] Grilla: filter row, HeaderFilter con valores únicos, orden por columna del universo filtrado, totales, column chooser, plantillas GEN-11 y export Excel disponibles.
- [ ] UI muestra confirmación con cantidad (y valores de atributo) antes de ejecutar.
- [ ] Tras éxito, el listado refleja el cambio sin recarga completa de la app.
- [ ] i18n + `data-testid` en proceso masivo.
- [ ] Tope param N>0 y selección > N → 422; si 0 → sin tope de negocio.
- [x] Listado masivo y `list_ids`/select-all no muestran ni devuelven compras (`es_tarea = 0`).
- [x] Un lote que incluye un id con `es_tarea = 0` falla por completo (cero cambios).

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | SP existentes de cerrar/reabrir + **SP/familia de actualización masiva de atributos**; transacción; validación tipo↔cliente; tope `PartesMasivoMaxIds` |
| Seed | Parámetro tope (ya existente); sin cambio de menú salvo permisos |
| Frontend | `ProcessDataGrid` en `/partes/proceso-masivo`; acciones actualizar tipo / sin cargo (+ Should); cerrar/reabrir; confirm + `rowVersion` |
| Tests | Feature: atomic fail por tipo inválido; update sinCargo; 403; E2E humo |
| Docs | Distinguir export grilla vs import Excel / auditoría |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| ¿Misma ruta que carga o pantalla aparte? | **Cerrado:** pantalla dedicada + atajo sin filtros. |
| Tope de N ids | **Cerrado:** `PartesMasivoMaxIds` (default 0). |
| Concurrent updates | **Cerrado:** optimistic lock; 409; lote atómico. |
| Validación parcial tipo↔cliente | **Cerrado:** fallo total del lote (producto: evitar éxito aparente parcial). |
| Mobile | Sin proceso masivo; este proceso es web. |
| Should atributos | Implementables en el mismo circuito; priorizar Must en primera entrega de la ampliación. |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A) — supervisión + masivo cerrar/reabrir atómico. |
| 2026-07-30 | A1: pantalla dedicada + atajo; tope param; select-all; optimistic lock. |
| 2026-07-30 | Enlace TR-005 (Parte C+C1). |
| 2026-07-31 | SPEC-update desde producto: grilla Framework (filter/totales/chooser/plantillas/Excel); edición masiva atributos Must (tipo, sin cargo) + Should (presencial, asistente, fecha); excluidos cliente/duración/descripción; R-SU-03* / R-SU-09 / R-SU-10. |
| 2026-07-31 | F1: Aprobado con observaciones (ver TR-005). |
| 2026-07-31 | F1: Aprobado con observaciones (ver TR-005). |
| 2026-07-31 | CC-PQ #1 (31/07/2026): masivo (listado, `list_ids`, lotes) opera únicamente sobre `es_tarea = 1`; id con `es_tarea = 0` en un lote falla atómico (R-SU-11/12). |
| 2026-08-01 | Parte I: fusionado SPEC-005-update (CC-PQ #1, 31/07) en este original; update eliminado. Estado → Finalizado. |
| 01/11/2026 | CC-PQ #11: paginación remota grilla masivo ↔ `GET /partes/tareas`; Parte I fusionó SPEC-005-update-cc-pq-11. [D-VERIFICACION](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-11-2026-11-01.md). |
| 01/11/2026 | CC-PQ #11 prod: `RemoteOperations` solo paging; verificado preview + prod (PR #117/#118). Parte I acotada en SPEC base. |
| 03/11/2026 | CC-PQ #13: FilterRow remoto en grilla masivo (`clienteNombre`, `tipoTareaDescripcion`). Parte I acotada; F1/F Preview OK; [D-VERIFICACION-CC-PQ-13](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-13-2026-11-03.md). |
| 04/11/2026 | CC-PQ #15: Informar a clientes (§4.7 — URL tenant, i18n destinatario, guard salida, plantilla v1). Parte I fusionó SPEC-005-update; [D-VERIFICACION-CC-PQ-15](../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-15-2026-11-04.md). |
| 04/11/2026 | Corrección PQ: destinatario = **`users.email`** del usuario vinculado; nunca `PQ_PARTES_CLIENTES.email`. |
| 06/11/2026 | Confirmación informar clientes: tarjetas de destinatarios, lista completa, viewport de **5** + **scroll** (GEN-01 §17); preview al abrir con filtros vigentes; sin jerga técnica. |
| 09/11/2026 | Grilla masivo: sort y HeaderFilter remotos sobre el universo filtrado; plantillas GEN-11 activas (revierte el opt-out del follow-up CC #11). §4.3 / §4.3b. |

---

**Trazabilidad:** [HU-005](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md).
