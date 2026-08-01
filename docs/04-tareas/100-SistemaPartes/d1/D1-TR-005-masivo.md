# Plan de implementación - TR-005

## Alcance entendido

Proceso masivo web solo supervisor:

1. **Hecho (D 2026-07-30):** filtros, select-all (+ modal N), lote atómico cerrar/reabrir `{id,rowVersion}`, param `PartesMasivoMaxIds`, ruta `/partes/proceso-masivo`, atajo desde carga.
2. **Ampliación (pendiente D):** `ProcessDataGrid` (filter row, totales, column chooser, plantillas, export Excel); `POST /partes/tareas/masivo/actualizar` + SP `pq_sp_partes_tarea_masivo_actualizar` para Must `tipoTareaId` / `sinCargo` (Should: presencial, usuarioId, fecha).

## Fuentes leídas
- Producto `05-operacion-diaria-y-supervision.md` (proceso masivo)
- SPEC-005, HU-005, TR-005 (ampliación 2026-07-31)

## Impacto esperado
### Base de datos
- Nuevo SP `pq_sp_partes_tarea_masivo_actualizar`
- Existentes: set_cerrado + list_ids (sin cambio)

### Backend
- `POST /partes/tareas/masivo/actualizar`
- Validación tipo↔cliente atómica; 422 `partes.masivo.atributoInvalido`

### Frontend
- Reemplazar DataGrid crudo por ProcessDataGrid + GEN templates/export
- UI aplicar tipo + sin cargo (+ Should T7g)

### Tests
- Feature update + tipo inválido; E2E humo sinCargo

### Documentación
- OpenAPI + manual SPEC-005

## Orden de trabajo
1. SP actualizar
2. API
3. ProcessDataGrid
4. UI campos Must
5. Tests / OpenAPI / manual
6. Should (T7g)

## Riesgos
- Multi-cliente + un tipo → fallo total esperado
- Tope técnico 5000 si param=0
- No confundir export grilla con import Excel

## Tests a ejecutar
- Feature masivo (regresión + nuevos); E2E humo

## Dudas / bloqueos
- **Bloqueo D ampliación:** layouts/export GEN disponibles en el paquete FE del producto
- AC-19 (Should): **diferible** en este D1 si se prioriza Must; marcar en cierre D

## Confirmación de alcance
- Sin cambio fuera SPEC/HU/TR ampliación: **Sí**
- No mobile; no cliente/duración/descripción en lote; no import Excel/mails

## Estado D (2026-07-31)
- Must implementado: ProcessDataGrid + `POST /masivo/actualizar` + UI tipo/sinCargo
- Should implementado: UI + API presencial / usuarioId / fecha
- Feature `ApiV1PartesMasivoTest` OK; unit `partesMasivoCampos` OK
- Pendiente: F1 formal
