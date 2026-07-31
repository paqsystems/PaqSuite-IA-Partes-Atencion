# SPEC-005 – Supervisión (terceros y proceso masivo)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-005 |
| Título | Supervisión: terceros y proceso masivo sobre tareas |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
| HU relacionada(s) | [HU-005-supervision-proceso-masivo](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) |
| TR relacionada(s) | [TR-005-supervision-proceso-masivo](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |
| Depende de | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md), [SPEC-004](./SPEC-004-operacion-carga-diaria.md) |
| Fuentes | [`05-operacion-diaria-y-supervision.md`](../../02-producto/Sistema-Partes-IA/05-operacion-diaria-y-supervision.md), [`01-vision-y-alcance.md`](../../02-producto/Sistema-Partes-IA/01-vision-y-alcance.md), [`07-fuera-de-alcance-y-evolucion.md`](../../02-producto/Sistema-Partes-IA/07-fuera-de-alcance-y-evolucion.md) |

---

## 1. Resumen ejecutivo

- **Problema:** el supervisor necesita operar sobre el universo ampliado de tareas (terceros) y cambiar el estado `cerrado` de **conjuntos** de registros sin resultados parciales confusos.
- **Resultado esperado:** contrato de **supervisión** coherente con la carga diaria (SPEC-004) más un **proceso masivo web** (cerrar / reabrir) con filtros, selección explícita, confirmación y atomicidad.

---

## 2. Alcance

### 2.1 En alcance

- Capacidades de supervisión sobre el mismo dominio de tareas (no módulo aislado):
  - vista / filtrado de tareas de terceros (universo supervisor);
  - creación / edición / eliminación de tareas de terceros **no cerradas** (detalle operativo en SPEC-004; este SPEC lo reafirma como capacidad de supervisión);
  - cierre / reapertura **individual** (SPEC-004 §4.6) y **masiva** (este SPEC).
- Proceso masivo MVP:
  - acciones: **cerrar** (`cerrado = 1`) y **reabrir** (`cerrado = 0`);
  - solo `esSupervisor = true`;
  - filtros previos + listado + selección explícita + confirmación;
  - preview de lo que se va a procesar;
  - rechazo si selección vacía o inválida;
  - atomicidad del lote (§4.5).
- Delimitación API: no confiar solo en UI; denegar a asistente no supervisor y a cliente.
- i18n + `data-testid`; UI web DevExtreme.

### 2.2 Fuera de alcance

- Redefinir validaciones de captura de alta/edición (SPEC-004).
- Consultas agrupadas / dashboard / pivots (SPEC-006).
- Mobile del proceso masivo (excluido en mobile del producto; SPEC-007).
- “Auditoría de partes” ampliada: edición masiva de campos de negocio, importación Excel, mails selectivos (`07-fuera-de-alcance`) — evolución distinta.
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

La supervisión **reutiliza** las mismas reglas de `cerrado`, propiedad e integridad; no inventa otro modelo de tarea.

### 4.2 Vista de terceros y composición UI (decisión A1)

- El supervisor puede listar tareas de cualquier asistente usable aplicando filtros (fechas, cliente, asistente, estado cerrado).
- **Proceso masivo:** pantalla dedicada “Proceso masivo” vía **menú Partes** (solo supervisor). Desde carga diaria: **atajo mínimo** (link de texto / acción secundaria) que navega a esa pantalla **sin** transferir filtros ni selección de la carga; el supervisor aplica filtros en el masivo. Misma API de lote.
- La grilla de carga diaria (SPEC-004) expone columna **Asistente** según SPEC-004 §4.3.

### 4.3 Proceso masivo — flujo

1. Solo supervisor entra a la pantalla/proceso.
2. Aplica **filtros previos** (mínimo §4.4) y obtiene un listado acotado.
3. Marca una **selección explícita** de filas (checkbox / selección de grilla). No se procesa “todo el filtro” implícitamente sin selección. La UI ofrece **«Seleccionar todos los del resultado actual»** = **todo el resultado filtrado** (todas las páginas del filtro, no solo la página visible), con `id` + `rowVersion` de cada fila.
   - Si esa selección abarca **más de una página** de la grilla → antes de seguir (o al armar el lote), mostrar **modal de confirmación** del tipo: «Afectará a N partes. ¿Confirma?» (`N` = cantidad de filas del resultado filtrado seleccionadas). Si el usuario cancela, no se mantiene/arma esa selección masiva.
