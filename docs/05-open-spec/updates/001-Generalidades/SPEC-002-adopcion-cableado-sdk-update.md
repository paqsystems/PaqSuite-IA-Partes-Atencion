# SPEC-002-update – Adopción de cableado SDK (deuda GEN)

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-002-adopcion-cableado-sdk-update |
| Título | Cerrar deuda de invocación del SDK Framework en el host Partes-Atención |
| Épica / carpeta | `001-Generalidades` |
| Estado | Finalizado |
| Última actualización | 2026-10-12 |
| HU relacionada(s) | [HU-002-update](../../../03-historias-usuario/updates/001-Generalidades/HU-002-adopcion-cableado-sdk-update.md) |
| TR relacionada(s) | [TR-002-update](../../../04-tareas/updates/001-Generalidades/TR-002-adopcion-cableado-sdk-update.md) |
| Origen | `00-ControlCalidad-PQ` · 11/10/2026 · Control de Calidad #6 · [deuda-cableado-sdk-por-gen.md](../../../06-operacion/deuda-cableado-sdk-por-gen.md) |
| Pin de referencia | `@paqsuite/react-core` **2.4.25-beta.1** · `paqsuite/laravel-core` **1.3.13-beta.1** |
| Original | [SPEC-002](../../001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) (PA-02…14 **F+I**) |
| Relacionado | PA-01 (GEN-35 path Composer) → [SPEC-012](../../100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) (**F+I** 11/10/2026) |

```text
[Proceso] Cableado SDK del host Partes: adoptar GEN-20, GEN-06, GEN-19, GEN-07, GEN-02, GEN-04, GEN-01.
UI/motor = exports react-core / laravel-core listados. No reimplementar.
SoT: Framework SPEC-001-xx + reglas BASE 19, 34, 36, 42, 43, 44. Host: wrappers, bootstrap, envelope, i18n namespace common.
```

---

## 1. Resumen ejecutivo

- **Problema:** el host consume el SDK pero reimplementa pantallas/motores GEN listos, duplica catálogos i18n, arma envelope/headers/cliente/tema por cuenta propia y fuerza locale DX a `es` al boot.
- **Resultado esperado:** cada GEN en alcance se **invoca** (export + `register*I18nResources` + mismo namespace). Sin forks de pantalla/motor GEN. Envelope canónico. Bootstrap y headers del SDK.

---

## 2. Alcance

### 2.1 En alcance

Ítems CC #6 **PA-03, PA-04, PA-14** (resto F+I en SPEC-002 original / SPEC-012):

| ID | GEN | Prioridad | Qué debe quedar |
|----|-----|-----------|-----------------|
| PA-02 | 20 | P0 | **Cerrado F+I** 11/10/2026 → original SPEC-002. |
| PA-03 | 06 | P1 | Wrapper fino de `UsuariosAdminPage` SDK. |
| PA-04 | 06 | P1 | Montar `PermisosAdminPage` SDK (no tres DataGrid crudos). |
| PA-05 | 06 | P1 | **Cerrado F+I** 11/10/2026. |
| PA-06 | 06 | P1 | **Cerrado F+I** 11/10/2026. |
| PA-14 | 06 | P2 | Registrar `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources` junto con PA-03/04. |
| PA-07 | 07+02 | P1 | **Cerrado F+I** 11/10/2026. |
| PA-11 | 02 | P2 | **Cerrado F+I** 11/10/2026. |
| PA-08 | 19 | P1 | **Cerrado F+I** 11/10/2026. |
| PA-09 | 19 | P1 | **Cerrado F+I** 11/10/2026. |
| PA-10 | 19 | P1 | **Cerrado F+I** 11/10/2026. |
| PA-12 | 04 | P2 | **Cerrado F+I** 11/10/2026. |
| PA-13 | 01 | P2 | **Cerrado F+I** 11/10/2026. |

**Errores de guardado en modal** de admin GEN-06: seguir [SPEC-001](../../001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) y regla 36 al sustituir forks por wrappers SDK.

### 2.2 Fuera de alcance

