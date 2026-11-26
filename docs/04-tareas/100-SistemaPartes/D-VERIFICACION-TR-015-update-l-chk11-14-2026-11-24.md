# D-VERIFICACION — TR-015-update-l (CHK-11..14)

| Fecha F1 | 2026-11-24 |
| Smoke UI cerrado | 2026-11-25 |

**Aprobado** — automatizado + smoke UI CHK-11..14 **OK** (usuario 2026-11-24 / 2026-11-25).

| Comando | OK |
|---------|-----|
| phpunit DesignLayout | 53 |
| dotnet LayoutCalculatedField | 3 |
| eval-emission-ia-oleada10-chk11-14-apply.ps1 | **PASS** CHK-11..14 |

| CHK | Prompt | Smoke |
|-----|--------|-------|
| 11 | Creá un campo calculado que sume duracionMinutos | **OK** (usuario 2026-11-24) |
| 12 | Campo calculado IIf entre clienteNombre y observacion | **OK** (usuario 2026-11-24) |
| 13 | Mostrá un campo calculado en el detalle | **OK** (usuario 2026-11-25) |
| 14 | Totalizá el campo calculado en el pie del informe | **OK** (usuario 2026-11-25) |

**Nota MVP:** nombre de calculado en prompts canónicos = fijos (`calc_sumaMinutos` / `calc_textoCliente`); `name` custom solo vía LLM/contrato, no heurística CHK.