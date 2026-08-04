# Reglas de Negocio - Sistema de Registro de Tareas

Este documento consolida todas las reglas de negocio del sistema, organizadas por entidad y funcionalidad.

---

## 1. Autenticación y Login

### 1.1 Validaciones de Login
- **Validaciones estándar:** Todas las validaciones estándar de un login (código no vacío, contraseña no vacía, formato válido)
- **Usuario activo:** El usuario debe estar activo (`activo = true`)
- **Usuario no inhabilitado:** El usuario no debe estar inhabilitado (`inhabilitado = false`)
- **Código asociado:** El usuario debe tener asociado un código de cliente o empleado (según el tipo de usuario)

**Implementación:**
- Verificar en tabla `PQ_PARTES_usuario` o `PQ_PARTES_cliente` según el tipo de autenticación
- Validar `activo = true` e `inhabilitado = false`
- Validar que `code` no sea NULL
- Resolver primero la identidad autenticable en `users` y luego el perfil funcional del modulo

### 1.2 Modelo conceptual de identidad y acceso

El modulo utiliza dos niveles de identidad:

1. **`users`** como identidad autenticable del framework comun.
2. **Entidades de negocio del modulo** vinculadas a esa identidad:
   - `PQ_PARTES_USUARIOS` para empleados;
   - `PQ_PARTES_CLIENTES` para clientes con acceso.

Reglas conceptuales:

- un empleado operativo del modulo debe estar representado en `PQ_PARTES_USUARIOS` y vinculado a un `user_id`;
- un cliente con acceso debe tener su `user_id` asociado en `PQ_PARTES_CLIENTES`;
- el login autentica sobre la identidad comun, pero la operatoria del modulo se resuelve segun su vinculacion como empleado o cliente;
- el rol funcional de **supervisor** pertenece al dominio del modulo y se expresa mediante `PQ_PARTES_USUARIOS.supervisor = true`;
- la inhabilitacion de la entidad funcional debe impedir la operatoria aun cuando exista una identidad autenticable subyacente.
- el mismo `code` funcional no deberia quedar representado simultaneamente como empleado y cliente dentro del modulo.

### 1.3 Resultado funcional del login y sesion

- El login del modulo debe determinar si el usuario autenticado opera como empleado o como cliente.
- Si el perfil funcional es empleado, la sesion debe exponer su `usuario_id` y si actua o no como supervisor.
- Si el perfil funcional es cliente, la sesion debe exponer su `cliente_id` y operar siempre sin capacidades de supervisor.
- La sesion debe conservar la informacion minima necesaria para filtrar menu, dashboard, consultas y permisos de negocio durante todo el circuito autenticado.
- Un usuario autenticado sin perfil funcional valido en `PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES` no debe ingresar al circuito operativo del modulo.

### 1.4 Perfil del usuario autenticado

- Todo usuario autenticado del modulo debe poder consultar su propio perfil en modo de solo lectura.
- El perfil muestra identidad operativa suficiente para confirmar como fue reconocido por el sistema: codigo, nombre visible, email si existe, tipo de usuario y condicion de supervisor cuando aplique.
- Un usuario solo puede consultar su propio perfil y no el de terceros dentro del modulo.

---

## 2. Cliente

### 2.1 Campos Obligatorios al Cargar Cliente
- **Código:** Obligatorio (único)
- **Descripción:** Obligatorio (campo `nombre` en el modelo)
- **Tipo de Cliente:** Obligatorio (`tipo_cliente_id` NOT NULL)
- **Inhabilitado:** Atributo booleano (default: false)

### 2.2 Validaciones de Tipo de Cliente
- El tipo de cliente asignado debe estar habilitado:
  - `activo = true`
  - `inhabilitado = false`

### 2.2.1 Cliente con acceso habilitado

