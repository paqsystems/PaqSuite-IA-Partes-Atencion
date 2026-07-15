# TR-GEN-11-mobile-login-tenant — Login tenant-first y config API (MONO mobile)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-11-mobile-login-tenant](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-login-tenant.md), [HU-GEN-11-mobile-config-api](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-config-api.md) |
| **SPEC relacionada** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Épica** | 001 — Generalidades / Mobile |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-11-mobile-capacitor-scaffold; TR-GEN-02-login-sesion (contrato auth existente) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-30 (Parte C) |

**Origen:** HU-GEN-11-mobile-login-tenant, HU-GEN-11-mobile-config-api  
**Patrón:** [`04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Login mobile MONO con tenant explícito, persistencia Preferences, cliente HTTP dinámico y config override URL API.

### Narrativa
Como usuario mobile MONO, quiero ingresar empresa, usuario y contraseña para conectarme a la base correcta antes de autenticarme.

### In scope / Out of scope
- **In scope:** campo tenant (solo native), normalización slug, `X-Paq-Cliente` dinámico, persistencia token+tenant, precarga post-logout, popup config API, health test, `firstLogin` → change-password.
- **Out of scope:** forgot/reset password v1 native; tenant en config engranaje; cambios backend auth.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Login native: tenant + usuario + contraseña (DevExtreme) |
| AC-02 | Login web **sin** campo tenant |
| AC-03 | `POST /auth/login` incluye `X-Paq-Cliente` = tenant |
| AC-04 | Tenant `desarrollo` + seed → login OK |
| AC-05 | Tenant inexistente → error sin sesión |
| AC-06 | Token + tenant en `@capacitor/preferences` |
| AC-07 | Requests autenticadas usan mismo tenant |
| AC-08 | `firstLogin` → `/change-password` en native |
| AC-09 | i18n `login.tenant`; testids estables |
| AC-10 | Config API: guardar override + probar health |
| AC-11 | Engrane solo en native (login + header post-login) |

### Escenarios Gherkin

(Heredados de HU-GEN-11-mobile-login-tenant y HU-GEN-11-mobile-config-api.)

---

## 3) Reglas de negocio

1. **RN-01:** Orden backend: tenant válido → SQL → credenciales ([patrón §04 BASE](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md)).
2. **RN-02:** Normalización tenant: `trim` + minúsculas; patrón slug `[a-z0-9_-]+`.
3. **RN-03:** Cambio de tenant → re-login; no hot-swap con sesión activa.
4. **RN-04:** Web mantiene `VITE_TENANT_DEFAULT_CLIENT` / proxy; **sin** campo tenant.
5. **RN-05:** Resolución URL API native: `override Preferences` → `VITE_API_BASE_URL` → default producto.
6. **RN-06:** Health OK ≠ login OK; solo conectividad + tenant reconocido.
7. **RN-07:** Logout: borrar token; **precargar** tenant en formulario (D1-14).

### Persistencia Preferences (claves sugeridas)

| Clave | Contenido |
|-------|-----------|
| `SistemaPartes.mobile.tenant` | Tenant activo post-login |
| `SistemaPartes.mobile.lastTenant` | Precarga login post-logout |
| `SistemaPartes.mobile.apiBaseUrlOverride` | URL base opcional |

Token: migrar o duplicar estrategia — en native preferir Preferences sobre `localStorage` para consistencia WebView (evaluar wrapper único `authStorage`).

---

## 3.1) Informe C1 (2026-06-30)

| Campo | Valor |
|-------|--------|
| **Veredicto C1** | **Apto** |
| **Puede pasar a D1** | **Sí** |

| ID | Tema | Resolución C1 |
|----|------|---------------|
| AMB-C-001-11-01 | Tenant solo native | `isNativeApp()` guard en LoginPage |
| AMB-C-001-11-02 | Un solo submit | Sin paso intermedio obligatorio; health en config/test opcional |
| AMB-M-001-11-02 | Precarga tenant | `lastTenant` en logout |

---

## 3.2) Plan D1 — Implementación

### Cliente HTTP (`frontend/src/shared/http/client.ts`)

Refactor mínimo:

```typescript
// Pseudocódigo — implementar en D
export async function getActiveTenant(): Promise<string> {
  if (isNativeApp()) return readTenantFromPreferences() ?? '';
  return import.meta.env.VITE_TENANT_DEFAULT_CLIENT ?? 'desarrollo';
}

export async function getApiBaseUrl(): Promise<string> {
  if (isNativeApp()) {
    const override = await readApiOverrideFromPreferences();
    if (override) return override;
  }
  return import.meta.env.VITE_API_BASE_URL ?? '/api/v1';
}
```

`apiRequest` debe usar tenant y base URL **async** en native (o cache sincronizada al boot).

### LoginPage

| Elemento | Regla |
|----------|--------|
| Campo tenant | `TextBox` DevExtreme; `visible={isNativeApp()}` |
| Orden | tenant → codigo → password |
| Placeholder tenant | `desarrollo`, `demo`, … |
| testids | `loginTenant`, `loginUsername`, `loginPassword` |
| Forgot/reset links | `display: none` o no render en native v1 |

### MobileConfigPopup

- DevExtreme `Popup` + `TextBox` URL + `Button` Guardar / Probar.
- testids: `mobileConfigOpen`, `mobileConfigApiUrl`, `mobileConfigSave`, `mobileConfigTestConnection`.
- Test: `GET {baseUrl}/health` con `X-Paq-Cliente` del tenant activo o último conocido.

### authStorage

- Extender `persistAuthSession` / `clearAuthSession` para tenant en Preferences.
- Boot app: hidratar tenant antes de primera request.

---

## 4) Impacto en datos

**N/A** — sin cambios BD. Usuarios seed tenant `desarrollo` existentes.

---

## 5) Contratos de API y OpenAPI

**Sin endpoints nuevos.** Usar contratos existentes:

### 5.1 Endpoints utilizados

| Método | Path | Auth | Uso mobile |
|--------|------|------|------------|
| GET | `/api/v1/health` | Público + `X-Paq-Cliente` | Test tenant / config |
| POST | `/api/v1/auth/login` | Público + `X-Paq-Cliente` | Login |
| POST | `/api/v1/auth/logout` | Bearer + tenant | Logout |

### 5.2 OpenAPI

- [ ] Verificar documentación existente TR-GEN-02 — **sin cambio** salvo nota en description mobile tenant header.
- [ ] No nueva fila matriz permisos.

Referencia: [TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md) §5.

---

## 6) Cambios frontend

| Componente | Cambio |
|------------|--------|
| `LoginPage.tsx` | Tenant field native |
| `client.ts` | Tenant/URL dinámicos |
| `authStorage.ts` | Preferences integration |
| `MobileConfigPopup.tsx` | Nuevo |
| `MobileConfigButton.tsx` | Engrane header/login |
| `shared/mobile/mobilePreferences.ts` | Wrapper Preferences |

### i18n (claves mínimas)

| Clave | Uso |
|-------|-----|
| `login.tenant` | Label tenant |
| `mobile.config.title` | Título popup |
| `mobile.config.apiUrl` | Label URL |
| `mobile.config.testConnection` | Botón probar |
| `mobile.config.testOk` / `testFailed` | Resultado |
| `auth.tenant.invalid` | Tenant desconocido |

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Wrapper Preferences + claves | Lectura/escritura OK |
| T2 | Frontend | Refactor `client.ts` tenant/URL | Requests con header correcto |
| T3 | Frontend | LoginPage tenant native | AC-01, AC-02 |
| T4 | Frontend | MobileConfigPopup | AC-10, AC-11 |
| T5 | Frontend | authStorage native | AC-06, AC-07 |
| T6 | Frontend | Ocultar forgot/reset native v1 | AC producto 101-032 |
| T7 | Tests | Unit normalización tenant | slug edge cases |
| T8 | E2E | Login native mock o emulador | ≥1 escenario (opcional CI Android) |

---

## 8) Estrategia de tests

- **Unit:** `normalizeTenant()`, resolución URL.
- **Integration:** reutilizar `AuthLoginTest.php` con `X-Paq-Cliente: desarrollo` — **sin cambios** backend.
- **E2E:** Playwright con flag o proyecto separado; smoke manual emulador obligatorio pre-tag.

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Regresión web login | Guard `isNativeApp()` estricto |
| Async Preferences en primer request | Boot loader / await init |
| HTTP dev en release | Warning UI; HTTPS en prod build |

---

## 10) Checklist final

- [ ] AC cumplidos
- [ ] Patrón BASE §04 implementado
- [ ] Sin cambios backend no autorizados

### Checklist normas transversales

- [x] Endpoints existentes — OpenAPI ya cubierto TR-GEN-02
- [x] Envelope respetado
- [x] X-Paq-Cliente en todas las requests native

---

## Archivos creados/modificados (post-D)

### Frontend
- `frontend/src/shared/http/client.ts`
- `frontend/src/features/auth/LoginPage.tsx`
- `frontend/src/features/auth/authStorage.ts`
- `frontend/src/features/mobile/MobileConfigPopup.tsx`
- `frontend/src/shared/mobile/mobilePreferences.ts`
- `frontend/src/locales/*.json` (claves i18n)










