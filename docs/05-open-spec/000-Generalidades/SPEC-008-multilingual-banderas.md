# SPEC-008 – Multilingual con idiomas y banderas

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-008 |
| Título | Idiomas extendidos con indicadores visuales (banderas) |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado (Coherente con CC documentado en HU-008) |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-008](../../03-historias-usuario/000-Generalidades/HU-008-multilingual-idiomas-banderas.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Extiende SPEC-004 / HU-004 con conjunto mínimo de idiomas (es, en, pt, fr, it), selector con **bandera + nombre en idioma nativo**, misma persistencia `users.locale` y recursos i18n completos por idioma.

---

## 2. Alcance

### 2.1 En alcance

- Idiomas y emojis/iconos según HU-008 (AR, GB, BR, FR, IT).
- Cambio inmediato sin reload; formatos fecha/número al locale.
- Login + header; fallback español si navegador no soportado.
- Archivos `es.json`, `en.json`, `pt.json`, `fr.json`, `it.json`.

### 2.2 Fuera de alcance

- Traducción de datos maestros.

---

## 3. Actores y contexto

- Misma base que HU-004; dependencia explícita en HU-008.

---

## 4. Comportamiento funcional

- Solo mostrar idiomas con traducciones completas.
- API preferencias igual que HU-004.

---

## 5. Criterios verificables

- [ ] Componente selector con bandera + etiqueta nativa.
- [ ] Cinco locales iniciales y paridad de claves.
- [ ] Persistencia y flujo invitado igual HU-004.

---

## 6. Impacto técnico (visión para TR)

- `LanguageSelector` u homólogo; ampliación `i18n` config; QA de cobertura de strings.

---

## 7. Riesgos y supuestos

- Tamaño bundle locales; lazy loading opcional.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-008 |

---

**Trazabilidad:** [HU-008](../../03-historias-usuario/000-Generalidades/HU-008-multilingual-idiomas-banderas.md).
