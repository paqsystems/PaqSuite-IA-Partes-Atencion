# SPEC-004 – Cambio de contraseña

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-004 |
| Título | Cambio de contraseña autenticado y primer login |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-004](../../03-historias-usuario/001-Seguridad/HU-004-cambio-contraseña.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Desde menú usuario el usuario abre modal con actual, nueva y confirmación; validaciones de política y coincidencia; si `first_login`, debe cambiar antes de usar el sistema; hash almacenado con bcrypt (u homólogo).

---

## 2. Alcance

### 2.1 En alcance

- Flujo solo para el propio usuario autenticado.
- Actualización `password_hash` y `first_login = false` tras éxito.
- Mensajes claros ante password actual incorrecta.

### 2.2 Fuera de alcance

- Reset por email (HU-005 / SPEC-005 Seguridad).

---

## 3. Actores y contexto

- Usuario autenticado; mockup M05 Change password.

---

## 4. Comportamiento funcional

- Política longitud/complejidad según configuración producto.

---

## 5. Criterios verificables

- [ ] Modal desde menú avatar.
- [ ] Validación actual + política nueva + confirmación.
- [ ] Forzar cambio si `first_login`.
- [ ] Hash seguro en reposo.

---

## 6. Impacto técnico (visión para TR)

- Endpoint PATCH password, reglas validación Laravel, frontend form.

---

## 7. Riesgos y supuestos

- Re-login después de cambio según política de tokens.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-004 Seguridad |

---

**Trazabilidad:** [HU-004](../../03-historias-usuario/001-Seguridad/HU-004-cambio-contraseña.md).
