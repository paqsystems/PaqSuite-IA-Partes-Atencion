# SPEC-003-update – Vínculo de usuario en ABM asistentes y clientes

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-003-update |
| Título | Código de usuario visible y vínculo único en ABM asistentes y clientes |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-10-05 |
| SPEC base | [SPEC-003-maestros-y-catalogos](../../100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) |
| HU relacionada(s) | [HU-003-update](../../../03-historias-usuario/updates/100-SistemaPartes/HU-003-maestros-y-catalogos-update.md) |
| TR relacionada(s) | [TR-003-update](../../../04-tareas/updates/100-SistemaPartes/TR-003-maestros-y-catalogos-update.md) |
| Origen | `00-ControlCalidad-PQ` · fecha 04/10/2026 · Control de Calidad #4 · ABM Asistentes y ABM Clientes |

---

## 1. Resumen ejecutivo

- **Problema:** en el ABM de asistentes no se ve el código del usuario Framework asociado, y el selector de usuario ofrece identidades ya vinculadas o no acotadas a activas y habilitadas. La unicidad del vínculo debe rechazarse tanto entre asistentes como entre asistente y cliente.
- **Resultado esperado:** la grilla de asistentes muestra el código de usuario; el selector de asistentes y de clientes solo ofrece usuarios activos, habilitados y libres; persistir un `user_id` ya usado responde 422.

---

## 2. Alcance

### 2.1 En alcance

- Grilla de asistentes: columna **código de usuario Framework** (`users.usuario`), además del código de negocio del asistente.
- Selector de usuario en alta/edición de **asistentes** y de **clientes** (campo de acceso):
  - `users.activo = 1`
  - `users.inhabilitado = 0`
  - `users.id` no presente en `PQ_PARTES_USUARIOS.user_id` ni en `PQ_PARTES_CLIENTES.user_id`
- En edición, el usuario ya vinculado **a ese mismo registro** permanece seleccionable.
- Rechazo de servidor (422, `partes.maestros.exclusividadUserId`) si el `user_id` ya está en otro asistente o en un cliente (y al revés). La fila en edición no se considera duplicado de sí misma.
- Rechazo (422, `partes.maestros.usuarioNoVinculable`) si el usuario no está activo o está inhabilitado.

### 2.2 Fuera de alcance

- Alta de usuarios Framework desde este ABM (sigue siendo GEN).
- Mostrar el código de usuario en la grilla de clientes (el CC no lo pide).
- Cambiar unicidad de `code` de negocio ni reglas ERP.

---

## 3. Actores y contexto

Operador con permiso de menú Archivos. El cliente funcional no administra maestros (R-MA-11, sin cambio).

---

## 4. Comportamiento funcional

### 4.2 Asistentes (delta)

| Campo UI | Regla |
|----------|--------|
| Código de usuario | Columna de listado con `users.usuario` del `user_id` vinculado. Vacío solo si no hay usuario (no debería ocurrir: `user_id` es obligatorio). |
| Selector `user_id` | Universo vinculable de §4.10. |

### 4.3 Clientes (delta)

El selector de acceso (`user_id` opcional) usa el mismo universo vinculable. Al editar, se conserva el usuario de esa fila.

### 4.10 Universo de usuarios vinculables

Un usuario Framework es elegible si y solo si:

1. `activo = 1` y `inhabilitado = 0`.
2. Su `id` no está en `PQ_PARTES_USUARIOS.user_id` ni en `PQ_PARTES_CLIENTES.user_id`, salvo el `user_id` del registro que se está editando.

Contrato de lectura: `GET /api/v1/partes/catalogos/usuarios-vinculables?exceptoUserId=`.

### 4.9 Reglas numeradas (delta)

| ID | Regla |
|----|--------|
| R-MA-15 | El listado de asistentes expone el código de usuario Framework (`usuarioCodigo` = `users.usuario`). |
| R-MA-16 | El selector de usuario de asistentes y de clientes lista solo el universo §4.10. |
| R-MA-17 | Un `users.id` no puede quedar en dos asistentes, en dos clientes, ni a la vez en asistente y cliente. La edición del propio registro no dispara el rechazo. |
| R-MA-18 | No se puede vincular un usuario inactivo o inhabilitado. |

R-MA-05 permanece: la exclusividad cruzada sigue vigente y queda cubierta por R-MA-17.

---

## 5. Criterios verificables

- [ ] La grilla de asistentes muestra el código de usuario Framework asociado.
- [ ] El selector de asistentes y el de clientes omiten inactivos, inhabilitados y ya asignados.
- [ ] En edición, el usuario actual de la fila sigue en el selector.
- [ ] Alta de un segundo asistente o cliente con el mismo `user_id` responde 422 `partes.maestros.exclusividadUserId`.
- [ ] Alta con usuario inactivo o inhabilitado responde 422 `partes.maestros.usuarioNoVinculable`.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| Backend | Listado asistentes hace join a `users`; catálogo `usuarios-vinculables` con `NOT EXISTS` (sin `IN` de conjunto variable); validación en upsert y en set acceso |
| Frontend | Columna en grilla de asistentes; SelectBox consume el catálogo nuevo |
| i18n | Clave de columna en 5 locales; mensaje `usuarioNoVinculable` |

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Índice único `user_id` | Sigue como red de seguridad; el rechazo funcional es el 422 de R-MA-17 |
| Paginación del listado admin de usuarios | El selector deja de usar `GET /admin/usuarios` para este vínculo |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | CC-PQ #4 (04/10/2026): código de usuario en asistentes; universo vinculable; unicidad cruzada y en el mismo maestro. |
