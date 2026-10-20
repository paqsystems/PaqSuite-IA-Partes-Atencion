# SPEC-002 — Adopción de cableado SDK (host Partes)

Jira: PIAPARTES-14


| Campo | Valor |
|-------|--------|
| **ID** | SPEC-002-adopcion-cableado-sdk |
| **Título** | Invocar exports GEN del SDK en el host Partes-Atención |
| **Épica / carpeta** | 001 — Generalidades |
| **Estado** | Finalizado |
| **Última actualización** | 2026-10-12 |
| **HU** | [HU-002](../../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) |
| **TR** | [TR-002](../../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) |
| **F** | [F PA-02](../../04-tareas/updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-02.md) · [F PA-05/06](../../04-tareas/updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-05-06.md) · [F PA-07](../../04-tareas/updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-07.md) · [F PA-08/10](../../04-tareas/updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-08-10.md) · [F PA-11/13](../../04-tareas/updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-11-13.md) · [F T6](../../04-tareas/updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-T6.md) · [F PA-03/04/14](../../04-tareas/updates/001-Generalidades/F-TR-002-adopcion-cableado-sdk-PA-03-04-14.md) |
| **PA-01** | [SPEC-012](../100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) |
| **Pin** | `@paqsuite/react-core` **2.4.29-beta.1** · `paqsuite/laravel-core` **1.3.15-beta.1** |

```text
[Proceso] Cableado SDK del host Partes: adoptar GEN-20, GEN-06, GEN-19, GEN-07, GEN-02, GEN-04, GEN-01.
UI/motor = exports react-core / laravel-core. No reimplementar.
SoT: Framework SPEC-001-xx + reglas BASE 19, 34, 36, 42, 43, 44. Host: wrappers, bootstrap, envelope, i18n namespace common.
```

## 1. Resumen

Las APIs de auth y status serializan `{ error: 0, respuesta, resultado }` vía `PaqSuite\LaravelCore\Http\Responses\ApiResponse`. El host no mantiene un `ApiEnvelope` que contradiga ese contrato.

## 2. Alcance cerrado

### PA-02 (GEN-20)

- Eliminado `backend/app/Support/ApiEnvelope.php`.
- `AuthController` (scaffold) y `SystemStatusController` usan `ApiResponse`.
- Login enrutado (`Auth\LoginController`) ya usaba el SDK.

### PA-05 / PA-06 (GEN-06 i18n)

- Empresas: `registerEmpresasAdminI18nResources(i18n, 'common')` + `i18nNamespace="common"`. Sin JSON host `admin.empresas.*` / `appearance.paqsuite.*`.
- Roles: `registerSecurityRolesI18nResources(i18n, 'common')`. Wrappers pasan `t` de i18next (el SDK no traduce sin `t`). Sin JSON host `admin.roles.*`.

### PA-08 / PA-09 / PA-10 (GEN-19)

- Tema empresas: `useEmpresaAppearanceBridge`; preview/restore/guardar sin reload de página.
- `{cliente}`: landing SoT + `bootstrapClienteFromWindow` antes del Router.
- Headers: `buildPlatformHeaders`; MONO sin `X-Company-Id` / sin id de empresa como cliente.

### PA-07 (GEN-07)

- Labels del sidebar: `menuSidebarLabelsFromT` + `t('menu.*')`. Sin captions ES en `shellI18n`. Catálogo host de chrome en JSON (el pin no registra menú GEN).

### PA-11 / PA-12 / PA-13 (GEN-02 / GEN-04 / GEN-01)

- DX: un solo `syncDevExtremeLocale` con `getGuestLocale()`; `main.tsx` no fuerza `'es'`.
- Cambio de clave: `AuthCardLayout` + `authClassNames` + i18n `change.*`.
- Procesos de dominio: `ProcessPageLayout` + `useProcessMenuTitle` (menú localizado o `t()` de producto). Native: kardex, no DataGrid.

### PA-03 / PA-04 / PA-14 (GEN-06 pantallas)

- Pin `@paqsuite/react-core` **2.4.26-beta.1**.
- Wrappers `UsuariosAdminPage` / `PermisosAdminPage` SDK (`i18nNamespace="common"`, permisos `tenancyMode="single"`).
- `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources`.
- API host: aliases `name` (usuario) y `userId` (permiso) para el contrato SDK.

## 3. Fuera de alcance

- PA-01 / path Composer: [SPEC-012](../100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md).
- Mobile: admin seguridad excluido (regla 80).
- Bulk de permisos SDK: incluido en `@paqsuite/react-core` **2.4.26-beta.1** (`PermisosAdminPage` altas por usuario/rol + `POST .../permisos/batch`). El host no reimplementa el modal.
- Adapters SP GEN-06 en `laravel-core`: no bloquean el cableado FE de Partes MONO.

## 4. Reglas (slice)

| ID | Regla |
|----|--------|
| R-SDK-ENV-01 | Éxito: `error` numérico `0`; `resultado` objeto (no `null`). |
| R-SDK-ENV-02 | Prohibido un `ApiEnvelope` host que contradiga `ApiResponse`. |
| R-SDK-ADM-01 | ABM Usuarios = export SDK `UsuariosAdminPage` (`accessToken`, `platform`, `i18nNamespace`). |
| R-SDK-ADM-02 | ABM Permisos = export SDK `PermisosAdminPage` (`tenancyMode="single"` en MONO). |
| R-SDK-ADM-03 | Una sola fuente `admin.empresas.*` / `appearance.paqsuite.*` (SDK). |
| R-SDK-ADM-04 | Roles: catálogo SDK `security.roles.*`; sin mapper host GEN. |
| R-SDK-ADM-05 | `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources` en namespace `common`. |
| R-SDK-MOD-01 | Errores de guardado GEN-06 **en** el Popup (SPEC-001 / regla 36). |
| R-SDK-TEN-01 | `{cliente}` vía `bootstrapClienteFromWindow` antes del Router. |
| R-SDK-TEN-02 | Headers con `buildPlatformHeaders`. MONO: `X-Paq-Cliente` ≠ id empresa. |
| R-SDK-THM-01 | Preview/restore de tema sin reload; helpers SDK. |
| R-SDK-I18N-01 | Chrome `menu.*` vía `t` / `menuSidebarLabelsFromT`; `productI18n` solo producto. |
| R-SDK-I18N-02 | `syncDevExtremeLocale` con locale guest/sesión; no literal `'es'` al boot. |
| R-SDK-AUTH-01 | Cambio de clave: layouts auth SDK + i18n. |
| R-SDK-LAY-01 | Procesos de dominio: `ProcessPageLayout` + título menú/`t()`. |

## 5. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-11 | Parte I · PA-02 unificado desde SPEC-002-update. |
| 2026-10-11 | Parte I · PA-05 / PA-06. |
| 2026-10-11 | Parte I · PA-08 / PA-09 / PA-10. |
| 2026-10-11 | Parte I · PA-07. |
| 2026-10-11 | Parte I · PA-11 / PA-12 / PA-13. |
| 2026-10-12 | Parte F1+F+I · PA-03 / PA-04 / PA-14 (pin 2.4.25-beta.1). SPEC-002-update unificado y eliminado. Framework: bulk y SP GEN-06 laravel-core fuera de alcance. |
| 2026-10-12 | Pin `@paqsuite/react-core` **2.4.26-beta.1**: `PermisosAdminPage` altas por usuario/rol + batch. |
