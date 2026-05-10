# SPEC-018 – Seed `pq_menus`: `routeName` y `enabled`

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-018 |
| Título | Datos de navegación en seed para sidebar dinámico |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Estado | Especificado |
| Última actualización | 2026-05-09 |
| HU relacionada(s) | [HU-018](../../03-historias-usuario/001-Seguridad/HU-018-seed-menu-routeName-enabled.md) |
| TR relacionada(s) | — |

---

## 1. Resumen ejecutivo

El archivo seed (`PQ_MENUS.seed.v2.json` u homólogo) define `enabled = 1` para opciones MONO relevantes (Usuarios, Roles, Permisos, Atributos) y `routeName` alineado al router React; ítems sección pueden tener `routeName` null; seeder persiste ambos campos; migración asegura columna nullable `routeName`.

---

## 2. Alcance

### 2.1 En alcance

- Rutas ejemplo `/admin/usuarios`, `/admin/roles`, `/admin/permisos`, Inicio `/`, Perfil `/perfil`.
- `PqMenuSeeder` upsert incluye `routeName`.
- Documentación convención en `docs/backend/seed/PQ_MENUS/README.md`.

### 2.2 Fuera de alcance

- Rutas de módulos de negocio no listados en HU-018 (extensible mismo patrón).

---

## 3. Actores y contexto

- Administrador/DevOps; depende SPEC-015.

---

## 4. Comportamiento funcional

- `enabled = 0` excluye del menú usuario (HU-016).
- `routeName` debe coincidir con paths del frontend.

---

## 5. Criterios verificables

- [ ] JSON y BD coherentes tras seed.
- [ ] README actualizado para nuevas opciones.
- [ ] Sidebar navega sin ajustes manuales en BD.

---

## 6. Impacto técnico (visión para TR)

- Edición JSON, migración SQL, tests de seeder.

---

## 7. Riesgos y supuestos

- Cambios de path en router requieren sync con seed.

---

## 8. Historial (opcional)

| Fecha | Cambio |
|-------|--------|
| 2026-05-09 | Versión inicial desde HU-018 |

---

**Trazabilidad:** [HU-018](../../03-historias-usuario/001-Seguridad/HU-018-seed-menu-routeName-enabled.md).
