# TR-003-update – Vínculo de usuario en ABM asistentes y clientes

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-003-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-003-maestros-y-catalogos-update.md) |
| **SPEC relacionada** | [SPEC-003-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-003-maestros-y-catalogos-update.md) |
| **TR base** | [TR-003](../../100-SistemaPartes/TR-003-maestros-y-catalogos.md) |
| **Épica** | 100 — Sistema Partes |
| **Prioridad** | MUST-HAVE |
| **Estado** | Pendiente de Revisión |
| **Última actualización** | 2026-10-05 |

**Origen:** `00-ControlCalidad-PQ` · 04/10/2026 · CC #4 · ABM Asistentes y ABM Clientes  
**Envelope:** respuesta de error con `respuesta` = clave i18n (`exclusividadUserId` / `usuarioNoVinculable`)

---

## 1) HU refinada (resumen)

### In scope
- `pq_sp_partes_usuarios_list` hace `LEFT JOIN users` y devuelve `usuario_codigo` (`users.usuario`).
- Nuevo contrato `pq_sp_partes_catalogo_usuarios_vinculables` (`exceptoUserId` opcional).
- Upsert de asistente, upsert de cliente y set acceso validan R-MA-17 y R-MA-18 antes de persistir.
- UI: columna en asistentes; SelectBox de ambos ABM contra el catálogo. `data-testid` `partesMaestrosAsistentesUsuario` y `partesMaestrosClientesUsuario`.

### Out of scope
- Columna de usuario en grilla de clientes. DDL nuevo. ABM de `users`.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|----------------|
| AC-U01 | `GET /api/v1/partes/asistentes` incluye `usuarioCodigo` igual a `users.usuario`. La grilla lo muestra. |
| AC-U02 | `GET /api/v1/partes/catalogos/usuarios-vinculables` omite inactivos, inhabilitados y asignados. Con `exceptoUserId` de la fila en edición, ese id vuelve a aparecer si sigue activo y habilitado. |
| AC-U03 | Segundo asistente o cliente con el mismo `userId` → 422 `partes.maestros.exclusividadUserId`. Editar la misma fila conservando el `userId` → 200. |
| AC-U04 | `userId` inactivo o `inhabilitado = 1` → 422 `partes.maestros.usuarioNoVinculable`. |

---

## 3) Reglas

| ID | Implementación |
|----|----------------|
| RN-TR-U01 | Universo vinculable con `NOT EXISTS` sobre `PQ_PARTES_USUARIOS` y `PQ_PARTES_CLIENTES`. Prohibido `whereIn` de ids asignados. |
| RN-TR-U02 | La comprobación de unicidad ignora el `id` del registro en edición del lado que se graba. |
| RN-TR-U03 | Clave de rechazo de duplicado: `partes.maestros.exclusividadUserId` (la que ya usa el test de exclusividad cruzada). |

---

## 4) API

| Método | Ruta | Notas |
|--------|------|--------|
| GET | `/api/v1/partes/asistentes` | Ítem con `usuarioCodigo` |
| GET | `/api/v1/partes/catalogos/usuarios-vinculables` | Query `exceptoUserId` opcional. Ítems: `id`, `codigo` (`users.usuario`), `nombre` (`users.name`) |
| POST/PUT | `/api/v1/partes/asistentes` y `/clientes` | Validación R-MA-17/18 |
| POST | `/api/v1/partes/clientes/{id}/acceso` | Misma validación, ignorando el cliente `{id}` |

---

## 5) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Join en listado de asistentes + catálogo vinculable + asserts en upsert/set acceso | AC-U01…U04 en feature test |
| T2 | Frontend | Columna, SelectBox, i18n 5 locales, testids, mensaje `usuarioNoVinculable` | AC-U01/U02 en UI |
| T3 | Tests | Feature API de duplicado, catálogo y usuario no vinculable | Suite verde del archivo tocado |

**Orden:** T1 → T2 → T3.

---

## 6) Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #4. |
