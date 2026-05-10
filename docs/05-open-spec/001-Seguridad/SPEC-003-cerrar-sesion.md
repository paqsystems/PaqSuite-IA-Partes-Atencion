# SPEC-003 – Cerrar sesión

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-003 |
| Título | Logout desde menú usuario |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-003](../../03-historias-usuario/001-Seguridad/HU-003-logout.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

El usuario cierra sesión desde el menú bajo el avatar; el backend invalida el token si aplica, el cliente limpia almacenamiento y estado en memoria, y redirige a login; peticiones con token previo reciben 401.

---

## 2. Alcance

### 2.1 En alcance

- Ítem “Cerrar sesión” visible en dropdown/menú usuario.
- Acción inmediata sin confirmación.
- Limpieza localStorage/sessionStorage + estado React/query si aplica.

### 2.2 Fuera de alcance

- Timeout de sesión inactiva (otra HU).

---

## 3. Actores y contexto

- Usuario autenticado; depende SPEC-001 login y shell M01.

---

## 4. Comportamiento funcional

- MONO: sin contexto empresa que limpiar.
- Revocación server-side según stack (Sanctum delete token).

---

## 5. Criterios verificables

- [ ] Post-logout: 401 con token antiguo.
- [ ] Redirección a pantalla login.
- [ ] UX coherente con diseño PaqSystems / mockup M01.

---

## 6. Impacto técnico (visión para TR)

- Endpoint logout, limpieza cliente, rutas protegidas.

---

## 7. Riesgos y supuestos

- Tokens stateless sin blacklist: documentar limitación o usar lista de revocados.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-003 |

---

**Trazabilidad:** [HU-003](../../03-historias-usuario/001-Seguridad/HU-003-logout.md).
