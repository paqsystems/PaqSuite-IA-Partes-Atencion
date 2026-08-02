# HU-009 – Importación de partes desde Excel (Carga diaria)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-009 |
| Título | Importar altas de partes (tareas) desde Excel embebido en Carga diaria |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-08-02 |
| SPEC origen | [SPEC-009-importacion-partes-excel](../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md) |
| TR relacionada(s) | [TR-009-importacion-partes-excel](../../04-tareas/100-SistemaPartes/TR-009-importacion-partes-excel.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-009 | Dónde en esta HU |
|--------------------------------|------------------|
| Embebido solo en Carga diaria; sin menú/pantalla aparte (§4.1, R-IMP-01/09/10) | Alcance; CA-01; R-IMP-01 |
| Toolbar fila exclusiva + flujo plantilla→validar→Procesar (§4.1) | Alcance; CA-01, CA-09 |
| Plantilla columnas §4.2 (R-IMP-03) | Alcance; CA-02; R-IMP-03 |
| Persistencia alta = campos SPEC-004 + `es_tarea=1` + `cerrado=0` (§4.2) | CA-08; R-IMP-05 |
| Asistente vs supervisor `asistente` (§4.3, R-IMP-04) | Actores; CA-03, CA-04; R-IMP-04 |
| Cliente no importa (§3, R-IMP-02) | Actores; CA-05; R-IMP-02 |
| Validación fila §4.4 (R-IMP-06) | CA-06; R-IMP-06 |
| Fecha Excel locale / nativa (§4.4.1, R-IMP-12) | CA-07; R-IMP-12 |
| Fecha futura sin bloqueo duro (§4.4.1) | CA-07b |
| Filas vacías ignoradas; sin dedupe (R-IMP-13) | CA-12; R-IMP-13 |
| Parcial / Procesar (§4.5, R-IMP-07) | CA-09; R-IMP-07 |
| Fallo a mitad / no éxito silencioso (§4.5.5) | Supuestos; **cerrado en TR-009** (txn atómica) |
| Refresco grilla filtros vigentes (§4.6, R-IMP-08) | CA-10; R-IMP-08 |
| Mobile excluido (R-IMP-09) | Fuera de alcance; CA-11 |
| i18n / testids GEN (R-IMP-10) | Alcance; CA-01 |
| SP MUST (R-IMP-11) | Alcance; R-IMP-11 |
| Capacidad GEN habilitada (precondición §3) | Contexto; Supuestos |
| Fuera: compras, edit Excel, auditoría, IA, ABM catálogo, redefinir GEN, SPEC-005 | Fuera de alcance |
| Criterios verificables §5 | CA-01…12 |

---

## Narrativa

Como asistente o supervisor de Partes  
quiero importar muchas tareas desde un Excel dentro de la pantalla de Carga diaria  
para dar de alta partes válidos de forma masiva sin cargar fila por fila, respetando las mismas reglas de negocio que la carga manual.

---

## Contexto funcional

Registrar dedicación fila a fila es costoso cuando el dato ya está en planillas. Esta historia cubre la **importación de altas** de tareas embebida en Carga diaria: plantilla fija, validación por fila, procesamiento con parcial permitido y grabación real con `es_tarea = true`, usando el motor GEN de importaciones Excel (adopción, no redefinición). Tras grabar, la grilla de carga se refresca sin perder el contexto de filtros del usuario.

### Precondiciones (SPEC §3)

- Sesión Partes usable (asistente o supervisor).
- Maestros usables disponibles para resolver códigos del Excel.
- Pantalla de Carga diaria operativa.
- Capacidad de importación Excel GEN **habilitada** en la instalación.

### Actores

| Actor | Puede importar |
|-------|----------------|
| Asistente (`esSupervisor = false`) | Sí |
| Supervisor (`esSupervisor = true`) | Sí |
| Cliente | No |

---

## Alcance incluido

- Toolbar GEN (**Descargar plantilla** \| **Importar**) **solo** en Carga diaria, en **fila exclusiva** (no compartida con filtros).
- Flujo: plantilla → subir `.xlsx` → validar/staging → errores por fila → **Procesar** (si hay válidas) → refresco de grilla si hubo altas.
- Plantilla con cabeceras canónicas: `cliente`, `asistente`, `tipo_tarea`, `fecha`, `duracion` (`hh:mm`), `sin_cargo`, `presencial`, `descripcion`.
- Flag de proceso con **procesamiento parcial permitido** (`permiteProcesamientoParcial` / allowPartial).
- Validaciones de fila alineadas a alta de carga diaria: códigos usables, tipo ∈ universo del cliente, duración `hh:mm` positiva múltiplo del tramo (default 15, tope 1440 min), booleanos verdadero/falso (case-insensitive), descripción no vacía.
- Propietario: no supervisor fuerza sesión; supervisor usa `asistente` del archivo.
- Cada alta: `es_tarea = true`, `cerrado = 0` (abierta).
- `fecha` en Excel según formato de fecha configurado de la app; UI de carga sigue presentando según locale; fecha nativa Excel aceptada si el motor la tipifica.
- Filas Excel totalmente vacías ignoradas; sin deduplicación obligatoria.
- Solo web; i18n `excelImport.*` + mensajes de validación Partes; testids GEN `excelImport.*`.
- Persistencia de negocio vía SP (MUST).

---

## Fuera de alcance

- Pantalla hermana o ítem de menú dedicado a la importación.
- Importar compras / movimientos con `es_tarea = false`.
- Editar o eliminar partes existentes vía Excel.
- Auditoría con mails / consulta auditora ampliada.
- Mobile / Capacitor.
- Smart Capture / IA rellenando el Excel.
- ABM web del catálogo GEN de procesos Excel.
- Redefinir o reemplazar el motor GEN-14.
- Proceso masivo de supervisión (SPEC-005 / HU-005).
- Dialog extra de confirmación además del botón **Procesar** (Should del SPEC, no Must).
- Sinónimos booleanos distintos de `verdadero`/`falso` (Should).
- Plantillas distintas asistente vs supervisor (Should).

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-IMP-01 | La importación vive solo embebida en Carga diaria; adopta el canal GEN de Excel (no lo reimplementa). |
| R-IMP-02 | El cliente funcional no importa; no ve la capacidad y la API lo deniega. |
| R-IMP-03 | Plantilla fija; `cliente` siempre obligatorio en archivo. |
| R-IMP-04 | Asistente no supervisor: `asistente` opcional; se fuerza el de sesión; si viene otro código → error de fila. Supervisor: `asistente` obligatorio y usable. |
| R-IMP-05 | Toda fila grabada es tarea (`es_tarea = true`) y alta ordinaria abierta (`cerrado = 0`). |
| R-IMP-06 | Validaciones alineadas a carga diaria (tramo, tipos del cliente, descripción, maestros usables). |
| R-IMP-07 | Con filas válidas y con error, **Procesar** graba solo las válidas; sin Procesar no se graba nada; sin válidas no se habilita Procesar. |
| R-IMP-08 | Tras grabar ≥1 fila, se refresca la grilla con los filtros vigentes (sin resetearlos). |
| R-IMP-09 | No disponible en mobile. |
| R-IMP-10 | Toolbar en fila propia; textos e identificadores de prueba del canal GEN. |
| R-IMP-11 | La grabación de negocio va por SP (MUST). |
| R-IMP-12 | `fecha` en Excel sigue el formato de fecha configurado de la app; también vale fecha nativa tipificada por el motor. |
| R-IMP-13 | Filas Excel vacías se ignoran; no hay deduplicación obligatoria. |

---

## Criterios de aceptación

- [ ] **CA-01** En Carga diaria (web), con capacidad Excel habilitada, el asistente/supervisor ve la toolbar GEN (plantilla + importar) en **fila propia**; no existe menú ni ruta aparte de importación en este MVP.
- [ ] **CA-02** La plantilla descargable incluye exactamente las cabeceras canónicas: `cliente`, `asistente`, `tipo_tarea`, `fecha`, `duracion`, `sin_cargo`, `presencial`, `descripcion`.
- [ ] **CA-03** Asistente no supervisor: importa sin `asistente` (omitida o vacía) → altas con su `usuario_id`; si una fila trae código de asistente distinto al de sesión → esa fila en error y no se graba.
- [ ] **CA-04** Supervisor: fila sin `asistente` usable → error; con `asistente` usable → se graba ese propietario.
- [ ] **CA-05** Usuario cliente no ve la toolbar y no puede invocar la importación (API deniega).
- [ ] **CA-06** Fila inválida (ej.: sin `cliente`; tipo fuera de universo del cliente; `duracion` no `hh:mm` / no múltiplo del tramo / fuera de rango; `sin_cargo`/`presencial` ≠ verdadero|falso; `descripcion` vacía) → aparece en grilla de errores (nº de fila + mensaje) y no se graba.
- [ ] **CA-07** Fechas en Excel aceptadas según el formato de fecha configurado de la app y/o como fecha nativa Excel; la UI de Carga diaria sigue mostrando fechas según locale (no fuerza ISO en pantalla).
- [ ] **CA-07b** Fecha futura en una fila **no** bloquea por sí sola el alta en el MVP (sin bloqueo duro; sin confirmación interactiva por fila).
- [ ] **CA-08** Tras Procesar con altas > 0, cada fila grabada tiene `es_tarea = true` y `cerrado = 0`, y puede listarse en Carga diaria / consultas de tareas según filtros aplicables.
- [ ] **CA-09** Lote mixto: cerrar/cancelar sin Procesar → 0 altas; `validRows = 0` → Procesar no habilitado; Procesar con válidas+errores → solo válidas.
- [ ] **CA-10** Tras Procesar con ≥1 alta, la grilla de Carga diaria se vuelve a cargar **conservando** filtros vigentes (fechas, cliente, asistente, estado, etc.).
- [ ] **CA-11** En mobile la importación Excel de partes no se ofrece.
- [ ] **CA-12** Filas Excel totalmente vacías no generan error ni alta; filas duplicadas en contenido pueden generar múltiples altas (sin dedupe Must).

### Escenarios Gherkin

```gherkin
Feature: Importación Excel en Carga diaria
  Como asistente o supervisor
  Quiero importar tareas desde Excel en Carga diaria
  Para registrar dedicación masiva con las mismas reglas de la carga manual

  Scenario: Asistente importa sin columna asistente
    Given un asistente no supervisor en Carga diaria con filtros de fecha aplicados
    And un Excel válido sin columna "asistente" o con ella vacía
    When valida y Procesa el lote
    Then se crean tareas a su nombre con es_tarea verdadero y cerrado falso
    And la grilla se refresca conservando esos filtros

  Scenario: Asistente no puede importar como otro
    Given un asistente no supervisor cuyo código es "A1"
    And una fila con asistente "A2"
    When valida el lote
    Then esa fila figura en error
    And Procesar no la graba

  Scenario: Supervisor asigna propietario por fila
    Given un supervisor en Carga diaria
    And un Excel con asistente usable distinto en cada fila válida
    When valida y Procesa
    Then cada tarea queda con el asistente indicado en su fila

  Scenario: Lote mixto solo graba válidas si Procesa
    Given un lote con filas válidas y filas con error
    When el usuario cierra sin Procesar
    Then no se crea ninguna tarea
    When el usuario Procesa
    Then solo se graban las filas válidas

  Scenario: Cliente no importa
    Given un usuario con identidad funcional cliente
    When abre el sistema en web
    Then no dispone de la importación Excel de partes

  Scenario: Filas vacías se ignoran
    Given un Excel con filas de datos válidas y filas totalmente vacías
    When valida el lote
    Then las filas vacías no aparecen como error
    And no se cuentan como filas válidas a grabar
```

---

## Supuestos explícitos

- El motor GEN de importaciones Excel está disponible en los paquetes Framework que consume el host.
- La acción explícita **Procesar** con parcial habilitado cumple la confirmación de “grabar solo válidas” (dialog adicional = Should).
- La instalación tiene la capacidad Excel import habilitada; si está deshabilitada, la toolbar no se ofrece (comportamiento GEN).
- El código de proceso es **`partes.tareas.import`** (TR-009); umbrales async/queued = GEN; el host maneja `onComplete` done/partial/failed/queued sin romper el refresco.
- Al Procesar: **transacción atómica** del conjunto de válidas (TR-009); ante fallo → `failed`, sin éxito silencioso.

---

## Preguntas abiertas

| # | Pregunta | Destino | Resolución |
|---|----------|---------|------------|
| 1 | Valor final de `processCode` | TR | **`partes.tareas.import`** (TR-009) |
| 2 | Comportamiento si Procesar falla a mitad | TR | **Txn atómica** del batch de válidas; rollback + `failed` (TR-009) |
| 3 | UX lote `queued` / async y refresco | TR | Sin refresh en `queued`; refresh si `done`/`partial` y `processedRows>0` (TR-009) |

Cerradas en Parte C (TR-009). Ninguna bloquea C1/D1.

---

## Riesgos de ambigüedad

| Riesgo | Mitigación en SPEC/HU |
|--------|------------------------|
| Confundir importación con pantalla/menú propio | CA-01 / R-IMP-01: solo embebida en Carga diaria |
| Formato de fecha Excel vs UI | CA-07 / R-IMP-12: parseo por formato configurado de app; UI independiente |
| “Preguntar” interpretado como dialog modal extra | CA-09: Procesar GEN = confirmación Must |
| Importar como edición masiva | Fuera de alcance: solo altas |
| Duplicados silenciados | CA-12: sin dedupe Must |

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-02 | Parte B: HU-009 desde SPEC-009 (post A1). |
| 2026-08-02 | Parte B1: enriquecimiento desde SPEC (actores, CA-07b/12, supuestos, riesgos, Gherkin). |
| 2026-08-02 | Parte C: enlace TR-009; preguntas B1 cerradas en TR. |
