# HU-GEN-11-mobile-capacitor-scaffold — Scaffold Capacitor (MONO)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-11-mobile-capacitor-scaffold |
| **SPEC origen** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Épica** | 001 — Generalidades / Mobile Capacitor |
| **Prioridad** | Must (release `v1.2.0-mobile`) |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | SPEC-001-05 (MONO); frontend React existente |

## Trazabilidad SPEC

| Criterio SPEC-001-11 | Cobertura |
|----------------------|-----------|
| Capacitor en `frontend/` | CA-01 … CA-04 |
| Android + iOS debug | CA-03, CA-04 |
| Plugins mínimos | CA-05 |
| `Capacitor.isNativePlatform()` | CA-06 |
| Scripts build mobile | CA-02 |

## Narrativa

Como **equipo de producto MONO**,  
quiero **empaquetar la SPA React con Capacitor para Android e iOS**,  
para **distribuir la misma base web como app instalable** sin duplicar backend.

## Alcance incluido

- Instalar `@capacitor/core`, `@capacitor/cli`, `@capacitor/android`, `@capacitor/ios`.
- Plugins: `@capacitor/preferences`, `@capacitor/status-bar`, `@capacitor/splash-screen`, `@capacitor/keyboard`, `@capacitor/app`.
- `capacitor.config.ts` con `webDir: dist`, `androidScheme: 'https'`.
- Vite `base: './'` para WebView.
- Scripts npm: `build:mobile`, `cap:sync`, `cap:android`, `cap:ios`.
- Carpetas `frontend/android/`, `frontend/ios/` generadas y sincronizadas.
- Utilidad `isNativeApp` (`Capacitor.isNativePlatform()`).
- Documentar en README frontend pasos mínimos dev.

## Fuera de alcance

- Login tenant (HU-GEN-11-mobile-login-tenant).
- Pantallas de negocio SistemaPartes (HU-101-031…).
- Publicación Play Store / TestFlight.
- React Native / Flutter.

## Reglas de negocio

1. El bundle web **no debe romper** el build desktop existente (`npm run build`).
2. Proyectos nativos **no** se commitean con secretos (keystore, certificados).
3. iOS smoke requiere Mac o CI `macos-latest` (SPEC D1-10).

## Criterios de aceptación

- [x] **CA-01:** `npm run build:mobile` genera `dist/` sin error TypeScript.
- [x] **CA-02:** `npm run cap:sync` completa sin error en Windows (Android).
- [x] **CA-03:** Proyecto Android abre en Android Studio y ejecuta debug.
- [ ] **CA-04:** Proyecto iOS generado; build debug documentado para Mac/CI.
- [x] **CA-05:** Plugins mínimos instalados y registrados.
- [x] **CA-06:** `isNativeApp()` retorna `false` en browser y `true` en emulador/dispositivo.
- [x] **CA-07:** CI web existente sigue pasando (sin regresión).

## Escenarios Gherkin

```gherkin
Feature: Scaffold Capacitor MONO

  Scenario: Build mobile sin regresión web
    Given el repositorio con frontend React
    When ejecuto build mobile y build web
    Then ambos completan exitosamente

  Scenario: Sync Capacitor
    When ejecuto cap sync
    Then existen proyectos android e ios con assets de dist
```

## Veredicto B1

**Lista para TR** (`TR-GEN-11-mobile-capacitor-scaffold`).










