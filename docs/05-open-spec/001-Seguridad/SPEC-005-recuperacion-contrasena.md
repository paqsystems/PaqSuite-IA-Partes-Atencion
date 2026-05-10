# SPEC-005 – Recuperación de contraseña

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-005 |
| Título | Flujo “olvidé mi contraseña” por email |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado (coherente con CC HU-005 updates) |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-005](../../03-historias-usuario/001-Seguridad/HU-005-recuperacion-contraseña.md) |
| TR relacionada(s) | — (vincular updates) |

---

## 1. Resumen ejecutivo

Desde login el usuario solicita reset por email; respuesta siempre genérica; token de un solo uso con expiración (ej. 60 min); formulario nueva contraseña invalida token y actualiza hash; errores claros si token inválido/expirado.

---

## 2. Alcance

### 2.1 En alcance

- Enlace “¿Olvidaste tu contraseña?”; form email.
- Envío mail con enlace (o simulación dev).
- Página reset: nueva + confirmación; redirect login con éxito.

### 2.2 Fuera de alcance

- MFA; canales distintos de email.

---

## 3. Actores y contexto

- Usuario no autenticado; `MAIL_*` configurado; tabla users con email único.

---

## 4. Comportamiento funcional

- No revelar si email existe.
- Laravel password broker pattern / equivalente.

---

## 5. Criterios verificables

- [ ] Mensaje genérico post-solicitud.
- [ ] Token expira e invalida tras uso.
- [ ] Config mail documentada para entornos.

---

## 6. Impacto técnico (visión para TR)

- Tabla `password_reset_tokens` (Laravel default), notificaciones, rutas web/API.

---

## 7. Riesgos y supuestos

- Entrega a spam; logging sin PII excesivo.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-005 Seguridad |

---

**Trazabilidad:** [HU-005](../../03-historias-usuario/001-Seguridad/HU-005-recuperacion-contraseña.md).
