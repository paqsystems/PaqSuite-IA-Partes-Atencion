# SPEC-005 – Selección de apariencia (look & feel) por usuario

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-005 |
| Título | Tema DevExtreme por usuario (menú avatar) |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-005](../../03-historias-usuario/000-Generalidades/HU-005-seleccion-apariencias.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Cada usuario autenticado elige un tema de una **lista cerrada** DevExtreme desde el **menú del avatar**; el valor se persiste en el modelo de usuario (p. ej. `users.theme` / `dx_theme`), no hay tema único por instalación ni por empresa (MONO).

---

## 2. Alcance

### 2.1 En alcance

- Ítem en menú usuario → modal/popover/submenú con lista de temas.
- Lectura en login/`me`; escritura solo para el **subject** del token (o política admin futura explícita).
- ThemeLoader/ThemeProvider; NULL/vacío → tema por defecto de producto; validación 422 si valor inválido.
- Reducir flash de estilos cuando sea posible.

### 2.2 Fuera de alcance

- ThemeBuilder o temas arbitrarios; tema global que ignore preferencia; tema por `PQ_Empresa`.

---

## 3. Actores y contexto

- Usuario autenticado cualquiera (salvo restricción de producto documentada).
- DevExtreme React en shell post-login.

---

## 4. Comportamiento funcional

1. Lista cerrada con nombres legibles.
2. Guardado inmediato en perfil y sesión actual; próximo acceso carga tema del usuario.
3. Seguridad: escritura acotada al usuario autenticado.

---

## 5. Criterios verificables

- [ ] Selector solo desde menú avatar.
- [ ] Persistencia por usuario en `users` (columna acordada).
- [ ] Contrato API coherente con HU-005 AC5–AC6.
- [ ] Fallback ante tema inválido o fallo de carga CSS.

---

## 6. Impacto técnico (visión para TR)

- Migración `users.theme` (nullable varchar).
- Endpoint PATCH perfil / preferencias.
- Frontend: carga temas DevExtreme, integración header.

---

## 7. Riesgos y supuestos

- Alineación con lista de temas realmente empaquetados en el build.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-005 (preferencia por usuario, no instalación) |

---

**Trazabilidad:** [HU-005](../../03-historias-usuario/000-Generalidades/HU-005-seleccion-apariencias.md).
