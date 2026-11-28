# Runbook — probar prompts naturales y brief por zonas (SPEC-015)

| Campo | Valor |
|-------|--------|
| Relacionado | [sesión DEV 2026-11-27](./sesion-pruebas-dev-spec-015-chk-emision-ia-2026-11-27.md) · [guía CHK canónico](./guia-manual-prompts-checklist-chk-emision-ia.md) |
| Matriz SoT | Framework [`15-reportes-emisiones-ia-checklist-matriz.json`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json) |
| Proceso | `partes.informes.consultaDetallada` — **Consulta detallada** |

---

## Qué estás testeando (tres capas)

| Capa | Pregunta | Herramienta hoy | Quién |
|------|----------|-----------------|-------|
| **A — Apply sidecar** | ¿La acción whitelist muta el REPX bien? | `eval-emission-ia-*-apply.ps1`, .NET appliers | CI / dev antes de sesión |
| **B — Planner** | ¿El mensaje (natural o brief) produce `actions[]` correctas? | PHPUnit `fromHeuristics` / `plan` (LLM en test con credencial fake + mock si existe) | Dev al cambiar heurísticas |
| **C — E2E producto** | ¿SC → turno → apply → diseñador + Guardar/Descartar funciona? | Navegador, `cliente=DEV`, BYOK | QA / piloto (vos en DEV) |

Los F1 históricos cubren sobre todo **A** + planner con **`promptCanonico`**. Tu trabajo piloto es **B+C** con lenguaje real.

**API del turno (capa B/C):** `POST /api/v1/emissions/design/processes/partes.informes.consultaDetallada/assistant/turn` — body: `message`, `reportUrl`, `credentialId`, `draftContext` (ver `ApiV1DesignAssistantTurnTest`).

---

## Cómo seguir — orden recomendado

```text
Fase 0 (15 min)     → Regresión canónica (scripts + 1 ítem en UI)
Fase 1 (por ítem)   → Matriz de paráfrasis (natural vs canónico)
Fase 2 (épica)      → LAB-ZONA-01 brief completo Consulta detallada
Fase 3 (cierre)     → D-VERIFICACION sesión + decisión CC/TR si hay gaps de producto
```

No mezclar fases en el mismo layout sucio: **Descartar** (o variante nueva) entre casos comparables.

---

## Fase 0 — Línea base (mismo día que DEV)

Confirmar que el lab no está roto **antes** de culpar al lenguaje natural.

```powershell
# Sidecar
Invoke-RestMethod http://127.0.0.1:5055/health

# Planner (sin LLM)
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayoutChecklistPlannerTest

# Apply oleadas según lo que vas a tocar (ej. page header)
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada5-apply.ps1
```

En UI: un solo **CHK-06 canónico** con `cliente=DEV` → **OK** → seguir a Fase 1.

---

## Fase 1 — Matriz de paráfrasis (manual DEV)

Por cada CHK **mvp** que quieras validar en producto:

1. **Canónico** (matriz) — debe ser **OK** (referencia).
2. **2–3 frases naturales** — inventario mínimo sugerido abajo.
3. Anotar por fila: **H** (heurística) vs **LLM** (inspeccionar `actions` en red o respuesta SC).
4. **Apply** sí/no; **visual** OK / Parcial / Fallo; **Descartar** antes del siguiente prompt del mismo ítem.

### Plantilla de registro (una fila = un prompt)

| CHK | Tipo | Prompt | Planner H/LLM | actions (resumen) | Apply | Visual | Notas |
|-----|------|--------|---------------|-------------------|-------|--------|-------|

### Paráfrasis mínima sugerida (empezar por page header — oleada 5)

| CHK | Canónico | Variantes naturales (probar) |
|-----|----------|------------------------------|
| CHK-06 | Poné el nombre de la empresa arriba a la izquierda | `agregar empresa en el encabezado arriba a la izquierda` · `agregar empresa` · `poné la empresa en el header` |
| CHK-07 | Agregá la fecha con el mes en letras arriba a la derecha | `fecha de impresión arriba a la derecha` · `agregar fecha en el encabezado` |
| CHK-04 | Poné un título encima de cada columna… | `títulos de columnas en page header` · `encabezado con nombre de cada campo del detalle` |
| CHK-03/08 | Cambiá el título a… | `el título del informe es Consulta Detallada` |

**Criterio:** variante natural **OK** si el layout es equivalente al canónico (mismos controles / filas / textos lab). **Gap** si el planner devuelve acciones distintas o el LLM inventa `payload.text` de ubicación.

**Atajo capa B (sin UI):** desde backend, para heurística solamente:

```php
// En tinker o test: DesignLayoutPageHeaderHeuristics::tryMatchEmpresa($msg, 'DEV')
// o DesignLayoutActionPlanner::fromHeuristics($msg)
```

Para **LLM** hace falta turno real con credencial (UI o POST assistant/turn).

---

