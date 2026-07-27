# HU-00 – Generación de la base de datos inicial a partir del modelo definido

## Épica
Épica 0: Infraestructura y Base del Sistema

## Rol
Administrador del sistema (infraestructura / plataforma)

## Clasificación
MUST-HAVE

## Historia
Como administrador del sistema, quiero generar la base de datos inicial a partir del modelo de datos definido, para disponer de una estructura consistente, versionada y reproducible que habilite el desarrollo, prueba y validación del resto del MVP.

## Contexto / Justificación
Esta historia de usuario es una **HU técnica habilitadora**.

Su objetivo no es aportar funcionalidad de negocio directa, sino establecer la infraestructura mínima necesaria para:
- implementar historias funcionales,
- ejecutar tests automatizados,
- garantizar consistencia entre entornos,
- permitir la reproducción completa del sistema desde el repositorio.

Debe ejecutarse **antes** del desarrollo de las historias funcionales del sistema.

## In Scope
- Generación del esquema completo de base de datos según el modelo definido en `docs/modelo-datos.md` y `database/modelo-datos.dbml`.
- **Uso del MCP de SQL Server (mssql-toolbox o mssql) configurado** para ejecutar la creación de tablas, índices, foreign keys y restricciones directamente en la base de datos.
- Creación de migraciones Laravel versionadas (up / down) que reflejen el esquema generado, para mantener sincronización entre el código y la base de datos.
- Aplicación de las convenciones del proyecto:
  - Prefijo de tablas `PQ_PARTES_` (excepto la tabla `USERS` que no lleva prefijo).
  - Nomenclatura de campos en snake_case.
  - Índices con prefijo `idx_`.
- Generación de datos mínimos (seeders Laravel) para permitir la ejecución de tests automatizados:
  - Al menos un usuario administrador/supervisor.
  - Al menos un cliente de prueba.
  - Al menos un tipo de cliente.
  - Al menos un tipo de tarea genérico (con `is_default = true`).
- Verificación de que la base de datos puede recrearse desde cero en un entorno limpio.
- Documentación del proceso de creación y ejecución de migraciones.

## Out of Scope
- Implementación de lógica de negocio.
- Desarrollo de endpoints o pantallas funcionales.
- Optimización avanzada de performance (índices adicionales más allá de los requeridos por integridad referencial).
- Uso de datos reales de producción.
- Configuración de backups automáticos.

## Suposiciones
- El modelo de datos inicial ya fue definido y validado en `docs/modelo-datos.md`.
- El entorno dispone de acceso a la base de datos SQL Server mediante el MCP configurado (`mssql-toolbox` o `mssql` en `mcp.json`).
- El motor de base de datos es SQL Server (compatible con las herramientas de migración Laravel).
- Laravel está configurado y listo para generar migraciones.

## Criterios de aceptación
- La base de datos a utilizar es la llamada "Lidr"
- La base de datos a utilizar puede generarse completamente desde cero a partir del repositorio.
- Todas las tablas respetan las convenciones del proyecto (prefijo `PQ_PARTES_`, excepto `USERS`).
- Todas las tablas, campos, índices y foreign keys del modelo están implementados correctamente.
- Existen migraciones Laravel versionadas con capacidad de rollback (métodos `up()` y `down()`).
- Las migraciones pueden ejecutarse tanto mediante Laravel (`php artisan migrate`) como mediante el MCP (para verificación y ejecución directa).
- Existen seeders con datos mínimos para permitir la ejecución de tests automatizados.
- El proceso es reproducible en entornos local y de testing.
- La ejecución no requiere pasos manuales fuera del repositorio y el MCP configurado.
- Se documenta el proceso de creación y ejecución de migraciones.

## Notas técnicas
- **Uso de MCP:** Se utilizará el servidor MCP de SQL Server configurado (`mssql-toolbox` o `mssql`) para ejecutar las sentencias SQL de creación de tablas, índices y restricciones. Esto permite verificación directa y ejecución controlada desde el entorno de desarrollo.
- **Migraciones Laravel:** Aunque la creación inicial puede realizarse mediante MCP, las migraciones Laravel deben generarse para mantener versionado y permitir rollback. Las migraciones deben reflejar exactamente el esquema creado.
- **Sincronización:** El esquema en la base de datos debe estar sincronizado con el modelo definido en `docs/modelo-datos.md` y `database/modelo-datos.dbml`.

## Dependencias
- No depende de historias funcionales.
- Es bloqueante para el desarrollo del resto de las historias del MVP.
- Requiere que el modelo de datos esté completamente definido y validado.

## Resultado Esperado
- Base de datos inicial creada correctamente con todas las tablas, índices y restricciones del modelo.
- Migraciones Laravel versionadas en el repositorio (`database/migrations/`).
- Seeders con datos mínimos en el repositorio (`database/seeders/`).
- Infraestructura de datos lista para el desarrollo de historias funcionales.
- Documentación del proceso de creación y ejecución.
- Evidencia verificable para validación del MVP.
