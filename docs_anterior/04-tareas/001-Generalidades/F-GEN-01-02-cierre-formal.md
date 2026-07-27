# Cierre F Formal — Oleada GEN-01 / GEN-02

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


## Alcance del cierre

Este cierre F formal cubre los slices **planificados y verificados** de la oleada `GEN-01` / `GEN-02` que fueron trabajados y contrastados contra código en esta etapa:

- `HU-GEN-01-shell-layout` / `TR-GEN-01-shell-layout`
- `HU-GEN-01-idioma` / `TR-GEN-01-idioma`
- `HU-GEN-01-apariencia-temas` / `TR-GEN-01-apariencia-temas`
- `HU-GEN-02-login-sesion` / `TR-GEN-02-login-sesion`
- `HU-GEN-02-cambio-contrasena` / `TR-GEN-02-cambio-contrasena`
- `HU-GEN-02-recuperacion-contrasena` / `TR-GEN-02-recuperacion-contrasena`

Quedan **fuera de este cierre F** los documentos `GEN-01/GEN-02` que permanecen pendientes o no fueron re-verificados en esta oleada.

## Resultado global

- **Aprobado con observaciones**

## Resumen por slice

| Slice | Resultado F | Observación principal |
|------|-------------|-----------------------|
| `TR-GEN-01-shell-layout` | Aprobado | Evidencia D suficiente y sin hallazgos críticos abiertos. |
| `TR-GEN-01-idioma` | Aprobado | Evidencia D suficiente; además quedó integrada la propagación de locale en recuperación. |
| `TR-GEN-01-apariencia-temas` | Aprobado con observaciones | El shell quedó alineado a la paleta del tema activo; la evidencia backend se apoya en verificaciones previas documentadas y tests frontend recientes. |
| `TR-GEN-02-login-sesion` | Aprobado con observaciones | La evidencia D es consistente, pero el cierre F se apoya en smoke/tests ya documentados y no en una re-ejecución completa integral en esta oleada. |
| `TR-GEN-02-cambio-contrasena` | Aprobado | Evidencia D consistente y cobertura funcional/e2e documentada. |
| `TR-GEN-02-recuperacion-contrasena` | Aprobado con observaciones | Frontend y comportamiento funcional verificados; tests backend feature siguen bloqueados por seed/entorno SQL Server. |

## Evidencia consolidada revisada

- Evidencia D ya documentada en cada TR objetivo.
- Verificaciones focalizadas ejecutadas en esta oleada:
  - `npm run test:e2e -- tests/e2e/password-recovery.spec.ts` -> **OK**
  - `npm run test:e2e -- tests/e2e/theme.spec.ts` -> **OK**
  - `npm run test -- src/features/theme/model/resolveThemePalette.test.ts src/features/theme/model/normalizeThemeKey.test.ts` -> **OK**
  - `php artisan test --filter=PasswordRecoveryTest` -> **bloqueado** por fallo previo del seed `paqsuite:seed-seguridad-mvp`
- Contraste manual de código/documentación en:
  - recuperación con `locale` propagado en el enlace del mail;
  - `ResetPasswordPage` y `ForgotPasswordPage` homogéneas con el patrón MONO;
  - shell autenticado consumiendo paleta derivada del tema activo;
  - documentación TR/HU alineada con comportamiento implementado.

## Hallazgos críticos

- No se detectan hallazgos críticos de desalineación entre los slices incluidos y el código actualmente generado.

## Advertencias

- La evidencia backend de `TR-GEN-02-recuperacion-contrasena` no está completamente cerrada por una falla previa del entorno/seed, ajena al cambio funcional actual.
- La verificación formal de `TR-GEN-02-login-sesion` en esta oleada reutiliza smoke y pruebas ya documentadas en el propio TR; no se re-ejecutó una suite backend integral específica en esta pasada.
- Este documento **no** declara cierre F de todos los `GEN-01/GEN-02` existentes en la carpeta, sino de la oleada explicitada en el alcance.

## Recomendación final

- Tratar los slices incluidos como **cerrados en F formal con observaciones**, manteniendo una tarea técnica pendiente para re-ejecutar `PasswordRecoveryTest` cuando el seed/entorno SQL Server vuelva a estar estable.






