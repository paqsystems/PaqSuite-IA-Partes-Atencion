# HU-007-update – Mobile v1 Capacitor (menú GEN-22, 3 procesos)

Jira: PIAPARTES-84

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-007-update-mobile-v1 |
| Título | Mobile v1 — Carga individual, Consulta detallada y Paquete de horas (Capacitor) |
| Épica / carpeta | `100-SistemaPartes` · Epic Jira **PIAPARTES-83** |
| Clasificación | MUST-HAVE (acota release v1) |
| Estado | Pendiente |
| Revisión B1 | **Lista para TR** (2026-11-27) |
| Superficie | **solo mobile-capacitor** (CA con sufijo **M**); **común** = web + policy compartida sin cambio UX web |
| Última actualización | 2026-11-27 |
| SPEC origen | [SPEC-007-mobile-v1-alcance-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-007-mobile-v1-alcance-update.md) |
| SPEC base (histórico) | [SPEC-007-mobile-capacitor](../../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) |
| HU base (histórico) | [HU-007-mobile-capacitor](../../100-SistemaPartes/HU-007-mobile-capacitor.md) |
| TR relacionada(s) | [TR-007-update-mobile-v1-alcance-gen22](../../../04-tareas/updates/100-SistemaPartes/TR-007-update-mobile-v1-alcance-gen22.md) |

**Adopción Framework (regla 19):**

```text
Mobile v1 Partes-Atención: adoptar GEN-22 (isNativeApp, createMobilePolicy, MobileConfigPanel, kardex GEN, ProcessPageLayout).
No reimplementar motor de exclusiones ni stack Capacitor.
SoT: SPEC-007-update + 21-mobile-v1-alcance-gen22. Host: partesMobilePolicy, ramas native, formulario carga SPEC-004.
```

---

## Trazabilidad SPEC (SPEC-007-update)

| Entregable / criterio SPEC | Dónde en esta HU |
|---------------------------|------------------|
| Tres procesos §2.1 | Alcance; CA-MV1-01 |
| D-MV1-A1-01 post-login | CA-MV1-02; R-MV1-U05 |
| D-MV1-A1-02 `/partes/carga` | CA-MV1-03; R-MV1-U03 |
| D-MV1-A1-03 consulta solo lectura | CA-MV1-05; R-MV1-U06 |
| D-MV1-A1-04 allowlist | CA-MV1-04; R-MV1-U02 |
| Exclusiones §2.2 | Fuera de alcance; CA-MV1-04 |
| R-MV1-01…08 | Reglas R-MV1-U01…U08 |
| CA-MV1-01…07 SPEC §5 | Criterios CA-MV1-01…CA-MV1-10 |
| Actores §3 | Matriz actores; Gherkin permisos |
| GEN-22 transversal §2.1 | CA-MV1-06 (M); `createMobilePolicy` + `genMobileExclusions` (TR) |
| R-MV1-05 cliente sin carga | R-MV1-U09; CA-MV1-03; Gherkin cliente |
| §6 impacto `partesMobilePolicy` | CA-MV1-08; CA-MV1-09 |
| R-MV1-01 solo UX native | CA-MV1-10 (común) |

---

## Narrativa

Como usuario móvil de Partes (asistente, supervisor o cliente)  
quiero entrar en la app nativa con **solo** los procesos de **carga individual**, **consulta detallada** e **informe paquete de horas**  
para operar en campo con el mismo backend que la web, sin dashboard ni consultas legacy, y con consulta detallada en modo lectura en v1.

---

## Contexto funcional

Este update **acota** [HU-007](../../100-SistemaPartes/HU-007-mobile-capacitor.md) para la release **v1** acordada en producto (`21-mobile-v1-alcance-gen22.md`). Capacitor sigue en `frontend/` (Android + iOS); dominio SPEC-002–006 sin cambios de negocio en backend.

**Cambios de experiencia respecto a HU-007 original:**

| Tema | HU-007 | HU-007-update v1 |
|------|--------|------------------|
| Post-login | Dashboard | Primera ruta permitida entre los 3 procesos (orden menú) |
| Consulta partes | Kardex `/partes/consulta` con CRUD | **Consulta detallada** kardex **solo lectura** |
| Carga | Form + kardex opcional | Proceso dedicado **`/partes/carga`**; sin grilla carga-diaria web |
| Menú | Más ítems (dashboard, chat, etc.) | Solo 3 procesos + cambio clave si policy lo incluye |

