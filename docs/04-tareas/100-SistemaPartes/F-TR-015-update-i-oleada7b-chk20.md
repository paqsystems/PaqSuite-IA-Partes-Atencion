# F — Cierre TR-015-update-i (oleada 7b / CHK-20)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-i](./TR-015-update-i-group-sum-minutos-chk20.md) |
| HU / SPEC | [HU-015-update-i](../../03-historias-usuario/100-SistemaPartes/HU-015-update-i-group-sum-minutos-chk20.md) · [SPEC oleada 7b](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-7b-group-sum-chk20-update.md) |
| F1 | [D-VERIFICACION-TR-015-update-i-chk20-2026-11-24.md](./D-VERIFICACION-TR-015-update-i-chk20-2026-11-24.md) |
| Jira | PIAPARTES-72 · PIAPARTES-73 · PIAPARTES-74 (**Finalizado** 2026-11-24) |
| Fecha | 2026-11-24 |

## Resultado

**Aprobado** — CHK-20 cerrado: batch grupo + suma, «Total minutos» en `GroupFooter_clienteNombre`, smoke manual confirmado.

## Smoke manual (usuario)

| CHK | Prompt | Resultado |
|-----|--------|-----------|
| CHK-20 | Agrupá por cliente y sumá los minutos | **OK** |

## Recomendación final

Épica/HU/TR/SPEC en **Finalizado**; **Desplegado** en Jira al abrir PR a `develop`.