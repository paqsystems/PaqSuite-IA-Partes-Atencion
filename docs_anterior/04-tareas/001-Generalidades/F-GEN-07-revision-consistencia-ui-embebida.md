# F-GEN-07 — Revisión consistencia UI embebida vs SPEC / HU / TR

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-06-16 |
| **Patrón** | [`patron-componente-excel-embebido.md`](../../00-contexto/_mono/importar-excel/patron-componente-excel-embebido.md) |
| **Veredicto** | **Apto con ajustes documentales e implementación D2** |

## Resumen

El motor backend GEN-07 (D1) es **reutilizable** sin cambios de esquema. Las HU/TR originales describían flujo **pantalla dedicada** (upload → redirect → grilla full-page con todas las filas → Procesar manual). El patrón embebido redefine la **capa de presentación** y el **contrato hacia el host** (`onComplete`), no las reglas de staging ni `PermiteProcesamientoParcial`.

Se autoriza **D2**: TR-GEN-07-ui-embebida-host + refactor FE; rutas `/excel-import/*` se mantienen para historial y modo legado/diagnóstico.

---

## Matriz de consistencia

| Tema | SPEC / HU / TR (D1) | Patrón embebido | Resolución |
|------|---------------------|-----------------|------------|
| Entrada UI | Pantalla proceso + redirect grilla | Toolbar host + modal | **D2** TR ui-embebida; rutas legado conservadas |
| Grilla tras carga | Todas las filas | Solo filas con error; sin grilla si cero errores | **D2** modal; historial readOnly sigue grilla completa (TR historial RN-06) |
| Procesar | Usuario confirma en grilla | Automático según matriz; host recibe `validRows` | **D2** orquestación en `ExcelImportHostModal` |
| Salida al negocio | Implícita vía handler en POST procesar | `onComplete({ validRows, meta })` explícito | **D2** + GET `filas/validas` post-proceso |
| Export errores | No en TR grilla (export deshabilitado D1) | `.xlsx` solo errores, nombre `{original}_errores_YYYYMMDDhhmmss` | **D2** endpoint `export-errores` |
| Nombre plantilla | HU/TR: `{Codigo}_plantilla_{fecha}` | `{codigoProceso}_plantilla.xlsx` fijo | **Alinear** HU-07-plantilla AC, TR plantilla AC-10, `ExcelTemplateService` |
| Paginación grilla | TR: `soloConError` query; server-side | Igual en modal errores | **Ya en API**; FE modal usa CustomStore |
| Error estructural | TR carga AC-03; sin grilla | Mensaje modal + Cerrar/Reintentar | **Consistente** con RN grilla RN-05 |
| Parcial / bloqueo | HU grilla RN-01–04 | Igual; vacío `validRows` si bloqueado | **Consistente** |
| Historial | Transversal; lote siempre persistido | Cierre modal con errores → lote en historial | **Consistente** |
| `PermiteSoloValidar` | Ocultar Procesar (TR grilla RN-06) | `validRows` = filas sin error de validación; sin POST procesar | **D2** rama en orquestador |
| Procesos negocio (pedidos) | Fuera epic GEN-07 | SPEC proceso invoca componente | **Fuera alcance** epic |

---

## Gaps cerrados en esta revisión

1. Nueva **HU-GEN-07-ui-embebida-host** y **TR-GEN-07-ui-embebida-host** (orden D2, depende D1).
2. Actualización flujo e2e en **SPEC-001-07** (modo embebido canónico; rutas legado como alternativa).
3. Ajuste nombre plantilla en HU/TR plantilla.
4. TR grilla: nota modo **full-page legado** vs **modal solo errores** (misma API).

---

## Pendiente post-D2 (no bloqueante)

| ID | Tema |
|----|------|
| P-01 | Matriz permisos MVP documentada |
| P-02 | OpenAPI completo epic |
| P-03 | Paginación server-side en `ExcelStagingGridPage` legado |
| P-04 | Export historial (Should) |
| P-05 | Integración host real (pedidos) — SPEC proceso aparte |

---

## Orden desarrollo

```text
D1 (hecho) → D2 ui-embebida-host → E revisión usuario → F cierre formal
```






