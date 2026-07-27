# TR-028(MH) – Carga de Tarea Diaria

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-028(MH)-carga-de-tarea-diaria          |
| Épica              | Épica 7: Registro de Tareas                |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Empleado / Empleado Supervisor             |
| Dependencias       | HU-001 (Login Empleado), HU-009 (Clientes), HU-024 (Tipos de Tarea), HU-012 (Asignación de Tipos a Clientes) |
| Última actualización | 2026-01-28 (correcciones: formato fecha DMY, duración hh:mm, restricción empleados) |
| Estado             | 📋 PENDIENTE                               |

---

## 1) HU Refinada

### Título
Carga de Tarea Diaria

### Narrativa
**Como** empleado (o empleado supervisor)  
**Quiero** registrar una tarea realizada indicando fecha, cliente, tipo de tarea, duración y descripción  
**Para** dejar constancia del trabajo efectuado

### Contexto/Objetivo
Los empleados necesitan registrar las tareas que realizan diariamente para poder generar informes de dedicación por cliente/proyecto. Esta funcionalidad es esencial para el MVP ya que permite capturar la información base que luego se utilizará para análisis operativo, comercial y/o facturación. El sistema debe validar que los datos ingresados sean correctos y que el usuario tenga permisos para registrar tareas (ya sea para sí mismo o para otros empleados si es supervisor).

### Suposiciones explícitas
- El usuario ya está autenticado como empleado o supervisor (tiene token válido de Sanctum)
- Las tablas `PQ_PARTES_CLIENTES`, `PQ_PARTES_TIPOS_TAREA`, `PQ_PARTES_CLIENTE_TIPO_TAREA` y `PQ_PARTES_USUARIOS` ya existen y tienen datos
- La tabla `PQ_PARTES_REGISTRO_TAREA` ya existe (creada en migración inicial)
- El frontend puede acceder al endpoint del backend mediante `VITE_API_URL`
- Los selectores de fecha, cliente y tipo de tarea se cargan dinámicamente desde el backend
- Si el usuario es supervisor, puede seleccionar cualquier empleado activo del sistema
- La validación de tramos de 15 minutos se realiza tanto en frontend como en backend

### In Scope
- Formulario de carga de tarea con todos los campos requeridos
- Selector de fecha (con valor por defecto: fecha actual)
- Selector de cliente (solo clientes activos y no inhabilitados)
- Selector de tipo de tarea (genéricos + asignados al cliente seleccionado)
- Campo de duración en minutos (validación de tramos de 15 minutos)
- Checkboxes para "Sin cargo" y "Presencial"
- Campo de observación/descripción (obligatorio, textarea)
- Selector de empleado (solo visible para supervisores, por defecto: usuario actual)
- Validaciones en frontend y backend
- Endpoint POST para crear registro de tarea
- Endpoints GET para obtener listas de clientes, tipos de tarea y empleados
- Mensaje de confirmación al guardar exitosamente
- Redirección a lista de tareas o limpieza del formulario después de guardar
- Tests unitarios, integración y E2E

### Out of Scope
- Edición de tareas existentes (HU-029)
- Eliminación de tareas (HU-030)
- Visualización de lista de tareas (HU-031)
- Filtros avanzados en los selectores
- Búsqueda en los selectores
- Validación de solapamiento de horarios
- Validación de límites de horas por día/cliente
- Carga masiva de tareas
- Importación desde archivos
- Notificaciones al guardar

---

## 2) Criterios de Aceptación (AC)

### Bullets
- **AC-01**: El empleado autenticado puede acceder al formulario de registro de tarea desde el dashboard. El botón "Cargar Tarea" solo es visible para empleados (tipoUsuario === 'usuario'), no para clientes. La ruta `/tareas/nueva` está protegida y redirige al dashboard si un cliente intenta acceder.
- **AC-02**: El formulario muestra el campo "Fecha" como input de texto con formato DD/MM/YYYY, valor por defecto: fecha actual en formato DMY. El campo tiene autoformato que agrega barras (/) automáticamente mientras el usuario escribe.
- **AC-03**: El formulario muestra el campo "Cliente" con selector que solo muestra clientes activos y no inhabilitados
- **AC-04**: El formulario muestra el campo "Tipo de tarea" con selector que muestra tipos genéricos activos + tipos asignados al cliente seleccionado
- **AC-05**: El formulario muestra el campo "Duración" en formato horario (hh:mm) para visualización del usuario, aunque internamente se maneja y guarda en minutos. El campo acepta formato hh:mm (ej: "02:30" = 150 minutos) con validación de tramos de 15 minutos (máximo 24:00 = 1440 minutos)
- **AC-06**: El formulario muestra checkboxes "Sin cargo" y "Presencial" con valor por defecto: false
- **AC-07**: El formulario muestra el campo "Observación/Descripción" (textarea, obligatorio)
- **AC-08**: Si el usuario es supervisor, el formulario muestra selector de "Empleado" con valor por defecto: usuario actual
- **AC-09**: El sistema valida que la fecha no esté vacía y tenga formato DMY válido (DD/MM/YYYY). El componente de fecha es un input de texto que acepta formato DMY del usuario y convierte el valor a YMD (YYYY-MM-DD) antes de enviarlo al API. El backend valida formato YMD (YYYY-MM-DD).
- **AC-10**: El sistema muestra advertencia (no bloquea) si la fecha es futura
- **AC-11**: El sistema valida que el cliente esté seleccionado, exista, esté activo y no inhabilitado
- **AC-12**: El sistema valida que el tipo de tarea esté seleccionado, exista, esté activo, no inhabilitado y sea genérico o esté asignado al cliente
- **AC-13**: El sistema valida que la duración sea mayor a cero, esté en tramos de 15 minutos y no exceda 1440 minutos. El campo acepta formato horario hh:mm (ej: "02:30" = 150 minutos) para facilitar la entrada del usuario, pero internamente se convierte a minutos para almacenamiento y envío al API.
- **AC-14**: El sistema valida que la observación no esté vacía
- **AC-15**: El sistema valida que si el usuario es supervisor y selecciona otro empleado, el empleado exista y esté activo/no inhabilitado
- **AC-16**: Al guardar exitosamente, el sistema crea el registro en `PQ_PARTES_REGISTRO_TAREA` asociado al usuario autenticado (o al seleccionado si es supervisor)
- **AC-17**: Al guardar exitosamente, se muestra mensaje de confirmación y el formulario se limpia o se redirige a la lista de tareas
- **AC-18**: El endpoint POST retorna 201 con los datos del registro creado
- **AC-19**: El endpoint POST retorna 422 con errores de validación si los datos son inválidos
- **AC-20**: El endpoint POST retorna 401 si no hay autenticación
- **AC-21**: El endpoint POST retorna 403 si el usuario intenta asignar tarea a otro empleado sin ser supervisor

### Escenarios Gherkin

