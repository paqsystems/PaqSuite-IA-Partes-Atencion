# SPEC-CTX-001 – Épica Seguridad y acceso (antecedente a las HU)

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-CTX-001 |
| Título | Identidad, sesión, autorización y menú navegable (MONO) |
| Épica / carpeta | 001 – Seguridad y Acceso |
| Rol en la metodología | **Contexto / alcance de épica** que en un flujo A→B→C precedería a la redacción de las HU (paso **B**). |
| Estado | Reconstrucción documental (ver §8) |
| Última actualización | 2026-05-09 |

---

## 1. Problema y resultado buscado

El sistema debe **saber quién entra**, **mantener la sesión**, **revocar el acceso** y **limitar qué ve y qué hace** cada persona, con un **menú** que refleje esos derechos. En paralelo, los administradores necesitan **gobernar usuarios, roles y permisos** sin ambigüedad entre entornos.

**Resultado:** en MONO, autenticación contra `users`, asignaciones **usuario–rol** sin empresa, atributos **rol–menú** para granularidad, **árbol de menú versionado** en repo y **API + UI** que muestran solo lo autorizado.

---

## 2. Contexto de instalación (decisión explícita)

- **MONO:** no hay `PQ_Empresa` operativo, ni `X-Company-Id`, ni lógica de tenant en estos flujos.
- Login **sin** selección de sociedad; mensajes y redirecciones coherentes con shell único.
- Tablas pivot del modelo: `users`, `Pq_Rol`, `Pq_Permiso`, `PQ_RolAtributo`, `pq_menus` (nombres físicos pueden variar en casing; la semántica es la descrita en el README de la épica).

---

## 3. Capacidades de épica (cómo se descompondrían las HU)

| Capacidad (origen lógico) | Qué debe quedar garantizado | HUs derivadas | SPEC detalle |
|---------------------------|----------------------------|---------------|--------------|
| **S1 – Ingreso al sistema** | Credenciales válidas, usuario activo, contraseña verificada, **token** y bloqueo sin rol asignado | [HU-001](../../03-historias-usuario/001-Seguridad/HU-001-login-usuario.md) | [SPEC-001](SPEC-001-login-usuario.md) |
| **S2 – Reserva MULTI (no MONO)** | Cambio de empresa en un ERP multi-sociedad documentado en generalidades; no entrega MONO | [HU-002](../../03-historias-usuario/000-Generalidades/HU-002-cambio-empresa-activa.md) (épica 000) | [SPEC-002](../000-Generalidades/SPEC-002-cambio-empresa-multi-referencia.md) |
| **S3 – Salida segura** | Logout invalida sesión en servidor si aplica y limpia cliente | [HU-003](../../03-historias-usuario/001-Seguridad/HU-003-logout.md) | [SPEC-003](SPEC-003-cerrar-sesion.md) |
| **S4 – Gestión de contraseña por el usuario** | Cambio autenticado, primer login forzado opcional | [HU-004](../../03-historias-usuario/001-Seguridad/HU-004-cambio-contraseña.md) | [SPEC-004](SPEC-004-cambio-contrasena.md) |
| **S5 – Recuperación ante olvido** | Flujo por email/token sin filtrar existencia de cuenta | [HU-005](../../03-historias-usuario/001-Seguridad/HU-005-recuperacion-contraseña.md) | [SPEC-005](SPEC-005-recuperacion-contrasena.md) |
| **S6 – Gobierno de identidades** | CRUD usuarios, unicidad, inhabilitación lógica | [HU-010](../../03-historias-usuario/001-Seguridad/HU-010-administracion-usuarios.md) | [SPEC-010](SPEC-010-administracion-usuarios.md) |
| **S7 – Gobierno de roles** | Definición de roles y flag de acceso total | [HU-012](../../03-historias-usuario/001-Seguridad/HU-012-administracion-roles.md) | [SPEC-012](SPEC-012-administracion-roles.md) |
| **S8 – Asignación usuario–rol** | `Pq_Permiso` sin dimensión empresa; unicidad lógica pares usuario–rol | [HU-013](../../03-historias-usuario/001-Seguridad/HU-013-administracion-permisos.md) | [SPEC-013](SPEC-013-administracion-permisos.md) |
| **S9 – Permisos por opción de menú** | Alta/Baja/Modi/Repo por rol y `pq_menus` cuando no hay acceso total | [HU-014](../../03-historias-usuario/001-Seguridad/HU-014-administracion-atributos-rol.md) | [SPEC-014](SPEC-014-administracion-atributos-rol.md) |
| **S10 – Menú versionado** | Seed idempotente, árbol reproducible | [HU-015](../../03-historias-usuario/001-Seguridad/HU-015-menu-sistema.md) | [SPEC-015](SPEC-015-menu-sistema-seed.md) |
| **S11 – Menú filtrado por sesión** | Endpoint que aplica roles + atributos + `enabled` | [HU-016](../../03-historias-usuario/001-Seguridad/HU-016-api-menu-usuario.md) | [SPEC-016](SPEC-016-api-menu-usuario.md) |
| **S12 – Navegación lateral dinámica** | Sidebar consume API; activo/expandido; sin hardcode de permisos | [HU-017](../../03-historias-usuario/001-Seguridad/HU-017-sidebar-dinamico-pqmenus.md) | [SPEC-017](SPEC-017-sidebar-dinamico-pqmenus.md) |
| **S13 – Datos de navegación en seed** | `routeName` y `enabled` para integrar router y menú | [HU-018](../../03-historias-usuario/001-Seguridad/HU-018-seed-menu-routeName-enabled.md) | [SPEC-018](SPEC-018-seed-menu-routename-enabled.md) |