- Pantallas de dominio (carga diaria, maestros, informes) salvo adoptar `ProcessPageLayout` (PA-13).
- Gaps de producto no ofrecidos: GEN-13, GEN-17, GEN-23, GEN-32.
- Rehacer `MenuSidebar` (ya cableado con `t`/`locale`/`labels`/`openInNewTab`/`onItemsLoaded`).
- Capacidades ya bien invocadas: `registerGridI18nResources`; `UserAvatarMenu`; `DashboardContainer`; `EmpresaSelectorPage`; `ParametrosGeneralesPage`; `ProcessDataGrid` (salvo permisos PA-04); `ConsultaGrillaPivotShell`; `ExcelImportToolbar`; `SmartCapturePanel`; `ChatAssistantPage`; `LlmPreferencesPanel`; `ShellLayout` + CSS SDK; `EmpresasAdminPage` con `tenancyMode="single"`, i18n `common` y preview SDK sin reload.
- PA-01 / path Composer: [SPEC-012](../../100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) (unificado F+I 11/10/2026).
- Bump de pin SDK (salvo que una Parte D posterior lo autorice).
- Mobile: admin seguridad sigue excluido (regla 80).

---

## 3. Actores y contexto

- Desarrollador del host Partes (MONO).
- Administrador que usa ABM usuarios/roles/permisos/empresas (GEN-06).
- Usuario autenticado (cambio de clave, procesos de dominio, menú).
- Precondiciones: pin react-core ≥ 2.4.20 (exports de tema y admin); pin de referencia 2.4.24-beta.1.

---

## 4. Comportamiento funcional

### 4.1 Reglas numeradas

| ID | Regla |
|----|--------|
| R-SDK-ENV-01 | Las respuestas de API de negocio y auth serializan `error` numérico `0` en éxito; `resultado` es objeto (no `null`). Clientes `isEnvelope` / `error === 0` no fallan. |
| R-SDK-ENV-02 | Prohibido un `ApiEnvelope` host que contradiga `PaqSuite\LaravelCore\ApiResponse`. |
| R-SDK-ADM-01 | ABM Usuarios = export SDK `UsuariosAdminPage`. Host: token, `platform`, permisos, menú, `i18nNamespace`. |
| R-SDK-ADM-02 | ABM Permisos = export SDK `PermisosAdminPage`. Prohibido DataGrid crudo de proceso como motor de esa pantalla. |
| R-SDK-ADM-03 | ABM Empresas: `i18nNamespace="common"` alineado a `defaultNS`. Una sola fuente de `admin.empresas.*` / `appearance.paqsuite.*` (SDK). |
| R-SDK-ADM-04 | ABM Roles: `registerSecurityRolesI18nResources`; sin mapper host de claves GEN. |
| R-SDK-ADM-05 | Registrar i18n usuarios/permisos SDK al adoptar PA-03/04. |
| R-SDK-I18N-01 | Chrome de menú (`menu.*` GEN) no se duplica en `shellI18n.ts`. Labels: `menuSidebarLabelsFromT` / `t('menu.*')`. Catálogo host = claves **producto**. |
| R-SDK-I18N-02 | Al boot, `syncDevExtremeLocale` usa el locale real (guest o sesión), no un literal `'es'` que pise EN/FR un tick. |
| R-SDK-TEN-01 | Resolución `{cliente}`: `bootstrapClienteFromWindow` **antes** del Router (regla 34). |
| R-SDK-TEN-02 | Headers plataforma: `buildPlatformHeaders`. MONO no envía el id de empresa como `X-Paq-Cliente`. |
| R-SDK-THM-01 | Persistencia/UI de tema vía helpers SDK; preview/restore **sin** reload de página. Quitar `sdkEmpresaThemeCompat` / switcher DX paralelo. |
| R-SDK-AUTH-01 | Cambio de clave usa layouts auth SDK + i18n (sin literales ES en JSX). |
| R-SDK-LAY-01 | Páginas de proceso de dominio usan `ProcessPageLayout`. Título desde menú localizado o `t()` de producto. |
| R-SDK-MOD-01 | Wrappers GEN-06 conservan errores de guardado **en** el Popup (SPEC-001 / regla 36). |