```gherkin
Feature: Carga de Tarea Diaria

  Scenario: Empleado registra tarea exitosamente
    Given el empleado "JPEREZ" está autenticado
    And accede al formulario de carga de tarea
    When completa todos los campos requeridos:
      | Campo          | Valor                    |
      | Fecha          | 28/01/2026 (visualización DMY, valor interno YMD: 2026-01-28) |
      | Cliente        | CLI001                   |
      | Tipo de tarea  | DESARROLLO               |
      | Duración       | 02:00 (formato hh:mm, equivalente a 120 minutos) |
      | Observación    | Desarrollo de feature X  |
    And marca "Sin cargo" como false
    And marca "Presencial" como true
    And hace click en "Guardar"
    Then se crea el registro de tarea en la base de datos
    And el registro está asociado al empleado "JPEREZ"
    And se muestra mensaje de confirmación
    And el formulario se limpia o redirige a la lista de tareas

  Scenario: Supervisor registra tarea para otro empleado
    Given el supervisor "MGARCIA" está autenticado
    And accede al formulario de carga de tarea
    When completa los campos requeridos
    And selecciona el empleado "JPEREZ" en el selector de empleado
    And hace click en "Guardar"
    Then se crea el registro de tarea en la base de datos
    And el registro está asociado al empleado "JPEREZ"
    And no está asociado al supervisor "MGARCIA"

  Scenario: Validación de duración en tramos de 15 minutos
    Given el empleado está autenticado
    And accede al formulario de carga de tarea
    When ingresa una duración de "00:25" (formato hh:mm, equivalente a 25 minutos)
    And hace click en "Guardar"
    Then se muestra error de validación
    And el mensaje indica que la duración debe ser múltiplo de 15 minutos

  Scenario: Validación de tipo de tarea asignado al cliente
    Given el empleado está autenticado
    And accede al formulario de carga de tarea
    And selecciona el cliente "CLI001"
    When selecciona un tipo de tarea que NO es genérico
    And el tipo de tarea NO está asignado al cliente "CLI001"
    And hace click en "Guardar"
    Then se muestra error de validación
    And el mensaje indica que el tipo de tarea no está disponible para el cliente seleccionado

  Scenario: Advertencia de fecha futura
    Given el empleado está autenticado
    And accede al formulario de carga de tarea
    When selecciona una fecha futura (mañana)
    Then se muestra una advertencia indicando que la fecha es futura
    And el formulario permite continuar (no bloquea)

  Scenario: Selector de tipos de tarea muestra genéricos y asignados
    Given el empleado está autenticado
    And accede al formulario de carga de tarea
    When selecciona el cliente "CLI001"
    Then el selector de tipos de tarea muestra:
      | Tipo de Tarea | Es Genérico | Asignado a CLI001 |
      | DESARROLLO     | Sí          | Sí                |
      | SOPORTE        | Sí          | Sí                |
      | ESPECIAL       | No          | Sí                |
    And NO muestra tipos de tarea que:
      | Tipo de Tarea | Es Genérico | Asignado a CLI001 |
      | OTRO_CLIENTE  | No          | No                |

  Scenario: Empleado no supervisor intenta asignar tarea a otro empleado
    Given el empleado "JPEREZ" está autenticado
    And "JPEREZ" NO es supervisor
    When intenta acceder al selector de empleado
    Then el selector de empleado NO está visible
    And al guardar, la tarea se asocia automáticamente a "JPEREZ"
```

---

## 3) Reglas de Negocio

1. **Permisos por rol:**
   - **Empleado**: Solo puede registrar tareas para sí mismo. No puede ver ni usar el selector de empleado. El botón "Cargar Tarea" en el dashboard solo es visible para empleados (tipoUsuario === 'usuario').
   - **Empleado Supervisor**: Puede registrar tareas para sí mismo o para cualquier otro empleado activo y no inhabilitado. El selector de empleado es visible y tiene valor por defecto: usuario actual.
   - **Cliente**: No puede acceder al formulario de carga de tarea. El botón "Cargar Tarea" no se muestra en el dashboard. Si intenta acceder directamente a la ruta `/tareas/nueva`, es redirigido al dashboard.

2. **Validación de fecha:**
   - La fecha es obligatoria y no puede estar vacía.
   - **Formato Interno (Frontend/Backend/BD):** YMD (YYYY-MM-DD). Todo el sistema maneja fechas internamente en formato YMD (YYYY-MM-DD).
   - **Formato Visualización (Frontend):** DMY (DD/MM/YYYY). El componente de fecha es un input de texto que muestra y acepta formato DD/MM/YYYY al usuario, pero internamente mantiene el valor en YMD.
   - **Formato Base de Datos:** YMD (YYYY-MM-DD). La columna `fecha` en `PQ_PARTES_REGISTRO_TAREA` almacena fechas en formato DATE (YYYY-MM-DD).
   - **Implementación:** El componente de fecha es un `<input type="text">` con formato DD/MM/YYYY. El campo tiene autoformato que agrega las barras (/) automáticamente mientras el usuario escribe. El valor del estado del componente se convierte de DMY a YMD antes de enviarlo al API. Las llamadas al API siempre usan YMD.
   - Si la fecha es futura, se muestra advertencia pero NO se bloquea el guardado.

3. **Validación de cliente:**
   - El cliente es obligatorio.
   - El cliente debe existir en `PQ_PARTES_CLIENTES`.
   - El cliente debe tener `activo = true` y `inhabilitado = false`.
   - El selector de clientes solo muestra clientes que cumplan las condiciones anteriores.

4. **Validación de tipo de tarea:**
   - El tipo de tarea es obligatorio.
   - El tipo de tarea debe existir en `PQ_PARTES_TIPOS_TAREA`.
   - El tipo de tarea debe tener `activo = true` y `inhabilitado = false`.
   - El tipo de tarea debe ser:
     - Genérico (`is_generico = true`), O
     - Asignado al cliente seleccionado (existe registro en `PQ_PARTES_CLIENTE_TIPO_TAREA` con `cliente_id` y `tipo_tarea_id` correspondientes).
   - El selector de tipos de tarea se actualiza dinámicamente cuando se selecciona un cliente.

5. **Validación de duración:**
   - La duración es obligatoria y debe ser mayor a cero.
   - **Formato de entrada:** El campo acepta formato horario hh:mm (ej: "02:30" = 150 minutos) para facilitar la entrada del usuario.
   - **Formato interno:** Internamente se convierte a minutos (número entero) para almacenamiento y envío al API.
   - La duración debe ser múltiplo de 15 minutos (15, 30, 45, 60, 75, 90, ..., 1440).
   - La duración no puede exceder 1440 minutos (24 horas = "24:00").
   - La validación se realiza tanto en frontend (UX inmediata) como en backend (seguridad).
   - El campo tiene autoformato que agrega los dos puntos (:) automáticamente mientras el usuario escribe.

6. **Validación de observación:**
   - La observación es obligatoria y no puede estar vacía.
   - La observación es un campo de texto (textarea) sin límite de caracteres específico (limitado por el tipo TEXT de la BD).

