# SPEC-007 – Mobile Capacitor del módulo Sistema Partes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-007 |
| Título | Mobile Capacitor del módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-07-30 |
| HU relacionada(s) | [HU-007-mobile-capacitor](../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md) |
| TR relacionada(s) | [TR-007-mobile-capacitor](../../04-tareas/100-SistemaPartes/TR-007-mobile-capacitor.md) |
| Depende de | [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md) … [SPEC-006](./SPEC-006-consultas-dashboard-navegacion.md) (mismas reglas de dominio; distinta UX) |
| Fuentes | [`10-mobile.md`](../../02-producto/Sistema-Partes-IA/10-mobile.md); normas BASE Capacitor / login tenant MONO; exclusiones mobile del repo |

---

## 1. Resumen ejecutivo

- **Problema:** mobile no es un clon de la web; necesita experiencia propia (config, login tenant, kardex, carga individual) sobre el **mismo** dominio y backend.
- **Resultado esperado:** contrato MVP mobile (Capacitor en `frontend/`) con pantallas incluidas/excluidas, mapeo del campo **empresa**, kardex, carga individual, dashboard, consulta de partes e **Informe Paquete de Horas**, sin ABM/pivot/masivo/Excel.

---

## 2. Alcance

### 2.1 En alcance

- Plataforma: **Capacitor** (Android + iOS) sobre el mismo `frontend/` del producto.
- Pantalla inicial pre-login: acceso a login + configuración de **URL base API** (override) con test de conectividad y persistencia local.
- Login mobile: **empresa** + usuario + contraseña (orden UI: empresa primero).
- Gate e identidad funcional: mismos que SPEC-002 (`resultado.partes`).
- Dashboard mobile (lectura por rol; periodo coherente con SPEC-006, presentación adaptada). Post-login mobile también aterriza en dashboard cuando el shell native lo permita (misma convención que web).
- Consulta de partes en **Kardex** como superficie principal: periodo default = día actual; acciones **agregar / editar / eliminar** en tarjeta/detalle (SPEC-004).
- Formulario de **una** tarea (misma UX desde kardex; menú Carga opcional sin divergencia).
- **IA chatbot fuera del MVP** mobile (alineado SPEC-004).
- **Informe Paquete de Horas** (§4.8) como proceso propio mobile.
- Filtrado de menú/rutas: ocultar exclusiones §2.2.
- Navegación **in-app** (sin `openInNewTab` / pestañas separadas).
- i18n (`mobile.*`, `partes.*`) + `data-testid` estables (`mobileConfig*`, `loginTenant`, etc.).

### 2.2 Fuera de alcance

| Exclusión mobile | Nota |
|------------------|------|
| ABMs de maestros | SPEC-003 solo web |
| Pivots | SPEC-006: Pivot en Informes web con grilla; **nunca** en native |
| Cargas / proceso masivo | SPEC-005 solo web |
| Excel / import-export | |
| Informes impresos | |
| App React Native / Flutter en carpeta `mobile/` | Fuera de este SPEC (Capacitor) |
| Redefinir reglas de duración, cerrado, gate | Hereda 002–006 |

---

## 3. Actores y contexto

| Actor | Mobile |
|-------|--------|
| Asistente | Dashboard, consulta kardex, carga individual, informe horas; CRUD propios no cerrados |
| Supervisor | Igual + puede operar terceros no cerrados en carga/consulta (mismas reglas SPEC-004); **sin** proceso masivo |
| Cliente | Dashboard, consulta kardex e informe de **su** organización; **sin** carga |

Mismo backend / envelope / `X-Paq-Cliente` que web.

---

## 4. Comportamiento funcional

### 4.1 Principio

Mismo dominio que web; cambia presentación, navegación y priorización. Backend único.

### 4.2 Pantalla inicial y configuración

- Pre-login: CTA a login + icono/botón **configuración**.
- Config MVP: **URL base del API** (ej. `http://10.0.2.2:8088/api/v1` en emulador).
- Test de conexión (`GET .../health` o endpoint acordado GEN) **antes** de guardar.
- Persistencia: `@capacitor/preferences` (o equivalente del stack).
- **No** configurar el tenant/empresa en el engranaje: el tenant va en **login** (patrón MONO mobile).
- `data-testid`: `mobileConfigOpen`, `mobileConfigApiUrl`, `mobileConfigTestConnection`, `mobileConfigSave`.

### 4.3 Login y campo `empresa` (decisión MVP)

| Campo UI | Mapeo |
|----------|--------|
| Empresa | Código de instalación / cliente Paq → header **`X-Paq-Cliente`** (ej. `DEMO`). En este producto MONO **no** es selector multi-empresa `X-Company-Id`. |
| Usuario | Credencial Framework (`users`) |
| Contraseña | Credencial Framework |