## Fase 2 — LAB-ZONA-01 (brief por zonas)

**Objetivo:** un mensaje (o pocos turnos) con el bloque «Diseño reporte Consulta Detallada» de [sesión DEV § ejemplo](./sesion-pruebas-dev-spec-015-chk-emision-ia-2026-11-27.md#especificación-por-zonas--ejemplo-consulta-detallada).

### Precondiciones

- Variante con **columnas en detalle** (ej. spike-layout-b-1 o la que uses para CHK-04).
- Layout **limpio** (Descartar o copia de reporte lab).
- Anotar `reportUrl` exacto del diseñador.

### Protocolo (un intento = un registro)

| Paso | Acción |
|------|--------|
| 1 | Pegar **brief completo** en SC → Enviar |
| 2 | Copiar `actions[]` del turno (cantidad, tipos, payloads) |
| 3 | Confirmar apply si SC lo ofrece |
| 4 | Captura diseñador + checklist § mapa (qué zona quedó / qué gap) |
| 5 | **No Guardar** hasta terminar la tabla; o Guardar solo si querés acumular |

### Hoja de veredicto LAB-ZONA-01

| Zona del brief | ¿Planificó acción? | ¿Apply OK? | ¿Visual OK? | Gap / nota |
|----------------|-------------------|------------|-------------|------------|
| Page Header — empresa | | | | CHK-06 |
| Page Header — fecha/hora | | | | CHK-07; hora = gap |
| Page Header — título | | | | CHK-03 |
| Page Header — títulos columnas | | | | CHK-04 |
| Page Footer — hoja x/x | | | | **sin whitelist** |
| Group Header — por código cliente | | | | `clienteCode` vs `clienteNombre` |
| Detalle — campos | | | | N× `addDetailField` |
| Group Footer — total + suma | | | | CHK-20 |

**Variantes del mismo lab (opcional):**

| ID | Estrategia |
|----|------------|
| LAB-ZONA-01a | Un solo paste del brief completo |
| LAB-ZONA-01b | Turnos por sección: «armá el page header así: …» → apply → «ahora el detalle: …» |
| LAB-ZONA-01c | Brief + nombres técnicos en detalle (`usuarioCode`, `tipoTareaCode`, …) para medir GEN-37 |

---

## Fase 3 — Cerrar y decidir siguiente trabajo

| Resultado sesión | Acción |
|------------------|--------|
| Solo paráfrasis de ítems ya cerrados fallan | **CC / TR-update** planner (+ sanitize LLM) + tests PHPUnit; reaplicar Fase 1 |
| LAB-ZONA-01: muchas zonas sin acción | **SPEC-update** «brief multi-banda» — no es bug de un CHK suelto |
| Gaps de producto (`x/x`, hora, remove*, etc.) | Backlog checklist (CHK futuro / `futuro_producto`); **no** bloquear LAB-ZONA si están documentados como N/A |
| Apply canónico falla tras cambios | Prioridad **A** (sidecar); no avanzar Fase 2 |

Documento de salida sugerido: ampliar [sesion-pruebas-dev-spec-015-chk-emision-ia-2026-11-27.md](./sesion-pruebas-dev-spec-015-chk-emision-ia-2026-11-27.md) § plantilla, o crear `D-VERIFICACION-sesion-prompts-naturales-YYYY-MM-DD.md` con tablas Fase 1 + LAB-ZONA-01.

---

## Qué puede automatizarse después (dev)

| Ítem | Dónde | Prioridad |
|------|-------|-----------|
| Variantes CHK-06/07 en `DesignLayoutActionPlannerTest` | Partes backend | Alta (ya hay fallos DEV) |
| Dataset JSON `prompts-naturales-chk.json` + test que recorra `fromHeuristics` | Partes / Framework doc | Media |
| Script POST turn + dump actions (sin apply) | `scripts/dev/` | Media — requiere token + credencial |
| Integración turn → apply → assert REPX | Nuevo `eval-emission-ia-planner-apply.ps1` | Media-alta |
| Parser brief por bandas | **Épica nueva** SPEC | Baja hasta Fase 2 documente gaps |

---

## Checklist rápido del operador (cada sesión DEV)

- [ ] `?cliente=DEV` en URL del diseñador
- [ ] Sidecar `:5055` ok
- [ ] BYOK habilitado en SC
- [ ] Proceso confirmado
- [ ] `reportUrl` anotado
- [ ] Descartar entre casos comparables
- [ ] Captura + fila en tabla Fase 1 o LAB-ZONA

---

## Referencias

- Lista prompts canónicos: tabla en [guia-manual-prompts-checklist-chk-emision-ia.md](./guia-manual-prompts-checklist-chk-emision-ia.md)
- Lab sidecar: [guia-bateria-manual-tr-015-ia-diseno.md](./guia-bateria-manual-tr-015-ia-diseno.md)
- Feature turn: `backend/tests/Feature/Emissions/ApiV1DesignAssistantTurnTest.php`