7. **Validación de campos booleanos:**
   - `sin_cargo` y `presencial` no pueden ser null.
   - Valores por defecto: `false` para ambos.
   - Si no se marca el checkbox, el valor es `false`.

8. **Validación de empleado (solo para supervisores):**
   - Si el usuario es supervisor y selecciona otro empleado, el empleado debe existir en `PQ_PARTES_USUARIOS`.
   - El empleado seleccionado debe tener `activo = true` y `inhabilitado = false`.
   - Si no se selecciona empleado (o se mantiene el valor por defecto), la tarea se asocia al usuario autenticado.

9. **Asociación de registro:**
   - El campo `usuario_id` en `PQ_PARTES_REGISTRO_TAREA` se asigna según:
     - Si el usuario es supervisor y seleccionó otro empleado: `usuario_id` del empleado seleccionado.
     - En cualquier otro caso: `usuario_id` del usuario autenticado.
   - El campo `cliente_id` se asigna al cliente seleccionado.
   - El campo `tipo_tarea_id` se asigna al tipo de tarea seleccionado.
   - El campo `cerrado` se inicializa en `false`.

10. **Timestamps:**
    - `created_at` y `updated_at` se establecen automáticamente al crear el registro.

---

## 4) Impacto en Datos

### Tablas Afectadas

| Tabla | Operación | Descripción |
|-------|-----------|-------------|
| `PQ_PARTES_REGISTRO_TAREA` | INSERT | Crear nuevo registro de tarea |
| `PQ_PARTES_CLIENTES` | SELECT | Obtener lista de clientes activos para selector |
| `PQ_PARTES_TIPOS_TAREA` | SELECT | Obtener tipos genéricos y tipos asignados al cliente |
| `PQ_PARTES_CLIENTE_TIPO_TAREA` | SELECT | Verificar asignaciones de tipos a clientes |
| `PQ_PARTES_USUARIOS` | SELECT | Obtener lista de empleados (solo para supervisores) |

### Nuevas Columnas/Índices/Constraints

**No se requieren nuevas columnas, índices o constraints.** La tabla `PQ_PARTES_REGISTRO_TAREA` ya existe con todas las columnas necesarias según la migración inicial.

### Migración + Rollback

**No se requiere migración nueva.** La funcionalidad utiliza la estructura existente de `PQ_PARTES_REGISTRO_TAREA`.

### Seed Mínimo para Tests

Se requiere seed mínimo con datos de prueba:

```php
// En TestDataSeeder o similar:
- Al menos 2 clientes activos (CLI001, CLI002)
- Al menos 1 cliente inactivo o inhabilitado (CLIINACTIVO)
- Al menos 3 tipos de tarea:
  - Tipo genérico activo (DESARROLLO)
  - Tipo genérico activo (SOPORTE)
  - Tipo NO genérico activo (ESPECIAL)
- Al menos 1 asignación ClienteTipoTarea (CLI001 → ESPECIAL)
- Al menos 2 empleados:
  - Empleado normal (JPEREZ)
  - Supervisor (MGARCIA)
```

---

## 5) Contratos de API

### Endpoint: POST /api/v1/tasks

**Descripción:** Crear un nuevo registro de tarea.

**Autenticación:** Requerida (Bearer token de Sanctum)

**Autorización:** 
- Empleado: Solo puede crear tareas para sí mismo (`usuario_id` debe ser el del usuario autenticado o no enviarse)
- Supervisor: Puede crear tareas para cualquier empleado activo

**Request Body:**

```json
{
  "fecha": "2026-01-28",
  "cliente_id": 1,
  "tipo_tarea_id": 2,
  "duracion_minutos": 120,
  "sin_cargo": false,
  "presencial": true,
  "observacion": "Desarrollo de feature X",
  "usuario_id": null  // Opcional: solo para supervisores, null = usuario actual
}
```

**Nota sobre formato de fecha:**
- El API recibe y retorna fechas en formato **YMD (YYYY-MM-DD)**.
- El componente de fecha en el frontend maneja internamente el valor en formato **YMD (YYYY-MM-DD)**.
- Solo la visualización al usuario se formatea a **DMY (DD/MM/YYYY)** usando funciones de formato o configuración del componente de fecha.
- No se requiere conversión de formato en el código, solo formateo de visualización.

**Response 201 Created:**

```json
{
  "error": 0,
  "respuesta": "Tarea registrada correctamente",
  "resultado": {
    "id": 1,
    "usuario_id": 1,
    "cliente_id": 1,
    "tipo_tarea_id": 2,
    "fecha": "2026-01-28",
    "duracion_minutos": 120,
    "sin_cargo": false,
    "presencial": true,
    "observacion": "Desarrollo de feature X",
    "cerrado": false,
    "created_at": "2026-01-28T10:30:00+00:00",
    "updated_at": "2026-01-28T10:30:00+00:00"
  }
}
```

**Response 400 Bad Request:**

```json
{
  "error": 4000,
  "respuesta": "Datos inválidos",
  "resultado": {}
}
```

**Response 401 Unauthorized:**

```json
{
  "error": 4001,
  "respuesta": "No autenticado",
  "resultado": {}
}
```

**Response 403 Forbidden:**

```json
{
  "error": 4003,
  "respuesta": "No tiene permisos para asignar tareas a otros empleados",
  "resultado": {}
}
```

**Response 422 Unprocessable Entity:**

```json
{
  "error": 4220,
  "respuesta": "Errores de validación",
  "resultado": {
    "errors": {
      "fecha": ["La fecha es obligatoria"],
      "duracion_minutos": ["La duración debe ser múltiplo de 15 minutos"],
      "observacion": ["La observación es obligatoria"]
    }
  }
}
```

**Response 500 Internal Server Error:**

```json
{
  "error": 9999,
  "respuesta": "Error inesperado del servidor",
  "resultado": {}
}
```

### Endpoint: GET /api/v1/tasks/clients

**Descripción:** Obtener lista de clientes activos para el selector.

**Autenticación:** Requerida (Bearer token de Sanctum)

**Autorización:** Cualquier empleado autenticado

**Query Parameters:** Ninguno

**Response 200 OK:**

```json
{
  "error": 0,
  "respuesta": "Clientes obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "code": "CLI001",
      "nombre": "Empresa ABC S.A."
    },
    {
      "id": 2,
      "code": "CLI002",
      "nombre": "Corporación XYZ"
    }
  ]
}
```

### Endpoint: GET /api/v1/tasks/task-types

**Descripción:** Obtener lista de tipos de tarea disponibles para un cliente específico.

**Autenticación:** Requerida (Bearer token de Sanctum)

**Autorización:** Cualquier empleado autenticado

**Query Parameters:**
- `cliente_id` (opcional): Si se proporciona, retorna tipos genéricos + tipos asignados al cliente. Si no se proporciona, retorna solo tipos genéricos.

**Response 200 OK:**

