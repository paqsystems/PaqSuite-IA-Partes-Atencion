# SPEC-001-11 — Mobile Capacitor (MONO)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | A generar: `HU-GEN-11-mobile-capacitor-*.md` (índice en README 001) |
| **TR relacionadas** | A generar: `TR-GEN-11-mobile-capacitor-*.md` |
| **Estado** | Especificado |
| **Revisión A1** | Apto con observaciones |
| **Parte B** | **Cerrada** — 4 HU GEN-11 |
| **Parte C** | **Cerrada** — 3 TR GEN-11 v1 |
| **Release mobile** | Tag **`v1.2.0-mobile`** (SistemaPartes y productos MONO que adopten este SPEC) |
| **Modo** | **MONO** (`X-Paq-Cliente`; sin `X-Company-Id`) |

## Objetivo

Definir la plataforma mobile empaquetada con **Capacitor** sobre el frontend React existente: Android e iOS desde el inicio, login con **tenant explícito**, exclusiones funcionales respecto a web, y roadmap por fases (v1 / v2 / v3) delegando detalle de producto al SPEC-101-17.

## Estado de ejecución

**A1 + B1 + C1 cerrados (2026-06-30).** Siguiente paso: **Parte D** (implementación v1). TR v2/v3 en HU 034–036 cuando cierre F v1.

## Decisiones humanas (2026-06-30)

| Tema | Decisión |
|------|----------|
| Plataformas | **Android + iOS** desde el inicio |
| Release | Tag Git **`v1.2.0-mobile`** |
| Tenant | **Campo en login** (empresa/cliente); ver [`docs/_base/01-mobile/04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md) |
| Orden auth | **Primero tenant → conexión; después credenciales** |
| Config avanzada | Icono engranaje solo para **override URL API** (dev/staging); tenant **no** en config |
| Consultas mobile | **Kardex** (tarjetas), no DataGrid web |
| Roadmap funcional | **v1:** una consulta kardex · **v2:** todos listados/consultas · **v3:** carga pedidos |

## Fuente de verdad técnica (BASE)

| Documento | Contenido |
|-----------|-----------|
| [`docs/_base/01-mobile/README.md`](../../_base/01-mobile/README.md) | Visión general |
| [`docs/_base/01-mobile/04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md) | **Login tenant-first (obligatorio MONO mobile)** |
| [`docs/_base/01-mobile/01-especificacion-capacitor.md`](../../_base/01-mobile/01-especificacion-capacitor.md) | Detalle Capacitor |
| [`docs/_base/01-mobile/03-comandos-generacion-aplicaciones.md`](../../_base/01-mobile/03-comandos-generacion-aplicaciones.md) | Comandos build/tiendas |
| `.cursor/rules/base/80-mobile/00-mobile-especificaciones-programacion.mdc` | Regla agente |

## Producto SistemaPartes

Detalle de fases v1/v2/v3 y consulta MVP: [`SPEC-101-17-mobile-capacitor-SistemaPartes.md`](../../02-producto/SistemaPartes/INDICE-Sistema-Partes.md).

## Dependencias

| SPEC | Relación |
|------|----------|
| SPEC-001-02 | Auth envelope, login, sesión |
| SPEC-001-01 | Shell, i18n, temas (adaptar mobile) |
| SPEC-001-05 | Tenancy MONO |
| [`resolucion-host-cliente-sql-mono.md`](../../_base/resolucion-host-cliente-sql-mono.md) | Resolución `{cliente}` → SQL |

**Backend:** sin nuevo contrato API obligatorio; login existente con header `X-Paq-Cliente` ya resuelve tenant.

## Alcance transversal (in scope)

