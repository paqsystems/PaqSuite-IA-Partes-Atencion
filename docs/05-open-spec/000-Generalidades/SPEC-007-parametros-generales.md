# SPEC-007 – Parámetros generales del sistema por módulo

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-007 |
| Título | Mantenimiento de `PQ_PARAMETROS_GRAL` filtrado por módulo |
| Épica / carpeta | 000 – Generalidades |
| Estado | Especificado (Coherente con CC documentado en HU-007) |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-007](../../03-historias-usuario/000-Generalidades/HU-007-Parametros-generales.md) |
| TR relacionada(s) | — (vincular updates TR-007 si aplica) |

---

## 1. Resumen ejecutivo

Proceso único de edición de valores en `PQ_PARAMETROS_GRAL` invocado desde ítems de menú cuyo `procedimiento` coincide con `Programa`; listado homogéneo en texto y edición por fila según `tipo_valor`, sin alta/baja de filas (solo seeds). **MONO:** una sola BD, sin `X-Company-Id`.

---

## 2. Alcance

### 2.1 En alcance

- Listado: Clave, caption/CAPTION, valor como texto uniforme; booleanos como Sí/No localizado; NULL bool = negativo; tooltip TOOLTIP.
- Edición solo vía botón “Editar” por fila: modal o pantalla con control por tipo (bool, int, decimal, string, text, datetime).
- Filtro estricto: registros cuyo `Programa` coincide con el menú desde el que se abre (comparación case-insensitive recomendada API/SQL).
- Seeds iniciales por módulo; sin multi-tenant.

### 2.2 Fuera de alcance

- CRUD de definición de parámetros en runtime (claves solo vía seed).

---

## 3. Actores y contexto

- Usuario con permiso de configuración del módulo.
- Tablas `PQ_PARAMETROS_GRAL`, `PQ_MENUS`.

---

## 4. Comportamiento funcional

- Reglas UI: `.cursor/rules/32-parametros-generales-ui-listado-y-edicion-por-tipo.md`, `27`, `28`.
- Textos `parametrosGral.*` en i18n.

---

## 5. Criterios verificables

- [ ] No se crean/eliminan filas desde UI; solo `Valor_*` según tipo.
- [ ] Filtro por módulo correcto; seeds alineados a literales de menú.
- [ ] Listado DevExtreme alineado a estándar de app (ver updates HU-007 en curso).
- [ ] MONO: sin cabecera de empresa.

---

## 6. Impacto técnico (visión para TR)

- API listado/patch por clave+módulo.
- Pantalla reusable por módulos con distinto `procedimiento`.

---

## 7. Riesgos y supuestos

- Divergencia `Programa` vs `Procedimiento` si no se normaliza casing.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-007 |

---

**Trazabilidad:** [HU-007](../../03-historias-usuario/000-Generalidades/HU-007-Parametros-generales.md).
