# Borrador GEN-15 — Selección de proceso en el diseñador

**Origen:** chat Partes (2026-08-25).  
**Destino:** aplicar en repo **PaqSuite-IA-FRAMEWORK** (no implementar acá).  
**Motivo:** un producto puede tener N procesos emisibles; el diseñador no debe abrir “en el aire” ni hardcodear un proceso. Aunque N=1, **siempre** se muestra la lista (unitaria).

---

## 0) Decisión cerrada (pegar al abrir el chat en Framework)

> La **previa de selección de proceso emisible** es responsabilidad del **Framework** (`@paqsuite/react-core` + `paqsuite/laravel-core`), no del host.  
> El host solo: seed de procesos/reportes, registro de puertos dataset/schema, y montaje de la página GEN + menú.  
> Flujo Must: entrar al diseñador → listar procesos activos → elegir uno (también si hay uno solo) → listar reportes del proceso → abrir DX con el schema de ese proceso.  
> No se diseña sin proceso elegido (D-EM-02 / unidad funcional = proceso).

**Orden Open Spec en Framework:** producto `15-update` → SPEC-001-15 → HU-GEN-15-dx-reporting-documental → TR-GEN-15-dx-reporting-documental → D/E.  
**Después (Partes):** alinear SPEC-011 / HU-011 / TR-011 (retirar “proceso fijo” CA-15) y montar `EmissionReportDesignerPage` sin `processCode` hardcodeado.

---

## 1) Producto — `docs/02-producto/15-reportes-emisiones-update.md`

### Añadir (sugerido tras §5 o en §13/diseñador)

**Nueva decisión permanente (o subsección del diseñador):**

- El diseñador documental GEN es **multi-proceso**.
- Al abrir la superficie de diseño, el Framework muestra el **listado de procesos emisibles activos** del tenant/producto (catálogo seed/SQL) y exige **selección explícita** de un proceso antes de editar layouts.
- La lista se muestra **también cuando hay un solo proceso** (lista unitaria; no auto-saltar al diseñador).
- El schema/dataset del Field List lo resuelve el Framework vía el **puerto registrado** del proceso elegido; el host no inventa un picker propio.
- Tras elegir proceso, se puede seleccionar reporte (principal por defecto) como hoy.

**Ajuste frontera producto/host (§ adopción / aporte del producto):**

| Aporta el producto (host) | Resuelve el Framework |
|---------------------------|------------------------|
| Seed procesos + reportes iniciales + puertos `resolveDataset`/schema | UI lista de procesos + lista de reportes + diseñador DX |
| Menú/ruta que monta `EmissionReportDesignerPage` | Authz `emission.design` → 4709; APIs design |

### Decisión permanente (añadir a la lista §31)

21. El diseñador siempre parte de una **selección de proceso emisible** (lista Must, también N=1); no se hardcodea el proceso en la página GEN.

---

## 2) SPEC — `docs/05-open-spec/001-Generalidades/SPEC-001-15-reportes-emisiones.md`

### §7 Plantillas y diseñador — añadir bullets

- Superficie de diseño desktop = **selección de proceso** → selección de reporte → diseñador DX.
- La selección de proceso usa el **catálogo** `GET /api/v1/emissions/processes` (o endpoint design equivalente con `emission.design`); **Must**, no Should.
- Con un solo proceso activo, la UI **igual** muestra la lista y pide confirmación/selección (no bypass).
- Sin proceso seleccionado no se monta el diseñador DX ni se pide schema.
- El host **no** debe pasar un `processCode` fijo como única vía de entrada; puede deep-link opcional `?processCode=` **preseleccionando** en la lista, nunca omitiendo la lista.

### §11 Contratos — ampliar filas

| Tema | Norma |
|------|--------|
| UI diseñador | `EmissionReportDesignerPage` incluye **paso previo** de selección de proceso (Must) |
| Listado procesos | `GET /api/v1/emissions/processes` es contrato Must para diseñador (y smoke); gated por capacidad emisiones + `emission.design` cuando se usa desde design |
| Schema diseñador | Por `processCode` elegido, vía puerto/host registry; no schema global único |

