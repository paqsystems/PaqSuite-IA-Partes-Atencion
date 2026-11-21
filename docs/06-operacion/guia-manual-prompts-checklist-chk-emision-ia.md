# Guía manual — un prompt por ítem (CHK-01 … CHK-26 + LAB-21)

Batería **camino B**: Smart Capture en **Diseñador de emisiones** + **apply** sidecar (whitelist v1).  
Matriz SoT: Framework [`15-reportes-emisiones-ia-checklist-matriz.json`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json).

Registrar resultados en el D-VERIFICACION del TR que corresponda:

- **Oleada 3 (apariencia):** [D-VERIFICACION-TR-015-update-c](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-c-2026-11-18.md) + guía acotada [smoke-oleada3-apariencia-controles.md](./smoke-oleada3-apariencia-controles.md)
- Resto checklist camino B: **TR-015-update-b** (columna **CHK-xx**: OK · Parcial · Fallo · N/A · Bloqueado)

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

---

## Tabla de prompts y criterio

| ID | Estado | Prompt (pegar en SC) | Resultado esperado (manual) | Capa auto ya cubierta |
|----|--------|----------------------|-----------------------------|------------------------|
| CHK-01 | **mvp** | `Agregá la columna observación en el detalle` | Apply → label en detalle con `[observacion]`; banner Guardar/Descartar | Planner + apply |
| CHK-02 | **mvp** | `Agregá un texto fijo que diga Borrador en el detalle` | Apply → label literal «Borrador» en detalle | Planner + apply |
| CHK-03 | mvp_parcial | `Cambiá el título a Consulta detallada` | Apply → `lblReportTitle` + bandas **Page Header/Footer** (vacías); Guardar/Descartar; Preview | Planner + apply |
| CHK-04 | **mvp** | `Poné un título encima de cada columna en el encabezado de página` | Variante **con columnas en detalle** (ej. spike-layout-b-1) → `lblPageHdr_*` fila 3; sin detalle → mensaje SC sin romper lienzo | Planner + apply + [smoke CHK-04](./smoke-oleada5-page-header-chk04.md) |
| CHK-05 | **mvp** | `Encerrá el título en un panel` | Apply → `pnlReportTitle` fila 2; **re-aplicar** con panel ya armado → mensaje *«El título del informe ya está encerrado…»* (`noop:wrapReportTitleInPanel:ya_colocados`); sin error HTTP | Planner + apply |
| CHK-06 | **mvp** | `Poné el nombre de la empresa arriba a la izquierda` | Lab (`local` / tenant `DEV`): apply → `lblPageHdr_Empresa` fila 1 izq.; **re-aplicar** mismo texto → mensaje *«…ya muestra el nombre de empresa…»* (`noop:setPageHeaderEmpresa:ya_colocados`) | Planner + apply (oleada 5) |
| CHK-07 | **mvp** | `Agregá la fecha con el mes en letras arriba a la derecha` | Apply → `pageInfoFecha` fila 1 derecha; **re-aplicar** si `pageInfoFecha` ya existe → mensaje *«…ya tiene la fecha de impresión…»* (`noop:setPageHeaderFecha:ya_colocados`); convive con CHK-06 | Planner + apply |
| CHK-08 | mvp | `Cambiá el título a CONSULTA DETALLADA` | Apply con texto **CONSULTA DETALLADA** (literal) | Planner + apply |
| CHK-08-format | **mvp** | `Poné el título en negrita, subrayado y centrado` | Apply → `lblReportTitle` con negrita/subrayado/centrado (page header o report header); **re-aplicar** → *«El título… ya estaba…»* (`noop:setControlAppearance:ya_formateado`); script `eval-emission-ia-chk08-format-apply.ps1` | Planner + apply oleada 3b |
| CHK-27 | **mvp** | `Al código de cliente asignale Times New Roman 12, azul marino, negrita, subrayado e italica, centrado` | Apply sobre control con `[clienteCode]` en detalle; variante con columna presente | Planner + apply |
| CHK-09 | diferido | `Agregá un pie de informe` | Sin apply; sin Report Footer nuevo | PHPUnit |
| CHK-10 | diferido | `Totalizá todos los registros en el pie del informe` | Sin apply a report footer | PHPUnit |
| CHK-11 | diferido | `Creá un campo calculado que sume duracionMinutos y presencial` | Sin apply | PHPUnit |
| CHK-12 | diferido | `Campo calculado IIf entre clienteNombre y observacion` | Sin apply | PHPUnit |
| CHK-13 | diferido | `Mostrá un campo calculado en el detalle` | Sin apply | PHPUnit |
| CHK-14 | diferido | `Totalizá el campo calculado en el pie del informe` | Sin apply | PHPUnit |
| CHK-15 | diferido | `Agregá una imagen de fondo al informe` | Sin apply | PHPUnit |
| CHK-16 | diferido | `Insertá el logo de la empresa` | Sin apply | PHPUnit |
| CHK-17 | diferido | `Mostrá clienteCode como código QR` | Sin apply | PHPUnit |
| CHK-18 | **mvp** | `Agrupá por cliente` | Apply → Group Header/Footer con **clienteNombre**; **re-aplicar** → mensaje *«…ya está agrupado por cliente…»* (`noop:addGroupByField:ya_agrupado`); Guardar/Descartar | Planner + apply + [oleada 6](../04-tareas/100-SistemaPartes/TR-015-update-f-agrupacion-cliente-chk18.md) |
| CHK-19 | mvp_parcial | `Agrupá por cliente` | Mismo prompt que CHK-18; group header con **Cliente:** + `[clienteNombre]` en negrita; **re-aplicar** con caption OK → `noop:addGroupByField:ya_agrupado`; layout CHK-18 legacy se repara en el mismo apply; script `eval-emission-ia-oleada7-chk19-apply.ps1` | Apply oleada 7 + [TR-015-update-h](../04-tareas/100-SistemaPartes/TR-015-update-h-group-header-cliente-chk19.md) |
| CHK-20 | mvp_parcial | `Agrupá por cliente y sumá los minutos` | Apply grupo + total **duracionMinutos** en group footer (copy ~«Total minutos») | Planner + apply |
| CHK-21 | mvp_parcial | `Agregá un segundo nivel de agrupación por fecha dentro de cada cliente` | **Un solo turno:** esperado **sin apply** (no_mapped). Para lab positivo ver **LAB-21** | Manual |
| CHK-22 | diferido | `Arma el detalle en columnas: fecha, cliente, minutos` | Sin apply | PHPUnit |
| CHK-23 | futuro_producto | `Usá papel continuo sin alto fijo para comanda` | **N/A** piloto Partes; anotar N/A | — |
| CHK-24 | futuro_producto | `Poné una tabla dinámica en el pie de página` | **N/A** | — |
| CHK-25 | futuro_producto | `Si el grupo no terminó no imprimas totales en el pie` | **N/A** | — |
| CHK-26 | **mvp** | `Poné el formato en horizontal` | Apply → orientación horizontal (Preview / Page Settings); alias: `Formato horizontal` | Planner + apply |
| CHK-28 | futuro_producto | `Quitá la columna observación del detalle` | **N/A** piloto (prioridad SPEC = comprobantes) | — |
| CHK-29 | futuro_producto | `Sacá la agrupación por cliente` | **N/A** | — |
| CHK-30 | futuro_producto | `Eliminá el campo calculado de duración total` | **N/A** | — |
| CHK-31 | futuro_producto | `Quitá el texto fijo que dice Borrador en el detalle` | **N/A** | — |
| CHK-32 | futuro_producto | `Eliminá el gráfico del informe` | **N/A** | — |
| LAB-21 | mvp_parcial | Ver secuencia abajo | Dos turnos con apply entre medias | Script `eval-emission-ia-checklist-lab-extras.ps1` |

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

Ver tabla viva en [`D-VERIFICACION-TR-015-update-b-2026-11-13.md`](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-b-2026-11-13.md) § **Checklist CHK — manual UI**.

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