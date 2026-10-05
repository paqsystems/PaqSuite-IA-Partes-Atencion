# HU-003-update – Vínculo de usuario en ABM asistentes y clientes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-003-update |
| Título | Código de usuario visible y vínculo único en asistentes y clientes |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente de Revisión |
| Última actualización | 2026-10-05 |
| SPEC origen | [SPEC-003-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-003-maestros-y-catalogos-update.md) |
| HU base | [HU-003-maestros-y-catalogos](../../100-SistemaPartes/HU-003-maestros-y-catalogos.md) |
| TR relacionada(s) | [TR-003-update](../../../04-tareas/updates/100-SistemaPartes/TR-003-maestros-y-catalogos-update.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 04/10/2026 |
| Ítem | Control de Calidad #4 · ABM Asistentes y ABM Clientes |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente de Revisión |

---

## Narrativa

Como operador de maestros Partes  
quiero ver el código del usuario asociado al asistente y elegir solo usuarios activos, habilitados y aún no asignados  
para no duplicar el vínculo ni en asistentes ni en clientes.

---

## Alcance incluido

- Columna de código de usuario Framework en el listado de asistentes.
- Selector de usuario (asistentes y clientes) limitado al universo vinculable del SPEC-update (activos, no inhabilitados, no asignados; en edición se conserva el de la fila).
- Rechazo al guardar si el usuario ya está en otro asistente o en un cliente, o si no es vinculable.

## Fuera de alcance

- Columna de código de usuario en la grilla de clientes.
- Alta de usuarios Framework.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-MA-15 | Listado de asistentes muestra `usuarioCodigo`. |
| R-MA-16 | Selector = universo vinculable. |
| R-MA-17 | Un `users.id` solo en un asistente o en un cliente, nunca en ambos ni repetido. |
| R-MA-18 | Usuario inactivo o inhabilitado no se vincula. |

---

## Criterios de aceptación

- [ ] **CA-U01** La grilla de asistentes muestra el código de usuario Framework.
- [ ] **CA-U02** El selector de asistentes y el de clientes no ofrecen inactivos, inhabilitados ni ya asignados; en edición sí ofrece el usuario de esa fila.
- [ ] **CA-U03** Persistir un `user_id` ya usado en otro asistente o en un cliente responde 422 `partes.maestros.exclusividadUserId`.
- [ ] **CA-U04** Persistir un usuario inactivo o inhabilitado responde 422 `partes.maestros.usuarioNoVinculable`.

---

## Escenarios Gherkin

```gherkin
Scenario: Código de usuario en el listado de asistentes
  Given un asistente vinculado al usuario Framework "asst2"
  When consulto el listado de asistentes
  Then la fila muestra el código de usuario "asst2"

Scenario: No reasignar un usuario ya vinculado
  Given el usuario "asst2" ya es asistente
  When intento crear otro asistente o un cliente con ese mismo usuario
  Then la operación se rechaza con exclusividad de user id

Scenario: Selector sin asignados
  Given "asst2" ya está vinculado y "libre1" está activo, habilitado y libre
  When abro el alta de asistente
  Then el selector incluye "libre1" y no incluye "asst2"
```

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #4 (04/10/2026). |
