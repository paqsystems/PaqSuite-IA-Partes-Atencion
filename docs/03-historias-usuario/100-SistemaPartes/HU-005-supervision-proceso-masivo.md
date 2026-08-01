# HU-005 – Supervisión y proceso masivo sobre tareas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-005 |
| Título | Supervisión: terceros y proceso masivo sobre tareas |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Finalizado |
| Última actualización | 2026-07-31 |
| SPEC origen | [SPEC-005-supervision-proceso-masivo](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) |
| TR relacionada(s) | [TR-005-supervision-proceso-masivo](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-005 | Dónde en esta HU |
|--------------------------------|------------------|
| Supervisión sobre mismo dominio §2.1 | Alcance; R-SU-02 |
| Descubrimiento: filtros + selección §4.3–4.4 | CA-02, CA-03, CA-14 |
| Grilla Framework §4.3b, R-SU-03c | CA-15; Alcance |
| Cerrar/reabrir lote §4.5b | CA-04, CA-05 |
| Actualizar atributos Must §4.6 | CA-16, CA-17, CA-18 |
| Atributos Should / excluidos §4.6 | Alcance; Fuera de alcance; CA-19 (Should) |
| Solo `esSupervisor` §3, R-SU-01 | CA-01 |
| Pantalla dedicada + atajo §4.2 | CA-12 |
| Atomicidad / optimistic lock §4.5 | CA-06, CA-07, CA-13 |
| Tope `PartesMasivoMaxIds` | CA-11 |
| Atributos sobre cerradas R-SU-10 | CA-16 |
| i18n + testids | CA-10 |
| Fuera: import Excel, mails, atributos excluidos | Fuera de alcance |

---

## Narrativa

Como supervisor del módulo Partes  
quiero filtrar y seleccionar tareas, operarlas en una grilla completa del framework, y aplicar en lote cambios de atributos permitidos o del estado cerrado  
para corregir y consolidar el control operativo sin resultados parciales confusos ni acceso indebido de otros perfiles.

---

## Contexto funcional

El supervisor ya puede cargar y editar tareas propias y de terceros no cerradas según SPEC-004. Esta HU formaliza la **supervisión** y el **proceso masivo web**: descubrimiento (periodo + filtros opcionales + tildado), grilla con capacidades GEN, actualización masiva de atributos (Must: tipo de tarea y sin cargo) y cerrar/reabrir. El cierre individual sigue en SPEC-004. Fuente de producto: `05-operacion-diaria-y-supervision.md`. Precondiciones: SPEC-002, SPEC-004.

---

## Alcance incluido

- Supervisión sobre el mismo dominio de tareas (terceros no cerrados en carga = SPEC-004; reafirmado aquí).
- Proceso masivo web (`esSupervisor`):
  - pantalla `/partes/proceso-masivo` vía menú Partes; atajo desde carga **sin** pasar filtros/selección;
  - filtros: periodo obligatorio; cliente, asistente, estado opcionales;
  - selección explícita + «seleccionar todos» del resultado filtrado (+ modal N si >1 página);
  - **ProcessDataGrid:** filter row, totales (duración), column chooser, plantillas, export Excel;
  - **Must acciones:** cerrar / reabrir; actualizar `tipoTarea` y/o `sinCargo` en lote (también sobre filas cerradas);
  - **Should acciones (mismo circuito):** `presencial`, `asistente`, `fecha`;
  - confirmación con cantidad y valores; lote atómico; tope `PartesMasivoMaxIds`;
  - SP + 403/409/422; i18n `partes.masivo.*` + `data-testid`.

---

## Fuera de alcance

- Redefinir captura individual SPEC-004.
- Consultas / dashboard / pivots de informes (SPEC-006).
- Mobile del masivo (SPEC-007).
- Edición masiva de **cliente**, **duración/minutos**, **descripción**.
- Importación Excel y mails (auditoría evolutiva) — distinto del **export** Excel de la grilla.
- Workflow de estados más allá de `cerrado`; ABM maestros (SPEC-003).

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-SU-01 | Solo `esSupervisor`; asistente/cliente denegados (UI + 403). |
| R-SU-02 | Mismo dominio de tareas; no duplica entidades. |
| R-SU-03 | Masivo = set `cerrado` + actualización atributos lista blanca. |
| R-SU-03b | Must: tipo de tarea + sin cargo. Should: presencial, asistente, fecha. Excluidos: cliente, duración, descripción. |
| R-SU-03c | Grilla: ProcessDataGrid con filter row, totales, column chooser, plantillas, export Excel. |
| R-SU-04 | Fechas + selección no vacía; select-all = todo el filtro; >1 página → modal N. |
| R-SU-05 | Confirmación visible (acción, cantidad, valores). |
| R-SU-06 | Lote atómico (inválido → cero cambios). |
| R-SU-06b | Tope param (default 0); exceso → 422. |
| R-SU-06c | `rowVersion`; stale → 409 y cero cambios. |
| R-SU-07 | Refresh inmediato tras éxito. |
| R-SU-08 | Acceso vía SP (MUST). |
| R-SU-09 | Fuera: import Excel, mails, atributos excluidos. Export grilla sí. |
| R-SU-10 | Actualización de atributos permitida sobre tareas cerradas. |

---

## Criterios de aceptación

- [ ] **CA-01** Asistente no supervisor y cliente no acceden (UI + 403 API).
- [ ] **CA-02** Supervisor lista con fechas (+ cliente / asistente / estado opcionales).
- [ ] **CA-03** Selección vacía → no ejecuta; `partes.masivo.emptySelection` (o eq.).
- [ ] **CA-04** Cerrar N abiertas → `cerrado = 1`.
- [ ] **CA-05** Reabrir N cerradas → `cerrado = 0`.
- [ ] **CA-06** Id inexistente en lote → fallo total; 0 cambios.
- [ ] **CA-07** Ya en estado/valor objetivo + versión OK → éxito idempotente.
- [ ] **CA-08** Confirmación con acción, cantidad y resumen (y valor de atributo si aplica).
- [ ] **CA-09** Tras éxito, listado refleja el cambio sin recarga completa de la app.
- [ ] **CA-10** i18n + `data-testid` estables.
- [ ] **CA-11** Tope `PartesMasivoMaxIds`: N>0 y selección>N → 422; 0 = sin tope negocio.
- [ ] **CA-12** Atajo desde carga = link sin query/filtros; entrada principal = menú.
- [ ] **CA-13** `rowVersion` stale → 409; 0 cambios.
- [ ] **CA-14** Select-all = todo el filtro; >1 página → modal N; cancelar no arma selección.
- [ ] **CA-15** Grilla masivo: filter row, totales de duración, column chooser, plantillas y export Excel.
- [ ] **CA-16** Actualizar `sinCargo` en lote (incl. filas cerradas) → las N reflejan el valor.
- [ ] **CA-17** Actualizar `tipoTarea` válido para todas las filas → las N quedan con ese tipo.
- [ ] **CA-18** `tipoTarea` incompatible con el cliente de alguna fila → error; **cero** cambios.
- [ ] **CA-19** (Should) UI/API permiten actualizar `presencial` y/o `asistente` y/o `fecha` en el mismo circuito (puede diferirse a entrega inmediata posterior a Must, documentado en TR).

---

## Escenarios Gherkin

```gherkin
Feature: Proceso masivo de supervisión Partes
  Como supervisor del módulo Partes
  Quiero filtrar, seleccionar y aplicar cambios en lote
  Para consolidar el control sin resultados parciales

  Scenario: Asistente no supervisor no accede al masivo
    Given un asistente autenticado con "esSupervisor" = false
    When intenta acceder al proceso masivo por UI o API
    Then no ve la opción de menú o recibe 403

  Scenario: Supervisor cierra lote de tareas abiertas
    Given un supervisor con filtros de fecha y 2 tareas abiertas seleccionadas
    When confirma la acción "Cerrar"
    Then las 2 tareas quedan con "cerrado" = 1

  Scenario: Supervisor actualiza sin cargo en lote
    Given un supervisor con N tareas seleccionadas (pueden estar cerradas)
    When confirma actualizar "sinCargo" = true
    Then las N tareas quedan con "sinCargo" = true

  Scenario: Tipo de tarea inválido para alguna fila falla atómico
    Given una selección con tareas de clientes distintos
    And un tipo de tarea no válido para al menos un cliente de la selección
    When confirma actualizar ese "tipoTarea"
    Then la API responde error de validación
    And ninguna fila del lote cambia

  Scenario: Selección vacía no ejecuta
    Given un supervisor en proceso masivo sin filas seleccionadas
    When intenta ejecutar una acción de lote
    Then muestra mensaje de selección vacía
    And no invoca la API de lote

  Scenario: Tope de lote por parámetro
    Given "PartesMasivoMaxIds" = 5
    And el supervisor selecciona 6 tareas
    When confirma la acción
    Then recibe 422
    And ninguna fila cambia
```

---

## Supuestos explícitos

- SPEC-004 y SPEC-002 desplegados; layouts/export GEN disponibles en el frontend del producto.
- Atajo desde carga no transfiere filtros; filtros se definen en el masivo.
- Seed param `PartesMasivoMaxIds` default `0` (ya definido en TR previa).
- Optimistic lock con `rowVersion` en todos los lotes.
- CA-19 (Should) puede implementarse en la misma TR o en un incremento inmediato marcado en el plan de tareas TR.

---

## Preguntas abiertas

- Contrato exacto del payload de actualización y nombre SP → **cerrado en TR-005** (ampliación 2026-07-31).
- Prioridad de entrega Should vs Must → **cerrado:** Must primero; Should en plan TR (T-should) sin bloquear F1 de Must si se acuerda en D1.

---

## Riesgos de ambigüedad

- Mezclar «export Excel de grilla» con «importación Excel / auditoría» → mitigado en Fuera de alcance y R-SU-09.
- Selección multi-cliente + un solo tipo → CA-18 / fallo total explícito.

---

## Dependencias

- [SPEC-002](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md)
- [SPEC-004](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md)
- Capacidades GEN de grilla (layouts, export) en el frontend/framework del producto.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1 desde SPEC-005 (cerrar/reabrir). |
| 2026-07-30 | Enlace TR-005; preguntas SP/param cerradas. |
| 2026-07-31 | SPEC-update: grilla Framework + atributos masivos Must/Should; CA-15…19; Gherkin sinCargo/tipo. |
| 2026-07-31 | F1: Finalizado (ver TR-005). |
| 2026-07-31 | F1: Finalizado (ver TR-005). |