```json
{
  "error": 0,
  "respuesta": "Tipos de tarea obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "code": "DESARROLLO",
      "descripcion": "Desarrollo de software",
      "is_generico": true
    },
    {
      "id": 2,
      "code": "ESPECIAL",
      "descripcion": "Tarea especial para cliente",
      "is_generico": false
    }
  ]
}
```

### Endpoint: GET /api/v1/tasks/employees

**Descripción:** Obtener lista de empleados activos (solo para supervisores).

**Autenticación:** Requerida (Bearer token de Sanctum)

**Autorización:** Solo supervisores

**Query Parameters:** Ninguno

**Response 200 OK:**

```json
{
  "error": 0,
  "respuesta": "Empleados obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "code": "JPEREZ",
      "nombre": "Juan Pérez"
    },
    {
      "id": 2,
      "code": "MGARCIA",
      "nombre": "María García"
    }
  ]
}
```

**Response 403 Forbidden (si no es supervisor):**

```json
{
  "error": 4003,
  "respuesta": "Solo los supervisores pueden acceder a esta información",
  "resultado": {}
}
```

---

## 6) Cambios Frontend

### Pantallas/Componentes Afectados

1. **Nuevo componente: `TaskForm.tsx`**
   - Ubicación: `frontend/src/features/tasks/components/TaskForm.tsx`
   - Responsabilidad: Formulario completo de carga de tarea
   - Estados UI: `idle`, `loading`, `success`, `error`, `validating`
   - **Componente de fecha:** Usa `<input type="text">` con formato DD/MM/YYYY. El campo tiene autoformato que agrega barras (/) automáticamente. Se convierte DMY a YMD antes de enviar al API.
   - **Internacionalización:** Todos los textos visibles al usuario usan la función `t()` de i18n con fallback en español. El componente tiene `lang="es"` y el formulario tiene `noValidate` para evitar mensajes de validación HTML5 en inglés.

2. **Nuevo servicio: `task.service.ts`**
   - Ubicación: `frontend/src/features/tasks/services/task.service.ts`
   - Responsabilidad: Llamadas al API de tareas
   - Funciones: `createTask()`, `getClients()`, `getTaskTypes()`, `getEmployees()`
   - **Manejo de fechas:** Las funciones reciben y envían fechas en formato YMD (YYYY-MM-DD). No se requiere conversión, solo formateo de visualización en el componente.
   - **Internacionalización:** Todos los mensajes de error del servicio usan la función `t()` de i18n con fallback en español.

3. **Nuevo componente: `ClientSelector.tsx`**
   - Ubicación: `frontend/src/features/tasks/components/ClientSelector.tsx`
   - Responsabilidad: Selector de clientes con carga dinámica
   - **Internacionalización:** Todos los textos visibles usan la función `t()` de i18n con fallback en español.

4. **Nuevo componente: `TaskTypeSelector.tsx`**
   - Ubicación: `frontend/src/features/tasks/components/TaskTypeSelector.tsx`
   - Responsabilidad: Selector de tipos de tarea con carga dinámica según cliente seleccionado
   - **Internacionalización:** Todos los textos visibles usan la función `t()` de i18n con fallback en español.

5. **Nuevo componente: `EmployeeSelector.tsx`**
   - Ubicación: `frontend/src/features/tasks/components/EmployeeSelector.tsx`
   - Responsabilidad: Selector de empleados (solo visible para supervisores)
   - **Internacionalización:** Todos los textos visibles usan la función `t()` de i18n con fallback en español.

6. **Modificación: `Dashboard.tsx`**
   - Agregar botón/enlace "Cargar Tarea" que navega a `/tareas/nueva`
   - El botón solo debe ser visible para empleados (tipoUsuario === 'usuario'), no para clientes

7. **Nueva ruta: `/tareas/nueva`**
   - Componente: `TaskForm`
   - Protección: Requiere autenticación y ser empleado (usar `EmployeeRoute` en lugar de `ProtectedRoute`)
   - Si un cliente intenta acceder, es redirigido al dashboard

### Estados UI

- **Loading**: Mientras se cargan los selectores (clientes, tipos de tarea, empleados)
- **Empty**: Si no hay clientes o tipos de tarea disponibles (mensaje informativo)
- **Error**: Si falla la carga de datos o el guardado (mensaje de error con opción de reintentar)
- **Success**: Después de guardar exitosamente (mensaje de confirmación + limpieza o redirección)
- **Validating**: Mientras se validan los datos antes de enviar

### Validaciones en UI

- **Fecha**: 
  - El componente de fecha es un input de texto que acepta formato DMY (DD/MM/YYYY)
  - Autoformato: agrega barras (/) automáticamente mientras el usuario escribe
  - Validar que la fecha tenga formato DMY válido (DD/MM/YYYY)
  - Convertir DMY a YMD antes de enviar al API
  - Validar que la fecha sea válida (día, mes, año correctos)
  - Mostrar advertencia si es futura (no bloquea)
  - El valor enviado al API está en formato YMD (YYYY-MM-DD)
- **Cliente**: Validar que esté seleccionado antes de permitir seleccionar tipo de tarea
- **Tipo de tarea**: Validar que esté seleccionado y sea válido para el cliente
- **Duración**: 
  - El campo acepta formato horario hh:mm (ej: "02:30")
  - Autoformato: agrega dos puntos (:) automáticamente después de 1-2 dígitos
  - Convertir hh:mm a minutos antes de enviar al API
  - Validar que sea múltiplo de 15 minutos, mayor a 0, máximo 1440 minutos (24:00)
- **Observación**: Validar que no esté vacía (trim)
- **Empleado**: Validar que esté seleccionado si es supervisor (opcional, por defecto usuario actual)

### Accesibilidad Mínima

- Labels asociados a todos los campos (`htmlFor` y `id`)
- Roles ARIA apropiados (`role="form"`, `role="alert"` para errores)
- Navegación por teclado funcional
- Mensajes de error asociados a campos con `aria-describedby`
- Indicadores de campos obligatorios con `aria-required="true"`
- Atributo `lang="es"` en el contenedor del formulario para que las validaciones HTML5 muestren mensajes en español
- Atributo `noValidate` en el formulario para evitar mensajes de validación HTML5 predeterminados del navegador

### Internacionalización (i18n)

- **Todos los textos visibles al usuario** deben usar la función `t()` de `frontend/src/shared/i18n` con fallback obligatorio en español.
- **Mensajes de error:** Todos los mensajes de error (validación, conexión, autenticación) usan `t()` con fallback en español.
- **Labels y placeholders:** Todos los labels, placeholders y textos de ayuda usan `t()` con fallback en español.
- **Mensajes de estado:** Mensajes de carga, éxito y error usan `t()` con fallback en español.
- **Estructura de keys:** Usar notación de puntos: `tasks.form.{seccion}.{elemento}` (ej: `tasks.form.fields.fecha.label`, `tasks.form.validation.fecha.required`).

### Selectores de Test (data-testid)

Agregar `data-testid` en elementos clave:

