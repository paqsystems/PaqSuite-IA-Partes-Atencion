# SPEC-004 – Selección de idioma de la aplicación

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-004 |
| Título | Selección de idioma (i18n) por usuario |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-004](../../03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

El usuario elige idioma de interfaz desde login y desde el header (control **independiente** del menú avatar), con persistencia en `users.locale`, fallback por navegador y actualización inmediata vía i18n (`t()`).

---

## 2. Alcance

### 2.1 En alcance

- Selector en pantalla de login y en layout post-login (header dedicado).
- Persistencia `users.locale` (nullable → usar `navigator.language`, fallback idioma por defecto si no soportado).
- Invitado: localStorage hasta login; tras auth, enviar y guardar en backend.
- Activación i18n completa (claves en recursos); formatos fecha/número según locale.

### 2.2 Fuera de alcance

- Traducción automática de datos de negocio (nombres propios, descripciones maestras).

---

## 3. Actores y contexto

- Usuario anónimo (login) y autenticado.
- Idiomas ofrecidos = conjunto con archivos de traducción existentes.

---

## 4. Comportamiento funcional

1. Cambio de idioma aplica sin full page reload.
2. Solo valores de idiomas soportados.
3. API lectura/escritura de `locale` (login/me/PATCH perfil).

---

## 5. Criterios verificables

- [ ] Selector login + header; **no** dentro del menú usuario.
- [ ] Persistencia backend autenticados; localStorage + sync en login para invitados.
- [ ] Fallback navegador → default (ej. es).
- [ ] UI usa exclusivamente claves i18n en textos de producto.

---

## 6. Impacto técnico (visión para TR)

- `users.locale`, contrato API, `react-i18next` (o equivalente), archivos `locales/*.json`.

---

## 7. Riesgos y supuestos

- Paridad de claves entre idiomas; HU-008 extiende idiomas/banderas.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-004 |

---

**Trazabilidad:** [HU-004](../../03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md).
