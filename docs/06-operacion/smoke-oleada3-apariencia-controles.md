# Smoke manual — oleada 3 (`setControlAppearance`)

Cierre SDD de **TR-015-update-c** · CHK **CHK-08-format** y **CHK-27** únicamente.

| Artefacto | Ruta |
|-----------|------|
| TR | [TR-015-update-c](../04-tareas/100-SistemaPartes/TR-015-update-c-apariencia-controles-informes.md) |
| D-VERIFICACION | [D-VERIFICACION-TR-015-update-c-2026-11-18.md](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-update-c-2026-11-18.md) |
| Guía general CHK | [guia-manual-prompts-checklist-chk-emision-ia.md](./guia-manual-prompts-checklist-chk-emision-ia.md) |

---

## 0. Pre-requisitos (todos obligatorios)

| # | Verificación | Comando / acción |
|---|--------------|------------------|
| 1 | Sidecar Dx Reporting | `cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\src\PaqSuite.DxReporting` → `dotnet run` |
| 2 | Health | `GET http://127.0.0.1:5055/health` → `"status":"ok"` |
| 3 | Partes FE + BE lab | URL diseñador: `http://127.0.0.1:3102/emisiones/disenador?cliente=DEV` (ajustar puerto) |
| 4 | Login | Usuario con `emission.design` |
| 5 | BYOK Smart Capture | Panel SC → preferences → credencial **habilitada** |
| 6 | Proceso | **Consulta detallada** → Confirmar proceso |

**Apply automatizado (opcional, mismo criterio que manual):**

```powershell
cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\scripts
.\eval-emission-ia-oleada3-apply.ps1
```

Debe mostrar `PASS CHK-08-format` y `PASS CHK-27`.

---

## 1. CHK-08-format — título con formato

| Campo | Valor |
|-------|--------|
| Variante sugerida | **Spike B titulo** (ya trae `lblReportTitle`) |
| Prompt | `Poné el título en negrita, subrayado y centrado` |

### Pasos

1. Abrí Smart Capture en el diseñador.
2. Pegá el prompt y **Enviar**.
3. Confirmá **apply** si el asistente lo ofrece.
4. En el lienzo: localizá el label del título (`lblReportTitle` o equivalente en Report Header).
5. Comprobá visualmente: **negrita**, **subrayado**, texto **centrado** (horizontal).
6. Banner **Guardar / Descartar** (L-B-10) visible.
7. (Opcional) **Preview** — el título conserva el estilo.
8. **Descartar** para no contaminar el siguiente caso, o anotá si usaste Guardar.

### Criterio OK

- Apply sin error 4xx/5xx.
- Cambio visible en el control del título.
- Guardar/Descartar operativo.

---

## 2. CHK-27 — apariencia del campo `clienteCode`

| Campo | Valor |
|-------|--------|
| Variante sugerida | **principal** (`partes_consultaDetallada_principal`) — detalle con `[clienteCode]` |
| Prompt | `Al código de cliente asignale Times New Roman 12, azul marino, negrita, subrayado e italica, centrado` |

### Pasos

1. Mismo setup (sidecar + BYOK).
2. Seleccioná variante **principal** si no está ya activa.
3. Pegá el prompt en Smart Capture y **Enviar**.
4. Confirmá apply.
5. En **Detail**: el control que muestra el código de cliente debe verse con fuente TNR ~12, color azul marino oscuro, B+I+U, centrado.
6. Guardar/Descartar visible; probá **Descartar** al cerrar la ronda.

### Criterio OK

- Control con binding `[clienteCode]` actualizado (no se crea columna nueva).
- Estilos coherentes con el pedido (tolerancia menor en antialiasing del diseñador DX).

---

## 3. Registro del smoke (completar en equipo)

Copiá filas a **D-VERIFICACION** § Smoke manual:

| CHK | Variante | Resultado | Operador | Fecha ART | Notas |
|-----|----------|-----------|----------|-----------|-------|
| CHK-08-format | Spike B titulo | OK / Parcial / Fallo | | | |
| CHK-27 | principal | OK / Parcial / Fallo | | | |

**Cierre oleada 3:** ambas filas **OK** (o **Parcial** documentado en CC) → TR/HU/SPEC a **Finalizado** (parte I).

---

## 4. Si algo falla

| Síntoma | Revisar |
|---------|---------|
| SC pide configurar LLM | BYOK en preferences |
| Apply 404 / connection | Sidecar `:5055` y proxy host hacia layout API |
| noop / sin cambio | ¿Variante correcta? CHK-08-format necesita título previo; CHK-27 necesita `clienteCode` en detalle |
| Estilo parcial | Registrar **Parcial** — oleada 3b (padding/bordes) sigue fuera de alcance |