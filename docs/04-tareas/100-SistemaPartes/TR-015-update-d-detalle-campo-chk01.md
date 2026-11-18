# TR-015-update-d – `addDetailField` (CHK-01)

| Campo | Valor |
|-------|--------|
| TR previa | [TR-015-update-c](./TR-015-update-c-apariencia-controles-informes.md) |
| CHK | CHK-01 |
| Estado | **Finalizado** |
| Evidencia | [D-VERIFICACION oleada4 CHK01-03](../updates/100-SistemaPartes/D-VERIFICACION-oleada4-chk01-03-2026-11-18.md) — smoke **OK** |

## Whitelist

| `addDetailField` | `payload.field` (schema) | `LayoutDetailFieldApplier` |

## Pruebas

`php vendor/bin/phpunit --filter DesignLayout` · `dotnet test … LayoutDetailFieldTests`