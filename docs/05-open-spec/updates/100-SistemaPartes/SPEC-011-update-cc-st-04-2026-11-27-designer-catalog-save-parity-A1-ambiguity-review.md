# Revisión de ambigüedad — SPEC-011-update CC ST #4 (catálogo vs Save DX)

**SPEC:** [SPEC-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md](./SPEC-011-update-cc-st-04-2026-11-27-designer-catalog-save-parity.md)  
**Fecha:** 27/11/2026 · Parte **A1**

## Resultado general

- **Estado: Apto con observaciones** — Se puede **refinar TR (C/D1)** y ejecutar implementación si se **cierran dos decisiones** (§ Preguntas). La HU-update existente (Parte G) puede mantenerse con un párrafo de supuestos.

## Hallazgo técnico (acota la ambigüedad)

Hoy `GET …/design/report-storage-urls` ya devuelve **todos** los `report_code` de `reportsForDesignCatalog` (misma fuente que el SelectBox). El diálogo Save de DX lista **intersección** `*.repx` en disco ∩ esas URLs (`FileReportStorage.GetUrls` + `FilterUrlsForProcess`).  
Por tanto el gap CC #4 **no** es “API de urls incompleta” sino **falta de `.repx` en sidecar** (y/o duplicados de clave). La solución debe materializar disco o cambiar semántica de `GetUrls` — no solo “otro filtro” en Laravel.

## Ambigüedades críticas

| ID | Tema | Riesgo de divergencia |
|----|------|------------------------|
| **A1-C1** | **CA-ST4-01 vs hydrate puntual** | R-ST4-02 solo exige hydrate **al cambiar variante**. Con eso, Save seguiría mostrando solo variantes ya hidratadas + las que tengan `.repx`. Un dev implementa hydrate lazy; otro espera K ítems en Save sin abrir cada variante. |
| **A1-C2** | **`is_standard = 1` (R-ST4-02)** | Fuera de alcance “cambiar semántica”, pero CA-ST4-02 habla de `layout_definition` en BD. Informes estándar pueden tener layout **solo** en sidecar. Hydrate desde BD sería no-op o error; comportamiento no definido. |

## Ambigüedades menores

| ID | Tema | Nota |
|----|------|------|
| A1-M1 | Definición de **K** en CA-ST4-01 | ¿Incluye `<Original>` / estándar? Catálogo diseño incluye `is_standard=1`; Emitir excluye estándar. Save debería alinearse al **catálogo diseño**, no al de Emitir — conviene una frase en SPEC. |
| A1-M2 | **Dos claves** (`partes.consultaDetallada.principal` vs `partes_consultaDetallada_principal`) | ¿K único o 2 hasta migración? Normalizer ya tiene `ComparisonKey`; CA de cardinalidad debe usar **claves canónicas únicas**. |
| A1-M3 | **Captions** en diálogo Save | CA exige cardinalidad, no texto visible; DX suele mostrar `id` de archivo. Aceptable si se documenta en F1. |
| A1-M4 | **Save vs Save As** | CA menciona ambos; D-EM-13 ya acota Save As por proceso; Save (sobrescribir) es el foco del CC. |
| A1-M5 | **Segundo proceso** (`paqueteHoras`) | R-ST4-01 es genérico; CAs solo nombran Consulta detallada. TR puede exigir smoke mínimo en un segundo proceso o dejar explícito “piloto Consulta detallada + regresión paqueteHoras API”. |
| A1-M6 | **Errores** hydrate | Sidecar caído, XML inválido, token sidecar ausente: sin envelope/mensaje UX en SPEC (solo “error explícito”). TR debe citar códigos envelope existentes 470x. |
| A1-M7 | **Conflicto** XML disco más nuevo que BD | No define autoridad si `.repx` existe pero difiere de BD al abrir variante. Supuesto habitual: BD gana al hydrate **solo si** se dispara hydrate; si archivo existe, no pisar — debe quedar en TR. |

## Supuestos detectados

- Permiso `emission.design` y token sidecar en lab/Forge operativos (ya TR-011).
- No se cambia contrato GEN-15 `ReportSaved` → PUT/POST layout.
- CC ST #3 (schema) es independiente; no bloquea hydrate.
- Smart Capture / apply muta disco; hydrate no debe borrar backups `.sc-backup` del sidecar.

## Preguntas para decisión humana

1. **D-ST4-A1-01 (crítica):** **Cerrada 27/11/2026 — opción A** (hydrate batch al confirmar proceso; sin cambio `GetUrls` en v1). Ver SPEC-update §2.1.
2. **D-ST4-A1-02 (crítica):** Para **`is_standard = 1`**, ¿hydrate desde plantilla/`.repx` seed si BD vacía, y **excluir** del batch BD-vacía o **incluir** en K con reglas propias? — **Pendiente** (provisional R-ST4-06 en SPEC).

## Recomendaciones de ajuste del SPEC (sin inventar alcance nuevo)

1. Añadir subsección **«Decisión hydrate (D-ST4-A1-01)»** con la opción elegida (recomendación **A**).
2. Aclarar en CA-ST4-01: **K** = `count(reportsForDesignCatalog)` para el proceso, claves **deduplicadas** por normalizador canónico.
3. Añadir **CA-ST4-06** (opcional): tras confirmar proceso, sin abrir cada variante, Save lista K destinos (prueba de batch).
4. En R-ST4-02, excepción explícita **is_standard** alineada a [persistencia-layouts](../../../06-operacion/persistencia-layouts-emisiones-dx.md).
5. TR-011-update §T2: documentar regla **M7** (no sobrescribir `.repx` salvo hydrate forzado o Descartar).

## Checklist 10 ejes (resumen)

| Eje | OK / Obs |
|-----|----------|
| 1 Alcance | OK — fuera CC #3, sin migración masiva naming |
| 2 Actores | OK — `emission.design` |
| 3 Flujo | **Obs** — falta decisión batch vs lazy |
| 4 Reglas | **Obs** — R-ST4-02 vs is_standard |
| 5 Datos | OK — BD + sidecar documentados |
| 6 UI | **Obs** — captions Save opcionales |
| 7 APIs | OK — rutas listadas en mapa |
| 8 Casos especiales | **Obs** — M6, M7 |
| 9 CA | **Obs** — CA-ST4-01 depende D-ST4-A1-01 |
| 10 Trazabilidad | OK — CC #4, PIAPARTES-82 |

## Veredicto

- **Puede pasar a HU/TR (refinamiento C / D1):** **Sí**, con observaciones.
- **Puede iniciar D sin cerrar D-ST4-A1-01/02:** **Sí** con **D-ST4-A1-01 cerrada**; **D-ST4-A1-02** pendiente con regla provisional R-ST4-06.