1. **Capacitor** en `frontend/`: `android/`, `ios/`, `capacitor.config.ts`, scripts build mobile.
2. **Android + iOS** en pipeline de desarrollo (APK debug / simulador iOS).
3. **Login mobile** con campos **tenant + usuario + contraseña** (patrón §04 BASE).
4. **Config avanzada** (opcional): URL API override + test health; persistencia `@capacitor/preferences`.
5. **Detección plataforma** (`Capacitor.isNativePlatform()`) para ramas UX mobile vs web.
6. **Shell mobile:** drawer menú, safe areas (`StatusBar` + `viewport-fit=cover`), sin `openInNewTab`.
7. **Exclusiones** (filtrado menú/rutas): pivots, Excel import, admin seguridad, pestañas separadas.
8. **Plugins mínimos:** `@capacitor/preferences`, `@capacitor/status-bar`, `@capacitor/splash-screen`, `@capacitor/keyboard`, `@capacitor/app`.

## Fuera de alcance (este SPEC)

| Tema | Dónde |
|------|--------|
| Pantallas de negocio concretas (kardex, carga) | SPEC-101-17 fases v1–v3 |
| React Native / Flutter | [`02-especificacion-react-native-flutter.md`](../../_base/01-mobile/02-especificacion-react-native-flutter.md) — épica futura |
| Publicación Play Store / TestFlight | TR operación + [`03-comandos-generacion-aplicaciones.md`](../../_base/01-mobile/03-comandos-generacion-aplicaciones.md) |
| Cambios backend tenancy | SPEC-101-01 (etapa posterior) salvo verificación login con tenant |

## Exclusiones funcionales mobile (norma)

| # | Prohibido en app mobile |
|---|-------------------------|
| 1 | PivotGrid / informes pivot |
| 2 | Importación Excel (`/excel-import/*`) |
| 3 | ABM admin seguridad (`/admin/*`) |
| 4 | Preferencia «Pestañas separadas» (`openInNewTab`, `window.open` en menú) |

## Login tenant-first (resumen normativo)

