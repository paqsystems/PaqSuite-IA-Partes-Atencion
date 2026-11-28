# HU-007-flutter — Mobile nativo Flutter v1 (Partes)

Jira: Epic **PIAPARTES-86**

## Metadatos

| Campo | Valor |
|-------|-------|
| SPEC | [SPEC-007-flutter-native-v1](../../../05-open-spec/updates/100-SistemaPartes/SPEC-007-flutter-native-v1.md) |
| Estado | **Finalizado** (F1/F/I 2026-11-28) |
| `superficie` | MOBILE_NATIVE |

## Historia

Como **usuario Partes en dispositivo móvil**, quiero una **app Flutter** con login tenant y acceso solo a **carga individual**, **consulta detallada** y **paquete de horas**, para operar el día a día sin la experiencia web completa.

## Criterios de aceptación (resumen)

1. Config URL + health antes del login; persistencia segura.
2. Login empresa + usuario + contraseña; sesión con `X-Paq-Cliente`.
3. Menú solo procesos v1; exclusiones admin/pivot/excel.
4. Los tres procesos cargan datos reales del backend (no placeholders).
5. Consultas en formato kardex (lista vertical + detalle).
6. `flutter test` cubre policy menú y bootstrap login.

## TR relacionadas

- [TR-007-flutter-native-v1](../../../04-tareas/updates/100-SistemaPartes/TR-007-flutter-native-v1.md)

## Cierre F1/F/I

- **F1:** Aprobado; evidencia en
  [`D-VERIFICACION-TR-007-flutter-native-v1.md`](../../../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-007-flutter-native-v1.md).
- **F:** cerrado con análisis, tests automatizados y smoke E2E Flutter.
- **I:** Finalizado por autorización explícita del usuario el 28/11/2026.