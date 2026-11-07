# Control de Calidad — PQ

| Campo | Valor |
|-------|--------|
| **ID archivo** | `00-ControlCalidad-PQ` |
| **Responsable** | Pablo Quarracino (PQ) |
| **Alcance** | Hallazgos de pruebas manuales y mejoras solicitadas por cliente en PaqSuite Partes de Atención |
| **Metodología** | Open-Spec / SDD — [`_OPEN-SPEC-METODOLOGIA.md`](../_base/_OPEN-SPEC-METODOLOGIA.md) |
| **Dispatcher** | Parte **G** (volcado), **H** (cierre opcional), ciclo **G → D → E → F → I** |

## Propósito

Registro operativo de **incidencias y mejoras** detectadas fuera del flujo automatizado de tests. Cada sesión de control se numera secuencialmente y conserva trazabilidad hasta su derivación a **SPEC-update**, **HU-update** y **TR-update** en `docs/.../updates/`.

Este archivo **no sustituye** SPEC, HU ni TR: es la **entrada** del circuito de correcciones (Parte G).

## Convenciones

| Tema | Regla |
|------|-------|
| **Fecha** | Formato `dd/MM/yyyy` en todo el documento |
| **Bloques** | `## Control de Calidad #N` — numeración incremental |
| **Ítems** | Preferir `### HU-XXX-slug` cuando la HU sea identificable |
| **Marcas de gestión** | `*Procesado*` tras volcado G; `*Sugerencia: HU-…*` si aún no hay HU asociable |
| **Comando de volcado** | `Corrige los errores del dd/MM/yyyy de PQ` (o *Realiza las mejoras…* / *Procesa las solicitudes…*) |

## Estados del bloque (*Referencia del control*)

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Control registrado; ítems sin volcar a `updates/` |
| **Con Sugerencias** | Volcado parcial: quedan ítems con sugerencia de HU sin archivo generado |
| **A Programar** | Todas las entradas marcadas `*Procesado*`; pendiente cierre formal G/H |
| **Especificado** | Parte G (o H) cerró el bloque: volcado documental completo; cola activa en `docs/.../updates/` — **no** implica código implementado ni **`Finalizado`** en metadatos de HU/TR |