- `task.form.container` - Contenedor del formulario
- `task.form.dateInput` - Input de fecha (texto con formato DD/MM/YYYY)
- `task.form.clientSelect` - Selector de cliente
- `task.form.taskTypeSelect` - Selector de tipo de tarea
- `task.form.durationInput` - Input de duración (texto con formato hh:mm)
- `task.form.sinCargoCheckbox` - Checkbox "Sin cargo"
- `task.form.presencialCheckbox` - Checkbox "Presencial"
- `task.form.observacionTextarea` - Textarea de observación
- `task.form.employeeSelect` - Selector de empleado (solo supervisores)
- `task.form.submitButton` - Botón "Guardar"
- `task.form.cancelButton` - Botón "Cancelar"
- `task.form.errorMessage` - Mensaje de error general
- `task.form.successMessage` - Mensaje de éxito
- `task.form.dateWarning` - Advertencia de fecha futura

---

## 7) Plan de Tareas / Tickets

### T1: DB - Verificar estructura de tabla RegistroTarea
**Tipo:** DB  
**Descripción:** Verificar que la tabla `PQ_PARTES_REGISTRO_TAREA` existe y tiene todas las columnas necesarias según el modelo de datos.  
**DoD:** 
- Tabla existe con columnas: `id`, `usuario_id`, `cliente_id`, `tipo_tarea_id`, `fecha`, `duracion_minutos`, `sin_cargo`, `presencial`, `observacion`, `cerrado`, `created_at`, `updated_at`
- Foreign keys configuradas correctamente
- Índices existentes verificados  
**Dependencias:** Ninguna  
**Estimación:** S

### T2: DB - Crear seeder de datos de prueba para tests
**Tipo:** DB  
**Descripción:** Crear o actualizar seeder con datos mínimos necesarios para tests (clientes, tipos de tarea, asignaciones, empleados).  
**DoD:**
- Seeder `TestTasksSeeder` o similar creado
- Incluye al menos 2 clientes activos, 1 inactivo
- Incluye tipos genéricos y no genéricos
- Incluye asignaciones ClienteTipoTarea
- Incluye empleado normal y supervisor  
**Dependencias:** T1  
**Estimación:** S

### T3: Backend - Crear FormRequest para validación de creación de tarea
**Tipo:** Backend  
**Descripción:** Crear `CreateTaskRequest` con todas las validaciones según reglas de negocio.  
**DoD:**
- Clase `CreateTaskRequest` creada en `app/Http/Requests/Api/V1/CreateTaskRequest.php`
- Validaciones implementadas: fecha (formato YMD YYYY-MM-DD), cliente_id, tipo_tarea_id, duracion_minutos, observacion, sin_cargo, presencial, usuario_id
- Validaciones custom para tramos de 15 minutos, tipo de tarea asignado al cliente, empleado activo
- Validación de formato de fecha: debe ser YMD (YYYY-MM-DD) válido
- Mensajes de error en español  
**Dependencias:** T1  
**Estimación:** M

### T4: Backend - Crear Service para lógica de negocio de tareas
**Tipo:** Backend  
**Descripción:** Crear `TaskService` con método `createTask()` que implementa la lógica de creación de tarea.  
**DoD:**
- Clase `TaskService` creada en `app/Services/TaskService.php`
- Método `createTask()` implementado
- Validaciones de negocio implementadas (cliente activo, tipo de tarea válido, empleado activo si es supervisor)
- Manejo de errores consistente
- Retorna array con datos del registro creado  
**Dependencias:** T3  
**Estimación:** M

### T5: Backend - Crear Controller para endpoints de tareas
**Tipo:** Backend  
**Descripción:** Crear `TaskController` con métodos para crear tarea y obtener listas (clientes, tipos, empleados).  
**DoD:**
- Clase `TaskController` creada en `app/Http/Controllers/Api/V1/TaskController.php`
- Método `store()` para POST /api/v1/tasks
- Método `getClients()` para GET /api/v1/tasks/clients
- Método `getTaskTypes()` para GET /api/v1/tasks/task-types
- Método `getEmployees()` para GET /api/v1/tasks/employees (solo supervisores)
- Manejo de errores consistente con códigos de error definidos
- Respuestas en formato estándar del proyecto  
**Dependencias:** T4  
**Estimación:** M

### T6: Backend - Crear rutas API para tareas
**Tipo:** Backend  
**Descripción:** Agregar rutas en `routes/api.php` para los endpoints de tareas.  
**DoD:**
- Rutas agregadas en grupo `v1` con middleware `auth:sanctum`
- POST `/api/v1/tasks` → `TaskController@store`
- GET `/api/v1/tasks/clients` → `TaskController@getClients`
- GET `/api/v1/tasks/task-types` → `TaskController@getTaskTypes`
- GET `/api/v1/tasks/employees` → `TaskController@getEmployees`
- Rutas con nombres descriptivos  
**Dependencias:** T5  
**Estimación:** S

### T7: Backend - Unit tests para TaskService
**Tipo:** Tests  
**Descripción:** Crear tests unitarios para `TaskService` cubriendo todos los casos de negocio.  
**DoD:**
- Archivo `tests/Unit/Services/TaskServiceTest.php` creado
- Tests para creación exitosa de tarea
- Tests para validaciones de cliente activo
- Tests para validaciones de tipo de tarea
- Tests para validaciones de duración
- Tests para supervisor asignando a otro empleado
- Tests para empleado normal (solo para sí mismo)
- Tests para empleado intentando asignar a otro (debe fallar)
- Cobertura mínima: 80%  
**Dependencias:** T4, T2  
**Estimación:** M

### T8: Backend - Integration tests para TaskController
**Tipo:** Tests  
**Descripción:** Crear tests de integración para los endpoints de tareas.  
**DoD:**
- Archivo `tests/Feature/Api/V1/TaskControllerTest.php` creado
- Tests para POST /api/v1/tasks (éxito, validaciones, permisos)
- Tests para GET /api/v1/tasks/clients
- Tests para GET /api/v1/tasks/task-types (con y sin cliente_id)
- Tests para GET /api/v1/tasks/employees (supervisor y no supervisor)
- Tests para autenticación requerida (401)
- Tests para permisos (403)
- Tests para validaciones (422)
- Usar `Sanctum::actingAs()` para autenticación  
**Dependencias:** T6, T2  
**Estimación:** M

### T9: Frontend - Crear servicio task.service.ts y utilidades de formato de fecha
**Tipo:** Frontend  
**Descripción:** Crear servicio TypeScript para llamadas al API de tareas y funciones helper para formateo de visualización de fechas.  
**DoD:**
- Archivo `frontend/src/features/tasks/services/task.service.ts` creado
- Función `createTask()` implementada (recibe fecha en YMD, envía en YMD)
- Función `getClients()` implementada
- Función `getTaskTypes()` implementada
- Función `getEmployees()` implementada
- Archivo `frontend/src/shared/utils/dateUtils.ts` creado con funciones de formateo:
  - `formatDateDMY(date: Date | string): string` - Formatea Date o YMD a DMY (DD/MM/YYYY) para visualización
  - `formatDateYMD(date: Date | string): string` - Formatea Date a YMD (YYYY-MM-DD) - útil para valores de input
  - `parseDateYMD(dateString: string): Date` - Parsea string YMD a Date
  - `parseDMYtoYMD(dmyString: string): string | null` - Parsea string DMY (DD/MM/YYYY) a YMD (YYYY-MM-DD)
  - `isValidYMD(dateString: string): boolean` - Valida formato YMD