Pipeline: resolver instalación con `X-Paq-Cliente` → credenciales → gate Partes (SPEC-002).

- `data-testid`: `loginTenant` (empresa), más los de login GEN.
- i18n: `login.tenant` / claves mobile equivalentes.
- Si empresa inválida → error tenant GEN (`tenant.invalid`), no mensaje de password.

### 4.4 Shell y menú mobile

- Shell autenticado Framework adaptado a native (`isNativeApp()`).
- Ítems desde menú API / `pq_menus`, **filtrados** para quitar: ABM archivos maestros, pivots, masivo, excel, admin seguridad GEN si aplica exclusión.
- Navegación siempre in-app.

### 4.5 Dashboard mobile

- Misma delimitación por rol que SPEC-006.
- Indicadores mínimos: total tiempo del periodo, cantidad de tareas, resumen principal (adaptado a cards).
- Periodo inicial: mes calendario del sistema (coherente web) **o** el control mobile puede enfatizar “mes actual” de forma compacta; debe ser explícito en UI.
- Refresco: **solo manual + pull-to-refresh** (sin timer automático en native). Coherente con decisión A1.

### 4.6 Consulta de partes (Kardex) — superficie principal de operación

- Lista vertical de tarjetas (una tarea = una tarjeta): identificadores + 2–4 campos resumen + estado `cerrado`.
- Tap → detalle (pantalla/drawer/modal).
- Filtros: periodo (default = **día actual**), clientes del universo, asistentes del universo (supervisor); cliente org fija para perfil cliente.
- Empty state claro (SPEC-006).
- **Acciones en kardex (cerrado batch):** asistente/supervisor operan **agregar / editar / eliminar** desde la propia superficie kardex (acciones en tarjeta y/o detalle), respetando SPEC-004 (`cerrado`, propiedad, obligatorios, tramo, `row_version` → 409).
  - **Agregar:** abre formulario/wizard de una tarea (misma UX que §4.7) desde kardex.
  - **Editar / eliminar:** desde detalle o acciones de tarjeta; cerradas = solo lectura.
- Perfil **cliente:** kardex solo lectura (sin acciones de escritura).

### 4.7 Carga individual (formulario)

- Formulario o wizard de **una** tarea (no grilla administrativa web).
- En MVP native, ese formulario es la **misma UX** invocada desde kardex (alta/edición); puede exponerse también como entrada de menú “Carga” si el seed lo incluye, **sin** duplicar reglas ni pantallas divergentes.
- Validaciones idénticas a SPEC-004: cliente usable, tipo ∈ universo, duración múltiplo del tramo (`PQ_PARAMETROS_GRAL`, default 15) ≤ 1440, observación obligatoria, marcas, fecha (advertencia futura), optimistic lock.
- Propietario: asistente = fijo; supervisor = selector.
- Cliente: sin acceso.
- IA chatbot: **fuera del MVP** (alineado SPEC-004); evolutivo.

### 4.8 Informe Paquete de Horas (decisión MVP)

Proceso **propio** mobile (no solo un widget del dashboard).

| Aspecto | Contrato MVP |
|---------|----------------|
| Objetivo | Ver el “paquete” de horas del periodo: total y desglose usable en campo |
| Entrada | Periodo (default: mes calendario actual); filtros opcionales dentro del universo (cliente / asistente según rol). **Navegación (cerrado batch):** ítem propio en menú **Informes** (visible según rol/delimitación) **y** atajo/card desde el **dashboard** mobile hacia el mismo proceso. |
| Salida | (1) Total minutos/horas del periodo; (2) desglose por **cliente**; (3) desglose por **tipo de tarea**; presentación kardex/cards o listas, **sin** PivotGrid; (4) **un gráfico simple** Must MVP — **DevExtreme Chart tipo `bar`** (fijado en TR-007); polish visual = mejora posterior. |
| Datos | Misma delimitación SPEC-002; solo lectura. **Origen (cerrado batch):** **híbrido** — totales del periodo reutilizan el contrato/agregados del **dashboard** (SPEC-006); desgloses por cliente y por tipo vía **consulta/SP de agregación** (familia consultas agrupadas / SP dedicado de desglose). Un endpoint “fachada” del informe puede orquestar ambas lecturas sin duplicar reglas de negocio. |
| Relación | Cuenta corriente de horas / facturación = evolución (`07`); este informe **no** las implementa |

### 4.9 Reglas numeradas

