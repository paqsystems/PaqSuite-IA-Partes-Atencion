# Evidencia Parte E — TR-011 Reportes / emisiones GEN-15 (2026-10-16)

| Campo | Valor |
|-------|--------|
| TR | [TR-011](../../100-SistemaPartes/TR-011-reportes-emisiones.md) |
| HU | [HU-011](../../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) · Jira PIAPARTES-25 |
| Task | PIAPARTES-39 |
| Fecha | 2026-10-16 |
| Resultado E | **OK con observaciones** (suite TR pass; E2E completo del repo con fallos ajenos + 1 flake DX) |

## Comandos / resultados

| Capa | Comando | Resultado |
|------|---------|-----------|
| Backend | `cd backend && php artisan test` | **108 passed**, 7 skipped (integridad SQL Server), 747 assertions, ~97 s |
| Feature TR-011 | `Tests\Feature\Emissions\ApiV1EmissionsPartesTest` (incluido en lo anterior) | **11 passed** |
| Vitest | `cd frontend && npm run test` | **53 files / 168 tests passed**, ~116 s |
| E2E TR-011 | `npx playwright test tests/e2e/partes-emisiones.spec.ts` | **2 passed** (~16 s) |
| E2E repo | `cd frontend && npm run test:e2e` | **15 passed**, **4 failed**, 5 did not run (~2.3 min) |

No existe `npm run test:run`; el script del host es `npm run test` (vitest run).

## Feature BE (CA-21 / §8 TR)

- GET process seed + 401 sin token
- `EmissionEnabled` off → **4704**
- Sin puerto → **4706**
- Fechas vacías → **4701**
- Sin menú consulta → **4703**
- Design sin permiso → **4709**; asistente emite
- PDF todas las filas + `hostContext` por `jobId`
- Cliente no ve otras orgs y puede emitir
- Mail síncrono con `mailTo`
- Supervisor design OK + save-as catálogo

## Vitest (gates)

Cubren montaje `EmissionDialog` solo en Consulta detallada, `partesConsultaDetalladaEmit`, `hostContext`, policy native `/emisiones`, catálogo de reportes.

## E2E TR-011 (aislado, pass)

1. Login → Consulta detallada → `partesConsultaDetalladaEmit` visible; si enabled abre `emission.dialog`.
2. `/emisiones/disenador` → lista+confirmación → host DX + `GetDesignerModel` OK.

## Observaciones (no retroceden la TR)

1. E2E `partes-emisiones.spec.ts` endurecido (2026-10-16 tarde): falla rápida si el catálogo GEN no carga (`infra.transport` / combo vacío); `GetDesignerModel` se espera **después** de confirmar proceso; retry de login. Aislado **2 passed**. La suite repo con 8 workers sigue saturando `php -S` (logins ajenos a GEN-15).
2. 7 tests `PqPartesSchemaIntegrityTest` skipped en sqlite (esperado).
3. Warnings DX trial (W0019/W0021) en consola Vite; filtrados en el spec de diseñador.
4. Backend local `:8010` respondió health 200; DX host `:5055` escuchaba (404 en `/`).

## Siguiente

Parte **F1** sobre TR-011 (verificación contra CA). HU PIAPARTES-25 se mantiene Story; no fusionar con 39.
