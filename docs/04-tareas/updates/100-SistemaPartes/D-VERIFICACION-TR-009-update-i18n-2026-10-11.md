# Verificación F1 + F — TR-009-update i18n importación Excel

| Campo | Valor |
|-------|--------|
| **Alcance** | i18n GEN-14 / `partes.import.*` en Carga diaria |
| **Fecha verificación** | 11/10/2026 |
| **Parte I (unificación)** | Ejecutada 11/10/2026: delta fusionado en SPEC/HU/TR-009; los `*-update` i18n eliminados. Originales en **Finalizado**. |
| **Origen** | Colateral de calidad (modal Importar Excel, 11/10/2026); no es ítem de CC-PQ #6. |

## Artefactos cubiertos (tras Parte I)

| Original | Delta unificado |
|----------|-----------------|
| [SPEC-009](../../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md) · [HU-009](../../../03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md) · [TR-009](../../100-SistemaPartes/TR-009-importacion-partes-excel.md) | Caption/hoja `excelImport.sheet.*`; `partes.import.*` en 5 locales; `t` i18next en `ExcelImportToolbar` |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado.** El humano confirmó el punto en UI (11/10/2026). Revisión de código + Vitest alineados a CA-I18N-01…05.

## Evidencia revisada

| AC | Evidencia | Estado |
|----|-----------|--------|
| CA-I18N-01 | `es/common.json`: `excelImport.sheet.label` = «Hoja». Smoke UI: selector no muestra la clave cruda | OK |
| CA-I18N-02 | `excelImport.sheet.placeholder` y `required` en es/en/pt/fr/it | OK |
| CA-I18N-03 | `partes.import.*` (incl. `tipoFueraUniverso`) en 5 locales; handler sigue emitiendo `partes.import.tipoFueraUniverso` | OK |
| CA-I18N-04 | Vitest: `i18n.t('excelImport.sheet.label')` ES=Hoja / EN=Sheet; `tipoFueraUniverso` EN ≠ ES ni la clave | OK |
| CA-I18N-05 | `CargaDiariaPage`: `t={(key, options) => t(key, options)}` — no `resolveAuthMessage` en el toolbar | OK |

Comando: `npx vitest run src/features/partes/carga/excelImportI18n.test.ts` — **2 passed**.

Manual: [SPEC-009-importacion-excel.md](../../../99-manual-usuario/SPEC-009-importacion-excel.md) ya documenta elegir **hoja** y textos según idioma de sesión.

---

# F — vs SPEC / HU / TR

## Resumen ejecutivo

El host completa el catálogo GEN de hoja y el de dominio `partes.import.*`. El toolbar traduce con i18next (`common`). El motor GEN-14 y el handler no se reimplementaron. Coincide con R-IMP-I18N-01…05.

## Completitud

| Ítem | Estado |
|------|--------|
| Caption hoja traducido (no clave cruda) | ✓ (UI + JSON + test) |
| `sheet.placeholder` / `sheet.required` en 5 locales | ✓ |
| `partes.import.*` en 5 locales; `{{tramo}}` conservado | ✓ |
| `t` del toolbar = i18n, no mapa solo-ES | ✓ |
| Handler sin cambio de `messageKey` | ✓ |
| Manual: paso elegir hoja | ✓ |
| Sin migrate/seed | ✓ |

## Cierre

- F1/F **Aprobado** 11/10/2026.
- Parte I ejecutada: originales [SPEC-009](../../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md) · [HU-009](../../../03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md) · [TR-009](../../100-SistemaPartes/TR-009-importacion-partes-excel.md).
