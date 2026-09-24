# Verificación F1 — TR-009 Importación Excel

| Campo | Valor |
|-------|-------|
| TR | [TR-009](../../100-SistemaPartes/TR-009-importacion-partes-excel.md) |
| SPEC | [SPEC-009](../../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md) |
| Fecha | 2026-09-23 |
| Resultado | **OK — finalizado** |

## Evidencia automatizada

| Capa | Comando | Resultado |
|------|---------|-----------|
| Feature backend | `php artisan test --filter=ApiV1ExcelImportPartesTest` | **4 passed, 30 assertions** |
| Vitest / carga diaria | `npx vitest run src/features/partes/carga` | **OK** — evidencia E-TR-009 |
| E2E | `npx playwright test tests/e2e/partes-excel-import.spec.ts` | **OK** — evidencia E-TR-009 |

## Cobertura verificada

- Plantilla `.xlsx` y capacidad deshabilitada (`4604`).
- Upload mixto: una fila válida y una inválida; procesamiento parcial.
- Alta real vía operación de tarea con `es_tarea = 1` y `cerrado = 0`.
- Denegación por permiso de menú (`4603`).
- Asistente no supervisor no puede importar para otro propietario.
- Toolbar embebida en Carga diaria; sin ruta ni menú de importación independiente.
- Refresco posterior solo cuando el procesamiento termina con altas; `queued`/`failed` no se consideran éxito.
- Exclusión de native y cliente en el host.

## Pendiente operativo

La validación contra SQL Server, DX Reporting real y SMTP requiere el entorno de despliegue correspondiente. No bloquea el alcance verificable del MVP local; la capacidad debe activarse mediante el parámetro `ExcelImportEnabled`.
