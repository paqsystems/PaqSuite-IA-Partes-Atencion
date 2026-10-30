# Guía — batería manual TR-015 (IA diseño informes, §4.4)

Para operadores con permiso **`emission.design`**. Registrá resultados en  
[`D-VERIFICACION-TR-015-2026-10-30.md`](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md).

Referencias: [SPEC-015 §4.4](../05-open-spec/100-SistemaPartes/SPEC-015-ia-diseno-informes.md) · [ops sidecar](./adopcion-tr-015-piloto-ia-diseno-sidecar.md) · Framework [runbook §6](../../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/runbook-dx-reporting-sidecar.md).

---

## 1. Antes de empezar (checklist)

| # | Requisito | Cómo comprobarlo |
|---|-----------|------------------|
| 1 | Sidecar FW **26.1.4** con build post–Parte D | `GET http://localhost:5055/health` → `{ "status": "ok" }` |
| 2 | **LLM en el proceso sidecar** (no Laravel) | En la terminal/servicio donde corre `dotnet run`: `$env:OPENAI_API_KEY` definido (o Azure completo). Al arrancar debe verse `[PaqSuite.DxReporting.AI] Reporting AI habilitado` |
| 3 | Backend Partes en **remote** | `PAQSUITE_EMISSION_DX_MODE=remote` y URL del sidecar |
| 4 | Frontend web (escritorio), no móvil | Navegador de escritorio |
| 5 | Usuario con **diseño de emisiones** | Menú **Soporte Técnico → Diseñador de emisiones** visible |
| 6 | Licencia DX JS | `VITE_DEVEXPRESS_LICENSE_KEY` en dev (ver TR-011) |
| 7 | Parámetro emisiones activo | Emitir visible en consulta detallada (referencia) |

**Si no aparece el asistente IA:** diseñador clásico puede funcionar igual; revisá env del sidecar, licencia IA DevExpress y consola del sidecar al iniciar. No continúes la batería #1–#7 sin wizard IA; documentá **bloqueado** en F1.

---

## 2. Levantar lab local (Windows, ejemplo)

### 2.1 Sidecar

```powershell
$env:OPENAI_API_KEY = '<tu-key>'   # variable de sesión o usuario Windows
$env:OPENAI_MODEL = 'gpt-4o-mini'  # opcional

cd C:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting\src\PaqSuite.DxReporting
dotnet run
```

Dejá esta ventana abierta. Si un sidecar viejo bloquea la DLL, cerralo antes de `dotnet run`.

### 2.2 Backend Partes

En `backend/.env` (solo DX remoto, **sin** OpenAI):

```env
PAQSUITE_EMISSION_DX_MODE=remote
PAQSUITE_EMISSION_DX_SERVICE_URL=http://localhost:5055/api/v1/dx-reporting
```

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php artisan serve --port=8088
```

### 2.3 Frontend

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion\frontend
npm run dev
```

Abrí la URL que muestre Vite (p. ej. `http://localhost:5173`) con `?cliente=DEMO` si tu instalación lo exige.

---

## 3. Entrar al diseñador (mismo flujo todas las pruebas)

1. Login (p. ej. `admin` / contraseña de lab).
2. Ir a **`/emisiones/disenador?cliente=DEMO`** (o menú **Soporte Técnico → Diseñador de emisiones**).
3. En **Elegir proceso**, seleccioná **Consulta detallada** (Partes).
4. Pulsá **Confirmar proceso**.
5. Esperá que cargue el diseñador DevExpress (barra superior `.dxrd-toolbar`).

**Smoke previo a la batería**

- **Field List** (lista de campos): deben figurar las **14** columnas del dataset (`fecha`, `clienteCode`, `clienteNombre`, `usuarioNombre`, `duracionMinutos`, `sinCargo`, etc.).
- Entrada al **asistente IA / Prompt-to-Report** (nombre puede variar según DX 26.1): suele estar en el asistente de **nuevo informe** o menú relacionado con **AI**. Si tu build muestra un campo de **prompt en lenguaje natural**, usalo para #1–#7.

Captura obligatoria: pantalla con Field List + evidencia de que IA está activa (captura `00-fieldlist-ia.png`).

---

## 4. Reglas durante la batería