- Un cliente puede existir solo como entidad operativa del modulo, sin acceso autenticado.
- Si se habilita acceso al sistema para un cliente, debe existir vinculacion con `users` mediante `user_id`.
- El `code` del cliente con acceso debe mantenerse consistente con la identidad autenticable asociada.
- Un cliente con acceso habilitado participa del login del modulo como `tipo_usuario = cliente`.
- Si se revoca el acceso, la documentacion funcional y la implementacion deben mantener coherencia entre el estado del cliente y su relacion con `users`.

### 2.3 Regla de Tipos de Tarea para Cliente
- **Al cargar un cliente:** Debe existir por lo menos un tipo de tarea genérico (`is_generico = true`) O el cliente debe tener asignado al menos un tipo de tarea específico en la tabla `ClienteTipoTarea`
- **Propósito:** Garantizar que el cliente pueda tener tareas registradas

**Implementación:**
```php
// Al crear/actualizar cliente, verificar:
$tiposGenericos = TipoTarea::where('is_generico', true)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->count();

$tiposAsignados = ClienteTipoTarea::where('cliente_id', $clienteId)->count();

if ($tiposGenericos === 0 && $tiposAsignados === 0) {
    throw new BusinessRuleException('El cliente debe tener al menos un tipo de tarea genérico disponible o un tipo de tarea asignado');
}
```

### 2.4 Asignacion de tipos de tarea a cliente

- La asignacion manual de tipos de tarea a cliente solo corresponde para tipos **no genericos**.
- Los tipos genericos ya estan disponibles para todos los clientes y no requieren alta explicita en la tabla de relacion.
- Solo pueden asignarse tipos existentes, activos y no inhabilitados.
- La asignacion o desasignacion debe reflejarse inmediatamente en la disponibilidad funcional del selector de tipo de tarea.

---

## 3. Empleado

### 3.1 Campos Obligatorios al Cargar Empleado
- **Código:** Obligatorio (único) - Campo `code`
- **Descripción:** Obligatorio - Campo `nombre`
- **Inhabilitado:** Atributo booleano (default: false)

### 3.2 Relacion con usuario autenticable

- Cada empleado del modulo debe vincularse a una identidad comun mediante `user_id`.
- El `code` funcional del empleado debe ser consistente con la identidad con la que opera dentro del sistema.
- La baja o inhabilitacion del empleado debe evaluarse junto con el acceso autenticable para no dejar usuarios con sesion valida pero sin rol funcional operativo.

---

## 4. Tipo de Tarea

### 4.1 Campos Obligatorios
- **Código:** Obligatorio (único) - **NOTA:** Requiere agregar campo `code` al modelo
- **Descripción:** Obligatorio - Campo `descripcion`
- **Inhabilitado:** Atributo booleano (default: false)

### 4.2 Regla de Tipo de Tarea por Defecto
- **Solo uno por defecto:** Solo puede haber un tipo de tarea con el atributo `is_default = true` en todo el sistema
- **Forzar genérico:** Si un tipo de tarea tiene `is_default = true`, debe forzar automáticamente `is_generico = true`
- **Propósito:** Garantizar que siempre haya un tipo de tarea predeterminado y que sea accesible para todos los clientes

**Implementación:**
```php
// Al crear/actualizar tipo de tarea con is_default = true:
if ($request->is_default) {
    // 1. Verificar que no haya otro tipo con is_default = true
    $otroDefault = TipoTarea::where('is_default', true)
        ->where('id', '!=', $id ?? 0)
        ->first();
    
    if ($otroDefault) {
        throw new BusinessRuleException('Solo puede haber un tipo de tarea por defecto');
    }
    
    // 2. Forzar is_generico = true
    $request->merge(['is_generico' => true]);
}
```

---

## 5. Tipo de Cliente

### 5.1 Campos Obligatorios
- **Código:** Obligatorio (único) - **NOTA:** Requiere agregar campo `code` al modelo
- **Descripción:** Obligatorio - Campo `descripcion`
- **Inhabilitado:** Atributo booleano (default: false)

---

## 6. Tarea (RegistroTarea)

