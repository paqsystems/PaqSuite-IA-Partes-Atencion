# TR-014 – APIs para importar datos maestros

Jira: [PIAPARTES-51](https://paqsystems.atlassian.net/browse/PIAPARTES-51)

| Campo | Valor |
|-------|--------|
| HU relacionada | [HU-014-apis-importar-datos-maestros](../../03-historias-usuario/100-SistemaPartes/HU-014-apis-importar-datos-maestros.md) |
| SPEC relacionada | [SPEC-014-apis-importar-datos-maestros](../../05-open-spec/100-SistemaPartes/SPEC-014-apis-importar-datos-maestros.md) |
| Épica | 100 — Sistema Partes |
| Prioridad | MUST-HAVE |
| Clasificación | HU COMPLEJA |
| Estado | Pendiente de Revisión |
| Última actualización | 2026-10-23 |

**Origen:** HU-014 derivada de SPEC-014 tras A1 repetido.  
**Alcance:** backend REST, validación, reconciliación completa, stored procedures por lote, seguridad, OpenAPI y tests. Sin UI ni mobile.

---

## 1) Objetivo técnico

Implementar cinco endpoints REST autenticados que reciban el universo completo de un recurso maestro y lo reconcilien con la base de Partes:

```text
POST /api/v1/integracion/maestros/clientes
POST /api/v1/integracion/maestros/asistentes
POST /api/v1/integracion/maestros/tipos-cliente
POST /api/v1/integracion/maestros/tipos-tarea
POST /api/v1/integracion/maestros/asignaciones-cliente-tipo
```

Cada request procesa un único recurso, aplica altas y modificaciones, informa sin cambios, intenta bajas lógicas de registros ausentes y permite `dryRun`.

---

## 2) Contrato API

### 2.1 Headers y autorización

- Bearer Sanctum obligatorio.
- `X-Paq-Cliente` obligatorio según el contrato de plataforma.
- Permiso requerido: `partes.integracion.maestros`.
- Sin Bearer: HTTP `401`.
- Sin permiso: HTTP `403`.
- Recurso desconocido: HTTP `404`.

### 2.2 Request común

```json
{
  "fechaProceso": "2026-10-23",
  "origen": "ERP",
  "dryRun": false,
  "items": []
}
```

Reglas:

- `fechaProceso`: formato ISO `YYYY-MM-DD`.
- `origen`: opcional, máximo 100 caracteres.
- `dryRun`: booleano JSON opcional; si se omite, vale `false`.
- `items`: arreglo del recurso; no se admite sobre multi-recurso.
- Request vacío: rechazo completo, sin bajas masivas.
- Máximo: 1500 registros por request.
- Máximo JSON default: 10 MB.
- Timeout default: 120 segundos.

### 2.3 Schemas por recurso

#### Clientes

```json
{
  "code": "CLI-001",
  "nombre": "Acme SA",
  "tipoCliente": { "code": "EMPRESA" },
  "email": null,
  "erpCliente": null,
  "erpArticulo": null
}
```

Obligatorios: `code`, `nombre`, `tipoCliente`.  
Opcionales: `email`, `erpCliente`, `erpArticulo`; si se omiten en un alta, se persisten como `null`. En una actualización, si se omiten, conservan el valor actual.

#### Asistentes

```json
{
  "code": "AST-001",
  "nombre": "Ana Pérez",
  "usuario": { "code": "USR-001" },
  "email": null,
  "supervisor": false
}
```

Obligatorios: `code`, `nombre`, `usuario`.  
Opcionales: `email`, `supervisor`; si `supervisor` se omite en un alta, vale `false`; en una actualización se conserva el valor actual.

#### Tipos de cliente

```json
{
  "code": "TC-001",
  "descripcion": "Empresa"
}
```

Obligatorios: `code`, `descripcion`.

#### Tipos de tarea

```json
{
  "code": "TAR-ADMIN",
  "descripcion": "Administración",
  "isGenerico": true,
  "isDefault": false
}
```

Obligatorios: `code`, `descripcion`.  
`isGenerico` es opcional y por defecto `true` en altas; en actualizaciones, si se omite, conserva el valor actual.  
`isDefault` es opcional y por defecto `false` en altas; en actualizaciones, si se omite, conserva el valor actual.

Si varios tipos llegan con `isDefault=true`, el último registro del archivo es el único default: deja sin default a los anteriores. El registro default debe ser genérico. `isDefault=true` impone `isGenerico=true`, aunque el JSON envíe `isGenerico=false`.

#### Asignaciones

`tiposTarea` es obligatorio y nunca puede ser `null`; puede ser un arreglo vacío:

```json
{
  "code": "CLI-001",
  "tiposTarea": []
}
```

### 2.4 Parámetros configurables

Leer desde `PQ_PARAMETROS_GRAL` mediante el mecanismo de parámetros existente:

```text
PARTES_INTEGRACION_MAESTROS_TIMEOUT_SEGUNDOS = 120
PARTES_INTEGRACION_MAESTROS_MAX_JSON_MB = 10
```

Si el request supera registros, bytes o timeout, rechazarlo antes de aplicar cambios.

### 2.5 Reglas de entrada

- Códigos case sensitive.
- Trim únicamente al inicio y al final.
- No modificar espacios internos.
- En altas, un atributo opcional omitido toma su valor default o `null` según el schema.
- En actualizaciones, un atributo omitido conserva su valor actual.
- `null` y `""` borran el valor cuando el campo lo permite.
- Fechas solo en formato ISO único; rechazar formatos locales.
- Booleanos solo `true` y `false` JSON.
- Duplicados dentro del lote: rechazo de las filas duplicadas sin ganador implícito.

### 2.6 Respuesta

Éxito total o parcial:

- HTTP `200`.
- Envelope GEN-20 con `error: 0`.
- Resultado con `recurso`, `dryRun`, `recibidos`, `aplicados`, `omitidos`, `rechazados`, resultados por acción y `errores[]`.

Acciones mínimas:

```text
alta
modificacion
inhabilitado
sinCambios
```

Cada error contiene:

```json
{
  "indice": 0,
  "clave": "CLI-001",
  "codigo": "integracion.maestro.itemInvalido"
}
```

`errores[]` se limita a 50 entradas; el truncamiento debe informarse con una clave i18n.

Errores globales de JSON, contrato, tamaño o timeout usan `error != 0` y HTTP `4xx/5xx`.

---

## 3) Reconciliación por recurso

### 3.1 Maestros individuales

Aplicar a clientes, asistentes, tipos de cliente y tipos de tarea:

1. Validar el lote completo.
2. Crear claves naturales inexistentes.
3. Actualizar campos permitidos de claves existentes.
4. Informar registros sin cambios como `omitido` / `sinCambios`.
5. Intentar inhabilitar lógicamente registros existentes ausentes del JSON, incluso si fueron creados manualmente.
6. Si una baja no está permitida, rechazar solo esa baja y continuar con el resto.
7. Nunca eliminar físicamente.

Baja lógica:

- `Inhabilitado = true`, si el modelo utiliza ese atributo.
- `Activo = false`, si el modelo utiliza `Activo`.

### 3.2 Asignaciones

El request representa el conjunto completo de clientes y tipos de tarea vigentes:

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
    }
  ]
}
```

- Cliente y tipo presentes sin relación → alta de relación.
- Relación existente y presente → sin cambios.
- Tipo actualmente asignado pero ausente del cliente → baja lógica de relación.
- Cliente ausente del JSON → baja lógica de todas sus relaciones.
- `tiposTarea: []` → baja lógica de todas las relaciones del cliente.
- No se informa una acción de alta o baja en el request.
- No se eliminan físicamente relaciones.

---

## 4) Stored procedures

### 4.1 Nombres

```text
pq_sp_partes_integracion_maestros_clientes
pq_sp_partes_integracion_maestros_asistentes
pq_sp_partes_integracion_maestros_tipos_cliente
pq_sp_partes_integracion_maestros_tipos_tarea
pq_sp_partes_integracion_maestros_asignaciones
```

### 4.2 Firma lógica común

```text
@itemsJson nvarchar(max),
@fechaProceso date,
@origen nvarchar(100) = NULL,
@dryRun bit = 0
```

### 4.3 Resultado único del SP

El SP devuelve un único resultset con una fila de resumen y cero o más filas de detalle. La columna `tipoRegistro` distingue ambas:

| Columna | Tipo lógico | Resumen | Detalle |
|---------|-------------|---------|---------|
| `tipoRegistro` | string | `RESUMEN` | `ITEM` |
| `recibidos` | integer | Sí | NULL |
| `aplicados` | integer | Sí | NULL |
| `omitidos` | integer | Sí | NULL |
| `rechazados` | integer | Sí | NULL |
| `erroresTruncados` | boolean | Sí | NULL |
| `indice` | integer | NULL | Sí |
| `clave` | string | NULL | Sí |
| `accion` | string | NULL | Sí |
| `codigo` | string | NULL | Sí |

La fila `RESUMEN` es obligatoria incluso si no hay filas `ITEM`. Las filas `ITEM` informan `alta`, `modificacion`, `inhabilitado`, `sinCambios` o el código de rechazo. El adapter transforma este único resultset al envelope GEN-20 sin ejecutar SQL ad-hoc.

### 4.4 Comportamiento de `dryRun`

`dryRun` debe impedir toda persistencia, pero ejecutar las mismas validaciones y cálculo de diferencias.

---

## 5) Tareas de implementación

| ID | Capa | Tarea | Criterio de cierre |
|----|------|-------|--------------------|
| T1 | SQL/DB | Crear los cinco scripts SP con reconciliación completa y resultado por fila | CA-02…CA-08, CA-10 |
| T2 | Backend | Crear contratos DTO/request, validación común y validadores por recurso | CA-05, CA-07, CA-11, CA-12 |
| T3 | Backend | Implementar repository/adapter SP y servicios de integración | CA-02…CA-08, CA-10 |
| T4 | Backend | Implementar middleware/gate `partes.integracion.maestros` y seed de permiso | CA-09 |
| T5 | Backend | Implementar cinco rutas/controller con envelope GEN-20 y errores globales | CA-01, CA-09, CA-13 |
| T6 | Backend | Implementar configuración desde `PQ_PARAMETROS_GRAL` para timeout/tamaño | CA-11 |
| T7 | OpenAPI | Documentar los schemas cerrados, parámetros, respuestas y ejemplos completos de los cinco endpoints | CA-14 |
| T8 | Tests | Tests feature de autorización, reconciliación, bajas, parcialidad, `dryRun` y límites | CA-02…CA-13 |
| T9 | Tests | Tests unitarios de normalización y construcción de resultados | CA-07, CA-12, CA-13 |
| T10 | Docs | Completar catálogo i18n y actualizar trazabilidad de SPEC/HU/TR | CA-13, CA-14 |

**Orden recomendado:** T1 → T2 → T3 → T4 → T5 → T6 → T7 → T8 → T9 → T10.

---

## 6) Criterios de aceptación técnicos

- [ ] **TR-CA-01** Existen las cinco rutas con slugs exactos y sin endpoint multi-recurso.
- [ ] **TR-CA-02** Cada ruta invoca únicamente el SP correspondiente.
- [ ] **TR-CA-03** Se crean y actualizan registros por clave natural.
- [ ] **TR-CA-04** Los registros ausentes se inhabilitan lógicamente si las reglas lo permiten.
- [ ] **TR-CA-05** Un request vacío se rechaza sin modificar datos.
- [ ] **TR-CA-06** Las asignaciones se sincronizan por diferencia del universo completo.
- [ ] **TR-CA-07** Una fila inválida o una baja no permitida no aborta las filas válidas.
- [ ] **TR-CA-08** `dryRun` no persiste y devuelve el mismo cálculo de resultados.
- [ ] **TR-CA-09** La autorización devuelve `401`/`403` según corresponda.
- [ ] **TR-CA-10** No se ejecuta SQL de negocio fuera de los SP.
- [ ] **TR-CA-11** Se aplican límite de 1500, timeout y tamaño JSON configurables.
- [ ] **TR-CA-12** Se aplican trim de extremos, case sensitive, ISO, booleanos JSON y semántica de omitido/`null`/`""`.
- [ ] **TR-CA-13** La respuesta es GEN-20 y usa claves i18n, sin prosa dependiente de idioma.
- [ ] **TR-CA-14** OpenAPI contiene ejemplos completos de request y response de cada recurso, incluyendo `dryRun`, bajas, sin cambios y error parcial.

---

## 7) Tests mínimos

### Backend / Feature

- Bearer ausente → `401`.
- Permiso ausente → `403`.
- Recurso desconocido → `404`.
- Alta y actualización por clave natural.
- Registro idéntico → `omitido` / `sinCambios`.
- Registro ausente → inhabilitación lógica.
- Baja no permitida → rechazo individual y continuación del lote.
- Request vacío → rechazo sin cambios.
- Duplicados en `items[]`.
- FK inexistente o inhabilitada.
- Reglas de asistente y usuario Framework.
- Reglas de default y tipo genérico.
- Reconciliación de asignaciones, cliente ausente y `tiposTarea: []`.
- `dryRun` sin persistencia.
- Límite de 1500, tamaño JSON y timeout.

### Unitarios

- Trim de extremos sin alterar espacios internos.
- Comparación case sensitive.
- ISO date.
- Booleanos JSON.
- Campos omitidos, `null` y `""`.
- Conteo y acciones de resultados.

### OpenAPI

- Validar que los cinco paths estén publicados.
- Validar schemas y ejemplos no vacíos.
- Validar ejemplos de éxito parcial, error individual, baja lógica y `dryRun`.

---

## 8) Implementación D

Implementación realizada en backend:

- Controller, repository y fallback sqlite para los cinco endpoints.
- Dispatcher explícito en `SpCaller` y permiso `partes.integracion.maestros`.
- Parámetros configurables de timeout y tamaño JSON.
- Migraciones para estado lógico de asignaciones y permisos de integración.
- Cinco scripts SP en `backend/scripts/sql/partes/`.
- OpenAPI con schemas y ejemplos completos.
- Tests feature de alta, `dryRun`, request vacío y booleanos estrictos.

Verificaciones ejecutadas:

- `php artisan test --filter ApiV1PartesIntegracionMaestrosTest`: OK.
- `php artisan test --filter 'ApiV1PartesMaestrosTest|ApiV1PartesIntegracionMaestrosTest'`: OK, 10 tests y 72 assertions.
- `php -l` sobre los archivos PHP nuevos/modificados: OK.
- `ReadLints`: sin errores en los archivos modificados.
- `php artisan l5-swagger:generate`: OK tras actualizar `paqsuite/laravel-core` a `1.3.16-beta.1`, que incorpora el contrato `RdsBackupLock`, y retirar el adapter local no utilizado `RdsBackupCacheLock`.
- OpenAPI generado: `backend/storage/api-docs/api-docs.json`, con los cinco paths de integración y sus ejemplos.

---

## 9) Riesgos y mitigaciones

| Riesgo | Mitigación |
|--------|------------|
| Baja masiva por universo incompleto | Rechazar `items` vacío; exigir request completo y `dryRun` |
| Incompatibilidad entre SP y gateway | Misma firma lógica y scripts versionados |
| Ejemplos OpenAPI desactualizados | Test estructural o revisión junto con cada cambio de schema |
| Confusión entre baja física y lógica | Prohibición explícita de `DELETE` físico y tests de estado |
| Diferencias de idioma en errores | Responder solo claves i18n y completar cinco locales |

---

## 10) Checklist pre-C1

- [ ] HU-014 y SPEC-014 enlazados.
- [ ] Cinco slugs y cinco SP documentados.
- [ ] Firma lógica y contrato de resultados definidos.
- [ ] Reconciliación completa y bajas lógicas cubiertas.
- [ ] `dryRun`, límites y parámetros cubiertos.
- [ ] OpenAPI tiene schemas y ejemplos completos.
- [ ] Tests mínimos definidos por capa.
- [ ] No se incorporó UI, mobile, Excel ni SQL ad-hoc.

---

## 11) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-23 | Parte C: TR derivada de HU-014 y SPEC-014. |
| 2026-10-24 | Parte D: implementación backend, SP, OpenAPI y tests; pasa a Pendiente de Revisión. |
