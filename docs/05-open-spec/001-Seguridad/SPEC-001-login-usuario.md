# SPEC-001 – Login de usuario

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-001 |
| Título | Autenticación con código y contraseña (MONO) |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-001](../../03-historias-usuario/001-Seguridad/HU-001-login-usuario.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

El usuario se autentica contra `users` con código y contraseña; se valida estado activo, hash, y existencia de al menos una fila en `Pq_Permiso` (usuario–rol, sin empresa). Tras éxito se emite token (p. ej. Sanctum) con datos mínimos para permisos y se navega al layout principal **sin** selector de empresa.

---

## 2. Alcance

### 2.1 En alcance

- Validación campos no vacíos; usuario existe; `activo`; no `inhabilitado`; password verificado.
- Bloqueo si no hay asignaciones en `Pq_Permiso`.
- Token persistido en cliente (localStorage/sessionStorage según política); claims: `user_id`, `user_code`, etc.
- Mensaje genérico ante fallo (sin filtrar existencia de usuario).

### 2.2 Fuera de alcance

- Login social (`users_identities`); multi-empresa.

---

## 3. Actores y contexto

- Empleado/supervisor/admin.
- Tablas `users`, `Pq_Permiso`; endpoint POST login.

---

## 4. Comportamiento funcional

- Reglas de negocio y CA alineados a HU-001.
- Redirección a shell post-login MONO.

---

## 5. Criterios verificables

- [ ] Flujo feliz con token y redirección.
- [ ] Usuario sin `Pq_Permiso`: mensaje y sin acceso.
- [ ] Usuario inactivo/inhabilitado/credenciales malas: mismo tipo de error genérico.
- [ ] No hay dependencia de empresa en token (MONO).

---

## 6. Impacto técnico (visión para TR)

- Laravel Sanctum (u homólogo), políticas password, seeds mínimos de permiso, frontend login form.

---

## 7. Riesgos y supuestos

- Rate limiting / bloqueo brute force (otra historia si aplica).

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-001 Seguridad |

---

**Trazabilidad:** [HU-001](../../03-historias-usuario/001-Seguridad/HU-001-login-usuario.md).
