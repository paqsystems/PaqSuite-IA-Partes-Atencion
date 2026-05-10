# HU-007 – Parámetros generales del sistema

## Épica
000 – Generalidades

## Clasificación
MUST-HAVE

## Estado de alcance
**Estado:** En Control Calidad — pendiente cierre de [HU-007-update](../updates/000-Generalidades/HU-007-Parametros-generales-update.md) (CC PQ 06/04/2026), [HU-007 – update UI DevExtreme](../updates/000-Generalidades/HU-007-Parametros-generales-update-ui-devextreme.md) y [HU-007 – update 01](../updates/000-Generalidades/HU-007-Parametros-generales-update-01.md) (CC PQ 18/04/2026 — CAPTION/TOOLTIP y cabecera).

## Rol
Usuario con permiso de configuración (por módulo)

## Narrativa

Como usuario responsable de la configuración quiero editar los parámetros generales de cada módulo desde una pantalla dedicada para que el sistema se adapte a las necesidades de la instalación sin modificar código.

## Criterios de aceptación

- Existe un proceso general de mantenimiento de la tabla `PQ_PARAMETROS_GRAL`.
- El proceso se invoca desde ítems de menú; cada ítem tiene `PQ_MENUS.Procedimiento` = nombre clave del módulo (ej. `PartesProduccion`).
- Solo se muestran y editan los registros cuyo `Programa` coincide con el `procedimiento` del ítem de menú desde el que se accedió.
- El proceso **no permite** agregar ni eliminar registros; solo editar el campo `Valor_*` correspondiente según `tipo_valor` de cada fila.
- **Interfaz en dos partes:** (1) **Listado** de solo lectura con **Clave**, **etiqueta breve** desde metadatos de datos (`caption` / columna `CAPTION` en `PQ_PARAMETROS_GRAL` cuando exista) y/o i18n para el marco de pantalla, y **valor mostrado homogéneo como texto** (todos los tipos convertidos a string para una vista uniforme). Los **booleanos** se muestran con etiquetas localizadas (p. ej. Sí/No según idioma), no literales `true`/`false`; `NULL` se lee como negativo. **Ayuda por fila:** tooltip desde `tooltip` / columna `TOOLTIP` cuando exista. (2) En cada fila, un botón **Editar** que abre el flujo de edición de **ese** parámetro: **modal** o **misma pantalla** mostrando únicamente el control acorde al `tipo_valor` (checkbox/boolean, número entero, decimal, texto, texto largo, fecha/hora). No se deben mostrar cajas de texto genéricas para todos los tipos en el listado. Textos de UI bajo claves `parametrosGral.*` en los locales del frontend. Ver `.cursor/rules/32-parametros-generales-ui-listado-y-edicion-por-tipo.md`.
- Los parámetros se consultan en la **única base de datos** del producto (modo **MONO**); la tabla `PQ_PARAMETROS_GRAL` reside en ese esquema compartido.
- **Sin multi-tenant:** las peticiones API **no** requieren **`X-Company-Id`**. No hay “empresa activa” ni Company DB por sociedad; un mismo usuario ve los parámetros de esa única instalación.
- **Coincidencia de módulo:** el filtro por módulo usa el campo `Programa` (mismo valor canónico que `PQ_MENUS.Procedimiento`). Backend y seeds deben usar el mismo literal; la API puede comparar **sin distinguir mayúsculas/minúsculas** para evitar desajustes con SQL Server.
- En el listado, cada fila muestra: Clave, descripción breve (`caption` / `CAPTION`) si aplica, y **valor en texto** (representación legible del dato). La edición del valor ocurre solo tras **Editar**, con validación de tipos y rangos al **Guardar** en ese flujo.
- Los registros iniciales se cargan vía seed en los deploys (igual que `PQ_MENUS`).

## Tablas involucradas

- `PQ_PARAMETROS_GRAL` (esquema único de la instalación): Programa, Clave, tipo_valor, CAPTION, TOOLTIP, Valor_String, Valor_Text, Valor_Int, Valor_DateTime, Valor_Bool, Valor_Decimal
- `PQ_MENUS`: procedimiento (vincula al proceso y filtra por módulo)

## Reglas de negocio

- Solo se editan valores; la estructura (claves) se define en seeds por módulo.
- El usuario debe tener permiso para **acceder a la configuración de parámetros** del módulo (autorización MONO: sin validación por empresa).
- El proceso es reutilizable: cada módulo define sus claves en su propia HU de parámetros e invoca este proceso indicando su nombre clave.

## Dependencias

- HU-001 (Login) – autenticación
- Tabla `PQ_PARAMETROS_GRAL` creada (migración/script)
- Seeds por módulo que definen las filas (Programa, Clave, tipo_valor)

## Control de calidad

- Hallazgo 2026-04-09: el listado de la pantalla de parámetros generales no está alineado con el estándar DevExtreme del resto de la aplicación. Alcance y criterios de corrección: [HU-007 – update](../updates/000-Generalidades/HU-007-Parametros-generales-update-ui-devextreme.md). Tarea técnica: [TR-007 – update](../../04-tareas/updates/000-Generalidades/TR-007-Parametros-generales-update-ui-devextreme.md).

## HUs que invocan este proceso

Cada módulo que tenga parámetros generales tendrá una HU que:
- Define el nombre clave (PROGRAMA) del módulo.
- Lista las claves con sus tipos.
- Incluye un ítem de menú con `procedimiento` = nombre clave, que abre este proceso filtrando por ese valor.

## Especificación (Open-Spec)

- [SPEC-007 – Parámetros generales por módulo](../../05-open-spec/000-Generalidades/SPEC-007-parametros-generales.md)

## Referencias

- `docs/00-contexto/05-parametros-generales.md` – Objetivo y diseño
- `docs/modelo-datos/md-empresas/pq-parametros-gral.md` – Esquema de la tabla (documentación legada puede citar “Company DB”; en **MONO** la tabla vive en el esquema único de la instalación)
- `.cursor/rules/27-parametros-generales-por-modulo.md` – Formato de la HU por módulo
- `.cursor/rules/28-plan-tareas-hu-parametros-generales.md` – Plan de tareas del TR
- `.cursor/rules/32-parametros-generales-ui-listado-y-edicion-por-tipo.md` – Listado homogéneo + edición por tipo
