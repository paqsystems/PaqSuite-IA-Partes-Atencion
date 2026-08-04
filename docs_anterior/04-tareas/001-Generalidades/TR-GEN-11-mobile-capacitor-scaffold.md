# TR-GEN-11-mobile-capacitor-scaffold — Scaffold Capacitor (MONO)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-11-mobile-capacitor-scaffold](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-capacitor-scaffold.md) |
| **SPEC relacionada** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Épica** | 001 — Generalidades / Mobile |
| **Prioridad** | Must (release `v1.2.0-mobile`) |
| **Dependencias** | Frontend React/Vite existente; SPEC-001-05 (MONO) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-30 (post-D + smoke QA) |

**Origen:** [HU-GEN-11-mobile-capacitor-scaffold](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-capacitor-scaffold.md)  
**BASE:** [`01-especificacion-capacitor.md`](../../_base/01-mobile/01-especificacion-capacitor.md), [`03-comandos-generacion-aplicaciones.md`](../../_base/01-mobile/03-comandos-generacion-aplicaciones.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Instalar y configurar Capacitor sobre el frontend React MONO para Android e iOS sin regresión del build web.

### Narrativa
Como equipo MONO, quiero empaquetar la SPA con Capacitor para distribuir app instalable reutilizando el mismo backend.

### In scope / Out of scope
- **In scope:** paquetes Capacitor, `capacitor.config.ts`, Vite `base: './'`, scripts npm, proyectos `android/`/`ios/`, utilidad `isNativeApp`, plugins mínimos.
- **Out of scope:** login tenant, pantallas negocio, publicación tiendas, branding producto (TR-SPEC-101-17-mobile-v1-scaffold).

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | `npm run build:mobile` → `dist/` sin error TS |
| AC-02 | `npm run cap:sync` OK en Windows (Android) |
| AC-03 | Android Studio abre y ejecuta debug |
| AC-04 | Proyecto iOS generado; smoke documentado Mac/CI |
| AC-05 | Plugins: preferences, status-bar, splash-screen, keyboard, app |
| AC-06 | `isNativeApp()` → `false` browser, `true` emulador |
| AC-07 | `npm run build` web sin regresión |

### Escenarios Gherkin

(Heredados de HU-GEN-11-mobile-capacitor-scaffold.)

---

## 3) Reglas de negocio

1. **RN-01:** El bundle web desktop **no** se rompe; Capacitor es capa adicional.
2. **RN-02:** Proyectos nativos **sin** secretos commiteados (keystore, certs).
3. **RN-03:** `webDir` = `dist`; `androidScheme: 'https'`.
4. **RN-04:** iOS smoke obligatorio en Mac o CI `macos-latest` antes del tag `v1.2.0-mobile` (D1-10).

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-06-30)

| Campo | Valor |
|-------|--------|
| **Veredicto C1** | **Apto** |
| **Puede pasar a D1** | **Sí** |

| ID | Tema | Resolución C1 |
|----|------|---------------|
| AMB-C-001-11-03 | iOS sin Mac local | CI `macos-latest` o Mac físico documentado en §7 |
| AMB-M-001-11-03 | Plugins extra | Solo lista mínima §3 RN; push/biometría fuera v1 |

---

## 3.2) Plan D1 — Implementación

### Paquetes npm (dev + runtime)

```text
@capacitor/core @capacitor/cli @capacitor/android @capacitor/ios
@capacitor/preferences @capacitor/status-bar @capacitor/splash-screen
@capacitor/keyboard @capacitor/app
```

### `capacitor.config.ts` (orientativo)

```typescript
import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.paqsystems.SistemaPartes',
  appName: 'SistemaPartes',
  webDir: 'dist',
  server: { androidScheme: 'https' },
  android: { allowMixedContent: true }, // smoke dev HTTP local
  plugins: { StatusBar: { overlaysWebView: false } },
};

export default config;
```

### Vite

