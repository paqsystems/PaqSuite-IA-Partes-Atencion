# HU-015-update-c – Apariencia de controles en diseño de informes (oleada 3)

## Metadatos

| Campo | Valor |
|-------|--------|
| ID | HU-015-update-c |
| Título | Pedir formato visual de controles en Smart Capture del diseñador |
| Épica | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (oleada 3) |
| Estado | **Finalizado** |
| SPEC | [SPEC-015-update oleada 3](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-3-apariencia-update.md) |
| TR | [TR-015-update-c](../../04-tareas/100-SistemaPartes/TR-015-update-c-apariencia-controles-informes.md) |
| HU previa | [HU-015-update](./HU-015-update-ia-diseno-informes-camino-b.md) |

---

## Narrativa

Como diseñador con permiso `emission.design`, quiero indicar en lenguaje natural el **formato** de un control del informe (por ejemplo el código de cliente), para ver el cambio en el lienzo sin dominar todas las propiedades DevExpress.

---

## Criterios de aceptación

1. **CA-01** — Prompt de estilo sobre campo de schema → apply visible en control que muestra ese campo (ej. `clienteCode`).
2. **CA-02** — Prompt de estilo sobre título del informe → `lblReportTitle` actualizado (negrita/subrayado/centrado).
3. **CA-03** — Sin credencial LLM, heurísticas cubren prompts canónicos CHK-27 y CHK-08-format.
4. **CA-04** — Tras apply, flujo Guardar/Descartar sin regresión (L-B-10).
5. **CA-05** — Campo inexistente no genera acción válida.

---

## Reglas de negocio

- Incremental: un pedido de estilo por turno preferido (L-B-04).
- Colores nombrados (“azul marino”) resueltos en backend Partes.

---

## Fuera de alcance

- Crear controles nuevos, pies, imágenes, campos calculados (CHK diferidos).