# Migración a SQL Server (MSSQL) - Documentación Completa

## Objetivo

Este documento describe el proceso completo de migración del proyecto de MySQL a SQL Server (MSSQL), incluyendo configuración, cambios a realizar, y guía de verificación. Es la **tarea inversa** a la documentada en `docs/migracion-mssql-a-mysql.md`.

---

## Contexto

### Estado Anterior (actual del proyecto)
- **Motor de Base de Datos:** MySQL 5.7+ (recomendado 8.0+)
- **Driver Laravel:** `mysql`
- **Host:** `127.0.0.1` (túnel SSH local)
- **Puerto:** `3306`
- **Base de datos:** `Lidr_testing` (o `_datosempresa` según configuración)

### Estado Objetivo
- **Motor de Base de Datos:** SQL Server 2019
- **Driver Laravel:** `sqlsrv`
- **Host:** `PAQ-GAUSS\SQLEXPRESS_AXOFT,2544` (o el host/instancia que corresponda)
- **Puerto:** Vacío o `2544` (según configuración de la instancia)
- **Base de datos:** `Lidr`

---

## Requisitos Previos para SQL Server

### Conexión directa (sin túnel SSH)

A diferencia de MySQL, SQL Server se conecta directamente. No se requiere túnel SSH.

**Requisitos técnicos:**
- Extensión PHP `pdo_sqlsrv` instalada
- Microsoft ODBC Driver for SQL Server instalado
- SQL Server accesible en la red (host e instancia o puerto correctos)

### Formato de conexión

Para instancia nombrada con puerto explícito:
```
DB_HOST=PAQ-GAUSS\SQLEXPRESS_AXOFT,2544
DB_PORT=
```

O si se usa solo el puerto:
```
DB_HOST=127.0.0.1
DB_PORT=2544
```

### Verificar conexión

Antes de proceder con la migración, verificar que SQL Server está accesible:

```bash
cd backend
php artisan tinker

# En tinker, probar conexión
DB::connection()->getPdo();
# Debe retornar el objeto PDO sin errores
```

O usar SQL Server Management Studio (SSMS) para verificar conectividad.

---

## Cambios a Realizar

### 1. Configuración de Entorno

**Archivo:** `backend/.env`

**Cambios:**
```env
# Antes (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=_datosempresa
DB_USERNAME=forge
DB_PASSWORD=Go4QQnC3vmPQ8YwlOaw7

# Después (SQL Server)
DB_CONNECTION=sqlsrv
DB_HOST=PAQ-GAUSS\SQLEXPRESS_AXOFT,2544
DB_PORT=
DB_DATABASE=Lidr
DB_USERNAME=Axoft
DB_PASSWORD=Tango2005
DB_TRUST_SERVER_CERTIFICATE=true
```

**Nota:** Agregar la variable `DB_TRUST_SERVER_CERTIFICATE=true` (requerida para SQL Server en muchos entornos).

### 2. Configuración de Base de Datos

**Archivo:** `backend/config/database.php`

La conexión `sqlsrv` ya existe y debe incluir:

```php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', ''),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'date_format' => 'Y-m-d H:i:s',
    'encrypt' => env('DB_ENCRYPT', 'no'),
    'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'true'),
],
```

**Verificar:** Que `date_format` esté definido como `'Y-m-d H:i:s'`.

### 3. Migraciones

#### 3.1 Migración `personal_access_tokens`

**Archivo:** `backend/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php`

**Estado actual:** Usa Schema Builder de Laravel (compatible con ambos motores). Laravel genera el SQL apropiado según el driver.

**Acción:** No requiere cambios. El Schema Builder adapta la salida para SQL Server automáticamente.

#### 3.2 Migración `fix_clientes_user_id_unique_sqlserver`

**Archivo:** `backend/database/migrations/2026_01_31_000001_fix_clientes_user_id_unique_sqlserver.php`

**Estado:** La migración detecta el driver (`sqlsrv`) y aplica el índice único filtrado específico para SQL Server. Para MySQL no hace nada.

**Acción:** No requiere cambios. Al conectar a SQL Server, la migración aplicará correctamente el índice filtrado que permite múltiples NULL en `user_id`.

### 4. Seeders

