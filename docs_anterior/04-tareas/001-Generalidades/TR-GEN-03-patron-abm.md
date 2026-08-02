# TR-GEN-03-patron-abm — Patrón ABM modal sobre grilla

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-03-patron-abm](../../03-historias-usuario/001-Generalidades/HU-GEN-03-patron-abm.md) |
| **SPEC relacionada** | [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-03-grillas-listados; TR-GEN-02-modelo-roles-permisos-seed |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 (cierre manual post-F) |

**Cierre F formal:** [F-GEN-03-cierre-formal](F-GEN-03-cierre-formal.md)

**Origen:** [HU-GEN-03-patron-abm](../../03-historias-usuario/001-Generalidades/HU-GEN-03-patron-abm.md)  
**Referencia SPEC:** [SPEC-001-03-ui-transversal](../../05-open-spec/001-Generalidades/SPEC-001-03-ui-transversal.md)  
**Contexto:** [`patrones-abm.md`](../../00-contexto/_mono/03-ui-transversal/patrones-abm.md) · Regla `24-ui-abm-grilla-alta-edicion-modal.md`

---

## 1) HU Refinada (resumen)

### Título
ABM transversal: grilla + modal DevExtreme + permisos + confirmación de baja.

### In scope / Out of scope
- **In scope:** extensión `DataGridDx` modo ABM, **alta solo con botón «+» nativo de la grilla DevExtreme** (`grillas.md`), acciones editar/eliminar/detalle por ícono + `hint`, `AbmFormPopup` (portal), confirmación eliminar, refresh grilla, permisos `Permiso_*`.
- **Out of scope:** APIs de negocio (mock/stub en referencia), formularios full-page (solo HU SPEC-101 explícita).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Botón **+** nativo del `DataGrid` visible si `Permiso_Alta` (`editing.allowAdding` + toolbar DX); **no** botón Agregar HTML/DX fuera de la grilla.
- **AC-02**: Editar/eliminar según `Permiso_Modi` / `Permiso_Baja`.
- **AC-03**: Alta/edición en `Popup` DX con overlay pantalla completa (portal).
- **AC-04**: Eliminar con `Dialog` confirmación i18n.
- **AC-05**: Éxito cierra modal y refresca `dataSource` sin perder layout activo salvo reload explícito.
- **AC-06**: Acciones fila: íconos + tooltip (reutiliza TR grillas).
- **AC-07**: `data-testid`: `abmAddRow` (botón + DX), `abmEdit`, `abmDelete`, `abmConfirmDelete`, `abmFormPopup`.
- **AC-08**: Página demo/stub ABM de referencia en ruta protegida (o ampliación dashboard solo si producto acuerda).
- **AC-09**: E2E smoke alta o edición en stub (si existe en release).

---

## 3) Reglas de Negocio

1. **RN-01**: Modal obligatorio en GEN-03; excepción solo HU negocio.
2. **RN-02**: **Alta (insert):** siempre mediante el botón **«+»** que provee la grilla DevExtreme (toolbar integrada / `addRowButton` del `DataGrid`), habilitado con `editing.allowAdding: true`. **Prohibido** sustituir por un `Button` «Agregar» en toolbar externa o en header de página.
3. **RN-03**: El «+» abre el flujo de alta (fila nueva o callback) que deriva en **modal** `AbmFormPopup`; no alta inline persistente salvo decisión explícita de proceso.
4. **RN-04**: Confirmación eliminar identifica registro (código o label).
5. **RN-05**: Acciones de fila: ícono + `hint` i18n (misma norma que TR-GEN-03-grillas-listados / regla 28).
6. **RN-06**: Permisos desde props o hook `useProcessPermissions(proceso)`.
7. **RN-07**: Popup: `hideOnOutsideClick: false`, `showCloseButton`, tamaño responsive mínimo según regla 24.
8. **RN-08**: Validación submit en cliente; errores API en modal.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-01)

**Fuentes revisadas:** HU-GEN-03-patron-abm, SPEC-001-03, `patrones-abm.md`, `grillas.md` § ABM, regla `24-ui-abm-grilla-alta-edicion-modal.md`, TR-GEN-03-grillas-listados (RN-07 alta con **+** DX).

