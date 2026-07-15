# Historias de usuario — 001-Generalidades

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.

Convención: `HU-GEN-{SPEC}-{tema}.md`, derivadas de `docs/05-open-spec/001-Generalidades/`.

Este bloque se copió desde el desarrollo documental del proyecto origen y se adaptó para:

- usar la carpeta `001-Generalidades`;
- reemplazar referencias de producto por `SistemaPartes`;
- dejar las HUs en estado pendiente dentro de este repositorio.

## SPEC-001-01 — Experiencia base

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-01-shell-layout](HU-GEN-01-shell-layout.md) | [TR-GEN-01-shell-layout](../../04-tareas/001-Generalidades/TR-GEN-01-shell-layout.md) | Shell principal post-login | Must |
| [HU-GEN-01-menu-general-sidebar](HU-GEN-01-menu-general-sidebar.md) | [TR-GEN-01-menu-general-sidebar](../../04-tareas/001-Generalidades/TR-GEN-01-menu-general-sidebar.md) | Menú general y sidebar | Must |
| [HU-GEN-01-menu-avatar](HU-GEN-01-menu-avatar.md) | [TR-GEN-01-menu-avatar](../../04-tareas/001-Generalidades/TR-GEN-01-menu-avatar.md) | Menú avatar y preferencias | Must |
| [HU-GEN-01-idioma](HU-GEN-01-idioma.md) | [TR-GEN-01-idioma](../../04-tareas/001-Generalidades/TR-GEN-01-idioma.md) | Selector de idioma e i18n | Must |
| [HU-GEN-01-apariencia-temas](HU-GEN-01-apariencia-temas.md) | [TR-GEN-01-apariencia-temas](../../04-tareas/001-Generalidades/TR-GEN-01-apariencia-temas.md) | Apariencia DevExtreme | Must |
| [HU-GEN-01-ayuda-externa](HU-GEN-01-ayuda-externa.md) | [TR-GEN-01-ayuda-externa](../../04-tareas/001-Generalidades/TR-GEN-01-ayuda-externa.md) | Asistente IA / ayuda | Should |

## SPEC-001-02 — Acceso y seguridad

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-02-modelo-roles-permisos-seed](HU-GEN-02-modelo-roles-permisos-seed.md) | [TR-GEN-02-modelo-roles-permisos-seed](../../04-tareas/001-Generalidades/TR-GEN-02-modelo-roles-permisos-seed.md) | Seed roles/permisos | Must |
| [HU-GEN-02-login-sesion](HU-GEN-02-login-sesion.md) | [TR-GEN-02-login-sesion](../../04-tareas/001-Generalidades/TR-GEN-02-login-sesion.md) | Login y sesión | Must |
| [HU-GEN-02-recuperacion-contrasena](HU-GEN-02-recuperacion-contrasena.md) | [TR-GEN-02-recuperacion-contrasena](../../04-tareas/001-Generalidades/TR-GEN-02-recuperacion-contrasena.md) | Recuperación contraseña | Must |
| [HU-GEN-02-cambio-contrasena](HU-GEN-02-cambio-contrasena.md) | [TR-GEN-02-cambio-contrasena](../../04-tareas/001-Generalidades/TR-GEN-02-cambio-contrasena.md) | Cambio contraseña | Must |
| [HU-GEN-02-expiracion-inactividad](HU-GEN-02-expiracion-inactividad.md) | [TR-GEN-02-expiracion-inactividad](../../04-tareas/001-Generalidades/TR-GEN-02-expiracion-inactividad.md) | Expiración inactividad | Must |
| [HU-GEN-02-autorizacion-menu-api](HU-GEN-02-autorizacion-menu-api.md) | [TR-GEN-02-autorizacion-menu-api](../../04-tareas/001-Generalidades/TR-GEN-02-autorizacion-menu-api.md) | Autorización menú API | Must |
| [HU-GEN-02-politicas-endpoints](HU-GEN-02-politicas-endpoints.md) | [TR-GEN-02-politicas-endpoints](../../04-tareas/001-Generalidades/TR-GEN-02-politicas-endpoints.md) | Políticas endpoints | Must |
| [HU-GEN-02-visibilidad-datos-SistemaPartes](HU-GEN-02-visibilidad-datos-SistemaPartes.md) | [TR-GEN-02-visibilidad-datos-SistemaPartes](../../04-tareas/001-Generalidades/TR-GEN-02-visibilidad-datos-SistemaPartes.md) | Visibilidad por perfil | Must |