### 4.2 Orden de resolución (Parte D)

1. P0 PA-02  
2. P1 GEN-06: PA-03, PA-04, PA-05, PA-06, PA-14  
3. P1 GEN-19: PA-09, PA-10, PA-08  
4. P1 GEN-07: PA-07  
5. P2: PA-11, PA-12, PA-13  

---

## 5. Criterios verificables

- [x] PA-02: envelope éxito con `error === 0`; `resultado` objeto; auth usa el mismo contrato. (**F+I** → SPEC-002 original)
- [x] PA-03/04: no hay listado/popup/batch GEN reimplementado; se monta el export SDK. (**F+I** 12/10/2026)
- [x] PA-05/06/14: `register*I18nResources` con namespace `common`; sin JSON/mapper host de las mismas claves GEN.
- [x] PA-07: sidebar labels desde GEN; `shellI18n` no duplica `menu.*`.
- [x] PA-11: boot EN/FR no pinta DX en ES un tick por `syncDevExtremeLocale('es')`. (**F+I**)
- [x] PA-08: un puente tema SDK; preview sin reload.
- [x] PA-09: `bootstrapClienteFromWindow` antes del Router.
- [x] PA-10: `buildPlatformHeaders` en auth/API.
- [x] PA-12: cambio de clave con layout SDK + i18n. (**F+I**)
- [x] PA-13: procesos `features/partes/**` con `ProcessPageLayout` y título no literal ES. (**F+I**)
- [x] Smoke: login + una pantalla GEN tocada (empresas o usuarios/permisos) en ES y EN/FR. (**F T6** Playwright empresas SDK)
- [ ] Admin en native: sigue excluido.

---

## 6. Impacto técnico (visión para TR)

- Backend: `ApiEnvelope.php`, `AuthController.php` (y quien serialice envelope propio).
- Frontend: `UsuariosAdminPage.tsx`, `PermisosAdminPage.tsx`, `EmpresasAdminPage.tsx`, `RolesAdminPage.tsx`, `i18n.ts`, `empresasAdminHostFallback.ts`, `securityEmpresasSdkI18n.ts`, `securityRolesSdkI18n.ts`, `shellI18n.ts`, `main.tsx`, `platformContext.ts`, `sdkEmpresaThemeCompat.ts`, `devExtremeThemeSwitcher.ts`, `ChangePasswordPage.tsx`, páginas `features/partes/**`.
- Tests: ajustar Vitest/E2E de admin usuarios/permisos (CC #5) al wrapper SDK; envelope PHP; bootstrap cliente.

---

## 7. Riesgos y supuestos

- Exports reales se confirman en el pin instalado (`node_modules/@paqsuite/react-core`); no inventar nombres.
- Conservar wrap Vercel landing solo si el SoT de resolución host lo admite (PA-09).
- Labs con path Composer no bloquean esta familia (PA-01 es SPEC-012-update).
- Al reemplazar forks GEN-06, no perder AC de SPEC-001 (errores en modal).

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-11 | Parte G · CC-PQ #6 · volcado PA-02…PA-14. |
| 2026-10-11 | Parte D T1 (PA-02): sin `ApiEnvelope` host; envelope canónico `ApiResponse`. |
| 2026-10-11 | Parte F+I PA-02: originales `001-Generalidades/SPEC-002-*`. Este update queda PA-03…14. |
| 2026-10-11 | Parte F+I PA-05 / PA-06. |
| 2026-10-11 | Parte F+I PA-08 / PA-09 / PA-10. |
| 2026-10-11 | Parte F+I PA-07. |
| 2026-10-11 | Parte F+I PA-11 / PA-12 / PA-13. Cola: PA-03, PA-04, PA-14. |
| 2026-10-11 | Parte F T6: smoke ES/EN/FR empresas. Cola PA-03/04/14 (pin sin exports). |
| 2026-10-12 | Parte F+I PA-03/04/14 · pin 2.4.25-beta.1. Update Finalizado. |

---

**Trazabilidad obligatoria:** este SPEC debe enlazar HU/TR; la HU y el TR deben enlazar este archivo.
