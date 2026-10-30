# HU-006-update — Informe Paquete de Horas (saldo y destaque compras)

Jira: [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52)

## Metadatos

| Campo | Valor |
|-------|-------|
| **ID** | HU-006-update |
| **Título** | Corregir saldo por cliente/renglón y destacar compras en informe Paquete de Horas |
| **Épica / carpeta** | `100-SistemaPartes` |
| **Clasificación** | MUST-HAVE (CC PQ #8) |
| **Estado** | **Especificado** (Parte B + B1 2026-10-30) |
| **Última actualización** | 2026-10-30 |
| **HU base** | [HU-006-consultas-dashboard-navegacion](../../100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) |
| **SPEC origen** | [SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../05-open-spec/updates/100-SistemaPartes/SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **TR relacionada** | [TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe](../../../04-tareas/updates/100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) |
| **Origen** | CC PQ **#8** · 25/10/2026 · [`18-obtencion-saldo-por-renglon.md`](../../../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) |

---

## Trazabilidad SPEC

| Criterio / entregable SPEC-006-update | Cobertura en esta HU |
|----------------------------------------|----------------------|
| Saldo S₀ + running por `cliente_id` §4.1 | CA-05, CA-06, CA-07; R-INF-PH-01, R-INF-PH-02 |
| Cliente obligatorio supervisor/asistente §2.1 | CA-01, CA-02; Gherkin |
| Una fila «Saldo inicial» §2.1 | CA-05 |
| Orden fecha/id §2.1 | R-INF-PH-02 |
| Filtro tipo cliente §2.1, R-INF-PH-03 | CA-08 |
| Destaque duración compras §4.2, CA-03/04 | CA-03, CA-04; R-INF-PH-04 |
| Signos duración vs saldo §4.3 | CA-03, CA-09 |
| SP `pq_sp_partes_informe_paquete_horas` §2.2 | CA-10; R-INF-PH-06 |
| Pivot sin Saldo §2.1 | CA-11; R-INF-PH-05 |
| i18n saldo inicial | CA-12 |

---

## Narrativa

Como **supervisor** o **asistente** con acceso al informe  
quiero consultar el **Paquete de Horas** para **un cliente** a la vez, con saldo inicial y saldo acumulado por renglón coherentes con la cuenta corriente  
para analizar consumo y compras sin mezclar clientes ni confundir filas de paquete con tareas.

Como **cliente funcional** quiero ver solo mi organización con la misma lógica de saldo, sin elegir cliente en pantalla.

---

## Contexto funcional

El informe mezcla movimientos `es_tarea = true` (suman minutos al saldo) y `es_tarea = false` (restan). El CC #8 detectó saldo incorrecto y falta de destaque visual en compras. La corrección mueve el cálculo a **T-SQL** (regla 74) y exige **cliente obligatorio** en consultas de supervisor/asistente para una sola serie temporal por cliente.

**Precondiciones:** filtros fecha desde/hasta según SPEC-006 base; catálogo clientes usables; permisos informe según rol.

---

## Alcance incluido

- **API** `GET /api/v1/partes/informes/paquete-horas`: validar `clienteId` requerido para supervisor/asistente (**422** + clave i18n si falta); actor cliente usa su cliente de sesión sin selector.
- **SP** `pq_sp_partes_informe_paquete_horas`: saldo inicial S₀, movimientos del periodo, running balance por `(fecha ASC, id ASC)`; **una** fila sintética saldo inicial por respuesta; envelope actual (items + metadatos acordados TR).
- **Frontend** `PaqueteHorasPage`: no disparar búsqueda al abrir sin cliente seleccionado (supervisor/asistente); selector cliente obligatorio para consultar.
- **Grilla:** columna Duración — compras (`esTarea === false`) con texto `-hh:mm` y estilo rojo; tareas estilo normal.
- **Columna Saldo:** formato `hh:mm` con signo algebraico coherente.
- **i18n** etiqueta fila saldo inicial (`partes.informe.saldoInicialLabel` o equivalente).
- **Tests:** integración API multi-cliente (series aisladas); Vitest formatter duración negativa.
- Confirmar pivot **sin** campo Saldo (sin cambio).

---

## Fuera de alcance

- Cambiar filtros obligatorios base (fechas; universo tipo cliente).
- Informe mobile kardex — paridad saldo inicial KPI salvo TR-007 futuro (observación SPEC).
- Nueva UI pivot o export con reglas distintas de signo (export: mismo signo textual si ya existe).
- Mantener `PartesInformeOperations::paqueteHoras` en **producción sqlsrv** (prohibido post-fix).

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-INF-PH-01 | Saldo inicial y running **solo** para el `cliente_id` de la consulta; prohibido mezclar clientes en una respuesta. |
| R-INF-PH-02 | Tras la fila saldo inicial, movimientos ordenados `(fecha ASC, id ASC)`. |
| R-INF-PH-03 | Filtro **tipo de cliente** aplica a S₀ y movimientos del periodo (SPEC-006 base). |
| R-INF-PH-04 | Compras: duración visible `-hh:mm` y destaque rojo; tareas: duración positiva normal. |
| R-INF-PH-05 | Campo Saldo excluido del catálogo pivot. |
| R-INF-PH-06 | En sqlsrv, lectura del informe **Must** ser vía SP; regla 74 sin excepción en prod. |
| R-INF-PH-07 | `duracionMinutos` en API permanece positivo; signo en duración de compras es presentacional. |

---

## Criterios de aceptación

- [ ] **CA-01** Supervisor/asistente: sin `clienteId` en request → **422** envelope con clave i18n; no se devuelven filas de negocio.
- [ ] **CA-02** FE supervisor/asistente: al abrir la pantalla **no** ejecuta consulta automática; el usuario debe elegir cliente y accionar buscar.
- [ ] **CA-03** Filas con `es_tarea = false`: columna Duración muestra `-hh:mm` y clase/CSS de destaque rojo (`data-testid` estable opcional en celda).
- [ ] **CA-04** Filas con `es_tarea = true`: duración `hh:mm` positiva sin destaque rojo.
- [ ] **CA-05** Exactamente **una** fila «Saldo inicial» por respuesta; S₀ = suma algebraica de movimientos del cliente con `fecha < fechaDesde` (mismo universo filtros actor + tipo cliente).
- [ ] **CA-06** Cada fila de movimiento expone saldo acumulado del **mismo** cliente según orden §4.1 SPEC.
- [ ] **CA-07** Dos consultas consecutivas con clientes A y B → saldos independientes; fixture regression A solo tareas / B tarea+paquete sin cruce.
- [ ] **CA-08** Cambiar filtro tipo cliente altera S₀ y movimientos de forma coherente (mismo universo).
- [ ] **CA-09** Columna Saldo muestra signo algebraico coherente (deuda de horas = saldo negativo visible).
- [ ] **CA-10** En entorno sqlsrv, endpoint invoca SP desplegado; no hay ruta prod que calcule saldo solo en PHP.
- [ ] **CA-11** Catálogo pivot del informe no incluye campo Saldo.
- [ ] **CA-12** Etiqueta i18n de fila saldo inicial visible en los 5 locales del producto.

---

## Escenarios Gherkin

```gherkin
Feature: Informe Paquete de Horas — saldo y destaque (CC PQ #8)

  Scenario: Consulta sin cliente rechazada
    Given un supervisor autenticado en Partes
    And fecha desde y hasta válidas
    When solicita paquete-horas sin clienteId
    Then la API responde 422
    And el envelope incluye clave i18n de cliente requerido

  Scenario: Saldo acumulado por renglón para un cliente
    Given un cliente C con saldo inicial S0 antes del periodo
    And movimientos de tarea y paquete dentro del periodo
    When consulto paquete-horas para C
    Then la primera fila es saldo inicial con saldo S0
    And cada fila siguiente muestra saldo running según es_tarea
    And no aparecen movimientos de otro cliente

  Scenario: Compra destacada en duración
    Given una fila de paquete es_tarea false con 120 minutos
    When la grilla renderiza la columna Duración
    Then se muestra "-02:00" o equivalente hh:mm negativo
    And la celda usa estilo de destaque rojo

  Scenario: Cliente funcional sin selector
    Given un usuario cliente funcional de la organización C
    When abre el informe Paquete de Horas
    Then la consulta usa C sin pedir clienteId en UI
    And aplica la misma lógica de saldo por renglón
```

---

## Supuestos explícitos

- Paridad con [`18-obtencion-saldo-por-renglon.md`](../../../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) y window SQL PQ en [`pq_sp_partes_informe_paquete_horas-REFERENCE-saldo-window.sql`](../../../../backend/scripts/sql/partes/pq_sp_partes_informe_paquete_horas-REFERENCE-saldo-window.sql) (TR-006-update).
- Tests sqlite pueden usar shim documentado en TR **solo** en CI/tests, no en Forge/prod.

---

## Preguntas abiertas

- Ninguna bloqueante para Parte C (A1 cerrado). Detalle de parámetros SP y shape JSON exacto → TR.

---

## Riesgos de ambigüedad

- **Bajo:** convención de presentación de |S₀| en fila saldo inicial — TR alinea con §4.3 SPEC.
- **Medio:** consultas históricas con `clienteId` omitido en logs — solo afecta post-deploy; no migración de datos.

---

## Veredicto B1

**HU apta para TR (Parte C)** — cobertura alineada a SPEC-006-update y A1; sin alcance fuera del SPEC.