4. Elige acción: **Cerrar** o **Reabrir**.
5. El sistema muestra **confirmación** de la acción (cerrar/reabrir) con: acción, cantidad, y resumen visible (p. ej. rango de fechas / muestra de ids o filas). *Nota:* el modal de «afectará a N partes» del paso 3 es adicional cuando la selección multi-página se arma; el paso 5 confirma la **ejecución** de la acción.
6. Confirma → API de lote → resultado reflejado de inmediato en la grilla (refresh).

### 4.4 Filtros previos del masivo (mínimo)

| Filtro | Obligatorio | Notas |
|--------|-------------|-------|
| Rango de fechas | Sí | Mismo criterio que carga: no universo histórico sin acotar |
| Cliente | No | |
| Asistente propietario | No | |
| Estado (`cerrado`) | Recomendado | Ayuda a listar solo abiertas antes de cerrar, o solo cerradas antes de reabrir |

### 4.5 Atomicidad y semántica del lote

**Entrada API (conceptual):** `accion` ∈ {`cerrar`, `reabrir`}, `items` = lista no vacía de `{ id, rowVersion }` (equivalente: `ids` + `rowVersions` alineados).

| Condición | Resultado |
|-----------|-----------|
| `ids` vacío | 422; no cambia nada |
| Algún `id` inexistente | **Fallo total**; nada persistido |
| Algún `id` fuera del universo supervisor (no aplica aquí) / no legible | **Fallo total** |
| Asistente o cliente invocan | 403; nada persistido |
| Lote válido | **Transacción única**: todos los ids pasan a `cerrado` objetivo |

**Idempotencia:** si una fila ya está en el estado objetivo **y** la versión coincide, cuenta como éxito (no error). El lote no falla solo por filas ya cerradas/abiertas.

**Optimistic lock:** cada ítem del lote incluye el `rowVersion` leído en el listado. Si **cualquier** id tiene versión desactualizada (u otro conflicto de concurrencia) → **409**; **fallo total** de la transacción (cero cambios). El cliente debe refrescar y rearmar la selección.

**Parciales:** prohibido dejar “unas sí y otras no” cuando el lote es inválido (ids faltantes / no autorizados). Si el SP/backend detecta inconsistencia → rollback.

Límite de tamaño de lote: **parametrizable** vía `PQ_PARAMETROS_GRAL` clave **`PartesMasivoMaxIds`** (programa `Partes`; fijada en TR-005).

| Valor del parámetro | Efecto |
|---------------------|--------|
| `0` (default) | Sin tope de negocio (solo límite técnico del request/infra si aplica) |
| `N > 0` | Máximo `N` ids por ejecución; si la selección excede → 422 claro, sin procesar |

### 4.6 Efecto de cerrar / reabrir

- **Cerrar:** `cerrado = 1` → la fila sale del circuito ordinario de edición/eliminación (SPEC-004).
- **Reabrir:** `cerrado = 0` → vuelve a ser editable/eliminable según SPEC-004.
- No modifica otros campos de negocio en el proceso masivo MVP.

### 4.7 Mensajes y UX

- Confirmación antes de ejecutar (cantidad + acción).
- Éxito: toast/mensaje i18n con cantidad procesada.
- Error de lote inválido: mensaje claro; grilla sin cambios sorprendentes.
- Claves sugeridas: `partes.masivo.*` (confirm, emptySelection, success, failedAtomic, forbidden).

### 4.8 Reglas numeradas

