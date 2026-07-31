# HU-005 – Supervisión y proceso masivo sobre tareas

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-005 |
| Título | Supervisión: terceros y proceso masivo sobre tareas |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
| SPEC origen | [SPEC-005-supervision-proceso-masivo](../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) |
| TR relacionada(s) | [TR-005-supervision-proceso-masivo](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-005 | Dónde en esta HU |
|--------------------------------|------------------|
| Supervisión sobre mismo dominio de tareas §2.1 | Alcance; R-SU-02 |
| Proceso masivo cerrar/reabrir §4.3–4.6 | Alcance; CA-04, CA-05 |
| Solo `esSupervisor` §3, R-SU-01 | CA-01; R-SU-01 |
| Pantalla dedicada + atajo desde carga §4.2 A1 | Alcance; Supuestos |
| Filtros previos mínimos §4.4 | CA-02; R-SU-04 |
| Selección explícita + confirmación §4.3, R-SU-05 | CA-03, CA-08 |
| Atomicidad e idempotencia §4.5, R-SU-06 | CA-06, CA-07 |
| Tope lote vía `PQ_PARAMETROS_GRAL` §4.5 A1 | CA-11; R-SU-06b |
| Acceso vía SP §4.8 R-SU-08 | Alcance backend |
| i18n + `data-testid` §2.1, §4.7 | CA-10 |
| Refresh inmediato tras éxito §4.3, R-SU-07 | CA-09 |
| Fuera alcance mobile/consultas/Excel §2.2 | Fuera de alcance |

---

## Narrativa

Como supervisor del módulo Partes  
quiero operar sobre tareas de terceros y cerrar o reabrir conjuntos de registros de forma atómica  
para consolidar el control operativo sin resultados parciales confusos ni acceso indebido de otros perfiles.

---

## Contexto funcional

El supervisor ya puede cargar y editar tareas propias y de terceros no cerradas según SPEC-004; esta HU formaliza la **supervisión** sobre el mismo universo ampliado de tareas y añade el **proceso masivo web** para cambiar `cerrado` en lotes. El cierre/reapertura individual sigue en SPEC-004; el masivo de N filas es contrato de este SPEC. Precondiciones: SPEC-002 (identidad y `esSupervisor`), SPEC-004 (carga diaria desplegable), flag `supervisor` en dominio (SPEC-001/002).

---

## Alcance incluido

- Capacidades de supervisión sobre el mismo dominio de tareas (no módulo aislado):
  - vista / filtrado de tareas de terceros (universo supervisor);
  - creación / edición / eliminación de tareas de terceros **no cerradas** (detalle operativo en SPEC-004; reafirmado aquí como capacidad de supervisión);
  - cierre / reapertura **individual** (SPEC-004 §4.6) y **masiva** (este SPEC).
- Proceso masivo MVP web (DevExtreme):
  - acciones: **cerrar** (`cerrado = 1`) y **reabrir** (`cerrado = 0`);
  - solo `esSupervisor = true`;
  - pantalla dedicada «Proceso masivo» vía **menú Partes** (solo supervisor); desde carga diaria: **atajo mínimo** (link) **sin** pasar filtros ni selección; misma API de lote;
  - filtros previos (rango de fechas obligatorio; cliente, asistente, estado `cerrado` opcionales/recomendados) + listado + selección explícita + confirmación con preview;
  - rechazo si selección vacía o inválida;
  - atomicidad del lote e idempotencia en filas ya en estado objetivo.
- Delimitación API: denegar a asistente no supervisor y a cliente (403); no confiar solo en ocultar UI.
- Acceso a datos vía stored procedures (R-SU-08).
- Parámetro general de tope de lote en `PQ_PARAMETROS_GRAL` (default `0` = sin límite de negocio).
- i18n (`partes.masivo.*`) + `data-testid` en proceso masivo.

---

## Fuera de alcance

- Redefinir validaciones de captura de alta/edición (SPEC-004).
- Consultas agrupadas / dashboard / pivots (SPEC-006).
- Mobile del proceso masivo (excluido; SPEC-007).
- «Auditoría de partes» ampliada: edición masiva de campos de negocio, importación Excel, mails selectivos — evolución distinta.
- Aprobación formal / workflow de estados más allá de `cerrado`.
- ABM maestros (SPEC-003).

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-SU-01 | Solo `esSupervisor`; asistente no supervisor y cliente denegados (UI + 403 API). |
| R-SU-02 | Supervisión amplía el mismo dominio de tareas; no duplica entidades. |
| R-SU-03 | Masivo MVP = solo set de `cerrado` (cerrar/reabrir); no modifica otros campos de negocio. |
| R-SU-04 | Exige filtros de fecha + selección explícita no vacía; «Seleccionar todos» = todo el resultado filtrado (todas las páginas); si abarca >1 página → modal «Afectará a N partes. ¿Confirma?» antes de armar la selección. |
| R-SU-05 | Confirmación visible antes de ejecutar (acción, cantidad, resumen). |
| R-SU-06 | Lote atómico: lote inválido → cero cambios; lote válido → transacción única; idempotente si fila ya está en estado objetivo **y** versión válida. |
| R-SU-06c | Optimistic lock: cada ítem con `rowVersion`; cualquier desactualizado → **409** y cero cambios en el lote. |
| R-SU-06b | Tope de lote = parámetro general (default `0` = sin límite); si `N > 0` y selección > `N` → 422 sin procesar. |
| R-SU-07 | Resultado visible de inmediato tras éxito (refresh de grilla). |
| R-SU-08 | Acceso a datos vía SP (MUST). |
| R-SU-09 | Fuera de alcance: Excel, mails, edición masiva de campos de negocio. |

Efecto de cerrar/reabrir: **cerrar** (`cerrado = 1`) saca la fila del circuito ordinario de edición/eliminación (SPEC-004); **reabrir** (`cerrado = 0`) la vuelve editable/eliminable según SPEC-004.

---

## Criterios de aceptación

- [ ] **CA-01** Asistente no supervisor y cliente no acceden al proceso masivo (UI oculta / sin menú + 403 API).
- [ ] **CA-02** Supervisor lista tareas de terceros aplicando filtros de fecha (y filtros opcionales de cliente, asistente, estado `cerrado`).
- [ ] **CA-03** Sin selección o selección vacía → no ejecuta; mensaje claro (`partes.masivo.emptySelection` o equivalente).
- [ ] **CA-04** Cerrar lote de N tareas abiertas → las N quedan `cerrado = 1` y dejan de editarse en carga ordinaria (SPEC-004).
- [ ] **CA-05** Reabrir lote de N tareas cerradas → quedan `cerrado = 0` y editables según SPEC-004.
- [ ] **CA-06** Incluir un `id` inexistente en el lote → ninguna fila del lote cambia (fallo total atómico).
- [ ] **CA-07** Filas ya en estado objetivo no rompen el lote (idempotencia: cuentan como éxito).
- [ ] **CA-08** UI muestra confirmación con acción, cantidad y resumen visible antes de ejecutar.
- [ ] **CA-09** Tras éxito, el listado refleja el nuevo estado sin recarga manual completa de la aplicación.
- [ ] **CA-10** i18n + `data-testid` estables en pantalla/proceso masivo.
- [ ] **CA-11** Si el parámetro de tope es `N > 0` y la selección supera `N` → 422 y ninguna fila cambia; si el parámetro es `0` (default) → no aplica tope de negocio.
- [ ] **CA-12** Desde carga diaria, el atajo es un link mínimo a «Proceso masivo» que **no** arrastra filtros ni selección; la entrada principal al proceso es el menú Partes.
- [ ] **CA-13** Lote con algún `rowVersion` desactualizado → HTTP 409; ninguna fila del lote cambia; tras refrescar se puede rearmar la selección.
- [ ] **CA-14** «Seleccionar todos los del resultado actual» selecciona **todas** las filas del filtro (todas las páginas). Si la selección implica más de una página, se muestra modal «Afectará a N partes. ¿Confirma?»; cancelar no arma esa selección masiva.

---

## Escenarios Gherkin

```gherkin
Feature: Proceso masivo de supervisión Partes
  Como supervisor del módulo Partes
  Quiero cerrar o reabrir tareas en lote de forma atómica
  Para consolidar el control operativo sin resultados parciales

  Scenario: Asistente no supervisor no accede al masivo
    Given un asistente autenticado con "esSupervisor" = false
    When intenta acceder al proceso masivo por UI o API
    Then no ve la opción de menú o recibe 403
    And ninguna fila de tarea cambia su estado "cerrado"

  Scenario: Supervisor cierra lote de tareas abiertas
    Given un supervisor autenticado con filtros de fecha aplicados
    And un listado con al menos 2 tareas con "cerrado" = 0
    When selecciona explícitamente esas 2 filas
    And elige la acción "Cerrar"
    And confirma la operación con la cantidad mostrada
    Then las 2 tareas quedan con "cerrado" = 1
    And el listado refleja el nuevo estado sin recargar toda la app

  Scenario: Lote con id inexistente falla de forma atómica
    Given un supervisor con una selección que incluye un "id" inexistente
    When confirma la acción de cerrar o reabrir
    Then la API responde con error de lote inválido
    And ninguna fila del lote cambia su estado "cerrado"

  Scenario: Idempotencia en filas ya en estado objetivo
    Given un supervisor que selecciona tareas ya cerradas para acción "Cerrar"
    When confirma la operación
    Then el lote completa con éxito
    And las tareas permanecen con "cerrado" = 1

  Scenario: Selección vacía no ejecuta
    Given un supervisor en la pantalla de proceso masivo
    When intenta ejecutar cerrar o reabrir sin filas seleccionadas
    Then el sistema muestra mensaje claro de selección vacía
    And no invoca la API de lote

  Scenario: Tope de lote por parámetro general
    Given el parámetro "PartesMasivoMaxIds" (o clave acordada) = 5
    And un supervisor selecciona 6 tareas válidas
    When intenta confirmar la acción masiva
    Then recibe 422 con mensaje claro
    And ninguna fila cambia su estado "cerrado"
```

---

## Supuestos explícitos

- SPEC-004 (carga diaria) y SPEC-002 (identidad funcional) están desplegados antes de implementar esta HU.
- La grilla de carga diaria (SPEC-004) expone columna **Asistente** según SPEC-004 §4.3. Atajo desde carga: link mínimo a la pantalla dedicada **sin** transferir filtros/selección; filtros se definen en el masivo.
- El universo supervisor no filtra por propietario propio (capa 1 SPEC-002), acotado por los filtros del proceso.
- Seed incluirá parámetro de tope masivo con default `0` (sin límite de negocio); clave exacta se fija en TR.
- Actualizaciones concurrentes: ~~pendiente~~ → **cerrado:** optimistic lock (`row_version`); conflicto → **409** + refrescar; en masivo, un conflicto invalida todo el lote.

---

## Preguntas abiertas

- Nombre del SP / clave param / select-all / preview: ~~pendiente~~ → **cerrado en [TR-005](../../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md):** `pq_sp_partes_tarea_masivo_set_cerrado`, param `PartesMasivoMaxIds`, `GET /partes/tareas/ids`, preview = acción + N + rango fechas + hasta 5 muestras.
- Detalle de composición UI del atajo desde carga diaria: ~~pendiente~~ → **cerrado:** link/acción mínima hacia pantalla dedicada; **no** preserva ni pasa filtros de la carga; entrada principal = menú Partes.
- Estrategia ante actualizaciones concurrentes: ~~pendiente~~ → **cerrado:** optimistic lock; 409; masivo atómico ante conflicto.
- «Seleccionar todos los del resultado actual» vs paginación: ~~pendiente~~ → **cerrado:** todo el resultado filtrado; si >1 página → modal de confirmación con cantidad N («Afectará a N partes. ¿Confirma?»).

---

## Riesgos de ambigüedad

- Preview de confirmación de acción y claves i18n lote: ~~pendiente~~ → **cerrado en TR-005** (`partes.masivo.*`, `partes.masivo.conflictoVersion`).

---

## Dependencias

- [SPEC-002](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) — identidad funcional y `esSupervisor`.
- [SPEC-004](../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) — carga diaria, reglas de `cerrado`, edición de terceros.
- SPEC-001 — flag `supervisor` en dominio.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1: HU creada y enriquecida desde SPEC-005. |
| 2026-07-30 | Batch: atajo desde carga = link mínimo sin filtros; entrada principal = menú. |
| 2026-07-30 | Batch: concurrencia optimistic lock (`row_version`); 409; masivo atómico. |
| 2026-07-30 | Batch: «Seleccionar todos» = resultado filtrado; modal N si >1 página. |
| 2026-07-30 | Enlace TR-005 (Parte C+C1); preguntas SP/param/preview cerradas. |