### Post-MVP

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-02-admin-roles](HU-GEN-02-admin-roles.md) | [TR-GEN-02-admin-roles](../../04-tareas/001-Generalidades/TR-GEN-02-admin-roles.md) | ABM roles (`Pq_Rol`) | Should |
| [HU-GEN-02-admin-rol-atributos](HU-GEN-02-admin-rol-atributos.md) | [TR-GEN-02-admin-rol-atributos](../../04-tareas/001-Generalidades/TR-GEN-02-admin-rol-atributos.md) | Atributos de rol (`PQ_RolAtributo`) | Should |
| [HU-GEN-02-admin-permisos](HU-GEN-02-admin-permisos.md) | [TR-GEN-02-admin-permisos](../../04-tareas/001-Generalidades/TR-GEN-02-admin-permisos.md) | Permisos individual (`Pq_Permiso`) | Should |
| [HU-GEN-02-admin-permisos-bulk](HU-GEN-02-admin-permisos-bulk.md) | [TR-GEN-02-admin-permisos-bulk](../../04-tareas/001-Generalidades/TR-GEN-02-admin-permisos-bulk.md) | Asignación masiva permisos | Should |

## SPEC-001-03 — UI transversal

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-03-grillas-listados](HU-GEN-03-grillas-listados.md) | [TR-GEN-03-grillas-listados](../../04-tareas/001-Generalidades/TR-GEN-03-grillas-listados.md) | Estándar grillas y listados | Must |
| [HU-GEN-03-layouts-grilla](HU-GEN-03-layouts-grilla.md) | [TR-GEN-03-layouts-grilla](../../04-tareas/001-Generalidades/TR-GEN-03-layouts-grilla.md) | Layouts persistentes (`pq_grid_layouts`) | Must |
| [HU-GEN-03-patron-abm](HU-GEN-03-patron-abm.md) | [TR-GEN-03-patron-abm](../../04-tareas/001-Generalidades/TR-GEN-03-patron-abm.md) | Patrón ABM sobre grilla | Must |
| [HU-GEN-03-exportaciones](HU-GEN-03-exportaciones.md) | [TR-GEN-03-exportaciones](../../04-tareas/001-Generalidades/TR-GEN-03-exportaciones.md) | Exportación Excel desde grillas | Must |

## SPEC-001-04 — Configuración global

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-04-consulta-parametros](HU-GEN-04-consulta-parametros.md) | [TR-GEN-04-consulta-parametros](../../04-tareas/001-Generalidades/TR-GEN-04-consulta-parametros.md) | Consulta parámetros SistemaPartes (solo lectura) | Should |

## SPEC-001-07 — Importar Excel

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-07-plantilla-excel](HU-GEN-07-plantilla-excel.md) | [TR-GEN-07-plantilla-excel](../../04-tareas/001-Generalidades/TR-GEN-07-plantilla-excel.md) | Plantilla modelo por proceso | Could |
| [HU-GEN-07-carga-staging-excel](HU-GEN-07-carga-staging-excel.md) | [TR-GEN-07-carga-staging-excel](../../04-tareas/001-Generalidades/TR-GEN-07-carga-staging-excel.md) | Carga de archivo y staging | Could |
| [HU-GEN-07-grilla-procesamiento-excel](HU-GEN-07-grilla-procesamiento-excel.md) | [TR-GEN-07-grilla-procesamiento-excel](../../04-tareas/001-Generalidades/TR-GEN-07-grilla-procesamiento-excel.md) | Grilla de staging y procesamiento | Could |
| [HU-GEN-07-historial-importaciones](HU-GEN-07-historial-importaciones.md) | [TR-GEN-07-historial-importaciones](../../04-tareas/001-Generalidades/TR-GEN-07-historial-importaciones.md) | Historial de importaciones | Could |
| [HU-GEN-07-ui-embebida-host](HU-GEN-07-ui-embebida-host.md) | [TR-GEN-07-ui-embebida-host](../../04-tareas/001-Generalidades/TR-GEN-07-ui-embebida-host.md) | UI embebida en host | Could |

