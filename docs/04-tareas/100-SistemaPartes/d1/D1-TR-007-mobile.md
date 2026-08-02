# Plan de implementación - TR-007

## Alcance entendido

Capacitor native Partes: config URL+health; login empresa→`X-Paq-Cliente`; dashboard (pull, sin timer); kardex CRUD; informe paquete horas (fachada + Chart bar); policy `partesMobilePolicy`; exclusiones ABM/pivot/masivo.

## Fuentes leídas
- SPEC-007, HU-007, TR-007 (C1 OK); normas BASE mobile

## Impacto esperado
### Base de datos
- Seed menú `partes_informe_paquete_horas` (opcional SP compuesto Should)

### Backend
- Fachada `GET /partes/informes/paquete-horas` (orquesta dashboard + agrupado×2)
- Reuso APIs TR-004/006

### Frontend
- `partesMobilePolicy` + tests; ramas `isNativeApp()`
- Vistas kardex/dashboard/informe; Preferences GEN
- Chart bar DX

### Tests
- Vitest policy; Feature fachada; smoke emulador manual; `build:mobile`

### Documentación
- OpenAPI fachada; checklist humo emulador

### DevOps
- `npm run build:mobile` + `npx cap sync`

## Orden de trabajo
1. Policy + filtro menú/deep-link
2. Dashboard/kardex/form native
3. Fachada + informe + chart
4. build:mobile + humo

## Riesgos
- Bypass deep-link → guard policy
- Chart DX Capacitor → solo bar
- Capacitor scaffold incompleto en este clone → evaluar en D (puede requerir bootstrap GEN)

## Tests a ejecutar
- `partesMobilePolicy.test.ts`; Feature paquete-horas; build:mobile

## Dudas / bloqueos
- **Bloqueo D:** TR-002…006 + scaffold Capacitor GEN (hoy: sin `build:mobile` / android-ios cableados; `isNativeApp={false}` fijo en shell)
- Fachada: orquestación PHP Must; SP compuesto = Should
- Chart: DX **bar**, default serie por cliente

## Confirmación de alcance
- Sin cambio fuera SPEC/HU/TR: **Sí**