### Criterios medibles SPEC — añadir

- [ ] Al abrir el diseñador desktop con `emission.design`, se listan los procesos emisibles activos y se exige elegir uno antes del layout DX (también si N=1).
- [ ] Tras elegir proceso, se listan reportes del proceso y el diseñador opera sobre el schema de ese proceso.
- [ ] El host de adopción no hardcodea el único proceso como sustituto de la lista GEN.

### Fuera de alcance (reafirmar)

- ABM web del catálogo de procesos (sigue seed/SQL).
- Picker de proceso inventado por cada producto host.

### Changelog / nota CC (sugerido)

| Fecha | Cambio |
|-------|--------|
| 2026-08-25 | CC: selección de proceso Must en diseñador GEN (también N=1); listado procesos Must para design; host solo adopta. |

---

## 3) HU — `docs/03-historias-usuario/001-Generalidades/HU-GEN-15-dx-reporting-documental.md`

### Narrativa (ajustar)

Como **diseñador de reportes**,  
quiero **elegir el proceso emisible y luego diseñar su layout DX**,  
para **trabajar el dataset correcto sin hardcodear un proceso del producto**.

### Alcance incluido — añadir

- Paso previo obligatorio: listado y selección de proceso emisible activo.
- Lista visible con N≥1 (incluida lista unitaria).
- Tras selección: comportamiento actual (reportes / principal / diseñador / deep-link NS).

### Reglas de negocio — añadir

8. Abrir el diseñador exige **selección explícita de proceso** antes del DX.
9. Con un único proceso activo, la UI **muestra** ese proceso en lista y requiere selección (no auto-abrir DX).
10. Deep-link con `processCode` solo **preselecciona**; no omite el paso de lista.

### Criterios de aceptación — añadir

- [ ] **CA-09** Con `emission.design` en desktop, al entrar al diseñador se muestran los procesos emisibles activos.
- [ ] **CA-10** Sin proceso seleccionado no se monta el diseñador DX.
- [ ] **CA-11** Con un solo proceso activo, la lista se muestra (unitaria) y solo tras seleccionarlo se habilita el diseño.
- [ ] **CA-12** Tras seleccionar proceso, el Field List / schema corresponde al puerto de ese proceso.

### Gherkin — añadir

```gherkin
  Scenario: Lista de procesos aunque haya uno solo
    Given un tenant con un unico proceso emisible activo
    And un usuario con emission.design en desktop
    When abre el disenador GEN
    Then ve la lista de procesos con ese unico item
    And el disenador DX no esta activo hasta seleccionarlo

  Scenario: Multiples procesos
    Given dos procesos emisibles activos
    When el usuario elige el proceso B
    Then el disenador opera sobre el schema y reportes de B
```

### Trazabilidad SPEC — añadir filas a CA-09..12.

---

## 4) TR — `docs/04-tareas/001-Generalidades/TR-GEN-15-dx-reporting-documental.md`

### Cierres B1→C / nuevo cierre C1

| ID | Tema | Decisión |
|----|------|----------|
| **C1-15-xx** | Selección proceso | Must en `EmissionReportDesignerPage`. Flujo: load processes → SelectBox/lista → load reports → DX. **Prohibido** auto-skip si `items.length === 1`. |
| **C1-15-xx** | API lista | Consumir `GET /api/v1/emissions/processes` (items activos). Si hace falta variante design-only, documentar path; authz: capacidad on + `emission.design` (4709). |
| **C1-15-xx** | Props host | `processCode` deja de ser **obligatorio** en la page canónica. Opcional `initialProcessCode` para preselección. Hosts legacy con prop fija: migrar en adopción. |
| **C1-15-xx** | Schema | Al cambiar proceso seleccionado, invalidar reporte seleccionado y recargar reports + contexto DX (`reportUrl`/schema del proceso). |

### API — confirmar / elevar a Must

| Método | Path | Auth | Notas |
|--------|------|------|--------|
| GET | `/api/v1/emissions/processes` | Bearer + capacidad emisiones; desde UI design + `emission.design` | Items: `processCode`, nombre/caption, `active`, … |
| GET | `/api/v1/emissions/design/processes/{processCode}/reports` | `emission.design` | Sin cambio |

