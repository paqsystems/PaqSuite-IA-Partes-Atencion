# SPEC-010 – Administración de usuarios

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-010 |
| Título | ABM de usuarios para administrador |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-010](../../03-historias-usuario/001-Seguridad/HU-010-administracion-usuarios.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Administrador lista y gestiona `users`: alta/edición, código y email únicos, contraseña inicial, flags activo/supervisor/inhabilitado, `first_login`; baja lógica vía `inhabilitado`; permisos usuario–rol en flujo aparte (HU-013).

---

## 2. Alcance

### 2.1 En alcance

- Filtros: código, nombre, email, activo, inhabilitado.
- Crear/editar con validaciones; sin borrado físico.
- Usuario inhabilitado no puede login.

### 2.2 Fuera de alcance

- Asignación de roles en esta pantalla (HU-013).

---

## 3. Actores y contexto

- Rol administrador; tablas `users`.

---

## 4. Comportamiento funcional

- Restricciones sobre cambio de `codigo` si clave de integración (según HU).

---

## 5. Criterios verificables

- [ ] Solo admin accede.
- [ ] Unicidad código y email.
- [ ] Inhabilitado bloquea acceso.
- [ ] Validación email y política contraseña inicial.

---

## 6. Impacto técnico (visión para TR)

- API CRUD users, seeds, pantalla admin `/admin/usuarios`.

---

## 7. Riesgos y supuestos

- Sincronización con directorio corporativo futuro.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-010 |

---

**Trazabilidad:** [HU-010](../../03-historias-usuario/001-Seguridad/HU-010-administracion-usuarios.md).
