# Migración a MySQL - Documentación Completa

## Objetivo

Este documento describe el proceso completo de migración del proyecto de SQL Server a MySQL, incluyendo configuración, cambios realizados, y guía de verificación.

---

## Contexto

### Estado Anterior
- **Motor de Base de Datos:** SQL Server 2019
- **Driver Laravel:** `sqlsrv`
- **Host:** `PAQ-GAUSS\SQLEXPRESS_AXOFT,2544`
- **Base de datos:** `Lidr`

### Estado Actual
- **Motor de Base de Datos:** MySQL 5.7+ (recomendado 8.0+)
- **Driver Laravel:** `mysql`
- **Host:** `127.0.0.1` (túnel SSH local)
- **Puerto:** `3306`
- **Base de datos:** `Lidr_testing`

---

## Configuración del Túnel SSH

### Requisito Previo

El proyecto utiliza un túnel SSH para conectarse al servidor MySQL remoto. **El túnel debe estar activo antes de ejecutar cualquier comando de migración o acceder a la base de datos.**

### Configuración MCP

La configuración del túnel SSH se encuentra en `mcp/mysql-toolbox/tools.yaml`:

```yaml
sources:
  mysql-forge:
    kind: mysql
    host: 127.0.0.1
    port: 3306
    database: Lidr_testing
    user: forge
    password: Go4QQnC3vmPQ8YwlOaw7
```

### Establecer Túnel SSH (Windows)

**Opción 1: PowerShell**
```powershell
# Comando exacto para establecer túnel SSH
ssh -i "C:\Users\PabloQ\pablo-notebook" -L 3306:localhost:3306 forge@18.218.140.170
```

**Nota:** Este comando abrirá una sesión SSH que debe mantenerse abierta. Dejar la ventana de PowerShell abierta mientras trabajas con la base de datos.

**Opción 2: PuTTY (Windows)**
1. Abrir PuTTY
2. En "Session", ingresar:
   - Host Name: `forge@18.218.140.170`
   - Port: `22`
3. Ir a "Connection > SSH > Auth"
4. En "Private key file for authentication", seleccionar: `C:\Users\PabloQ\pablo-notebook`
5. Ir a "Connection > SSH > Tunnels"
6. Agregar:
   - Source port: `3306`
   - Destination: `localhost:3306`
   - Seleccionar "Local"
7. Hacer clic en "Add"
8. Volver a "Session" y guardar la configuración (ej: "MySQL Tunnel")
9. Conectar

**Opción 3: Usando herramienta MCP**
Si el MCP está configurado correctamente, el túnel puede establecerse automáticamente. Verificar la configuración en `mcp/mysql-toolbox/tools.yaml`.

### Verificar Conexión

Antes de proceder con la migración, verificar que el túnel está activo y la conexión funciona:

```bash
# Desde el backend
cd backend
php artisan tinker

# En tinker, probar conexión
DB::connection()->getPdo();
# Debe retornar el objeto PDO sin errores
```

O usar un cliente MySQL local conectándose a `127.0.0.1:3306`.

---

## Cambios Realizados

### 1. Configuración de Entorno

**Archivo:** `backend/.env`

**Cambios:**
```env
# Antes (SQL Server)
DB_CONNECTION=sqlsrv
DB_HOST=PAQ-GAUSS\SQLEXPRESS_AXOFT,2544
DB_PORT=
DB_DATABASE=Lidr
DB_USERNAME=Axoft
DB_PASSWORD=Tango2005
DB_TRUST_SERVER_CERTIFICATE=true

# Después (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=_datosempresa
DB_USERNAME=forge
DB_PASSWORD=Go4QQnC3vmPQ8YwlOaw7
```

**Nota:** Eliminada la variable `DB_TRUST_SERVER_CERTIFICATE` (no aplica para MySQL).

### 2. Migraciones Adaptadas

#### 2.1 Migración `personal_access_tokens`

