# Plan de implementación - TR-004

## Alcance entendido

Carga diaria web: listado/filtros, CRUD tarea, cerrar/reabrir individual (supervisor), tramo duración param `PartesDuracionTramoMin` (default 15), optimistic lock `rowVersion`, confirm fecha futura, ruta `/partes/carga-diaria`. SP `pq_sp_partes_tarea_*`.

## Fuentes leídas
- SPEC-004, HU-004, TR-004 (C1 OK); TR-001 `row_version`; TR-002 gate

## Impacto esperado
### Base de datos
- SP list/get/upsert/delete/set_cerrado
- Seed param `PartesDuracionTramoMin=15` programa `Partes`
- Seed menú carga diaria

### Backend
- API `/partes/tareas` + cerrar/reabrir + param tramo
- 409 conflicto versión; 422 validaciones

### Frontend
- Página carga diaria DataGrid + modal; columna asistente (supervisor)
- Atajo link a masivo (sin filtros) — destino TR-005
- i18n `partes.tarea.*`

### Tests
- Feature CRUD + cerrado + 409 + tramo; E2E humo alta

### Documentación
- OpenAPI tareas

### DevOps
- migrate SP + seed param/menú

## Orden de trabajo
1. Param + SP tarea
2. API
3. FE carga diaria
4. Tests

## Riesgos
- Hex `rowVersion` encoding FE/BE
- Fecha futura confirm flag
- Delimitación por rol en list

## Tests a ejecutar
- Feature familia tarea; E2E alta 15 min

## Dudas / bloqueos
- **Bloqueo D:** TR-002 + TR-003 (perfil + catálogos/universo). TR-001 (`REGISTRO_TAREA` + `row_version`) **ya hecho**.
- `Programa=Partes` para param GRAL: **adoptado** (decisión D1)
- Código menú propuesto: `partes_carga_diaria` → `/partes/carga-diaria`
- `rowVersion` = hex mayúsculas estable BE+FE

## Confirmación de alcance
- Sin cambio fuera SPEC/HU/TR: **Sí** (IA fuera MVP)