### 6.1 Validaciones Obligatorias
Una tarea debe verificar que:

1. **Código de empleado:** Contenga un `usuario_id` válido (obligatorio)
2. **Código de cliente:** Contenga un `cliente_id` válido (obligatorio)
3. **Tipo de tarea:** Contenga un `tipo_tarea_id` válido (obligatorio)
4. **Fecha válida:** La fecha sea una fecha válida en formato YYYY-MM-DD
5. **Fecha futura (advertencia):** Si la fecha es mayor a hoy, presenta un mensaje de **advertencia** (no bloquea la creación)
6. **Duración en tramos de 15 minutos:** La duración debe estar en tramos de 15 minutos (0, 15, 30, 45, 60, 75, 90, ..., 1440), ser mayor a cero y menor/igual a 24 horas (1440 minutos)
7. **Descripción no vacía:** El campo `observacion` no debe estar vacío (obligatorio, no opcional)
8. **Sin cargo no null:** El atributo `sin_cargo` no debe estar null (iniciar por defecto en `false`)
9. **Presencial no null:** El atributo `presencial` no debe estar null (iniciar por defecto en `false`)

### 6.1.1 Reglas de captura en UI

- La fecha debe presentarse con el formato funcional definido para el usuario, aunque en persistencia se almacene como `date`.
- Para la operatoria del modulo se admite como formato de entrada `DD/MM/YYYY`, convirtiendose antes de persistir al formato tecnico correspondiente.
- La duracion debe ser comprensible para el usuario en formato de carga amigable, pero persistirse en minutos.
- Para la operatoria diaria se admite la carga en formato `hh:mm`, convirtiendose a minutos antes de persistir.
- El supervisor, al crear o editar una tarea, puede seleccionar el empleado propietario de la misma.
- Los selectores de cliente, tipo de tarea y empleado no deben ofrecer registros inhabilitados.
- El selector de tipo de tarea debe responder a la regla `genericos + asignados al cliente`.
- Al elegir un cliente, la lista de tipos disponibles debe actualizarse dinamicamente.
- Si cambia el cliente y el tipo previamente elegido deja de ser valido para el nuevo contexto, la seleccion debe limpiarse o revalidarse antes de guardar.
- Si para el cliente elegido no existen tipos disponibles, la UI debe dejarlo claro y no permitir una persistencia inconsistente.
- El estado `cerrado` debe ser reconocible para el usuario tambien en listados y pantallas de edicion.
- Solo empleados y supervisores deben acceder al formulario de carga; un cliente no deberia ver esa accion ni operar la ruta de carga de tareas.
- Tras guardar correctamente, la experiencia debe cerrar el circuito de carga con mensaje de confirmacion y limpieza del formulario o retorno a la lista.

### 6.2 Validación de Atributos Inhabilitados
- **No mostrar inhabilitados:** Que no aparezca para asignar a una tarea ningún atributo con estado `inhabilitado = true`:
  - Cliente (`inhabilitado = false`)
  - Empleado (`inhabilitado = false`)
  - Tipo de tarea (`inhabilitado = false`)

**Implementación en selects:**
```php
// Al listar clientes para select:
$clientes = Cliente::where('activo', true)
    ->where('inhabilitado', false)
    ->get();

// Al listar tipos de tarea para select:
$tiposTarea = TipoTarea::where('activo', true)
    ->where('inhabilitado', false)
    ->get();

// Al listar empleados para select (solo supervisores):
$empleados = Usuario::where('activo', true)
    ->where('inhabilitado', false)
    ->get();
```

### 6.3 Validación de Duración en Tramos de 15 Minutos
- **Regla:** `duracion_minutos % 15 === 0`
- **Rango:** `0 < duracion_minutos <= 1440`
- **Valores válidos:** 15, 30, 45, 60, 75, 90, 105, ..., 1440

