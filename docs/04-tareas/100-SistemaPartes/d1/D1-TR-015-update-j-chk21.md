# Plan de implementación — TR-015-update-j (oleada 8, **CHK-21**)

> Parte **D1** (2026-11-24). **Opción B** confirmada usuario 2026-11-24. Parte **D** ejecutada.

## Alcance entendido

- **CHK-21 (un turno):** matriz exige `no_mapped` — no aplicar mal un solo grupo.
- **LAB-21 (dos turnos):** cliente, luego fecha — camino positivo documentado con script.
- Sidecar hoy: cada `addGroupByField` asume primer nivel o reparación; **no** hay contrato probado para segundo header anidado.

**Drift detectado:** el prompt CHK-21 contiene «agrupación» + «cliente»; la heurística actual (`agrup…cliente`) puede devolver **una** acción `clienteNombre` → viola matriz hasta corregir (típico de **opción B**).

---

## Fuentes leídas

- SPEC oleada 8, HU/TR CHK-21, matriz CHK-21 / LAB-21
- `DesignLayoutActionPlanner.php` L91–97 (elseif único por dimensión)
- `LayoutGroupByFieldApplier.cs` (insert before Detail, un campo por invoke)
- `eval-emission-ia-checklist-lab-extras.ps1`
- Guía manual §CHK-21 / §LAB-21

---

## Opción A — Un turno, dos acciones

| Pros | Contras |
|------|---------|
| UX “pedir todo en una frase” | Cambia matriz CHK-21 (`no_mapped` → `actions`) |
| Alineado a LLM futuro | Planner + orden batch + sidecar multi-nivel en un release |
| | Mayor superficie de regresión (p.ej. noop por nivel) |
| | Smoke manual más amplio |

**Orden trabajo A:** heurística 2 acciones → extender applier (header fecha interno) → actualizar matriz CHK-21 → script oleada 8 batch → LAB-21 regresión.

---

## Opción B — `no_mapped` + LAB-21 (recomendación D1)

| Pros | Contras |
|------|---------|
| Coincide con matriz y checklist markdown vigente | Usuario debe dos turnos (guía SC) |
| Menor alcance planner; cambio acotado | No satisface literal “un turno” funcional |
| Encaja MVP “mvp_parcial” | |
| Corrige falso positivo CHK-21 con guardias | |

**Orden trabajo B:**

1. **Planner** — Antes de ramas `agrup…cliente/fecha`: si texto indica segundo nivel (`segundo nivel`, `segundo grupo`, `dentro de cada`, etc.) → salir sin acciones.
2. **PHPUnit** — `test_heuristics_chk21_no_mapped` + opcional `phpunit_planner` en matriz CHK-21.
3. **Sidecar** — Si ya existe `GroupHeader` con `clienteNombre` y llega `addGroupByField` `fecha`: insertar header fecha antes de `Detail`; footer `GroupFooter_fecha`; caption fecha desde schema.
4. **Tests .NET** — LAB-21 equivalente in-memory.
5. **Script** — `eval-emission-ia-checklist-lab-extras.ps1` LAB-21 PASS; ajustar needles si hace falta `GroupHeader_clienteNombre`.
6. **i18n (Should)** — Mensaje que mencione LAB-21 (dos turnos) al no mapear CHK-21.
7. **Regresión** — oleadas 6, 7, 7b sin tocar CHK-19/20 en el mismo PR funcional.

---

## Impacto esperado (común)

| Capa | B | A |
|------|---|---|
| DB | — | — |
| Backend Partes | Planner + tests | + heurística 2 acciones |
| Sidecar | Applier multi-header | Igual |
| Framework matriz | Sin cambio CHK-21 | Actualizar CHK-21 expectedActions |
| Docs | Veredicto en SPEC §6 | Idem |

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Orden bandas DX incorrecto | Assert en test + script LAB-21 |
| CHK-20 sum en pie cliente con footer fecha intermedio | Test layout con cliente+fecha+suma |
| Needles `GroupHeaderCliente` obsoletos | Leer XML post CHK-19 |

---

## Tests a ejecutar (E)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet test --filter LayoutGroupByField

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-checklist-lab-extras.ps1
```

---

## Recomendación D1

**Opción B** — respeta SoT matriz, menor riesgo, LAB-21 ya define el camino E2E; la opción A puede ser épica posterior si producto exige un turno.

---

## Confirmación de alcance

- Sin CHK-19/20 en la misma corrida: **Sí**
- Opción **B** adoptada; parte D ejecutada.