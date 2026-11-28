# SPEC-007-flutter — Mobile nativo Flutter v1 (Partes)

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-007-flutter-native-v1 |
| Título | Partes mobile v1 — cliente Flutter (`mobile/`), tres procesos |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | **Finalizado** (F1/F/I 2026-11-28) |
| SPEC relacionado (web/Capacitor) | [SPEC-007-update-mobile-v1](./SPEC-007-mobile-v1-alcance-update.md) · **stack distinto** |
| Fuentes producto | [`21-mobile-v1-alcance-gen22.md`](../../../02-producto/Sistema-Partes-IA/21-mobile-v1-alcance-gen22.md) · [`10-mobile.md`](../../../02-producto/Sistema-Partes-IA/10-mobile.md) |
| Framework | GEN-33 piloto (`smoke-flutter`) · envelope GEN-20 · login tenant MONO |
| `superficie` | `MOBILE_NATIVE` |
| `stackCliente` | Flutter (`mobile/`) |
| `modalidad` | MONO |
| `backend` | PROPIO (mismas APIs `/api/v1/partes/*`) |
| `repositorioPropietario` | PaqSuite-IA-Partes-Atencion |
| Jira | **PIAPARTES-86** |

**Plantilla regla 19 (ajustada nativo):**

```text
Mobile v1 Partes-Atención Flutter: SoT SPEC-007-flutter. Host: mobile/lib (core/api, policy menú v1, kardex, APIs Partes).
No @paqsuite/react-core. Paridad funcional con decisión producto v1 (3 procesos); no Capacitor.
```

---

## 1. Resumen

Cliente **Flutter nativo** en `mobile/`, mismo backend Laravel. Alcance **idéntico en procesos** al update Capacitor v1, distinto **presentador** y stack.

## 2. Alcance v1

| # | Ruta menú | API / comportamiento |
|---|-----------|---------------------|
| 1 | `/partes/carga` | Carga individual: listado día + alta/edición tarea (`/api/v1/partes/tareas`) |
| 2 | `/partes/informes/consulta-detallada` | Kardex lectura (`/api/v1/partes/informes/tareas`) |
| 3 | `/partes/informes/paquete-horas` | Cuenta corriente (`/api/v1/partes/informes/paquete-horas`) |

Transversal:

- Config URL + health (`X-Paq-Cliente` en health desde login).
- Login tenant-first.
- Menú: `PartesMobileMenuPolicy` (allowlist + exclusiones GEN).
- Sin admin, pivot, excel-import, emisiones pivot, openInNewTab.

## 3. Fuera de alcance

- Capacitor / `frontend/` android-ios como producto entregable de **esta** épica.
- Dashboard, maestros, masivo, Excel, emisiones PDF.
- Paquete `@paqsuite/flutter-core` hasta publicación Framework (copiar patrones smoke-flutter).

## 4. QA por tema

Ver [`docs/06-operacion/manual-qa-mobile-flutter-por-tema.md`](../../../06-operacion/manual-qa-mobile-flutter-por-tema.md).

## 5. Trazabilidad

| Artefacto | Ruta |
|-----------|------|
| HU | `docs/03-historias-usuario/updates/100-SistemaPartes/HU-007-flutter-native-v1.md` |
| TR | `docs/04-tareas/updates/100-SistemaPartes/TR-007-flutter-native-v1.md` |
| Runbook | `docs/06-operacion/runbook-smoke-mobile-flutter-partes.md` |

## 6. Cierre F1/F/I

- **F1:** Aprobado. Evidencia en
  [`D-VERIFICACION-TR-007-flutter-native-v1.md`](../../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-007-flutter-native-v1.md).
- **F:** cerrado con `flutter analyze`, `flutter test` (10 tests) y smoke E2E
  en emulador Android Flutter.
- **I:** Finalizado por autorización explícita del usuario el 28/11/2026.