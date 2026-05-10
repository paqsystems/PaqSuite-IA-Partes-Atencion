# SPEC-015 – Menú del sistema (seed versionado)

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-015 |
| Título | Seed idempotente de `pq_menus` |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-015](../../03-historias-usuario/001-Seguridad/HU-015-menu-sistema.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Fuente de verdad del árbol de menú vive en repo (JSON seed); comando/seeder idempotente sincroniza `pq_menus`; orden determinístico; constraints de integridad; integrado en migraciones/deploy; sin ABM UI de menú en esta historia.

---

## 2. Alcance

### 2.1 En alcance

- Archivo seed versionado + script que no duplica al re-ejecutar.
- Árbol completo tras deploy en entorno vacío.
- Índices/unique pertinentes (p. ej. parent + order).

### 2.2 Fuera de alcance

- Edición interactiva de menú en producción vía UI.

---

## 3. Actores y contexto

- DevOps / desarrollo; tabla `pq_menus` migrada.

---

## 4. Comportamiento funcional

- Campos según diccionario: id, text, expanded, Idparent, order, tipo, procedimiento, enabled, routeName, estructura.

---

## 5. Criterios verificables

- [ ] Idempotencia verificada (dos corridas = mismo estado).
- [ ] Integración pipeline post-migration.
- [ ] Documentación cruzada con `Historia_PQ_MENUS_seed` / diccionario.

---

## 6. Impacto técnico (visión para TR)

- `PqMenuSeeder`, JSON, migraciones columnas faltantes.

---

## 7. Riesgos y supuestos

- Merge concurrente de seeds en varias features: proceso de revisión.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-015 |

---

**Trazabilidad:** [HU-015](../../03-historias-usuario/001-Seguridad/HU-015-menu-sistema.md).
