# Matriz endpoint ↔ permiso — MVP SistemaPartes

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


Documento **vivo**: actualizar en el mismo slice/PR que el endpoint (norma [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1).

**Estado:** sincronizado con seed TR-GEN-02-modelo-roles-permisos-seed y login TR-GEN-02-login-sesion (2026-05-29). **Pivots (2026-06-11):** TR-GEN-08-*.

## Roles seed (`Pq_Rol`)

| `nombre_rol` | `acceso_total` | Notas |
|--------------|----------------|--------|
| Cliente | false | `cliente.mvp` |
| Vendedor | false | `vendedor.sinMenu.mvp`, `usuario.sinVinculo.mvp` |
| VendedorAcotado | false | `vendedor.acotado.mvp` + `PQ_RolAtributo` |
| Supervisor | true | `supervisor.mvp` |

## Menú acotado (`vendedor.acotado.mvp`)

Procedimientos ERP: `pw_cargapedidos`, `pw_presupuestosingresados`, `pw_pedidosingresados`, `pw_dashboard`.

## Usuarios QA seed

| `codigo` | Objetivo |
|----------|----------|
| `cliente.mvp` | Login OK cliente |
| `vendedor.acotado.mvp` | Menú parcial |
| `supervisor.mvp` | Acceso total |
| `usuario.sinPermiso.mvp` | 403 `auth.noPermission` |
| `usuario.sinVinculo.mvp` | 403 `auth.noCommercialProfile` |
| `usuario.perfilAmbiguo.mvp` | 403 `auth.noCommercialProfile` (cliente + vendedor mismo `cod_login`) |
| `vendedor.sinMenu.mvp` | Login OK, menú vacío |
| `primerIngreso.mvp` | Gate `firstLogin` + cambio obligatorio |
| `cambioClave.mvp` | Cambio voluntario + login post-cambio |

## Leyenda

| Columna | Significado |
|---------|-------------|
| **Público** | Sin Bearer; sin `security` en OpenAPI |
| **Permiso** | Atributo mínimo o regla (AccesoTotal satisface atributos) |
| **TR origen** | TR que introduce o documenta el endpoint |

## Rutas públicas

| Método | Path | TR origen | Notas |
|--------|------|-----------|--------|
| GET | `/api/v1/health` | scaffold | Health check |
| POST | `/api/v1/auth/login` | TR-GEN-02-login-sesion | |
| POST | `/api/v1/auth/password/forgot` | TR-GEN-02-recuperacion-contrasena | |
| POST | `/api/v1/auth/password/reset` | TR-GEN-02-recuperacion-contrasena | |

## Autenticación y sesión

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| POST | `/api/v1/auth/logout` | Usuario autenticado | TR-GEN-02-login-sesion |
| GET | `/api/v1/auth/me` | Usuario autenticado | TR-GEN-02-login-sesion |
| POST | `/api/v1/auth/password/change` | Usuario autenticado | TR-GEN-02-cambio-contrasena |

## Preferencias y shell (Generalidades)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/users/me/preferences` | Usuario autenticado | TR-GEN-01-menu-avatar |
| PATCH | `/api/v1/users/me/preferences` | Usuario autenticado | TR-GEN-01-menu-avatar |
| PATCH | `/api/v1/users/me/preferences/locale` | Usuario autenticado | TR-GEN-01-idioma |
| PATCH | `/api/v1/users/me/preferences/theme` | Usuario autenticado | TR-GEN-01-apariencia-temas |
| GET | `/api/v1/config/public` | Usuario autenticado | TR-GEN-01-ayuda-externa, TR-GEN-03-layouts-grilla (`gridLayoutsEnabled`), TR-GEN-08-* (`pivotsEnabled`, `pivotLayoutsEnabled`) |
| GET | `/api/v1/config/parametros` | `Permiso_Repo` + **`pw_consultaparametros`** | TR-GEN-04-consulta-parametros |

## Menú

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/user/menu` | `Pq_Permiso` + filtro atributos menú | TR-GEN-02-autorizacion-menu-api |

## Layouts de grilla (UI transversal)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/grid-layouts` | Usuario autenticado | TR-GEN-03-layouts-grilla |
| GET | `/api/v1/grid-layouts/active` | Usuario autenticado | TR-GEN-03-layouts-grilla |
| POST | `/api/v1/grid-layouts` | Usuario autenticado | TR-GEN-03-layouts-grilla |
| PUT | `/api/v1/grid-layouts/{id}` | Usuario autenticado; solo creador del layout | TR-GEN-03-layouts-grilla |
| DELETE | `/api/v1/grid-layouts/{id}` | Usuario autenticado; solo creador del layout | TR-GEN-03-layouts-grilla |
| PUT | `/api/v1/grid-layouts/active` | Usuario autenticado | TR-GEN-03-layouts-grilla |

## Pivots — metadata y dataset (SPEC-001-08)

Permiso: `Permiso_Repo` sobre `procedimiento_host` de la fila en `pq_pivots_consultas` (resuelto vía `VisibilityPermissionGuard`).

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/pivots/consultas/{consultaId}/metadata` | `Permiso_Repo` + `procedimiento_host` | TR-GEN-08-motor-metadata-pivots |
| POST | `/api/v1/pivots/consultas/{consultaId}/data` | Idem | TR-GEN-08-motor-metadata-pivots |
| POST | `/api/v1/pivots/consultas/{consultaId}/validate-structure` | Idem | TR-GEN-08-motor-metadata-pivots |

## Pivots — diseños guardados (SPEC-001-08)

Misma regla de permiso consulta que metadata. PUT/DELETE diseño: solo `created_by_user_id`.

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/pivot-configs` | Permiso consulta (`consultaId`) | TR-GEN-08-layouts-pivot |
| GET | `/api/v1/pivot-configs/active` | Permiso consulta | TR-GEN-08-layouts-pivot |
| POST | `/api/v1/pivot-configs` | Permiso consulta | TR-GEN-08-layouts-pivot |
| PUT | `/api/v1/pivot-configs/{configId}` | Permiso consulta; solo creador | TR-GEN-08-layouts-pivot |
| DELETE | `/api/v1/pivot-configs/{configId}` | Permiso consulta; solo creador | TR-GEN-08-layouts-pivot |
| PUT | `/api/v1/pivot-configs/active` | Permiso consulta | TR-GEN-08-layouts-pivot |

## Export pivot (client-side)

Sin endpoint API. Visible en UI solo en modo pivot; requiere permiso de ver la consulta (misma pantalla). TR: TR-GEN-08-exportacion-pivot.

## Visibilidad de datos (base; extiende SPEC-101)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/clientes` | `Permiso_Repo` + `visibleClientsForUser` + perfil §7.3 | TR-GEN-02-visibilidad-datos-SistemaPartes |
| GET | `/api/v1/comprobantes/{id}` | `Permiso_Repo` + `visibleClientsForUser` + perfil §7.3 | TR-GEN-02-visibilidad-datos-SistemaPartes |
| GET | `/api/v1/dashboard/resumen` | `Permiso_Repo` + `visibleClientsForUser` + perfil §7.3 | TR-GEN-02-visibilidad-datos-SistemaPartes |

## Negocio SistemaPartes (SPEC-101 — detalle en cada TR)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| POST/PUT/GET/DELETE | `/api/v1/pedidos/*` | Menú **`pw_cargapedidos`** + visibilidad | TR-SPEC-101-05-controllers-rest |
| POST/PUT/GET | `/api/v1/presupuestos/*` | **`pw_cargapedidos`** — **sin** DELETE | TR-SPEC-101-05-controllers-rest |
| POST | `/api/v1/presupuestos/{id}/cerrar` | **`pw_cargapedidos`** | TR-SPEC-101-05-controllers-rest |
| POST | `/api/v1/comprobantes/grabar`, `/copiar` | **`pw_cargapedidos`** | TR-SPEC-101-05-controllers-rest |
| POST | `/api/v1/pedidos/{id}/edicion/*` | **`pw_cargapedidos`** | TR-SPEC-101-05-controllers-rest |
| GET | `/api/v1/consultas/pedidos-ingresados` | `Permiso_Repo` + **`pw_pedidosingresados`** + visibilidad | TR-SPEC-101-07-consultas-api |
| GET | `/api/v1/consultas/pedidos-pendientes` | `Permiso_Repo` + **`pw_pedidospendientes`** + visibilidad | TR-SPEC-101-07-consultas-api |
| GET | `/api/v1/consultas/presupuestos` | `Permiso_Repo` + **`pw_presupuestosingresados`** + visibilidad | TR-SPEC-101-07-consultas-api |
| GET | `/api/v1/consultas/stock` | `Permiso_Repo` + **`pw_consultastock`** | TR-SPEC-101-07-consultas-api |
| GET | `/api/v1/consultas/deuda` | `Permiso_Repo` + **`pw_deudaclientes`** + visibilidad | TR-SPEC-101-07-consultas-api |
| GET | `/api/v1/consultas/cheques` | `Permiso_Repo` + **`pw_consultacheques`** + visibilidad | TR-SPEC-101-07-consultas-api |
| GET | `/api/v1/consultas/historial-ventas` | `Permiso_Repo` + **`pw_historialventas`** + visibilidad | TR-SPEC-101-07-consultas-api |
| GET | `/api/v1/consultas/detalle-pedidos` | `Permiso_Repo` + **`pw_detallepedidos`** + visibilidad | TR-SPEC-101-07-consultas-api (Bloque 3) |
| GET | `/api/v1/config/parametros-carga` | Usuario autenticado + perfil comercial | TR-SPEC-101-10-pantalla-carga |
| GET | `/api/v1/dashboard/operativo` | `Permiso_Repo` + **`pw_dashboard`** + visibilidad | TR-SPEC-101-14-dashboard |
| GET | `/api/v1/integracion/logs` | `Permiso_Repo` + **`pw_logsintegracion`** | TR-SPEC-101-08-logs-integracion |

## Menú MVP — procedimientos nuevos (2026-06-03)

| Procedimiento | Ruta UI | TR origen |
|---------------|---------|-----------|
| `pw_consultaparametros` | `/general/parametros` | TR-GEN-04-consulta-parametros |
| `pw_detallepedidos` | `/pedidos/detalle` | TR-SPEC-101-11-consultas-ui (Bloque 3) |
| `grp_general` | (grupo) | TR-GEN-04-consulta-parametros |

## Admin seguridad — roles y permisos (SPEC-001-02-admin — aplicado D1 2026-06-19)

Epic post-MVP. Gate: `ADMIN_SECURITY_UI_ENABLED` + `AdminSecurityAccessService`. Procedimientos menú: `pw_adminroles`, `pw_adminpermisos`.

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/admin/roles` | `Permiso_Repo` + `pw_adminroles` | TR-GEN-02-admin-roles |
| POST | `/api/v1/admin/roles` | `Permiso_Alta` + `pw_adminroles` | TR-GEN-02-admin-roles |
| PUT | `/api/v1/admin/roles/{id}` | `Permiso_Modi` + `pw_adminroles` | TR-GEN-02-admin-roles |
| DELETE | `/api/v1/admin/roles/{id}` | `Permiso_Baja` + `pw_adminroles` | TR-GEN-02-admin-roles |
| GET | `/api/v1/admin/roles/{id}/atributos` | `Permiso_Repo` + `pw_adminroles` | TR-GEN-02-admin-rol-atributos |
| PUT | `/api/v1/admin/roles/{id}/atributos` | `Permiso_Modi` + `pw_adminroles` | TR-GEN-02-admin-rol-atributos |
| GET | `/api/v1/admin/permisos` | `Permiso_Repo` + `pw_adminpermisos` | TR-GEN-02-admin-permisos |
| POST | `/api/v1/admin/permisos` | `Permiso_Alta` + `pw_adminpermisos` | TR-GEN-02-admin-permisos |
| PUT | `/api/v1/admin/permisos/{id}` | `Permiso_Modi` + `pw_adminpermisos` | TR-GEN-02-admin-permisos |
| DELETE | `/api/v1/admin/permisos/{id}` | `Permiso_Baja` + `pw_adminpermisos` | TR-GEN-02-admin-permisos |
| POST | `/api/v1/admin/permisos/batch` | `Permiso_Alta` + `pw_adminpermisos` | TR-GEN-02-admin-permisos-bulk |
| GET | `/api/v1/admin/usuarios` | `Permiso_Repo` + `pw_adminpermisos` | TR-GEN-02-admin-permisos |

**Menú (seed D1 — `paqsuite:seed-menus-mvp`):**

| Procedimiento | Ruta UI | TR origen |
|---------------|---------|-----------|
| `grp_seguridad` | (grupo) | TR-GEN-02-admin-roles |
| `pw_adminroles` | `/admin/roles` | TR-GEN-02-admin-roles |
| `pw_adminpermisos` | `/admin/permisos` | TR-GEN-02-admin-permisos |

## Chat Asistente IA (SPEC-001-10 — slice 1 D 2026-06-21)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/chat-assistant/providers` | Usuario autenticado | TR-GEN-10-catalogo-proveedores-ia |
| GET | `/api/v1/chat-assistant/me/configuration` | Usuario autenticado | TR-GEN-10-configuracion-asistente-ia |
| PUT | `/api/v1/chat-assistant/me/configuration` | Usuario autenticado | TR-GEN-10-configuracion-asistente-ia |
| PATCH | `/api/v1/chat-assistant/me/configuration/status` | Usuario autenticado | TR-GEN-10-configuracion-asistente-ia |
| POST | `/api/v1/chat-assistant/messages` | Usuario autenticado + configuración válida | TR-GEN-10-chat-documental / TR-GEN-10-imagenes-asistente-ia (texto e imágenes JSON `contentBase64`) |

## Checklist de mantenimiento

- [ ] Cada fila nueva tiene OpenAPI con `security`, 401, 403 (si protegida)
- [ ] Tests integración 401/403 por endpoint crítico
- [ ] Coherente con seed `Pq_Permiso` / `PQ_RolAtributo`







