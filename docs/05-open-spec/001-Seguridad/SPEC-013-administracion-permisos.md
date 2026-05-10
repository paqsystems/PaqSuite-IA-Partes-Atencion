# SPEC-013 – Administración de permisos (asignaciones usuario–rol)

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-013 |
| Título | Asignación usuario ↔ rol en MONO |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-013](../../03-historias-usuario/001-Seguridad/HU-013-administracion-permisos.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Administrador gestiona filas de `Pq_Permiso` vinculando **usuario + rol** sin dimensión empresa; unicidad lógica `(IDUsuario, IDRol)`; sin asignaciones el usuario no opera tras login (coherente con HU-001).

---

## 2. Alcance

### 2.1 En alcance

- Listado con filtros por usuario y rol.
- Alta/edición/baja de asignaciones.
- Validación FK usuario y rol existentes.

### 2.2 Fuera de alcance

- IDEmpresa en UI/lógica (MONO; columna legado puede existir con valor constante).

---

## 3. Actores y contexto

- Administrador; tablas `users`, `Pq_Rol`, `Pq_Permiso`.

---

## 4. Comportamiento funcional

- Varios roles por usuario si producto lo permite; permisos efectivos = unión de atributos por rol (HU-014/016).

---

## 5. Criterios verificables

- [ ] Unicidad par usuario–rol.
- [ ] Solo administrador.
- [ ] Consistencia con login (mínimo una asignación para entrar).

---

## 6. Impacto técnico (visión para TR)

- API permisos, pantalla `/admin/permisos`, índice único compuesto.

---

## 7. Riesgos y supuestos

- Quitar último rol de un usuario activo: política de negocio.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-013 |

---

**Trazabilidad:** [HU-013](../../03-historias-usuario/001-Seguridad/HU-013-administracion-permisos.md).
