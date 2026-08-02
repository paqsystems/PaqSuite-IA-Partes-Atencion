# Plan de implementación - TR-003

## Alcance entendido

ABM web de 5 maestros Partes + catálogos usables + universo tipos por cliente + habilitar/revocar acceso cliente; SP familia `pq_sp_partes_*`; menú Archivos; deny cliente/native. Reusa assert exclusividad y marcar default (TR-001). Lookup `GET /admin/usuarios?soloActivos=`.

## Fuentes leídas
- SPEC-003, HU-003, TR-003 (C1 OK)
- Dependencias TR-001/002; patrones ABM/menú seed existentes

## Impacto esperado
### Base de datos
- Familia SP list/get/upsert/delete/estado por entidad + catálogos + universo tipos
- Seed menú Archivos (5 ítems)

### Backend
- Controllers `/api/v1/partes/...`; middleware perfil + deny cliente
- Invocar assert exclusividad / marcar default
- Lookup usuarios (implementar si falta)

### Frontend
- 5 pantallas grilla+modal DevExtreme; rutas `/archivos/partes/...`
- testids `partesMaestros*`; i18n; sin native

### Tests
- Feature CRUD + exclusividad + default + universo + 403 cliente
- E2E humo 1 ABM

### Documentación
- OpenAPI maestros

### DevOps
- migrate SP + seed menú; permisos rol supervisor

## Orden de trabajo
1. SP maestros + catálogos
2. API + middleware deny cliente
3. Lookup usuarios
4. FE 5 ABM + menú
5. Tests

## Riesgos
- Lookup users ausente en host → implementar mínimo
- Delete con refs → 422 consistente
- Mobile bypass → policy/filtrado menú

## Tests a ejecutar
- Feature maestros; E2E humo asistentes
- Smoke: marcar default + exclusividad userId

## Dudas / bloqueos
- **Bloqueo D:** TR-002 (gate + `EnsurePartesFunctionalProfile` + `resultado.partes` + seed admin/PQ). TR-001 **ya hecho**.
- Lookup `GET /admin/usuarios` ausente hoy → T0 Must antes de FE
- OpenAPI: no hay artefacto en repo; fijar path canónico en T7 sin inventar contrato

## Confirmación de alcance
- Sin cambio fuera SPEC/HU/TR: **Sí**
- Lookup users = solo GET; no ABM users/roles GEN