### Matriz de actores (SPEC §3)

| Actor | Carga individual (M) | Consulta detallada (M) | Paquete de horas (M) |
|-------|----------------------|------------------------|----------------------|
| Asistente | Sí | Sí (universo propio) | Sí |
| Supervisor | Sí (+ terceros no cerrados, SPEC-004) | Sí | Sí |
| Cliente | **No** | Sí (organización propia) | Sí (cliente fijo en universo) |

### Procesos v1 — referencia menú / rutas

| # | `codigo` menú | Ruta web ref. | Ruta / UX native v1 |
|---|---------------|---------------|---------------------|
| 1 | `partes_carga_diaria` | `/partes/carga-diaria` | **`/partes/carga`** (formulario una tarea) |
| 2 | `partes_consulta_detallada` | `/partes/informes/consulta-detallada` | Misma ruta; kardex **solo lectura** |
| 3 | `partes_informe_paquete_horas` | `/partes/informes/paquete-horas` | Misma ruta; kardex/cards; sin pivot |

Orden post-login si hay permiso a varios: ids menú **30100 → 40100 → 40300** (D-MV1-A1-01).

---

## Alcance incluido (v1, solo mobile)

- **GEN-22 MUST:** `createMobilePolicy` + exclusiones GEN (`genMobileExclusions`: pivot, excel, admin seguridad, `openInNewTab`); `MobileConfigPanel` con test health antes de guardar; login tenant-first (`X-Paq-Cliente`); navegación in-app; `data-testid` mínimos config: `mobileConfigOpen`, `mobileConfigApiUrl`, `mobileConfigTestConnection`, `mobileConfigSave`; login `loginTenant`.
- **Menú native filtrado:** únicamente **Carga diaria** (`partes_carga_diaria`), **Consulta detallada** (`partes_consulta_detallada`), **Paquete de horas** (`partes_informe_paquete_horas`), según permisos SPEC-002.
- **Aterrizaje post-login (M):** tras auth, navegar a la primera ruta permitida entre esos tres, en orden `pq_menus` (30100 → 40100 → 40300); **sin** dashboard.
- **Carga individual (M):** ruta canónica **`/partes/carga`**; reglas SPEC-004; cliente sin carga; sin `CargaDiariaPage` / DataGrid en native.
- **Consulta detallada (M):** kardex lectura sobre universo detalle SPEC-006 (`es_tarea = true`); filtros coherentes con web; **sin** Emitir, pivot ni DataGrid desktop.
- **Paquete de horas (M):** cuenta corriente SPEC-006; cliente obligatorio donde aplique; presentación kardex/cards; gráfico simple **opcional** v1.
- i18n `mobile.*` / `partes.*` y `data-testid` estables en pantallas tocadas.

---

## Fuera de alcance (v1)

| Exclusión | Nota |
|-----------|------|
| Dashboard `/partes` | D-MV1-A1-01 / producto §2.3 |
| `/partes/consulta` legacy | Sustituido por consulta detallada |
| CRUD en kardex consulta detallada | Evolutivo; solo lectura v1 |
| Maestros, masivo, compras paquetes | SPEC-003, 005, 016 |
| Emitir, diseñador informes | GEN-15 |
| Chat, Smart Capture en carga | GEN-21 / evolutivo |
| Flutter / RN | GEN-33 |

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-MV1-U01 | Mismo backend y envelope que web; solo cambia UX native (R-MV1-01). |
| R-MV1-U02 | `partesMobilePolicy` allowlist mínima: `/partes/carga`, `/partes/informes/consulta-detallada`, `/partes/informes/paquete-horas`, `/change-password` + rutas auth; denegar dashboard, `/partes`, `/partes/consulta`, `/chat-assistant` y §2.2 SPEC (R-MV1-03, D-MV1-A1-04). |
| R-MV1-U03 | Ítem menú carga diaria resuelve a **`/partes/carga`** en native; prohibida grilla web carga-diaria (D-MV1-A1-02, R-MV1-04). |
| R-MV1-U04 | Menú native = solo tres procesos §2.1 con permiso (R-MV1-02). |
| R-MV1-U05 | Post-login = primera ruta permitida entre los tres; sin hub dashboard (D-MV1-A1-01). |
| R-MV1-U06 | Consulta detallada kardex **solo lectura** v1; altas/edición/baja solo en carga (D-MV1-A1-03, R-MV1-06). |
| R-MV1-U07 | Paquete horas: contrato SPEC-006; sin pivot (R-MV1-07). |
| R-MV1-U08 | Montar exports GEN-22; no clonar policy/kardex/config del Framework (R-MV1-08). |
| R-MV1-U09 | Perfil **cliente** sin acceso a carga individual ni rutas excluidas; gate `resultado.partes` SPEC-002 (R-MV1-05). |
| R-MV1-U10 | Tareas **cerradas** no editables/eliminables en carga mobile; mismas reglas SPEC-004 que web. |
| R-MV1-U11 | Universo consulta detallada mobile usa contrato/API de consulta detallada (`es_tarea = true`); **no** el listado genérico `/partes/consulta` (A1-M3; detalle TR). |

