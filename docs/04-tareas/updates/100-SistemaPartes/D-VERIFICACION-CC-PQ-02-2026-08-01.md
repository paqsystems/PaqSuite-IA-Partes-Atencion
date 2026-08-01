# Verificación F1 + F — Control de Calidad PQ #2 (01/08/2026)

| Campo | Valor |
|-------|--------|
| **Control** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) · CC #2 |
| **Fecha control** | 01/08/2026 |
| **Fecha verificación** | 01/08/2026 |
| **Alcance** | `erp_cliente` / `erp_articulo` en maestro clientes + informes detallada/agrupada (grilla + pivot) |
| **Prueba manual** | Aceptada por PQ (01/08/2026) |
| **Parte I (unificación)** | Ejecutada 2026-08-01: los TR/HU/SPEC-update listados abajo fueron fusionados en sus originales bajo `100-SistemaPartes` y eliminados; enlaces a `*-update*.md` quedan solo como referencia histórica de este documento. |

## TRs / HUs / SPECs cubiertos (unificados en base)

| Original unificado | Alcance |
|--------------------|---------|
| [TR-001](../../100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [HU-001](../../../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [SPEC-001](../../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) | DDL ERP |
| [TR-003](../../100-SistemaPartes/TR-003-maestros-y-catalogos.md) · [HU-003](../../../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [SPEC-003](../../../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) | ABM clientes |
| [TR-006](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) · [HU-006](../../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [SPEC-006](../../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) | Informes |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado**

## Evidencia revisada

- Migración `2026_08_01_100100_add_erp_fields_to_pq_partes_clientes.php` (aplicada en local)
- BE: `PartesMaestrosOperations` (list/upsert + `normalizeErpField` max 15); `PartesTareaOperations::mapRow`; `PartesInformeOperations` detalle/agrupado/paquete
- FE: maestro clientes columnas/form (`maxLength: 15`); `PartesConsultasPages` + `partesInformePivotFields` + i18n es/en/fr/pt/it; Paquete de Horas alineado
- Prueba manual PQ OK

## Hallazgos críticos

- Ninguno

## Advertencias

- Dashboard no incluye columnas ERP (fuera de alcance del ítem CC).
- Paquete de Horas no era ítem del CC #2; se alineó por coherencia con detallada.

## Sugerencias

- Parte **I** unificación cuando se autorice.
- Deploy: `php artisan migrate --path=database/migrations/2026_08_01_100100_add_erp_fields_to_pq_partes_clientes.php --force`

## Tests

- Comandos:
  - `php artisan test --filter="ApiV1PartesMaestrosTest|ApiV1PartesInformeTest|ApiV1PartesTareaTest|ApiV1PartesMasivoTest"`
  - `npx vitest run src/features/partes/informes/partesInformePivotFields.test.ts src/features/partes/carga/ src/features/partes/masivo/`
- Resultado: Feature **23 passed**; Vitest **14 passed**; casos ERP (`test_cliente_erp_campos_crud_y_validacion`, detalle/agrupado con ERP) OK

## Pendientes

- Unificación Parte I

## Recomendación final

- Cerrar updates CC #2 en **Finalizado**. Listo para commit cuando se autorice.

---

# F — openspec-05 (vs SPEC / HU / TR)

## Resumen ejecutivo

Implementación coherente con SPEC/HU/TR-update del CC #2. Completitud ✓ · Corrección ✓ · Coherencia ✓.

## Completitud

| Ítem CC | Estado |
|---------|--------|
| DDL `erp_cliente` / `erp_articulo` nvarchar(15) NULL | ✓ |
| ABM clientes API + UI | ✓ |
| Validación >15 → 422 | ✓ |
| Consulta detallada grilla + pivot | ✓ |
| Consulta agrupada grilla + pivot | ✓ |

## Corrección

- API camelCase `erpCliente` / `erpArticulo`.
- Agrupado: campos presentes en eje cliente; vacíos en otros ejes (aceptable).

## Coherencia

- Docs producto (`04-maestros`, `06-consultas`, `09-modelo`) alineados.
- i18n de captions de informe en 5 locales.

## Próximos pasos

1. Metadatos updates → **Finalizado** (este cierre).
2. Commit/push cuando se autorice.
3. Parte **I** unificación cuando se pida.