---

## 4. Cadena de dependencias (vista “hubiese sido antes”)

Coherente con el README de la épica; en forma de capacidades:

```
S1 → S3                    (sesión: entrar / salir)
S1 → S4, S5                 (credenciales: gestión y recuperación)
S1 → S6, S7, S8            (admin: usuarios, roles, asignaciones)
S7, S10, S9 → S11 → S12    (menú maestro + permisos → API → sidebar)
S10 → S13                  (seed reforzado para rutas)
```

**S2** es trazabilidad cruzada con épica 000 (MULTI); no bloquea entrega MONO.

---

## 5. Reglas transversales (épica)

- Resolución de permisos efectivos: unión de roles; `AccesoTotal` simplifica menú completo habilitado.
- Sin `users_identities` / login social en el alcance documentado actual.
- Cumplir mapa visual de seguridad MONO del repo cuando exista divergencia con legado ERP.

---

## 6. Fuera de alcance

- ABM visual completo de edición de árbol de menú en producción (HU-015 lo excluye).
- Administración de empresas / grupos en MONO.

---

## 7. Criterios verificables a nivel épica

- [ ] **S1–S5** cubren ciclo de vida de credenciales y sesión sin fugas de información en errores de login.
- [ ] **S6–S9** permiten reproducir en un entorno limpio un usuario con menú acotado.
- [ ] **S10–S13** permiten reconstruir navegación solo desde seed + datos de permisos, sin edición manual requerida en BD para el caso documentado.

---

## 8. Reconstrucción y límites

Este SPEC-CTX es una **reconstrucción**: resume la intención de épica **como si** hubiera sido el insumo previo a las HU. No sustituye `docs/01-arquitectura/06-mapa-visual-seguridad-roles-permisos-menu.md` ni el diccionario de datos; **complementa** la trazabilidad Open-Spec.

Si el producto cambia (p. ej. OAuth), el cambio de alcance debe entrar primero como **SPEC-update** de contexto o del SPEC operativo afectado, y luego propagarse a HU/TR.

---

**Lectura sugerida siguiente:** [README de épica](../../03-historias-usuario/001-Seguridad/README.md) e índice [Open-Spec README](../README.md).
