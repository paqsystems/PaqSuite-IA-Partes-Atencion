# Análisis de valor residual desde `000-Generalidades`

## Objetivo

Registrar, antes de modificar el bloque vigente `001-Generalidades`, qué contenido histórico de `000-Generalidades` todavía podría aportar valor documental real.

## Criterio de lectura

- Este archivo es un **análisis previo**.
- No cambia por sí mismo el contenido funcional de `001-Generalidades`.
- Si luego se decide incorporar algo, debe hacerse sobre el bloque `001-*`, no reactivando `000-*`.

## Resultado resumido

La mayor parte de `000-Generalidades` ya quedó:

- absorbida por `docs/00-contexto/_mono/`;
- reemplazada por `docs/05-open-spec/001-Generalidades/`;
- o superada por un modelado más amplio y moderno en `001`.

El valor residual detectado es **acotado** y se concentra en detalles finos, no en alcance estructural.

## Matriz de revisión

| Origen en `000` | Estado frente a `001` | Valor residual detectado | Recomendación |
|---|---|---|---|
| `HU-001-layouts-grilla` | Absorbida y superada por `SPEC-001-03` + `HU-GEN-03-layouts-grilla` | Muy bajo | No migrar |
| `HU-003-apertura-menu-misma-o-nueva-pestana` | Mayormente absorbida por `SPEC-001-01` + `HU-GEN-01-menu-avatar` | Medio | Revisar si conviene explicitar nombre de campo `users.menu_abrir_nueva_pestana`, restricción solo web y expectativa de sesión reutilizable en nueva pestaña |
| `HU-004-seleccion-idioma` | Absorbida por `HU-GEN-01-idioma` | Bajo | No migrar por separado |
| `HU-005-seleccion-apariencias` | Absorbida por `SPEC-001-01` / `HU-GEN-01-apariencia-temas` | Muy bajo | No migrar |
| `HU-006-exportacion-excel` | Absorbida y ampliada por `SPEC-001-03` y `SPEC-001-08` | Bajo | No migrar; revisar solo si se quiere una convención más rígida de `data-testid` o naming de archivos |
| `HU-007-Parametros-generales` | No aplica como traslado directo: `001-Generalidades` es producto-específica y hoy define consulta de parámetros, no ABM completo | Bajo para `001`; ya resuelto en `_mono` | No migrar a `001`; mantener el comportamiento genérico en contexto común |
| `HU-008-multilingual-idiomas-banderas` | Parcialmente absorbida por `HU-GEN-01-idioma` | Medio | Revisar si conviene incorporar catálogo inicial de idiomas, iconografía visual y regla explícita de formatos por locale |
| `HU-009-eliminar-registros-grillas-abm` | Absorbida de forma fuerte por `HU-GEN-03-patron-abm` | Bajo a medio | Solo reforzar, si se desea, semántica de error `401/403/422` y feedback de confirmación |
| `HU-010-acceso-ayuda-externa-chat-compartido` | Absorbida por `HU-GEN-01-ayuda-externa` + contexto `_mono` | Muy bajo | No migrar |
| `SPEC-002-cambio-empresa-multi-referencia` | Sustituida como referencia operativa por `SPEC-001-05-variantes-y-alcance` | Bajo | Mantener solo como histórico mientras exista trazabilidad heredada |

## Hallazgos con posible valor real

### 1. Navegación en nueva pestaña

`HU-003` aporta tres precisiones que en `001` podrían quedar más explícitas:

- nombre concreto del campo de persistencia: `users.menu_abrir_nueva_pestana`;
- alcance solo **web** y exclusión explícita de mobile;
- expectativa de que abrir una nueva pestaña no obligue a reautenticarse.

Estado actual:

- `HU-GEN-01-menu-avatar` ya cubre el toggle y la persistencia;
- `SPEC-001-11-mobile-capacitor` ya aclara que mobile no usa este comportamiento.

Conclusión:

- no falta alcance;
- sí podría mejorar la precisión documental de la HU/TR de navegación.

### 2. Idioma con iconografía y catálogo inicial

`HU-008` agrega valor potencial en:

- catálogo inicial sugerido de idiomas;
- representación visual del selector con bandera o icono equivalente;
- regla explícita de formatos de fecha y número según locale;
- persistencia temporal en cliente para no autenticados, sincronizada luego con backend.

Estado actual:

- `HU-GEN-01-idioma` ya cubre login + header, persistencia en `users.locale`, fallback y actualización inmediata;
- no deja tan explícita la iconografía ni un catálogo inicial recomendado.

Conclusión:

- es el candidato con más valor residual para enriquecer `001-Generalidades`.

### 3. Eliminación en ABM con semántica de error

`HU-009` no aporta un nuevo alcance, pero sí formula con claridad:

- confirmación con identificador del registro;
- respeto explícito de `Permiso_Baja`;
- manejo consistente de errores de integridad o restricciones del backend.

Estado actual:

- `HU-GEN-03-patron-abm` ya cubre confirmación, permisos, modal y refresco;
- el tratamiento fino de `401/403/422` podría detallarse un poco más si se busca una norma transversal más estricta.

Conclusión:

- posible mejora menor, no hueco crítico.

## Recomendación priorizada

Si se decide enriquecer `001-Generalidades`, el orden sugerido es:

1. `HU-GEN-01-idioma` / `SPEC-001-01`: iconografía del selector, catálogo inicial y formatos por locale.
2. `HU-GEN-01-menu-avatar` o TR asociada: precisión del toggle de nueva pestaña (`users.menu_abrir_nueva_pestana`, solo web, nueva pestaña sin relogin).
3. `HU-GEN-03-patron-abm`: semántica más explícita de errores de baja (`403/422`) si se desea endurecer el estándar.

## No recomendado migrar

No conviene mover a `001-Generalidades`:

- la edición genérica de parámetros de `HU-007`, porque `001-Generalidades` hoy modela una consulta de parámetros propia de `SistemaPartes` y el marco reutilizable ya vive en `_mono`;
- los layouts, exportaciones, ayuda externa o apariencia de `000`, porque `001` ya los cubre igual o mejor;
- la reserva MULTI de `SPEC-002` como alcance activo, porque su lugar vigente es `SPEC-001-05-variantes-y-alcance`.



