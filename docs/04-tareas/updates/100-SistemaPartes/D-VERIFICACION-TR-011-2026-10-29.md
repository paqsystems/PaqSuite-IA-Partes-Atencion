# Verificación F1 + F — TR-011 Reportes / emisiones (GEN-15)

| Campo | Valor |
|-------|--------|
| **TR / HU / SPEC** | [TR-011](../../100-SistemaPartes/TR-011-reportes-emisiones.md) · [HU-011](../../../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) · [SPEC-011](../../../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md) |
| **Producto** | [15-reportes-emisiones](../../../02-producto/Sistema-Partes-IA/15-reportes-emisiones.md) |
| **Manual** | [SPEC-010-emisiones-informes](../../../99-manual-usuario/SPEC-010-emisiones-informes.md) |
| **Jira** | PIAPARTES-11 (Epic) · PIAPARTES-25 (HU) · PIAPARTES-39 (TR) |
| **Fecha** | 2026-10-29 |
| **Cierre** | El usuario dio por finalizado el GEN en el chat |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado con observaciones**

Las observaciones quedaron escritas en producto, SPEC, HU, TR y manual. No quedan como defecto abierto del GEN.

## Evidencia revisada

| Pieza | Evidencia |
|-------|-----------|
| PDF en blanco en DEV desplegado | El sitio Forge DEV tenía el config cacheado en `PAQSUITE_EMISSION_DX_MODE=fake`. Ese modo usa `MinimalDxReportingEngine`: una página sin filas ni diseño. |
| Emisión real | Tras pasar DEV a `remote` (`http://127.0.0.1:5055/api/v1/dx-reporting`) y recargar PHP-FPM, el job `fb47f29d-d144-4404-a77d-fbf012b59464` (reporte id 2, código `Consulta Partes`) quedó `done`, canal PDF, **128 filas**. Artefacto `partes-informes-consultaDetallada-fb47f29d.pdf`, productor DevExpress. |
| Diseño feo (medidor en cada fila) | El REPX de ese reporte tenía un `XRGauge` en el detalle, sin vínculo a un campo. Se repetía en cada fila (33 páginas). Se quitó del archivo del sidecar `Consulta-Partes.repx` y se copió el mismo XML a `layout_definition` del reporte id 2. Un render de prueba del mismo código salió sin imagen JPEG del medidor. |
| Qué reporte se puede emitir | `SpEmissionRepository::reportsForEmissionDialog` e `isEmitReportAllowed` exigen `is_standard = 0`. La plantilla semilla no entra en Emitir. |
| Duración | `PartesConsultaDetalladaEmissionPort` entrega `duracionMinutos` (entero). El diseño en uso enlaza `[duracionMinutos]`. No hay formato `hh:mm` en el puerto. |
| Sidecar vs SQL | El binario desplegado usa el archivo `data/layouts/{código}.repx` (espacios → guiones) cuando el id del render coincide con ese código. PHP igual manda `layoutDefinition` si el catálogo tiene REPX. |

## AC relevantes

| AC | Verificación | Estado |
|----|--------------|--------|
| CA-04 | 0 filas → Emitir visible y deshabilitado (TR y manual; no se reemitió un universo vacío en esta corrida) | OK por código y doc |
| CA-07 | Job de 128 filas del universo filtrado; duración en minutos | OK con observación de formato (documentada) |
| CA-15 | Diseñador de escritorio; el diseño guardado es el que emitió el job | OK |
| R-EM-24 | Emitir no ofrece la plantilla estándar; el PDF siguió el REPX guardado de `Consulta Partes` | OK |

## Hallazgos críticos

- Ninguno abierto. El PDF en blanco y el medidor repetido se corrigieron en el entorno DEV (variables Forge + archivo de layout + fila SQL). No hubo commit de código en este cierre.

## Advertencias

