# F-GEN-11 — Cierre revisión B1 (mobile Capacitor transversal)

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-11-mobile-capacitor](../../05-open-spec/001-Generalidades/SPEC-001-11-mobile-capacitor.md) |
| **Fecha** | 2026-06-30 |
| **Alcance** | Revisión B1 de HU-GEN-11-* (4 HU) |
| **Veredicto** | **Apto** — **autorizada Parte C** (TR-GEN-11-*) |

## Resultado por HU

| HU | Veredicto B1 | Lista para TR |
|----|--------------|---------------|
| [HU-GEN-11-mobile-capacitor-scaffold](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-capacitor-scaffold.md) | **Apto** | Sí — orden **1** |
| [HU-GEN-11-mobile-login-tenant](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-login-tenant.md) | **Apto** | Sí — orden **2** |
| [HU-GEN-11-mobile-config-api](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-config-api.md) | **Apto** | Sí — embebida en TR scaffold/login |
| [HU-GEN-11-mobile-shell-exclusiones](../../03-historias-usuario/001-Generalidades/HU-GEN-11-mobile-shell-exclusiones.md) | **Apto** | Sí — orden **3** |

## Checklist B1 transversal

| Área | Estado | Notas |
|------|--------|-------|
| Cobertura SPEC CA transversal | OK | Scaffold, login tenant-first, config API, shell |
| Patrón login tenant MONO | OK | `04-patron-login-tenant-mobile-mono.md` |
| Plataformas Android + iOS v1 | OK | Capacitor desde v1 |
| Exclusiones mobile | OK | Pivot, Excel import, admin seguridad, openInNewTab |
| i18n / tema shell v1 | OK | Hereda GEN-01 (D1-17 producto) |
| Tags release | OK | v1 `v1.2.0-mobile`; v2/v3 en SPEC-101-17 |
| Dependencia SPEC-101-17 | OK | HU producto 031–036 alineadas |

## Orden C recomendado (v1)

```text
1. TR-GEN-11-mobile-capacitor-scaffold
2. TR-GEN-11-mobile-login-tenant
3. TR-GEN-11-mobile-shell
```

Coordinar con TR-SPEC-101-17-mobile-v1-* en la misma iteración.

## Siguiente paso

Generar TR-GEN-11-* y TR-SPEC-101-17-mobile-v1-*; **no implementar código** hasta cierre C1.






