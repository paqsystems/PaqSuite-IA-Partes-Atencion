# TR-007-flutter — Mobile nativo Flutter v1 (Partes)

## Metadatos

| Campo | Valor |
|-------|-------|
| HU | [HU-007-flutter-native-v1](../../../03-historias-usuario/updates/100-SistemaPartes/HU-007-flutter-native-v1.md) |
| SPEC | [SPEC-007-flutter-native-v1](../../../05-open-spec/updates/100-SistemaPartes/SPEC-007-flutter-native-v1.md) |
| Estado | **Finalizado** (F1/F/I 2026-11-28) |
| Tipo TR | `TR-MOB` |
| `superficie` | MOBILE_NATIVE |
| Jira | Epic **PIAPARTES-86** |

## Alcance técnico

| Tema | Entregable |
|------|------------|
| T1 Plataforma | `core/*`, login, config, envelope |
| T2 Menú | `partes_mobile_menu_policy.dart`, drawer |
| T3 Carga | API tareas + formulario móvil |
| T4 Consulta detallada | API informe tareas + kardex |
| T5 Paquete horas | API paquete + kardex agregado |
| T6 QA | manual por tema + `flutter test` |

## Carpetas

- `mobile/lib/`

## Dependencias

- Backend Partes sin cambios de contrato (reutiliza APIs web).

## Cierre F1/F/I

- **F1:** Aprobado; evidencia en
  [`D-VERIFICACION-TR-007-flutter-native-v1.md`](D-VERIFICACION-TR-007-flutter-native-v1.md).
- **F:** cerrado con `flutter analyze`, `flutter test` (10 tests) y smoke E2E
  en emulador Android Flutter.
- **I:** Finalizado por autorización explícita del usuario el 28/11/2026.