**Archivos afectados:**
- `backend/database/seeders/UserSeeder.php`
- `backend/database/seeders/TipoClienteSeeder.php`
- `backend/database/seeders/TipoTareaSeeder.php`
- `backend/database/seeders/UsuarioSeeder.php`
- `backend/database/seeders/ClienteSeeder.php`
- `backend/database/seeders/TestUsersSeeder.php`
- `backend/database/seeders/TestTasksSeeder.php`

**Cambio:** Reemplazar `now()` por `DB::raw('GETDATE()')`.

**Antes (MySQL):**
```php
'created_at' => now(),
'updated_at' => now(),
```

**Después (SQL Server):**
```php
'created_at' => DB::raw('GETDATE()'),
'updated_at' => DB::raw('GETDATE()'),
```

**Razón:** SQL Server puede producir errores de conversión con `now()` en ciertos contextos. `GETDATE()` es la función nativa de SQL Server y evita problemas. Ver `.cursor/rules/20-mssql-server-datetime-format.md`.

### 5. Tests

**Archivos afectados:** Todos los archivos de test en `backend/tests/` que usan `now()` para insertar datos.

**Cambio:** Reemplazar `now()` por `DB::raw('GETDATE()')` en inserciones directas con `DB::table()`.

**Archivos a actualizar:**
- `backend/tests/Feature/Api/V1/ReportControllerTest.php`
- `backend/tests/Feature/Api/V1/TaskControllerTest.php`
- `backend/tests/Unit/Services/PasswordResetServiceTest.php`
- `backend/tests/Feature/Api/V1/Auth/PasswordResetTest.php`
- `backend/tests/Feature/Api/V1/UserProfileTest.php`
- `backend/tests/Unit/Services/UserProfileServiceTest.php`
- `backend/tests/Feature/Api/V1/Auth/ChangePasswordTest.php`
- `backend/tests/Unit/Services/AuthServiceTest.php`
- `backend/tests/Feature/Api/V1/DashboardControllerTest.php`
- `backend/tests/Unit/Services/TaskServiceTest.php`
- `backend/tests/Feature/Api/V1/Auth/LogoutTest.php`
- `backend/tests/Feature/Api/V1/Auth/LoginTest.php`
- `backend/tests/Feature/Database/MigrationTest.php`

### 6. Modelos Eloquent

**Estado:** Sin cambios necesarios. Todos los modelos ya tienen `protected $dateFormat = 'Y-m-d H:i:s'`, que es compatible con SQL Server.

**Archivos verificados:**
- `backend/app/Models/User.php`
- `backend/app/Models/Usuario.php`
- `backend/app/Models/Cliente.php`
- `backend/app/Models/RegistroTarea.php`
- `backend/app/Models/TipoCliente.php`
- `backend/app/Models/TipoTarea.php`
- `backend/app/Models/ClienteTipoTarea.php`
- `backend/app/Models/PersonalAccessToken.php`

### 7. Documentación

- Actualizar `docs/06-operacion/deploy-infraestructura.md` con requisitos de SQL Server
- Consultar `.cursor/rules/20-mssql-server-datetime-format.md` para reglas de fechas

---

## Diferencias Clave: MySQL vs SQL Server

### 1. Índices Únicos con NULLs

| Motor | Comportamiento |
|-------|---------------|
| MySQL | Permite múltiples NULL automáticamente en columnas UNIQUE nullable. |
| SQL Server | Trata múltiples NULL como duplicados en columnas UNIQUE. Requiere índices filtrados (`WHERE columna IS NOT NULL`). |

**Impacto:** La migración `fix_clientes_user_id_unique_sqlserver` ya implementa el índice filtrado para SQL Server. No requiere acción adicional.

### 2. Tipos de Datos

| Tipo MySQL | Equivalente SQL Server | Notas |
|------------|------------------------|-------|
| `VARCHAR` (utf8mb4) | `NVARCHAR` | Laravel Schema Builder maneja esto automáticamente |
| `TIMESTAMP` / `DATETIME` | `DATETIME2` o `DATETIME` | Laravel usa tipos estándar; SQL Server puede usar `datetime2` para mejor compatibilidad |
| `BIGINT AUTO_INCREMENT` | `BIGINT IDENTITY` | Laravel Schema Builder maneja esto con `id()` |

### 3. Funciones de Fecha