1. **Prompts en español**, tal cual la tabla (podés copiar/pegar).
2. La IA **propone** layout; vos **revisás** en el lienzo.
3. **No** se considera guardado hasta **Guardar** o **Guardar como** en la barra del diseñador (icono disco / menú Archivo). Ver hint UI `emission.design.dxSaveHint`.
4. Anotá por prueba: **OK / Parcial / Fallo / Bloqueado** + 1–2 frases + **captura** (`01-…png`, …).
5. Si el layout usa un campo que **no** está en el Field List → **Fallo** (salvo prueba #7 donde se espera rechazo).

---

## 5. Pruebas #1–#8

### #1 — Listado por fecha y cliente

| | |
|---|---|
| **Prompt** | `Listá los partes por fecha y cliente` |
| **Éxito mínimo** | Tabla o bandas con **fecha** y **cliente** (nombre o código); solo campos del schema; nada de “rentabilidad” u otros inventados. |
| **Fallo** | Campos inexistentes, diseño vacío, error LLM sin recuperación. |
| **Evidencia** | Captura del diseño + Field List visible. |

---

### #2 — Agrupación

| | |
|---|---|
| **Prompt** | `Agrupá por cliente ordenados por fecha` |
| **Éxito mínimo** | Agrupación por cliente y orden por fecha (grupo + sort o bandas coherentes). |
| **Fallo** | Sin agrupación; orden ilógico; campos ajenos al schema. |

---

### #3 — Total horas / técnico

| | |
|---|---|
| **Prompt** | `Totalizá minutos por técnico y mostrá horas` |
| **Éxito mínimo** | Usa `duracionMinutos` y/o `usuarioNombre` (sinónimo “técnico” en schema); total o expresión numérica razonable; presentación en horas **aceptable** aunque sea aproximada (minutos÷60, summary, etc.). |
| **Parcial** | Total en minutos sin convertir a horas → anotar en F1. |
| **Fallo** | Ignora duración o inventa campos. |

---

### #4 — Filtro sin cargo

| | |
|---|---|
| **Prompt** | `Solo partes sin cargo` |
| **Éxito mínimo** | Condición o filtro sobre **`sinCargo`** (boolean), no datos ficticios. |
| **Fallo** | Sin filtro; filtra otro concepto. |

---

### #5 — Presentación

| | |
|---|---|
| **Prompt** | `Formato horizontal, título, encabezado, número de página` |
| **Éxito mínimo** | Orientación horizontal (o landscape), título visible, encabezado de página y numeración (Page Info o equivalente). |
| **Parcial** | Falta un solo elemento → describir. |

---

### #6 — Modificación (clave veredicto A vs B/C)

**Preparación:** abrí un diseño **ya existente** (reporte principal o uno guardado en TR-011), no empieces desde cero.

| | |
|---|---|
| **Prompt** | `Agregá subtotal de horas` |
| **Éxito mínimo** | Se **agrega** subtotal/summary de horas/minutos **sin borrar** el resto del layout (bandas, campos previos siguen). |
| **Fallo** | Reemplaza todo el informe; pierde controles; layout roto. |
| **F1** | Calidad buena → favorece **A**. Modificación pobre o inestable → considerar **B** o **C**. |

Captura **antes** y **después**.

---

### #7 — Campo inexistente (Must rechazo)

| | |
|---|---|
| **Prompt** | `Agregá rentabilidad de cada parte` |
| **Éxito mínimo** | Mensaje de error / imposible / “no hay campo rentabilidad”; **no** aparece columna inventada en el diseño. |
| **Fallo** | Crea campo `rentabilidad` o similar fuera del schema. |

---

### #8 — Save As + catálogo GEN-15

1. Con un diseño generado o abierto, usá **Guardar como** en el diseñador (nombre nuevo, p. ej. `Prueba-IA-bateria-8`).
2. Completá el flujo del host (si pide alta en catálogo).
3. Cerrá y volvé a **Emitir** en consulta detallada (opcional): el nuevo diseño debería listarse (no plantilla `is_standard`).

| | |
|---|---|
| **Éxito mínimo** | Save As sin error; código nuevo en catálogo; sidecar tiene `.repx` bajo `data/layouts/` (ops). |
| **Fallo** | `emission.design.saveAsFailed` o diseño no registrado. |

---

## 6. Después de la batería

1. Completá la tabla §4.4 en [`D-VERIFICACION-TR-015-2026-10-30.md`](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md).
2. Completá la **matriz §5** (licencia DX IA, calidad #1–#5, conclusión #6, regresión Save).
3. Firmá **una** fila:

   | Veredicto | Cuándo elegirlo |
   |-----------|-----------------|
   | **A** | #1–#5 aceptables, **#6 OK**, #7 rechaza bien, #8 OK → seguir con schema + runbook (oleada 2 mínima). |
   | **B** | Generar OK pero **#6 flojo** o modificación web no confiable → planear **Smart Capture** (TR-016 camino B). |
   | **C** | Mezcla: SC + Prompt-to-Report sidecar. |

4. Smoke rápido TR-011: **Emitir PDF** con un diseño guardado (opcional pero recomendado).

---

## 7. Problemas frecuentes

| Síntoma | Qué revisar |
|---------|-------------|
| Diseñador no carga | Sidecar caído, CORS, proxy `/DXXRD`, `hostMissing` en UI |
| Field List genérico (`id`, `label`) | Schema JSON no resuelve: `reportUrl` vs archivos en `data/schemas/` |
| Sin IA | Env LLM solo en sidecar; reiniciar sidecar; licencia DX IA |
| PDF en blanco al emitir | `PAQSUITE_EMISSION_DX_MODE=fake` en Forge (ver TR-011 F1) |
| Guardar no actualiza combo Emitir | Callback catálogo Laravel; ver consola red `POST/PUT .../reports` |

---

## 8. Plantilla de registro (copiar al F1)

```markdown
| # | Resultado | Notas | Captura |
|---|-----------|-------|---------|
| 1 | OK / Parcial / Fallo | | 01-....png |
| 2 | | | |
...
| 8 | | | |

Veredicto = A | B | C
Firmado: ______ Fecha: ______
```
