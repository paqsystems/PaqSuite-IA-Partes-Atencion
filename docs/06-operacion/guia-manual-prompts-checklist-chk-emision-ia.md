# Guía manual — un prompt por ítem (CHK-01 … CHK-26 + LAB-21)

Batería **camino B**: Smart Capture en **Diseñador de emisiones** + **apply** sidecar (whitelist v1).  
Matriz SoT: Framework [`15-reportes-emisiones-ia-checklist-matriz.json`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json).

**Sesión DEV (prompts naturales, hallazgos):** [sesion-pruebas-dev-spec-015-chk-emision-ia-2026-11-27.md](./sesion-pruebas-dev-spec-015-chk-emision-ia-2026-11-27.md) — CHK-06 variantes + capturas Caso A/B; § normalidad de uso, brecha smoke vs DEV, **brief por zonas** (Consulta detallada).

### Cómo leer esta guía

| Modo | Uso |
|------|-----|
| **Columna «Prompt (pegar en SC)»** | Frases **canónicas** alineadas a la matriz Framework: regresión, comparar oleadas, scripts `eval-emission-ia-*-apply.ps1`. |
| **Uso normal en producto** | Prompts **naturales** o un **brief por bandas** (*Page Header → campos…*, *Detalle → …*). Ver ejemplo en [sesión DEV](./sesion-pruebas-dev-spec-015-chk-emision-ia-2026-11-27.md#especificación-por-zonas--ejemplo-consulta-detallada); **procedimiento** en [runbook prompts naturales](./runbook-pruebas-prompts-naturales-emision-ia.md). |
| **Criterio de éxito piloto** | Mismo layout útil que el encadenamiento CHK; sin regresiones de apply; gaps documentados (ítems sin whitelist). |

Los F1 cerrados con prompts canónicos **no sustituyen** prueba en DEV con lenguaje natural ni briefs multi-zona.

Registrar resultados en el **D-VERIFICACION / F** del TR u oleada que corresponda (no usar la tabla histórica de TR-015-update-b):

| Alcance | Cierre (F) / F1 |
|---------|-----------------|
| CHK-01, 02, 03, 08 texto | [F oleada4](../04-tareas/100-SistemaPartes/F-TR-015-update-d-oleada4-chk01-03-08.md) · [D oleada4](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-oleada4-chk01-03-2026-11-18.md) |
| CHK-08-format | [F oleada 3b](../04-tareas/100-SistemaPartes/F-TR-015-update-g-oleada3b-chk08-format.md) |
| CHK-27 + CHK-08-format (oleada 3) | [D-CIERRE oleada3](../04-tareas/updates/100-SistemaPartes/D-CIERRE-oleada3-apariencia-2026-11-18.md) · [D update-c](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-c-2026-11-18.md) · [smoke oleada3](./smoke-oleada3-apariencia-controles.md) |
| CHK-04…07 | [F oleada5](../04-tareas/100-SistemaPartes/F-TR-015-update-e-oleada5-page-header.md) |
| CHK-09, 10 | [F oleada9](../04-tareas/100-SistemaPartes/F-TR-015-update-k-oleada9-chk09-10.md) |
| CHK-11…14 | [F oleada10](../04-tareas/100-SistemaPartes/F-TR-015-update-l-oleada10-chk11-14.md) |
| CHK-18 | [F oleada 6](../04-tareas/100-SistemaPartes/F-TR-015-update-f-oleada6-chk18.md) |
| CHK-19 | [F oleada 7](../04-tareas/100-SistemaPartes/F-TR-015-update-h-oleada7-chk19.md) |
| CHK-20 | [F oleada 7b](../04-tareas/100-SistemaPartes/F-TR-015-update-i-oleada7b-chk20.md) |
| CHK-21 + LAB-21 | [F oleada 8](../04-tareas/100-SistemaPartes/F-TR-015-update-j-oleada8-chk21.md) |
| **CHK-15..17** | [F oleada 11](../04-tareas/100-SistemaPartes/F-TR-015-update-m-oleada11-chk15-17.md) · [prompts SDD](./prompts-nuevo-hilo-oleada-11-chk15-17-sdd.md) · [SPEC oleada 11](../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md) |
| **CHK-28** | [F oleada 12](../04-tareas/100-SistemaPartes/F-TR-015-update-n-oleada12-chk28.md) · [D F1](../04-tareas/100-SistemaPartes/D-VERIFICACION-TR-015-update-n-chk28-2026-11-26.md) · [prompts oleada 12](./prompts-nuevo-hilo-oleada-12-chk28-sdd.md) |
| **CHK-26** | **Diferido** — lab script TR-j; sin F |
| Registro libre / huecos | Tabla viva [D-VERIFICACION-TR-015-update-b](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-b-2026-11-13.md) (§ histórico 2026-11-13, no SoT de pendientes) |

---

## Antes de cada ítem

| Paso | Detalle |
|------|---------|
| URL lab | `http://127.0.0.1:3102/emisiones/disenador?cliente=DEV` (o tu puerto Vite; **no** uses `PQ` como `cliente`) |
| Login | Usuario de lab con `emission.design` (ej. **PQ**) |
| Sidecar | `GET http://127.0.0.1:5055/health` → `status: ok` |
| BYOK SC | En el panel Smart Capture → **preferences** → credencial **habilitada** (sin esto el turno pide configuración y no apply) |
| Proceso | **Consulta detallada** → **Confirmar proceso** |
| Variante sugerida | **Spike B titulo** (o principal; anotá cuál usaste) |
| Tras apply exitoso | Banner **Guardar / Descartar** (L-B-10): probá **Descartar** para el siguiente ítem, o **Guardar** si querés conservar |

**Cómo pegar el prompt:** abrí Smart Capture, copiá el texto de la columna **Prompt (pegar en SC)** y **Enviar**. Confirmá apply si el asistente lo ofrece.

**Ítems `diferido` / `futuro_producto`:** éxito = **no** mutar el REPX (sin apply o mensaje claro de límite); anotá el texto de la respuesta.

**Ítems `mvp_parcial` en la tabla:** viene de la **matriz Framework** (alcance del checklist histórico ≠ 100 % paridad producto). **No** indica oleada pendiente: si hay fila en la tabla **Cierre (F)** arriba, el ítem ya tiene **F Finalizado** y smoke documentado.

---

## Tabla de prompts y criterio

Columna **Estado (matriz):** `mvp` | `mvp_parcial` | `diferido` | `futuro_producto`. Para re-smoke o auditoría, mirá la última columna (**Cierre F / TR**) además del estado.

| ID | Estado (matriz) | Prompt (pegar en SC) | Resultado esperado (manual) | Cierre F / TR |
|----|--------|----------------------|-----------------------------|------------------------|
| CHK-01 | **mvp** | `Agregá la columna observación en el detalle` | Apply → label en detalle con `[observacion]`; banner Guardar/Descartar | Cierre [F oleada4](../04-tareas/100-SistemaPartes/F-TR-015-update-d-oleada4-chk01-03-08.md) |
| CHK-02 | **mvp** | `Agregá un texto fijo que diga Borrador en el detalle` | Apply → label literal «Borrador» en detalle | Idem oleada4 |
| CHK-03 | mvp_parcial | `Cambiá el título a Consulta detallada` | Apply → `lblReportTitle` + bandas **Page Header/Footer** (vacías); Guardar/Descartar; Preview | Idem oleada4 |
| CHK-04 | **mvp** | `Poné un título encima de cada columna en el encabezado de página` | Variante **con columnas en detalle** (ej. spike-layout-b-1) → `lblPageHdr_*` fila 3; sin detalle → mensaje SC sin romper lienzo | Planner + apply + [smoke CHK-04](./smoke-oleada5-page-header-chk04.md) |
| CHK-05 | **mvp** | `Encerrá el título en un panel` | Apply → `pnlReportTitle` fila 2; **re-aplicar** con panel ya armado → mensaje *«El título del informe ya está encerrado…»* (`noop:wrapReportTitleInPanel:ya_colocados`); sin error HTTP | Planner + apply |
| CHK-06 | **mvp** | `Poné el nombre de la empresa arriba a la izquierda` | Lab (`local` / tenant `DEV`): apply → `lblPageHdr_Empresa` fila 1 izq.; **re-aplicar** mismo texto → mensaje *«…ya muestra el nombre de empresa…»* (`noop:setPageHeaderEmpresa:ya_colocados`) | Planner + apply (oleada 5) |
| CHK-07 | **mvp** | `Agregá la fecha con el mes en letras arriba a la derecha` | Apply → `pageInfoFecha` fila 1 derecha; **re-aplicar** si `pageInfoFecha` ya existe → mensaje *«…ya tiene la fecha de impresión…»* (`noop:setPageHeaderFecha:ya_colocados`); convive con CHK-06 | Planner + apply |
| CHK-08 | mvp | `Cambiá el título a CONSULTA DETALLADA` | Apply con texto **CONSULTA DETALLADA** (literal) | Planner + apply · cierre [F oleada4](../04-tareas/100-SistemaPartes/F-TR-015-update-d-oleada4-chk01-03-08.md) |
| CHK-08-format | **mvp** | `Poné el título en negrita, subrayado y centrado` | Apply → `lblReportTitle` con negrita/subrayado/centrado (page header o report header); **re-aplicar** → *«El título… ya estaba…»* (`noop:setControlAppearance:ya_formateado`); script `eval-emission-ia-chk08-format-apply.ps1` | Planner + apply oleada 3b |
| CHK-27 | **mvp** | `Al código de cliente asignale Times New Roman 12, azul marino, negrita, subrayado e italica, centrado` | Apply sobre control con `[clienteCode]` en detalle; variante con columna presente | Planner + apply · **cerrado** oleada 3 ([D-CIERRE](../04-tareas/updates/100-SistemaPartes/D-CIERRE-oleada3-apariencia-2026-11-18.md)) |
| CHK-09 | **mvp** | `Agregá un pie de informe` | Apply → `ReportFooterBand` (no PageFooter); script oleada 9 | [TR-k](../04-tareas/100-SistemaPartes/TR-015-update-k-report-footer-chk09-10.md) |
| CHK-10 | **mvp** | `Totalizá todos los registros en el pie del informe` | Batch ensureReportFooter + count; «Total registros» | oleada 9 |
| CHK-11 | **mvp** | `Creá un campo calculado que sume duracionMinutos` | `addCalculatedField` sum (solo numéricos) | [TR-l](../04-tareas/100-SistemaPartes/TR-015-update-l-calculados-chk11-14.md) |
| CHK-12 | **mvp** | `Campo calculado IIf entre clienteNombre y observacion` | `calc_textoCliente` | oleada 10 |
| CHK-13 | **mvp** | `Mostrá un campo calculado en el detalle` | Tras CHK-12; label en Detail | oleada 10 |
| CHK-14 | **mvp** | `Totalizá el campo calculado en el pie del informe` | Tras CHK-11; suma calc en ReportFooter | oleada 10 |
| CHK-15 | **mvp** | `Agregá una imagen de fondo al informe` | Apply → watermark (`setReportWatermarkImage`); re-aplicar → `noop:ya_existe`; Guardar/Descartar | **F cerrado** [oleada 11](../04-tareas/100-SistemaPartes/F-TR-015-update-m-oleada11-chk15-17.md) · `eval-emission-ia-oleada11-chk15-17-apply.ps1` |
| CHK-16 | **mvp** | `Insertá el logo de la empresa` | Apply → `imgPageHdr_Logo` (`setPageHeaderLogo`); smoke 16→15→17 | Idem F oleada 11 |
| CHK-17 | **mvp** | `Mostrá clienteCode como código QR` (detalle default) | `addDetailBarCode`; variantes: `… en el encabezado de página` (`pageHeader`), pie informe/página, etc. | Idem F oleada 11 |
| CHK-18 | **mvp** | `Agrupá por cliente` | Group Header/Footer **clienteNombre**; re-aplicar → `noop:ya_agrupado`; Guardar/Descartar | **F cerrado** [oleada 6](../04-tareas/100-SistemaPartes/F-TR-015-update-f-oleada6-chk18.md) · [TR-f](../04-tareas/100-SistemaPartes/TR-015-update-f-agrupacion-cliente-chk18.md) |
| CHK-19 | **mvp_parcial** (F cerrado) | `Agrupá por cliente` | Igual prompt CHK-18 + caption **Cliente:** + negrita (subrayado no MVP); `eval-emission-ia-oleada7-chk19-apply.ps1` | **F cerrado** [oleada 7](../04-tareas/100-SistemaPartes/F-TR-015-update-h-oleada7-chk19.md) — matriz: sin subrayado obligatorio |
| CHK-20 | **mvp_parcial** (F cerrado) | `Agrupá por cliente y sumá los minutos` | Batch grupo + suma; copy **«Total minutos»** (no literal «Total:» genérico); `eval-emission-ia-chk20-apply.ps1` | **F cerrado** [oleada 7b](../04-tareas/100-SistemaPartes/F-TR-015-update-i-oleada7b-chk20.md) |
| CHK-21 | **mvp_parcial** (F cerrado) | `Agregá un segundo nivel de agrupación por fecha dentro de cada cliente` | **Un turno:** sin apply → `useTwoTurns`. Segundo nivel: **LAB-21** (dos turnos cliente → fecha) | **F cerrado** [oleada 8](../04-tareas/100-SistemaPartes/F-TR-015-update-j-oleada8-chk21.md) · opción B |
| CHK-22 | diferido | `Arma el detalle en columnas: fecha, cliente, minutos` | Sin apply | PHPUnit |
| CHK-23 | futuro_producto | `Usá papel continuo sin alto fijo para comanda` | **N/A** piloto Partes; anotar N/A | — |
| CHK-24 | futuro_producto | `Poné una tabla dinámica en el pie de página` | **N/A** | — |
| CHK-25 | futuro_producto | `Si el grupo no terminó no imprimas totales en el pie` | **N/A** | — |
| CHK-26 | **diferido** | `Poné el formato en horizontal` | Apply → orientación horizontal (Preview / Page Settings); alias: `Formato horizontal` | Código lab (`setPageOrientation`); **prioridad diferida** tras CHK-15..17 |
| CHK-28 | **mvp** | `Quitá la columna observación del detalle` | Sin observación en **detalle** ni título en page header; re-prompt → noop | **F cerrado** [oleada 12](../04-tareas/100-SistemaPartes/F-TR-015-update-n-oleada12-chk28.md) |
| CHK-29 | futuro_producto | `Sacá la agrupación por cliente` | **N/A** | — |
| CHK-30 | futuro_producto | `Eliminá el campo calculado de duración total` | **N/A** | — |
| CHK-31 | futuro_producto | `Quitá el texto fijo que dice Borrador en el detalle` | **N/A** | — |
| CHK-32 | futuro_producto | `Eliminá el gráfico del informe` | **N/A** | — |
| LAB-21 | mvp_parcial | Ver secuencia abajo | Dos turnos con apply entre medias | Script `eval-emission-ia-checklist-lab-extras.ps1` |

---

## Secuencia oleada 11 — CHK-15, 16, 17 (cuando exista apply)

Mismo setup Consulta detallada; variante con columna **clienteCode** en detalle antes de CHK-17.

| Orden | Prompt (pegar en SC) | Verificar |
|-------|----------------------|-----------|
| 1 | `Insertá el logo de la empresa` | PictureBox logo page header |
| 2 | `Agregá una imagen de fondo al informe` | Watermark en preview |
| 3 | `Mostrá clienteCode como código QR` | QR escaneable en detalle |

Contrato completo: [SPEC oleada 11](../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-11-imagenes-chk15-17-update.md).

---

## Secuencia LAB-21 (dos turnos)

Mismo setup; **no** descartar entre turnos si querés ver el layout acumulado (o **Guardar** tras turno 2).

| Turno | Prompt (pegar en SC) | Verificar en diseñador |
|-------|----------------------|-------------------------|
| 1 | `Agrupá por cliente` | Primer grupo por cliente |
| 2 | `Agrupá por fecha` | Segundo nivel (fecha) dentro del layout; dos bandas de grupo coherentes |

Éxito mínimo: ambos apply OK; **Preview** o Report Explorer muestra agrupación cliente + fecha.  
Fallo: segundo turno ignora fecha o pisa el primer grupo sin explicación.

---

## Registro acumulado (manual)

SoT de cierre por ítem: tabla **Registrar resultados** al inicio de esta guía. La sección «Checklist CHK — manual UI» en [`D-VERIFICACION-TR-015-update-b-2026-11-13.md`](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-b-2026-11-13.md) es **histórica** (2026-11-13); no define pendientes actuales.

Oleada 6 CHK-18: [F-TR-015-update-f-oleada6-chk18](../04-tareas/100-SistemaPartes/F-TR-015-update-f-oleada6-chk18.md) · [D-VERIFICACION](../04-tareas/100-SistemaPartes/D-VERIFICACION-TR-015-update-f-chk18-2026-11-21.md).

## Plantilla de registro (copiar al D-VERIFICACION)

```markdown
| CHK | Prompt usado | Resultado | Notas | Captura |
|-----|--------------|-----------|-------|---------|
| CHK-03 | Cambiá el título… | OK / Parcial / Fallo | | chk-03.png |
```

---

## Automatización (no sustituye BYOK en UI)

```powershell
# Planner (sin LLM)
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest

# Apply sidecar
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-checklist-apply.ps1
.\eval-emission-ia-oleada6-apply.ps1
.\eval-emission-ia-checklist-lab-extras.ps1
```

Relacionado: [`guia-bateria-manual-tr-015-ia-diseno.md`](./guia-bateria-manual-tr-015-ia-diseno.md) (batería #1–#8 histórica).