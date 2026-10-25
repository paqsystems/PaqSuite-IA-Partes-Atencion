# Plan de implementación — TR-014

## Tipo de trabajo

Original — Must-Have de integración REST de maestros Partes.

## Alcance entendido

Implementar cinco endpoints autenticados para reconciliar el universo completo de:

- clientes;
- asistentes;
- tipos de cliente;
- tipos de tarea;
- asignaciones cliente–tipo de tarea.

El proceso debe aplicar altas, modificaciones, no-op e inhabilitaciones lógicas, con procesamiento parcial por fila y `dryRun`. La persistencia de negocio será mediante stored procedures y el contrato público se documentará en OpenAPI con ejemplos completos.

No incluye UI, menú, mobile, Excel, jobs, webhooks ni eliminación física.

## Fuentes leídas

- SPEC-014 y su revisión A1.
- HU-014.
- TR-014 y revisión C1 aprobada.
- `SPEC-101-22-importacion-maestros-erp.md` de PedidosWeb para envelope y resultado de lote.
- `backend/routes/api.php`.
- `SpCaller`, `PartesMaestrosOperations`, `SpParametroRepository`.
- `PartesMaestrosController`, `OpenApiPathsPartesMaestros`.
- `ApiV1PartesMaestrosTest`.
- `docs/04-tareas/100-SistemaPartes/TR-014-apis-importar-datos-maestros.md`.

## Dependencias y estado

| Dependencia | Uso | Estado |
|-------------|-----|--------|
| GEN-20 / `ApiResponse` | Envelope `error`, `respuesta`, `resultado` | Disponible |
| Auth Sanctum | Bearer | Disponible |
| Header `X-Paq-Cliente` | Instalación MONO | Disponible |
| `SpCaller` | Ejecución SQL Server y fallback sqlite | Disponible; requiere nueva rama explícita |
| `SpParametroRepository` | Lectura de `PQ_PARAMETROS_GRAL` | Disponible |
| SPEC-003 / maestros | Tablas y reglas de dominio | Disponible |
| Permisos | `partes.integracion.maestros` | Requiere seed/configuración |

## Impacto esperado

### Base de datos

- Crear scripts SQL Server en `backend/scripts/sql/partes/`:
  - `pq_sp_partes_integracion_maestros_clientes.sql`
  - `pq_sp_partes_integracion_maestros_asistentes.sql`
  - `pq_sp_partes_integracion_maestros_tipos_cliente.sql`
  - `pq_sp_partes_integracion_maestros_tipos_tarea.sql`
  - `pq_sp_partes_integracion_maestros_asignaciones.sql`
- Cada SP recibirá:
  ```text
  @itemsJson nvarchar(max),
  @fechaProceso date,
  @origen nvarchar(100) = NULL,
  @dryRun bit = 0
  ```
- Cada SP devolverá un único resultset:
  - una fila `RESUMEN`;
  - cero o más filas `ITEM`;
  - columna discriminadora `tipoRegistro`.
- La reconciliación debe ser lógica:
  - alta/update de presentes;
  - inhabilitación de ausentes;
  - ninguna eliminación física;
  - `dryRun` sin escritura.
- El resultado incluirá contadores, acción, clave, índice, código i18n y truncamiento de errores.
- No agregar DDL destructivo.

### Backend

Crear:

- `PartesIntegracionMaestrosController`.
- `PartesIntegracionMaestrosRepository`.
- `PartesIntegracionMaestrosOperations` para fallback sqlite/tests.
- DTOs/validadores de request común y de los cinco recursos.
- Mapper del resultset único `RESUMEN`/`ITEM` al envelope GEN-20.
- Excepciones y catálogo de claves `integracion.maestro.*`.

Modificar:

- `backend/routes/api.php`: grupo autenticado de integración.
- `SpCaller`: rama explícita para `pq_sp_partes_integracion_maestros_*` antes del detector genérico de maestros.
- `AppServiceProvider` o configuración equivalente: binding/registro si aplica.
- Seed/configuración de permiso `partes.integracion.maestros`.
- Acceso a parámetros mediante `SpParametroRepository`.

Validaciones:

- Bearer y `X-Paq-Cliente`.
- Permiso dedicado.
- Fecha ISO.
- Booleanos JSON.
- Código case sensitive con trim de extremos.
- Campos obligatorios por recurso.
- Semántica de alta/update para opcionales.
- `tiposTarea` obligatorio y no anulable.
- Request vacío, duplicados, FK y referencias inhabilitadas.
- Defaults de asistentes y tipos de tarea.
- Último `isDefault=true` prevalece e impone `isGenerico=true`.
- Límite 1500, tamaño configurable 10 MB y timeout configurable 120 segundos.

