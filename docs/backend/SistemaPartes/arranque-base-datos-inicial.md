# Arranque de base de datos inicial de Sistema Partes

## Objetivo

Dejar documentado el proceso operativo para generar la base de datos inicial del modulo `SistemaPartes` de forma consistente, versionada y reproducible desde el repositorio.

Este documento absorbe el contenido operativo de la antigua HU tecnica de generacion inicial de base de datos y no debe confundirse con las reglas de negocio del modulo.

## Alcance

Incluye:

- generacion del esquema inicial a partir del modelo definido;
- migraciones Laravel versionadas;
- uso del MCP SQL Server para verificacion o ejecucion controlada;
- seeders minimos para desarrollo, pruebas y validacion inicial;
- capacidad de recrear el entorno desde cero.

No incluye:

- logica de negocio;
- pantallas o endpoints funcionales;
- optimizaciones avanzadas de performance;
- datos reales de produccion.

## Fuentes del esquema

La base inicial debe mantenerse sincronizada con:

- `docs/02-producto/SistemaPartes/modelo-datos.md`
- `database/modelo-datos.dbml` si se mantiene como fuente auxiliar del modelo
- migraciones Laravel del backend

## Convenciones del modulo

- Prefijo de tablas `PQ_PARTES_`, excepto la tabla comun `USERS`.
- Campos en `snake_case`.
- Indices con prefijo `idx_` cuando corresponda.
- Relaciones e integridad referencial alineadas con el modelo de producto.

## Herramientas de ejecucion

### Laravel

El esquema versionado debe existir en migraciones con soporte de `up()` y `down()`, de modo que pueda ejecutarse con:

```powershell
php artisan migrate
php artisan migrate:rollback
```

### MCP SQL Server

Puede utilizarse `mssql-toolbox` o `mssql` para:

- verificar el esquema generado;
- ejecutar sentencias SQL controladas;
- validar consistencia directa contra la base objetivo.

El uso del MCP no reemplaza a las migraciones versionadas del repositorio.

## Seeders minimos esperados

Para habilitar el trabajo inicial y los tests automatizados, el arranque deberia incluir como minimo:

- un usuario administrador o supervisor;
- un cliente de prueba;
- un tipo de cliente;
- un tipo de tarea generico con `is_default = true`.

## Reproducibilidad

La base inicial debe poder recrearse desde cero en un entorno limpio sin pasos manuales fuera de:

- el repositorio;
- la configuracion del entorno Laravel;
- y el acceso a SQL Server o MCP configurado.

## Base objetivo

Mientras el entorno del proyecto lo mantenga asi, la base inicial utilizada para este modulo es la llamada `Lidr`.

Si este criterio cambia, debe actualizarse este documento y la configuracion operativa correspondiente.

## Resultado esperado

- esquema completo creado correctamente;
- migraciones Laravel versionadas;
- seeders minimos disponibles;
- rollback funcional;
- evidencia de que el entorno puede reconstruirse desde cero.
