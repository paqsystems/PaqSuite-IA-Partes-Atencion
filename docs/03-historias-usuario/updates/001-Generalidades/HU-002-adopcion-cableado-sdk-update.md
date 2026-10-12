# HU-002-update – Adopción de cableado SDK (deuda GEN)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-002-adopcion-cableado-sdk-update |
| Título | Invocar exports GEN del SDK en lugar de forks del host |
| Épica / carpeta | `001-Generalidades` |
| Clasificación | MUST-HAVE |
| Estado | Finalizado |
| Última actualización | 2026-10-12 |
| SPEC origen | [SPEC-002-update](../../../05-open-spec/updates/001-Generalidades/SPEC-002-adopcion-cableado-sdk-update.md) |
| TR relacionada(s) | [TR-002-update](../../../04-tareas/updates/001-Generalidades/TR-002-adopcion-cableado-sdk-update.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 11/10/2026 |
| Ítem | Control de Calidad #6 · deuda cableado SDK PA-02…PA-14 |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-002 | Dónde en esta HU |
|--------------------------------|------------------|
| Envelope canónico PA-02 | CA-ENV-01; R-SDK-ENV-* |
| Wrappers GEN-06 PA-03/04/05/06/14 | CA-ADM-*; R-SDK-ADM-* |
| i18n menú / DX PA-07/11 | CA-I18N-*; R-SDK-I18N-* |
| Tenancy / tema / headers PA-08/09/10 | CA-TEN-*; CA-THM-01 |
| Auth cambio clave PA-12 | CA-AUTH-01 |
| ProcessPageLayout PA-13 | CA-LAY-01 |
| Errores modal SPEC-001 | CA-ADM-06 |
| Fuera de alcance dominio / GEN no ofrecidos / PA-01 | Fuera de alcance |

---

## Narrativa

Como equipo que mantiene el host Partes sobre el SDK PaqSuite  
quiero cablear los exports GEN ya listos (envelope, admin, shell, i18n, auth layouts, process layout)  
para no duplicar pantallas ni contratos que el Framework ya cubre y poder bump-ear el SDK sin drift.

---

## Alcance incluido

- PA-02 … PA-14 según [SPEC-002-update](../../../05-open-spec/updates/001-Generalidades/SPEC-002-adopcion-cableado-sdk-update.md).
- Conservar el comportamiento de errores en modal (HU-001 / regla 36) al pasar admin a wrappers SDK.

## Fuera de alcance

- Carga diaria, maestros, informes (salvo layout de proceso PA-13).
- GEN-13, GEN-17, GEN-23, GEN-32.
- Rehacer `MenuSidebar`.
- PA-01 path Composer ([HU-012](../../100-SistemaPartes/HU-012-adopcion-gen-35-cloudsmith.md), F+I 11/10/2026).
- Habilitar admin seguridad en mobile.

---

## Reglas de negocio / UX

| ID | Regla |
|----|--------|
| R-SDK-ENV-01 … R-SDK-LAY-01 | Ver SPEC-002-update §4.1 |

---

## Criterios de aceptación

- [x] **CA-ENV-01** Login y endpoints que usaban `ApiEnvelope` responden `error: 0` (número) y `resultado` objeto en éxito; el cliente `isEnvelope` no rechaza el payload. **(F+I PA-02 → HU-002 original)**
- [x] **CA-ADM-01** ABM Usuarios es el export SDK (wrapper fino); alta/edición/inhabilitación no están reimplementadas en el host. **(F+I PA-03, pin 2.4.25-beta.1)**
- [x] **CA-ADM-02** ABM Permisos es el export SDK; no hay tres DataGrid DX como motor propio. **(F+I PA-04)**
- [x] **CA-ADM-03** ABM Empresas usa `i18nNamespace="common"`; no hay triple fuente de `admin.empresas.*` / `appearance.paqsuite.*`. **(F+I PA-05)**
- [x] **CA-ADM-04** ABM Roles registra recursos SDK; no mapea claves GEN desde un `t` host. **(F+I PA-06)**
- [x] **CA-ADM-05** i18n usuarios/permisos SDK registrado al cerrar CA-ADM-01/02. **(F+I PA-14)**
- [x] **CA-ADM-06** Guardado con error en esos ABM sigue visible **en** el Popup (HU-001). **(F PA-03: SDK `adminUsuariosFormError`)**
- [x] **CA-I18N-01** Chrome `menu.*` no está forkeado en `shellI18n`; labels del sidebar vía helper SDK / `t('menu.*')`. **(F+I PA-07)**
- [x] **CA-I18N-02** Arranque con locale EN o FR no deja DevExtreme en español un tick por `syncDevExtremeLocale('es')`. **(F+I PA-11)**
- [x] **CA-THM-01** Tema empresa: helpers SDK; Aplicar/preview no recarga la página. **(F+I PA-08)**
- [x] **CA-TEN-01** `{cliente}` se resuelve con `bootstrapClienteFromWindow` antes del Router. **(F+I PA-09)**
- [x] **CA-TEN-02** Headers con `buildPlatformHeaders`; en MONO `X-Paq-Cliente` no es el id de empresa. **(F+I PA-10)**
- [x] **CA-AUTH-01** Cambio de contraseña usa layout auth SDK y textos i18n. **(F+I PA-12)**
- [x] **CA-LAY-01** Procesos de dominio Partes usan `ProcessPageLayout` y título localizado (menú o `t()`). **(F+I PA-13)**
- [x] **CA-SMK-01** Smoke login + empresas o usuarios/permisos en ES y EN/FR. **(F T6 Playwright empresas SDK)**

---

## Escenarios Gherkin

```gherkin
Scenario: Envelope de éxito es canónico
  When el cliente llama login o un endpoint de negocio que antes usaba ApiEnvelope host
  Then la respuesta JSON tiene error igual a 0 (número)
  And resultado es un objeto

Scenario: Admin usuarios usa el SDK
  Given soy administrador en /admin/usuarios
  Then la pantalla es el export UsuariosAdminPage del SDK
  And si el guardado falla el error se ve en el modal

Scenario: Locale guest no español al arrancar
  Given el locale persistido es fr
  When cargo la SPA
  Then DevExtreme no queda forzado a es por un sync inicial hardcodeado
```

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-11 | Parte G · CC-PQ #6 · volcado PA-02…PA-14. |
| 2026-10-11 | Parte D T1 (PA-02): envelope canónico `ApiResponse`; CA-ENV-01 en código. |
| 2026-10-11 | Parte F+I PA-02. CA-ENV-01 cerrado. Cola PA-03…14. |
| 2026-10-11 | Parte F+I PA-05 / PA-06. |
| 2026-10-11 | Parte F+I PA-08 / PA-09 / PA-10. |
| 2026-10-11 | Parte F+I PA-07. |
| 2026-10-11 | Parte F+I PA-11 / PA-12 / PA-13. |
| 2026-10-11 | Parte F T6: CA-SMK-01 y CA-ADM-06 residual. CA-ADM-01/02/05 siguen abiertos (pin). |
| 2026-10-12 | Parte F+I PA-03/04/14. CA-ADM-01/02/05/06 cerrados. Update Finalizado. |