**Archivo:** `backend/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php`

**Cambio:** Reemplazado SQL directo específico de SQL Server por Schema Builder de Laravel (compatible con MySQL).

**Antes:**
```php
DB::statement("
    CREATE TABLE personal_access_tokens (
        id BIGINT IDENTITY(1,1) PRIMARY KEY,
        tokenable_type NVARCHAR(255) NOT NULL,
        ...
    )
");
```

**Después:**
```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
```

#### 2.2 Migración `fix_clientes_user_id_unique_sqlserver`

**Archivo:** `backend/database/migrations/2026_01_31_000001_fix_clientes_user_id_unique_sqlserver.php`

**Cambio:** La migración ya detecta el driver y solo aplica lógica específica para SQL Server. Para MySQL, no requiere ninguna acción adicional ya que MySQL permite múltiples NULL en columnas UNIQUE nullable automáticamente.

**Estado:** Sin cambios necesarios, ya es compatible con ambos motores.

### 3. Seeders Actualizados

**Archivos afectados:**
- `backend/database/seeders/UserSeeder.php`
- `backend/database/seeders/TipoClienteSeeder.php`
- `backend/database/seeders/TipoTareaSeeder.php`
- `backend/database/seeders/UsuarioSeeder.php`
- `backend/database/seeders/ClienteSeeder.php`
- `backend/database/seeders/TestUsersSeeder.php`
- `backend/database/seeders/TestTasksSeeder.php`

**Cambio:** Reemplazado `DB::raw('GETDATE()')` por `now()` de Laravel.

**Antes:**
```php
'created_at' => DB::raw('GETDATE()'),
'updated_at' => DB::raw('GETDATE()'),
```

**Después:**
```php
'created_at' => now(),
'updated_at' => now(),
```

**Razón:** `now()` de Laravel es compatible con múltiples motores de base de datos y maneja automáticamente las diferencias entre MySQL y SQL Server.

### 4. Tests Actualizados

**Archivos afectados:** Todos los archivos de test en `backend/tests/` que usaban `DB::raw('GETDATE()')`.

**Cambio:** Reemplazado `DB::raw('GETDATE()')` por `now()` en todos los tests.

**Archivos actualizados:**
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

### 5. Modelos Eloquent

**Estado:** Sin cambios necesarios. Todos los modelos ya tienen `protected $dateFormat = 'Y-m-d H:i:s'`, que es compatible con MySQL.

**Archivos verificados:**
- `backend/app/Models/User.php`
- `backend/app/Models/Usuario.php`
- `backend/app/Models/Cliente.php`
- `backend/app/Models/RegistroTarea.php`
- `backend/app/Models/TipoCliente.php`
- `backend/app/Models/TipoTarea.php`
- `backend/app/Models/ClienteTipoTarea.php`
- `backend/app/Models/PersonalAccessToken.php`

### 6. Documentación Actualizada

#### 6.1 `docs/06-operacion/deploy-infraestructura.md`
- Actualizado requisitos de SQL Server a MySQL
- Actualizada configuración de conexión
- Actualizado troubleshooting con errores específicos de MySQL
- Agregada nota sobre túnel SSH

#### 6.2 `.cursor/rules/20-mysql-datetime-format.md`
- Creada nueva regla específica para MySQL
- Documentadas diferencias con SQL Server
- Actualizadas mejores prácticas para MySQL

---

## Diferencias Clave: SQL Server vs MySQL

### 1. Índices Únicos con NULLs

| Motor | Comportamiento |
|-------|---------------|
| SQL Server | Trata múltiples NULL como duplicados en columnas UNIQUE. Requiere índices filtrados para permitir múltiples NULL. |
| MySQL | Permite múltiples NULL automáticamente en columnas UNIQUE nullable. No requiere lógica especial. |

**Impacto:** La migración `fix_clientes_user_id_unique_sqlserver` solo aplica cambios en SQL Server. Para MySQL, el comportamiento ya es correcto.

