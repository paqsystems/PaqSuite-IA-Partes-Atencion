# Guía manual — un prompt por ítem (CHK-01 … CHK-26 + LAB-21)

Batería **camino B**: Smart Capture en **Diseñador de emisiones** + **apply** sidecar (whitelist v1).  
Matriz SoT: Framework [`15-reportes-emisiones-ia-checklist-matriz.json`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json).

Registrar resultados en el D-VERIFICACION de **TR-015-update-b** (columna **CHK-xx**: OK · Parcial · Fallo · N/A · Bloqueado).

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
| CHK-01 | diferido | `Agregá la columna observación en el detalle` | Sin apply; sin nueva columna en diseño; respuesta acorde a límite MVP | PHPUnit planner |
| CHK-02 | diferido | `Agregá un texto fijo que diga Borrador en el detalle` | Sin apply; sin control nuevo en detalle | PHPUnit |
| CHK-03 | mvp_parcial | `Cambiá el título a Consulta detallada` | Apply → título en Report Header (`lblReportTitle`); banner Guardar/Descartar; **Preview** muestra el texto | Planner + script apply |
| CHK-04 | diferido | `Poné un título encima de cada columna en el encabezado de página` | Sin apply; no títulos por columna auto-generados | PHPUnit |
| CHK-05 | diferido | `Encerrá el título en un panel` | Sin apply; sin panel nuevo | PHPUnit |
| CHK-06 | diferido | `Poné el nombre de la empresa arriba a la izquierda` | Sin apply (v1); opcional captura si el asistente explica límite | PHPUnit |
| CHK-07 | diferido | `Agregá la fecha con el mes en letras arriba a la derecha` | Sin apply | PHPUnit |
| CHK-08 | mvp_parcial | `Cambiá el título a CONSULTA DETALLADA` | Apply con texto **CONSULTA DETALLADA**; formato rico (negrita/subrayado/centrado) = **Parcial** si solo cambia el literal | Planner + apply |
| CHK-09 | diferido | `Agregá un pie de informe` | Sin apply; sin Report Footer nuevo | PHPUnit |
| CHK-10 | diferido | `Totalizá todos los registros en el pie del informe` | Sin apply a report footer | PHPUnit |
| CHK-11 | diferido | `Creá un campo calculado que sume duracionMinutos y presencial` | Sin apply | PHPUnit |
| CHK-12 | diferido | `Campo calculado IIf entre clienteNombre y observacion` | Sin apply | PHPUnit |
| CHK-13 | diferido | `Mostrá un campo calculado en el detalle` | Sin apply | PHPUnit |
| CHK-14 | diferido | `Totalizá el campo calculado en el pie del informe` | Sin apply | PHPUnit |
| CHK-15 | diferido | `Agregá una imagen de fondo al informe` | Sin apply | PHPUnit |
| CHK-16 | diferido | `Insertá el logo de la empresa` | Sin apply | PHPUnit |
| CHK-17 | diferido | `Mostrá clienteCode como código QR` | Sin apply | PHPUnit |
| CHK-18 | **mvp** | `Agrupá por cliente` | Apply → Group Header/Footer con **clienteNombre**; lienzo coherente; Guardar/Descartar | Planner + apply |
| CHK-19 | mvp_parcial | `Agrupá por cliente` | Mismo prompt que CHK-18; en grupo: etiqueta tipo **Cliente:** + campo; negrita/subrayado = bonus (Parcial si falta subrayado) | Apply script |
| CHK-20 | mvp_parcial | `Agrupá por cliente y sumá los minutos` | Apply grupo + total **duracionMinutos** en group footer (copy ~«Total minutos») | Planner + apply |
| CHK-21 | mvp_parcial | `Agregá un segundo nivel de agrupación por fecha dentro de cada cliente` | **Un solo turno:** esperado **sin apply** (no_mapped). Para lab positivo ver **LAB-21** | Manual |
| CHK-22 | diferido | `Arma el detalle en columnas: fecha, cliente, minutos` | Sin apply | PHPUnit |
| CHK-23 | futuro_producto | `Usá papel continuo sin alto fijo para comanda` | **N/A** piloto Partes; anotar N/A | — |
| CHK-24 | futuro_producto | `Poné una tabla dinámica en el pie de página` | **N/A** | — |
| CHK-25 | futuro_producto | `Si el grupo no terminó no imprimas totales en el pie` | **N/A** | — |
| CHK-26 | **mvp** | `Poné el formato en horizontal` | Apply → orientación horizontal (Preview / Page Settings); alias: `Formato horizontal` | Planner + apply |
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
.\eval-emission-ia-checklist-lab-extras.ps1
```

Relacionado: [`guia-bateria-manual-tr-015-ia-diseno.md`](./guia-bateria-manual-tr-015-ia-diseno.md) (batería #1–#8 histórica).