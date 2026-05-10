# SPEC-014 – Administración de atributos de rol

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-014 |
| Título | Permisos granulares por rol y opción de menú |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-014](../../03-historias-usuario/001-Seguridad/HU-014-administracion-atributos-rol.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Para roles sin `AccesoTotal`, el administrador configura en `PQ_RolAtributo` permisos Alta/Baja/Modi/Repo por combinación rol + opción `pq_menus`; roles con acceso total no requieren filas de atributo.

---

## 2. Alcance

### 2.1 En alcance

- Listar/editar atributos por rol.
- Unicidad `(IDRol, IDOpcionMenu, IDAtributo)` según modelo.
- Opciones de menú desde `pq_menus`.

### 2.2 Fuera de alcance

- Definición del árbol de menú en runtime (seed HU-015).

---

## 3. Actores y contexto

- Administrador; tablas `Pq_Rol`, `pq_menus`, `PQ_RolAtributo`.

---

## 4. Comportamiento funcional

- Coherencia con API menú (HU-016): al menos un permiso TRUE para incluir ítem.

---

## 5. Criterios verificables

- [ ] Solo admin.
- [ ] CRUD atributos sin duplicar clave lógica.
- [ ] Integración con pantalla existente patrón `RolAtributosPage`.

---

## 6. Impacto técnico (visión para TR)

- `RolAtributoController`, API, UI jerárquica parentId/order.

---

## 7. Riesgos y supuestos

- `IDAtributo` catálogo fijo vs variable según DDL real.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-014 |

---

**Trazabilidad:** [HU-014](../../03-historias-usuario/001-Seguridad/HU-014-administracion-atributos-rol.md).