- `base: './'` en build mobile (o condicional por modo `mobile`).
- Verificar assets relativos en WebView.

### Scripts `package.json`

| Script | Acción |
|--------|--------|
| `build:mobile` | `vite build` (modo mobile) |
| `cap:sync` | `cap sync` |
| `cap:android` | `cap open android` |
| `cap:ios` | `cap open ios` |

### Utilidad plataforma

Archivo sugerido: `frontend/src/shared/platform/isNativeApp.ts`

```typescript
import { Capacitor } from '@capacitor/core';

export function isNativeApp(): boolean {
  return Capacitor.isNativePlatform();
}
```

Exportar desde barrel si existe patrón en `shared/`.

### `.gitignore`

- No ignorar `android/`/`ios/` si el repo los versiona (decisión: **sí versionar** proyectos generados, **no** keystores).
- Añadir `*.keystore`, `GoogleService-Info.plist` con secretos, etc.

---

## 4) Impacto en datos

**N/A** — slice solo frontend/nativo.

---

## 5) Contratos de API y OpenAPI

**Sin endpoints nuevos ni modificados.** No actualizar matriz permisos.

Referencia futura login/health: [TR-GEN-11-mobile-login-tenant](TR-GEN-11-mobile-login-tenant.md).

---

## 6) Cambios frontend

| Artefacto | Descripción |
|-----------|-------------|
| `frontend/capacitor.config.ts` | Config Capacitor |
| `frontend/android/`, `frontend/ios/` | Proyectos nativos |
| `frontend/vite.config.ts` | `base: './'` |
| `frontend/package.json` | Scripts + deps |
| `frontend/src/shared/platform/isNativeApp.ts` | Detección native |
| `frontend/README.md` o sección en docs BASE | Pasos dev mínimos |

### data-testid

N/A en este slice (login en TR siguiente).

---

## 7) Plan de tareas / tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Instalar deps Capacitor + plugins | `package.json` actualizado |
| T2 | Frontend | `capacitor.config.ts` + `npx cap add android/ios` | Carpetas generadas |
| T3 | Frontend | Vite `base: './'` + script `build:mobile` | Build OK |
| T4 | Frontend | `isNativeApp()` + smoke import | Unit smoke opcional |
| T5 | DevOps | Documentar smoke Android + iOS | BASE §03 actualizado |
| T6 | QA | `npm run build` web sin regresión | CI verde |

---

## 8) Estrategia de tests

- **Unit:** test mínimo `isNativeApp` con mock `Capacitor` (opcional).
- **Integration:** N/A backend.
- **E2E:** no obligatorio en scaffold; E2E login en TR login.
- **Manual:** APK debug en dispositivo; simulador iOS en Mac/CI.

---

## 9) Riesgos y edge cases

| Riesgo | Mitigación |
|--------|------------|
| Rutas SPA rotas en WebView | `base: './'` + HashRouter evaluar solo si HistoryRouter falla |
| CORS en dev | Override API en TR login; live reload documentado |
| Tamaño bundle | Sin cambio funcional; monitorear en D |

---

## 10) Checklist final

### Checklist del slice
- [x] AC-01 … AC-07 cumplidos
- [x] Documentación smoke en BASE §03, §05, §06

### Checklist normas transversales

- [x] Sin endpoints nuevos — matriz N/A
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados (post-D)

### Frontend
- `frontend/capacitor.config.ts` — `allowMixedContent`, `StatusBar`
- `frontend/android/**`, `frontend/ios/**`
- `frontend/vite.config.ts` — `base: './'`
- `frontend/package.json`
- `frontend/src/shared/platform/isNativeApp.ts`
- `frontend/index.html` — `viewport-fit=cover`
- `frontend/android/app/src/main/res/xml/network_security_config.xml`
- `frontend/android/app/src/debug/AndroidManifest.xml`

### Docs
- Referencia cruzada en `docs/_base/01-mobile/03-comandos-generacion-aplicaciones.md`