**Implementación:**
```php
if ($duracion_minutos % 15 !== 0) {
    throw new ValidationException('La duración debe estar en tramos de 15 minutos', 1210);
}

if ($duracion_minutos <= 0 || $duracion_minutos > 1440) {
    throw new ValidationException('La duración debe ser mayor a cero y menor o igual a 24 horas', 1207);
}
```

### 6.4 Estado "Cerrado" de Tarea
- **Campo requerido:** Agregar campo `cerrado` (boolean, default: false) a la tabla `RegistroTarea`
- **Regla de modificación:** Una tarea no se puede modificar ni eliminar si está en estado "cerrado" (`cerrado = true`)

**Implementación:**
```php
// Al intentar actualizar tarea:
if ($tarea->cerrado) {
    throw new BusinessRuleException('No se puede modificar una tarea cerrada', 2110);
}

// Al intentar eliminar tarea:
if ($tarea->cerrado) {
    throw new BusinessRuleException('No se puede eliminar una tarea cerrada', 2111);
}
```

### 6.5 Semantica de campos funcionales de tarea

- **`sin_cargo`**: marca que la tarea no debe computarse como dedicacion facturable, aunque siga siendo dedicacion operativa.
- **`presencial`**: indica que la ejecucion de la tarea requirio presencia fisica o prestacion presencial.
- **`cerrado`**: indica que la tarea ya no puede modificarse ni eliminarse individualmente dentro del circuito normal.
- **`observacion`**: describe el trabajo realizado y forma parte central del valor funcional del registro.

---

## 7. Proceso Masivo de Tareas

### 7.1 Validación de Supervisor
- **Permiso requerido:** Verificar que el usuario que quiere procesar sea supervisor (`supervisor = true`)
- **Acceso denegado:** Si el usuario no es supervisor, mostrar error 403

### 7.2 Validación de Botón de Procesar
- **Botón deshabilitado:** El botón de procesar NO se debe activar si no hay ningún registro activo seleccionado
- **Validación:** Al menos una tarea debe estar seleccionada para habilitar el botón

**Implementación en frontend:**
```typescript
const canProcess = selectedTasks.length > 0 && selectedTasks.some(task => !task.cerrado);
```

### 7.3 Reglas funcionales del proceso masivo

- El proceso masivo actua sobre un conjunto de tareas seleccionado explicitamente por el supervisor.
- Su objetivo principal es invertir el estado `cerrado` de forma controlada.
- La pantalla debe permitir filtrar por periodo, cliente, empleado y estado `cerrado`.
- Los filtros se aplican en conjunto sobre el universo visible del supervisor.
- Debe mostrarse el total de registros resultante del filtrado antes de procesar.
- La pantalla debe permitir distinguir entre tareas ya cerradas y abiertas antes de procesar.
- Si no hay seleccion valida, el proceso no debe ejecutarse.
- El procesamiento masivo del modulo debe ser atomico: si una tarea del conjunto no puede procesarse, ninguna debe cambiar su estado.
- Tras un procesamiento exitoso, la vista debe reflejar inmediatamente los nuevos estados y la cantidad de registros afectados.

---

## 8. Informes y Consultas

### 8.1 Validación de Período
- **Rango válido:** Verificar que el período sea correcto (`fecha_desde <= fecha_hasta`)
- **Código de error:** 1305

### 8.2 Restricciones por Tipo de Usuario

#### 8.2.1 Usuario Cliente
- **Filtro automático:** Si el usuario es cliente, solo puede ver las tareas que se le realizaron a su `cliente_id` funcional autenticado
- **Filtros ocultos:** No debe aparecer "cliente" ni "tipo de cliente" como posibilidad de filtro (ya está filtrado automáticamente)

#### 8.2.2 Usuario No Supervisor
- **Filtro automático:** Si el usuario no es un empleado supervisor, solo puede ver las tareas que realizó (`usuario_id = usuario_autenticado.usuario_id`)
- **Filtros ocultos:** No debe aparecer la opción "empleado" como posibilidad de filtro (ya está filtrado automáticamente)