| Función MySQL | Equivalente SQL Server | Uso en proyecto |
|---------------|------------------------|-----------------|
| `NOW()` | `GETDATE()` | Usar `DB::raw('GETDATE()')` en seeders y tests |

**Recomendación:** Para SQL Server, usar `DB::raw('GETDATE()')` en inserciones directas (seeders, tests). Los modelos Eloquent con `$dateFormat` funcionan correctamente.

### 4. Conexión

| Aspecto | MySQL | SQL Server |
|---------|-------|------------|
| Túnel SSH | Común para servidores remotos | No aplica; conexión directa |
| Formato host | `127.0.0.1:3306` | `SERVIDOR\INSTANCIA,puerto` o `host,puerto` |
| Certificado | No aplica | `DB_TRUST_SERVER_CERTIFICATE=true` en muchos entornos |

---

## Proceso de Migración

### Paso 1: Preparación

1. **Backup de datos MySQL (si aplica)**
   - Exportar datos actuales si se necesita migrar datos existentes
   - Documentar estructura actual

2. **Verificar SQL Server accesible**
   - Probar conexión con SSMS o cliente similar
   - Verificar que la base de datos existe o puede crearse
   - Verificar credenciales y permisos

3. **Revisar documentación**
   - `docs/migracion-mssql-a-mysql.md` (migración inversa)
   - `.cursor/rules/20-mssql-server-datetime-format.md`

### Paso 2: Configuración

1. **Actualizar `.env`**
   - Cambiar `DB_CONNECTION=sqlsrv`
   - Actualizar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - Agregar `DB_TRUST_SERVER_CERTIFICATE=true`

2. **Verificar `config/database.php`**
   - La configuración `sqlsrv` existe
   - Verificar `date_format` y `trust_server_certificate`

### Paso 3: Modificar Seeders y Tests

1. **Seeders:** Reemplazar `now()` por `DB::raw('GETDATE()')` en todos los seeders
2. **Tests:** Reemplazar `now()` por `DB::raw('GETDATE()')` en todos los tests que insertan datos con `DB::table()`

### Paso 4: Ejecutar Migraciones

```bash
cd backend

# Ver estado actual de migraciones
php artisan migrate:status

# Ejecutar migraciones (crear tablas)
php artisan migrate

# O recrear desde cero (solo en desarrollo)
php artisan migrate:fresh
```

### Paso 5: Ejecutar Seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# O ejecutar seeders específicos
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=TipoClienteSeeder
php artisan db:seed --class=TipoTareaSeeder
php artisan db:seed --class=UsuarioSeeder
php artisan db:seed --class=ClienteSeeder
```

### Paso 6: Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# O ejecutar tests específicos
php artisan test --filter=AuthServiceTest
php artisan test --filter=TaskServiceTest
```

### Paso 7: Verificación

1. **Verificar estructura de tablas**
   ```bash
   php artisan tinker
   DB::select("SELECT name FROM sys.tables ORDER BY name");
   ```

2. **Verificar datos de seed**
   ```bash
   php artisan tinker
   DB::table('USERS')->count();
   DB::table('PQ_PARTES_USUARIOS')->count();
   ```

3. **Probar flujo E2E**
   - Login de empleado
   - Crear tarea
   - Ver dashboard
   - Verificar permisos de supervisor

---

## Checklist de Verificación

### Pre-Migración
- [ ] Backup de datos MySQL realizado (si aplica)
- [ ] SQL Server accesible y verificado
- [ ] Documentación revisada

### Configuración
- [ ] `.env` actualizado con credenciales SQL Server
- [ ] `DB_TRUST_SERVER_CERTIFICATE` configurado
- [ ] `config/database.php` verificado

### Código
- [ ] Seeders actualizados (`GETDATE()` en lugar de `now()`)
- [ ] Tests actualizados (`GETDATE()` en lugar de `now()`)
- [ ] Migraciones compatibles (Schema Builder y fix_clientes_user_id_unique_sqlserver)

### Migraciones
- [ ] Todas las migraciones ejecutadas sin errores
- [ ] Estructura de tablas verificada en SQL Server

### Seeders
- [ ] Seeders ejecutados sin errores
- [ ] Datos iniciales verificados

### Tests
- [ ] Suite completa de tests ejecutada
- [ ] Todos los tests pasando

