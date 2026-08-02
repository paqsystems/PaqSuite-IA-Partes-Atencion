# Verificación F1 + F — Control de Calidad PQ #1 (31/07/2026)

| Campo | Valor |
|-------|--------|
| **Control** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) · CC #1 |
| **Fecha control** | 31/07/2026 |
| **Fecha verificación** | 01/08/2026 |
| **Alcance** | `es_tarea` + filtros carga/masivo/informes/dashboard + Paquete de Horas (cuenta corriente) |
| **Prueba manual** | Aceptada por PQ (01/08/2026) |
| **Parte I (unificación)** | Ejecutada 2026-08-01: los TR/HU/SPEC-update listados abajo fueron fusionados en sus originales bajo `100-SistemaPartes` y eliminados; enlaces a `*-update*.md` quedan solo como referencia histórica de este documento. |

## TRs / HUs / SPECs cubiertos (unificados en base)

| Original unificado | Alcance |
|--------------------|---------|
| [TR-001](../../100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [HU-001](../../../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [SPEC-001](../../../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) | Columna `es_tarea` |
| [TR-004](../../100-SistemaPartes/TR-004-operacion-carga-diaria.md) · [HU-004](../../../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [SPEC-004](../../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) | Carga diaria fuerza/filtra |
| [TR-005](../../100-SistemaPartes/TR-005-supervision-proceso-masivo.md) · [HU-005](../../../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [SPEC-005](../../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) | Masivo excluye compras |
| [TR-006](../../100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) · [HU-006](../../../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [SPEC-006](../../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) | Informes/dashboard + Paquete de Horas |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado**

## Evidencia revisada

- Migración `2026_08_01_100000_add_es_tarea_to_pq_partes_registro_tarea.php`
- BE: `PartesTareaOperations` (list/upsert/`es_tarea=1`; masivo rechaza no-tarea); `PartesInformeOperations` (filtro informes/dashboard; paquete sin filtro + saldo inicial/running)
- FE: `PaqueteHorasPage` (Saldo en grilla; pivot sin Saldo); filtros i18n
- Docs producto / OpenSpec updates del CC #1
- Prueba manual PQ OK

## Hallazgos críticos

- Ninguno

## Advertencias

- Alta de compras (`es_tarea=false`) sigue fuera de alcance (proceso a definir).
- Runtime SP vía Query Builder (patrón MONO heredado; scripts T-SQL gateway = follow-up).
- OpenAPI de contratos nuevos no versionado en repo (mismo patrón TR-005).

## Sugerencias

- Parte **I** (unificar updates → bases) cuando se autorice.
- Smoke post-deploy: migrate `es_tarea` + paquete horas con cliente de prueba.

## Tests

- Comandos:
  - `php artisan test --filter="ApiV1PartesMaestrosTest|ApiV1PartesInformeTest|ApiV1PartesTareaTest|ApiV1PartesMasivoTest"`
  - `npx vitest run src/features/partes/informes/partesInformePivotFields.test.ts src/features/partes/carga/ src/features/partes/masivo/`
- Resultado: Feature **23 passed** (220 assertions); Vitest **14 passed** (3 files)

## Pendientes

- Unificación Parte I
- Proceso de alta compras (fuera de alcance)

## Recomendación final

- Cerrar updates CC #1 en **Finalizado**. Listo para commit cuando se autorice.

---

# F — openspec-05 (vs SPEC / HU / TR)

## Resumen ejecutivo

Implementación coherente con SPEC/HU/TR-update del CC #1. Completitud ✓ · Corrección ✓ · Coherencia ✓.

## Completitud

| Ítem CC | Estado |
|---------|--------|
| Columna EsTarea default true | ✓ |
| Carga/masivo: list solo true; upsert fuerza true | ✓ |
| Informes detallada/agrupada + dashboard filtran true | ✓ |
| Paquete de Horas: sin filtro EsTarea; saldo inicial; Saldo running; pivot sin Saldo | ✓ |

## Corrección

- Signo saldo: tarea +, compra − (`signedMinutos`).
- Masivo: 422 `partes.masivo.noEsTarea` si el lote incluye compra.
- Feature `test_es_tarea_filtro_informes_y_paquete_cuenta_corriente` y `test_list_excluye_compras_y_upsert_fuerza_es_tarea` OK.

## Coherencia

- API camelCase `esTarea` / `esSaldoInicial` / `saldoInicial`.
- Docs producto alineados al comportamiento verificado manualmente.

## Próximos pasos

1. Metadatos updates → **Finalizado** (este cierre).
2. Commit/push cuando se autorice.
3. Parte **I** unificación cuando se pida.
