# D — Verificación spike oleada 2 (aplicador layout camino B)

| Campo | Valor |
|-------|--------|
| Fecha | 2026-11-10 / cierre agente 2026-11-12 |
| Variante lab | `spike-layout-b-1` |
| Proceso | `partes.informes.consultaDetallada` |
| Sidecar | `http://127.0.0.1:5055` (PID lab variable) |
| Host | `http://127.0.0.1:3002/emisiones/disenador?cliente=DEV` |
| Referencia | [spike-015-oleada2-aplicador-layout-comparativa.md](./spike-015-oleada2-aplicador-layout-comparativa.md) |

## Veredicto

**Spike camino B — APTO** para pasar a TR oleada 2 (aplicador productivo + UX refresh + SC).

- Camino **A** (mutación JS diseñador): **no ejecutado** (cancelado MVP Partes; SPEC oleada 2 = camino B).
- **Refresh** del diseñador tras patch REPX: **obligatorio** (documentar en TR).
- **Deuda explícita:** D4 cleanup stubs al guardar; posible **XRE090** en subtotal hasta ajustar ACT-03 productivo (`XRSummary`).

## Evidencia ACT-01..03 (camino B)

| ID | Resultado | Evidencia |
|----|-----------|-----------|
| **ACT-01** | OK | `DisplayName="Spike B titulo"` en `spike-layout-b-1.repx`; usuario: pestaña/título en Properties. Script: `Apply-Act01-SetTitle.ps1`. |
| **ACT-02** | OK | `GroupHeaderCliente` + `GroupField clienteNombre` + expresión `'Cliente: ' + [clienteNombre]` en REPX; usuario: agrupación visible en lienzo. Script: `Apply-Act02-GroupBy.ps1`. |
| **ACT-03** | OK (con observación) | `GroupFooterCliente` + `sumSum([duracionMinutos])` en REPX; usuario: pie y total visibles. Validación DX: usuario reportó **Validate Bindings** limpio con **Data Source = Consulta detallada** (sin naranja en controles). Si reaparece **XRE090** en `lblGroupSumDuracion`, anotar en TR aplicador. Script: `Apply-Act03-GroupSum.ps1`. |

## Validate Bindings y Data Source

- Con **Consulta detallada** (no `Dataset` stub): bindings `[clienteNombre]`, `[duracionMinutos]`, etc. válidos (confirmación usuario + sesión diseñador previa agente).
- **Data Member** vacío: aceptable para JsonDataSource raíz array (ver nota spike §0).

## Field List / sidecar D1–D3

| Prueba | Resultado |
|--------|-----------|
| `POST /DXXRD/GetDesignerModel` `reportUrl=spike-layout-b-1` + `processCode=partes.informes.consultaDetallada` | **200**, payload incluye **Consulta detallada** (fix `DesignerModelFactory` commit Framework `4042430`). |
| Usuario: sin nuevos `componentN` tras fix + limpieza manual | OK (confirmación usuario 2026-11-10). |
| REPX en disco aún lista `Dataset` / `component1` en `ComponentStorage` | Deuda **D4**; DataSource serializado `#Ref-0` puede seguir apuntando stub hasta **Save** tras limpieza. |

## Regresión Save / TR-011

- Usuario: flujo diseñador y guardado **OK** en lab (sin bloqueo reportado).
- Agente: no repitió Save en browser (catálogo emisiones no cargó en sesión automatizada el 2026-11-12).

## Intento verificación browser (agente 2026-11-12)

- Navegación a diseñador con sesión **PQ** autenticada; pantalla **«Cargando…»** / combo proceso sin ítems (sin montar DX).
- **Backend** `GET /api/v1/health` → 200; sidecar `/health` → ok.
- Conclusión: cierre apoyado en **REPX + GetDesignerModel + validación manual usuario** (fehaciente combinada).

## Evidencia visual UI (usuario 2026-11-12)

Captura diseñador en `http://127.0.0.1:3002/emisiones/disenador?cliente=DEV`:

- Proceso **Consulta detallada** + variante **Spike B titulo** (`spike-layout-b-1`).
- Lienzo: **ACT-02** `'Cliente: ' + [clienteNombre]` · **ACT-03** `'Total minutos: ' + sumSum([duracionMinutos])` · detalle con campos schema.
- Shell cargado (menú lateral + DX designer montado); corrige intento agente sin catálogo.

## Matriz decisión (resumen)

Ver tabla actualizada en [spike §3](./spike-015-oleada2-aplicador-layout-comparativa.md).

**Veredicto spike:** **B** (patch REPX sidecar + refresh diseñador; no camino A).

## Siguiente SDD

1. Anexo este documento + veredicto en TR oleada 2 (Parte C).
2. HU oleada 2 (SC + whitelist acciones).
3. Implementar endpoint aplicador (evolución scripts spike) + montaje GEN-03 en diseñador Partes.