### Resultado general

- **Estado:** Apto con observaciones
- **Puede pasar a D1/D:** **Sí** (cerrar contradicción Agregar en §3.2; HU alineada en misma fecha)

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **HU “Agregar en toolbar” vs TR botón + nativo DX** | Dos UX distintas; Gherkin HU dice “pulsa Agregar” | **Prevalece TR + `grillas.md`:** alta solo con **+** integrado del `DataGrid` (`editing.allowAdding`). Actualizar redacción HU CA-04 / Gherkin en TR-update HU (no bloquea D1). |
| AMB-C02 | **Opción A vs B (API demo)** | E2E y permisos indefinidos si queda abierto | **Cierre MVP:** **Opción A** — `CustomStore` local en `AbmDemoPage`; Opción B solo si QA exige integración real. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Ruta demo | `/demo/abm` protegida; ítem menú MVP opcional (oculto en prod). |
| AMB-M02 | `useProcessPermissions(proceso)` | Reutilizar mapa permisos del menú/bootstrap si existe; en demo, objeto estático `{ alta, modi, baja, repo: true }`. |
| AMB-M03 | `data-testid` `abmAddRow` | Apunta al botón **+** DX (`elementAttr` / wrapper testid estable). |
| AMB-M04 | Alta inline DX vs modal | El **+** dispara `onInitNewRow` / callback que abre `AbmFormPopup`; **no** edición inline persistente. |

### Contradicciones TR ↔ HU ↔ SPEC

| Contradicción | Resolución |
|---------------|------------|
| HU alcance “Botón Agregar en toolbar” | Significa **toolbar de la grilla DX** (ícono +), no `Button` externo. |
| HU Gherkin “pulsa Agregar” | Equivalente a pulsar **+** del DataGrid. |
| TR AC-08 “dashboard o demo” | **Solo `AbmDemoPage`** en GEN-03; dashboard queda en TR grillas sin ABM. |

### Supuestos detectados

- `AbmFormPopup` usa portal `document.body` (regla 24).
- Permisos `Permiso_*` ya modelados en seed (TR-GEN-02-modelo-roles-permisos-seed).

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1.**

---

## 3.2) Resoluciones C1 — pre-D1 (2026-06-01)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Alta ABM | Solo botón **+** nativo DevExtreme; prohibido Agregar externo. |
| R-C1-02 | Referencia MVP | `AbmDemoPage` + store local (Opción A). |
| R-C1-03 | HU | Alinear CA-04/Gherkin a “+” DX (TR-update HU, no bloqueante). |
| R-C1-04 | Flujo + | `onInitNewRow` / handler → `AbmFormPopup` modal. |

---

## 3.3) Plan D1 — Implementación (2026-06-01)

**Depende de:** TR-GEN-03-grillas-listados (`DataGridDx`).

### Alcance entendido

Patrón UI ABM: `AbmFormPopup` (portal), `confirmDelete` (Dialog DX), extensión `DataGridDx` con `editing.allowAdding` + botón **+** DX, row actions ícono/hint, permisos vía props; página **`/demo/abm`** con **CustomStore local** (Opción A — sin API productiva).

### Decisiones D1

| ID | Decisión |
|----|----------|
| D1-1 | Referencia: ruta `/demo/abm`, `proceso="pw_demo_abm"`, `gridId="main"` |
| D1-2 | Store mock en memoria (array + id autoincrement) para E2E alta/edición/baja |
| D1-3 | Permisos demo: todos `true` o usuario supervisor en E2E |
| D1-4 | No menú MVP obligatorio; ruta registrada en `protectedRoutes` (acceso directo / link dev) |

### Orden de trabajo

1. `AbmFormPopup.tsx` + `useAbmModal.ts`
2. `confirmDelete.ts` (Dialog DX)
3. Props ABM en `DataGridDx` (`abmEnabled`, callbacks, `permissions`)
4. `AbmDemoPage.tsx` + formulario mínimo DX (`Form` 2–3 campos)
5. i18n `abm.*`
6. E2E `abm-transversal.spec.ts`
7. README opcional `shared/ui/abm/README.md`