- Archivo `frontend/src/shared/utils/durationUtils.ts` creado con funciones de conversión:
  - `minutesToTime(minutos: number): string` - Convierte minutos a formato hh:mm
  - `timeToMinutes(timeString: string): number | null` - Convierte formato hh:mm a minutos
  - `isValidTimeFormat(timeString: string): boolean` - Valida formato hh:mm
  - `formatMinutesForInput(minutos: number | string): string` - Formatea minutos para mostrar en input
- Manejo de errores consistente
- Tipos TypeScript definidos (interfaces)
- Uso de `getToken()` para autenticación  
**Dependencias:** T6  
**Estimación:** M

### T10: Frontend - Crear componente TaskForm
**Tipo:** Frontend  
**Descripción:** Crear componente principal del formulario de carga de tarea.  
**DoD:**
- Archivo `frontend/src/features/tasks/components/TaskForm.tsx` creado
- Todos los campos del formulario implementados
- **Campo de fecha:** Usa `<input type="text">` con formato DD/MM/YYYY. El campo tiene autoformato que agrega barras (/) automáticamente. El valor del estado se mantiene en formato DMY para visualización y se convierte a YMD antes de enviar al API usando `parseDMYtoYMD()` de `dateUtils.ts`.
- **Campo de duración:** Usa `<input type="text">` con formato hh:mm. El campo tiene autoformato que agrega dos puntos (:) automáticamente. El valor se convierte de hh:mm a minutos antes de enviar al API usando `timeToMinutes()` de `durationUtils.ts`.
- Estados UI manejados (loading, error, success)
- Validaciones en frontend implementadas (fecha válida en formato YMD)
- Integración con `task.service.ts` (envía fecha en YMD directamente)
- Manejo de advertencia de fecha futura
- Selector de empleado condicional (solo supervisores)
- `data-testid` agregados según especificación
- Estilos CSS básicos  
**Dependencias:** T9  
**Estimación:** L

### T11: Frontend - Crear componentes de selectores
**Tipo:** Frontend  
**Descripción:** Crear componentes reutilizables para selectores (cliente, tipo de tarea, empleado).  
**DoD:**
- `ClientSelector.tsx` creado con carga dinámica
- `TaskTypeSelector.tsx` creado con carga dinámica según cliente
- `EmployeeSelector.tsx` creado (solo visible para supervisores)
- Manejo de estados loading/error
- `data-testid` agregados
- Estilos CSS básicos  
**Dependencias:** T9  
**Estimación:** M

### T12: Frontend - Agregar ruta y navegación
**Tipo:** Frontend  
**Descripción:** Agregar ruta `/tareas/nueva` y botón en Dashboard.  
**DoD:**
- Ruta agregada en `App.tsx` con protección `EmployeeRoute` (solo empleados)
- Botón "Cargar Tarea" agregado en `Dashboard.tsx` (solo visible para empleados)
- Componente `EmployeeRoute.tsx` creado para proteger rutas solo para empleados
- Navegación funcional
- Redirección después de guardar exitosamente (a lista o limpieza de formulario)  
**Dependencias:** T10  
**Estimación:** S

### T13: Frontend - E2E Playwright test para flujo completo
**Tipo:** Tests  
**Descripción:** Crear test E2E con Playwright para el flujo completo de carga de tarea.  
**DoD:**
- Archivo `frontend/tests/e2e/task-create.spec.ts` creado
- Test para empleado registrando tarea exitosamente (usando formato DMY en la entrada)
- Test para supervisor registrando tarea para otro empleado
- Test para validaciones de campos obligatorios
- Test para validación de formato de fecha DMY (DD/MM/YYYY)
- Test para validación de duración en tramos de 15 minutos
- Test para advertencia de fecha futura
- Test para selector de tipos de tarea según cliente
- Test para verificar que la fecha se envía al API en formato YMD (verificar en network request)
- Usar `data-testid` para selectores
- Sin waits ciegos (waitForTimeout, sleep)
- Assertions sobre estados visibles
- Screenshots/videos en fallos  
**Dependencias:** T12, T2  
**Estimación:** M

### T14: Docs - Actualizar documentación de API
**Tipo:** Docs  
**Descripción:** Actualizar documentación de API con los nuevos endpoints.  
**DoD:**
- Archivo `docs/backend/tareas.md` creado o actualizado
- Documentación de todos los endpoints
- Ejemplos de request/response
- Códigos de error documentados
- Autenticación/autorización documentada  
**Dependencias:** T6  
**Estimación:** S

---

## 8) Estrategia de Tests (Playwright y otros)

### Unit Tests

**Cobertura objetivo:** Funciones de negocio y servicios

**Tests para `TaskService`:**
- `test_create_task_success`: Crear tarea exitosamente con datos válidos
- `test_create_task_validates_cliente_activo`: Validar que cliente esté activo
- `test_create_task_validates_tipo_tarea_generico`: Validar tipo genérico
- `test_create_task_validates_tipo_tarea_asignado`: Validar tipo asignado al cliente
- `test_create_task_validates_duracion_multiplo_15`: Validar tramos de 15 minutos
- `test_create_task_validates_duracion_maxima`: Validar máximo 1440 minutos
- `test_create_task_supervisor_asigna_otro_empleado`: Supervisor asigna a otro empleado
- `test_create_task_empleado_solo_para_si_mismo`: Empleado solo puede asignar para sí mismo
- `test_create_task_empleado_intenta_asignar_otro_falla`: Empleado no puede asignar a otro

**Tests para validaciones de `CreateTaskRequest`:**
- Tests para cada regla de validación individual
- Tests para combinaciones de validaciones
- `test_validates_fecha_formato_ymd`: Validar que fecha debe estar en formato YMD (YYYY-MM-DD)
- `test_validates_fecha_formato_invalido`: Validar que formato DMY o inválido retorna error

### Integration Tests

**Cobertura objetivo:** Endpoints API completos

