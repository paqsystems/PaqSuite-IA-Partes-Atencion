# HU-004 – Operación / carga diaria de tareas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-004 |
| Título | Operación / carga diaria de tareas |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | En Control Calidad |
| Última actualización | 2026-07-31 |
| SPEC origen | [SPEC-004-operacion-carga-diaria](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) |
| TR relacionada(s) | [TR-004-operacion-carga-diaria](../../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-004 | Dónde en esta HU |
|--------------------------------|------------------|
| Pantalla grilla filtrada §4.1 | Alcance; CA-08, CA-09; R-OP-01 |
| Filtros previos mínimos §4.2 | Alcance; CA-08, CA-09; R-OP-04 |
| Propiedad `usuario_id` / columna Asistente §4.3 | CA-01, CA-02; R-OP-03 |
| Validaciones captura §4.4 | CA-04, CA-05, CA-06, CA-10; R-OP-05/06/07/08 |
| Listado/edición/eliminación §4.5 | Alcance; CA-07 |
| Estado `cerrado` §4.6 | CA-07; R-OP-09/10 |
| Delimitación API por rol §4.7 | CA-01, CA-02, CA-03; R-OP-02 |
| Complemento IA §4.8 | Fuera MVP; R-OP-12 |
| Cliente no carga | CA-03; R-OP-02 |
| Universo tipos SPEC-003 §4.7 | CA-05, CA-10; R-OP-07 |
| Fuera de alcance mobile / masivo SPEC-005 | Fuera de alcance |

---

## Narrativa

Como asistente o supervisor del módulo Partes  
quiero registrar, editar y eliminar tareas diarias desde una grilla filtrada por fechas y contexto  
para documentar dedicación real con reglas claras de propiedad, duración, catálogos usables y estado cerrado.

---

## Contexto funcional

El valor central del módulo es registrar dedicación con baja fricción sobre `PQ_PARTES_REGISTRO_TAREA`. La carga web usa DataGrid con filtros previos obligatorios (default: día del sistema), delimitación por identidad funcional (SPEC-002) y catálogos usables (SPEC-003). Asistentes operan solo tareas propias; supervisores pueden ver terceros, elegir propietario y cerrar/reabrir filas individuales. El proceso masivo de supervisión queda en SPEC-005.

---

## Alcance incluido

- Pantalla web de carga diaria: DataGrid de trabajo + filtros previos obligatorios.
- Insertar, editar y eliminar registros según rol y estado `cerrado`.
- Campos de negocio: `fecha`, `cliente_id`, `tipo_tarea_id`, `duracion_minutos`, `observacion`, `sin_cargo`, `presencial`, `usuario_id`, `cerrado`.
- Validaciones: no grabar si falta cualquier obligatorio (`fecha`, `cliente_id`, `tipo_tarea_id`, `duracion_minutos`, `observacion`, `usuario_id`); duración entera > 0, **múltiplo del tramo** (`PQ_PARAMETROS_GRAL`, default **15**), máximo 1440; UI duración = **selector de tramos en `hh:mm`**; grilla: Cliente/Tipo = **descripción**, columnas **Sin cargo** / **Presencial**, duración visible **`hh:mm`** con sumatoria en **horas decimales**; listado con **paginación DevExtreme**; cliente/tipo usables; tipo ∈ universo del cliente (SPEC-003 §4.7). Al cambiar cliente con tipo fuera de universo → **limpiar** tipo. `sin_cargo` / `presencial` default `0` (false).
- Delimitación de filas visibles según `tipoFuncional` / `esSupervisor` (SPEC-002).
- Columna Asistente: fija para asistente no supervisor; editable para supervisor con selector de asistentes usables.
- Advertencia confirmable (no bloqueo) ante fecha futura.
- Filtros mínimos: rango de fechas (default al abrir = día del sistema); cliente opcional; asistente propietario opcional (supervisor); filtro abiertas/cerradas/todas (**default: todas**; cerradas no editables).
- Cerrar/reabrir fila individual: solo supervisor (MVP).
- Complemento IA **fuera del MVP** (solo carga manual en esta HU).
- i18n claves `partes.tarea.*` + `data-testid`; persistencia vía SP (MUST).

---

## Fuera de alcance

- DDL (SPEC-001), gate login (SPEC-002), ABM maestros (SPEC-003).
- Proceso masivo cerrar/reabrir conjunto (SPEC-005).
- Consultas agrupadas, dashboard, pivots (SPEC-006).
- Carga mobile individual / kardex (SPEC-007); este SPEC es web.
- Cliente funcional: no carga tareas.
- Facturación, aprobación formal, importación masiva, **integración IA en carga diaria** (evolutivo).
- Exportación Excel como Must del MVP de carga.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-OP-01 | Carga web = grilla filtrada; no formulario unitario como único modo. |
| R-OP-02 | Cliente funcional no carga; API deniega. |
| R-OP-03 | Asistente solo opera tareas propias; columna Asistente fija a su código. Supervisor: columna Asistente editable con cualquier asistente activo/usable. |
| R-OP-04 | Filtro de fechas obligatorio antes de listar; **default al abrir = día del sistema**. |
| R-OP-05 | `duracion_minutos` > 0, múltiplo del tramo (`PQ_PARAMETROS_GRAL`, default 15), ≤ 1440. UI = selector tramos en `hh:mm`. |
| R-OP-05c | Grilla: Cliente y Tipo de tarea = descripción; códigos opcionales en column chooser. |
| R-OP-05d | Grilla: Sin cargo y Presencial disponibles. |
| R-OP-05e | Grilla: duración en `hh:mm`; sumatoria horas decimales; API en minutos. |
| R-OP-05b | Listado: paginación estándar DevExtreme. |
| R-OP-06 | `observacion` obligatoria (no blank). |
| R-OP-07 | Cliente/tipo usables; tipo ∈ universo del cliente (SPEC-003). Cambio cliente fuera de universo → limpiar tipo; vacío no grabable. |
| R-OP-07b | Rechazo si falta cualquier obligatorio; `sin_cargo`/`presencial` default `0`. |
| R-OP-08 | Fecha futura → advertencia, no bloqueo. |
| R-OP-09 | `cerrado = 1` → sin edición/eliminación ordinaria. |
| R-OP-10 | Cerrar/reabrir individual: solo supervisor (MVP); masivo → SPEC-005. |
| R-OP-11 | Acceso de negocio vía SP (MUST). |
| R-OP-11b | Update/delete: optimistic lock `row_version`; conflicto → 409. |
| R-OP-12 | IA **fuera del MVP** de carga diaria; no bloquea ni es requisito. |

---

## Criterios de aceptación

- [ ] **CA-01** Asistente no supervisor lista solo sus tareas; columna Asistente fija a su código/nombre; no puede crear ni persistir con otro `usuario_id` (403 en API).
- [ ] **CA-02** Supervisor ve columna Asistente editable y puede asignar cualquier asistente activo y usable (`activo = 1`, `inhabilitado = 0`).
- [ ] **CA-03** Cliente autenticado recibe 403 en APIs de carga y no ve menú/ruta de carga diaria.
- [ ] **CA-04** Alta rechaza duración no múltiplo del tramo (default 15), 0 y >1440; acepta p. ej. 15, 60, 1440 con tramo 15. UI = selector tramos en `hh:mm`; grilla con paginación DevExtreme.
- [ ] **CA-04b** Grilla muestra descripción de Cliente y Tipo de tarea; columnas Sin cargo y Presencial disponibles; duración en `hh:mm` con sumatoria en horas decimales.
- [ ] **CA-05** Alta rechaza observación vacía o solo whitespace; rechaza cliente/tipo inhabilitados o tipo fuera del universo del cliente.
- [ ] **CA-06** Fecha de negocio futura muestra advertencia confirmable y permite completar el alta/edición.
- [ ] **CA-07** Tarea con `cerrado = 1` no se edita ni elimina en flujo ordinario; supervisor puede cerrar y reabrir una fila mediante acción explícita.
- [ ] **CA-08** Al abrir carga diaria, filtros de fecha precargados con el día del sistema (`fechaDesde` = `fechaHasta` = hoy).
- [ ] **CA-08b** Al abrir, el filtro de estado es **todas**; filas con `cerrado = 1` se ven pero no se editan ni eliminan en el flujo ordinario.
- [ ] **CA-09** Sin filtro de fechas aplicado, la UI no dispara listado del universo histórico completo (mensaje i18n claro).
- [ ] **CA-10** Al cambiar `cliente_id` en edición, si el `tipo_tarea_id` no pertenece al nuevo universo se **limpia**; grabar con tipo vacío (u otro obligatorio faltante) se rechaza en UI y API.
- [ ] **CA-10b** Alta nueva fila inicia `sin_cargo = 0` y `presencial = 0`; no se graba si falta cualquiera de: `fecha`, `cliente_id`, `tipo_tarea_id`, `duracion_minutos`, `observacion`, `usuario_id`.
- [ ] **CA-11** i18n + `data-testid` estables en pantalla de carga diaria.

---

## Escenarios Gherkin

```gherkin
Feature: Carga diaria de tareas Partes
  Como asistente o supervisor
  Quiero registrar tareas en una grilla filtrada
  Para documentar dedicación con reglas de dominio

  Scenario: Asistente opera solo tareas propias
    Given sesión con "resultado.partes.tipoFuncional" = "asistente" y "esSupervisor" = false
    And filtros de fecha aplicados para el día actual
    When listo tareas en carga diaria
    Then solo veo filas con "usuario_id" = mi "asistenteId"
    And la columna Asistente muestra mi código/nombre sin ser editable
    When intento crear una tarea con otro "usuario_id" vía API
    Then recibo HTTP 403

  Scenario: Supervisor asigna propietario
    Given sesión con "esSupervisor" = true
    When doy de alta una tarea desde la grilla
    Then la columna Asistente es editable
    And puedo elegir cualquier asistente usable del selector
    And el alta persiste con el "usuario_id" elegido

  Scenario: Validación de duración
    Given contexto de alta con cliente y tipo válidos
    When ingreso "duracion_minutos" = 10
    Then la operación es rechazada
    When ingreso "duracion_minutos" = 60
    Then la operación es aceptada

  Scenario: Fecha futura con advertencia
    Given una fecha de negocio posterior a la fecha del sistema
    When completo el alta de tarea
    Then el sistema muestra advertencia confirmable
    And tras confirmar la operación puede completarse

  Scenario: Tarea cerrada sin edición ordinaria
    Given una tarea con "cerrado" = 1 visible para supervisor
    When intento editar campos de negocio o eliminar desde flujo ordinario
    Then la operación es rechazada
    When supervisor ejecuta reabrir fila individual
    Then "cerrado" pasa a 0 y la edición ordinaria vuelve a estar permitida según rol

  Scenario: Filtro de fechas obligatorio al listar
    Given abro la pantalla de carga diaria
    Then "fechaDesde" y "fechaHasta" vienen precargados con el día del sistema
    When intento listar sin rango de fechas aplicado
    Then la grilla no carga el histórico completo
    And se muestra mensaje i18n indicando acotar fechas
```

---

## Supuestos explícitos

- Sesión con `resultado.partes` válido (SPEC-002) y catálogos usables vía APIs SPEC-003.
- Alta ordinaria de tarea crea registro con `cerrado = 0`, `sin_cargo = 0`, `presencial = 0` salvo que el usuario marque los bits.
- Backend no confía solo en filtros de UI; delimitación API §4.7 es obligatoria.
- Default sugerido de `tipo_tarea_id` al alta: tipo con `is_default = 1` si pertenece al universo del cliente seleccionado.
- Complemento IA: ~~opcional en MVP~~ → **cerrado:** fuera del MVP; solo carga manual (R-OP-12).
- Layouts persistentes de grilla Framework GEN, si aplican, no bloquean MVP.
- Seed incluirá parámetro de tramo de duración (clave p. ej. `PartesDuracionTramoMin`, default `15`).

---

## Preguntas abiertas

- Default del filtro abiertas/cerradas/todas: ~~pendiente~~ → **cerrado:** default **todas**; cerradas visibles pero no editables/eliminables en flujo ordinario.
- Al cambiar cliente en edición: ~~pendiente~~ → **cerrado:** limpiar tipo si no está en el nuevo universo; no grabar con tipo (ni otros obligatorios) vacíos. `sin_cargo`/`presencial` default `0`.
- Contrato mínimo del complemento IA: ~~pendiente~~ → **cerrado:** fuera del MVP de carga; evolutivo posterior.
- Paginación vs scroll: ~~pendiente~~ → **cerrado:** paginación estándar DevExtreme.
- Presentación UI de `duracion_minutos`: ~~pendiente~~ → **cerrado:** selector de tramos en **`hh:mm`**; tramo = param `PQ_PARAMETROS_GRAL` (clave p. ej. `PartesDuracionTramoMin`, default **15**); persiste minutos; grilla expone horas decimales para sumatoria y muestra celdas en `hh:mm`.
- Presentación Cliente / Tipo / bits en grilla: ~~pendiente~~ → **cerrado:** descripción de cliente y tipo; Sin cargo y Presencial disponibles; códigos opcionales vía column chooser.
- Nombres de SP y clave param tramo: ~~pendiente~~ → **cerrado en [TR-004](../../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md)** (`PartesDuracionTramoMin`; `pq_sp_partes_tarea_*`).

---

## Riesgos de ambigüedad

- Filtro estado y cambio cliente/tipo: ~~cerrados en batch~~.
- Tras limpiar tipo por cambio de cliente, el usuario debe reelegir antes de grabar (mensaje i18n claro).
- Cierre/reapertura individual solo supervisor vs lectura en grilla para asistente en tareas cerradas: permitida lectura; edición bloqueada — verificar mensajes i18n diferenciados en TR.

---

## Dependencias

- [HU-001](./HU-001-modelo-datos-modulo.md) / SPEC-001: tabla `PQ_PARTES_REGISTRO_TAREA`.
- [HU-002](./HU-002-identidad-funcional-y-acceso.md) / SPEC-002: delimitación por rol y revalidación sesión.
- [HU-003](./HU-003-maestros-y-catalogos.md) / SPEC-003: catálogos usables y universo de tipos por cliente (§4.7).

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1: HU creada y enriquecida desde SPEC-004. |
| 2026-07-30 | Batch: filtro estado default = todas; cerradas no editables. |
| 2026-07-30 | Batch: cambio cliente limpia tipo; no grabar sin obligatorios; bits default 0. |
| 2026-07-30 | Batch: complemento IA fuera del MVP de carga diaria. |
| 2026-07-30 | Batch: duración tramos+editable; tramo param GRAL default 15; paginación DX. |
| 2026-07-30 | Parte C+C1: enlazada TR-004. |
| 2026-07-31 | Grilla: descripción Cliente/Tipo; bits; duración hh:mm + sumatoria horas decimales. |