### Documentación
- [ ] `docs/06-operacion/deploy-infraestructura.md` actualizado (si aplica)
- [ ] `docs/migracion-mysql-a-mssql.md` creado (este documento)

### Post-Migración
- [ ] Flujo E2E verificado
- [ ] Performance verificado
- [ ] Logs revisados (sin errores relacionados con base de datos)
- [ ] Funcionalidades críticas probadas

---

## Troubleshooting

### Error: "La conversión del tipo de datos nvarchar en datetime produjo un valor fuera de intervalo"

**Causa:** Formato de fecha incorrecto o uso de `now()` en contextos donde SQL Server espera `GETDATE()`.

**Solución:**
1. Verificar que los seeders y tests usan `DB::raw('GETDATE()')` para timestamps
2. Verificar que los modelos tienen `protected $dateFormat = 'Y-m-d H:i:s'`
3. Verificar `date_format` en `config/database.php` para la conexión `sqlsrv`

### Error: "Could not find driver"

**Causa:** Extensión PHP `pdo_sqlsrv` no instalada.

**Solución:**
1. Instalar Microsoft ODBC Driver for SQL Server
2. Instalar extensión PHP pdo_sqlsrv (compatible con la versión de PHP)
3. Reiniciar el servidor web o PHP-FPM

### Error: "Connection refused" o "No connection could be made"

**Causa:** SQL Server no accesible, firewall, o configuración incorrecta de host/puerto.

**Solución:**
1. Verificar que SQL Server está en ejecución
2. Verificar que el puerto está abierto (1433 por defecto, o el configurado)
3. Verificar formato de host: `SERVIDOR\INSTANCIA,puerto` para instancia nombrada
4. Verificar que el cliente puede alcanzar el servidor (ping, telnet al puerto)

### Error: "Trust Server Certificate"

**Causa:** SQL Server requiere o rechaza certificados SSL.

**Solución:**
1. Agregar `DB_TRUST_SERVER_CERTIFICATE=true` en `.env`
2. O configurar certificados correctamente si el entorno lo exige

### Error: "Table doesn't exist"

**Causa:** Migraciones no ejecutadas o ejecutadas en base de datos incorrecta.

**Solución:**
```bash
php artisan migrate:status  # Ver estado
php artisan migrate          # Ejecutar migraciones pendientes
php artisan migrate:fresh    # Recrear desde cero (solo desarrollo)
```

---

## Referencias

- [Laravel Database Configuration](https://laravel.com/docs/10.x/database)
- [SQL Server Documentation](https://docs.microsoft.com/en-us/sql/sql-server/)
- [Laravel Migrations](https://laravel.com/docs/10.x/migrations)
- [`docs/migracion-mssql-a-mysql.md`](migracion-mssql-a-mysql.md) - Documentación de la migración inversa (MySQL ← MSSQL)
- [`.cursor/rules/20-mssql-server-datetime-format.md`](../.cursor/rules/20-mssql-server-datetime-format.md) - Regla de formato de fechas en SQL Server
- [`docs/06-operacion/deploy-infraestructura.md`](06-operacion/deploy-infraestructura.md) - Documentación de despliegue

---

## Historial de Cambios

| Fecha     | Cambio              | Autor |
|-----------|---------------------|-------|
| 2026-02-22 | Creación del documento (tarea inversa a migracion-mssql-a-mysql) | Sistema |

---

## Notas Adicionales

### Compatibilidad con Laravel

Laravel Schema Builder es compatible con múltiples motores de base de datos. Las migraciones que usan Schema Builder (la mayoría) no requieren cambios al migrar entre motores. Solo las migraciones que usan SQL directo pueden necesitar adaptación.

### Mantenimiento de Compatibilidad

Si se necesita mantener compatibilidad con ambos motores (MySQL y SQL Server), usar:
- `now()` de Laravel para MySQL (compatible con la mayoría de casos)
- `DB::raw('GETDATE()')` para SQL Server en seeders y tests
- Detección de driver cuando sea necesario: `DB::connection()->getDriverName() === 'sqlsrv'`

### Relación con migracion-mssql-a-mysql.md

Este documento es el **inverso** de `docs/migracion-mssql-a-mysql.md`. Si el proyecto está actualmente en MySQL y se desea volver a SQL Server, seguir este documento. Si el proyecto está en SQL Server y se desea migrar a MySQL, seguir el otro documento.