### 8.3 Resultado Vacío
- **Mensaje informativo:** Si el resultado de la obtención de datos es vacío, avisar al usuario
- **Ocultar elementos:** No presentar la lista ni habilitar el botón para exportar a Excel

**Implementación:**
```php
if ($tareas->isEmpty()) {
    return response()->json([
        'error' => 0,
        'respuesta' => 'No se encontraron tareas para los filtros seleccionados',
        'resultado' => []
    ]);
}
```

### 8.4 Consultas agrupadas y navegacion analitica

- Las consultas agrupadas deben permitir comprender la dedicacion por cliente, empleado, tipo de tarea y fecha.
- Cuando el diseno lo permita, una vista agrupada debe poder llevar al detalle subyacente sin perder el contexto funcional.
- Un resultado vacio nunca debe dejar habilitada una exportacion sin contenido.
- Las horas o duraciones presentadas en informes deben mantener criterio consistente entre visualizacion y exportacion.
- Las consultas del modulo deben utilizar las capacidades transversales del framework para grillas, pivots, exportacion y layouts, sin redefinir localmente esas reglas comunes.

### 8.4.1 Consulta detallada

- La consulta detallada debe representar cada tarea con el nivel suficiente para analisis operativo.
- Cuando el rol lo permita, la grilla puede mostrar empleado, cliente, fecha, tipo de tarea, duracion u horas, indicadores funcionales y descripcion.
- El total del periodo filtrado debe ser visible para el usuario cuando agregue valor al analisis.
- La unidad de visualizacion debe ser consistente: si el modulo presenta horas decimales en consultas, la exportacion debe respetar ese criterio o dejar clara la conversion aplicada.
- Cuando la consulta ofrezca configuracion de columnas, agrupacion o variantes de presentacion, debe poder convivir con layouts persistentes segun el criterio comun del framework.

### 8.4.2 Exportacion de consultas

- Las consultas del modulo pueden delegar el comportamiento transversal de exportacion al framework comun.
- La exportacion debe respetar exactamente el mismo conjunto de datos visible para el rol autenticado, incluyendo filtros automaticos y manuales.
- El nombre del archivo debe ser descriptivo y vincularse al proceso o al periodo consultado.
- Las consultas agrupadas deben exportar una estructura comprensible para negocio, aunque la implementacion concreta quede delegada a la capacidad comun de grillas o pivots.
- Si una consulta ofrece vista pivot, esa vista debe poder exportarse con la modalidad coherente definida por el framework comun.

### 8.5 Dashboard

- El dashboard representa la puerta de entrada analitica del modulo.
- Debe mostrar indicadores utiles y comprensibles para el rol autenticado.
- El empleado comun ve informacion propia.
- El supervisor ve informacion global o no restringida al usuario actual.
- El cliente ve solo informacion correspondiente a su propia organizacion.
- Si existe actualizacion automatica, su frecuencia debe ser una decision funcional explicita del modulo.
- El periodo por defecto del dashboard debe ser claro para el usuario, por ejemplo el mes actual.
- Como minimo, el dashboard del MVP debe mostrar total de horas del periodo, cantidad de tareas y algun resumen principal acorde al rol autenticado.
- Cuando el rol lo justifique, pueden mostrarse rankings o distribuciones simples, como top clientes o top empleados.
- El dashboard puede ofrecer navegacion hacia consultas relacionadas, siempre sin romper los filtros de rol.
- Los graficos basicos son admisibles si aportan lectura rapida al MVP; quedan fuera de alcance las visualizaciones sofisticadas o de analitica avanzada que agreguen complejidad innecesaria.

### 8.6 Actualizacion del dashboard

- El dashboard puede ofrecer actualizacion automatica sin recargar la pagina completa.
- Si se habilita, el intervalo debe ser configurable.
- Debe existir tambien la posibilidad de refresco manual.
- La interfaz deberia informar el momento de la ultima actualizacion o un indicador equivalente.
- Durante una actualizacion debe quedar claro para el usuario que los datos se estan refrescando.
- Los filtros automaticos por rol se aplican tambien en la actualizacion automatica o manual.