### Frontend

Sin cambios funcionales. No crear rutas, pantallas ni ramas mobile.

### Tests

Crear `backend/tests/Feature/Integracion/ApiV1PartesIntegracionMaestrosTest.php` o ubicación equivalente.

Casos:

- `401`, `403`, `404`.
- Alta, actualización y no-op de cada maestro.
- Bajas lógicas de ausentes.
- Baja no permitida sin abortar el resto.
- Request vacío.
- Reconciliación de asignaciones:
  - cliente ausente;
  - `tiposTarea: []`;
  - tipo presente nuevo;
  - tipo ausente existente.
- Defaults de tipos de tarea y prevalencia del último default.
- Asistente con `usuario` inválido o ya vinculado.
- `dryRun` sin persistencia.
- Campos omitidos en alta y actualización.
- `null`/`""` explícitos.
- Duplicados, FK y referencias inhabilitadas.
- Límite de 1500, tamaño y timeout.
- Mapeo del resultset único al envelope.

Tests unitarios:

- Normalización.
- Defaults.
- Reglas de `isDefault`.
- Parser `RESUMEN`/`ITEM`.
- Contadores y acciones.

### OpenAPI y documentación

Modificar o crear `backend/app/OpenApi/OpenApiPathsPartesIntegracionMaestros.php`.

Documentar:

- cinco paths;
- seguridad Bearer y `X-Paq-Cliente`;
- request común;
- schemas completos de cada `items[]`;
- campos obligatorios, opcionales y anulables;
- ejemplos de alta, modificación, baja, no-op, error parcial y `dryRun`;
- respuesta GEN-20;
- resultset ya transformado a respuesta pública;
- errores 401/403/404 y errores globales.

Actualizar trazabilidad de:

- SPEC-014;
- HU-014;
- TR-014;
- README de tareas si cambia el estado.

## Orden de trabajo

1. Confirmar contratos de tablas, columnas de estado, referencias y códigos de permiso.
2. Crear scripts SP y definir el resultset único común.
3. Crear `PartesIntegracionMaestrosOperations` para sqlite/tests.
4. Extender `SpCaller` con rama explícita de integración.
5. Crear repository, validadores, mapper y excepción.
6. Crear controller y rutas protegidas.
7. Implementar lectura de parámetros y límites.
8. Seedear/configurar `partes.integracion.maestros`.
9. Crear OpenAPI con schemas y ejemplos completos.
10. Crear tests feature/unitarios.
11. Ejecutar tests, revisar envelope y actualizar documentación.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| `SpCaller` deriva el nuevo prefijo al dispatcher genérico | Rama explícita antes del detector `pq_sp_partes_*` |
| Baja masiva por universo incompleto | Rechazar `items: []`, soportar `dryRun` y testear ausentes |
| Drift entre Operations y SQL Server | Misma firma, resultset y casos de prueba |
| Default de tipos inconsistente | Procesar orden del JSON y aplicar regla de último default |
| Borrado accidental | Solo flags de estado; prohibir `DELETE` físico |
| OpenAPI incompleto | Schemas y ejemplos como criterio bloqueante de tests/documentación |
| Parámetros no disponibles | Defaults 120/10 y lectura por repository SP |

## Tests a ejecutar

```text
cd backend
php artisan test --filter ApiV1PartesIntegracionMaestros
php artisan test --filter PartesIntegracion
php artisan l5-swagger:generate
```

Validaciones adicionales:

- revisar los cinco paths en Swagger;
- comprobar ejemplos completos;
- ejecutar suite backend completa;
- verificar `git diff --check`.

## Dudas / bloqueos

No quedan bloqueos funcionales de C1.

Durante D1 debe verificarse contra el esquema real:

- nombres exactos de columnas de estado;
- mecanismo vigente de seed del permiso;
- disponibilidad de `PQ_PARAMETROS_GRAL` para los dos parámetros;
- compatibilidad del resultset único con SQL Server y el fallback sqlite.

Si alguno difiere, debe documentarse como ajuste técnico sin alterar el contrato de HU/TR.

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC-014/HU-014/TR-014: **Sí**.
- No se implementará UI, mobile, Excel, jobs ni eliminación física.
- No se hará commit ni push en D1.
