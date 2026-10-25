# Revisión de ambigüedad — SPEC-014 — APIs para importar datos maestros

| Campo | Valor |
|-------|-------|
| **SPEC** | [`SPEC-014-apis-importar-datos-maestros.md`](./SPEC-014-apis-importar-datos-maestros.md) |
| **Paso** | A1 |
| **Fecha** | 2026-10-23 |
| **Origen** | Parte A 2026-10-21 + decisiones humanas 2026-10-23 · producto [`17-APIs-importar-datos-maestros.md`](../../02-producto/Sistema-Partes-IA/17-APIs-importar-datos-maestros.md) |

## Resultado general

- Estado: **Apto con observaciones**

La revisión incorpora las decisiones humanas tomadas durante A1. El contrato de Partes queda definido como reconciliación completa por recurso, con bajas lógicas, procesamiento parcial por fila, `dryRun`, SP por lote y límites configurables. Quedan observaciones de detalle para HU/TR y para completar el contrato OpenAPI, pero no bloquean la derivación de HU.

## Decisiones cerradas por referencia de PedidosWeb y Partes

- Endpoint por recurso:
  `POST /api/v1/integracion/maestros/{recurso}`.
- Sin sobre multi-recurso en v1.
- Request común JSON: `fechaProceso`, `origen` opcional, `dryRun` e `items[]`.
- Cada request representa el universo completo vigente del recurso; lo ausente se intenta inhabilitar lógicamente.
- Request vacío = rechazo completo, sin bajas masivas.
- El origen carga las dependencias; el API no reordena. FK faltante = fila rechazada.
- Unidad transaccional: fila, con éxito parcial.
- Éxito parcial: HTTP `200` con `error: 0`.
- Request abortado por auth, contrato, tamaño u otro error global: HTTP `4xx/5xx` con `error != 0`.
- `401` sin Bearer, `403` sin permiso, `404` recurso desconocido.
- Resultado: `recurso`, `dryRun`, `recibidos`, `aplicados`, `omitidos`, `rechazados`, resultados con `accion` y `errores[]`.
- Límite de `1500` ítems por request y máximo de `50` errores informados.
- `Idempotency-Key` fuera de v1; reenviar el mismo universo produce un no-op.
- Claves de error de dominio con prefijo `integracion.maestro.*`.
- Permiso dedicado: `partes.integracion.maestros`.
- `dryRun` usa los mismos contadores y no persiste cambios.

## Observaciones de detalle para HU/TR/OpenAPI

- Completar en OpenAPI el esquema exacto de cada `items[]`, con tipos, obligatoriedad, anulabilidad y ejemplos completos.
- Incorporar ejemplos de alta, modificación, baja lógica, sin cambios, error parcial y `dryRun` para los cinco recursos.
- Convertir la firma lógica común de los SP en parámetros físicos definitivos durante TR/SQL.
- Completar el seed/configuración del permiso `partes.integracion.maestros`.
- Completar el catálogo i18n en los cinco locales sin prosa de negocio en las respuestas.

## Supuestos detectados

- El contrato común de PedidosWeb es una referencia de plataforma y no sustituye las reglas de maestros de SPEC-003.
- La reconciliación completa aplica a cada recurso; no se usa `delta` para este alcance.
- Los registros ausentes también pueden haber sido creados manualmente y se intentan inhabilitar.
- No existe dependencia cruzada dentro de un request porque cada request representa un único recurso.
- El origen es responsable de cargar previamente tipos y referencias requeridas.
- El ABM de SPEC-003 continúa siendo la fuente de validación de negocio.

## Decisiones humanas incorporadas

- Bajas lógicas: `Inhabilitado = true` o `Activo = false`, según el modelo.
- Código case sensitive; trim solo en extremos, sin modificar espacios internos.
- Fechas en formato ISO único.
- Booleanos exclusivamente `true`/`false` JSON.
- Campo omitido conserva valor; `null` o `""` borra cuando corresponde.
- Timeout por defecto: 120 segundos.
- JSON máximo por defecto: 10 MB.
- Parámetros: `PARTES_INTEGRACION_MAESTROS_TIMEOUT_SEGUNDOS` y `PARTES_INTEGRACION_MAESTROS_MAX_JSON_MB`.
- SP: `pq_sp_partes_integracion_maestros_clientes`, `pq_sp_partes_integracion_maestros_asistentes`, `pq_sp_partes_integracion_maestros_tipos_cliente`, `pq_sp_partes_integracion_maestros_tipos_tarea` y `pq_sp_partes_integracion_maestros_asignaciones`.
- Firma lógica: `@itemsJson`, `@fechaProceso`, `@origen`.

## Recomendaciones de ajuste del SPEC

- Mantener el contrato transversal alineado con PedidosWeb donde no contradiga la reconciliación completa de Partes.
- Derivar HU/TR usando los ejemplos del §4.7 como base del contrato OpenAPI.
- No permitir ejemplos incompletos de `items` en Swagger.
- Validar en TR que los cinco SP produzcan resultados por fila y respeten las bajas lógicas.

## Veredicto

- **Puede pasar a HU (Parte B): Sí, con observaciones.**
- El comportamiento funcional y las decisiones humanas están cerrados. Las observaciones restantes son de precisión de schemas OpenAPI, catálogo i18n y firmas físicas de SQL, y deben resolverse en HU/TR antes de implementar.