---

## Criterios de aceptación

| ID | Criterio | Superficie |
|----|----------|------------|
| CA-MV1-01 | Menú native muestra **únicamente** los tres procesos con permiso según SPEC-002 | M |
| CA-MV1-02 | Tras login, la app abre la **primera** ruta permitida entre los tres procesos (orden menú); **no** navega a dashboard | M |
| CA-MV1-03 | Carga en `/partes/carga` cumple validaciones SPEC-004; cliente bloqueado; **no** aparece grilla `CargaDiariaPage` en native | M |
| CA-MV1-04 | Policy y menú **deniegan** dashboard, `/partes/consulta`, maestros, masivo, excel, admin, chat y rutas §2.2 | M |
| CA-MV1-05 | Consulta detallada en kardex = universo web detalle; **sin** acciones alta/edición/baja ni Emitir/pivot en v1 | M |
| CA-MV1-06 | Config + login tenant + navegación in-app cumplen GEN-22 (health, preferences, `loginTenant`, sin `openInNewTab`) | M |
| CA-MV1-07 | Paquete horas: cliente obligatorio según rol; datos según SPEC-006; sin pivot; gráfico simple **opcional** v1 (A1-M2) | M |
| CA-MV1-08 | Tests `partesMobilePolicy.test.ts` cubren allowlist v1 y rutas denegadas §2.2 | M |
| CA-MV1-09 | `npm run build:mobile` y `npx cap sync` exitosos tras implementación (SPEC CA-MV1-07) | M |
| CA-MV1-10 | Con `isNativeApp() === false`, menú y rutas web **no** pierden procesos ni se ocultan ítems fuera del delta v1 native (R-MV1-U01; regresión web) | común |

---

## Escenarios Gherkin (v1)

```gherkin
Feature: Mobile v1 Partes — menú acotado GEN-22
  Como usuario mobile de Partes
  Quiero solo carga, consulta detallada y paquete de horas
  Para trabajar en campo sin procesos web excluidos

  Scenario: Post-login sin dashboard
    Given un asistente autenticado en native con permiso a los tres procesos
    When completa el login
    Then la app navega a la primera ruta permitida según orden de menú
    And no muestra dashboard ni hub "/partes"

  Scenario: Menú sin consulta legacy ni chat
    Given un supervisor autenticado en native
    When abre el menú lateral
    Then ve como máximo Carga, Consulta detallada y Paquete de horas
    And no ve dashboard, "/partes/consulta" ni asistente chat

  Scenario: Cliente sin carga
    Given un cliente autenticado con perfil cliente en "resultado.partes"
    When revisa el menú native
    Then no ve el proceso de carga individual
    And puede abrir consulta detallada y paquete de horas si tiene permiso

  Scenario: Consulta detallada solo lectura
    Given un asistente en consulta detallada mobile
    When abre el detalle de una tarjeta kardex
    Then puede ver datos del universo detalle
    And no puede iniciar alta, edición ni baja desde esa consulta

  Scenario: Configuración API con test de health
    Given la app native en pantalla pre-login
    When abre configuración e ingresa URL base del API
    And ejecuta test de conexión contra health
    Then solo puede guardar si el test es exitoso
    And login y operaciones posteriores usan esa URL persistida

  Scenario: Login con tenant inválido
    Given un usuario en login mobile con empresa, usuario y contraseña
    When el código de empresa no resuelve instalación válida
    Then ve error de tenant inválido
    And no recibe mensaje genérico de contraseña incorrecta

  Scenario: Post-login con un solo proceso permitido
    Given un cliente autenticado con permiso solo a consulta detallada
    When completa el login
    Then la app navega a consulta detallada sin error de ruta vacía
    And no intenta abrir dashboard ni carga

  Scenario: Deep link a ruta excluida
    Given un usuario autenticado en native
    When intenta navegar a "/dashboard" o "/partes/consulta"
    Then la policy bloquea o redirige según contrato GEN-22 + host
    And muestra feedback i18n tipo mobile.exclusion (sin pantalla web cruda)

  Scenario: Carga en ruta canónica
    Given un asistente con permiso de carga
    When entra al proceso de carga desde el menú
    Then la ruta activa es "/partes/carga"
    And el formulario aplica las mismas validaciones que web SPEC-004

  Scenario: Tarea cerrada en carga
    Given un asistente con una tarea propia cerrada
    When intenta editar o eliminar desde el formulario de carga
    Then la acción no está disponible o es rechazada según SPEC-004
```

