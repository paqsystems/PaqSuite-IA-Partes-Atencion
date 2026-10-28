# Handoff GEN-15 — lab local 2026-10-28

Continuar acá: el PDF **trae datos** pero **no el diseño**; el diseñador **no muestra** los layouts bien.

## Qué ya quedó

- SDK por **Cloudsmith** (`paqsuite/laravel-core` 1.3.17-beta.1, `@paqsuite/react-core` 2.4.30-beta.1). Token **`CLOUDSMITH_READ_TOKEN`** en el SO (no en `backend/.env`).
- Lab contra RDS: `PAQSUITE_INSTALACION_RESOLVER=sql` + `X-Paq-Cliente`.
- En localhost el SDK fuerza **DEMO**. El host honra `VITE_TENANT_DEFAULT_CLIENT` / `?cliente=` (`frontend/src/features/auth/platformContext.ts`).
- Reportes diseñados viven **por tenant**. Los de **DEV** (`paqsystems_partesatencion_dev`) no se ven en DEMO.
- Emitir **exige reporte no estándar**. Solo `<Original>` (`is_standard=1`) → diálogo sin combo y POST **4701** «Datos de emisión inválidos». Hace falta **Guardar como**.
- Con tenant DEV y un reporte copiado: **PDF con filas** de Consulta detallada (`PAQSUITE_EMISSION_DX_MODE=remote`, sidecar `:5055`).

`TENANT_ALLOWED_CLIENTS` en Laravel **no** se usa en este host.

## Deuda (mañana)

1. **Render no usa el layout diseñado** — el dataset llega; el REPX/apariencia no.
2. **Diseñador** — los diseños no se ven/editan bien (superficie DX / proxy `/DXXRD*`).

Pistas (verificar, no asumir):

| Dónde | Qué mirar |
|-------|-----------|
| `pq_emission_reports` en `_dev` | `report_code`, `is_standard`, `layout_mime`, si `layout_definition` es XML REPX o JSON tabular del seed |
| `SpEmissionRepository::findReportLayoutForRender` | Solo reenvía `application/xml`; JSON se ignora; sidecar resuelve `.repx` por código |
| POST sidecar `/api/v1/dx-reporting` | `reportId` / `report_code` vs archivo en `data/layouts` |
| Diseñador | `GetDesignerModel`, proxy Vite `/DXXRD*` → `:5055`, chunk Knockout/Ace |
| Host `EmissionDialog` | ¿manda `reportId` del combo? |

SPEC-011: «Emitir no exige diseñar» vs código actual (rechaza estándar). No cambiar esa RN en este handoff; primero hacer que una copia diseñada **renderice su REPX**.

## Cómo retomar el lab

Puertos si chocan con TANGO/PedidosWeb: FE `:3012`, BE `:8012`, sidecar `:5055`.

```text
http://127.0.0.1:3012/login?cliente=DEV
```

Pie de shell: **DEV**, no DEMO. `frontend/.env`: `VITE_TENANT_DEFAULT_CLIENT=DEV`. `backend/.env`: `PAQSUITE_CLIENTE=DEV`, `PAQSUITE_EMISSION_DX_MODE=remote`.

Health: `GET /api/v1/health` con `X-Paq-Cliente: DEV`. Sidecar: `GET http://127.0.0.1:5055/health`.

Detalle de operador: `MANUAL-DEL-PROGRAMADOR.md` §1.12 y Anexo A.3 / A.6.
