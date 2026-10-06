# 99 — Manual de usuario (corpus Asistente IA)

Corpus restringido de información para el **Asistente IA** del menú **avatar** en **Partes de Atención**. También sirve como manual operativo para asistentes, supervisores, clientes y soporte funcional.

Plantilla de redacción: [`_plantilla-manual-spec.md`](./_plantilla-manual-spec.md).

## Reglas de este corpus

| Regla | Detalle |
|-------|---------|
| Lenguaje | Uso y negocio; español canónico v1 |
| Prohibido | Explicaciones técnicas de software, infraestructura o codificación |
| SDK / componentes GEN | No se documentan aquí (manual análogo en Framework) |
| Conectividad / integraciones | Solo indicar que el administrador provea la URL de documentación **OpenAPI** del backend (`/api/documentation` en su instalación) |
| Errores | Catálogo maestro en [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md); cada proceso lista los que pueden aparecer (código **P00**–**P11**) |

Un archivo `.md` por capacidad (prefijo `SPEC-00N`). Los archivos que empiezan con `_` no se incluyen en el índice automático del asistente.

## Índice Partes de Atención

| Documento | Contenido | Estado |
|-----------|-----------|--------|
| [Partes-Atencion.md](./Partes-Atencion.md) | Visión integral, menú, perfiles, mapa de pantallas | Publicado |
| [SPEC-001-objetos-datos-y-glosario.md](./SPEC-001-objetos-datos-y-glosario.md) | Objetos maestros, significado de cada dato, glosario | Publicado |
| [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md) | Todos los errores catalogados por proceso | Publicado |
| [SPEC-002-identidad-funcional-y-acceso.md](./SPEC-002-identidad-funcional-y-acceso.md) | Login, perfil Partes, acceso | Publicado |
| [SPEC-003-maestros-y-catalogos.md](./SPEC-003-maestros-y-catalogos.md) | Archivos: asistentes, clientes, tipos | Publicado |
| [SPEC-004-operacion-carga-diaria.md](./SPEC-004-operacion-carga-diaria.md) | Carga diaria, captura inteligente | Publicado |
| [SPEC-005-supervision-proceso-masivo.md](./SPEC-005-supervision-proceso-masivo.md) | Cierre/reapertura masiva (supervisor) | Publicado |
| [SPEC-006-consultas-dashboard-navegacion.md](./SPEC-006-consultas-dashboard-navegacion.md) | Dashboard, informes, pivot web | Publicado |
| [SPEC-007-mobile-capacitor.md](./SPEC-007-mobile-capacitor.md) | App móvil | Publicado |
| [SPEC-008-asistente-ia-y-preferencias.md](./SPEC-008-asistente-ia-y-preferencias.md) | Chat avatar, preferencias de modelo | Publicado |
| [SPEC-009-importacion-excel.md](./SPEC-009-importacion-excel.md) | Importación Excel en carga diaria | Publicado |
| [SPEC-010-emisiones-informes.md](./SPEC-010-emisiones-informes.md) | Emitir reportes y diseñador | Publicado |
| [SPEC-011-parametros-seguridad.md](./SPEC-011-parametros-seguridad.md) | Parámetros y administración de seguridad | Publicado |

Última actualización del índice: **2026-10-05**.