Ver documento completo [`04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md).

1. Usuario ingresa **tenant** (slug empresa: `demo`, `ankasdelsur`, `quento`, …).
2. Cliente envía **`X-Paq-Cliente`** en health/login.
3. Backend **resuelve conexión SQL** del tenant.
4. Backend **valida** usuario/contraseña en esa base.
5. Sesión/token ligados al tenant; cambio de tenant → re-login.

## Roadmap por fases (referencia)

| Fase | Tag / release | Contenido |
|------|---------------|-----------|
| **Mobile v1** | `v1.2.0-mobile` | Scaffold Capacitor + login tenant + **una** consulta kardex |
| **Mobile v2** | `v1.2.1-mobile` | Todos listados y consultas en kardex |
| **Mobile v3** | `v1.2.2-mobile` | Proceso carga pedidos/presupuestos mobile |

Detalle SistemaPartes: SPEC-101-17.

## Criterios de aceptación medibles (plataforma)

- [ ] `npm run cap:sync` genera proyectos Android e iOS sin error.
- [ ] Login mobile muestra tenant, usuario, contraseña; `data-testid="loginTenant"`.
- [ ] Login exitoso con tenant válido (`demo` o seed) en dispositivo/emulador.
- [ ] Request login incluye `X-Paq-Cliente` antes de validar credenciales.
- [ ] Tenant inválido → error claro sin autenticar.
- [ ] Menú/rutas excluidas no accesibles en build native.
- [ ] Sin toggle «pestañas separadas» en mobile.
- [ ] Documentación patrón login tenant en BASE §04 citada en TR.

## Trazabilidad HU (a generar — parte B)

| HU propuesta | Tema |
|--------------|------|
| HU-GEN-11-mobile-capacitor-scaffold | Capacitor init, Android/iOS, build scripts |
| HU-GEN-11-mobile-login-tenant | Login tenant-first MONO |
| HU-GEN-11-mobile-config-api | Config avanzada URL API |
| HU-GEN-11-mobile-shell-exclusiones | Shell, menú filtrado, exclusiones |

## Trazabilidad TR (parte C — v1)

| TR | HU | Estado |
|----|-----|--------|
| [TR-GEN-11-mobile-capacitor-scaffold](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-capacitor-scaffold.md) | HU-GEN-11-mobile-capacitor-scaffold | **C1 cerrado** |
| [TR-GEN-11-mobile-login-tenant](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-login-tenant.md) | HU-GEN-11-mobile-login-tenant + config-api | **C1 cerrado** |
| [TR-GEN-11-mobile-shell](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-shell.md) | HU-GEN-11-mobile-shell-exclusiones | **C1 cerrado** |

## Definición de listo (SPEC-001-11)

- [x] A1 cerrado sin ambigüedades bloqueantes (§ Revisión A1)
- [x] HU-GEN-11-* generadas (Parte B)
- [x] TR-GEN-11-* v1 generadas (Parte C) — [F-GEN-11-cierre-c1](../../04-tareas/001-Generalidades/F-GEN-11-cierre-c1.md)
- [ ] Implementación D según TR (autorizada post-C1)

---

## Revisión A1 — cierre (2026-06-30)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno |

### Checklist A1 (resumen)

| **Estado** | Especificado |
|------|--------|-------|
| Alcance / fuera de alcance | OK | Plataforma vs negocio (101-17); exclusiones explícitas |
| Actores / permisos | OK | Mismos perfiles web; menú filtrado + backend |
| Flujo login tenant-first | OK | Patrón BASE §04; orden conexión → auth |
| Plataformas Android + iOS | OK | iOS smoke vía Mac/CI (D1-10) |
| UI / i18n | OK | DevExtreme; testids definidos |
| APIs / backend | OK | Sin contrato nuevo; `X-Paq-Cliente` existente |
| Roadmap v1/v2/v3 | OK | Delegado a SPEC-101-17 |
| Trazabilidad HU/TR | Obs. | Propuestas GEN-11; producto en 101-17 |
| Criterios aceptación | OK | Medibles; completar en HU/TR |

### Ambigüedades críticas

Ninguna bloqueante para **Parte B** tras decisiones D1.

| ID | Tema | Resolución A1 |
|----|------|---------------|
| AMB-C-001-11-01 | ¿Login web también muestra tenant? | **Cerrado D1-3:** campo tenant **solo** si `Capacitor.isNativePlatform()`; web mantiene tenant por subdominio/env |
| AMB-C-001-11-02 | ¿Un paso o dos (tenant luego credenciales)? | **Cerrado D1-4:** formulario **único** (tenant + usuario + contraseña); health pre-submit **opcional**, no bloqueante |
| AMB-C-001-11-03 | ¿iOS en entorno Windows sin Mac? | **Cerrado D1-10:** build/smoke iOS obligatorio en **Mac o CI `macos-latest`** antes del tag `v1.2.0-mobile`; Android suficiente en dev local |
| AMB-C-001-11-04 | ¿Config avanzada cuándo visible? | **Cerrado D1-13:** icono engranaje en **header post-login** y accesible en **login** (solo native); no en shell web |

### Ambigüedades menores (resolver en TR, no bloquean B)

| ID | Tema | Resolución / propuesta |
|----|------|------------------------|
| AMB-M-001-11-01 | Slugs tenant ejemplo vs dev (`desarrollo`) | **Cerrado D1-6:** smoke/tests usan `desarrollo`; ejemplos productivos `demo`, `ankasdelsur`, `quento`; normalización minúsculas sin espacios |
| AMB-M-001-11-02 | Persistir último tenant tras logout | **Cerrado D1-14:** precargar campo tenant en login; limpiar token siempre |
| AMB-M-001-11-03 | Plugins Capacitor adicionales (push, biometría) | Fuera v1; TR scaffold lista mínima § alcance |
| AMB-M-001-11-04 | `GET /preferences` incluye `openInNewTab` | Mobile **ignora** campo; no renderizar toggle (regla 80-mobile) |
| AMB-M-001-11-05 | Deep links / URL scheme | Fuera v1; documentar en TR si hace falta esquema `https` |

### Decisiones D1 (A1 — 2026-06-30)

| # | Tema | Decisión |
|---|------|----------|
| D1-3 | Tenant en UI | Solo plataforma native; web sin cambio |
| D1-4 | Flujo login | Un submit; tenant normalizado antes de `POST /auth/login` |
| D1-6 | Tenant dev vs prod | `desarrollo` en dev/smoke; slugs cliente en prod |
| D1-10 | iOS | Smoke iOS en Mac/CI antes de tag release |
| D1-13 | Config API | Engrane login + header post-login (native) |
| D1-14 | Logout | Borrar token; precargar tenant en formulario |

### Supuestos detectados

- Middleware `ValidatePaqTenant` y login actual ya rechazan tenant inválido (tests Feature existentes).
- `client.ts` debe evolucionar en TR para tenant **dinámico** en native (hoy `VITE_TENANT_DEFAULT_CLIENT`).
- Capacitor comparte bundle web; ramas `isNativeApp` evitan regresión desktop.
- Publicación tiendas **fuera** v1 salvo TR operación explícita.

### Veredicto

**Apto con observaciones** — **autoriza Parte B** (HU-GEN-11-*) y generación paralela de HU producto en SPEC-101-17.

---

## Parte B — cierre (2026-06-30)

| HU | TR (a generar) | Título |
|----|----------------|--------|
| [HU-GEN-11-mobile-capacitor-scaffold](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-capacitor-scaffold.md) | TR-GEN-11-mobile-capacitor-scaffold | Scaffold Capacitor |
| [HU-GEN-11-mobile-login-tenant](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-login-tenant.md) | TR-GEN-11-mobile-login-tenant | Login tenant-first |
| [HU-GEN-11-mobile-config-api](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-config-api.md) | (en TR login/scaffold) | Config URL API |
| [HU-GEN-11-mobile-shell-exclusiones](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-shell-exclusiones.md) | TR-GEN-11-mobile-shell | Shell y exclusiones |

**Veredicto B1:** **Cerrado** — autoriza **Parte C**.

---

## Parte C — cierre (2026-06-30)

| TR | HU | Estado C1 |
|----|-----|-----------|
| [TR-GEN-11-mobile-capacitor-scaffold](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-capacitor-scaffold.md) | HU-GEN-11-mobile-capacitor-scaffold | **Apto** |
| [TR-GEN-11-mobile-login-tenant](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-login-tenant.md) | HU-GEN-11-mobile-login-tenant, config-api | **Apto** |
| [TR-GEN-11-mobile-shell](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-shell.md) | HU-GEN-11-mobile-shell-exclusiones | **Apto** |

**Veredicto C1:** **Apto** — [F-GEN-11-cierre-c1](../../04-tareas/001-Generalidades/F-GEN-11-cierre-c1.md) — autoriza **Parte D** v1.

---

## Parte D — cierre v1 (2026-06-30)

| TR | Estado D1 |
|----|-----------|
| [TR-GEN-11-mobile-capacitor-scaffold](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-capacitor-scaffold.md) | **Implementado** |
| [TR-GEN-11-mobile-login-tenant](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-login-tenant.md) | **Implementado** |
| [TR-GEN-11-mobile-shell](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-shell.md) | **Implementado** |

**Veredicto D1:** **Implementado** — [F-GEN-11-cierre-formal](../../04-tareas/001-Generalidades/F-GEN-11-cierre-formal.md). Smoke **Android emulador OK**; tag `v1.2.0-mobile` tras smoke iOS.

## Referencias

- [`00-inicio-arquitectura.md`](../../_base/00-inicio-arquitectura.md) — fila Mobile
- [`SPEC-001-02-acceso-y-seguridad.md`](SPEC-001-02-acceso-y-seguridad.md)
- Envelope: `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`








