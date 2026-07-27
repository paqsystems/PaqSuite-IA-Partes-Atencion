# F-GEN-11 — Cierre revisión C1 (mobile Capacitor transversal)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Fecha** | 2026-06-30 |
| **Alcance** | Revisión C1 de TR-GEN-11-* (3 TR v1) |
| **Veredicto** | **Apto** — **autorizada Parte D** (implementación v1) |

## Resultado por TR

| TR | Estado C1 | Bloqueantes D1 |
|----|-----------|----------------|
| [TR-GEN-11-mobile-capacitor-scaffold](TR-GEN-11-mobile-capacitor-scaffold.md) | **Apto** | Ninguno |
| [TR-GEN-11-mobile-login-tenant](TR-GEN-11-mobile-login-tenant.md) | **Apto** | Ninguno |
| [TR-GEN-11-mobile-shell](TR-GEN-11-mobile-shell.md) | **Apto** | Ninguno |

## Decisiones transversales cerradas en C1

| Tema | Decisión |
|------|----------|
| Backend v1 | **Sin endpoints nuevos** — login/health/stock exist check existentes |
| Tenant UI | Solo `isNativeApp()`; web sin cambio |
| Preferences | Claves `SistemaPartes.mobile.*` |
| Exclusiones menú | Filtro client-side post `GET /user/menu` |
| iOS smoke | Mac/CI antes tag `v1.2.0-mobile` |
| HU config API | Fusionada en TR login-tenant |

## Orden D recomendado (v1)

Coordinar con TR-SPEC-101-17-mobile-v1-* en la misma iteración:

```text
1. TR-GEN-11-mobile-capacitor-scaffold + TR-SPEC-101-17-mobile-v1-scaffold
2. TR-GEN-11-mobile-login-tenant + TR-SPEC-101-17-mobile-v1-login-tenant
3. TR-GEN-11-mobile-shell + TR-SPEC-101-17-mobile-v1-stock-kardex
```

## Siguiente paso

**Parte D1** — implementación según TR; regla `80-mobile` autoriza código tras este cierre C1.