---

## 9. Integridad Referencial

### 9.1 Regla de Eliminación
- **No eliminar si está referenciado:** No se puede eliminar un cliente, empleado, tipo de tarea ni tipo de cliente, si están referenciados en otras tablas

**Implementación:**
```php
// Al intentar eliminar cliente:
$tareasAsociadas = RegistroTarea::where('cliente_id', $clienteId)->count();
if ($tareasAsociadas > 0) {
    throw new BusinessRuleException('No se puede eliminar un cliente que tiene tareas asociadas', 2112);
}

// Al intentar eliminar empleado:
$tareasAsociadas = RegistroTarea::where('usuario_id', $usuarioId)->count();
if ($tareasAsociadas > 0) {
    throw new BusinessRuleException('No se puede eliminar un empleado que tiene tareas asociadas', 2113);
}

// Al intentar eliminar tipo de tarea:
$tareasAsociadas = RegistroTarea::where('tipo_tarea_id', $tipoTareaId)->count();
$clientesAsociados = ClienteTipoTarea::where('tipo_tarea_id', $tipoTareaId)->count();
if ($tareasAsociadas > 0 || $clientesAsociados > 0) {
    throw new BusinessRuleException('No se puede eliminar un tipo de tarea que está en uso', 2114);
}

// Al intentar eliminar tipo de cliente:
$clientesAsociados = Cliente::where('tipo_cliente_id', $tipoClienteId)->count();
if ($clientesAsociados > 0) {
    throw new BusinessRuleException('No se puede eliminar un tipo de cliente que tiene clientes asociados', 2115);
}
```

---

## 10. Resumen de Cambios Requeridos en el Modelo de Datos

### 10.1 Campos a Agregar

1. **RegistroTarea:**
   - `cerrado` (boolean, default: false) - Indica si la tarea está cerrada

2. **TipoTarea:**
   - `code` (string, único, obligatorio) - Código del tipo de tarea

3. **TipoCliente:**
   - `code` (string, único, obligatorio) - Código del tipo de cliente

4. **Cliente:**
   - `code` debe ser obligatorio (cambiar de opcional a NOT NULL)

### 10.2 Campos a Modificar

1. **RegistroTarea:**
   - `observacion` debe ser obligatorio (cambiar de opcional a NOT NULL)

---

## 11. Códigos de Error Adicionales Requeridos

| Código | Descripción | HTTP | Contexto |
|--------|-------------|------|----------|
| 1210 | Duración debe estar en tramos de 15 minutos | 422 | Validación de duración |
| 2110 | No se puede modificar una tarea cerrada | 403 | Edición de tarea |
| 2111 | No se puede eliminar una tarea cerrada | 403 | Eliminación de tarea |
| 2112 | No se puede eliminar un cliente con tareas asociadas | 422 | Eliminación de cliente |
| 2113 | No se puede eliminar un empleado con tareas asociadas | 422 | Eliminación de empleado |
| 2114 | No se puede eliminar un tipo de tarea en uso | 422 | Eliminación de tipo de tarea |
| 2115 | No se puede eliminar un tipo de cliente con clientes asociados | 422 | Eliminación de tipo de cliente |
| 2116 | El cliente debe tener al menos un tipo de tarea disponible | 422 | Creación/actualización de cliente |
| 2117 | Solo puede haber un tipo de tarea por defecto | 422 | Creación/actualización de tipo de tarea |

---

## 12. Referencias

- Modelo de datos: `docs/modelo-datos.md`
- Reglas de validación: `specs/rules/validation-rules.md`
- Códigos de error: `specs/errors/domain-error-codes.md`
- Especificaciones de endpoints: `specs/endpoints/`

---

**Última actualización:** 2025-01-20