| ID | Regla |
|----|--------|
| R-MO-01 | Mismo dominio/backend que web; distinta UX native. |
| R-MO-02 | Config pre-login = URL API + health; tenant solo en login. |
| R-MO-03 | `empresa` mobile = `X-Paq-Cliente` (MONO instalación). |
| R-MO-04 | Incluye: dashboard, kardex partes, carga individual, informe paquete horas. |
| R-MO-05 | Excluye: ABM, pivot, masivo, Excel, impresos, openInNewTab, menú seguridad admin. |
| R-MO-06 | Consultas/listados = Kardex, no DataGrid desktop. |
| R-MO-07 | Formulario de una tarea hereda SPEC-004; no cliente; misma UX desde kardex. |
| R-MO-08 | Periodo default consulta partes = día actual. Kardex = superficie principal con acciones agregar/editar/eliminar. |
| R-MO-08b | Menú «Carga» opcional sin pantallas/reglas divergentes del formulario kardex. IA fuera del MVP. |
| R-MO-09 | Informe Paquete de Horas = total + desgloses + gráfico simple; datos híbridos; menú Informes + atajo dashboard; gráfico = más simple DX (TR). |
| R-MO-10 | Menú filtrado; `isNativeApp()` / policy de rutas. |
| R-MO-11 | i18n + `data-testid` mobile estables. |

---

## 5. Criterios verificables

- [ ] App native abre config URL, testea health y persiste; login pide empresa+usuario+password.
- [ ] `X-Paq-Cliente` enviado desde el campo empresa; instalación inválida no autentica.
- [ ] Tras login, `resultado.partes` gobierna menú y pantallas (cliente sin carga).
- [ ] No hay rutas/menú de ABM, pivot, masivo ni Excel en native.
- [ ] Consulta partes en kardex con acciones agregar/editar/eliminar (cerradas solo lectura); formulario de una tarea = misma UX.
- [ ] Alta mobile rechaza las mismas duraciones/observaciones inválidas que web.
- [ ] Tarea cerrada no editable/eliminable en mobile.
- [ ] Existe pantalla Informe Paquete de Horas con total + desgloses §4.8 + gráfico simple.
- [ ] Navegación in-app; sin pestañas separadas.
- [ ] `npm run build:mobile` / sync Capacitor viable tras implementación (criterio de TR).

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Frontend | Ramas `isNativeApp()`; policy mobile Partes; vistas kardex/carga/informe; Preferences |
| Backend | Reutiliza SP dashboard (totales) + agregaciones desglose; fachada informe opcional que orquesta ambas |
| Menú | Flags/filtrado native en seed o client-side policy |
| Tests | Policy unit tests; E2E web no sustituye smoke dispositivo (manual/CI según repo) |
| Docs | Cierra empresa mobile + definición MVP informe paquete horas |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Auto-refresh dashboard 60 s en native | **Cerrado:** no; solo manual + pull-to-refresh. |
| Origen datos informe paquete horas | **Cerrado:** híbrido (totales dashboard + desgloses agregación). |
| Detalle UI del informe (gráficos) | **Cerrado MVP:** un gráfico simple Must; tipo = el más simple en DX Capacitor (TR); polish → mejora posterior. |
| Menú informe paquete horas | **Cerrado:** ítem Informes **y** atajo desde dashboard. |
| Permisos finos por acción en kardex | **Cerrado:** kardex con acciones CRUD; formulario = misma UX; hereda SPEC-004 |
| Capacitor ya usado en otros productos | Reutilizar patrones GEN; no inventar stack |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Versión inicial (Parte A) — MVP mobile Capacitor + empresa + paquete horas. |
| 2026-07-30 | A1: apto con observaciones (auto-refresh dashboard native; gráficos informe opcionales). |
| 2026-07-30 | A1 cierre: dashboard mobile = manual + pull-to-refresh (sin auto). |
| 2026-07-30 | A1 cierre: informe paquete horas incluye gráfico simple; polish visual = mejora posterior. |
| 2026-07-30 | Batch HU: kardex = superficie CRUD; formulario misma UX; IA fuera MVP. |
| 2026-07-30 | Batch HU: informe horas datos híbridos (dashboard + desgloses). |
| 2026-07-30 | Batch HU: informe = menú Informes + atajo dashboard; gráfico = más simple DX (TR). |
| 2026-07-30 | Enlace TR-007 (Parte C+C1); gráfico = DX Chart `bar`; policy `partesMobilePolicy`. |

---

**Trazabilidad:** [HU-007](../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md) · [TR-007](../../04-tareas/100-SistemaPartes/TR-007-mobile-capacitor.md).

**Mapa de dominio:** set **SPEC-001 … SPEC-007** + **TR-001 … TR-007** (C1 OK → D1) cerrado a nivel OpenSpec/TR. Siguiente paso metodológico: **D1/D por TR** cuando se autorice (orden 001→007).
