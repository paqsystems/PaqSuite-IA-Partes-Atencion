# HU-013 – Carga de paquetes de horas (compras / créditos)

Jira: PIAPARTES-47

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-013 |
| Título | ABM de paquetes de horas y Smart Capture en modal (supervisor) |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Especificado |
| Última actualización | 2026-10-21 |
| SPEC origen | [SPEC-013-carga-paquetes-horas](../../05-open-spec/100-SistemaPartes/SPEC-013-carga-paquetes-horas.md) |
| TR relacionada(s) | [TR-013-carga-paquetes-horas](../../04-tareas/100-SistemaPartes/TR-013-carga-paquetes-horas.md) |
| Producto | [`16-carga-paquetes-horas.md`](../../02-producto/Sistema-Partes-IA/16-carga-paquetes-horas.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-013 | Dónde en esta HU |
|--------------------------------|------------------|
| Proceso propio grilla + modal §4.1, D-PH-07 | Alcance; CA-01, CA-08; R-PH-07 |
| Filtros fecha + cliente §4.2; default mes (A1) | CA-02, CA-03; R-PH-17 |
| Solo `es_tarea = false` §2.1, R-PH-01/12 | CA-04, CA-14; R-PH-01, R-PH-12 |
| Captura modal §4.3 | CA-05, CA-06, CA-07; R-PH-07 |
| Persistencia forzada §4.4, D-PH-02 | CA-04, CA-09; R-PH-02, R-PH-18 |
| Edición/baja §4.5, D-PH-10 | CA-10; R-PH-10 |
| Informe cuenta corriente §4.6, D-PH-03 | CA-11; R-PH-03 |
| API solo supervisor / 422 notPaquete §4.7 | CA-12, CA-13; R-PH-05, R-PH-19 |
| Smart Capture modal §4.8, D-PH-11 | CA-15…CA-20; R-PH-11, R-PH-SC-01…05 |
| Reglas R-PH-01…19 | § Reglas de negocio |
| Errores en modal §4.5, R-PH-13 | CA-08 |
| GEN-11 layouts §4.10 | CA-16; R-PH-15 |
| Export no Must §4.10 | Fuera de alcance Must; R-PH-16 |
| Fuera mobile, Excel, GEN-15, chat §2.2 | Fuera de alcance |
| Criterios verificables §5 | CA-01…20 |

---

## Narrativa

Como **supervisor** del módulo Partes  
quiero dar de alta, editar y eliminar **compras de horas** (créditos de paquete) desde un proceso propio, con grilla filtrada y formulario en modal  
para que el informe **Paquete de Horas** refleje reposiciones además del consumo de tareas, sin mezclar este flujo con la carga diaria ni con la importación Excel.

Además, quiero usar **Smart Capture** en el mismo modal para proponer cliente, fecha, duración y observación con texto, audio o imagen, revisando siempre antes de guardar.

---

## Contexto funcional

Un movimiento de paquete **no es una tarea**: se persiste en `PQ_PARTES_REGISTRO_TAREA` con `es_tarea = false`. La carga diaria (HU-004) fuerza `es_tarea = true` y no debe usarse para créditos. El informe (HU-006 / SPEC-006) ya calcula saldo mezclando tareas (+) y paquetes (−); sin este proceso solo se ve consumo.

Solo **supervisores** operan el ABM. El menú es **hermano** de Carga diaria, con permiso propio. Smart Capture adopta GEN-03 con contrato acotado (sin tipo/asistente/bits); el turno reutiliza la infraestructura de HU-010 / SPEC-010 con discriminador de proceso paquete.

### Precondiciones

- Sesión Partes con `esSupervisor = true` (SPEC-002).
- Catálogo de clientes usables (SPEC-003).
- Tipo de tarea con `is_default` en maestros.
- Tramo de duración en `PQ_PARAMETROS_GRAL` (misma clave que carga diaria, default 15).
- Para SC con turnos: credencial LLM BYOK (SPEC-008); sin ella, formulario manual sigue disponible.

### Actores

| Actor | Carga de paquetes |
|-------|-------------------|
| Supervisor | Sí — ABM + SC en modal |
| Asistente no supervisor | No — menú/API 403 |
| Cliente funcional | No — solo consulta informe si aplica |

---

## Alcance incluido

- Pantalla web **Paquetes de horas**: `ProcessPageLayout`, filtros, `ProcessDataGrid`, alta/edición en **Popup** (ABM modal).
- Filtros: **fecha desde / hasta** (obligatorios para listar; default al abrir = **mes calendario en curso**); **cliente** opcional (vacío = todos los clientes usables del universo supervisor).
- Listado solo `es_tarea = false`; columnas: cliente (descripción), fecha, duración `hh:mm`, observación, quién registró, auditoría; sin `es_tarea` ni tipo en UI.
- Modal: cliente, fecha, duración (tramos `hh:mm`), observación — todos obligatorios; fecha futura con advertencia confirmable (no bloqueo duro v1).
- Alta persiste: `es_tarea = false`, `tipo_tarea_id` = default, `usuario_id` = asistente del supervisor, `sin_cargo`/`presencial`/`cerrado` = false.
- Edición: conserva `usuario_id` original; cliente inhabilitado puede mantenerse; cambio de cliente solo a usables.
- Eliminación con confirmación explícita; sin bloqueo por `cerrado`.
- Errores de guardado **dentro** del Popup; limpiar al cancelar.
- **Smart Capture** debajo del formulario en alta/edición: campos cliente/fecha/duración/observación; hint i18n de paquete; mismo API al guardar que el botón Guardar; BYOK gate si falta credencial.
- Menú propio bajo Carga de Partes; i18n 5 locales + `data-testid`.
- Plantillas GEN-11 (`partes.carga.paquetes` / `cargaPaquetes`) compartidas como carga diaria.
- Persistencia vía **SP**; envelope GEN-20; `row_version` en update/delete (409 si conflicto).

---

## Fuera de alcance

- Mobile v1 (informe paquete sí en mobile; esta carga no).
- Importación Excel de paquetes (HU-009 / GEN-14 solo tareas).
- Carga diaria, proceso masivo (HU-004, HU-005), emisiones (HU-011).
- Chat documental para grabar paquetes (HU-008).
- SC en grilla o en informes.
- Tabla de saldos persistido; facturación ERP.
- **Export Excel** y columna duración decimal en grilla — no Must v1 (opcional TR).
- Alta de paquetes desde cualquier otra pantalla.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-PH-01 | Alta/edición desde este proceso fuerza `es_tarea = false`. |
| R-PH-02 | `tipo_tarea_id` = default; no UI. |
| R-PH-03 | Duración = horas compradas; informe mantiene signos SPEC-006. |
| R-PH-04 | Solo opera registros-paquete. |
| R-PH-05 | Solo supervisor. |
| R-PH-06 | Menú y permiso propios (≠ carga diaria). |
| R-PH-07 | ABM grilla + modal; filtros periodo y cliente. |
| R-PH-08 | Fuera de mobile v1. |
| R-PH-09 | Sin import Excel paquetes. |
| R-PH-10 | Sin `cerrado` bloqueante; baja con confirmación. |
| R-PH-11 | Smart Capture Must en modal (GEN-03). |
| R-PH-12 | Carga diaria no lista ni crea paquetes. |
| R-PH-13 | Errores guardado en modal. |
| R-PH-14 | Negocio vía SP. |
| R-PH-15…16 | GEN-11 compartido; export no Must v1. |
| R-PH-17 | Default filtros fecha = mes calendario en curso. |
| R-PH-18 | `usuario_id` solo en alta. |
| R-PH-19 | Id de tarea → 422 `notPaquete`. |
| R-PH-SC-01…05 | SC solo modal paquete; solo supervisor; campos acotados; hint paquete; save = API Guardar. |

---

## Criterios de aceptación

- [ ] **CA-01** Supervisor ve menú/ruta de paquetes; asistente no supervisor y cliente **no** ven menú y reciben **403** en API.
- [ ] **CA-02** Al abrir la pantalla, `fechaDesde` = 1.er día del mes en curso y `fechaHasta` = último día del mismo mes (calendario del sistema).
- [ ] **CA-03** Sin rango de fechas aplicado, no se lista el histórico completo (mensaje i18n); con fechas, solo paquetes en el rango; cliente vacío = todos los usables del universo supervisor; con cliente = acotado.
- [ ] **CA-04** Alta persiste `es_tarea = false`, tipo default, `usuario_id` = asistente del supervisor autenticado, bits en false.
- [ ] **CA-05** Rechazo si falta cliente, fecha, duración válida (horas enteras entre 8 y 240, inclusive) u observación vacía.
- [ ] **CA-06** Fecha futura: advertencia confirmable en UI manual; permite guardar tras confirmar.
- [ ] **CA-07** Alta: solo clientes usables en selector. Edición: puede mantener cliente inhabilitado; al cambiar, solo usables.
- [ ] **CA-08** Error de guardado con modal abierto se muestra **dentro** del Popup (`*FormError`); al cancelar/cerrar se limpia.
- [ ] **CA-09** Tras editar paquete, `usuario_id` del registrador **no** cambia.
- [ ] **CA-10** Eliminar pide confirmación; editar/eliminar paquete permitido sin estado `cerrado`.
- [ ] **CA-11** Tras alta, informe Paquete de Horas (mismo cliente/periodo) muestra movimiento que **resta** minutos (convención SPEC-006).
- [ ] **CA-12** API update/delete/get por id de **tarea** (`es_tarea = true`) responde **422** con clave `partes.paquete.notPaquete` (o equivalente acordado TR).
- [ ] **CA-13** Backend fuerza flags §4.4 en insert/update aunque el cliente envíe otros valores.
- [ ] **CA-14** Carga diaria con mismos filtros **no** lista paquetes creados desde este proceso.
- [ ] **CA-15** SC visible solo en modal alta/edición supervisor, **debajo** del formulario; **no** en grilla.
- [ ] **CA-16** Dos usuarios ven y aplican plantillas GEN-11 del par `partes.carga.paquetes`/`cargaPaquetes`; no eliminan ajenas (contrato GEN-11).
- [ ] **CA-17** Sin BYOK válido: gate Preferencias; formulario manual operativo.
- [ ] **CA-18** SC: ambigüedad de cliente → lista numerada; fecha futura confirmada antes de aplicar al draft; no propone tipo/asistente/bits.
- [ ] **CA-19** SC action `save` y botón Guardar llaman al **mismo** endpoint y validaciones.
- [ ] **CA-20** i18n (5 locales) + `data-testid` estables en pantalla, modal y alerta de error.

---

## Escenarios Gherkin

```gherkin
Feature: Carga de paquetes de horas
  Como supervisor
  Quiero registrar compras de horas
  Para alimentar la cuenta corriente del informe Paquete de Horas

  Scenario: Asistente no accede al proceso
    Given sesión con "esSupervisor" = false
    When intento abrir la ruta o API de paquetes
    Then recibo HTTP 403 o no veo el ítem de menú

  Scenario: Alta de paquete con persistencia forzada
    Given sesión supervisor y filtros de mes aplicados
    When doy de alta un paquete válido desde el modal
    Then el registro tiene "esTarea" = false
    And "usuarioId" = mi asistenteId de sesión
    And el informe Paquete de Horas refleja resta de minutos

  Scenario: Listado excluye tareas
    Given existen tareas y paquetes en el mismo periodo
    When listo paquetes con ese rango de fechas
    Then solo veo filas con "esTarea" = false

  Scenario: No editar tarea vía API paquete
    Given un id de registro con "esTarea" = true
    When llamo update o delete del API de paquetes
    Then recibo HTTP 422 con clave de no paquete

  Scenario: usuario_id estable en edición
    Given un paquete cargado por el supervisor A
    When el supervisor B edita la observación
    Then "usuarioId" sigue siendo el asistenteId de A

  Scenario: Smart Capture en modal
    Given abro Nueva compra de paquete
    Then veo el panel Smart Capture bajo el formulario con hint de paquete
    When envío un turno que propone cliente y duración
    And confirmo y guardo
    Then los datos persisten con las mismas reglas que Guardar manual
```

---

## Supuestos explícitos

- Supervisor tiene `asistenteId` en sesión Partes (SPEC-002).
- Optimistic lock igual que carga diaria (`row_version` / 409).
- Turno SC discrimina `processKind = 'paquete'` (nombre exacto en TR-013).
- Timeout LLM SC = el del chat documental (SPEC-008).
- No se monta ruta en `partesMobilePolicy` v1.

---

## Preguntas abiertas (B1 — cierre desde A1)

| Pregunta | Estado |
|----------|--------|
| Default filtros fecha al abrir | **Cerrado:** mes calendario en curso (R-PH-17). |
| Cliente vacío en filtro | **Cerrado:** universo supervisor usables. |
| `usuario_id` en update | **Cerrado:** conservar registrador original. |
| Cliente inhabilitado en edición | **Cerrado:** mantener o cambiar solo a usable. |
| 404 vs 422 not paquete | **Cerrado:** 422 envelope. |
| Export / decimal grilla | **Cerrado:** no Must v1. |
| Endpoint SC | **Cerrado:** mismo que tareas + discriminador contexto. |
| Código menú / `pq_menus` id | **Abierto en TR-013** (regla 76); no bloquea HU. |

---

## Riesgos de ambigüedad residuales

- Nombres finales de SP y rutas API → TR-013.
- Mensajes i18n exactos para `notPaquete` y validaciones → TR-013 + 5 JSON.

---

## Dependencias

- [HU-001](./HU-001-modelo-datos-modulo.md) / SPEC-001: `es_tarea`, `PQ_PARTES_REGISTRO_TAREA`.
- [HU-002](./HU-002-identidad-funcional-y-acceso.md) / SPEC-002: gate supervisor.
- [HU-003](./HU-003-maestros-y-catalogos.md) / SPEC-003: clientes usables, tipo default.
- [HU-004](./HU-004-operacion-carga-diaria.md) / SPEC-004: patrones grilla/modal, tramo duración (sin mezclar listados).
- [HU-006](./HU-006-consultas-dashboard-navegacion.md) / SPEC-006: informe cuenta corriente (lectura).
- [HU-010](./HU-010-smart-capture-carga-diaria.md) / SPEC-010: patrón SC GEN-03 y turno base.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-21 | Parte B + B1: HU creada desde SPEC-013 (A+A1 2026-10-20). |
| 2026-10-21 | Parte C + C1: enlazada [TR-013](../../04-tareas/100-SistemaPartes/TR-013-carga-paquetes-horas.md). Estado → Especificado. |
| 2026-10-21 | Jira catch-up: Story **PIAPARTES-47** (columna Especificado). |
