# Plan de implementación - TR-007 (D delta GEN-22)

## Alcance entendido

Sustituir clones mobile del host por exports GEN-22 de `@paqsuite/react-core@2.2.1`. Conservar dominio Partes (allowlist, mappers, form tarea, Chart `bar`, fachada informe ya hecha). Completar scaffold Capacitor.

## Fuentes leídas

- SPEC-007, HU-007, TR-007 (realineado 2026-08-17)
- Paquete `react-core` `src/mobile/*` + `DashboardContainer` / `AuthLoginLayout` / `ShellLayout`

## Impacto esperado

### Base de datos

- Sin cambio (seed informe ya existe)

### Backend

- Sin reabrir fachada `GET /partes/informes/paquete-horas`

### Frontend

- `createMobilePolicy` + const allowlist (eliminar engine/denylist propios)
- `MobileRouteGuard`, `MobileMenuShell`, `MobileConfigPanel`, `ConsultaKardexList`
- Dashboard/informe native: `DashboardContainer` + kardex; **no** `ProcessDataGrid`
- Login: `loginTenant` + persistencia GEN cliente
- Scaffold Capacitor `android/` `ios/` + `build:mobile`

### Tests

- Vitest policy sobre policy GEN; mapper kardex; humo emulador

### Documentación

- Runbook smoke: testids GEN (`consultaKardexList`, `mobileConfig*`)

## Orden de trabajo

1. Policy GEN + guard + menú native
2. Config + login tenant
3. Kardex `ConsultaKardexList` + form compartido
4. Dashboard/informe sin DataGrid native
5. Capacitor + humo

## Riesgos

- `isNativeApp()` false hasta Capacitor (T6)
- `MobileConfigPanel` pide tenant para health → cablear login/persistido, no campo en popup
- No duplicar form carga

## Tests a ejecutar

- `partesMobilePolicy.test.ts` (API `createMobilePolicy`)
- Feature paquete-horas (regresión)
- `build:mobile` + `cap sync`

## Dudas / bloqueos

- Scaffold Capacitor ausente en este clone → T6 Must
- Allowlist y mapper **no** se suben al Framework (producto)

## Confirmación de alcance

- Sin cambio funcional fuera SPEC/HU/TR: **Sí** (solo adopción GEN-22)
