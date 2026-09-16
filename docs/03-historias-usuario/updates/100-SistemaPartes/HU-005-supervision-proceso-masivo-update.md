# HU-005-update – Proceso masivo: la selección no se pierde al tildar

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-005-update |
| Título | Proceso masivo — conservar tildes de fila y de «todos» |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente de Revisión |
| Última actualización | 2026-09-15 |
| SPEC origen | [SPEC-005-supervision-proceso-masivo](../../../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) (**sin** SPEC-update: el alcance de selección ya está en §4.3 / R-SU-04; este ítem es corrección de implementación) |
| TR relacionada(s) | [TR-005-supervision-proceso-masivo-update](../../../04-tareas/updates/100-SistemaPartes/TR-005-supervision-proceso-masivo-update.md) |
| HU base | [HU-005-supervision-proceso-masivo](../../100-SistemaPartes/HU-005-supervision-proceso-masivo.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Fuente | `00-ControlCalidad-PQ` |
| Fecha | 09/08/2026 |
| Control | #3 |
| Ítem | proceso masivo: tildo fila y se borra el tilde |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente de Revisión |

---

## Narrativa (delta)

Como supervisor  
quiero que al tildar filas (o el tilde de todos) la selección **permanezca**  
para poder confirmar el lote sin volver a marcar.

---

## Alcance

- Conservar `selectedRowKeys` / mapa de `{ id, rowVersion }` al marcar una fila, varias o «seleccionar todos».
- El tilde no debe limpiarse de inmediato por re-render, recarga de `dataSource`, aplicación de layout ni `onSelectionChanged` con lista vacía espuria.

## Fuera de alcance

- Cambiar semántica de lote atómico, filtros o atributos masivos.
- Plantillas GEN-11 (cubiertas en HU-004-update; la API es compartida).

---

## Criterios de aceptación

- [ ] **CA-CC3-05** En Proceso masivo, al tildar una o más filas los checks **siguen marcados** hasta que el usuario destilde, cambie filtros/búsqueda de listado de forma explícita, o ejecute una acción de lote que refresque el resultado.
- [ ] **CA-CC3-06** El tilde de cabecera / «seleccionar todos» (página o resultado filtrado, según SPEC-005) **no** se borra solo al hacer click; la selección resultante permanece visible.

### Gherkin

```gherkin
Scenario: Tilde de fila estable
  Given un supervisor con listado masivo de al menos 2 tareas
  When tilda la primera fila
  Then el check permanece marcado
  And la selección cuenta 1 ítem para las acciones de lote

Scenario: Tilde de todos estable
  Given el mismo listado
  When tilda el check de cabecera / seleccionar todos
  Then los checks no se limpian inmediatamente
  And la selección coincide con el contrato de select-all vigente
```

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026). Bug técnico vs SPEC-005 §4.3; sin SPEC-update. |
