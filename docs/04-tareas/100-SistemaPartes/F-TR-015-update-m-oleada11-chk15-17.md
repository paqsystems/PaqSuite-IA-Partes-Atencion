# F — TR-015-update-m (oleada 11 / CHK-15..17)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-m](./TR-015-update-m-imagenes-chk15-17.md) |
| HU / SPEC | [HU-015-update-m](../../03-historias-usuario/100-SistemaPartes/HU-015-update-m-imagenes-chk15-17.md) · [SPEC oleada 11](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md) |
| F1 | [D-VERIFICACION-TR-015-update-m-chk15-17-2026-11-25.md](./D-VERIFICACION-TR-015-update-m-chk15-17-2026-11-25.md) |
| Jira | PIAPARTES-78 (HU) · PIAPARTES-79 (TR) · PIAPARTES-53 |
| Fecha cierre | 2026-11-25 |

## Resultado

**Aprobado** — watermark, logo page header y QR; smoke UI confirmado (orden 16 → 15 → 17). Extensión acordada en prueba: QR con `placement` en bandas del informe (no solo detalle).

## Smoke manual (usuario)

| CHK | Prompt | Resultado |
|-----|--------|-----------|
| CHK-16 | `Insertá el logo de la empresa` | **OK** |
| CHK-15 | `Agregá una imagen de fondo al informe` | **OK** |
| CHK-17 | `Mostrá clienteCode como código QR` (+ variantes por zona) | **OK** |

## Evidencia automatizada (E)

- PHPUnit `DesignLayoutChecklistPlannerTest` / `DesignLayoutActionPlannerTest`
- `dotnet test` — `LayoutImagesOleada11Tests`
- `eval-emission-ia-oleada11-chk15-17-apply.ps1` PASS

## Notas post-cierre

- Matriz CHK-17 canónica sigue en **detalle**; otras bandas vía prompt con ubicación (`pageHeader`, `reportFooter`, etc.).
- Commits/repos: pendiente autorización explícita del usuario.