# SPEC-016 – API de menú del usuario

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-016 |
| Título | Endpoint menú filtrado por permisos (MONO) |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-016](../../03-historias-usuario/001-Seguridad/HU-016-api-menu-usuario.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

`GET /api/v1/user/menu` (o ruta equivalente) autenticado devuelve subárbol de `pq_menus` al que el usuario tiene acceso según `Pq_Permiso`, `Pq_Rol`, `PQ_RolAtributo`, con unión de roles y respeto a `enabled` y texto no vacío.

---

## 2. Alcance

### 2.1 En alcance

- Resolución usuario desde token.
- `AccesoTotal`: todas las opciones con `enabled = 1`.
- Sin acceso total: solo ítems con algún permiso TRUE en `PQ_RolAtributo` para el rol.
- Multirrol: **unión** de opciones autorizadas.
- Respuesta jerárquica: id, text, parentId, orden, routeName/procedimiento.
- Orden por parentId y orden.
- MONO: sin `X-Company-Id`.

### 2.2 Fuera de alcance

- Cache edge CDN; i18n server-side de `text` (cliente puede mapear claves).

---

## 3. Actores y contexto

- Usuario autenticado con al menos un rol (puede retornar vacío o mínimo según política).

---

## 4. Comportamiento funcional

- Admitir PascalCase/snake_case en tablas según implementación.
- Si sin asignaciones o sin ítems: lista vacía o solo públicos (documentar decisión producto).

---

## 5. Criterios verificables

- [ ] Contrato JSON estable para Sidebar (HU-017).
- [ ] Casos multirrol y AccesoTotal cubiertos por tests.
- [ ] Solo `enabled = 1` y `text` no vacío.

---

## 6. Impacto técnico (visión para TR)

- Service de resolución menú, queries optimizadas, tests PHP/Pest.

---

## 7. Riesgos y supuestos

- Profundidad de árbol y N+1; considerar eager load.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-016 |

---

**Trazabilidad:** [HU-016](../../03-historias-usuario/001-Seguridad/HU-016-api-menu-usuario.md).
