# Épica 000 – Generalidades

## Open-Spec de contexto (épica)

Antecedente lógico “como si hubiese existido antes de las HU”: [SPEC-CTX-000 – Generalidades](../../05-open-spec/000-Generalidades/SPEC-CTX-000-generalidades.md).

## Objetivo

Historias de usuario que aplican de forma transversal a múltiples módulos o pantallas del sistema.

**Instalación de este repo:** modo **MONO** (sin tabla operativa `PQ_Empresa`, sin `X-Company-Id`). La narrativa “cambio de empresa activa” **no aplica**; queda documentada como referencia MULTI en [SPEC-002 – Cambio empresa (MULTI)](../../05-open-spec/000-Generalidades/SPEC-002-cambio-empresa-multi-referencia.md) (la historia HU-002 homónima no se mantiene como archivo en esta carpeta). La **apariencia (tema)** es **por usuario** ([HU-005](HU-005-seleccion-apariencias.md)), no por empresa ni por instalación única.

## Normas transversales (rendimiento y obtención de datos)

Ver `.cursor/rules/34-obtencion-datos-performance.md` (en MONO ignorar apartados exclusivos de tenant). Si el repo define un README agrupador de historias en esta rama, conviene enlazarlo aquí.

## Historias

| HU | Título | Clasificación |
|----|--------|---------------|
| [HU-001](HU-001-layouts-grilla.md) | Layouts persistentes de grillas | SHOULD-HAVE |
| [HU-003](HU-003-apertura-menu-misma-o-nueva-pestana.md) | Apertura de opción de menú en misma o nueva pestaña | SHOULD-HAVE |
| [HU-004](HU-004-seleccion-idioma.md) | Selección de idioma de la aplicación | SHOULD-HAVE |
| [HU-005](HU-005-seleccion-apariencias.md) | Selección de apariencia (look & feel) por usuario | SHOULD-HAVE |
| [HU-006](HU-006-exportacion-excel.md) | Exportación a Excel desde grillas | SHOULD-HAVE |
| [HU-007](HU-007-Parametros-generales.md) | Parámetros generales del sistema | MUST-HAVE |
| [HU-008](HU-008-multilingual-idiomas-banderas.md) | Multilingual con varios idiomas y banderas | SHOULD-HAVE |
| [HU-009](HU-009-eliminar-registros-grillas-abm.md) | Eliminar registros en grillas ABM | MUST-HAVE |
| [HU-010](HU-010-acceso-ayuda-externa-chat-compartido.md) | Acceso a ayuda externa mediante chat compartido | SHOULD-HAVE |

**Reserva MULTI (no hay archivo HU en esta carpeta):** cambio de empresa activa — ver [SPEC-002](../../05-open-spec/000-Generalidades/SPEC-002-cambio-empresa-multi-referencia.md). La épica 001 – Seguridad enlaza el mismo tema en su tabla con la ruta histórica `HU-002-cambio-empresa-activa.md` si se restaura el archivo.

Cada HU incluye sección **Especificación (Open-Spec)** con enlace al `SPEC-NNN` correspondiente. Índice completo: [docs/05-open-spec/README.md](../../05-open-spec/README.md).

## Referencias

- `.cursor/rules/34-obtencion-datos-performance.md` – Rendimiento y obtención de datos (API; en MONO ignorar apartados exclusivos de tenant)
- `docs/06-operacion/instructivo-optimizar-velocidad.md` – Medición y optimización
- `.cursor/Docs/eficiencia-tecnica.md` – Patrones aplicados en el repo
- `.cursor/rules/24-devextreme-grid-standards.md` – Estándar de grillas
- `docs/modelo-datos/md-seguridad.md` – Esquema en diccionario (incluye `pq_grid_layouts`, `users`, etc.)
- `docs/modelo-datos/pq-parametros-gral.md` – Tabla `PQ_PARAMETROS_GRAL` (MONO: mismo esquema que el resto de la instalación)
- `docs/00-contexto/05-parametros-generales.md` – Parámetros generales
- `docs/05-open-spec/README.md` – Índice Open-Spec (SPEC y SPEC-CTX)
