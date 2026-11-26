# F — TR-015-update-n (oleada 12 / CHK-28)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-n](./TR-015-update-n-remove-detalle-chk28.md) |
| HU / SPEC | [HU-015-update-n](../../03-historias-usuario/100-SistemaPartes/HU-015-update-n-remove-detalle-chk28.md) · [SPEC oleada 12](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-12-remove-detail-chk28-update.md) |
| F1 | [D-VERIFICACION-TR-015-update-n-chk28-2026-11-26.md](./D-VERIFICACION-TR-015-update-n-chk28-2026-11-26.md) |
| Jira | PIAPARTES-80 (HU) · PIAPARTES-81 (TR) · PIAPARTES-53 |
| Fecha cierre | 2026-11-26 |

## Resultado

**Aprobado** — `removeDetailField` para observación; quita binding en detalle y `lblPageHdr_observacion`; noop en re-aplicación. Smoke UI confirmado por usuario.

## Smoke manual (usuario)

| CHK | Prompt | Resultado |
|-----|--------|-----------|
| CHK-28 | `Quitá la columna observación del detalle` | **OK** — detalle + encabezado columna |

## Evidencia automatizada (E)

- PHPUnit `DesignLayout*` (matriz CHK-28)
- `dotnet test` — `LayoutDetailFieldRemoveTests`
- `eval-emission-ia-oleada12-chk28-apply.ps1` PASS

## Notas post-cierre

- Primera familia **remove*** en whitelist v1.3; CHK-29..32 siguen fuera.
- Commits: Framework (`dx-reporting`) + Partes; pendiente autorización explícita.