# SPEC-012 – Administración de roles

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-012 |
| Título | ABM de roles (`Pq_Rol`) |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-012](../../03-historias-usuario/001-Seguridad/HU-012-administracion-roles.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

Administrador define roles reutilizables: nombre, descripción, `AccesoTotal` (bypass de atributos granulares); alta/edición/baja condicionada a ausencia de dependencias; atributos por menú en HU-014.

---

## 2. Alcance

### 2.1 En alcance

- Listado con NombreRol, DescripcionRol, AccesoTotal.
- CRUD sobre `Pq_Rol`; política de delete si hay `Pq_Permiso`/`PQ_RolAtributo` (definir cascade o bloqueo).

### 2.2 Fuera de alcance

- Dimensión empresa (MONO).

---

## 3. Actores y contexto

- Administrador; dependencias HU-001.

---

## 4. Comportamiento funcional

- `AccesoTotal = true` implica menú completo en lógica HU-016 sin filas en `PQ_RolAtributo`.

---

## 5. Criterios verificables

- [ ] Solo administrador.
- [ ] No eliminar rol en uso sin política clara.
- [ ] Datos consistentes con seed de menú y atributos.

---

## 6. Impacto técnico (visión para TR)

- API roles, pantalla `/admin/roles`.

---

## 7. Riesgos y supuestos

- Renombrar rol en uso: impacto documentación y auditoría.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-012 |

---

**Trazabilidad:** [HU-012](../../03-historias-usuario/001-Seguridad/HU-012-administracion-roles.md).