1. **Tests de esta corrida:** no se reejecutó PHPUnit, Vitest ni Playwright el 2026-10-29. La evidencia de suite sigue siendo la Parte E del 2026-10-16 (`E-TR-011-reportes-emisiones-2026-10-16.md`). Esos tests cubren contrato y humo; no renderizan un PDF DevExpress real (`Http::fake`).
2. **Mail SMTP:** no se verificó un envío real.
3. **Hay que volver a emitir** para ver el PDF sin el medidor. El archivo `fb47f29d` queda como estaba (33 páginas).
4. **`fake` en un Forge nuevo** vuelve a dar PDF en blanco. El default de `.env.example` sigue en `fake` a propósito (local/CI). DEV y el sitio productivo que ya emitía quedaron en `remote`.

## Tests

| Comando | Resultado |
|---------|-----------|
| Suite Parte E (2026-10-16) | Documentada en `E-TR-011-reportes-emisiones-2026-10-16.md` |
| Esta verificación (2026-10-29) | No se relanzó la suite. Evidencia = job DEV, render del sidecar sin medidor y lectura del código de puerto/catálogo |

## Pendientes

- Parte **I** en esta misma fecha: documentos base en **Finalizado**. No había archivos `*-update` que fusionar.

## Recomendación final

- GEN-15 de Partes (diseño y emisión en Consulta detallada) **cerrado**. SPEC-011, HU-011 y TR-011 en **Finalizado**.

---

# F — Verificación documental (SPEC ↔ HU ↔ TR ↔ código)

## Resumen ejecutivo

El código emite el diseño guardado de un reporte no estándar, con el universo filtrado de Consulta detallada y la duración en minutos. La documentación decía `hh:mm` automático y, en notas de Parte D, un stub DX fijo. Eso se alineó el 2026-10-29.

Completitud ✓ · Corrección ✓ (tras el alineado) · Coherencia ✓.

## Completitud

| Criterio | Estado |
|----------|--------|
| Emitir en Consulta detallada (web), no en mobile | ✓ |
| Dataset = filtros de pantalla, todas las filas, no la página ni el pivot | ✓ (job 128 filas; puerto `p_page_size=0`) |
| Diseñador en Soporte Técnico, permiso distinto de emitir | ✓ |
| Canales PDF / impresión / Excel / CSV / mail; sin ZIP ni segmentado | ✓ en seed y SPEC (mail SMTP no reprobado hoy) |
| Plantilla estándar fuera de Emitir; el artefacto sigue el diseño guardado | ✓ código + job `Consulta Partes` |
| Manual de usuario | ✓ actualizado 2026-10-29 |

## Corrección

- R-EM-05 ya no promete `hh:mm`. El puerto manda minutos; el diseño decide el texto.
- R-EM-24 describe el combo (`is_standard = 0`) y que un control suelto en el detalle se repite por fila.
- El plan de sidecar ya no dice que Partes registra `MinimalDxReportingEngine` fijo. La fábrica elige `remote` o `fake`.
- `.env.example` deja `fake` para local/CI y avisa que un entorno real tiene que usar `remote`.

## Coherencia

| Documento | Alineado | Nota |
|-----------|----------|------|
| SPEC-011 / HU-011 / TR-011 | Sí | **Finalizado** 2026-10-29 |
| `15-reportes-emisiones.md` | Sí | D-EM-11…13 |
| Manual `SPEC-010-emisiones-informes.md` | Sí | Sin rutas de servidor ni variables |
| Plan sidecar §4 | Sí | Fábrica + archivo `.repx` del sidecar |
| D1-TR-011 | Histórico | Banner al cierre; el cuerpo sigue siendo el plan del 2026-08-25 |
| Handoff lab 2026-10-28 | Sí | Banner de cierre; el cuerpo queda como bitácora de ese día |

## Próximos pasos

1. Estado **Finalizado** en SPEC, HU y TR (pedido explícito del usuario, 2026-10-29).
2. Jira: comentario F1/F y transición a **Finalizado** de PIAPARTES-11, PIAPARTES-25 y PIAPARTES-39.
3. Commit y push solo si se piden. Este cierre no agrega migraciones ni seeds.