### Confirmación de alcance

**Sí** — Opción B API demo **no** en primer cierre salvo necesidad QA.

---

## 4) Impacto en Datos

- Sin tablas nuevas en slice transversal.
- Stub backend opcional: `GET/POST/PUT/DELETE /api/v1/demo/abm-items` **solo entorno dev/test** — **no obligatorio** si la referencia ABM es puramente frontend mock hasta SPEC-101.

**Decisión MVP transversal:** implementar patrón UI con **CustomStore local** o endpoint demo documentado en esta TR; procesos reales en SPEC-101.

---

## 5) Contratos de API y OpenAPI

### Opción A (recomendada para cerrar GEN-03 sin negocio)

Sin endpoints productivos. Store mock en frontend para E2E del patrón.

### Opción B (opcional — demo)

| Método | Path | Permiso |
|--------|------|---------|
| GET | `/api/v1/demo/abm-items` | `Permiso_Repo` |
| POST | `/api/v1/demo/abm-items` | `Permiso_Alta` |
| PUT | `/api/v1/demo/abm-items/{id}` | `Permiso_Modi` |
| DELETE | `/api/v1/demo/abm-items/{id}` | `Permiso_Baja` |

Si se implementa Opción B, documentar en OpenAPI y matriz como **demo MVP transversal**.

---

## 6) Cambios Frontend

### Estructura

```text
frontend/src/shared/ui/abm/
  AbmFormPopup.tsx          # Popup DX + portal
  useAbmModal.ts            # open create / edit / view
  confirmDelete.ts          # Dialog DX
frontend/src/shared/ui/grids/
  DataGridDx.tsx            # extender: editing.allowAdding + toolbar «+» DX, rowActions ABM
```

### Props adicionales `DataGridDx` (modo ABM)

| Prop | Notas |
|------|-------|
| `abmEnabled` | boolean |
| `onCreate` | abre modal vacío |
| `onEdit(row)` | abre modal con datos |
| `onDelete(row)` | confirm + callback |
| `permissions` | `{ alta, modi, baja, repo }` |

### `AbmFormPopup`

- Children: formulario DevExtreme (`Form` o campos sueltos).
- Botones Guardar / Cancelar i18n.
- `data-testid="abmFormPopup"`.

### Integración permisos

- Reutilizar matriz menú: permisos del `proceso` host vía API existente o mapa estático en demo.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | `AbmFormPopup` + portal según regla 24 | AC-03 |
| T2 | Frontend | `confirmDelete` Dialog | AC-04 |
| T3 | Frontend | `DataGridDx` ABM: `editing.allowAdding` + botón + DX + row actions ícono/hint | AC-01, AC-02, AC-06 |
| T4 | Frontend | Página referencia `AbmDemoPage` + ruta `/demo/abm` (protegida) | AC-08 |
| T5 | i18n | `abm.*` | |
| T6 | Tests | E2E `abm-transversal.spec.ts` con store mock | AC-09 |

---

## 8) Estrategia de Tests

- **Unit:** `useAbmModal` open/close.
- **E2E:** agregar ítem mock → aparece en grilla → editar → eliminar con confirmación.

---

## 9) Riesgos y Edge Cases

- Solapamiento con futuras pantallas SPEC-101 → documentar que procesos reales **componen** `DataGridDx` + formulario propio dentro de `AbmFormPopup`.
- Popup anidado en shell con scroll → obligatorio portal body.

---

## 10) Verificación F formal (2026-06-01)

- **Tests:** E2E `abm-transversal.spec.ts` 2 OK
- **QA manual:** alta/edición/baja y botón **+** en `/demo/abm`

---

## 11) Checklist final

- [x] Patrón reusable en `shared/ui/abm/` + demo `/demo/abm`
- [x] Alta solo con botón **+** DX; acciones fila ícono + hint i18n

---

## Orden en bloque GEN-03

**3/4** — Requiere `DataGridDx`. Convive con layouts/export en toolbar.