| ID | Regla |
|----|--------|
| R-SU-01 | Solo `esSupervisor`; asistente/cliente denegados. |
| R-SU-02 | Supervisión amplía el mismo dominio de tareas; no duplica entidades. |
| R-SU-03 | Masivo MVP = solo set de `cerrado` (cerrar/reabrir). |
| R-SU-04 | Exige filtros de fecha + selección explícita no vacía. «Seleccionar todos» = resultado filtrado completo; si >1 página → modal «Afectará a N partes. ¿Confirma?». |
| R-SU-05 | Confirmación visible antes de ejecutar. |
| R-SU-06 | Lote atómico: inválido → cero cambios; válido → todos (idempotente en estado ya objetivo con versión válida). |
| R-SU-06b | Tope de lote = parámetro general (default 0 = sin límite); si N>0 y selección > N → 422 sin procesar. |
| R-SU-06c | Optimistic lock: ítems con `rowVersion`; cualquier desactualizado → 409 y cero cambios. |
| R-SU-07 | Resultado visible de inmediato tras éxito (refresh). |
| R-SU-08 | Acceso vía SP (MUST). |
| R-SU-09 | Fuera de alcance: Excel, mails, edición masiva de campos de negocio. |

---

## 5. Criterios verificables

- [ ] Asistente no supervisor y cliente no acceden al proceso masivo (UI + 403 API).
- [ ] Supervisor lista tareas de terceros con filtros de fecha.
- [ ] Sin selección / selección vacía → no ejecuta; mensaje claro.
- [ ] Cerrar lote de N abiertas → las N quedan `cerrado = 1` y dejan de editarse en carga ordinaria.
- [ ] Reabrir lote de N cerradas → quedan `cerrado = 0` y editables según SPEC-004.
- [ ] Incluir un id inexistente en el lote → ninguna fila del lote cambia.
- [ ] Filas ya en estado objetivo no rompen el lote (idempotencia).
- [ ] UI muestra confirmación con cantidad antes de ejecutar.
- [ ] Tras éxito, el listado refleja el nuevo estado sin recarga manual completa de la app.
- [ ] i18n + `data-testid` en proceso masivo.
- [ ] Si el parámetro de tope es N>0 y la selección supera N → 422 y ninguna fila cambia; si es 0 → no aplica tope de negocio.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | SP `pq_sp_partes_tarea_masivo_set_cerrado` (o familia); transacción; validación de ids; leer tope desde `PQ_PARAMETROS_GRAL` |
| Seed | Parámetro tope masivo default `0` |
| Frontend | Pantalla/proceso masivo (filtros + selección + confirm + acciones + `rowVersion` por fila); menú solo supervisor; atajo link desde carga sin filtros |
| Tests | Feature: atomic fail, idempotencia, 403 no supervisor; E2E humo cerrar 2 filas |
| Docs | Distinguir de evolución “auditoría de partes” |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| ¿Misma ruta que carga o pantalla aparte? | **Cerrado:** pantalla dedicada (menú) + atajo mínimo desde carga **sin** pasar filtros. |
| Tope de N ids | **Cerrado:** parámetro `PQ_PARAMETROS_GRAL` (default `0` = sin límite de negocio). |
| Concurrent updates | **Cerrado:** optimistic lock (`row_version`); conflicto → **409** y recargar; en masivo, cualquier conflicto invalida todo el lote. |
| Mobile | Sin cargas masivas; este proceso es web |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A) — supervisión + masivo cerrar/reabrir atómico. |
| 2026-07-30 | A1: apto con observaciones (tope N; composición de pantallas en TR). |
| 2026-07-30 | A1 cierre: masivo = pantalla dedicada + atajo desde carga. |
| 2026-07-30 | A1 cierre: lote masivo sin tope de negocio (solo técnico). |
| 2026-07-30 | A1 corrección: tope masivo = `PQ_PARAMETROS_GRAL` (0 = sin límite; default 0). |
| 2026-07-30 | Batch HU: atajo desde carga = link mínimo; no transfiere filtros; entrada principal = menú. |
| 2026-07-30 | Batch HU: concurrencia = optimistic lock (`row_version`); 409; masivo atómico ante conflicto. |
| 2026-07-30 | Batch HU: «Seleccionar todos» = resultado filtrado; modal N partes si >1 página. |
| 2026-07-30 | Enlace TR-005 (Parte C+C1). |

---

**Trazabilidad:** [HU-005](../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md). Siguiente previsto: **SPEC-006 consultas, dashboard y navegación**.
