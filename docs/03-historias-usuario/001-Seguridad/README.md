# Épica 001 – Seguridad y Acceso

## Open-Spec de contexto (épica)

Antecedente lógico “como si hubiese existido antes de las HU”: [SPEC-CTX-001 – Seguridad y acceso](../../05-open-spec/001-Seguridad/SPEC-CTX-001-seguridad-y-acceso.md).

## Objetivo

Definir todas las historias de usuario relacionadas con:

1. **Acceso al sistema:** Login y logout (**MONO:** sin selección de empresa).
2. **Mantenimiento de tablas:** Administración de usuarios, roles, permisos (asignación usuario–rol) y menú.

**Instalación de este repo — MONO:** no hay **`PQ_Empresa`**, **`X-Company-Id`** ni flujos por tenant. La documentación ERP que mencione varias bases por compañía es **referencia**, no alcance.

## Normas transversales (rendimiento y obtención de datos)

Ver el apartado **Rendimiento y obtención de datos** en [README padre](../README.md).

## Tablas involucradas (esquema único)

| Tabla | Propósito |
|-------|-----------|
| `users` | Usuarios del sistema (autenticación) |
| `Pq_Rol` | Roles disponibles |
| `Pq_Permiso` | Asignaciones **usuario → rol** (sin dimensión empresa en MONO) |
| `PQ_RolAtributo` | Permisos por rol y opción de menú |
| `pq_menus` | Opciones de menú del sistema |

**No aplica en MONO:** `PQ_Empresa` (multi-empresa).

**Nota:** La tabla `users_identities` no se aplica por el momento (ver `docs/modelo-datos/md-diccionario/md-diccionario.md`).

## Historias por área

### Acceso (login + logout)

| HU | Título | Clasificación |
|----|--------|---------------|
| [HU-001](HU-001-login-usuario.md) | Login de usuario | MUST-HAVE |
| [HU-002](../../000-Generalidades/HU-002-cambio-empresa-activa.md) | Cambio de empresa activa — **no aplica (MONO)** | — |
| [HU-003](HU-003-logout.md) | Cerrar sesión | MUST-HAVE |
| [HU-004](HU-004-cambio-contraseña.md) | Cambio de contraseña | SHOULD-HAVE |
| [HU-005](HU-005-recuperacion-contraseña.md) | Recuperación de contraseña | SHOULD-HAVE |

### Mantenimiento de tablas

| HU | Título | Clasificación |
|----|--------|---------------|
| [HU-010](HU-010-administracion-usuarios.md) | Administración de usuarios | MUST-HAVE |
| [HU-012](HU-012-administracion-roles.md) | Administración de roles | MUST-HAVE |
| [HU-013](HU-013-administracion-permisos.md) | Administración de permisos (asignaciones usuario–rol) | MUST-HAVE |
| [HU-014](HU-014-administracion-atributos-rol.md) | Administración de atributos de rol | SHOULD-HAVE |
| [HU-015](HU-015-menu-sistema.md) | Menú del sistema (seed versionado) | MUST-HAVE |
| [HU-016](HU-016-api-menu-usuario.md) | API de menú del usuario | MUST-HAVE |
| [HU-017](HU-017-sidebar-dinamico-pqmenus.md) | Sidebar dinámico desde pq_menus | MUST-HAVE |
| [HU-018](HU-018-seed-menu-routeName-enabled.md) | Seed pq_menus: routeName y enabled para sidebar | SHOULD-HAVE |

**No aplica (MONO):** administración de empresas (no hay HU equivalente en este alcance).

## Dependencias

```
HU-001 (Login) → HU-003 (Logout)
HU-001 → HU-010, HU-012, HU-013 (mantenimiento, requiere admin)
HU-015, HU-014 → HU-016 (API menú) → HU-017 (Sidebar dinámico)
HU-015 → HU-018 (Seed routeName/enabled)
```

## Referencias

- `.cursor/rules/34-obtencion-datos-performance.md` – Rendimiento y obtención de datos
- `docs/06-operacion/instructivo-optimizar-velocidad.md` – Medición
- `.cursor/Docs/eficiencia-tecnica.md` – Patrones del repo
- `docs/01-arquitectura/README.md` / `docs/01-arquitectura/06-mapa-visual-seguridad-roles-permisos-menu.md` – Seguridad **MONO**
- `docs/01-arquitectura/01-arquitectura-proyecto.md` – Modelo de capas (secciones multi-DB como referencia)
- `docs/00-contexto/00-contexto-global-erp.md` – Contexto ERP
- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema datos
- `docs/ui/mockups/mockup-spec-mainlayout.md` – Shell (sin CompanySwitcher en MONO salvo decisión explícita)
- `docs/05-open-spec/README.md` – Índice Open-Spec (especificaciones alineadas a estas HUs)
