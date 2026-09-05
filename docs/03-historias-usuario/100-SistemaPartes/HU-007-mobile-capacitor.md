# HU-007 – Mobile Capacitor del módulo Sistema Partes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-007 |
| Título | Mobile Capacitor del módulo Sistema Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente |
| Última actualización | 2026-08-17 |
| SPEC origen | [SPEC-007-mobile-capacitor](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) |
| TR relacionada(s) | [TR-007-mobile-capacitor](../../04-tareas/100-SistemaPartes/TR-007-mobile-capacitor.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-007 | Dónde en esta HU |
|--------------------------------|------------------|
| Capacitor Android+iOS §2.1 | Alcance |
| Config URL + health pre-login §4.2, R-MO-02 | CA-01, CA-01b; R-MO-02 |
| Login empresa+usuario+password §4.3, R-MO-03 | CA-01, CA-02; R-MO-03 |
| Gate `resultado.partes` §3 | CA-03 |
| Exclusiones ABM/pivot/masivo/Excel §2.2, R-MO-05 | CA-04; R-MO-05 |
| Consulta kardex §4.6, R-MO-06, R-MO-08 | CA-05; R-MO-06, R-MO-08 |
| Carga individual SPEC-004 §4.7, R-MO-07 | CA-06, CA-07 |
| Informe Paquete de Horas §4.8, R-MO-09 | CA-08; R-MO-09 |
| Navegación in-app §2.1, R-MO-05 | CA-09 |
| Dashboard mobile §4.5 A1 | Alcance; Supuestos |
| Menú filtrado §4.4, R-MO-10 | Alcance; CA-04 |
| i18n + `data-testid` §2.1, R-MO-11 | Alcance |
| `build:mobile` viable §5 | CA-10 |
| Mismo dominio/backend §4.1, R-MO-01 | R-MO-01 |

---

## Narrativa

Como usuario móvil del módulo Partes (asistente, supervisor o cliente)  
quiero una experiencia Capacitor nativa con configuración de API, login por empresa, dashboard, consulta kardex, carga individual e Informe Paquete de Horas  
para operar en campo sobre el mismo dominio y backend que la web, sin clonar grillas desktop ni procesos excluidos de mobile.

---

## Contexto funcional

Mobile **no** es un clon de la web: comparte dominio, envelope, `X-Paq-Cliente` y reglas SPEC-002–006, pero cambia presentación (kardex vs DataGrid), navegación in-app y exclusiones funcionales (ABM, pivot, masivo, Excel, `openInNewTab`). Plataforma: **Capacitor** en el mismo `frontend/` (Android + iOS). Login MONO: campo **empresa** primero → header `X-Paq-Cliente`; tenant **no** se configura en engranaje. Post-login mobile aterriza en dashboard cuando el shell native lo permita (misma convención que web). Decisión A1: dashboard native sin auto-refresh (solo manual + pull-to-refresh); informe paquete horas incluye gráfico simple Must.

---

## Alcance incluido

- **Plataforma Capacitor** (Android + iOS) sobre el mismo `frontend/` del producto.
- **Pantalla inicial pre-login:** acceso a login + configuración de **URL base API** (override) con test de conectividad (`GET .../health` o endpoint GEN) **antes** de guardar; persistencia `@capacitor/preferences`; esa URL es la que usa **toda** operación posterior (login, menú, kardex, informes, chat). `data-testid`: `mobileConfigOpen`, `mobileConfigApiUrl`, `mobileConfigTestConnection`, `mobileConfigSave`.
- **Login mobile:** empresa + usuario + contraseña (orden UI: empresa primero); `empresa` → `X-Paq-Cliente` (MONO instalación, no `X-Company-Id`); `data-testid`: `loginTenant`; i18n `login.tenant` / claves mobile.
- **Gate e identidad funcional:** mismos que SPEC-002 (`resultado.partes`).
- **Shell y menú native** (`isNativeApp()`): ítems desde API / `pq_menus`, filtrados (sin ABM maestros, pivot, masivo, excel, admin seguridad GEN si aplica); navegación **in-app** únicamente.
- **Dashboard mobile:** misma delimitación por rol que SPEC-006; indicadores mínimos en cards (total tiempo, cantidad, resumen); periodo inicial mes calendario coherente con web; refresco **solo manual + pull-to-refresh** (sin timer automático).
- **Consulta de partes en Kardex (superficie principal):** tarjetas verticales; tap → detalle; filtros con default periodo = **día actual**; acciones **agregar / editar / eliminar** en tarjeta y/o detalle según rol y `cerrado` (SPEC-004); cliente = solo lectura.
- **Formulario de una tarea:** misma UX invocada desde kardex (alta/edición); menú «Carga» opcional sin reglas divergentes; validaciones SPEC-004; **Smart Capture / IA operativa en carga = fuera de esta HU** (TR-010 no monta panel en native). El Asistente IA documental del avatar (GEN-21 / HU-008) **sí** puede usarse in-app; no forma parte de los CA de esta HU.
- **Informe Paquete de Horas** (proceso propio mobile): periodo default mes calendario; total minutos/horas; desglose por cliente y por tipo; kardex/cards; **un gráfico simple** Must (tipo = el más simple en DevExtreme Capacitor, TR); solo lectura. **Navegación:** ítem menú **Informes** + atajo desde dashboard. **Datos híbridos:** totales vía dashboard; desgloses vía agregación; fachada opcional.
- i18n (`mobile.*`, `partes.*`) + `data-testid` estables.

---

## Fuera de alcance

| Exclusión | Nota |
|-----------|------|
| ABMs de maestros | SPEC-003 solo web |
| Pivots | SPEC-006 pivot solo web |
| Cargas / proceso masivo | SPEC-005 solo web |
| Excel / import-export | |
| Informes impresos | |
| App React Native / Flutter en `mobile/` | Fuera de este SPEC (Capacitor) |
| Redefinir reglas de duración, cerrado, gate | Hereda SPEC-002–006 |
| Preferencia «Pestañas separadas» / `openInNewTab` | Exclusión mobile BASE |

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-MO-01 | Mismo dominio y backend que web; distinta UX native. |
| R-MO-02 | Config pre-login = URL API + test health; esa URL es la base de **todas** las llamadas de la app native; tenant/empresa solo en login. |
| R-MO-03 | Campo `empresa` mobile = código instalación → header `X-Paq-Cliente` (MONO). |
| R-MO-04 | Incluye: dashboard, kardex partes, carga individual, informe paquete horas. |
| R-MO-05 | Excluye: ABM, pivot, masivo, Excel, impresos, `openInNewTab`. |
| R-MO-06 | Consultas/listados = Kardex, no DataGrid desktop. |
| R-MO-07 | Formulario de una tarea hereda SPEC-004; no cliente; misma UX desde kardex. |
| R-MO-08 | Periodo default consulta partes = día actual. Kardex = superficie principal con acciones CRUD. |
| R-MO-08b | Menú Carga opcional sin divergencia. Smart Capture / IA operativa en el form = fuera de esta HU (no native). Chat documental avatar = HU-008 (in-app). |
| R-MO-09 | Informe Paquete de Horas = total + desgloses + gráfico simple; datos híbridos; menú Informes + atajo dashboard; gráfico = más simple DX (TR). |
| R-MO-10 | Menú filtrado vía `isNativeApp()` / `createMobilePolicy` GEN-22 + allowlist de rutas Partes. |
| R-MO-11 | i18n + `data-testid` mobile estables. |

Actores mobile: **asistente** — dashboard, kardex, carga individual, informe; CRUD propios no cerrados. **Supervisor** — igual + terceros no cerrados en carga/consulta; **sin** proceso masivo. **Cliente** — dashboard, kardex e informe de su org; **sin** carga.

---

## Criterios de aceptación

- [ ] **CA-01** App native abre config URL, testea health y persiste; login pide empresa + usuario + contraseña (empresa primero en UI).
- [ ] **CA-01b** Tras guardar la URL del engranaje, login y el resto de operaciones (menú, kardex, informes) hablan con **ese** servidor; no con el origen de la WebView ni con otra base embebida.
- [ ] **CA-02** Header `X-Paq-Cliente` enviado desde el campo empresa; instalación inválida → error tenant GEN (`tenant.invalid`), no mensaje de contraseña.
- [ ] **CA-03** Tras login, `resultado.partes` gobierna menú y pantallas (cliente sin carga ni rutas excluidas).
- [ ] **CA-04** No hay rutas/menú de ABM, pivot, masivo ni Excel en native.
- [ ] **CA-05** Consulta partes en kardex; periodo default = día actual; filtros acotados por rol; asistente/supervisor tienen acciones agregar/editar/eliminar (cerradas solo lectura); cliente solo lectura.
- [ ] **CA-05b** El formulario de una tarea es la misma UX desde kardex; si existe ítem de menú Carga, no diverge en reglas.
- [ ] **CA-06** Alta mobile rechaza las mismas duraciones/observaciones inválidas que web (SPEC-004).
- [ ] **CA-07** Tarea cerrada no editable ni eliminable en mobile.
- [ ] **CA-08** Existe pantalla Informe Paquete de Horas con total, desgloses por cliente y por tipo, y un gráfico simple; totales reutilizan agregados de dashboard; desgloses vía agregación/consulta (híbrido).
- [ ] **CA-08b** El informe es alcanzable por ítem de menú Informes y por atajo desde el dashboard mobile.
- [ ] **CA-09** Navegación in-app; sin pestañas separadas ni `window.open` para procesos del menú.
- [ ] **CA-10** `npm run build:mobile` y sync Capacitor viable tras implementación (criterio verificable en TR).

---

## Escenarios Gherkin

```gherkin
Feature: Mobile Capacitor Sistema Partes
  Como usuario mobile del módulo Partes
  Quiero operar con UX native sobre el mismo backend
  Para registrar y consultar tareas en campo

  Scenario: Configuración API con test de health
    Given la app native en pantalla inicial pre-login
    When abre configuración e ingresa URL base del API
    And ejecuta test de conexión contra health
    Then solo puede guardar si el test es exitoso
    And la URL persiste en almacenamiento local
    And las operaciones siguientes (login, consultas) usan esa misma URL de API

  Scenario: Login con empresa inválida
    Given un usuario en login mobile con empresa, usuario y contraseña
    When el código de empresa no resuelve instalación válida
    Then ve error de tenant inválido
    And no recibe mensaje genérico de contraseña incorrecta

  Scenario: Cliente sin acceso a carga
    Given un cliente autenticado con "resultado.partes" de perfil cliente
    When navega el menú native filtrado
    Then no ve rutas de carga individual ni ABM ni proceso masivo
    And sí puede acceder a dashboard y consulta kardex de su organización

  Scenario: Consulta kardex con default día actual
    Given un asistente autenticado en consulta de partes mobile
    When abre la consulta sin cambiar filtros
    Then el periodo default es el día actual
    And ve tarjetas kardex con campos resumen y estado "cerrado"

  Scenario: Tarea cerrada no editable en mobile
    Given un asistente con una tarea propia con "cerrado" = 1
    When intenta editar o eliminar desde kardex o detalle
    Then la acción no está disponible o es rechazada según SPEC-004

  Scenario: Informe Paquete de Horas con gráfico
    Given un supervisor autenticado en informe paquete de horas
    When selecciona periodo del mes calendario actual
    Then ve total de minutos/horas del periodo
    And desglose por cliente y por tipo de tarea
    And un gráfico simple del desglose principal
```

---

## Supuestos explícitos

- Capacitor y exports GEN-22 se montan desde `@paqsuite/react-core`; el host no clona presentadores. Allowlist de rutas y mapper dominio→kardex quedan en Partes (detalle en TR-007).
- Backend: informe paquete horas = **híbrido** (totales dashboard + agregación desgloses); fachada `GET /partes/informes/paquete-horas` Must (TR-007).
- Post-login mobile aterriza en dashboard cuando el shell native lo permita (misma convención que web SPEC-006).
- «IA fuera del MVP» en esta HU = Smart Capture / asistente operativo en el formulario de carga. El chat documental del avatar es HU-008 / TR-008 (allowlist in-app).
- Cuenta corriente de horas / facturación = evolución (`07`); informe paquete horas no las implementa.

---

## Preguntas abiertas

- Detalle de botones de acción en kardex: ~~cerrado~~ — kardex CRUD; formulario misma UX; IA fuera MVP.
- Policy / fachada / gráfico / smoke: ~~pendiente~~ → **cerrado en [TR-007](../../04-tareas/100-SistemaPartes/TR-007-mobile-capacitor.md):** `createMobilePolicy` + allowlist host; `GET /partes/informes/paquete-horas`; DX Chart `bar`; presentadores GEN-22; smoke = unit + `build:mobile` + humo emulador manual.
- Origen datos informe / menú: ~~cerrado~~ — híbrido; Informes + atajo dashboard.

---

## Riesgos de ambigüedad

- Periodo dashboard mobile: ~~cerrado en TR-007~~ — selector **mes** compacto (default mes sistema).
- Acciones kardex vs carga: ~~cerrado~~.
- Gráfico: ~~cerrado~~ — DX Chart tipo `bar`.
- Filtrado menú vs deep-link: ~~cerrado~~ — policy + redirect `/partes`.

---

## Dependencias

- [SPEC-002](../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) … [SPEC-006](../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) — mismas reglas de dominio; distinta UX.
- Normas BASE Capacitor / login tenant MONO y exclusiones mobile del repo.
- SPEC-004 — validaciones de carga; SPEC-005 excluido en mobile.

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte B + B1: HU creada y enriquecida desde SPEC-007. |
| 2026-07-30 | Batch: kardex = superficie CRUD; formulario misma UX; IA fuera MVP. |
| 2026-07-30 | Batch: informe horas datos híbridos (dashboard + desgloses). |
| 2026-07-30 | Batch: informe = menú Informes + atajo dashboard; gráfico = más simple DX (TR). |
| 2026-07-30 | Enlace TR-007 (Parte C+C1); policy/Chart bar/fachada cerrados. |
| 2026-08-17 | Alineación TR-007 GEN-22: montar componentes Framework; allowlist + mapper dominio en el host. |
| 2026-08-17 | Antes de D: CA-01b (URL engranaje = única base API); R-MO-08b aclara IA operativa ≠ chat HU-008; fachada informe Must. |
