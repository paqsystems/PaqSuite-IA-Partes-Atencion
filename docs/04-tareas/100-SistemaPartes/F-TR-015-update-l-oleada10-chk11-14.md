# F — TR-015-update-l (oleada 10 / CHK-11..14)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-l](./TR-015-update-l-calculados-chk11-14.md) |
| HU / SPEC | [HU-015-update-l](../../03-historias-usuario/100-SistemaPartes/HU-015-update-l-calculados-chk11-14.md) · [SPEC oleada 10](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-10-calculados-chk11-14-update.md) |
| F1 | [D-VERIFICACION-TR-015-update-l-chk11-14-2026-11-24.md](./D-VERIFICACION-TR-015-update-l-chk11-14-2026-11-24.md) |
| Fecha | 2026-11-25 |

## Resultado

**Aprobado** — calculados sum/iif, label en detalle y total en report footer; smoke UI confirmado (bloque 2: CHK-11..14).

## Smoke manual (usuario)

| CHK | Prompt | Resultado |
|-----|--------|-----------|
| CHK-11 | `Creá un campo calculado que sume duracionMinutos` | **OK** (2026-11-24) |
| CHK-12 | `Campo calculado IIf entre clienteNombre y observacion` | **OK** (2026-11-24) |
| CHK-13 | `Mostrá un campo calculado en el detalle` | **OK** (2026-11-25) |
| CHK-14 | `Totalizá el campo calculado en el pie del informe` | **OK** (2026-11-25) |