---

## Supuestos explícitos

- La baseline [TR-007](../../04-tareas/100-SistemaPartes/TR-007-mobile-capacitor.md) puede estar parcialmente implementada; este update define **delta** de policy, router y pantallas (detalle en TR-007-update).
- Periodo default consulta detallada = **día actual** (A1-M1); mes calendario para paquete horas si SPEC-006 lo exige en web.
- HU-007 original permanece referencia histórica; **esta HU gobierna** el circuito PIAPARTES-83.
- Capacitor scaffold en `frontend/` operativo; pin `@paqsuite/react-core` del host expone exports GEN-22 acordados.
- Backend y SP de consulta detallada y paquete horas **sin cambio de contrato** en v1; el TR limita cambios a FE + `partesMobilePolicy`.
- Consulta detallada mobile reutiliza **presentador kardex GEN** con datos del endpoint de consulta detallada, no del listado legacy (A1-M3).

---

## Preguntas abiertas

- Gráfico en paquete horas v1: **opcional** (A1-M2); TR documenta implementación u omisión sin bloquear CA-MV1-07.
- Clave i18n exacta para deep-link excluido: familia `mobile.exclusion.*` u homólogo producto (A1-M5); TR alinea con GEN-22.
- Smoke dispositivo físico Android+iOS (A1-M4): runbook F1; fuera de CA funcionales de esta HU.

---

## Riesgos de ambigüedad

- Coexistencia código antiguo (allowlist con dashboard/chat) hasta merge TR: validar con CA-MV1-08/09 antes de release.
- Cliente con un solo proceso permitido: cubierto en CA-MV1-02 y Gherkin; TR debe implementar selector de primera ruta sin fallback a `/partes`.
- Confundir API consulta detallada con `GET` listado `/partes/consulta`: violaría R-MV1-U11; TR debe citar contrato web detallado.

---

## Dependencias

- [SPEC-002](../../../05-open-spec/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso.md) — gate y permisos menú.
- [SPEC-004](../../../05-open-spec/100-SistemaPartes/SPEC-004-carga-individual.md) — carga.
- [SPEC-006](../../../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) — consulta detallada y paquete horas.
- Framework **GEN-22** — exclusiones y componentes; no reinventar en host.

---

## Veredicto B1 (enriquecimiento)

| Campo | Valor |
|-------|--------|
| SPEC leído | [SPEC-007-mobile-v1-alcance-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-007-mobile-v1-alcance-update.md) + decisiones §2.1.1 |
| HU base | Este archivo (post Parte B) |
| **Lista para TR** | **Sí** — observaciones menores solo en i18n deep-link y gráfico opcional (TR, no HU) |
| Alcance nuevo | **No** — solo detalle, trazabilidad, CA-MV1-09/10 y Gherkin ampliado |

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-11-27 | Parte B SDD — HU-update desde SPEC-007-mobile-v1-alcance-update (PIAPARTES-83). |
| 2026-11-27 | Parte B1 — matriz actores/rutas, reglas R-MV1-U09…11, CA-MV1-08…10, Gherkin +8 escenarios, veredicto lista para TR. |
| 2026-11-27 | Parte C — enlace TR-007-update-mobile-v1-alcance-gen22. |