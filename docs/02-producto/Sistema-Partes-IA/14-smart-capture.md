# Smart Capture — carga de partes (tarea)

## Objetivo

Definir, en lenguaje de producto, la adopción de **Smart Capture** (asistente operativo GEN-03) en el **alta/edición de una tarea** de **Carga diaria** del módulo Partes de Atención.

Este documento es la **definición conceptual de producto**. No es SPEC, HU ni TR; alimenta la posterior generación Open-Spec (flujo SDD / Partes A→C).

Smart Capture **complementa** la carga manual: interpreta texto, voz e imagen y **propone** datos sobre el formulario abierto. El usuario **revisa** en el formulario y **confirma** la grabación. No reemplaza la grilla, ni la importación Excel, ni el Asistente IA documental del avatar.

---

## 1. Relación con el Framework (no reinventar)

Partes **adopta** el componente y el contrato de turno del Framework; especializa campos, catálogos, keywords y reglas de dominio.

| Norma Framework | Rol |
|-----------------|-----|
| [`02-producto/03-asistente-inteligente-smart-capture.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/03-asistente-inteligente-smart-capture.md) | Definición, UI embebida, contrato de turno, BYOK |
| Conceptualización SC | `docs/00-Conceptualizacion/02-componentes/smart-capture/` (Framework) |
| Patrón embebido | `…/03-patrones/ASISTENTE_OPERATIVO_EMBEBIDO.md` |
| Guía de adopción | `…/04-guias/COMO_INCORPORAR_SMART_CAPTURE.md` |
| Provider IA (`16`) | Misma BYOK / Preferencias LLM que el chat documental |
| Adopción BYOK | Framework [`adopcion-gen-16-byok.md`](../../../PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-gen-16-byok.md) — prereq BD+SP antes del panel |
| Chat documental (`21` / Partes `12`) | **Distinto canal** — no opera sobre el formulario |

**Regla:** no redefinir panel, modalidades ni shape genérico del turno. Partes declara **dónde se monta**, **qué campos/entidades** gobierna y **qué confirmaciones** de dominio aplica.

Paquete FE de referencia (host): `@paqsuite/react-core` → `SmartCapturePanel`, helpers de turno / `pendingChoice` / dictado / imágenes.

---

## 2. Distinción obligatoria (Partes)

| Capacidad | Propósito | Relación con este doc |
|-----------|-----------|------------------------|
| **Smart Capture** (este doc) | Completar / proponer datos en el **formulario de tarea** abierto | **Sí — alcance** |
| **Carga diaria (manual)** | Alta/edición/baja por UI clásica (grilla + modal) | Base; SC es complemento |
| **Importación Excel** (`13`) | Alta **masiva** por archivo | Canal distinto; no se mezcla |
| **Asistente IA (chat documental)** (`12`) | Explicar el sistema; **no** graba tareas | Fuera de este alcance |
| **Proceso masivo** | Cambios en lote sobre tareas ya existentes | Fuera de este alcance |

Anti-patrón: usar el Asistente IA del avatar para registrar partes, o montar Smart Capture sobre la **grilla** sin formulario abierto.

---

## 3. Dónde vive el proceso

### 3.1 Ubicación UI (cerrado)

| Decisión | Detalle |
|---------|---------|
| **D-SC-01** | Smart Capture se monta **dentro del modal** de alta/edición de tarea (Carga diaria), **debajo** del formulario (norma GEN: formulario = fuente de verdad). |
| **D-SC-02** | **No** se monta en la grilla de listado, ni en informes, dashboard, maestros ni proceso masivo. |
| **D-SC-03** | Alta y edición usan el **mismo** panel sobre el mismo formulario (draft de la tarea en curso). |

Coherente con GEN-03: «solo procesos que ameritan SC (carga / ABM); no en listados ni solo lectura».

### 3.2 Acceso / menú

- No agrega ítem de menú lateral.
- Visible cuando el usuario abre **Nueva tarea** o **Editar** en Carga diaria y el producto habilita el panel.
- Reutiliza el mismo gate de perfil que la carga: asistente / supervisor; **cliente no opera carga** (coherente con `02-actores-identidad-y-acceso.md`).

### 3.3 Mobile

**Fuera de alcance en mobile v1** (Capacitor): no montar Smart Capture en native. Coherente con exclusiones de operaciones avanzadas de carga y con Web Speech / panel DX pensados para web. Evolución aparte en `10-mobile.md` si se retoma.

---

## 4. Quién puede usarlo

| Actor | ¿Puede usar SC en carga? |
|-------|--------------------------|
| Asistente (no supervisor) | Sí |
| Supervisor | Sí |
| Cliente | **No** |

Sin credencial LLM válida (BYOK): el panel muestra el gate GEN «Ir a Preferencias» / `configurationRequired`; no se envían turnos.

---

## 5. Modalidades (adopción completa GEN)

| Modalidad | Alcance Partes |
|-----------|----------------|
| **Texto** | Must — prompt en el panel |
| **Audio / dictado** | Must — Web Speech del navegador (norma GEN actual) |
| **Imagen** | Must — hasta los límites GEN (máx. 4; 2 MB c/u), si el turno/host las admite |
| **Texto en archivo (`.txt`/`.md`)** | Must GEN — adjunto removable; contenido → `message` al enviar (no multimodal) |

Partes adopta la **solución Smart Capture completa** del Framework en modalidades; no inventa un subconjunto distinto salvo exclusión mobile (§3.3).

### 5.1 Composer (fuente de verdad GEN)

Norma Framework: producto [`03`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/03-asistente-inteligente-smart-capture.md) § Composer · SPEC-001-03 §3.1 · TR-GEN-03-panel-ui.

| Regla | Partes |
|-------|--------|
| Enviar habilitado | Texto en prompt **o** ≥1 adjunto |
| Enviar deshabilitado | Prompt vacío **y** sin adjuntos |
| Quitar adjunto | Lista con trash antes de enviar |
| Shift+Enter | Enviar (Enter = nueva línea) |

---

## 6. Campos del formulario (contrato de dominio)

### 6.1 Obligatorios para grabar

Para **confirmar/guardar** la tarea (por SC o por botón UI), deben estar resueltos:

| Campo | Notas |
|-------|--------|
| **Cliente** | Maestro clientes; lookup por **código** y **descripción/nombre** |
| **Fecha** | Fecha de trabajo del parte |
| **Duración** | Respeta tramo parametrizable del módulo (misma regla que carga diaria) |
| **Descripción** | Texto / observación del trabajo (campo esencial del dominio) |

### 6.2 Opcionales y defaults

| Campo | Regla |
|-------|--------|
| **Asistente** | Visible/seleccionable si el usuario es **supervisor**. Si no se informa: **asistente de la sesión** (propietario por defecto). Lookup por **código** y **descripción**. Asistente no supervisor: se fuerza el de sesión (no puede cargar “como otro”). |
| **Tipo de tarea** | Opcional en el mensaje SC. Si no se informa: **tipo de tarea marcado como default** en maestros (genérico; ver `04` / `08`). Lookup por **código** y **descripción**. Debe ser válido para el cliente (genéricos + asignados). |
| **Sin cargo** | Default **falso** si no se indica |
| **Presencial** | Default **falso** si no se indica |

### 6.3 Orden de ingreso (cerrado)

| Decisión | Detalle |
|---------|---------|
| **D-SC-04** | **No hay prioridad/orden obligatorio** de campos: el usuario/SC pueden aportar datos en **cualquier orden** en el mismo o sucesivos turnos. |

Especialización Partes respecto del gate genérico “raíz primero” (P7 GEN): en este proceso **no** se bloquea el resto del turno por falta de cliente; se aplica lo resoluble y se deja pendiente lo ambiguo o faltante hasta completar obligatorios al **guardar**.

### 6.4 Búsqueda en catálogos

Cliente, asistente (si aplica) y tipo de tarea se resuelven por coincidencia en **código** y/o **descripción** (texto libre del usuario / STT / visión).

---

## 7. Ambigüedad y elección (pendingChoice)

| Decisión | Detalle |
|---------|---------|
| **D-SC-05** | Si cliente, asistente o tipo de tarea tienen **más de una coincidencia**, el sistema **no elige en silencio**: lista numerada en el hilo del panel (patrón PedidosWeb / artículos) y el usuario elige por **número** (u opción clara). |
| **D-SC-06** | Mientras hay elección pendiente, se **conserva** el resto de datos ya interpretados / aplicados al draft (`pendingChoice` + diferidos GEN). |

Límite orientativo de opciones: el del Framework (~10 visibles).

---

## 8. Confirmaciones especiales

### 8.1 Fecha posterior al día (cerrado)

| Decisión | Detalle |
|---------|---------|
| **D-SC-07** | Si la **fecha** propuesta es **posterior a la fecha del día** (calendario del sistema / instalación), el sistema pide **confirmación explícita en el hilo antes de aplicarla al draft** (D-SC-16). Sin confirmar, la fecha futura **no** queda en el formulario. |

### 8.2 Tarea cerrada (cerrado)

| Decisión | Detalle |
|---------|---------|
| **D-SC-13** | Si la tarea en edición está **cerrada**, Smart Capture queda **deshabilitado** (panel no usable: sin envío de turnos / dictado / imágenes). La edición manual sigue las reglas ya vigentes de carga para tareas cerradas; SC no propone ni graba sobre cerradas. |

### 8.3 Timeout LLM y hint del panel (cerrado)

| Decisión | Detalle |
|---------|---------|
| **D-SC-14** | Timeout de llamada LLM: **reutilizar el del host del chat documental** (misma config / `.env` / BYOK del Asistente IA). No hay timeout específico Partes-SC en este MVP. |
| **D-SC-15** | Hint inicial del panel: **texto fijo Partes**, vía **i18n** (clave de producto; no parametrizable por `PQ_PARAMETROS` en este alcance). |

### 8.4 Palabras de confirmación de grabación (cerrado + sinónimos)

La persistencia por SC exige intención explícita de grabar (misma validación que el botón Guardar del modal).

| Decisión | Detalle |
|---------|---------|
| **D-SC-08** | Keywords canónicas de grabación (sin distinción relevante de mayúsculas): **`guardar`**, **`confirmar`**, **`procesar`**, **`grabar`**. |

**Sinónimos recomendados** (adoptar en SPEC/TR junto a los canónicos):

- **`registrar`**
- **`aceptar`**
- **`ok`** / **`dale`** (informales frecuentes en dictado)
- **`guardar tarea`** / **`confirmar tarea`** (frases compuestas)

Si faltan obligatorios al pedir grabar: el sistema indica qué falta; **no** graba a medias.

Conflictos con valores ya cargados en el form: visibles y confirmables (norma GEN).

---

## 9. Comportamiento esperado (lenguaje natural)

1. Usuario está en **Carga diaria**, abre **Nueva tarea** o **Editar**.
2. Si la tarea está **cerrada**, el panel SC está **deshabilitado** (D-SC-13); en alta o tarea abierta, bajo el formulario ve el panel (colapsable; norma GEN) con hint i18n Partes (D-SC-15).
3. Envía texto, dicta o adjunta imagen(es) describiendo la tarea (parcial o completa).
4. El sistema interpreta, aplica al draft lo resoluble, pide elección si hay ambigüedad; si la fecha es futura, pide confirmación **antes de aplicarla al draft** (D-SC-07 / D-SC-16).
5. El usuario ve el resultado en los controles del modal; puede corregir a mano.
6. Con obligatorios OK, el usuario dice una keyword de grabación **o** pulsa Guardar en la UI → misma validación y persistencia de dominio.
7. Tras grabar OK: se cierra el flujo de alta/edición según la UX actual del modal y la grilla se refresca con los filtros vigentes.

Información parcial: válido en cualquier momento; no se inventan datos faltantes.

En **edición**, el formulario ya tiene los datos de la tarea: el usuario/SC solo indica lo que quiere **cambiar**. Al grabar (keyword/`save` o botón Guardar) se valida que no falte ningún obligatorio en el draft resultante.

---

## 10. Relación con carga diaria e import Excel

| Capacidad | Propósito |
|-----------|-----------|
| **Carga diaria manual** | Formulario + grilla |
| **Smart Capture** (este doc) | Asistente **sobre el formulario** del modal |
| **Importación Excel** (`13`) | Lote desde archivo en toolbar de la grilla |

Las tres comparten **reglas de dominio** de una tarea (cliente, tipo, duración en tramos, marcas, `es_tarea`, etc.). Canales de captura distintos.

---

## 11. Decisiones cerradas (resumen)

| ID | Decisión |
|----|----------|
| **D-SC-01** | Montaje en **modal** de alta/edición, debajo del form |
| **D-SC-02** | No en grilla / listados / informes |
| **D-SC-03** | Mismo panel en alta y edición |
| **D-SC-04** | Sin orden obligatorio de campos |
| **D-SC-05** | Ambigüedad → lista numerada; elección del usuario |
| **D-SC-06** | Conservar draft / diferidos durante `pendingChoice` |
| **D-SC-07** | Fecha > hoy → confirmación explícita **al aplicar al draft** (no en silencio) |
| **D-SC-08** | Keywords grabación: guardar / confirmar / procesar / grabar (+ sinónimos §8.4) |
| **D-SC-09** | Modalidades: texto + audio + imagen (GEN completo en web) |
| **D-SC-10** | Mobile: fuera de alcance v1 |
| **D-SC-11** | BYOK obligatorio; misma credencial que chat documental |
| **D-SC-12** | Lookups por código **y** descripción (cliente, asistente, tipo) |
| **D-SC-13** | Tarea **cerrada** → SC **deshabilitado** |
| **D-SC-14** | Timeout LLM = el del **host chat** (sin timeout SC aparte) |
| **D-SC-15** | Hint inicial = **texto fijo Partes** (i18n) |
| **D-SC-16** | Momento de confirmación fecha futura = **al aplicar al draft** |
| **D-SC-17** | Keywords = guía LLM → action `save` (no matcher substring) |
| **D-SC-18** | Persistencia = FE aplica `save` → mismo API que Guardar UI |
| **D-SC-19** | Overwrite de campos/cliente al draft **sin** confirmación destructiva extra |
| **D-SC-20** | Edición: SC solo aporta cambios deseados; al grabar validar draft completo |

---

## 12. Fuera de alcance (este documento)

- Redefinir UI GEN del panel o el contrato genérico de turno.
- Smart Capture en grilla, informes, maestros o proceso masivo.
- Usar el chat documental (`12`) para grabar tareas.
- Rellenar Excel por IA / SC sobre importación (`13`).
- Mobile / Capacitor v1.
- RAG, historial servidor del hilo SC, o motor de voz distinto de Web Speech (evolución GEN).
- Inventar campos de tarea fuera del modelo de carga diaria.

---

## 13. Preguntas cerradas (2026-08-03)

| # | Tema | Resolución | Decisión |
|---|------|------------|---------|
| Q1 | Tarea cerrada + SC | **Deshabilitado** | D-SC-13 |
| Q2 | Timeout LLM | **Reutilizar el del host chat** | D-SC-14 |
| Q3 | Hint inicial del panel | **Texto fijo Partes (i18n)** | D-SC-15 |
| Q4 | Confirmación fecha futura | **Al aplicar al draft** | D-SC-07 / D-SC-16 |
| A1-1 | Keyword / save | **LLM → action save** (no substring) | D-SC-17 |
| A1-2 | Persistencia | **FE → mismo API Guardar** | D-SC-18 |
| A1-3 | Overwrite cliente/campos | **Sin** confirmación destructiva | D-SC-19 |
| A1-4 | Edición parcial | Solo cambios deseados; validar todo al grabar | D-SC-20 |

Sin preguntas abiertas de producto para arrancar SDD.

---

## 14. Resultado esperado para SDD

Con este documento **cerrado**, el flujo Open-Spec puede generar:

1. **[SPEC-010](../../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md)** — adopción GEN-03 en Carga diaria / modal tarea  
2. **HU** — criterios de aceptación trazables a D-SC-01…20  
3. **TR** — endpoint de turno Partes, `draftContext`, actions de dominio, tests, OpenAPI, i18n (incl. hint), `data-testid`

No debe reabrirse en SPEC: ubicación modal vs grilla (D-SC-01/02), distinción con chat documental / Excel, Q1–Q4 ni cierres A1 (D-SC-17…20).

---

## 15. Referencias internas Partes

- Operación: `05-operacion-diaria-y-supervision.md`
- Actores: `02-actores-identidad-y-acceso.md`
- Evolución previa SC: `07-fuera-de-alcance-y-evolucion.md` (actualizar estado: conceptualizado aquí)
- Chat documental: `12-asistente-ia-ayuda-y-chat-documental.md`
- Import Excel: `13-importacion-partes-excel.md`
- Mobile: `10-mobile.md`
