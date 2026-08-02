# HU-GEN-11-mobile-config-api — Configuración URL API (mobile)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-11-mobile-config-api |
| **SPEC origen** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Épica** | 001 — Generalidades / Mobile |
| **Prioridad** | Must (v1) |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | HU-GEN-11-mobile-capacitor-scaffold; HU-GEN-11-mobile-login-tenant |

## Narrativa

Como **usuario o soporte en app mobile**,  
quiero **configurar opcionalmente la URL del backend y probar conectividad**,  
para **apuntar a staging o entorno local sin recompilar la app**.

## Alcance incluido

- Icono configuración (`data-testid="mobileConfigOpen"`) en login native y header post-login.
- Popup/pantalla DevExtreme con override `apiBaseUrl` (sin campo tenant).
- Botón **Probar conexión:** `GET {url}/health` con `X-Paq-Cliente` del tenant en login o último guardado.
- Botón **Guardar** → `@capacitor/preferences`.
- Resolución URL en cliente HTTP native: override → env build → default producto.
- Producción release: default `https://backend.{proyecto}.paqsystems.com/api/v1`.

## Fuera de alcance

- Editar tenant desde config (solo login).
- VPN / certificados pinning.
- Multi-backend por usuario.

## Reglas de negocio

1. Override solo aplica en **native**.
2. Health OK no implica login OK; solo conectividad + tenant reconocido.
3. HTTPS obligatorio en builds release (warning en dev HTTP).

## Criterios de aceptación

- [x] **CA-01:** Engrane visible solo en native.
- [x] **CA-02:** Guardar URL persiste y siguiente request la usa.
- [x] **CA-03:** Probar conexión muestra éxito/error i18n.
- [x] **CA-04:** Sin override, usa URL embebida SistemaPartes prod/dev según build.
- [x] **CA-05:** testids `mobileConfigSave`, `mobileConfigApiUrl`, `mobileConfigTestConnection`.

## Veredicto B1

**Lista para TR** (puede fusionarse en TR scaffold/login según slice; referenciar en TR-GEN-11-mobile-login-tenant).










