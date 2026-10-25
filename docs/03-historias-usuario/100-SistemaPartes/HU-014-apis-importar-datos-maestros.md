# HU-014 – APIs para importar datos maestros

Jira: [PIAPARTES-50](https://paqsystems.atlassian.net/browse/PIAPARTES-50)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-014 |
| Título | APIs para importar datos maestros |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Especificado |
| Última actualización | 2026-10-23 |
| SPEC origen | [SPEC-014-apis-importar-datos-maestros](../../05-open-spec/100-SistemaPartes/SPEC-014-apis-importar-datos-maestros.md) |
| TR relacionada(s) | [TR-014-apis-importar-datos-maestros](../../04-tareas/100-SistemaPartes/TR-014-apis-importar-datos-maestros.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-014 | Dónde en esta HU |
|--------------------------------|------------------|
| Cinco APIs por recurso | Alcance; CA-01 |
| Reconciliación completa y bajas lógicas | Reglas R-IM-01…R-IM-06; CA-02…CA-06 |
| Procesamiento parcial por fila | R-IM-07; CA-07 |
| `dryRun` | R-IM-08; CA-08 |
| Permiso dedicado | R-IM-09; CA-09 |
| Stored procedures por lote | R-IM-10; CA-10 |
| Límites y parámetros configurables | R-IM-11; CA-11 |
| Normalización de entrada | R-IM-12…R-IM-14; CA-12 |
| Envelope y claves i18n | R-IM-15; CA-13 |
| Ejemplos completos OpenAPI | R-IM-16; CA-14 |
| Exclusiones del SPEC | Fuera de alcance |

---

## Narrativa

Como sistema externo autorizado  
quiero sincronizar los datos maestros de Partes mediante APIs REST autenticadas  
para mantener actualizados clientes, asistentes, tipos y asignaciones sin cargar manualmente cada registro.

---

## Contexto funcional

La integración recibe el universo completo vigente de un único recurso por request. El sistema compara ese universo con la base: crea los registros nuevos, actualiza los existentes, informa los que no cambian e intenta inhabilitar lógicamente los que no aparecen.

La integración no elimina físicamente datos ni crea usuarios, credenciales o accesos de cliente.

## Actores

| Actor | Capacidad |
|-------|-----------|
| Sistema externo o usuario técnico autorizado | Ejecutar los endpoints y recibir el resultado por fila |
| Usuario autenticado sin `partes.integracion.maestros` | No puede ejecutar la integración |
| Usuario anónimo | No puede ejecutar la integración |

---

## Alcance incluido

- Endpoint por recurso:
  - `POST /api/v1/integracion/maestros/clientes`
  - `POST /api/v1/integracion/maestros/asistentes`
  - `POST /api/v1/integracion/maestros/tipos-cliente`
  - `POST /api/v1/integracion/maestros/tipos-tarea`
  - `POST /api/v1/integracion/maestros/asignaciones-cliente-tipo`
- Reconciliación completa del recurso recibido.
- Alta, actualización, no-op e inhabilitación lógica.
- Sincronización completa de asignaciones mediante clientes y `tiposTarea`.
- Procesamiento parcial: una fila inválida o una baja no permitida no bloquea las filas válidas.
- Modo `dryRun`, sin persistencia.
- Autenticación Sanctum, `X-Paq-Cliente` y permiso `partes.integracion.maestros`.
- Envelope GEN-20 y claves de error `integracion.maestro.*`.
- SP por lote:
  - `pq_sp_partes_integracion_maestros_clientes`
  - `pq_sp_partes_integracion_maestros_asistentes`
  - `pq_sp_partes_integracion_maestros_tipos_cliente`
  - `pq_sp_partes_integracion_maestros_tipos_tarea`
  - `pq_sp_partes_integracion_maestros_asignaciones`
- Parámetros:
  - `PARTES_INTEGRACION_MAESTROS_TIMEOUT_SEGUNDOS`, default `120`.
  - `PARTES_INTEGRACION_MAESTROS_MAX_JSON_MB`, default `10`.
- Máximo de 1500 registros por request.
- OpenAPI con ejemplos completos de request y response para los cinco recursos.

---

## Fuera de alcance

- UI, menú o proceso web.
- Mobile.
- Importación Excel o CSV.
- Eliminación física.
- Alta o modificación de `users`, credenciales Sanctum o logins.
- Habilitación de acceso de clientes mediante `user_id`.
- Webhooks, jobs programados o sincronización bidireccional.
- Versionado de catálogos.
- Aprobación humana previa.

---

## Reglas de negocio

| ID | Regla |
|----|-------|
| R-IM-01 | Cada request corresponde a un único recurso y representa su universo completo vigente. |
| R-IM-02 | Registro ausente del universo recibido se intenta inhabilitar lógicamente, incluso si fue creado manualmente. |
| R-IM-03 | La baja lógica usa `Inhabilitado = true` o `Activo = false`, según el modelo. |
| R-IM-04 | Una baja no permitida se rechaza individualmente y no impide procesar el resto. |
| R-IM-05 | Request vacío se rechaza y no provoca bajas masivas. |
| R-IM-06 | En asignaciones, cliente ausente queda sin asignaciones y `tiposTarea: []` elimina sus asignaciones. |
| R-IM-07 | Registro idéntico se informa como `omitido` con acción `sinCambios`. |
| R-IM-08 | `dryRun` calcula el resultado previsto, pero no persiste. |
| R-IM-09 | El permiso requerido es `partes.integracion.maestros`. |
| R-IM-10 | Los SP reciben `@itemsJson`, `@fechaProceso`, `@origen` y `@dryRun` para procesar el lote completo. |
| R-IM-11 | El timeout default es 120 segundos, el JSON default 10 MB y el lote máximo 1500 registros; los dos primeros son configurables. |
| R-IM-12 | Los códigos son case sensitive y solo se recortan espacios al inicio y al final. |
| R-IM-13 | En altas, opcionales omitidos toman default o `null`; en actualizaciones, omitidos conservan valor. `null` y `""` borran cuando el campo lo permite. |
| R-IM-14 | Fechas usan formato ISO único y booleanos solo aceptan `true` o `false` JSON. |
| R-IM-15 | Éxito total o parcial responde HTTP 200 con `error: 0`; errores globales usan `error != 0`. |
| R-IM-16 | OpenAPI debe mostrar valores concretos y completos de ingreso y devolución para cada recurso. |
| R-IM-17 | `dryRun` es opcional y por defecto `false`. |
| R-IM-18 | `tiposTarea` es obligatorio y no admite `null`; un arreglo vacío es válido. |
| R-IM-19 | `nombre` y `tipoCliente` son obligatorios en clientes; `nombre` y `usuario` en asistentes; `descripcion` en tipos de cliente y tipos de tarea. |
| R-IM-20 | En tipos de tarea, `isGenerico` default `true`, `isDefault` default `false`; el último default del archivo prevalece e impone `isGenerico=true`. |

---

## Criterios de aceptación

- [ ] **CA-01** Cada uno de los cinco recursos dispone de su endpoint propio y no existe un sobre multi-recurso.
- [ ] **CA-02** Un registro nuevo se crea usando su clave natural y un registro existente se actualiza con los campos permitidos.
- [ ] **CA-03** Un registro existente que no figura se intenta inhabilitar lógicamente, sin eliminarlo físicamente.
- [ ] **CA-04** Las asignaciones se reconcilian con el universo completo de clientes y sus `tiposTarea`; se agregan, conservan o inhabilitan según corresponda.
- [ ] **CA-05** Un request vacío se rechaza completamente sin modificar datos.
- [ ] **CA-06** Una baja no permitida se informa como rechazo y no impide aplicar los demás cambios válidos.
- [ ] **CA-07** Una fila inválida no impide procesar las filas válidas del mismo lote.
- [ ] **CA-08** `dryRun` devuelve los mismos contadores y resultados previstos que una ejecución real, sin persistir cambios.
- [ ] **CA-09** Sin Bearer se responde `401`; sin `partes.integracion.maestros`, `403`.
- [ ] **CA-10** El acceso de negocio se ejecuta mediante el SP correspondiente, recibiendo el lote completo.
- [ ] **CA-11** Se rechaza el request si supera 1500 registros, el tamaño configurado o el timeout configurado.
- [ ] **CA-12** Se aplican trim de extremos, sensibilidad de mayúsculas/minúsculas, semántica de campos omitidos/`null`/`""` y booleanos JSON.
- [ ] **CA-13** La respuesta usa GEN-20, contadores `recibidos`, `aplicados`, `omitidos`, `rechazados` y claves `integracion.maestro.*`.
- [ ] **CA-14** OpenAPI contiene ejemplos completos de request y response para clientes, asistentes, tipos de cliente, tipos de tarea y asignaciones, incluyendo `dryRun`, bajas, sin cambios y errores parciales.

---

## Escenarios Gherkin

```gherkin
Feature: Sincronización de maestros mediante API

  Scenario: Crear y actualizar clientes
    Given un llamador autenticado con permiso "partes.integracion.maestros"
    When envía el universo vigente de clientes
    Then se crean los clientes nuevos
    And se actualizan los clientes existentes con cambios
    And los clientes sin cambios se informan como omitidos

  Scenario: Inhabilitar registros ausentes
    Given existen clientes en la base que no figuran en el JSON
    When se procesa un universo completo de clientes
    Then se intenta inhabilitar lógicamente cada cliente ausente
    And no se elimina físicamente ningún cliente

  Scenario: Reconciliar asignaciones
    Given CLI-001 tiene TAR-ADMIN asignado y el JSON no lo incluye
    When se procesa la lista de tipos de tarea de CLI-001
    Then la asignación TAR-ADMIN queda inhabilitada

  Scenario: Baja no permitida
    Given un registro ausente no puede inhabilitarse por una regla de negocio
    When se procesa el universo
    Then esa baja se informa como rechazada
    And se procesan los demás registros válidos

  Scenario: Dry run
    Given un JSON válido con altas, cambios y bajas
    When se procesa con "dryRun" igual a true
    Then se informa el resultado previsto
    And no se persiste ningún cambio

  Scenario: Request vacío
    Given un request con "items" vacío
    When se invoca cualquier endpoint de maestros
    Then se rechaza el request
    And no se ejecuta ninguna baja masiva
```

---

## Supuestos explícitos

- El origen envía el universo completo vigente de cada recurso.
- Las referencias necesarias se cargan previamente en el orden requerido.
- Las reglas del ABM de SPEC-003 siguen siendo la fuente de validación de negocio.
- La firma lógica de los SP se precisará físicamente en TR/SQL sin cambiar el comportamiento de esta HU.
- Los ejemplos OpenAPI se completarán con los schemas definitivos durante HU/TR.

## Preguntas abiertas

| Pregunta | Estado |
|----------|--------|
| Slugs de recursos | Cerrado en A1 |
| Baja de asignaciones | Cerrado: reconciliación por universo |
| `null`, `""` y omitidos | Cerrado en A1 |
| Permiso | Cerrado: `partes.integracion.maestros` |
| Nombres y firma lógica de SP | Cerrado en A1; firma física queda para TR |
| Timeout y tamaño JSON | Cerrado: parámetros configurables, defaults 120 s / 10 MB |
| Normalización | Cerrado en A1 |
| Catálogo i18n | Cerrado como prefijo y catálogo inicial; detalle de cinco locales en TR |

## Riesgos de ambigüedad residuales

- Definir en TR los tipos físicos, longitudes y obligatoriedad final de cada propiedad del JSON.
- Mantener sincronizados los ejemplos OpenAPI con los schemas y respuestas implementadas.
- Definir la forma física de retorno de resultados por fila desde cada SP.

## Dependencias

- SPEC-001: modelo de datos.
- SPEC-002: identidad, autenticación y permisos.
- SPEC-003: maestros, catálogos y reglas de validación.
- GEN-20: envelope API.
- Regla 74: acceso de negocio mediante stored procedures.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-23 | Parte B: HU derivada de SPEC-014 tras A1 repetido. |