> El **Estado** bajo *Referencia del control* es independiente del **Estado** en metadatos de HU/TR ([`07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)). Tras **Parte I**, los originales unificados quedan en **Finalizado**; el CC permanece como referencia histórica.

## Flujo tras registrar un control

1. Registrar hallazgos en el bloque `#N` con estado **Pendiente**.
2. Ejecutar **Parte G** (`Corrige… dd/MM/yyyy de PQ`): §0 SPEC-update si cambia el alcance; HU-update / TR-update.
3. Implementar (**D**), tests (**E**), verificación (**F**).
4. Marcar updates **`Finalizado`** (manual) y **Parte I** (unificar).

## Índice de controles

| # | Fecha | Estado | Resumen |
|---|-------|--------|---------|
| 1 | 31/07/2026 | Especificado | `es_tarea` + Paquete de Horas — F1/F Aprobado · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md)) |
| 2 | 01/08/2026 | Especificado | ERP clientes + informes — F1/F Aprobado · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-02-2026-08-01.md)) |
| 3 | 09/08/2026 | Especificado | Masivo tilde + carga decimal/Excel/plantillas/tipo default + Smart Capture controles — **F1/F Aprobado 16/09/2026** · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-03-2026-09-16.md)) |
| 4 | 04/10/2026 | Especificado | ABM asistentes/clientes + Paquete de Horas tipo cliente — **F1/F Aprobado 11/10/2026** · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-04-2026-10-04.md)) |
| 5 | 05/10/2026 | Especificado | Errores de validación en modal — **F1/F Aprobado 11/10/2026** · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/001-Generalidades/D-VERIFICACION-CC-PQ-05-2026-10-05.md)) |
| 6 | 11/10/2026 | Especificado | Deuda cableado SDK — PA-01…14 **F+I** (PA-03/04/14: 12/10/2026, pin 2.4.25-beta.1) |
| 7 | 19/10/2026 | Especificado | Mail acceso ABM Usuarios + informar clientes desde Proceso masivo (GEN Mail Engine; cola updates 002/005) |
| 8 | 25/10/2026 | Especificado · **F1/F Aprobado** · **Parte I** | Carga de paquetes desde 1 hora + destaque de paquetes + cálculo de saldo por renglón · [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) · [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-8-2026-10-25.md) |
| 9 | 30/10/2026 | Especificado | **Hito FRAMEWORK** — compatibilizar tablas de seguridad MONO vs MULTI (`users`, `pq_empresa`, roles, permisos) + GEN-34 |
| 10 | 30/10/2026 | Especificado | Tareas programadas (historial / próxima ejecución) + saldo informe Paquete de horas — [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-10-2026-10-30.md) |
| 11 | 01/11/2026 | Especificado | Proceso masivo paginación grilla — **F1/F Aprobado** · **Parte I unificada** · **Prod OK** (PR #117/#118) ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-11-2026-11-01.md)) |
| 12 | 02/11/2026 | Especificado | ABM maestros paginación · menú sin agrupadas · Emitir Paquete de Horas — **F1/F Aprobado** · **Preview OK** · **Prod OK** (PR #123 → `main`) — [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-12-2026-11-02.md) |
| 13 | 02/11/2026 | Especificado | Diseñador Paquete de Horas (dataset) · FilterRow maestros + Proceso masivo — **F1/F Aprobado** · **Parte I** · **Preview OK** 03/11/2026 (`88c46ea`) — [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-13-2026-11-03.md) |
| 14 | 03/11/2026 | Especificado | ABM Clientes — columnas usuario/mail + FilterRow usuario — **F1/F Aprobado** · **Parte I** · manual DEV OK — [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-14-2026-11-03.md) |
| 15 | 04/11/2026 | Especificado · **F1/F Aprobado** · **Parte I** cerrada | Informar por mail a clientes desde Proceso masivo (asistencias / revisar / observaciones) — refino CC #7 (URL tenant, i18n destinatario, guard salida, plantilla v1 hardcode) |
| 16 | 07/11/2026 | Especificado · **F1/F Aprobado con observaciones** | ABM Usuarios — notificar acceso **por usuario** (no masivo): ícono junto al mail, alta sin contraseña manual + envío automático, reenvío en edición — [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-16-2026-11-07.md) |

---

## Control de Calidad #16

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 07/11/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado · **F1/F Aprobado con observaciones** 07/11/2026 |
| **Parte G** | 07/11/2026 — [SPEC-002-update-02](../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update-02.md) · [HU-002-update-02](../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update-02.md) · [TR-002-update-02](../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update-02.md) |
| **F1/F** | [D-VERIFICACION-CC-PQ-16](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-16-2026-11-07.md) · [E-CC-PQ-16](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-16-2026-11-07.md) |
| **Origen** | Prueba manual preview (`partesatencion-dev`) post implementación **TR-002-update** / PR #143–#145: el operador esperaba notificación **en el contexto del usuario** (alta/edición), no un proceso aparte tipo “masivo”. |
| **Proceso** | Seguridad — ABM **Usuarios** (`/admin/usuarios`) · **GEN-06** `UsuariosAdminPage` (SDK) + wrapper host |
| **Relación** | **Corrige conceptualmente** el ítem «Envio mail a usuario para acceder al sitio» de **CC #7** §19/10/2026 y alinea SPEC-002-update / HU-002-update / TR-002-update con la intención original (botón en alta/edición, no selector masivo). **No** confundir con mail Proceso masivo (**CC #15**). |
| **Implementación actual (revertir / sustituir)** | Toolbar host `NotifyUsuarioAccessToolbar` + popup con `SelectBox` de usuarios (`NotifyUsuarioAccessDialog.tsx`); `data-testid` `adminUsuariosNotify`. |

### Resumen del error conceptual

La notificación de acceso **no** es un envío masivo ni un segundo proceso con combo de usuarios. Es una acción **por fila / por usuario en curso de alta o edición**: dar de alta (o reenviar en cualquier momento) las credenciales iniciales por mail, con contraseña generada por el sistema según `PasswordPolicy` parametrizada.

### Objetivo / mejora solicitada

#### ABM Usuarios — notificar acceso por usuario (refino CC #7 ítem 1)

*Procesado* (Parte G 07/11/2026) → [SPEC-002-update-02](../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update-02.md) · [HU-002-update-02](../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update-02.md) · [TR-002-update-02](../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update-02.md)

1. **Quitar el modo masivo:** eliminar el botón superior **«Notificar acceso»** y el diálogo que lista todos los usuarios para elegir uno. No debe existir un flujo “seleccionar usuario y notificar” desacoplado del ABM.
2. **Ícono de envío junto al mail:** en el popup de **alta** y **edición** (y consulta si el SDK la expone), un control de acción (ícono tipo *mail/send*) **al lado del campo Email**, habilitado **solo** si `email` no está vacío (tras trim). Deshabilitado sin mail; tooltip i18n (p. ej. “Notificar acceso por correo”).
3. **Alta sin contraseña manual:** en **alta**, el operador **no** ingresa contraseña. El backend (o el guardar del alta) genera clave aleatoria conforme a la política parametrizada (`PasswordPolicy` / generador ya usado en `notify-access`), persiste hash y `firstLogin=true`. El campo contraseña del formulario GEN-06 no se muestra en create (host +/o extensión SDK acordada; **no** fork completo de `UsuariosAdminPage`).
4. **Alta — envío automático:** al confirmar **Guardar** en alta con email válido, tras persistir el usuario, **disparar automáticamente** el mismo contrato que `POST .../notify-access` (un solo usuario recién creado). Si el mail falla: envelope de error en el **mismo contexto** del modal (regla 36); **no** cerrar sin aviso si el fallo es del envío post-guardado (criterio A1).
5. **Edición — reenvío bajo demanda:** con usuario ya persistido, el ícono junto al mail invoca `notify-access` para ese `id`. Habilitado solo con email; errores en el popup (regla 36).

**Contrato backend (mantener / extender):** `POST /api/v1/admin/usuarios/{id}/notify-access`; alta puede reutilizar notify post-`create` o unificar en un solo endpoint de alta+notify si el SPEC lo documenta. Envelope **GEN-20**. Mail con URL tenant + usuario + contraseña generada (vía notificación host actual). Sin exponer password en `resultado` JSON.

**GEN / Cloudsmith:** preferir **extensión** del SDK (`UsuariosFormPopup` / props `renderEmailActions`, ocultar password en create, callbacks `onNotify`) sobre fork de pantalla. Si el export no existe en el pin vigente, documentar bump Framework + ritual bump host; el CC **no** exige subir Cloudsmith por sí solo.

**Excluido:** mobile native (admin seguridad).

### Criterios de aceptación preliminares

- [x] **CA-16-01** No existe toolbar/diálogo masivo `adminUsuariosNotify` con selector de usuario. *(F1/F 07/11/2026)*
- [x] **CA-16-02** Ícono junto a **Email** en edición (disabled sin mail; `adminUsuariosNotifyEmail`). Alta: envío automático al guardar (sin ícono — ver D-VERIFICACION obs.). *(F1/F)*
- [x] **CA-16-03** Alta sin campo contraseña visible; clave generada server-side según política. *(F1/F)*
- [x] **CA-16-04** Tras **Guardar** en alta con email, se envía mail de acceso automáticamente (éxito o error visible en modal). *(F1/F)*
- [x] **CA-16-05** En edición, el ícono reenvía credenciales vía `notify-access` para el usuario abierto. *(F1/F)*
- [x] **CA-16-06** i18n 5 locales; errores regla 36; tests Vitest + Playwright + PHPUnit. *(F1/F)*

### Derivación SDD

| Paso | Artefacto |
|------|-----------|
| **G** | 07/11/2026 — SPEC/HU/TR-002-**update-02** (corrección UI CC #16) |
| **D** | Implementado 07/11/2026 — TR-002-update-02 |
| **E** | [E-CC-PQ-16](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-16-2026-11-07.md) |
| **F / F1** | **Aprobado con observaciones** — [D-VERIFICACION-CC-PQ-16](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-16-2026-11-07.md) |
| **Jira** | [PIAPARTES-44](https://paqsystems.atlassian.net/browse/PIAPARTES-44) |

**Siguiente:** Parte **N** (PR `develop`) + smoke preview manual; Parte **I** unificación SPEC-002 cuando corresponda.

---

## Control de Calidad #15

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 04/11/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado · **F1/F Aprobado** · **Parte I** cerrada |
| **Parte G** | 04/11/2026 — confirmado/alineado SPEC/HU/TR-005-update (CC #7 + refino #15); sin épica paralela |
| **Parte D/E/F** | 04/11/2026 — [D-CC-PQ-15](../04-tareas/updates/100-SistemaPartes/D-CC-PQ-15-2026-11-04.md) · [E-CC-PQ-15](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-15-2026-11-04.md) · [D-VERIFICACION-CC-PQ-15](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-15-2026-11-04.md) |
| **F1/F** | **Aprobado** 04/11/2026 — F1 código ↔ TR + PHPUnit/Vitest OK |
| **Parte I** | 04/11/2026 — fusionado en SPEC/HU/TR-005 base; updates 005 eliminados |
| **Origen** | Mejora solicitada — anuncio a clientes de asistencias registradas; refino operativo del ítem «Envio mail a clientes…» de **CC #7** (19/10/2026) |
| **Proceso** | **Proceso masivo** — `partes.masivo` · ruta `/partes/masivo` · `ProcesoMasivoPage` |
| **Jira** | [PIAPARTES-56](https://paqsystems.atlassian.net/browse/PIAPARTES-56) / [57](https://paqsystems.atlassian.net/browse/PIAPARTES-57) / [45](https://paqsystems.atlassian.net/browse/PIAPARTES-45) |
| **Relación** | Extiende / precisa CC #7 § «Envio mail a clientes para informar existencia de novedades»; no sustituye el mail de acceso ABM Usuarios (HU-002-update). **No** confundir con **CC #14** (columnas usuario/mail en ABM Clientes). |

### Resumen del hito

Enviar a clientes mails anunciando que la **empresa emisora** registró **asistencias** en su entidad, para que puedan **revisarlas**, estar al tanto y/o **realizar observaciones**.

### Objetivo / mejora solicitada

#### Mail «Informar a clientes» desde Proceso masivo

*Procesado* · cola **SPEC/HU/TR-005-update** (sin `-update-01`; mismo alcance formalizado)

1. **Disparo:** botón/ícono específico en el proceso **Proceso masivo** (no desde otros procesos).
2. **Contenido del mail (MUST):**
   - Identificar la **empresa que lo envía** (razón social / nombre de la empresa emisora).
   - Recordar el **link de acceso** canónico: `{tenant}.partesatencion.paqsystems.com` (URL completa `https://{tenant}.partesatencion.paqsystems.com`) y el **código de usuario** de acceso (`users.codigo`).
   - **No** incluir contraseña; si no la recuerda, recupero vía login (GEN-04).
   - Invitar a revisar las asistencias y, si corresponde, formular observaciones.
3. **i18n:** asunto y cuerpo respetan el idioma del sitio / destinatario (`users.locale`, catálogo producto en los 5 locales del host). El envío no debe quedar fijo en el idioma del supervisor.
4. **Destinatarios (MUST):** todos los clientes que cumplan **ambas** condiciones:
   - a) tengan **usuario** de la tabla `users` asociado (vía vínculo cliente ↔ usuario del producto);
   - b) **aparezcan en al menos un registro** de la grilla del proceso masivo en el universo filtrado actual (**no importa** si el renglón fue modificado o no; no se exige tilde).
5. **Plantilla v1:** texto **hardcodeado** (claves i18n fijas). **Fuera de alcance de este hito:** componente/ABM de armado de plantillas de mails predefinidos (evolución posterior).

#### Guard al abandonar el proceso

*Procesado* · mismo TR-005-update (RN-CC15-M03)

Si el supervisor **abandona la pestaña** (cambio de pestaña del shell, cambio de opción de menú, intento de cierre/recarga) **y** se realizaron **cambios** en el proceso masivo, **avisar** y **preguntar** si quiere **enviar** el mail a clientes antes de salir (diálogo i18n; cancelar debe conservar el trabajo en pantalla).

### Criterios de aceptación preliminares

- [x] **CA-15-01** En Proceso masivo hay un botón/ícono específico para informar a clientes (`data-testid` estable; tooltip i18n). *(F1/F 04/11/2026)*
- [x] **CA-15-02** El mail identifica la empresa emisora. *(F1/F)*
- [x] **CA-15-03** El mail incluye URL `https://{tenant}.partesatencion.paqsystems.com` y el código de usuario; sin contraseña. *(F1/F)*
- [x] **CA-15-04** Asunto/cuerpo en el locale del destinatario (5 locales). *(F1/F)*
- [x] **CA-15-05** Destinatarios = clientes con `users` asociado **y** presentes en ≥1 fila del universo filtrado (todas las páginas; sin exigir modificación ni selección). *(F1/F)*
- [x] **CA-15-06** Al salir con cambios pendientes, diálogo ofrece enviar / salir sin enviar / cancelar. *(F1/F código + Vitest; smoke UI opcional post-deploy)*
- [x] **CA-15-07** Texto v1 hardcodeado vía i18n; sin editor de plantillas. *(F1/F)*

### Notas para Parte G

- Reutilizar / alinear la cola **SPEC/HU/TR-005-update** (origen CC #7); no abrir épica paralela salvo ambigüedad A1.
- Motor de envío: Mail Engine GEN / futuro **GEN-30** (no SMTP ad-hoc en el host).
- Acceso datos: MUST SP; sin `WHERE IN` de N incierto (regla 77).
- Excluido mobile (Proceso masivo ya fuera de MVP native).

### Derivación SDD

| Paso | Artefacto |
|------|-----------|
| **G** | 04/11/2026 — cola confirmada (sin update-01) |
| **D/E/F** | [D-CC-PQ-15](../04-tareas/updates/100-SistemaPartes/D-CC-PQ-15-2026-11-04.md) · [E-CC-PQ-15](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-15-2026-11-04.md) · [D-VERIFICACION-CC-PQ-15](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-15-2026-11-04.md) |
| **I** | Fusionado en [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) / [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) / [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) |

**Cierre:** F1/F **Aprobado**, **Parte I** 04/11/2026. Smoke UI preview opcional post-deploy.

---

## Control de Calidad #14

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 03/11/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado · **F1/F Aprobado** · **Parte I** cerrada |
| **Parte G** | 03/11/2026 — SPEC/HU/TR-update CC #14 (003) |
| **Parte D/E/F** | 03/11/2026 — [D-CC-PQ-14](../04-tareas/updates/100-SistemaPartes/D-CC-PQ-14-2026-11-03.md) · [E-CC-PQ-14](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-14-2026-11-03.md) · [D-VERIFICACION-CC-PQ-14](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-14-2026-11-03.md) |
| **F1/F** | **Aprobado** 03/11/2026 — smoke manual DEV (tenant **DEV**); regresión E OK |
| **Parte I** | 03/11/2026 — fusionado en SPEC/HU/TR-003 base |
| **Origen** | Prueba manual post **CC #13** (preview): ABM **Clientes** y **Asistentes** — visibilidad de usuario en listado y FilterRow |
| **Proceso** | Maestros Partes — `partes.maestros.clientes` · `partes.maestros.asistentes` ([HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md)) |

### Resumen del hito

| # | Ítem | Notas |
|---|------|--------|
| 1 | **ABM Clientes** — columnas **usuario** y **mail** en la grilla | Hoy el modal incluye `userId` y `email`; la grilla no los muestra (solo código, nombre, tipo, ERP, activo). |
| 2 | **ABM Asistentes** — FilterRow no filtra por **usuario** | El resto de columnas con FilterRow responden; la columna de usuario (`usuarioCodigo`) no acota filas. |
| 3 | **ABM Clientes** — FilterRow **usuario** al incorporar columna | Misma corrección que ítem 2: al exponer usuario en grilla, verificar filtro remoto coherente con paginación (CC #12/#13). |

### Hallazgos

#### 1) ABM Clientes — usuario y mail ausentes en grilla

Desde la grilla no se identifica qué **usuario de acceso** ni qué **mail** tiene cada cliente sin abrir el modal de edición.

**Expectativa:** columnas visibles alineadas al formulario (código de usuario vinculado y correo), con i18n existente (`partes.maestros.field.*`).

#### 2) ABM Asistentes — FilterRow en columna usuario

Tras **CC #13**, FilterRow en nombre/código/supervisor/activo funciona; al filtrar por **usuario** la grilla no reduce resultados (comportamiento distinto al resto).

**Triage preliminar (código host):**

| Capa | Observación |
|------|-------------|
| **FE** | `maestroGridLoadOptionsToQuery` (`maestroListPaging.ts`) mapea FilterRow solo a query `code`, `nombre`, `descripcion` — no incluye `usuarioCodigo` / `email`. |
| **API** | Revisar si `GET /api/v1/partes/asistentes` (y `clientes`) acepta filtro por usuario/mail en listado remoto; extender SP/list si falta contrato. |

#### 3) ABM Clientes — FilterRow usuario (regresión explícita)

Al agregar la columna de usuario en Clientes, **obligatorio** validar FilterRow sobre ese campo (y, si aplica, mail) en entorno con paginación remota — no repetir el gap del ítem 2.

### Mejoras solicitadas

#### HU-003-maestros-y-catalogos — ABM Clientes (grilla) + FilterRow usuario

*Procesado* · **Parte I** → [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

- Agregar a la grilla del ABM **Clientes** las columnas **usuario** (código/login vinculado) y **mail** (mismo payload list API).
- Corregir FilterRow remoto para la columna **usuario** en **Asistentes**.
- Al incorporar usuario/mail en **Clientes**, FilterRow equivalente (contains / query API).
- Tests de regresión en `maestroListPaging` + integración list API si cambia query.

### Criterios de aceptación

- [x] **CA-14-01** ABM Clientes muestra columnas **usuario** y **mail** en la grilla, con valores coherentes al modal. *(F1/F manual DEV 03/11/2026)*
- [x] **CA-14-02** ABM Asistentes: FilterRow en columna **usuario** reduce filas según texto ingresado (paginación remota). *(F1/F manual DEV)*
- [x] **CA-14-03** ABM Clientes: FilterRow en columna **usuario** (y **mail** si es filtrable) funciona igual que nombre/código. *(F1/F manual DEV)*
- [x] **CA-14-04** Sin regresión de paginación remota ni FilterRow de CC #12/#13 en otros maestros. *(F1/F manual DEV)*

### Derivación SDD

| Ítem | Updates |
|------|---------|
| Columnas + filtros maestros | Unificado Parte I en SPEC/HU/TR-003 (updates CC #14 eliminados) |

**Cierre:** F1/F **Aprobado**, **Parte I** 03/11/2026 — [D-VERIFICACION-CC-PQ-14 §1–§4](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-14-2026-11-03.md). **Prod/preview** pendiente de deploy del código host.

---

## Control de Calidad #13

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 02/11/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **Parte G** | 02/11/2026 — SPEC/HU/TR-update CC #13 (003 / 005 / 011) |
| **Parte D/E/F** | 03/11/2026 — [D-CC-PQ-13](../04-tareas/updates/100-SistemaPartes/D-CC-PQ-13-2026-11-03.md) · [E-CC-PQ-13](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-13-2026-11-03.md) · [D-VERIFICACION-CC-PQ-13](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-13-2026-11-03.md) |
| **F1/F** | **Aprobado** 03/11/2026 — F1 §1.1 + regresión E OK; smoke §4 **OK** DEV + **preview** (`v1.3.0-CLOUDSMITH` @ `88c46ea`, PQ) |
| **Parte I** | 03/11/2026 — fusionado en SPEC/HU/TR-003, 005, 011 base (cierre acotado) |
| **Origen** | Prueba manual post **CC #12** (prod tenant **PQ**): diseñador Paquete de horas, maestros, Proceso masivo |
| **Evidencia** | Capturas CC #13: Field List `id`/`label`; FilterRow **ANKAS** en Clientes sin acotar filas |

### Resumen del hito

| # | Ítem | Notas |
|---|------|--------|
| 1 | Diseñador **Paquete de horas** — dataset negocio en Field List | `partes.informes.paqueteHoras` |
| 2 | **FilterRow** remoto maestros + **Proceso masivo** | Sin regresión paginación CC #11/#12 |

### Criterios de aceptación

- [x] **CA-13-01** Field List Paquete de horas con columnas de negocio *(F1/F preview 03/11/2026)*
- [x] **CA-13-02** Clientes FilterRow nombre/código *(F1/F preview)*
- [x] **CA-13-03** Asistentes FilterRow *(F1/F preview)*
- [x] **CA-13-04** Proceso masivo FilterRow cliente/tipo tarea *(F1/F preview)*
- [x] **CA-13-05** Tests regresión *(ver E-CC-PQ-13)*

### Derivación SDD

| Ítem | Updates |
|------|---------|
| Diseñador | [SPEC-011-update](../05-open-spec/updates/100-SistemaPartes/SPEC-011-update-cc-pq-13-2026-11-02-designer-paquete-horas-dataset.md) · [TR-011-update](../04-tareas/updates/100-SistemaPartes/TR-011-update-cc-pq-13-2026-11-02-designer-paquete-horas-dataset.md) |
| FilterRow maestros | [SPEC-003-update](../05-open-spec/updates/100-SistemaPartes/SPEC-003-update-cc-pq-13-2026-11-02-filterrow-maestros.md) · [TR-003-update](../04-tareas/updates/100-SistemaPartes/TR-003-update-cc-pq-13-2026-11-02-filterrow-maestros.md) |
| FilterRow masivo | [SPEC-005-update](../05-open-spec/updates/100-SistemaPartes/SPEC-005-update-cc-pq-13-2026-11-02-filterrow-masivo.md) · [TR-005-update](../04-tareas/updates/100-SistemaPartes/TR-005-update-cc-pq-13-2026-11-02-filterrow-masivo.md) |

Plan D1: [D1-TR-003-005-011-update-cc-pq-13](../04-tareas/updates/100-SistemaPartes/d1/D1-TR-003-005-011-update-cc-pq-13.md).

**Hito FRAMEWORK (causa raíz):** el Field List del diseñador no debe depender de despliegue manual al sidecar ni de hardcode C# por producto. Seguimiento en [PaqSuite-IA-FRAMEWORK — Control de Calidad #13](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/00-ControlCalidad/00-ControlCalidad-PQ.md#control-de-calidad-13) (**GEN-15 + GEN-34:** definición del dataset de reportes nuevos/modificados en **instalación/actualización**).

**Cierre:** F1/F **Aprobado**, **Parte I** y **Preview OK** 03/11/2026 — [D-VERIFICACION §1.1, §6, §8](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-13-2026-11-03.md). **Prod** pendiente (PR → `develop` → `main`). Hito FRAMEWORK (schema en GEN-34) sigue en [Framework CC #13](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/00-ControlCalidad/00-ControlCalidad-PQ.md#control-de-calidad-13).

---

## Control de Calidad #12

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 02/11/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **Parte G** | 02/11/2026 — SPEC/HU/TR-update CC #12 |
| **Parte D/E/F** | 02/11/2026 — ver [D-CC-PQ-12](../04-tareas/updates/100-SistemaPartes/D-CC-PQ-12-2026-11-02.md) · [E-CC-PQ-12](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-12-2026-11-02.md) · [D-VERIFICACION-CC-PQ-12](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-12-2026-11-02.md) |
| **F1/F** | Aprobado 02/11/2026 — smoke API preview develop (tenant DEV); ver [D-VERIFICACION-CC-PQ-12](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-12-2026-11-02.md) |
| **Smoke preview** | 02/11/2026 — **OK** API (PR #124/#125; SHA `eafa6fe`). UI Vercel no verificada (SSO). |
| **Cierre prod** | 02/11/2026 — PR [#123](https://github.com/paqsystems/PaqSuite-IA-Partes-Atencion/pull/123) → `main` (SHA `57f2438`); smoke API tenant **PAQ** OK (menú, clientes `total=192`, emisión paquete horas). |
| **Origen** | Prueba manual en **producción** (UI en italiano) — hallazgos acumulados en sesión de control |
| **Evidencia** | Captura CC #12 (02/11/2026): ABM **Clienti** (`/archivos/partes/clientes`) con grilla que no supera ~50 filas pese a **192** registros en `pq_partes_clientes` |

### Resumen del hito (alcance por producto)

| # | Ítem | Proyecto | Notas |
|---|------|----------|--------|
| 1 | ABM Clientes (y resto de maestros Partes) limitado a 50 filas | **Partes** | Revisión transversal ABMs; reutiliza motor GEN (`ProcessDataGrid`) sin cambio de contrato Framework salvo que el fix sea transversal documentado |
| 2 | Quitar del menú **Consultas agrupadas** | **Partes** | Menú `pq_menus` / seed + coherencia rutas y mobile policy |
| 3 | **Emitir** en informe **Paquete de Horas** | **Partes** (emisión vía GEN-15 existente) | Hoy excluido por SPEC-011 / HU-011; ampliar alcance acordado con negocio |

### Hallazgos

#### 1) ABM Clientes — solo 50 registros visibles

En producción el ABM **Clienti** muestra como máximo **50** clientes. En base hay **192** filas en `pq_partes_clientes`. El usuario no puede administrar el catálogo completo desde la grilla.

**Triage preliminar (código host):**

| Capa | Observación |
|------|-------------|
| **API** | `GET /api/v1/partes/clientes` — `PartesMaestrosController::list` usa `pageSize` por defecto **50** si el cliente no envía query. |
| **FE** | `MaestroCrudPage` → `listPartesResource` **sin** `page` / `pageSize`; carga una sola página en `rows` y pagina **en cliente** con `<Paging defaultPageSize={20} />`. |
| **Patrón** | Mismo componente alimenta **Asistentes**, **Tipos cliente**, **Tipos tarea** (`PartesMaestrosPages.tsx`). **Asignaciones cliente–tipo tarea** (`ClienteTiposTareaPage`) usa otro listado — incluir en auditoría. |

**Hito solicitado:** corregir listado maestros para que el universo administrable coincida con el total en BD (paginación remota alineada al API **o** carga completa documentada con límite de producto), y **verificar todos los ABMs Partes** para el mismo anti-patrón (CC #11 ya corrigió Proceso masivo).

*Procesado* · fusionado Parte I en [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

#### 2) Menú — eliminar **Consultas agrupadas**

Solicitud de negocio: la opción de menú **Consultas agrupadas** / **Consultazioni raggruppate** (`partes_consultas_agrupadas`, ruta `/partes/informes/consultas-agrupadas`) **no debe ofrecerse** en producción.

Alcance mínimo esperado:

- Retirar ítem del menú (`pq_menus` seed / installation update) y permisos asociados si aplica.
- Decidir si la ruta queda inaccesible (404 / redirect) o solo oculta — documentar en SPEC-update.
- Actualizar manual usuario y `partesMobilePolicy` si el ítem seguía referenciado.
- **No** implica borrar código pivot/agrupadas salvo decisión explícita posterior; foco en **no exponer** el proceso.

*Procesado* · fusionado Parte I en [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

#### 3) Informe **Paquete de Horas** — agregar **Emitir**

Solicitud: el informe **Paquete de Horas** (`/partes/informes/paquete-horas`, `PaqueteHorasPage.tsx`) debe ofrecer **Emitir** (mismo patrón GEN que Consulta Detallada: `EmissionDialog`, filtros de pantalla, contrato SPEC-011).

Estado actual documentado: SPEC-011 / HU-011 **excluyen** Paquete de horas de Emitir; `partesConsultasEmissionsGate.test.ts` exige que agrupadas **no** monten el diálogo; Paquete de Horas tampoco lo monta hoy.

**Cerrado (PQ):** emisión sobre **filas tildadas** (paridad CC-ST #1). Proceso `partes.informes.paqueteHoras`; mobile sin Emitir.

*Procesado* · fusionado Parte I en [HU-011](../03-historias-usuario/100-SistemaPartes/HU-011-reportes-emisiones.md) · [TR-011](../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md)

### Criterios de aceptación preliminares

- [x] **CA-12-01** Con **> 50** clientes activos en BD, el ABM Clientes permite ver/administrar **todos** (paginación remota coherente con `total` del API o carga completa acordada).
- [x] **CA-12-02** Asistentes, tipos cliente, tipos tarea y asignaciones cliente–tipo tarea **no** quedan capados en 50 filas sin aviso si el volumen supera el default del API.
- [x] **CA-12-03** Tras deploy, ningún perfil ve **Consultas agrupadas** en el menú Partes; la ruta no queda accesible por menú estándar.
- [x] **CA-12-04** Paquete de Horas muestra **Emitir** en web (filas tildadas) y respeta permisos / envelope GEN-15.
- [x] **CA-12-05** Tests de regresión (unit/E2E) cubren listado maestros ampliado y gate de emisiones actualizado.

### Derivación SDD

| Artefacto | Ruta |
|-----------|------|
| SPEC base | [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [SPEC-011](../05-open-spec/100-SistemaPartes/SPEC-011-reportes-emisiones.md) |
| D / E / F | [D-CC-PQ-12](../04-tareas/updates/100-SistemaPartes/D-CC-PQ-12-2026-11-02.md) · [E-CC-PQ-12](../04-tareas/updates/100-SistemaPartes/E-CC-PQ-12-2026-11-02.md) · [D-VERIFICACION-CC-PQ-12](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-12-2026-11-02.md) |

Parte **I** aplicada (02/11/2026): fusionado en SPEC/HU/TR base; updates CC #12 eliminados.

**Comando de volcado:** `Corrige los errores del 02/11/2026 de PQ`

---

## Control de Calidad #11

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 01/11/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **Origen** | Prueba manual en entorno **v1.1.0** (UI en italiano): la grilla muestra **Totale filtro: 418** pero el paginador DevExtreme solo ofrece la página **1**, aun con tamaño de página **40**. |
| **Parte G** | 01/11/2026 — volcado SPEC/HU/TR-update CC #11 |
| **Parte E** | 01/11/2026 — Vitest 191 OK; E2E `partes-masivo-paging` + `partes-masivo-selection` OK |
| **F1/F** | Aprobado 01/11/2026 — prueba manual PQ en preview develop; ver [D-VERIFICACION-CC-PQ-11](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-11-2026-11-01.md) |
| **Parte I** | 01/11/2026 — fusionado en SPEC/HU/TR-005 base; updates CC #11 eliminados |
| **Parte I acotada** | 01/11/2026 — follow-up prod (`RemoteOperations` solo paging) documentado en SPEC/HU/TR-005 base + D (sin updates) |
| **Cierre prod** | 01/11/2026 — PR [#118](https://github.com/paqsystems/PaqSuite-IA-Partes-Atencion/pull/118) → `main`; PQ OK `paq.partesatencion…` |
| **Proceso (probable)** | **Proceso masivo** — `partes.masivo` · ruta host `/partes/masivo` · componente `ProcesoMasivoPage.tsx` |
| **Evidencia** | Captura CC #11 (01/11/2026): agrupación por tipo de actividad, selector de filas, leyenda «Totale filtro: 418», pager con único botón «1». |

### Hallazgos

1. El usuario interpreta que existen **418 filas** en el universo del filtro (coherente con el texto **Totale filtro**).
2. No puede avanzar a páginas 2…N desde la grilla; el paginador no refleja el total declarado.
3. Comportamiento observado con filtros de fecha que incluyen fin de octubre 2026 y agrupación por columna de tipo de actividad (persistida vía preferencias de grilla / `ProcessDataGrid`).

**Triage preliminar (código host, sin cerrar causa raíz):**

| Capa | Observación |
|------|-------------|
| **API** | `listTareas` se invoca con `page: 1` y `pageSize` fijo (20 en código); la respuesta expone `total` global (p. ej. 418) e `items` de **una sola página**. |
| **UI** | `dataSource={rows}` alimenta la grilla con **solo esa página**; `<Paging>` / `<Pager>` paginan **en cliente** sobre ese subconjunto → una sola página visible aunque `total` sea mayor. |
| **Leyenda** | «Totale filtro» usa `total` del envelope (**418**), no `rows.length` → **desalineación** entre mensaje y paginador. |
| **Selección masiva** | Existe flujo aparte (`listTareaIds`) para «seleccionar todo el universo»; no sustituye navegar página a página en la grilla. |

**Pantallas a descartar en la investigación:** Consulta detallada carga con `pageSize: '0'` (un solo fetch del universo) y **no** muestra «Totale filtro» en el host; si el síntoma se reproduce fuera de Proceso masivo, ampliar alcance del hito.

### Hito solicitado

*Procesado* (Parte G 01/11/2026) → unificado Parte I en [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) · [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md)

**Revisar y corregir la paginación del universo filtrado** en Proceso masivo (y, si aplica el mismo patrón, otras grillas Partes con total server-side + `ProcessDataGrid`):

1. **Diagnóstico formal** — confirmar en red (DevTools) que `GET` listado devuelve `total` ≫ `items.length` mientras la grilla no dispara requests al cambiar de página.
2. **Decisión de diseño** — una de:
   - **Paginación remota** DevExtreme (`CustomStore` / `remoteOperations`) alineada a `page` + `pageSize` del API y `total` del envelope; o
   - **Carga completa** del universo filtrado cuando el volumen esté acotado por producto (documentar límite); o
   - **Coherencia UX**: si solo se muestra la primera página en grilla, no mostrar un total que implique páginas navegables (solo si el producto acepta ese recorte).
3. **Implementación** — respetar selección múltiple, agrupación, preferencias de grilla GEN, i18n (`partes.common.totalFiltro`) y `data-testid` existentes.
4. **Regresión** — escenario E2E o integración: filtro con **> pageSize** filas → paginador con **> 1** página y datos distintos al cambiar página; total coherente con API.

Decisión documentada en SPEC-update: **paginación remota** DevExtreme ↔ `GET /api/v1/partes/tareas` (`page`, `pageSize`, `total`).

### Criterios de aceptación preliminares

- [x] **CA-11-01** Con un filtro que devuelva **418** (o cualquier total > tamaño de página), el paginador permite ir a la **última página** y los renglones cambian respecto de la primera.
- [x] **CA-11-02** «Totale filtro» (o texto acordado) es **coherente** con lo que la grilla puede navegar o se documenta explícitamente el alcance (p. ej. solo página actual).
- [x] **CA-11-03** Cambiar tamaño de página (20 / 40 / 80) recalcula páginas y mantiene alineación con el API.
- [x] **CA-11-04** Agrupación por columnas (tipo actividad, etc.) no rompe paginación ni vacía filas de datos sin error en UI/red.
- [x] **CA-11-05** Selección por fila y flujos de cierre/aplicar campos siguen operativos en páginas distintas de la primera.

### Derivación SDD

Parte G **01/11/2026** — volcado updates CC #11. Implementación en `ProcesoMasivoPage` (`CustomStore`, `remoteOperations`). Parte E/F **01/11/2026** — tests + manual PQ. Parte I **01/11/2026** — SPEC/HU/TR-005 base **Finalizado**. Follow-up prod **01/11/2026** — PR #117/#118, `<RemoteOperations paging … summary={false} />`; Parte I acotada + cierre PQ prod en [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-11-2026-11-01.md).

---

## Control de Calidad #10

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 30/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **Origen** | Verificación post-deploy **beta.14** (`laravel-core` 1.3.18-beta.14, Forge **81700511**, PR #98–#102): el usuario reporta dos síntomas que **persisten** y **no** se explican por el alcance del release. |
| **Conclusión de triage (30/10/2026)** | **No causados por esta actualización** — ver §Hallazgos. |
| **F1/F** | GEN-13 **Aprobado** (Parte I 06/11/2026) · saldo informe **CC #8 Parte I** 06/11/2026 → [D-VERIFICACION-CC-PQ-8](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-8-2026-10-25.md) |

### Hallazgos

#### 1) Tareas programadas — historial y columna «Próxima ejecución» vacíos o ausentes

**Alcance del deploy beta.14 (Partes):** manifiesto/post-deploy, SP integración maestros (collation/cursor), pin `laravel-core`, fix CI Cloudsmith. **Sin cambios** en `frontend/src/features/tareasProgramadas/**` respecto de la causa de este ítem; el pin FE vigente del host quedó evolucionando tras beta.14 (p. ej. **2.4.30-beta.9** al 2026-11 — ver `ritual-bump-sdk.md`).

**Alcance `laravel-core` beta.14 vs beta.13:** un único commit — `StoredProcedureTaskExecutionRepository`: SP **sin result set** (`save_snapshot`, `append_log`) vía `statement` (fix sqlsrv IMSSP). **No** altera `listExecutions` ni APIs de definiciones / `nextRunAt`.

**UI:** historial = `TaskExecutionsPage`; próxima ejecución = columna `nextRunAt` en `TaskDefinitionsPage` (SDK). Revisar después: datos en `pq_task_execution`, respuesta `GET /api/v1/task-executions`, cálculo `nextRunAt` en definiciones, scheduler/cron Forge (GEN-13), permisos menú.

#### 2) Informe **Paquete de horas** — saldo incorrecto

**Alcance del deploy:** se reaplicó el mismo `pq_sp_partes_informe_paquete_horas.sql` (checksum sin cambio desde commit **f66a3a6**, CC #8). PR #98–#101 **no** modificaron ese SP ni `PaqueteHorasPage.tsx`.

**Trazabilidad:** ítem original **CC #8** (PIAPARTES-52) — saldo por renglón; **Parte I 06/11/2026** en [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md).

### Mejoras solicitadas / seguimiento

#### Tareas programadas — historial y próxima ejecución

*Procesado* (Parte G 30/10/2026) · **Parte I 06/11/2026 — Finalizado** → [SPEC-001-13](../05-open-spec/001-Generalidades/SPEC-001-13-host-adopcion-tareas-programadas.md) · [HU-GEN-13](../03-historias-usuario/001-Generalidades/HU-GEN-13-adopcion-tareas-programadas.md) · [TR-GEN-13](../04-tareas/001-Generalidades/TR-GEN-13-adopcion-tareas-programadas.md)

- Adapter host sqlsrv: drenado de rowsets en listados de definiciones y ejecuciones.
- Comando `partes:tasks-backfill-next-run-at` para definiciones legacy sin `next_run_at`.
- Repositorio ejecuciones: escrituras sqlsrv + mensaje en historial; resolver backup MONO; gateway S3 sin ARN duplicado; runbook `TASKS_BACKUP_S3_ARN_PREFIX` (adopción GEN-13).

#### Informe Paquete de horas — saldo

*Procesado* (Parte G 30/10/2026) · **Parte I 06/11/2026** → [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) · SP `pq_sp_partes_informe_paquete_horas` (window `PARTITION BY cliente_id`); [D-VERIFICACION-CC-PQ-8](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-8-2026-10-25.md).

### Criterios de aceptación preliminares

- [x] **CA-10-01** Tras fix, historial lista ejecuciones cuando existen filas en BD; sin error silencioso en red.
- [x] **CA-10-02** Definiciones activas muestran `nextRunAt` coherente con cron/schedule (o mensaje explícito si no aplica).
- [x] **CA-10-03** Informe Paquete de horas: saldo inicial + running por cliente cumple CC #8 / doc 18. *(cerrado Parte I CC #8 06/11/2026)*
- [x] **CA-10-04** Evidencia en D-VERIFICACION de que el síntoma **no** era regresión de beta.14 (referencia a este triage).

### Derivación SDD

Parte G **30/10/2026** — cola `updates/` (GEN-13 CC #10 + saldo TR-006 / CC #8). Implementación host en `develop`; smoke sqlsrv Forge post-deploy.

Parte I **06/11/2026** — familia GEN-13 unificada a ruta base y **Finalizado** (backup RDS verificado). CA-10-03 (saldo informe) cerrado con CC #8 Parte I el mismo día.

---

## Control de Calidad #9

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 30/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **Proyecto de ejecución** | **FRAMEWORK** (`PaqSuite-IA-FRAMEWORK` / `paqsuite/laravel-core`) — hosts MONO (Partes) consumen el contrato unificado |
| **Origen** | Deploy Forge 30/10/2026: `paqsuite:installation:update` falla en `pq_sp_admin_empresa_list` (`Invalid column name 'IDEmpresa'`) por desalineación dictionary TANGO vs esquema MONO del host |
| **Referencias host** | [`integracion-framework-sdk.md`](../01-arquitectura/integracion-framework-sdk.md) § huecos · [`mono-abm-empresas-gen06.md`](../01-arquitectura/mono-abm-empresas-gen06.md) · [`md-seguridad.md`](../modelo-datos/md-seguridad.md) (MONO vs legado MULTI) · GEN-34 [`RELEASE-PARTES.md`](../06-operacion/RELEASE-PARTES.md) |

### Hallazgos

Existen **dos contratos físicos** para el mismo dominio de seguridad bajo nombres parecidos:

| Ámbito | MULTI / dictionary (Framework, Tango) | MONO / host Partes (unified DB) |
|--------|----------------------------------------|----------------------------------|
| Empresa | `pq_empresa`: `IDEmpresa`, `NombreEmpresa`, `Habilita`, `NombreBD`, ERP… | `pq_empresa`: `id`, `nombre`, `activo`, `theme` |
| SP admin empresas | `pq_sp_admin_empresa_*` (singular) en manifiesto Framework | `pq_sp_admin_empresas_*` (plural) en host |
| Usuarios / roles / permisos | Legado `Pq_Rol`, `Pq_Permiso`, casing ERP | Laravel: `users`, `pq_roles`, `pq_permisos`, `pq_rol_atributos` |

El pipeline **GEN-34** ejecuta SQL del Framework **sin bifurcar** por `PAQSUITE_TENANCY=single`, lo que **rompe deploy** (y puede **eliminar** SP del host antes del error).

### Hito solicitado (FRAMEWORK)

**Compatibilizar estructuras de tablas de seguridad entre MULTI y MONO** con contrato explícito, versionado y aplicable en `installation:update` / manifiesto merge.

Entidades en alcance (nombres lógicos; unificar DDL + SP + DTO API):

| Entidad | Notas |
|---------|--------|
| **`users`** | Campos auth, locale, flags operativos; alinear naming MULTI/MONO donde el SDK expone admin |
| **`pq_empresa`** | Superset documentado o perfiles `single` / `multi`; columnas ERP/BD opcionales en MONO |
| **`pq_rol`** | En host físico `pq_roles`; roles, `acceso_total`, códigos |
| **`pq_rolatributo`** | En host físico `pq_rol_atributos`; atributos de menú/proceso por rol |
| **`pq_permiso`** | En host físico `pq_permisos`; usuario ↔ rol (y empresa solo donde aplique MULTI) |

**Entregables esperados del hito (Framework):**

1. **Decisión de arquitectura** publicada (SPEC Framework): modelo canónico, columnas obligatorias/opcionales por tenancy, mapeo Tango ↔ Laravel.
2. **Scripts SP únicos o ramificados** (`PAQSUITE_TENANCY` / target manifiesto) — **no** desplegar `pq_sp_admin_empresa_*` dictionary sobre BD MONO incompatible.
3. **Manifiesto GEN-34**: exclusión, reemplazo host, o paso condicionado para hosts `single` (merge no solo “append”).
4. **Migraciones / DDL idempotente** dictionary y guía de adopción para hosts existentes (Partes).
5. Pin SDK release + nota en `RELEASE-FRAMEWORK-MANIFEST` y checklist host.

**Fuera de alcance inmediato:** pantallas de dominio Partes; ABM empresas MONO (alta ERP) ya prohibido en producto.

### Reparto Framework vs Partes

| Responsable | Qué corresponde | Por qué |
|-------------|----------------|---------|
| **FRAMEWORK** (principal) | Contrato canónico MONO/MULTI; SP y manifiesto GEN-34; `installation:update` condicionado a `PAQSUITE_TENANCY`; adapters SDK; release `laravel-core` | El fallo de deploy nace en SQL/manifiesto **del core**, no en reglas de negocio Partes |
| **Partes** (adopción) | Pin del SDK; manifiesto host; migraciones/SP **solo si** el SPEC Framework lo exige para alinear columnas; smoke Forge; retirar parches duplicados cuando el core unifique | El host **consume** el contrato; no redefine el modelo de plataforma en silencio |

Regla práctica: si la pregunta es *«¿qué DDL/SP debe existir para todos los hosts?»* → **Framework**. Si es *«¿cómo aplicamos el release en esta BD ya poblada?»* → **Partes** (TR de adopción, sin cambiar semántica de datos).

### Partes — preservación de datos en cambios de esquema

Cualquier migración o script SQL en **Partes-Atención** que toque tablas de seguridad debe ser **evolutivo e idempotente** y **no alterar valores existentes** salvo corrección documentada en TR.

| Restricción | Detalle |
|-------------|---------|
| **Identidad** | No reasignar ni regenerar **`id`** (PK/FK): `users.id`, `pq_empresa.id`, `pq_roles.id`, filas de `pq_permisos` / `pq_rol_atributos` |
| **Login** | **`users.codigo`** (código de usuario) y **`password` / `password_hash`**: sin reset masivo, sin rehash salvo migración explícita acordada; credenciales vigentes siguen válidas post-deploy |
| **Denominación** | **`nombre`** (y equivalentes visibles: `name_user`, `pq_empresa.nombre`, nombres de rol): solo cambios de **columna** (rename/add), con **copia 1:1** del valor, nunca sustitución por defaults |
| **Permisos** | Matriz usuario–rol–empresa: mismos vínculos lógicos tras el cambio (mismos `user_id` / `rol_id` / `empresa_id` o mapeo 1:1 documentado) |
| **Prohibido** | `migrate:fresh`, truncates, seeds destructivos sobre seguridad en **preview/producción**; renumeración masiva de IDs |

Validación mínima post-cambio en Partes: conteo de filas estable, login con usuario conocido, menú y permisos equivalentes al pre-deploy.

### Criterios de aceptación preliminares

- [ ] **CA-FW-01** Documento SPEC (Framework) fija contrato único o dual explícito (`single` / `multi`) para las cinco entidades.
- [ ] **CA-FW-02** `paqsuite:installation:update` en instalación **MONO** de Partes **no falla** por columnas Tango inexistentes.
- [ ] **CA-FW-03** SP de admin empresas/seguridad invocados por SDK y host comparten **misma familia de nombres** o adapter documentado sin `DROP` cruzado.
- [ ] **CA-FW-04** Manifiesto permite al host **omitir o sustituir** pasos Framework incompatibles con `single`.
- [ ] **CA-FW-05** Smoke post-deploy: migrate + installation update + `deploy-sp` + login + menú + ABM empresas (MONO). *(lado host cubierto en CA-H01…H03; contrato Framework abierto)*
- [x] **CA-FW-06** Host Partes actualizado (migración/SP/manifiesto) según guía del release Framework. *(omit + pin; Parte I 06/11/2026)*
- [x] **CA-FW-07** En Partes, migraciones de adopción **preservan** `id`, `codigo`, `nombre` y hashes de contraseña; smoke de login y permisos sin pérdida de accesos.

### Derivación SDD

*Procesado* (Parte G 30/10/2026) → Framework ([repo `PaqSuite-IA-FRAMEWORK`](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK)): [SPEC-001-06-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/05-open-spec/updates/001-Generalidades/SPEC-001-06-update-cc-pq-9-2026-10-30-seguridad-mono-multi.md) · [SPEC-001-34-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/05-open-spec/updates/001-Generalidades/SPEC-001-34-update-cc-pq-9-2026-10-30-install-single-tenancy.md) · [HU-GEN-06-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/03-historias-usuario/updates/001-Generalidades/HU-GEN-06-seguridad-mono-multi-update.md) · [HU-GEN-34-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/03-historias-usuario/updates/001-Generalidades/HU-GEN-34-install-single-tenancy-update.md) · [TR-GEN-06-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/04-tareas/updates/001-Generalidades/TR-GEN-06-seguridad-mono-multi-update.md) · [TR-GEN-34-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/04-tareas/updates/001-Generalidades/TR-GEN-34-install-single-tenancy-update.md)

**Parte I 06/11/2026 — host PARTES Finalizado** → [TR-adopcion-host CC #9](../04-tareas/001-Generalidades/TR-adopcion-host-cc-pq-9-seguridad-mono-multi.md) (omit `fw-sp-pq_sp_admin_empresa_tango_dictionary` + pin `1.3.18-beta.24`). CA-FW-01…07 siguen en Framework.

---

## Control de Calidad #8

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 25/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado · **F1/F Aprobado** · **Parte I** cerrada |
| **Origen** | Prueba manual de Paquete de horas e indicación en [`18-obtencion-saldo-por-renglon.md`](../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) |
| **Jira** | [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |
| **F1/F** | **Aprobado** 06/11/2026 — smoke PQ informe + rango duración |
| **Parte I** | 06/11/2026 — fusionado en SPEC/HU/TR-006 y SPEC/HU/TR-013 base; updates eliminados |

### Hallazgos

En la prueba manual del informe **Paquete de horas** se detectaron tres ajustes:

1. El rango de duración de la **carga de paquetes de horas** debe ser de **1 a 240 horas enteras, inclusive**. La definición anterior que comenzaba en 8 horas queda corregida.
2. En el informe **Paquete de horas**, los registros con `es_tarea = 0` deben distinguirse visualmente en la columna **Duración**, mediante una presentación negativa, color rojo u otro destaque equivalente.
3. La columna **Saldo** no está calculándose correctamente.

### Mejoras solicitadas

#### Carga de paquetes — duración de 1 a 240 horas

*Procesado* (Parte G 30/10/2026) · **Parte I** 06/11/2026 → [SPEC-013](../05-open-spec/100-SistemaPartes/SPEC-013-carga-paquetes-horas.md) · [HU-013](../03-historias-usuario/100-SistemaPartes/HU-013-carga-paquetes-horas.md) · [TR-013](../04-tareas/100-SistemaPartes/TR-013-carga-paquetes-horas.md)

- Ajustar el rango permitido de duración para la carga de paquetes para aceptar horas enteras de **1 a 240**, inclusive.
- Alinear validación frontend, backend/SP, Smart Capture, mensajes i18n y tests.

#### Informe Paquete de horas — destaque de paquetes

*Procesado* (Parte G 30/10/2026) · **Parte I** 06/11/2026 → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

- Para cada fila con `es_tarea = 0`, destacar el valor de la columna **Duración**.
- El resultado puede expresarse como duración negativa y/o color rojo, siempre que sea visible y consistente en grilla, exportación si corresponde e informe.
- Las filas con `es_tarea = 1` deben conservar la presentación normal.

#### Informe Paquete de horas — saldo por renglón

*Procesado* (Parte G 30/10/2026) · fusionado Parte I en SPEC/HU/TR-006 (ítem saldo + orden fecha/id).

- Calcular el saldo acumulado por cliente y mostrarlo en la columna **Saldo**.
- Aplicar la convención indicada en [`18-obtencion-saldo-por-renglon.md`](../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md): los registros con `es_tarea = 1` suman duración y los registros con `es_tarea = 0` restan duración.
- El saldo inicial debe considerar los movimientos anteriores al inicio del período (`fecha < @fechaDesde`) y cada renglón posterior debe continuar el acumulado del cliente.
- Definir y respetar el orden de cálculo cuando existan varias filas del mismo cliente en la misma fecha.

### Criterios de aceptación preliminares

- [x] **CA-01** La carga de paquetes acepta duraciones enteras de 1 a 240 horas y rechaza valores fuera del rango.
- [x] **CA-02** La regla de duración es consistente en UI, API/SP, Smart Capture, i18n y tests.
- [x] **CA-03** Las filas con `es_tarea = 0` muestran la duración destacada en el informe Paquete de horas.
- [x] **CA-04** Las filas con `es_tarea = 1` mantienen la presentación normal de duración.
- [x] **CA-05** El saldo inicial se obtiene sumando/restando los movimientos anteriores al período por cliente.
- [x] **CA-06** El saldo de cada renglón continúa el acumulado del cliente según `es_tarea`.
- [x] **CA-07** La solución funciona para clientes distintos sin mezclar sus saldos.

### Derivación SDD

Parte G **30/10/2026** — cola `updates/` lista para **D → E → F** (PIAPARTES-52).

Parte I **06/11/2026** — familia 006 + 013 unificada a ruta base y **Finalizado**. Smoke PQ (informe + rango duración). [D-VERIFICACION-CC-PQ-8](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-8-2026-10-25.md).

---

## Control de Calidad #7

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 19/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |

### Hallazgos

Envios de mails a clientes y asistentes antes dos eventos : 1) aviso desde ABM de usuarios para proveedor link del sitio y credenciales iniciales de acceso. 2) Anuncio de novedades a los clientes sobre partes ingresados por los asistentes y aprobado por el supervisor

### Errores encontrados - Mejoras solicitadas

#### Envio mail a usuario para acceder al sitio

*Procesado* (Parte G 20/10/2026) → [SPEC-002-update](../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update.md) · [HU-002-update](../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update.md) · [TR-002-update](../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update.md)

en el Abm de Usuarios, agregar un botón "Notificación", por el cual : 
- se envia un mail al usuario (en ALTA; se habilita después de haber ingresado un mail)
- EL mismo notifica en Asunto : Codigo de la Empresa y algo así como " - ingreso a nuestro sistema de Partes de Atención".
- En el cuerpo del mensaje, hacer mención que se ofrece al receptor el link para poder controlar los servicios que se prestaron a su empresa, aclarando el link de acceso (hipervínculo para eso), y las credenciales de acceso : usuario y contraseña
- La contraseña se define en el momento con un número random que cumpla con las normas de seguridad parametrizadas (simple o segura, longitud de la clave)
- se graba en el usuario que es el primer login (para que lo modifique en el proximo acceso)

#### Envio mail a clientes para informar existencia de novedades

*Procesado* (Parte G 20/10/2026) · refino **CC #15** · **Parte I** 04/11/2026 → [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) §4.7 · [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md)

En el proceso de "Partes Masivos":
- habilitar un ícono con la imagen de mail, y tooltip "Informar a clientes", con el cual.
- se determinan todos los clientes que figuran en la grilla en ese momento y que tengan asociado un código/usuario de la tabla `USERS` (`PQ_PARTES_CLIENTES.user_id`).
- ese usuario debe estar **activo** (usable: activo y no inhabilitado) y tener **mail en `USERS`**.
- enviar el aviso a **`users.email`** de cada usuario recolectado (destinatarios únicos). **No** usar el mail que eventualmente figure en el maestro cliente (`PQ_PARTES_CLIENTES.email`).
- Asunto : Codigo de la empresa y algo así como : "Existen novedades de atención a su empresa".
- Mensaje : informar que hubo novedades de servicios prestados en el periodod (desde y hasta con se filtró el procesó), recordando la URL de aviso y el codigo de usuario para acceder, y que si no recuerda su contraseña podrá solicitar una nueva en el login.

> **Corrección conceptual (04/11/2026):** el destinatario del aviso es siempre `users.email` del usuario vinculado al cliente; nunca `PQ_PARTES_CLIENTES.email`.

---

## Control de Calidad #6

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 11/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | PA-01…14: F Aprobado (PA-03/04/14: 12/10/2026). |
| **Parte I** | PA-01 (012); PA-02…14 unificados en SPEC/HU/TR-002 originales. Updates 002 **eliminados** 12/10/2026. |
| **Cierre updates** | Familia 002 **Finalizado**. F1+F+I 12/10/2026. |
| **Origen** | [deuda-cableado-sdk-por-gen.md](../06-operacion/deuda-cableado-sdk-por-gen.md) · relevamiento Framework 11/10/2026 |
| **Pin de referencia** | Histórico relevamiento 2026-10: **2.4.29-beta.1** / **1.3.15-beta.1**. **Pin vigente en repo (2026-11):** **2.4.30-beta.9** · **1.3.18-beta.24** (`ritual-bump-sdk.md`). |

### Hallazgos

Cerrar la **deuda de cableado SDK** del host Partes-Atención: mala invocación de exports GEN listos, forks de pantallas/motores que el SDK ya exporta, o contratos que el SDK ya cubre (headers, cliente, envelope, tema, i18n GEN).

**Fuera de alcance de este control:** pantallas de dominio (carga diaria, maestros, informes); gaps de producto no ofrecidos (GEN-13, GEN-17, GEN-23, GEN-32).

**Qué no pedir (ya está bien):** `registerGridI18nResources`; `MenuSidebar` con `t`/`locale`/`labels`/`openInNewTab`/`onItemsLoaded`; `UserAvatarMenu`; `DashboardContainer`; `EmpresaSelectorPage`; `ParametrosGeneralesPage`; `ProcessDataGrid` (salvo permisos); `ConsultaGrillaPivotShell`; `ExcelImportToolbar`; `SmartCapturePanel`; `ChatAssistantPage`; `LlmPreferencesPanel`; `ShellLayout` + CSS SDK; `EmpresasAdminPage` con `tenancyMode="single"` y preview sin reload.

**Criterio de aceptación (por ítem, al resolver vía SDD):** se invoca el **export** del SDK (sin pantalla/motor GEN duplicado); i18n GEN con `register*I18nResources` y el mismo namespace (sin JSON/mapper host de las mismas claves); envelope `{ error: 0, respuesta, resultado }`; `bootstrapClienteFromWindow` antes del Router y headers con `buildPlatformHeaders`; smoke login + una pantalla GEN tocada (empresas o usuarios/permisos) en ES y EN/FR.

**Orden sugerido de resolución (Parte G / D):** P0 PA-02 → P1 GEN-06 (PA-03, PA-04, PA-05, PA-06, PA-14) → P1 GEN-19 (PA-09, PA-10, PA-08) → P1 GEN-07 PA-07 → P2 PA-11, PA-12, PA-13 → GEN-35 PA-01 al pasar de labo a Cloudsmith en CI/Forge.

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) · PA-01 **F+I** → [SPEC-012](../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md)

### Errores encontrados - Mejoras solicitadas

#### GEN-20 — Envelope API (PA-02, P0)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- `ApiEnvelope::ok` serializa `'error' => null`. Contrato SDK: `'error' => 0` y `resultado` objeto, no `null`. Rompe clientes que usan `isEnvelope` / `error === 0`.
- Dónde: `backend/app/Support/ApiEnvelope.php`, `AuthController.php`.
- Pedido: eliminar `ApiEnvelope` o alinearlo a `PaqSuite\LaravelCore` `ApiResponse`. Auth (y status) deben usar el envelope canónico.

#### GEN-06 — Seguridad admin (PA-03, PA-04, PA-05, PA-06, PA-14)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) · regla 44 / 36

- **PA-03 P1:** el SDK exporta `UsuariosAdminPage`; el host reimplementa listado, popup, alta/edición e inhabilitación (`UsuariosAdminPage.tsx`). Pedido: wrapper fino del export SDK. Dominio solo: token, `platform`, permisos, menú.
- **PA-04 P1:** el SDK exporta `PermisosAdminPage`; el host monta tres `DataGrid` DX y lógica batch propia (`PermisosAdminPage.tsx`). Pedido: montar `PermisosAdminPage` del SDK. No DataGrid crudo de proceso.
- **PA-05 P1:** wrapper de empresas **no pasa** `i18nNamespace="common"` (init usa `defaultNS: 'common'`). Pasa `t={translateSdk}` (`securityEmpresasSdkI18n`) **y** `registerEmpresasAdminI18nResources` **y** `empresasAdminHostFallback` con overwrite. Triple fuente de `admin.empresas.*` / `appearance.paqsuite.*`. Pedido: `registerEmpresasAdminI18nResources(i18n, 'common')` + `i18nNamespace="common"`. Quitar mapper y fallback host de claves GEN.
- **PA-06 P1:** `SecurityRolesPage` recibe `t` que mapea a claves host. **No** se llama `registerSecurityRolesI18nResources`. Pedido: registrar recursos SDK y no mapear claves GEN.
- **PA-14 P2:** existen `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources`; el host no los registra (coherente con el fork de pantallas). Pedido: cerrar junto con PA-03 / PA-04.

#### GEN-07 + GEN-02 — Menú / i18n GEN (PA-07, PA-11)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- **PA-07 P1:** `shellI18n.ts` duplica chrome `menu.*`. Pedido: labels del sidebar vía `t('menu.*')` / helper SDK (`menuSidebarLabelsFromT`). Catálogo host solo claves **producto**.
- **PA-11 P2:** `main.tsx` hace `syncDevExtremeLocale('es')` y `i18n.ts` sincroniza `getGuestLocale()`. Arranque EN/FR puede pintar DX en ES un tick. Pedido: un solo `syncDevExtremeLocale` con el locale real (guest/sesión), no forzar `'es'` al boot.

#### GEN-19 — Shell / tenancy / tema / `{cliente}` (PA-08, PA-09, PA-10)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) · regla 34

- **PA-08 P1:** con pin ≥ 2.4.20 el SDK exporta `normalizeThemeFromApi`, `mapThemeForApiPersistence`, `paqsuiteDxStockBridge`, `useEmpresaAppearanceBridge`. El host mantiene `sdkEmpresaThemeCompat.ts` y switcher DX propio. Pedido: un solo puente SDK. Preview/restore sin reload de página.
- **PA-09 P1:** `main.tsx` llama `bootstrapPlatformCliente()` **antes** del Router (orden OK) pero **no** `bootstrapClienteFromWindow`. Reimplementa query/cookie/session/Vercel/landing. Pedido: delegar a `bootstrapClienteFromWindow`. Conservar wrap de landing Vercel **solo** si el SoT de resolución host lo admite.
- **PA-10 P1:** `buildAuthPlatformHeaders()` arma `{ cliente, companyId, tenancy }` a mano. Pedido: usar `buildPlatformHeaders`. MONO: `X-Paq-Cliente` ≠ id de empresa.

#### GEN-04 — Auth / cambio de clave (PA-12, P2)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- `ChangePasswordPage` sin `AuthCardLayout` / `AuthPageLayout`, literales en español.
- Pedido: layouts SDK + i18n (mismo patrón que login/forgot/reset, que ya están bien).

#### GEN-01 — Layouts de proceso (PA-13, P2)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- Procesos de dominio no usan `ProcessPageLayout` (div/h2 propios) en varias páginas `features/partes/**`.
- Pedido: adoptar `ProcessPageLayout`. Título desde menú localizado (`resolveMenuCaption`) o `t()` de producto — no literal ES.

#### GEN-35 — Distribución SDK (PA-01, lab / ops)

*Procesado* (Parte G 11/10/2026) · **F+I 11/10/2026** → [SPEC-012](../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) · [HU-012](../03-historias-usuario/100-SistemaPartes/HU-012-adopcion-gen-35-cloudsmith.md) · [TR-012](../04-tareas/100-SistemaPartes/TR-012-adopcion-gen-35-cloudsmith.md) · [F PA-01](../04-tareas/100-SistemaPartes/F-TR-012-adopcion-gen-35-cloudsmith-PA-01.md)

**F+I 11/10/2026:** path quitado; lock Cloudsmith `/basic/`; Forge rechaza path/Satis. F: [F PA-01](../04-tareas/100-SistemaPartes/F-TR-012-adopcion-gen-35-cloudsmith-PA-01.md) **Aprobado con observaciones**. SPEC/HU/TR-012-update fusionados en originales (**Finalizado**). El ítem #6 de la tabla sigue abierto por PA-02…PA-14.

---

## Control de Calidad #5

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 05/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 11/10/2026 → [D-VERIFICACION](../04-tareas/updates/001-Generalidades/D-VERIFICACION-CC-PQ-05-2026-10-05.md) |
| **Parte I** | Unificado 11/10/2026 → originales [SPEC/HU/TR-001](../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) |
| **Cierre updates** | 11/10/2026 — Parte I ejecutada; originales en **Finalizado** |

### Hallazgos

Alta de usuario (ABM Usuarios): si la contraseña no cumple la política, el mensaje aparece como texto en la **pantalla principal** (bajo el título), no dentro del modal «Nuevo usuario».

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-001](../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) · [HU-001](../03-historias-usuario/001-Generalidades/HU-001-ui-errores-validacion-contexto-carga.md) · [TR-001](../04-tareas/001-Generalidades/TR-001-ui-errores-validacion-contexto-carga.md) · Regla BASE [36-ui-errores-validacion-contexto-carga](../../.cursor/rules/base/20-frontend/36-ui-errores-validacion-contexto-carga.mdc)

### Errores encontrados - Mejoras solicitadas

#### ABM Usuarios (GEN seguridad)

- Los errores de validación al guardar deben mostrarse **en el modal de carga** o en un **popup modal**, no en el layout de la página de fondo.
- Aplicar el mismo criterio a **todos los procesos de carga en modal** del producto (regla transversal).

---

## Control de Calidad #4

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 04/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 11/10/2026 → [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-04-2026-10-04.md) |
| **Parte I** | Unificado 11/10/2026 → originales [SPEC/HU/TR-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |
| **Cierre updates** | 11/10/2026 — Parte I ejecutada; originales en **Finalizado** |

### Hallazgos

Mejoras y correcciones tras control de calidad manual y completo

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md) · [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

### Errores encontrados - Mejoras solicitadas

#### ABM Asistentes

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

- No aparece el codigo de usuario asociado.
- Verificar que no se duplique el usuario asignado. (ni en asistente ni en clientes)
- limitar la lista de usuario a elegir, a los activos, habilitados y no asignados previamente.

#### ABM Clientes

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

- Verificar que no se duplique el usuario asignado. (ni en asistente ni en clientes)
- limitar la lista de usuario a elegir, a los activos, habilitados y no asignados previamente.

#### Paquete de Horas

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

-  Agregar filtro "Tipo de Cliente".
- Agregar columna "Tipo de Cliente" (codigo y descripcion)

---

## Control de Calidad #3

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 09/08/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 16/09/2026 → [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-03-2026-09-16.md) |
| **Parte I** | Unificado 16/09/2026 → originales [SPEC/HU/TR-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md), [005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md), [010](../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) |
| **Cierre updates** | 16/09/2026 — Parte I ejecutada; originales en **Finalizado** |

### Hallazgos

Mejoras y correcciones tras pruebas manuales

*Procesado* (unificado Parte I 16/09/2026) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) · [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) · [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) · [SPEC-010](../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) · [HU-010](../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) · [TR-010](../04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md)

### Errores encontrados - Mejoras solicitadas

#### proceso masivo : tildo fila y se borra el tilde

*Procesado* (unificado Parte I) → [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) (sin SPEC-update: bug vs SPEC-005 §4.3)

- si en la grilla tildo registros para procesarlos masivamente (incluso el tilde que marca todos los registros), se borran inmediatamente.

#### Carga Diaria : Mostrar (agregado) duración en formato decimal

*Procesado* (unificado Parte I) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md)

- en la grilla de carga diaria, (que además sea exportable a Excel), expresar también la duración en formato decimal (mm/60). Ejemplos : 02:15 -> 2.25, 15:30 -> 15.5, 14.45 -> 14.75

#### Plantillas de grillas : diseños de otros usuarios.

*Procesado* (unificado Parte I) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) (GEN-11 compartido; **no** hay parámetro para ocultar ajenas)

- Dos usuarios que guardan plantillas personalizadas de grillas, sólo ven las suyas, no las de otros usuarios. desconozco si hay un atributo en la instalación o en parámetros generales para optar por verlos o no. Ocurre al menos en Cargas de Partes Diarios.

#### Cargas de Partes Diarios - Implementar IA

*Procesado* (unificado Parte I) → [SPEC-010](../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) · [HU-010](../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) · [TR-010](../04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md) · F1/F → [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-03-2026-09-16.md) (slice IA: [D-VERIFICACION-TR-010](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-010-update-CC-PQ-03-2026-09-16.md))

- implementar bien la asistencia IA. cargo la información, pero no actualiza los datos en los controles. (Ver imagen CC#3-PQ-2026-09-16.png)

#### Cargas de Partes Diarios - inicializar tipo de tarea

*Procesado* (unificado Parte I) → [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) (sin cambio SPEC: ya está en SPEC-004 §4.4)

- al generar un parte nuevo, no aparece en Tipo de Tarea por defecto el registro marcado como tal.



---

## Control de Calidad #2

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 01/08/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 01/08/2026 → [D-VERIFICACION-CC-PQ-02](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-02-2026-08-01.md) |
| **Parte I** | Unificado 01/08/2026 → originales [SPEC/HU/TR-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md), [003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |

### Hallazgos

Incorporar en los clientes dos atributos referidos al sistema ERP, para obtener informes para facturación.

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

### Errores encontrados - Mejoras solicitadas

#### Agregar atributo al maestro de Clientes

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

Agregar dos atributos Erp_Cliente y Erp_Articulo, string de 15 caracteres cada uno, para poder referenciar atributos del ERP con el cual después se puedan hacer informes de facturación.

#### Incorporar Atributos a Informes

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

Incorporar ambos atributos en la consulta detallada y la consulta agrupada, tango en la grilla como en el pivot.


---

## Control de Calidad #1

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 31/07/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 01/08/2026 → [D-VERIFICACION-CC-PQ-01](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md) |
| **Parte I** | Unificado 01/08/2026 → originales [SPEC/HU/TR-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md), [004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md), [005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |

### Hallazgos

Poder llevar una cuenta corriente de horas para aquellos clientes que abonan por paquete anticipado

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) (incluido en rehacer Paquete de Horas)

### Errores encontrados - Mejoras solicitadas

#### Agregar atributo booleano "EsTarea"

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md)

Agregar en la tabla de tareas un atributo booleano denominado "EsTarea"
True : es una tarea (cargada del proceso "Carga de Partes").
False : Es una compra de horas (en un proceso a definir)

#### Carga de Partes y Proceso Masivo - Asignar True a "EsTarea"

*Procesado* (unificado Parte I) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) · [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) · [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md)

- Solo traer registros donde este atributo es "true"
- Al grabar un parte (nuevo/Edicion), asignar "true" a este atributo

#### Informes "Carga detallada" y "Carga agrupada" y Dashboard

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

- Filtrar los registros para que consideren unicamente los que este atributo es "true"

#### Informe "Paquete de Horas" : Rehacer

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

el objetivo de este informe es poder llevar una cuenta corriente de horas para aquellos clientes que contratan paquetes de horas anticipadas
este informe debe ser tipo grilla/pivot con las siguientes características:
- seleccionar como filtros fecha desde, fecha hasta, cliente (cuando el usuario es asistente o supervisor).
- presentar los mismos atributos que "Carga Detallada".
- este informe NO debe filtrar por el campo "Estarea"
- debe agregar un registro "Saldo inicial" con la suma/resta de minutos hasta la fecha desde solicitada (exclusive). Si "Estarea"=true, suma, si es false, resta.
- agregar una columna Saldo, donde en el primer registro "saldo inicial" se coloca la sumatoria antedicha, y cada registro agrega el saldo anterior más sus minutos (sumando/restando)
- en el PIVOT, no incluir este atributo de Saldo.