## SPEC-001-08 — Pivots

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-08-motor-metadata-pivots](HU-GEN-08-motor-metadata-pivots.md) | [TR-GEN-08-motor-metadata-pivots](../../04-tareas/001-Generalidades/TR-GEN-08-motor-metadata-pivots.md) | Motor y catálogo de consultas pivotables | Could |
| [HU-GEN-08-pivotgrid-visualizacion](HU-GEN-08-pivotgrid-visualizacion.md) | [TR-GEN-08-pivotgrid-visualizacion](../../04-tareas/001-Generalidades/TR-GEN-08-pivotgrid-visualizacion.md) | PivotGrid DevExtreme y visualización | Could |
| [HU-GEN-08-layouts-pivot](HU-GEN-08-layouts-pivot.md) | [TR-GEN-08-layouts-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-layouts-pivot.md) | Diseños guardados y toolbar del pivot | Could |
| [HU-GEN-08-exportacion-pivot](HU-GEN-08-exportacion-pivot.md) | [TR-GEN-08-exportacion-pivot](../../04-tareas/001-Generalidades/TR-GEN-08-exportacion-pivot.md) | Exportación Excel desde pivot | Could |

## SPEC-001-10 — Chat Asistente IA

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-10-catalogo-proveedores-ia](HU-GEN-10-catalogo-proveedores-ia.md) | [TR-GEN-10-catalogo-proveedores-ia](../../04-tareas/001-Generalidades/TR-GEN-10-catalogo-proveedores-ia.md) | Catálogo inicial de proveedores IA | Should |
| [HU-GEN-10-configuracion-asistente-ia](HU-GEN-10-configuracion-asistente-ia.md) | [TR-GEN-10-configuracion-asistente-ia](../../04-tareas/001-Generalidades/TR-GEN-10-configuracion-asistente-ia.md) | Configuración personal del asistente IA | Should |
| [HU-GEN-10-mensajes-asistente-ia](HU-GEN-10-mensajes-asistente-ia.md) | [TR-GEN-10-mensajes-asistente-ia](../../04-tareas/001-Generalidades/TR-GEN-10-mensajes-asistente-ia.md) | Mensajes editables del asistente IA | Should |
| [HU-GEN-10-chat-documental](HU-GEN-10-chat-documental.md) | [TR-GEN-10-chat-documental](../../04-tareas/001-Generalidades/TR-GEN-10-chat-documental.md) | Chat documental del asistente IA | Should |
| [HU-GEN-10-imagenes-asistente-ia](HU-GEN-10-imagenes-asistente-ia.md) | [TR-GEN-10-imagenes-asistente-ia](../../04-tareas/001-Generalidades/TR-GEN-10-imagenes-asistente-ia.md) | Adjuntos de imágenes en el chat | Should |

## SPEC-001-11 — Mobile Capacitor

| HU | TR | Título | Prioridad |
|----|----|--------|-----------|
| [HU-GEN-11-mobile-capacitor-scaffold](HU-GEN-11-mobile-capacitor-scaffold.md) | [TR-GEN-11-mobile-capacitor-scaffold](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-capacitor-scaffold.md) | Scaffold Capacitor Android+iOS | Must |
| [HU-GEN-11-mobile-login-tenant](HU-GEN-11-mobile-login-tenant.md) | [TR-GEN-11-mobile-login-tenant](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-login-tenant.md) | Login tenant-first MONO | Must |
| [HU-GEN-11-mobile-config-api](HU-GEN-11-mobile-config-api.md) | [TR-GEN-11-mobile-login-tenant](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-login-tenant.md) | Override URL API | Must |
| [HU-GEN-11-mobile-shell-exclusiones](HU-GEN-11-mobile-shell-exclusiones.md) | [TR-GEN-11-mobile-shell](../../04-tareas/001-Generalidades/TR-GEN-11-mobile-shell.md) | Shell native y exclusiones mobile | Must |