### 2. Tipos de Datos

| Tipo SQL Server | Equivalente MySQL | Notas |
|-----------------|-------------------|-------|
| `NVARCHAR` | `VARCHAR` con `utf8mb4` | Laravel Schema Builder maneja esto automáticamente |
| `DATETIME2` | `DATETIME` o `TIMESTAMP` | Laravel usa `TIMESTAMP` para `timestamps()` |
| `BIGINT IDENTITY` | `BIGINT AUTO_INCREMENT` | Laravel Schema Builder maneja esto con `id()` |

### 3. Funciones de Fecha

| Función SQL Server | Equivalente MySQL | Laravel |
|-------------------|-------------------|---------|
| `GETDATE()` | `NOW()` | `now()` (recomendado) |

**Recomendación:** Usar `now()` de Laravel en lugar de funciones SQL específicas para mantener compatibilidad entre motores.

### 4. Nombres de Objetos

| Aspecto | SQL Server | MySQL |
|---------|------------|-------|
| Case sensitivity | Depende de collation (generalmente case-insensitive) | Depende del sistema operativo (case-sensitive en Linux, case-insensitive en Windows) |
| Recomendación | Mantener nombres consistentes (mayúsculas según convención) | Mantener nombres consistentes (mayúsculas según convención) |

---

## Proceso de Migración

### Paso 1: Preparación

1. **Establecer túnel SSH**
   ```powershell
   ssh -i "C:\Users\PabloQ\pablo-notebook" -L 3306:localhost:3306 forge@18.218.140.170
   ```
   
   **Importante:** Mantener esta ventana de PowerShell abierta mientras trabajas con la base de datos. El túnel se cerrará si cierras la ventana.

2. **Verificar conexión**
   ```bash
   cd backend
   php artisan tinker
   DB::connection()->getPdo();
   ```

3. **Backup de datos (si aplica)**
   - Exportar datos actuales de SQL Server si se necesita migrar datos existentes
   - Documentar estructura actual

### Paso 2: Configuración

1. **Actualizar `.env`**
   - Cambiar `DB_CONNECTION=mysql`
   - Actualizar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - Eliminar `DB_TRUST_SERVER_CERTIFICATE`

2. **Verificar `config/database.php`**
   - La configuración `mysql` ya existe y está correcta
   - Verificar `charset` y `collation` (`utf8mb4` y `utf8mb4_unicode_ci`)

### Paso 3: Ejecutar Migraciones

```bash
cd backend

# Ver estado actual de migraciones
php artisan migrate:status

# Ejecutar migraciones (crear tablas)
php artisan migrate

# O recrear desde cero (solo en desarrollo)
php artisan migrate:fresh
```

### Paso 4: Ejecutar Seeders

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

### Paso 5: Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# O ejecutar tests específicos
php artisan test --filter=AuthServiceTest
php artisan test --filter=TaskServiceTest
```

### Paso 6: Verificación

1. **Verificar estructura de tablas**
   ```bash
   php artisan tinker
   DB::select('SHOW TABLES');
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
- [ ] Túnel SSH configurado y activo
- [ ] Conexión MySQL verificada
- [ ] Backup de datos realizado (si aplica)
- [ ] Documentación revisada

### Configuración
- [ ] `.env` actualizado con credenciales MySQL
- [ ] `config/database.php` verificado
- [ ] Variables de entorno cargadas correctamente

### Migraciones
- [ ] Migración `personal_access_tokens` adaptada
- [ ] Migración `fix_clientes_user_id_unique_sqlserver` verificada (compatible)
- [ ] Todas las migraciones ejecutadas sin errores
- [ ] Estructura de tablas verificada

### Seeders
- [ ] Todos los seeders actualizados (sin `GETDATE()`)
- [ ] Seeders ejecutados sin errores
- [ ] Datos iniciales verificados

