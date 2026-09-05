# TR-007 – Mobile Capacitor del módulo Sistema Partes

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-007-mobile-capacitor](../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md) |
| **SPEC relacionada** | [SPEC-007-mobile-capacitor](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Roles** | Asistente / supervisor / cliente (mismas reglas; UX native) |
| **Dependencias** | [TR-002](./TR-002-identidad-funcional-y-acceso.md) … [TR-006](./TR-006-consultas-dashboard-navegacion.md) (dominio/API); [TR-008](./TR-008-asistente-ia-chat-documental.md) (allowlist `/chat-assistant`); `@paqsuite/react-core@2.2.1` GEN-22 / GEN-01 / GEN-04 / GEN-07 / GEN-08 / GEN-09 / GEN-21 |
| **Clasificación** | HU COMPLEJA |
| **Estado** | Pendiente de Revisión (D delta GEN-22 implementado; F1 humo emulador pendiente) |
| **Última actualización** | 2026-08-17 |
| **Revisión C1** | Apto con observaciones (ver §11) |

**Origen:** [HU-007](../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md)  
**Referencia SPEC:** [SPEC-007](../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md)

**Referencia GEN (checkout Framework / paquete):**

- `@paqsuite/react-core` `src/mobile/*` (GEN-22) — `isNativeApp`, `MobileConfigPanel`, `ConsultaKardexList`, `MobileRouteGuard`, `MobileMenuShell`, `createMobilePolicy`, `bootstrapApiBaseUrl`
- Layouts GEN-01/04/07/08/09: `AuthLoginLayout`, `ShellLayout`, `UserAvatarMenu`, `MenuSidebar`, `DashboardContainer`, `EmptyState` / `ErrorState` / `LoadingOverlay`
- Chat GEN-21: `ChatAssistantPage` (contrato [TR-008](./TR-008-asistente-ia-chat-documental.md); no reimplementar)

**Norma de adopción (MUST):** el host **no** clona presentadores, policy engine, config URL ni guard de rutas. Monta exports de `@paqsuite/react-core`. El host solo aporta: **allowlist de rutas de producto**, **mapeo dominio → `KardexItem`**, **formulario de tarea** (SPEC-004) y **gráfico de barras del informe** (no hay Chart GEN).

---

## 1) HU refinada (resumen)

### In scope

- Capacitor Android+iOS sobre el mismo `frontend/`: config URL + health vía **`MobileConfigPanel`**; login empresa → `X-Paq-Cliente` dentro de **`AuthLoginLayout`**.
- Shell native: **`ShellLayout`** + **`MobileMenuShell`** (ítems filtrados con **`createMobilePolicy`**); post-login → dashboard; sin timer auto.
- Consulta partes: **`ConsultaKardexList`** + mapper host; CRUD según rol/`cerrado`; formulario una tarea = misma UX (host).
- Dashboard native: **`DashboardContainer`** + widgets/cards; top clientes en kardex, **no** `ProcessDataGrid`.
- Informe Paquete de Horas: desgloses en **`ConsultaKardexList`** + Chart DX `bar` (host).
- Guard native: **`MobileRouteGuard`**. Allowlist host + exclusiones GEN (`genMobileExclusions`).
- `build:mobile` + `npx cap sync`.

### Out of scope

- ABM maestros, pivot, masivo, Excel, impresos, `openInNewTab`, seguridad admin, RN/Flutter.
- Reimplementar List/Popup de kardex, policy engine, config URL o menú native propios.
- Smart Capture en native (TR-010: no montar panel si `isNativeApp()`).
- Redefinir contratos GEN-22.

### Drift a corregir en D (código actual)

| Hoy en el host | Debe quedar |
|----------------|-------------|
| `ConsultaKardexMobilePage` con `List` DX propio | Página host que monta **`ConsultaKardexList`** + mapper |
| `partesMobilePolicy` engine allow/deny propio | **`createMobilePolicy({ allowlistRouteNames })`**; const allowlist de producto |
| `RequirePartesMobilePolicy` con `Navigate` propio | Adapter fino sobre **`MobileRouteGuard`** |
| Login sin `loginTenant` ni `MobileConfigPanel` | **`AuthLoginLayout`** + campo empresa + **`MobileConfigPanel`** en toolbar |
| Dashboard/informe native con **`ProcessDataGrid`** | **`DashboardContainer`** / **`ConsultaKardexList`**; pivot oculto (ya) |
| `MenuSidebar` también en native | Native: **`MobileMenuShell`**; web: `MenuSidebar` |
| Sin `android/` / `ios/` ni `build:mobile` | Scaffold Capacitor GEN + sync |

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | `MobileConfigPanel` (testids GEN `mobileConfig*`); health OK antes de save; persistencia Preferences GEN; login empresa primero (`loginTenant`) |
| AC-02 | `X-Paq-Cliente` desde empresa (`persistClienteCode` / `resolveClienteCode` GEN); inválida → `tenant.invalid` |
| AC-03 | `resultado.partes` gobierna menú (cliente sin carga); filtro dominio **después** de `createMobilePolicy.filterItems` |
| AC-04 | Sin ABM/pivot/masivo/Excel/seguridad admin en native (`genMobileExclusions` + fuera de allowlist) |
| AC-05 | Kardex `ConsultaKardexList`; default día actual; CRUD asistente/supervisor; cliente RO |
| AC-05b | Formulario única UX kardex (± menú Carga sin divergencia); no hay segundo form native |
| AC-06 | Mismas validaciones SPEC-004/TR-004 |
| AC-07 | Cerrada no editable/eliminable |
| AC-08 | Informe: total + desglose cliente + desglose tipo en kardex GEN + gráfico barra DX |
| AC-08b | Menú Informes + atajo dashboard |
| AC-09 | Navegación in-app; `UserAvatarMenu isNativeApp`; `showOpenInNewTab={false}` |
| AC-10 | `npm run build:mobile` + `npx cap sync` OK |
| AC-11 | **Ningún** presentador mobile clonado: kardex/config/guard/menú native/policy engine = exports GEN-22 |
| AC-12 | Tras guardar URL en engranaje, login/menú/kardex/chat/health/LLM pegan a **ese** host (mismo origin en Network); no hay llamadas `/api` al origen de la WebView |

---

## 3) Adopción Framework vs host

### 3.1 Inventario MUST (montar, no clonar)

| Capacidad | Export `@paqsuite/react-core` | Dónde en el host |
|-----------|------------------------------|------------------|
| Detectar native | `isNativeApp` | Ramas de página / shell / policy |
| URL API | `bootstrapApiBaseUrl`, `readApiBaseUrlOverride` / `writeApiBaseUrlOverride` | `main.tsx` (ya); no reimplementar persistencia |
| Config engranaje | `MobileConfigPanel` | Toolbar de `AuthLoginLayout` (y header native si aplica). `getTenantForHealth` = valor de `loginTenant` o `readPersistedClienteCode` — **no** campo tenant en el popup (el panel GEN solo pide URL) |
| Login layout | `AuthLoginLayout` + `LanguageSelector` | `LoginPage` (ya layout). **Agregar** campo empresa `data-testid="loginTenant"` + `persistClienteCode` |
| Shell | `ShellLayout`, `UserAvatarMenu` (`isNativeApp`, `showOpenInNewTab={!isNativeApp()}`) | `ShellPage` |
| Menú native | `MobileMenuShell` | `menuSlot` cuando `isNativeApp()` |
| Menú web | `MenuSidebar` | Igual que hoy (no native) |
| Policy | `createMobilePolicy`, `genMobileExclusions`, `filterMenuItemsForMobile` | `partesMobilePolicy.ts` **deja de ser engine**; reexporta policy GEN + allowlist |
| Guard rutas | `MobileRouteGuard` | Reemplaza la lógica de `RequirePartesMobilePolicy` |
| Kardex | `ConsultaKardexList`, tipos `KardexItem` / `KardexField` / `KardexStatus`; helpers `filterKardexItems` / `sliceKardexPage` si hay filtro/página | `/partes/consulta` y `/partes/carga` (misma vista) |
| Dashboard | `DashboardContainer`, `LoadingOverlay`, `EmptyState`, `ErrorState` | `/partes` native. **Prohibido** `ProcessDataGrid` en native |
| Chat | `ChatAssistantPage` | `/chat-assistant` (TR-008) |
| i18n GEN | claves `mobile.config.*`, `consulta.kardex.*`, `mobile.exclusion.*` | Namespaces del SDK + `login.tenant` / `partes.*` del host |

### 3.2 Solo host (justificado — no generalizar a Framework)

| Pieza | Por qué queda en el host |
|-------|--------------------------|
| Const `partesMobileAllowlist` | Rutas de **este** producto. El engine GEN ya existe (`createMobilePolicy`). |
| Mapper `PartesTareaItem` → `KardexItem` | Dominio Partes (código cliente, tipo, minutos, cerrado). Un DSL genérico no aporta a otros hosts. |
| Mapper desglose paquete horas → `KardexItem` | Igual: ejes cliente/tipo son de este informe. |
| Filtro menú por `tipoFuncional` / supervisor | Reglas SPEC-002 del módulo, no GEN-22. |
| Formulario alta/edición de **una** tarea | Contrato SPEC-004; no hay form GEN de partes. Reutilizar el form de TR-004 (extraer si hace falta), no duplicar. |
| Chart DX `bar` del informe | No hay widget Chart en GEN-22. Un Chart GEN no se justifica: el informe es de producto. |
| Allowlist `/chat-assistant` | Producto habilitó GEN-21 (TR-008); otros hosts pueden no incluirlo. |

### 3.3 Prohibido en D

- `List` / cards CSS propias como presentador kardex (usar `ConsultaKardexList`).
- Reescribir matching de rutas, denylist GEN o toast de exclusión (usar `MobileRouteGuard` + i18n GEN).
- Popup de config URL propio (usar `MobileConfigPanel`).
- `ProcessDataGrid` / `PivotGrid` en ramas `isNativeApp()`.
- `window.open` / `openInNewTab` en native.

---

## 4) Reglas de implementación

R-MO-01…11 (SPEC). Detalle técnico:

| ID | Implementación |
|----|----------------|
| RN-TR-01 | `partesMobilePolicy.ts`: `export const partesMobileAllowlist = [ ... ] as const` + `export const partesMobilePolicy = createMobilePolicy({ allowlistRouteNames: [...partesMobileAllowlist] })`. **Sin** denylist propia: lo no listado no pasa; `genMobileExclusions` gana. Tests Vitest contra `partesMobilePolicy.isAllowed` / `filterItems`. |
| RN-TR-02 | Allowlist native exacta: `/partes`, `/partes/consulta`, `/partes/carga` (misma vista kardex), `/partes/informes/paquete-horas`, `/chat-assistant` (TR-008), `/change-password`, `/select-empresa`. Fuera (no listar): `/archivos/partes/**`, `/partes/proceso-masivo`, `/partes/carga-diaria`, `/partes/informes/consulta-detallada`, `/partes/informes/consultas-agrupadas`, `/admin/**`, `/parametros/**`. |
| RN-TR-03 | `RequirePartesMobilePolicy`: si `!isNativeApp()` → `<Outlet />`; si native → `<MobileRouteGuard homePath="/partes" pathname={location.pathname} isAllowed={partesMobilePolicy.isAllowed} onRedirect={(p) => navigate(p, { replace: true })}><Outlet /></MobileRouteGuard>`. i18n de exclusión = la del GEN (`mobile.exclusion.*` / `menu.forbidden`). |
| RN-TR-04 | Gráfico informe: **`devextreme-react/chart`** serie **`bar`** (eje = cliente **o** tipo según tab; default = **cliente**). Sin Pie/Polar. |
| RN-TR-05 | Fachada API: **`GET /partes/informes/paquete-horas`** (orquesta dashboard + agregación); FE no recalcula reglas. |
| RN-TR-06 | SP compuesto paquete horas = Should; orquestación PHP Must (ya). |
| RN-TR-07 | Dashboard native: selector **mes** compacto; sin auto-timer; refresh = `DashboardContainer.onRefresh` + pull si Capacitor lo permite. KPIs = widgets; ranking clientes = `ConsultaKardexList` (mapper host). |
| RN-TR-08 | Kardex: mapper host (`id`, `title` = cliente code+nombre, `subtitle` = tipo + minutos, `fields` ≤ 4, `status` cerrado/abierta). `ConsultaKardexList` `onItemTap` → form host. Default `fechaDesde=fechaHasta=hoy`. FAB/alta = `Button` DX **junto** a la lista GEN, no un segundo List. |
| RN-TR-09 | Menú seed: ítem `partes_informe_paquete_horas` → `/partes/informes/paquete-horas` (grupo Informes); visible porque está en allowlist. |
| RN-TR-10 | Smoke: unit policy (sobre `createMobilePolicy`) + `build:mobile`; humo emulador **manual** §8; CI E2E dispositivo **no** Must. |
| RN-TR-11 | `LoginPage` native/web: campo empresa primero; `data-testid="loginTenant"`; al submit `persistClienteCode` + headers `buildAuthPlatformHeaders(tenant)`. `MobileConfigPanel` en `toolbar` de `AuthLoginLayout` cuando `isNativeApp()` (también visible pre-login en native). `projectSlug="partesatencion"`. |
| RN-TR-12 | Native `menuSlot`: `MobileMenuShell` con ítems ya filtrados (`transformItems` = filtros SPEC-002 **luego** `partesMobilePolicy.filterItems`). Web: `MenuSidebar` como hoy. |
| RN-TR-13 | `/partes/consulta` y `/partes/carga` en native → **la misma** página host kardex (no `CargaDiariaPage` grilla). Web: consulta detallada / carga diaria sin cambio de contrato. |
| RN-TR-14 | Informe native: totales (texto/cards) + dos `ConsultaKardexList` (por cliente / por tipo) + Chart `bar`. **Sin** `ProcessDataGrid`. Web conserva grilla/pivot. |
| RN-TR-15 | **Una sola base API (MUST).** La URL del engranaje (`MobileConfigPanel` → `writeApiBaseUrlOverride` + cache `setCachedResolvedApiBaseUrl`) es la base de **todos** los endpoints `/api/v1/*` en native. Camino único: `bootstrapApiBaseUrl` al boot (`hydrateApiBaseUrlCache`: override Preferences → env → fallback slug) y cada llamada vía `apiRequest` → `resolveRequestUrl`. **Prohibido** en código vivo: `axios`/`httpClient` con `baseURL: '/api'`, `fetch('/api/...')` relativo, u otra base. CSS/temas DX son assets del FE (no API). Tras Save del engranaje, el cache GEN se actualiza **sin** reload. Host native: `setPreferencesAdapter` con `@capacitor/preferences` para que el override sobreviva el cierre de la app. |

---

## 5) Datos / backend

| Pieza | Detalle |
|-------|---------|
| Reuso | `GET /partes/tareas` CRUD TR-004; `GET /partes/dashboard`; `GET /partes/informes/agrupado` |
| Nuevo / ya D | `GET /partes/informes/paquete-horas?fechaDesde&fechaHasta&clienteId?&usuarioId?` → `{ totalMinutos, cantidadTareas, porCliente[], porTipo[] }` |
| Menú | Seed ítem informe; filtrado client = GEN policy + allowlist |

Sin nuevas tablas. Sin SP nuevos Must.

---

## 6) Frontend native (contrato de montaje)

| Pieza | Ruta | Montaje |
|-------|------|---------|
| Config | pre-login | `MobileConfigPanel` |
| Login | `/login` | `AuthLoginLayout` + `loginTenant` + usuario/password |
| Dashboard | `/partes` | `DashboardContainer` + kardex ranking |
| Kardex | `/partes/consulta`, `/partes/carga` | `ConsultaKardexList` + form host |
| Informe | `/partes/informes/paquete-horas` | kardex desgloses + Chart `bar` |
| Chat | `/chat-assistant` | `ChatAssistantPage` (TR-008) |
| Policy | router + menú | `createMobilePolicy` + `MobileRouteGuard` + `MobileMenuShell` |

Patrón de página (host = mapper + form; GEN = presentador):

```typescript
if (isNativeApp()) {
  return (
    <ConsultaKardexList
      items={rows.map(mapPartesTareaToKardexItem)}
      filtersSlot={/* DateBox host */}
      onItemTap={openDetalle}
      onRefresh={load}
      t={t}
    />
  )
}
return <PartesConsultaWebView />
```

Mapper (orientativo; camelCase):

```typescript
function mapPartesTareaToKardexItem(row: PartesTareaItem): KardexItem {
  return {
    id: String(row.id),
    title: `${row.clienteCode} — ${row.clienteNombre ?? ''}`.trim(),
    subtitle: `${row.tipoTareaCode} · ${row.duracionMinutos} min`,
    fields: [
      { label: t('partes.informe.field.observacion'), value: row.observacion },
    ],
    status: {
      text: row.cerrado ? t('partes.mobile.cerrada') : t('partes.mobile.abierta'),
      tone: row.cerrado ? 'success' : 'neutral',
    },
  }
}
```

---

## 7) Plan de tareas (D delta)

| ID | Tipo | Descripción | DoD | Est. |
|----|------|-------------|-----|------|
| T1 | FE | Sustituir engine `partesMobilePolicy` por `createMobilePolicy` + allowlist; tests | AC-04, AC-11 | M |
| T2 | FE | `MobileRouteGuard` + `MobileMenuShell` native; `UserAvatarMenu` ya native | AC-09 | M |
| T3 | FE | `MobileConfigPanel` + `loginTenant` + persistencia GEN cliente | AC-01…03 | M |
| T4 | FE | Kardex: `ConsultaKardexList` + mapper + form TR-004; borrar List propio | AC-05…07, AC-11 | L |
| T5 | FE | Dashboard e informe native sin DataGrid: `DashboardContainer` + kardex + Chart bar | AC-08, AC-08b | M |
| T6 | Build | Scaffold Capacitor + `build:mobile` + `cap sync`; checklist humo | AC-10 | M |
| T7 | Docs | Manual usuario ya cubre UX; ajustar testids GEN en runbook smoke si hace falta | | S |

Backend fachada/menú seed: **no rehacer** si ya está de la D previa.

---

## 8) Tests

- Vitest: allowlist vía `createMobilePolicy` (permite dashboard/kardex/informe/chat; deniega ABM/masivo/pivot/admin). Mapper kardex (1–2 casos: abierta/cerrada).
- Feature: `GET .../paquete-horas` delimitación por rol (reusa asserts).
- E2E web: no sustituye native; no exigir DataGrid en rama native.
- Manual: emulador Android (`10.0.2.2`) — config GEN, login tenant, kardex alta, informe+gráfico.

---

## 9) Checklist humo emulador (AC-10)

1. Engranaje `MobileConfigPanel`: URL + health OK + save.  
2. Login empresa+user+pass → dashboard (`DashboardContainer`).  
3. Kardex `consultaKardexList`: día actual; alta; editar; cerrada RO.  
4. Informe paquete horas: total + listas kardex + barra.  
5. Menú native (`MobileMenuShell`) sin ABM/masivo/pivot.  
6. Chat avatar in-app (`ChatAssistantPage`).  
7. `npm run build:mobile` + `npx cap sync` sin error.

---

## 10) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Bypass menú por URL | `MobileRouteGuard` + allowlist |
| Chart DX en Capacitor | Solo `bar`; sin pivot |
| Duplicar form carga | Un componente compartido con TR-004 |
| `MobileConfigPanel` exige tenant para health | Cablear `getTenantForHealth` al campo `loginTenant` / cliente persistido GEN; **no** poner tenant en el popup |
| Scaffold Capacitor ausente | T6 Must; sin él `isNativeApp()` queda false en browser |

---

## 11) Checklist DoD

- [ ] AC-01…12  
- [ ] Cero presentadores mobile clonados (AC-11)  
- [ ] Una sola base API native (AC-12 / RN-TR-15)  
- [ ] Fachada + Chart bar (ya D)  
- [ ] build:mobile + sync  
- [ ] Humo emulador documentado  

---

## 12) Informe C1

# Revisión de ambigüedad - TR-007

## Resultado general

- **Apto con observaciones** (absorbidas; realineado 2026-08-17 a GEN-22)

## Críticas cerradas

- Policy → **`createMobilePolicy`** + const allowlist de producto (no engine propio)
- Presentadores → **`MobileConfigPanel`**, **`ConsultaKardexList`**, **`MobileRouteGuard`**, **`MobileMenuShell`**, **`DashboardContainer`**
- Gráfico → **DX Chart tipo `bar`**, default serie por **cliente** (host; no hay Chart GEN)
- Fachada → **`GET /partes/informes/paquete-horas`**; orquestación PHP Must
- Rutas native → §RN-TR-02 (incluye `/chat-assistant` TR-008)
- Dashboard periodo → selector **mes** compacto; sin auto-refresh
- Smoke → unit + build:mobile; emulador manual; CI dispositivo no Must
- Mapper dominio y allowlist → **host** (no subir al Framework; ver comentario de adopción)

## Menores

- SP compuesto paquete horas = Should
- Menú Carga = misma vista kardex (`/partes/carga`)
- Scaffold Capacitor = T6 Must (gap de este clone)

## Veredicto

- **Puede pasar a D1/D delta: Sí** (reemplazo de clones por exports GEN-22; backend informe no se reabre)

---

## 13) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-30 | Parte C + C1: TR mobile; policy, Chart bar, fachada, rutas y smoke cerrados. |
| 2026-07-30 | Parte D: `partesMobilePolicy` propio, kardex List DX, paquete-horas + Chart bar, fachada API, menú/guard native. **Pendiente:** scaffold Capacitor. |
| 2026-08-17 | Realineación MUST GEN-22: montar exports `react-core` en lugar de kardex/policy/config/guard/menú propios. Allowlist + mappers de dominio quedan en el host. D delta pendiente. |
| 2026-08-17 | Parte D delta: `createMobilePolicy`, `MobileRouteGuard`, `MobileMenuShell`, `MobileConfigPanel`, `ConsultaKardexList`, `DashboardContainer`, loginTenant, Capacitor android/ios + Preferences. F1 humo emulador pendiente. |

---

**Siguiente:** F1 humo emulador (checklist TR-007 §9 / `docs/06-operacion/runbook-smoke-mobile-partes.md`).