### Cambios por capa

| Pieza | Cambio |
|-------|--------|
| `laravel-core` | Asegurar `listProcesses` estable; authz design sobre listado si hoy es laxo; OpenAPI |
| `@paqsuite/react-core` | `EmissionReportDesignerPage`: UI selección proceso (siempre) + luego reports; tests unitarios N=1 y N=2 |
| Smoke host | Dejar de hardcodear proceso en la page; seed ≥1 proceso; E2E: lista visible → seleccionar → stub/DX |
| Docs adopción | Guía host: montar page GEN sin `processCode` fijo; seed N procesos |

### Plan de tareas (delta)

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T-sel-1 | Backend | Contrato list processes Must + tests 4709/capacidad | CA-09 |
| T-sel-2 | Frontend | Paso selección proceso en `EmissionReportDesignerPage` (no skip N=1) | CA-09..11 |
| T-sel-3 | Frontend | Wire schema/reports al `processCode` elegido | CA-12 |
| T-sel-4 | Tests | Unit N=1/N=2 + smoke E2E humo selección | CA-09..12 |
| T-sel-5 | Docs | Guía adopción + changelog SPEC | — |

### Tests mínimos

- N=1: lista visible; DX no monta hasta click/selección.
- N=2: cambiar de A→B recarga reports/schema.
- Sin `emission.design`: 4709 / UI forbidden (sin lista operativa).
- Native: sin diseñador (sin cambio).

---

## 5) Nota para Partes (adopción docs)

**Hecho (2026-08-25):** alineados SPEC-011 §4.8 / CA, HU-011 CA-15 + Gherkin, TR-011 RN-TR-12 / CA-15 / listado Must, producto `15-reportes-emisiones.md` (D-EM-02b).

| Artefacto | Estado |
|-----------|--------|
| SPEC-011 / HU-011 CA-15 / TR-011 | Docs alineados a GEN Q13 |
| FE `ReportDesignerHostPage` | **Hecho:** `EmissionReportDesignerPage` sin hardcode; `?processCode=` → `initialProcessCode`; tests unitarios N=1. Dep `@paqsuite/react-core` `^2.3.0` (instalado vía path local Framework si Verdaccio no responde). |
| Tests E2E lista→confirm | Pendiente con el FE |

No implementar el FE en Partes hasta que Framework publique el slice en Satis/`react-core`.

---

## 6) Prompt sugerido para el chat en Framework

```text
Parte G / CC sobre GEN-15 diseñador.

Decisión ya cerrada (chat Partes 2026-08-25):
- La selección de proceso emisible en el diseñador es Must del Framework
  (EmissionReportDesignerPage + GET /emissions/processes).
- Siempre se muestra la lista, también si N=1 (no auto-skip).
- El host solo seed + puertos + monta la page; no inventa picker.
- Tras elegir proceso → reports → DX con schema de ese proceso.

Aplicar el borrador que está en el repo Partes:
docs/_handoff/GEN-15-seleccion-proceso-disenador-borrador.md
(o el texto pegado de producto → SPEC-001-15 → HU-GEN-15-dx-reporting-documental
→ TR-GEN-15-dx-reporting-documental).

Seguir Open Spec en orden; no implementar código hasta C/D según dispatcher.
```

---

## 7) Checklist al aplicar en Framework

- [ ] Actualizar `15-reportes-emisiones-update.md` (decisión 21 + frontera diseñador)
- [ ] Actualizar SPEC-001-15 (§7, §11, CA medibles, changelog CC)
- [ ] Actualizar HU-GEN-15-dx-reporting-documental (CA-09..12 + Gherkin)
- [ ] Actualizar TR-GEN-15-dx-reporting-documental (cierres, API Must, tareas T-sel-*)
- [ ] Marcar estado HU/TR según metodología (Especificado tras alinear)
- [ ] Solo entonces Parte D (código) + smoke
- [ ] Avisar Partes para delta SPEC-011/HU/TR-011 + bump paquetes
