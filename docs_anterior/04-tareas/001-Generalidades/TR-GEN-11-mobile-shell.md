# TR-GEN-11-mobile-shell — Shell mobile y exclusiones (MONO)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-11-mobile-shell-exclusiones](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-shell-exclusiones.md) |
| **SPEC relacionada** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Épica** | 001 — Generalidades / Mobile |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-11-mobile-login-tenant; TR-GEN-01-shell-layout; TR-GEN-01-idioma; TR-GEN-01-apariencia-temas |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-30 (post-D + smoke QA) |

**Origen:** [HU-GEN-11-mobile-shell-exclusiones](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-shell-exclusiones.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Adaptar shell post-login para native: drawer, safe areas, idioma/tema, menú filtrado y bloqueo rutas desktop-only.

### Narrativa
Como usuario mobile, quiero un shell simplificado sin pivot, Excel, admin ni pestañas separadas.

### In scope / Out of scope
- **In scope:** drawer overlay, safe areas, filtros menú/rutas, bloqueo navegación directa, idioma+tema header (D1-17), sin `openInNewTab`.
- **Out of scope:** dashboard completo; chat assistant; contenido kardex (TR producto stock); **controles expandir/contraer árbol menú y vista ramas** en native v1 (simplificación UX).

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| AC-01 | Menú drawer operativo en native |
| AC-02 | Rutas excluidas no en menú native |
| AC-03 | `/admin/*` manual → bloqueado/redirect |
| AC-04 | Sin toggle pestañas separadas en avatar mobile |
| AC-05 | Selector idioma y tema en header |
| AC-06 | Safe area notch — header no tapado |
| AC-07 | `window.open` no usado en ítems menú native |

### Escenarios Gherkin

(Heredados de HU-GEN-11-mobile-shell-exclusiones.)

---

## 3) Reglas de negocio

1. **RN-01:** Filtro cliente es **adicional** a permisos backend.
2. **RN-02:** Lista exclusiones (SPEC-001-11):

| Patrón | Acción |
|--------|--------|
| `/pivot/*`, informes pivot | Ocultar + guard |
| `/excel-import/*` | Ocultar + guard |
| `/admin/*` | Ocultar + guard |
| Preferencia `openInNewTab` | No renderizar; ignorar en native |

3. **RN-03:** Navegación menú siempre in-app (`navigate()`).
4. **RN-04:** `@capacitor/status-bar` — estilo coherente con tema (opcional v1 mínimo).

### Módulo sugerido: `mobileMenuPolicy.ts`

```typescript
export const mobileExcludedRoutePrefixes = [
  '/admin',
  '/excel-import',
  '/pivot',
  '/informes/pivot',
] as const;

export function isRouteAllowedOnMobile(pathname: string): boolean {
  if (!isNativeApp()) return true;
  return !mobileExcludedRoutePrefixes.some((p) => pathname.startsWith(p));
}

export function filterMenuItemsForMobile<T extends { route?: string }>(items: T[]): T[] {
  if (!isNativeApp()) return items;
  return items.filter((item) => item.route && isRouteAllowedOnMobile(item.route));
}
```

Ajustar prefijos según rutas reales en `SistemaPartesRoutes.tsx` / menú seed.

---

## 3.1) Informe C1 (2026-06-30)

| Campo | Valor |
|-------|--------|
| **Veredicto C1** | **Apto** |
| **Puede pasar a D1** | **Sí** |

| ID | Tema | Resolución C1 |
|----|------|---------------|
| AMB-M-001-11-04 | `openInNewTab` en preferences | Ignorar en native; ocultar toggle avatar |
| D1-17 | Idioma/tema v1 | Mantener selectores header — reutilizar TR-GEN-01-idioma/apariencia |

---

## 3.2) Plan D1 — Implementación

### Shell layout

- En native `<768px`: sidebar como **drawer** overlay (patrón existente responsive — verificar y endurecer).
- CSS safe area en header/footer:

```css
.mobileShellHeader {
  padding-top: env(safe-area-inset-top);
}
```

### Route guard

- Componente `MobileRouteGuard` envuelve rutas en router o check en layout:
  - Si native && ruta excluida → redirect `/consultas/stock` (v1) o `/` según sesión.

### Menú API

- Filtrar respuesta `GET /user/menu` **después** de recibir (client-side) con `filterMenuItemsForMobile`.
- No modificar backend menú en v1.

### Avatar / preferencias

- Ocultar control «pestañas separadas» cuando `isNativeApp()`.
- Mantener `LocaleSelector` y selector tema visibles.

### UX native v1 (post-smoke Android — 2026-06-30)

| Tema | Implementación |
|------|----------------|
| Drawer | Overlay con `shellLayoutSidebarOverlay`; backdrop clickeable (`shellSidebarBackdrop`) |
| Cierre drawer | Al cambiar ruta; al elegir ítem menú; backdrop; sidebar **cerrado por defecto** en native |
| Controles menú header | Solo ☰ en native (`MenuToolbarControls` `compact`); sin expandir/contraer árbol ni vista ramas |
| **Estado** | Pendiente |
| Vista menú | `operationalOnly` forzado en native v1 → ítem **Stock** plano |
| Safe area | `viewport-fit=cover`; `StatusBar.setOverlaysWebView({ overlay: false })`; `--shell-status-bar-inset: max(env(safe-area-inset-top), 28px)` |
| Header | `z-index: 30`; `LocaleSelector` modo `compact` |

### UX native v2 (`v1.2.1-mobile` — 2026-06-30)

| Tema | Implementación |
|------|----------------|
| Rutas MVP | `mobileV2AllowedRoutePrefixes` en `SistemaPartesMobilePolicy.ts` |
| Menú | `filterMenuTreeForMobileV2` — ítems según permiso web |
| Guard | `isRouteAllowedOnMobileApp` — bloquea `/pedidos/carga`, dashboard, admin, excel |
| Redirect bloqueado | `getMobileDefaultRoute()` → `/consultas/stock` |

---

## 4) Impacto en datos

**N/A**

---

## 5) Contratos de API y OpenAPI

**Sin endpoints nuevos.**

| Método | Path | Uso |
|--------|------|-----|
| GET | `/api/v1/user/menu` | Menú filtrado client-side |
| GET | `/api/v1/user/preferences` | Ignorar `openInNewTab` en UX native |

OpenAPI: sin cambios. Matriz: sin cambios.

---

## 6) Cambios frontend

| Archivo | Cambio |
|---------|--------|
| `ShellLayout.tsx` (o equivalente) | Drawer + safe areas |
| `mobileMenuPolicy.ts` | Nuevo |
| `MobileRouteGuard.tsx` | Nuevo |
| `MenuSidebar.tsx` | Filtro native |
| `MenuAvatarPopup.tsx` | Ocultar openInNewTab |
| `App.css` / shell CSS | safe-area |

### data-testid

Preservar existentes shell; añadir si falta `menuDrawer` en mobile.

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | `mobileMenuPolicy` + tests unit | Prefixes correctos |
| T2 | Frontend | Filtro menú post-fetch | AC-02 |
| T3 | Frontend | Route guard exclusiones | AC-03 |
| T4 | Frontend | Drawer + safe areas | AC-01, AC-06 |
| T5 | Frontend | Ocultar openInNewTab native | AC-04 |
| T6 | Frontend | Idioma/tema header visible | AC-05 |
| T7 | E2E | Navegación admin bloqueada native | ≥1 escenario manual/E2E |

---

## 8) Estrategia de tests

- **Unit:** `isRouteAllowedOnMobile`, `filterMenuItemsForMobile`.
- **E2E:** intento navegar `/admin/roles` → redirect (con mock native o flag test).

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Rutas pivot con path distinto | Auditar `SistemaPartesRoutes.tsx` al implementar |
| Deep link a ruta excluida | Guard centralizado |

---

## 10) Checklist final

- [x] Exclusiones SPEC-001-11 aplicadas
- [x] Drawer + safe area smoke Android emulador
- [x] Sin regresión shell web desktop (tests automatizados)

---

## Archivos creados/modificados (post-D)

### Frontend
- `frontend/src/features/mobile/mobileMenuPolicy.ts`
- `frontend/src/features/mobile/SistemaPartesMobilePolicy.ts`
- `frontend/src/features/mobile/MobileRouteGuard.tsx`
- `frontend/src/app/layout/ShellLayout.tsx` — drawer, backdrop, cierre ruta native
- `frontend/src/app/layout/ShellHeader.tsx` — menú derecha native, `shellHeader--native`
- `frontend/src/app/layout/MenuToolbarControls.tsx` — `compact` + botón DevExtreme `menu`
- `frontend/src/app/layout/shellLayout.css` — safe area, header native
- `frontend/src/features/menu/hooks/useMenuPresentation.ts` — estado funcional sidebar
- `frontend/src/shared/mobile/mobileRuntime.ts` — `StatusBar.setOverlaysWebView(false)`
- `frontend/index.html` — `viewport-fit=cover`
- `frontend/capacitor.config.ts` — `plugins.StatusBar.overlaysWebView: false`










