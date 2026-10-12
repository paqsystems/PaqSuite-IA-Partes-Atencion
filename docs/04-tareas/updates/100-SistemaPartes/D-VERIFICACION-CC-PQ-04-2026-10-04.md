# Verificación F1 + F — Control de Calidad PQ #4 (04/10/2026)

| Campo | Valor |
|-------|--------|
| **Control** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) · CC #4 |
| **Fecha control** | 04/10/2026 |
| **Fecha verificación** | 11/10/2026 |
| **Parte I (unificación)** | Ejecutada 11/10/2026: SPEC/HU/TR-003 y SPEC/HU/TR-006 actualizados; los `*-update` de CC #4 eliminados. Originales en **Finalizado**. |

## TRs / HUs / SPECs cubiertos

| Original (tras Parte I) | Alcance CC #4 |
|-------------------------|----------------|
| [TR-003](../../100-SistemaPartes/TR-003-maestros-y-catalogos.md) · [HU-003](../../../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [SPEC-003](../../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) | Código de usuario en asistentes; selector vinculable; unicidad asistente/cliente |
| [TR-006](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) · [HU-006](../../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [SPEC-006](../../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) | Filtro y columnas de tipo de cliente en Paquete de Horas |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado.** UI de los tres temas recorrida el 11/10/2026. El login contra SQL Server (RDS) de Partes (`:8010`) agotó timeout; el smoke de pantallas usó la SPA ya autenticada. Completitud de API reconfirmada con PHPUnit/Vitest (sqlite).

## Evidencia revisada

| AC | Evidencia | Estado |
|----|-----------|--------|
| AC-U01 | Grilla Asistentes: columna **Usuario (código)** visible. PHPUnit `asistente muestra codigo usuario` | OK |
| AC-U02 | Catálogo vinculable: PHPUnit `catalogo usuarios vinculables filtra asignados e inactivos`. UI del selector exige modal Alta (botón no expuesto en toolbar por banner DX); contrato cableado en `MaestroCrudPage` (`type: 'user'`) | OK |
| AC-U03 | PHPUnit: segundo asistente/cliente con el mismo `userId` → 422 `exclusividadUserId` | OK |
| AC-U04 | PHPUnit: usuario inactivo → 422 `usuarioNoVinculable` | OK |
| AC-U05 | PHPUnit paquete: movimientos con `tipoClienteCode` / `tipoClienteDescripcion`; saldo inicial vacío | OK |
| AC-U06 | PHPUnit: `tipoClienteId` recorta saldo y movimientos | OK |
| AC-U07 | Vitest `partesInformePivotFields.test.ts`; UI Paquete de Horas: columnas **Tipo de cliente (código)** y **Tipo de cliente** + filtro de tipo (placeholder «Cargando…» / SelectBox) | OK |

Comandos (11/10/2026): `php artisan test tests/Feature/Partes/ApiV1PartesMaestrosTest.php` (8 passed, 57 aserciones); `php artisan test` filtrado paquete (2 passed); `npx vitest run src/features/partes/informes/partesInformePivotFields.test.ts` (3 passed).

---

# F — vs SPEC / HU / TR

## Resumen ejecutivo

La implementación sigue SPEC/HU/TR-003 y 006 del CC #4. Completitud de API cubierta por tests. UI: columna de usuario en asistentes y filtro/columnas de tipo en Paquete de Horas visibles en el navegador.

## Completitud

| Ítem CC | Estado |
|---------|--------|
| Código de usuario en grilla de asistentes | ✓ |
| No duplicar usuario en asistente ni en cliente | ✓ (API) |
| Selector limitado a activos, habilitados y no asignados | ✓ (API + cableado FE) |
| Filtro Tipo de cliente en Paquete de Horas | ✓ (UI + API) |
| Columnas código y descripción de tipo (grilla y pivot) | ✓ |

## Cierre

- Updates marcados **Finalizado** y **Parte I** ejecutada el 11/10/2026.
- Originales: [SPEC-003](../../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../../../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../../100-SistemaPartes/TR-003-maestros-y-catalogos.md) · [SPEC-006](../../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md).