**Tests para `TaskController`:**
- `test_store_creates_task_success`: POST /api/v1/tasks retorna 201 con fecha en formato YMD
- `test_store_validates_fecha_formato_ymd`: POST retorna 422 si fecha no está en formato YMD
- `test_store_validates_required_fields`: POST retorna 422 con campos faltantes
- `test_store_validates_duracion_multiplo_15`: POST retorna 422 si duración no es múltiplo de 15
- `test_store_validates_tipo_tarea_asignado_cliente`: POST retorna 422 si tipo no asignado
- `test_store_supervisor_can_assign_to_other`: Supervisor puede asignar a otro empleado
- `test_store_empleado_cannot_assign_to_other`: Empleado no puede asignar a otro (403)
- `test_store_requires_authentication`: POST retorna 401 sin autenticación
- `test_get_clients_returns_active_only`: GET /api/v1/tasks/clients retorna solo activos
- `test_get_task_types_returns_genericos`: GET /api/v1/tasks/task-types sin cliente_id
- `test_get_task_types_returns_genericos_y_asignados`: GET con cliente_id
- `test_get_employees_requires_supervisor`: GET /api/v1/tasks/employees retorna 403 si no es supervisor
- `test_get_employees_returns_active_only`: GET retorna solo empleados activos

### E2E Tests (Playwright)

**Cobertura objetivo:** Flujo completo de usuario

**Tests en `task-create.spec.ts`:**
- `test_empleado_registra_tarea_exitosamente`: Flujo completo de empleado (valor interno YMD, visualización DMY, verificar que se envía YMD al API)
- `test_supervisor_registra_tarea_para_otro_empleado`: Flujo de supervisor
- `test_validaciones_campos_obligatorios`: Validar que campos requeridos muestran error
- `test_formato_visualizacion_fecha_dmy`: Verificar que la fecha se muestra al usuario en formato DMY (DD/MM/YYYY)
- `test_valor_interno_fecha_ymd`: Verificar que el valor interno de la fecha está en formato YMD y se envía correctamente al API (usar `page.waitForResponse` para verificar el request)
- `test_validacion_duracion_tramos_15`: Validar mensaje de error para duración inválida
- `test_advertencia_fecha_futura`: Verificar que advertencia aparece pero no bloquea
- `test_selector_tipos_tarea_segun_cliente`: Verificar que tipos se actualizan al cambiar cliente
- `test_selector_empleado_solo_supervisor`: Verificar que selector solo aparece para supervisores
- `test_mensaje_exito_despues_guardar`: Verificar mensaje de confirmación
- `test_redireccion_o_limpieza_despues_guardar`: Verificar comportamiento post-guardado

**Estrategia de selectores:**
- Usar `data-testid` para todos los elementos interactuables
- No usar selectores CSS/XPath frágiles
- Esperar estados visibles con `expect().toBeVisible()`
- No usar `waitForTimeout` o `sleep`

**Evidencias en fallos:**
- Screenshots automáticos
- Videos de ejecución (si está configurado)
- Traces de Playwright (si está configurado)

---

## 9) Riesgos y Edge Cases

### Concurrencia/Duplicados
- **Riesgo:** Múltiples usuarios creando tareas simultáneamente
- **Mitigación:** La tabla tiene constraints de foreign keys, pero no hay validación de duplicados específica. Si se requiere evitar duplicados exactos, agregar validación en `TaskService`.

### Permisos
- **Riesgo:** Empleado no supervisor intenta asignar tarea a otro empleado manipulando el request
- **Mitigación:** Validar en backend que si `usuario_id` es diferente al autenticado, el usuario debe ser supervisor. Retornar 403 si no cumple.

### Datos Incompletos
- **Riesgo:** Cliente sin tipos de tarea asignados (ni genéricos ni específicos)
- **Mitigación:** El selector de tipos de tarea mostrará lista vacía. Validar en frontend que haya al menos un tipo disponible antes de permitir guardar.

### Estados Intermedios
- **Riesgo:** Usuario selecciona cliente, luego cambia de cliente antes de seleccionar tipo de tarea
- **Mitigación:** Resetear selector de tipo de tarea cuando cambia el cliente seleccionado.

### Performance
- **Riesgo:** Si hay muchos clientes o tipos de tarea, los selectores pueden ser lentos
- **Mitigación:** 
  - Los endpoints ya filtran por activo/inhabilitado
  - Considerar paginación o búsqueda si hay más de 100 registros
  - Para MVP, asumir que no habrá más de 50-100 clientes/tipos

### Validación de Duración
- **Riesgo:** Usuario ingresa duración inválida (ej: 25 minutos)
- **Mitigación:** Validación en frontend (UX inmediata) y backend (seguridad). Mostrar mensaje claro indicando tramos válidos.

### Fecha Futura
- **Riesgo:** Usuario registra tarea con fecha futura por error
- **Mitigación:** Mostrar advertencia visual pero permitir continuar (según regla de negocio). El usuario puede corregir si es error.

### Tipo de Tarea No Disponible
- **Riesgo:** Usuario selecciona tipo de tarea, luego cambia cliente y el tipo ya no está disponible
- **Mitigación:** Resetear tipo de tarea seleccionado cuando cambia el cliente. Mostrar mensaje si el tipo anterior ya no está disponible.

---

## 10) Checklist final (para validar HU terminada)

- [ ] AC cumplidos (todos los 21 AC verificados)
- [ ] Migración verificada (tabla existe con estructura correcta)
- [ ] Seed de datos de prueba creado y funcionando
- [ ] Backend listo:
  - [ ] FormRequest con validaciones
  - [ ] Service con lógica de negocio
  - [ ] Controller con endpoints
  - [ ] Rutas configuradas
  - [ ] Errores consistentes con códigos definidos
- [ ] Frontend listo:
  - [ ] Formulario completo implementado
  - [ ] Selectores dinámicos funcionando
  - [ ] Validaciones en UI
  - [ ] **Formateo de visualización de fechas implementado**
  - [ ] Campo de fecha muestra formato DMY (DD/MM/YYYY) al usuario
  - [ ] Valor interno del componente de fecha está en formato YMD (YYYY-MM-DD)
  - [ ] Fecha se envía al API en formato YMD sin conversión adicional
  - [ ] Estados UI (loading/error/success)
  - [ ] Navegación y rutas
  - [ ] `data-testid` agregados
- [ ] Unit tests ok:
  - [ ] TaskService con cobertura mínima 80%
  - [ ] CreateTaskRequest con tests de validación
- [ ] Integration tests ok:
  - [ ] TaskController con todos los casos cubiertos
  - [ ] Autenticación y permisos verificados
- [ ] ≥1 E2E Playwright ok:
  - [ ] Flujo completo de empleado
  - [ ] Flujo de supervisor
  - [ ] Validaciones verificadas
  - [ ] **Formateo de visualización DMY y valor interno YMD verificados en tests E2E**
  - [ ] Sin waits ciegos
- [ ] Docs actualizados:
  - [ ] Documentación de API creada/actualizada
  - [ ] Ejemplos de uso documentados
- [ ] CI/CD pasa:
  - [ ] Tests ejecutándose en pipeline
  - [ ] Sin errores de linting

---

## Archivos creados/modificados