### Tests
- [ ] Todos los tests actualizados (sin `GETDATE()`)
- [ ] Suite completa de tests ejecutada
- [ ] Todos los tests pasando

### Documentación
- [ ] `docs/06-operacion/deploy-infraestructura.md` actualizado
- [ ] `.cursor/rules/20-mysql-datetime-format.md` creado
- [ ] `docs/migracion-mssql-a-mysql.md` creado (este documento)

### Post-Migración
- [ ] Flujo E2E verificado
- [ ] Performance verificado
- [ ] Logs revisados (sin errores relacionados con base de datos)
- [ ] Funcionalidades críticas probadas

---

## Troubleshooting

### Error: "SQLSTATE[HY000] [2002] No connection could be made"

**Causa:** Túnel SSH no está activo o no está configurado correctamente.

**Solución:**
1. Verificar que el túnel SSH está activo
2. Verificar que el puerto local 3306 no está siendo usado por otra aplicación
3. Verificar configuración en `backend/.env` (`DB_HOST=127.0.0.1`, `DB_PORT=3306`)

### Error: "Access denied for user"

**Causa:** Credenciales incorrectas o usuario sin permisos.

**Solución:**
1. Verificar credenciales en `backend/.env`
2. Verificar que el usuario tiene permisos en la base de datos
3. Verificar que la base de datos existe en el servidor MySQL

### Error: "Table doesn't exist"

**Causa:** Migraciones no ejecutadas o ejecutadas incorrectamente.

**Solución:**
```bash
php artisan migrate:status  # Ver estado
php artisan migrate          # Ejecutar migraciones pendientes
php artisan migrate:fresh    # Recrear desde cero (solo desarrollo)
```

### Error: "Unknown column 'created_at'"

**Causa:** Tabla creada sin timestamps o migración incompleta.

**Solución:**
1. Verificar que la migración incluye `$table->timestamps()`
2. Ejecutar `php artisan migrate:fresh` si es desarrollo
3. Verificar estructura de tabla: `DESCRIBE nombre_tabla;`

### Error: "Incorrect datetime value"

**Causa:** Formato de fecha incorrecto o problema con zona horaria.

**Solución:**
1. Verificar que los modelos tienen `protected $dateFormat = 'Y-m-d H:i:s'`
2. Verificar que se usa `now()` en lugar de funciones SQL específicas
3. Verificar configuración de zona horaria en `config/app.php`

---

## Referencias

- [Laravel Database Configuration](https://laravel.com/docs/10.x/database)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Laravel Migrations](https://laravel.com/docs/10.x/migrations)
- [`.cursor/rules/20-mysql-datetime-format.md`](../.cursor/rules/20-mysql-datetime-format.md) - Regla de formato de fechas
- [`docs/06-operacion/deploy-infraestructura.md`](06-operacion/deploy-infraestructura.md) - Documentación de despliegue

---

## Historial de Cambios

| Fecha     | Cambio              | Autor |
|-----------|---------------------|-------|
| 2026-02-11 | Migración inicial de SQL Server a MySQL | Sistema |

---

## Notas Adicionales

### Compatibilidad con Laravel

Laravel Schema Builder es compatible con múltiples motores de base de datos. Las migraciones que usan Schema Builder (la mayoría) no requieren cambios al migrar entre motores. Solo las migraciones que usan SQL directo necesitan adaptación.

### Mantenimiento de Compatibilidad

Si se necesita mantener compatibilidad con ambos motores (SQL Server y MySQL), usar:
- `now()` de Laravel en lugar de funciones SQL específicas
- Schema Builder en lugar de SQL directo
- Detección de driver cuando sea necesario (ej: `DB::connection()->getDriverName()`)

### Próximos Pasos

1. Ejecutar migraciones en entorno de desarrollo
2. Ejecutar suite completa de tests
3. Verificar flujo E2E completo
4. Documentar cualquier problema encontrado
5. Actualizar este documento con lecciones aprendidas
