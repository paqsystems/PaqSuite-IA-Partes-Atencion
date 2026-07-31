# Plan de implementación - TR-005

## Alcance entendido

Proceso masivo web solo supervisor: filtros, select-all resultado filtrado (+ modal N si >1 página), lote atómico cerrar/reabrir con `{id,rowVersion}`, param `PartesMasivoMaxIds`, pantalla `/partes/proceso-masivo`, atajo desde carga sin filtros.

## Fuentes leídas
- SPEC-005, HU-005, TR-005 (C1 OK)

## Impacto esperado
### Base de datos
- SP `pq_sp_partes_tarea_masivo_set_cerrado` + `pq_sp_partes_tarea_list_ids`
- Seed param `PartesMasivoMaxIds=0` + menú

### Backend
- `GET /partes/tareas/ids`, `POST /partes/tareas/masivo/set-cerrado`
- 403 no supervisor; 409/422 atómicos

### Frontend
- Pantalla masivo + select-all + confirms; link desde carga
- i18n `partes.masivo.*`

### Tests
- Feature atomic/idempotencia/tope/409; E2E cerrar 2 filas

### Documentación
- OpenAPI masivo

### DevOps
- migrate + seed param/menú

## Orden de trabajo
1. SP + param
2. API
3. FE
4. Tests

## Riesgos
- Select-all miles de ids → tope param + **hard 5000**
- JSON payload size → mismo tope
- Encoding `rowVersion` alineado a TR-004

## Tests a ejecutar
- Feature masivo; E2E humo 2 filas

## Dudas / bloqueos
- **Bloqueo D:** TR-002 + TR-004 (list/`rowVersion`/`esSupervisor`)
- Tope técnico si param=0: **cerrado D1 = 5000** → 422 `partes.masivo.loteDemasiadoGrande` (FE+BE)
- Payload SP = JSON (no TVP)

## Confirmación de alcance
- Sin cambio fuera SPEC/HU/TR: **Sí**
- No mobile masivo; no edición campos en lote