### Backend
- `backend/app/Http/Requests/Api/V1/CreateTaskRequest.php` (nuevo)
- `backend/app/Services/TaskService.php` (nuevo)
- `backend/app/Http/Controllers/Api/V1/TaskController.php` (nuevo)
- `backend/routes/api.php` (modificado)
- `backend/tests/Unit/Services/TaskServiceTest.php` (nuevo)
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` (nuevo)
- `backend/database/seeders/TestTasksSeeder.php` (nuevo o modificado)

### Frontend
- `frontend/src/features/tasks/services/task.service.ts` (nuevo)
- `frontend/src/features/tasks/services/index.ts` (nuevo)
- `frontend/src/shared/utils/dateUtils.ts` (nuevo) - Utilidades para formateo de visualización de fechas (DMY para usuario, YMD para valores internos)
- `frontend/src/features/tasks/components/TaskForm.tsx` (nuevo)
- `frontend/src/features/tasks/components/TaskForm.css` (nuevo)
- `frontend/src/features/tasks/components/ClientSelector.tsx` (nuevo)
- `frontend/src/features/tasks/components/TaskTypeSelector.tsx` (nuevo)
- `frontend/src/features/tasks/components/EmployeeSelector.tsx` (nuevo)
- `frontend/src/features/tasks/components/index.ts` (nuevo)
- `frontend/src/features/tasks/index.ts` (nuevo)
- `frontend/src/app/App.tsx` (modificado)
- `frontend/src/app/Dashboard.tsx` (modificado)
- `frontend/src/routes/EmployeeRoute.tsx` (nuevo)
- `frontend/src/shared/utils/durationUtils.ts` (nuevo)
- `frontend/tests/e2e/task-create.spec.ts` (nuevo)

### Docs
- `docs/backend/tareas.md` (nuevo o modificado)

---

## Comandos ejecutados

### Backend
```bash
# Ejecutar tests unitarios
php artisan test --filter TaskServiceTest

# Ejecutar tests de integración
php artisan test --filter TaskControllerTest

# Ejecutar todos los tests
php artisan test

# Ejecutar seeder de datos de prueba (si es necesario)
php artisan db:seed --class=TestTasksSeeder
```

### Frontend
```bash
# Ejecutar tests E2E de Playwright
npm run test:e2e -- task-create.spec.ts

# Ejecutar todos los tests E2E
npm run test:e2e
```

---

## Notas y decisiones

### Formato de Fechas (Decisión de Diseño)

**Regla establecida:** 
- **Formato Interno (Todo el sistema):** YMD (YYYY-MM-DD)
- **Formato Visualización (Frontend):** DMY (DD/MM/YYYY) para entrada y visualización del usuario
- **Base de Datos:** Formato YMD (YYYY-MM-DD) almacenado como DATE

**Implementación:**
- Todo el sistema maneja fechas internamente en formato YMD (YYYY-MM-DD).
- El componente de fecha es un `<input type="text">` que acepta formato DMY (DD/MM/YYYY) del usuario.
- El campo tiene autoformato que agrega barras (/) automáticamente mientras el usuario escribe.
- El valor DMY ingresado por el usuario se convierte a YMD antes de enviar al API usando `parseDMYtoYMD()`.
- Las llamadas al servicio y al API siempre usan formato YMD.
- El backend valida y almacena fechas en formato YMD.

**Duración:**
- **Formato Visualización (Frontend):** hh:mm (ej: "02:30")
- **Formato Interno (API/BD):** minutos (número entero)
- El campo acepta formato hh:mm del usuario y se convierte a minutos antes de enviar al API.
- El campo tiene autoformato que agrega dos puntos (:) automáticamente después de 1-2 dígitos.

**Archivos relacionados:**
- `frontend/src/shared/utils/dateUtils.ts` - Funciones helper para formateo de visualización (DMY) y parsing (DMY→YMD, YMD→Date)
- `frontend/src/shared/utils/durationUtils.ts` - Funciones helper para conversión entre formato hh:mm y minutos
- `frontend/src/features/tasks/components/TaskForm.tsx` - Usa input de texto con formato DMY, convierte a YMD antes de enviar
- `frontend/src/features/tasks/services/task.service.ts` - Maneja fechas en formato YMD directamente

**Componente de fecha implementado:**
- `<input type="text">` con formato DD/MM/YYYY
- Autoformato con barras (/) automáticas
- Conversión DMY → YMD antes de enviar al API

**Tests:**
- Los tests E2E deben verificar que la fecha se muestra en formato DMY al usuario pero el valor interno y el request al API están en formato YMD.
- Los tests de integración del backend deben validar que el API recibe fechas en formato YMD válido.

_(Otras notas se completarán durante la implementación)_

---

## Pendientes / follow-ups

### Completado ✅
- [x] Verificación de estructura de tabla RegistroTarea
- [x] Seeder de datos de prueba (TestTasksSeeder)
- [x] FormRequest con validaciones completas
- [x] Service con lógica de negocio
- [x] Controller con todos los endpoints
- [x] Rutas API configuradas
- [x] Unit tests para TaskService (9 tests)
- [x] Integration tests para TaskController (12 tests)
- [x] Servicio frontend (task.service.ts)
- [x] Utilidades de fecha (dateUtils.ts)
- [x] Componente TaskForm completo
- [x] Componentes de selectores (ClientSelector, TaskTypeSelector, EmployeeSelector)
- [x] Rutas y navegación agregadas
- [x] E2E Playwright tests (9 tests)
- [x] Documentación de API (docs/backend/tareas.md)
- [x] Registro en IA log
- [x] Internacionalización (i18n): Todos los textos visibles usan función `t()` con fallback en español

### Correcciones Realizadas (2026-01-28)
- [x] **Formato de fecha DMY**: Cambiado de input type="date" a input type="text" con formato DD/MM/YYYY. El campo tiene autoformato que agrega barras (/) automáticamente. Se convierte DMY a YMD antes de enviar al API usando `parseDMYtoYMD()`.
- [x] **Duración en formato horario**: Cambiado de input type="number" a input type="text" con formato hh:mm. El campo tiene autoformato que agrega dos puntos (:) automáticamente. Se convierte hh:mm a minutos antes de enviar al API usando `timeToMinutes()`.
- [x] **Restricción de acceso solo para empleados**: Implementado `EmployeeRoute` para proteger la ruta `/tareas/nueva`. El botón "Cargar Tarea" en el dashboard solo es visible para empleados (tipoUsuario === 'usuario'). Los clientes son redirigidos al dashboard si intentan acceder.
- [x] **Internacionalización (i18n)**: Todos los textos visibles al usuario (labels, placeholders, mensajes de error, mensajes de estado) ahora usan la función `t()` de i18n con fallback obligatorio en español. El formulario tiene `lang="es"` y `noValidate` para evitar mensajes de validación HTML5 en inglés.

### Pendientes / Mejoras Futuras
- [ ] Optimización de carga de selectores (considerar caché si hay muchos registros)
- [ ] Mejora de UX: mostrar mensaje cuando no hay tipos de tarea disponibles para un cliente
- [ ] Mejora de accesibilidad: agregar más aria-labels y mejor navegación por teclado
- [ ] Tests E2E adicionales: cubrir más casos edge (cliente sin tipos asignados, etc.)
