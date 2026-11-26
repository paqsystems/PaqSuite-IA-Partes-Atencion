# Plan de implementación — TR-015-update-e (oleada 5), hito **T2 CHK-06**

> Parte **D1** (2026-11-20). Documento **retroactivo** tras Parte D ejecutada en el mismo día (cierre SDD T2).  
> Gate T1: [D-VERIFICACION T1](../D-VERIFICACION-TR-015-update-e-t1-chk04-2026-11-20.md) **Aprobado**.  
> SoT: [TR-015-update-e](../TR-015-update-e-page-header-informes.md) · [SPEC oleada 5](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-5-page-header-update.md) §4.2.

## Alcance entendido

Implementar **solo** CHK-06 / `setPageHeaderEmpresa`:

- Sidecar: `lblPageHdr_Empresa` en **fila 1** (`Row1Y=0`), arriba a la izquierda; texto literal `payload.text` 1..200.
- Partes: heurística prompt canónico + extracción por comillas / “empresa …”; default **Empresa demo** en lab.
- Matriz CHK-06 → `mvp`, `expectedActions`, `applyAssert`.
- Sin CHK-07/05 (T3/T4).

## Decisiones D1 (T2)

| ID | Decisión |
|----|----------|
| **D1-F01** | Reutilizar `LayoutPageHeaderLayout.EnsurePageHeaderBand` y `Row1Y=0` (TR §1). |
| **D1-F02** | Applier `LayoutApplyStepResult`; noop idempotente `noop:setPageHeaderEmpresa:ya_colocados` (misma convención token que CHK-04). |
| **D1-F03** | Buscar `lblPageHdr_Empresa` en el árbol del `PageHeaderBand` (no solo controles de primer nivel) para evitar duplicar nombre al re-aplicar. |
| **D1-F04** | Host: `DesignLayoutPageHeaderLab` — `Empresa demo` si `APP_ENV` ∈ `{local, testing}` o `X-Paq-Cliente=DEV`; en producción sin nombre en prompt → no mapear. |
| **D1-F05** | Heurística `tryMatchEmpresa` tras CHK-04 en planner; sanitize `text` 1..200 en `sanitizeActions`. |
| **D1-F06** | FE: si `applied===0` y detail `ya_colocados` (o `noop:setPageHeaderEmpresa`), i18n `partes.emissionDesign.pageHeader.empresaAlreadyPlaced` (5 locales). |
| **D1-F07** | `eval-emission-ia-oleada5-apply.ps1`: añadir `CHK-06`; aceptar `applied:0` con `ya_colocados` si REPX cumple `applyAssert`. |

## Excluido de T2

- `setPageHeaderFecha`, `wrapReportTitleInPanel`, CHK-05/07.
- Lectura nombre empresa desde BD/sesión en sidecar (SPEC D-02).

## Tests de cierre (F1)

| Capa | Comando |
|------|---------|
| Partes | `php vendor/bin/phpunit --filter DesignLayout` |
| FW | `dotnet test … -c Release --filter FullyQualifiedName~PageHeader` |
| Integrado | `.\eval-emission-ia-oleada5-apply.ps1` (sidecar `:5055`) |

## Veredicto D1

**Listo y ejecutado** — evidencia F1: [D-VERIFICACION T2](../D-VERIFICACION-TR-015-update-e-t2-chk06-2026-11-20.md). Siguiente gate: **D1 T3** antes de CHK-07.