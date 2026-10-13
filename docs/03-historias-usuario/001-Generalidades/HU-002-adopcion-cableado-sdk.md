# HU-002 — Adopción de cableado SDK (slice PA-02)

Jira: PIAPARTES-28


## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-002-adopcion-cableado-sdk |
| Título | Invocar exports GEN del SDK en el host Partes-Atención |
| Épica | 001 — Generalidades |
| Clasificación | MUST-HAVE |
| Estado | Finalizado |
| SPEC | [SPEC-002](../../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) |
| TR | [TR-002](../../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) |

## Criterios de aceptación

- [x] **CA-ENV-01** Login y endpoints que usaban `ApiEnvelope` responden `error: 0` y `resultado` objeto.
- [x] **CA-ADM-03** ABM Empresas: `i18nNamespace="common"`; sin JSON host de claves GEN.
- [x] **CA-ADM-04** ABM Roles: `registerSecurityRolesI18nResources`; `t` de i18n con claves SDK.
- [x] **CA-THM-01** Preview/restore tema SDK sin reload.
- [x] **CA-TEN-01** `bootstrapClienteFromWindow` antes del Router.
- [x] **CA-TEN-02** `buildPlatformHeaders`; MONO sin id empresa como cliente.
- [x] **CA-I18N-01** Sidebar `menuSidebarLabelsFromT`; chrome GEN vía `t('menu.*')`.
- [x] **CA-I18N-02** Boot DX con locale guest; sin `syncDevExtremeLocale('es')`.
- [x] **CA-AUTH-01** Cambio de contraseña: `AuthCardLayout` + i18n `change.*`.
- [x] **CA-LAY-01** Procesos Partes: `ProcessPageLayout` + título localizado.
- [x] **CA-ADM-01** ABM Usuarios = export SDK (pin 2.4.25-beta.1).
- [x] **CA-ADM-02** ABM Permisos = export SDK.
- [x] **CA-ADM-05** i18n usuarios/permisos SDK registrado.
- [x] **CA-ADM-06** Error de guardado en Popup (`adminUsuariosFormError`).
- [x] **CA-SMK-01** Smoke empresas SDK ES/EN/FR.

**Fuera de alcance:** bulk de permisos SDK (diferido Framework); adapters SP GEN-06 de laravel-core; admin en mobile.

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-11 | Parte I · PA-02. |
| 2026-10-11 | Parte I · PA-05 / PA-06. |
| 2026-10-11 | Parte I · PA-08 / PA-09 / PA-10. |
| 2026-10-11 | Parte I · PA-07. |
| 2026-10-11 | Parte I · PA-11 / PA-12 / PA-13. |
| 2026-10-12 | Parte F1+F+I · PA-03 / PA-04 / PA-14. HU-002-update unificado y eliminado. |
