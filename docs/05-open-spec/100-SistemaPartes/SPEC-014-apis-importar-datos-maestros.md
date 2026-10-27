# SPEC-014 – APIs para importar datos maestros

Jira: [PIAPARTES-46](https://paqsystems.atlassian.net/browse/PIAPARTES-46)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-014 |
| Título | APIs para importar datos maestros |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-10-21 |
| **A1** | [`SPEC-014-apis-importar-datos-maestros-A1-ambiguity-review.md`](./SPEC-014-apis-importar-datos-maestros-A1-ambiguity-review.md) — **Apto con observaciones** (2026-10-23) |
| HU relacionada(s) | Pendiente — se derivará en Parte B |
| TR relacionada(s) | Pendiente — se derivará en Parte C |
| Fuente | [`17-APIs-importar-datos-maestros.md`](../../02-producto/Sistema-Partes-IA/17-APIs-importar-datos-maestros.md) |
| Dependencias | SPEC-001 (modelo de datos), SPEC-002 (identidad y permisos), SPEC-003 (maestros y catálogos), GEN-20 (envelope), regla 74 (SP) |

**Adopción Framework:** proceso de dominio Partes. No aplica GEN-14 `ExcelImportToolbar`: el canal es REST y el Excel de GEN-14 corresponde a la importación de tareas. La respuesta usa GEN-20 y la persistencia de negocio se ejecuta mediante stored procedures según la regla 74. No hay UI de menú en v1 ni rama mobile. OpenAPI debe documentar cada endpoint con ejemplos completos de request y response, incluyendo todos los valores de `items`.

---

## 1. Resumen ejecutivo

Un sistema externo —ERP, CRM u otro origen— debe poder crear y actualizar masivamente los maestros utilizados por Partes de Atención mediante APIs REST autenticadas.

El resultado esperado es un canal de integración idempotente, con las mismas reglas de negocio que el ABM de Archivos, commit parcial por fila e informe detallado de aceptados y rechazados, sin reemplazar la operatoria humana ni crear identidades autenticables.

---

## 2. Alcance

### 2.1 En alcance

- APIs REST autenticadas para upsert de:
  - tipos de cliente;
  - tipos de tarea;
  - clientes;
  - asignaciones cliente–tipo de tarea;
  - asistentes.
- Upsert por clave natural `code`; en asignaciones, por el par `cliente.code` + `tipoTarea.code`.
- Uso de las mismas reglas de dominio, unicidades, estados y referencias definidas para el ABM de maestros.
- Un endpoint por recurso bajo `POST /api/v1/integracion/maestros/{recurso}`; no se utiliza un sobre multi-recurso en v1.
- La responsabilidad del orden de carga queda en el sistema origen. Si falta una FK, la fila se rechaza; el API no reordena ni espera dependencias de otra sección.
- Validación completa del lote, grabación de filas válidas y rechazo individual de filas inválidas.
- Idempotencia: reenvío idéntico sin cambio efectivo; datos distintos actualizan el registro.
- Informe de conteo de aceptados/rechazados y detalle por fila mediante índice o `code` y clave i18n de error.
- Envelope GEN-20, con `error: 0` en respuestas exitosas.
- Permiso de integración dedicado, distinto del permiso del ABM.
- Headers de plataforma `X-Paq-Cliente` y autenticación Sanctum.
- Persistencia y comandos de negocio únicamente mediante stored procedures.
- Inhabilitación lógica mediante estado usable/inhabilitado; no eliminación física por este canal.
- Para asistentes, validación obligatoria del código `usuario` de Framework ya existente y de su disponibilidad para vinculación.
- Aplicación de `erpCliente` y `erpArticulo` opcionales en clientes, con máximo de 15 caracteres cada uno.
- Contrato REST con `fechaProceso`, `origen` opcional, `dryRun` e `items[]`; cada request representa el universo completo vigente del recurso y se reconcilia contra la base.
- Registro de cada request en la bitácora de integración, con contadores y muestra acotada de errores.
- Límite configurable de 120 segundos para el procesamiento y 10 MB para el JSON, mediante `PQ_PARAMETROS_GRAL`.

### 2.2 Fuera de alcance

- Pantalla de menú o proceso UI en v1; el operador continúa usando Archivos.
- Importación Excel o CSV de maestros y reutilización de GEN-14.
- Alta o modificación de usuarios `users`, credenciales Sanctum o logins.
- Habilitación del acceso de un cliente mediante `user_id`.
- Importación de partes, tareas, paquetes de horas o layouts.
- Sincronización bidireccional, webhooks o jobs programados hacia el sistema externo.
- Eliminación física de maestros referenciados.
- Mobile.
- Versionado de catálogos, aprobación humana o reemplazo completo del ABM. `dryRun` forma parte de v1 y no persiste cambios.

---

## 3. Actores y contexto

| Actor | Capacidad |
|-------|-----------|
| Sistema externo / usuario técnico con permiso de integración | Invocar los endpoints y recibir el resultado fila por fila |
| Supervisor u operador de Archivos sin permiso de integración | No puede usar el canal de integración; conserva el ABM |
| Cliente funcional | No puede invocar las APIs |
| Anónimo | No puede invocar las APIs |

### 3.1 Precondiciones

- Sesión autenticada mediante Sanctum y headers de plataforma válidos.
- El llamador posee el permiso de integración dedicado.
- Las entidades de referencia necesarias ya existen en Partes; el origen debe respetar el orden de carga.
- Para asistentes, el `usuario` Framework ya existe y no está vinculado a otro asistente ni a un cliente.
- Las reglas y estructuras de maestros de SPEC-003 siguen siendo la fuente de verdad del dominio.

---

## 4. Comportamiento funcional

### 4.1 Recursos y claves

| Recurso | Tabla | Clave de integración | Datos permitidos conceptualmente |
|---------|-------|-----------------------|-----------------------------------|
| Tipos de cliente | `PQ_PARTES_TIPOS_CLIENTE` | `code` | `descripcion`, estado usable |
| Tipos de tarea | `PQ_PARTES_TIPOS_TAREA` | `code` | `descripcion`, `isGenerico`, `isDefault`, estado usable |
| Clientes | `PQ_PARTES_CLIENTES` | `code` | `nombre`, tipo de cliente por `code`, `email`, `erpCliente`, `erpArticulo`, estado usable |
| Asignaciones cliente–tipo | `PQ_PARTES_CLIENTE_TIPO_TAREA` | `cliente.code` + `tipoTarea.code` | sincronización completa de clientes y sus tipos de tarea |
| Asistentes | `PQ_PARTES_USUARIOS` | `code` | `nombre`, `email`, `supervisor`, estado usable, `usuario` Framework |

El `id` interno nunca es clave del contrato de integración.

### 4.2 Ruta y contrato común

- Cada recurso tiene un endpoint propio:
  `POST /api/v1/integracion/maestros/{recurso}`.
- El request es JSON y contiene `fechaProceso`, `origen` opcional, `dryRun` e `items[]`.
- Cada request representa el universo completo vigente del recurso. Lo que no figura se intenta inhabilitar lógicamente.
- Un request vacío se rechaza y no provoca bajas masivas.
- El origen debe cargar primero las referencias requeridas. Una FK inexistente produce rechazo de esa fila.
- `Idempotency-Key` queda fuera de v1; reenviar el mismo universo produce un no-op efectivo.
- Los SP reciben el lote completo mediante `@itemsJson`, `@fechaProceso`, `@origen` y `@dryRun bit = 0`.

#### 4.2.1 Configuración y límites

Los valores se leen de `PQ_PARAMETROS_GRAL`:

```text
PARTES_INTEGRACION_MAESTROS_TIMEOUT_SEGUNDOS = 120
PARTES_INTEGRACION_MAESTROS_MAX_JSON_MB = 10
```

El límite máximo es de 1500 registros por request. Si se supera el límite de registros, el tamaño JSON o el timeout configurado, se rechaza el request completo antes de aplicar cambios.

### 4.3 Lote y atomicidad

1. Se valida todo el lote recibido antes de aplicar sus resultados.
2. Una fila inválida o una baja no permitida no aborta las filas válidas.
3. La unidad de transacción es la fila; cada alta, modificación o baja permitida puede aplicarse de forma independiente.
4. Los duplicados dentro de `items[]` se rechazan sin elegir silenciosamente un ganador.
5. Si el lote supera el máximo de 1500 ítems, el tamaño JSON o el timeout configurado, se rechaza el request completo sin recorte silencioso.
6. En `dryRun` se calculan los mismos resultados, pero no se persiste ningún cambio.

### 4.4 Upsert e idempotencia

- Si no existe la clave natural, se crea el maestro.
- Si existe, se actualizan los campos permitidos por el contrato.
- Reenviar los mismos datos produce un no-op efectivo y se informa como `omitido` con acción `sinCambios`.
- Las propiedades omitidas conservan los valores persistidos.
- `null` y cadena vacía borran el valor cuando el campo lo permite.
- La comparación de códigos es case sensitive; solo se quitan espacios al inicio y al final. No se modifican espacios internos.
- Las fechas usan formato ISO único; no se aceptan formatos locales. Los booleanos aceptan únicamente `true` y `false` JSON.
- `dryRun` es opcional y por defecto `false`.
- Todo registro existente que no figure en el universo recibido se intenta inhabilitar lógicamente, incluso si fue creado manualmente.
- Una baja no permitida se rechaza individualmente y no impide procesar el resto.
- Una asignación se agrega si figura, se conserva si ya existe y se elimina lógicamente si no figura.
- En asignaciones, un cliente con `tiposTarea: []` queda sin asignaciones; un cliente ausente queda sin asignaciones.
- El canal no elimina físicamente maestros ni relaciones.

### 4.5 Reglas de dominio

- `code` es único en cada maestro.
- Un cliente solo referencia un tipo de cliente existente y usable, salvo que la dependencia llegue antes en el mismo sobre.
- `erpCliente` y `erpArticulo` son opcionales y no superan 15 caracteres.
- Un tipo genérico no puede asignarse a un cliente.
- Puede existir como máximo un tipo de tarea usable marcado como default.
- Marcar un tipo como default exige que sea genérico y desmarca el default anterior según la regla del ABM.
- Si llegan varios tipos con `isDefault=true`, el último registro del archivo es el único default.
- `isDefault=true` impone `isGenerico=true`, aunque el JSON envíe `isGenerico=false`.
- `isGenerico` es opcional y por defecto `true`; `isDefault` es opcional y por defecto `false`.
- No se puede inhabilitar el tipo default vigente sin que resulte otro default válido.
- Inhabilitar no borra historial ni elimina físicamente el registro.
- Un asistente debe incluir `usuario`, que referencia un usuario Framework ya existente.
- Si `usuario` no existe o ya está vinculado a otro asistente o cliente, la fila de asistente se rechaza.
- Si un asistente existente conserva el mismo `usuario`, puede actualizarse el resto de sus campos.
- Cambiar el `usuario` vinculado a un asistente existente se rechaza en v1; el cambio queda en el ABM.
- Este canal no habilita acceso de cliente ni crea usuarios `users`.

#### 4.5.1 Stored procedures

Cada endpoint invoca el SP correspondiente con el lote completo:

```text
pq_sp_partes_integracion_maestros_clientes
pq_sp_partes_integracion_maestros_asistentes
pq_sp_partes_integracion_maestros_tipos_cliente
pq_sp_partes_integracion_maestros_tipos_tarea
pq_sp_partes_integracion_maestros_asignaciones
```

Firma lógica común:

```text
@itemsJson nvarchar(max),
@fechaProceso date,
@origen nvarchar(100) = NULL,
@dryRun bit = 0
```

Cada SP devuelve un único resultset con una fila `RESUMEN` y cero o más filas `ITEM`, distinguidas por `tipoRegistro`, para que el adapter arme el envelope GEN-20.

### 4.6 Respuesta y errores

- Éxito HTTP con envelope GEN-20 y `error: 0`.
- Un lote procesado con éxito parcial también responde HTTP `200` con `error: 0`.
- La respuesta informa `recurso`, `dryRun`, `recibidos`, `aplicados`, `omitidos`, `rechazados` y `errores[]`.
- Cada rechazo identifica `indice`, `clave` y `codigo` i18n.
- Cada resultado aplicado u omitido puede informar `clave` y `accion`, por ejemplo `alta`, `modificacion`, `inhabilitado` o `sinCambios`.
- `errores[]` se limita a 50 entradas; el truncamiento se comunica mediante una clave i18n en `respuesta`.
- El JSON no contiene prosa de negocio ni mensajes literales dependientes de idioma.
- Sin Bearer válido: HTTP `401`. Sin permiso: HTTP `403`. Recurso desconocido: HTTP `404`.
- Error de JSON, contrato, tamaño excedido o request abortado: HTTP `4xx/5xx` con `error != 0`.
- El prefijo de claves de dominio será `integracion.maestro.*`.
- El permiso dedicado es `partes.integracion.maestros`.

Ejemplo de resultado:

```json
{
  "recurso": "clientes",
  "dryRun": false,
  "recibidos": 10,
  "aplicados": 9,
  "omitidos": 0,
  "rechazados": 1,
  "errores": [
    {
      "indice": 3,
      "clave": "CL-404",
      "codigo": "integracion.maestro.itemInvalido"
    }
  ]
}
```

### 4.7 Ejemplos obligatorios para OpenAPI

OpenAPI debe publicar ejemplos completos, no solo ejemplos con `{}` o valores genéricos. Como mínimo, debe incluir request realista y response de éxito parcial para cada recurso:

```json
{
  "fechaProceso": "2026-10-23",
  "origen": "ERP",
  "dryRun": false,
  "items": [
    {
      "code": "CLI-001",
      "nombre": "Acme SA",
      "tipoCliente": { "code": "EMPRESA" },
      "email": "contacto@acme.example",
      "erpCliente": "1001",
      "erpArticulo": "A-100"
    }
  ]
}
```

```text
POST /api/v1/integracion/maestros/clientes
```

```json
{
  "fechaProceso": "2026-10-23",
  "origen": "ERP",
  "dryRun": true,
  "items": [
    {
      "code": "AST-001",
      "nombre": "Ana Pérez",
      "email": "ana.perez@example",
      "supervisor": true,
      "usuario": { "code": "USR-001" }
    }
  ]
}
```

```text
POST /api/v1/integracion/maestros/asistentes
```

```json
{
  "fechaProceso": "2026-10-23",
  "origen": "ERP",
  "dryRun": false,
  "items": [
    {
      "code": "TC-001",
      "descripcion": "Empresa"
    }
  ]
}
```

```text
POST /api/v1/integracion/maestros/tipos-cliente
```

```json
{
  "fechaProceso": "2026-10-23",
  "origen": "ERP",
  "dryRun": false,
  "items": [
    {
      "code": "TAR-ADMIN",
      "descripcion": "Administración",
      "isGenerico": true,
      "isDefault": false
    }
  ]
}
```

```text
POST /api/v1/integracion/maestros/tipos-tarea
```

Para asignaciones, el JSON representa el universo completo. Un arreglo vacío elimina lógicamente todas las asignaciones del cliente; un cliente ausente elimina lógicamente todas sus asignaciones:

```json
{
  "fechaProceso": "2026-10-23",
  "origen": "ERP",
  "dryRun": false,
  "items": [
    {
      "code": "CLI-001",
      "tiposTarea": [
        { "code": "TAR-ADMIN" },
        { "code": "TAR-CAPAC" }
      ]
    },
    {
      "code": "CLI-002",
      "tiposTarea": [
        { "code": "TAR-CAPAC" }
      ]
    }
  ]
}
```

```text
POST /api/v1/integracion/maestros/asignaciones-cliente-tipo
```

La respuesta debe mostrar explícitamente acciones y errores:

```json
{
  "error": 0,
  "respuesta": "integracion.maestro.procesado",
  "resultado": {
    "recurso": "clientes",
    "recibidos": 3,
    "aplicados": 2,
    "omitidos": 1,
    "rechazados": 1,
    "resultados": [
      { "indice": 0, "clave": "CLI-001", "accion": "modificacion" },
      { "indice": 1, "clave": "CLI-002", "accion": "inhabilitado" },
      { "indice": 2, "clave": "CLI-003", "accion": "sinCambios" }
    ],
    "errores": [
      {
        "indice": 3,
        "clave": "CLI-004",
        "codigo": "integracion.maestro.bajaNoPermitida"
      }
    ]
  }
}
```

---

## 5. Criterios verificables

- [ ] Un llamador autenticado sin el permiso de integración no puede ejecutar el upsert.
- [ ] Los cinco recursos se pueden upsertar usando sus claves naturales, sin exponer el `id` interno como clave requerida.
- [ ] Cada recurso tiene un endpoint propio bajo `/api/v1/integracion/maestros/{recurso}`.
- [ ] El API no reordena recursos; una FK inexistente rechaza la fila.
- [ ] Una fila inválida o una baja no permitida no impide procesar las filas válidas del mismo lote.
- [ ] La respuesta usa los contadores y el detalle por fila definidos en §4.6.
- [ ] Reenviar un registro idéntico no genera cambios efectivos y se informa como omitido.
- [ ] Los duplicados dentro del lote se rechazan sin ganador implícito.
- [ ] Un lote que supera el máximo de 1500 ítems se rechaza completo y no se recorta.
- [ ] Un lote vacío se rechaza y no provoca bajas masivas.
- [ ] `dryRun` devuelve el resultado previsto sin persistir cambios.
- [ ] Se aplican las reglas de dominio del ABM: referencias válidas, default, genéricos, estados y límites ERP.
- [ ] Un asistente exige un `usuario` Framework existente, disponible y estable; no crea `users`.
- [ ] El cambio de vínculo `usuario` de un asistente existente se rechaza en v1.
- [ ] El canal no habilita acceso de cliente ni elimina físicamente maestros.
- [ ] Las respuestas exitosas usan GEN-20 con `error: 0`.
- [ ] OpenAPI contiene ejemplos completos de request y response para los cinco recursos, incluyendo altas, modificaciones, bajas, sin cambios, errores parciales y `dryRun`.
- [ ] El acceso de negocio se realiza mediante stored procedures, sin SQL ad-hoc del controller o servicio.
- [ ] No se agregan ruta de menú, pantalla web ni ruta mobile para esta capacidad v1.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | Controllers/servicios de integración, middleware o gate de permiso, validación por recurso, envelope GEN-20 y manejo de resultado parcial |
| Repositorio | Adaptador de stored procedures para listados/referencias y upsert de cada recurso; contrato portable a gateway |
| SQL | Scripts `pq_sp_partes_*` para validación, reconciliación y resultado por fila; nombres y firma lógica definidos en §4.5.1 |
| Seguridad | Permiso dedicado de integración, separado de Archivos; Sanctum + `X-Paq-Cliente` |
| OpenAPI | Documentar un endpoint por recurso, payloads completos, límite de 1500 ítems, límites configurables, respuestas parciales, `dryRun` y claves de error |
| Tests | Feature API para autorización, referencias faltantes, commit parcial por fila, idempotencia, asistentes y reglas de dominio; unitarios de normalización si corresponde |
| Frontend | Sin cambios funcionales en v1; solo documentación/OpenAPI si el contrato se expone en Swagger |
| Mobile | Sin ruta ni policy mobile |

### 6.1 Decisiones cerradas específicas de Partes

- Slugs: `clientes`, `asistentes`, `tipos-cliente`, `tipos-tarea`, `asignaciones-cliente-tipo`.
- Las asignaciones se sincronizan como universo completo; no se informan acciones de alta o baja.
- Campos omitidos conservan valor; `null` y cadena vacía borran cuando corresponde.
- Permiso: `partes.integracion.maestros`.
- SP y firma lógica: §4.5.1.
- Timeout y tamaño: parámetros `PARTES_INTEGRACION_MAESTROS_TIMEOUT_SEGUNDOS` y `PARTES_INTEGRACION_MAESTROS_MAX_JSON_MB`, con valores por defecto 120 y 10.
- Catálogo inicial de errores: claves `integracion.maestro.*` definido en la documentación de integración y a completar en OpenAPI/i18n.
- `dryRun` forma parte de v1 y no persiste cambios.

---

## 7. Riesgos y supuestos

| Riesgo / supuesto | Tratamiento |
|-------------------|-------------|
| Divergencia entre ABM e integración | Reutilizar las mismas reglas de SPEC-003 y centralizar validaciones de dominio |
| Orden y referencias cruzadas | Un recurso por request; el origen carga referencias antes y el API rechaza FK faltantes |
| Lotes grandes | Tope de 1500 ítems; el origen parte los lotes |
| Vinculación de asistentes | Validar el usuario Framework existente y prohibir cambios de vínculo en v1 |
| Compatibilidad con gateway | Toda persistencia de negocio queda detrás de SP conocidos |
| Exposición accidental de seguridad | Permiso dedicado; no crear usuarios ni accesos de cliente |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-21 | Versión inicial — Parte A desde `17-APIs-importar-datos-maestros.md`. |
| 2026-10-21 | Parte A1 — **No apto**: contrato REST, atomicidad, límites, permiso y errores requieren decisión antes de HU. |
| 2026-10-23 | Revisión con SPEC-101-22/23 de PedidosWeb: se adopta el contrato común de endpoint por recurso, resultado parcial HTTP 200, tope 1500, errores 50 e idempotencia sin `Idempotency-Key`; el modo `delta` de la referencia no aplica a la reconciliación completa de Partes. |

---

**Trazabilidad:** A1 **Apto con observaciones**. El contrato transversal queda alineado a PedidosWeb; quedan decisiones específicas de Partes para resolver antes de pasar a **B**.
