# D-VERIFICACION — TR-015-update-f (CHK-18)

| Campo | Valor |
|-------|--------|
| TR | [TR-015-update-f](./TR-015-update-f-agrupacion-cliente-chk18.md) |
| HU | [HU-015-update-f](../../03-historias-usuario/100-SistemaPartes/HU-015-update-f-agrupacion-cliente-chk18.md) |
| SPEC | [SPEC oleada 6](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-6-agrupacion-chk18-update.md) |
| D1 | [D1-TR-015-update-f-chk18](./d1/D1-TR-015-update-f-chk18.md) |
| Jira | PIAPARTES-63 (Epic) · PIAPARTES-64 (HU) · PIAPARTES-65 (TR) |
| Fecha F1 | 2026-11-21 |

---

## Verificación agente (F1 — CHK-18)

### Resultado

**Aprobado** — CHK-18 cerrado (apply + idempotencia + Guardar).

### Alcance

Solo **CHK-18** / `addGroupByField` → `clienteNombre`. Sin CHK-19/20/21.

### Evidencia manual UI (usuario, 2026-11-21)

Prompt canónico: **«Agrupá por cliente»** (Captura inteligente, diseñador emisiones).

| Turno | Secuencia observada | Veredicto |
|-------|---------------------|-----------|
| **1** | Asistente: *«Voy a aplicar las acciones permitidas al layout.»* → **«Cambios aplicados en el lienzo. Confirmá con Guardar o revertí con Descartar.»** → usuario **Guardar** → *«Layout guardado en el catálogo.»* | **OK** — apply L-B-10 |
| **2** | Mismo prompt → *«Voy a aplicar…»* → **«El informe ya está agrupado por cliente (clienteNombre) con encabezado y pie de grupo. No apliqué cambios.»** | **OK** — noop SC + clave `partes.emissionDesign.group.alreadyGrouped` |

**Captura:** `docs/04-tareas/100-SistemaPartes/evidence/chk-18-smart-capture-2026-11-21.png` (copia de sesión lab).

Referencia agente (misma captura): `assets/image-e82bf9b1-eb53-4b0b-ac38-d1e5b95549c7.png` en workspace Cursor.

### Checklist matriz CHK-18

| ID | Prompt | Resultado manual | Notas |
|----|--------|------------------|-------|
| CHK-18 | Agrupá por cliente | **OK** | Turno 1 apply + Guardar; turno 2 mensaje ya agrupado |

### Tests automatizados (E)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit --filter DesignLayout` | **OK** — 48 tests |
| `dotnet test --filter LayoutGroupByField` | **OK** — 3 tests |
| `.\eval-emission-ia-oleada6-apply.ps1` | **OK** — PASS CHK-18 (apply + re-apply `ya_agrupado`) |

### Criterios SPEC / HU

| ID | Descripción | Veredicto |
|----|-------------|-----------|
| CA-B6-01 | Planner → `addGroupByField` / `clienteNombre` | **OK** (PHPUnit + manual turno 1) |
| CA-B6-02 | Header + footer + GroupField en REPX | **OK** (eval + manual) |
| CA-B6-03 | Re-apply noop + mensaje SC | **OK** (manual turno 2) |
| CA-B6-04 | Guardar / Descartar | **OK** (Guardar ejecutado) |
| AC-01 … AC-04 | HU alineada | **OK** |

### Observaciones

- Copy del header de grupo sigue estilo técnico `clienteNombre:` (mejora prevista en **CHK-19**, fuera de este hito).

### Recomendación

Marcar TR/HU **Pendiente de Revisión humana** / cierre oleada 6 CHK-18 en tablero; **no** incluir CHK-19 en el mismo PR sin nuevo hito.