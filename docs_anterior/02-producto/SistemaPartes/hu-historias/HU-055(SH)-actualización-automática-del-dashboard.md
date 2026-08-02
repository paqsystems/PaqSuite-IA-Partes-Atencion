# HU-055 – Actualización automática del dashboard

## Épica
Épica 10: Dashboard


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero que el dashboard se actualice automáticamente para ver información siempre actualizada.

**Criterios de aceptación:**
- El dashboard se actualiza automáticamente cada X minutos (ej: 5 minutos, configurable).
- Se muestra un indicador de última actualización (ej: "Actualizado hace 2 minutos").
- El usuario puede actualizar manualmente con un botón "Actualizar".
- Durante la actualización, se muestra un indicador de carga.
- Los datos se refrescan sin recargar toda la página (AJAX/fetch).
- Los datos actualizados respetan los filtros automáticos según rol:
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Notas de reglas de negocio:**
- La actualización automática es opcional y puede deshabilitarse (según UX).
- El intervalo de actualización debe ser configurable.
- Los filtros automáticos según rol se aplican siempre, incluso en actualizaciones automáticas.

**Dependencias:** HU-051.

---

## Tabla Resumen de Historias de Usuario

| ID | Épica | Rol | Clasificación | Breve Descripción |
|----|-------|-----|---------------|-------------------|
| HU-00 | Infraestructura | Administrador | MUST-HAVE | Generación de la base de datos inicial |
| HU-001 | Autenticación | Empleado / Supervisor | MUST-HAVE | Login de empleado |
| HU-002 | Autenticación | Cliente | SHOULD-HAVE | Login de cliente |
| HU-003 | Autenticación | Todos | MUST-HAVE | Logout |
| HU-004 | Autenticación | Todos | SHOULD-HAVE | Recuperación de contraseña |
| HU-005 | Autenticación | Todos | SHOULD-HAVE | Cambio de contraseña |
| HU-006 | Configuración | Todos | MUST-HAVE | Visualización de perfil |
| HU-007 | Configuración | Todos | SHOULD-HAVE | Edición de perfil |
| HU-008 | Gestión Clientes | Supervisor | MUST-HAVE | Listado de clientes |
| HU-009 | Gestión Clientes | Supervisor | MUST-HAVE | Creación de cliente |
| HU-010 | Gestión Clientes | Supervisor | MUST-HAVE | Edición de cliente |
| HU-011 | Gestión Clientes | Supervisor | MUST-HAVE | Eliminación de cliente |
| HU-012 | Gestión Clientes | Supervisor | MUST-HAVE | Asignación de tipos de tarea a cliente |
| HU-013 | Gestión Clientes | Supervisor | SHOULD-HAVE | Detalle de cliente |
| HU-014 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Listado de tipos de cliente |
| HU-015 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Creación de tipo de cliente |
| HU-016 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Edición de tipo de cliente |
| HU-017 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Eliminación de tipo de cliente |
| HU-018 | Gestión Empleados | Supervisor | MUST-HAVE | Listado de empleados |
| HU-019 | Gestión Empleados | Supervisor | MUST-HAVE | Creación de empleado |
| HU-020 | Gestión Empleados | Supervisor | MUST-HAVE | Edición de empleado |
| HU-021 | Gestión Empleados | Supervisor | MUST-HAVE | Eliminación de empleado |
| HU-022 | Gestión Empleados | Supervisor | SHOULD-HAVE | Detalle de empleado |
| HU-023 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Listado de tipos de tarea |
| HU-024 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Creación de tipo de tarea |
| HU-025 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Edición de tipo de tarea |
| HU-026 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Eliminación de tipo de tarea |
| HU-027 | Gestión Tipos Tarea | Supervisor | SHOULD-HAVE | Detalle de tipo de tarea |
| HU-028 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Carga de tarea diaria |
| HU-029 | Registro Tareas | Empleado | MUST-HAVE | Edición de tarea propia |
| HU-030 | Registro Tareas | Empleado | MUST-HAVE | Eliminación de tarea propia |
| HU-031 | Registro Tareas | Supervisor | MUST-HAVE | Edición de tarea (supervisor) |
| HU-032 | Registro Tareas | Supervisor | MUST-HAVE | Eliminación de tarea (supervisor) |
| HU-033 | Registro Tareas | Empleado | MUST-HAVE | Lista de tareas propias |
| HU-034 | Registro Tareas | Supervisor | MUST-HAVE | Lista de todas las tareas |
| HU-035 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Validación duración tramos 15 min |
| HU-036 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Advertencia fecha futura |
| HU-037 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Filtrado tipos de tarea por cliente |
| HU-038 | Registro Tareas | Supervisor | MUST-HAVE | Selección empleado propietario |
| HU-039 | Proceso Masivo | Supervisor | SHOULD-HAVE | Acceso al proceso masivo de tareas |
| HU-040 | Proceso Masivo | Supervisor | SHOULD-HAVE | Filtrado de tareas para proceso masivo |
| HU-041 | Proceso Masivo | Supervisor | SHOULD-HAVE | Selección múltiple de tareas |
| HU-042 | Proceso Masivo | Supervisor | SHOULD-HAVE | Procesamiento masivo de tareas |
| HU-043 | Proceso Masivo | Supervisor | SHOULD-HAVE | Validación de selección para procesamiento |
| HU-044 | Informes | Todos | MUST-HAVE | Consulta detallada de tareas |
| HU-045 | Informes | Supervisor | SHOULD-HAVE | Consulta agrupada por empleado |
| HU-046 | Informes | Todos | MUST-HAVE | Consulta agrupada por cliente |
| HU-047 | Informes | Supervisor | SHOULD-HAVE | Consulta agrupada por tipo de tarea |
| HU-048 | Informes | Todos | SHOULD-HAVE | Consulta agrupada por fecha |
| HU-049 | Informes | Todos | SHOULD-HAVE | Exportación de consultas a Excel |
| HU-050 | Informes | Todos | MUST-HAVE | Manejo de resultados vacíos en consultas |
| HU-051 | Dashboard | Todos | MUST-HAVE | Dashboard principal |
| HU-052 | Dashboard | Todos | MUST-HAVE | Resumen de dedicación por cliente |
| HU-053 | Dashboard | Supervisor | SHOULD-HAVE | Resumen de dedicación por empleado |
| HU-054 | Dashboard | Todos | SHOULD-HAVE | Gráficos y visualizaciones |
| HU-055 | Dashboard | Todos | SHOULD-HAVE | Actualización automática del dashboard |

---

## Tickets Técnicos Derivados (MUST-HAVE)

### TK-001 – Migraciones y Modelos de Base de Datos

**HU Relacionadas:** HU-00, HU-001, HU-002, HU-008, HU-009, HU-014, HU-015, HU-018, HU-019, HU-023, HU-024, HU-028, HU-039, HU-044, HU-051

**Descripción:**
- Crear migración para tabla `USERS` (sin prefijo PQ_PARTES_) con campos: `id`, `code` (único, obligatorio), `password_hash` (obligatorio), `activo`, `inhabilitado`, `created_at`, `updated_at`.
- Actualizar migración de `PQ_PARTES_USUARIOS`: agregar campo `user_id` (FK → USERS, obligatorio, único), eliminar campo `password_hash`.
- Actualizar migración de `PQ_PARTES_CLIENTES`: agregar campo `user_id` (FK → USERS, opcional, único), eliminar campo `password_hash`.
- Crear/actualizar migraciones para todas las tablas: `PQ_PARTES_TIPO_CLIENTE`, `PQ_PARTES_TIPO_TAREA`, `PQ_PARTES_REGISTRO_TAREA`, `PQ_PARTES_CLIENTE_TIPO_TAREA`.
- Asegurar que todos los campos requeridos estén definidos (incluyendo `code` en TipoTarea y TipoCliente, `cerrado` en RegistroTarea).
- Definir índices: `USERS.code` (UNIQUE), `PQ_PARTES_USUARIOS.user_id` (UNIQUE), `PQ_PARTES_CLIENTES.user_id` (UNIQUE), y otros índices para campos de búsqueda y relaciones.
- Definir foreign keys: `PQ_PARTES_USUARIOS.user_id → USERS.id`, `PQ_PARTES_CLIENTES.user_id → USERS.id`, y otras restricciones de integridad referencial.
- Implementar soft delete o campo `inhabilitado` según diseño.

**Entregables:**
- Archivos de migración Laravel (incluyendo migración de `USERS`).
- Modelo `User` en `backend/app/Models/User.php`.
- Actualizar modelo `Usuario` con relación `belongsTo(User::class)`.
- Actualizar modelo `Cliente` con relación `belongsTo(User::class)` (opcional).
- Modelos Eloquent con relaciones y validaciones básicas.
- Seeders para datos de prueba/demo.

---

### TK-002 – Endpoints de Autenticación

**HU Relacionadas:** HU-001, HU-002, HU-003

**Descripción:**
- Implementar `POST /api/v1/auth/login` unificado para empleados y clientes.
- Validar credenciales contra tabla `USERS` (sin prefijo PQ_PARTES_).
- Después del login exitoso, determinar tipo de usuario (cliente o usuario) buscando `User.code` en `PQ_PARTES_CLIENTES.code` o `PQ_PARTES_USUARIOS.code`.
- Obtener datos del usuario/cliente desde `PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES`.
- Generar token con todos los valores a conservar: `user_id`, `user_code`, `tipo_usuario`, `usuario_id`/`cliente_id`, `es_supervisor`.
- Implementar `POST /api/v1/auth/logout` para cerrar sesión.
- Integración con Laravel Sanctum para tokens.
- Validaciones de credenciales y estado de usuario (tanto en `USERS` como en entidad asociada).
- Manejo de errores con códigos de dominio.
- Implementar middleware para conservar valores de autenticación durante el ciclo del proceso.

**Entregables:**
- Controlador `AuthController`.
- Servicio `AuthService` con método `determineUserType(User $user)`.
- Servicio `AuthService` con método `getUserData(User $user, string $tipoUsuario)`.
- Middleware para extraer y hacer disponibles valores de autenticación del token.
- Requests de validación.
- Tests unitarios y de integración.

---

### TK-003 – Endpoints de Gestión de Clientes

**HU Relacionadas:** HU-008, HU-009, HU-010, HU-011, HU-012, HU-013

**Descripción:**
- `GET /api/v1/clientes` - Listado con filtros y paginación.
- `POST /api/v1/clientes` - Creación de cliente. Si se habilita acceso al sistema, crear también registro en `USERS`. Validar que el `code` no exista en `USERS` (si se habilita acceso).
- `GET /api/v1/clientes/{id}` - Detalle de cliente.
- `PUT /api/v1/clientes/{id}` - Edición de cliente. Si se cambia contraseña, actualizar `password_hash` en `USERS`. Sincronizar estados (`activo`, `inhabilitado`) entre `USERS` y `PQ_PARTES_CLIENTES` (si tiene `user_id`). Permitir habilitar/deshabilitar acceso al sistema.
- `DELETE /api/v1/clientes/{id}` - Eliminación de cliente (con validación de referencias). Si tiene `user_id`, eliminar también registro en `USERS` (o marcar como inhabilitado según diseño).
- `GET /api/v1/clientes/{id}/tipos-tarea` - Listado de tipos de tarea asignados.
- `POST /api/v1/clientes/{id}/tipos-tarea` - Asignación de tipos de tarea.
- `DELETE /api/v1/clientes/{id}/tipos-tarea/{tipo_tarea_id}` - Desasignación de tipos de tarea.
- Validaciones de reglas de negocio (tipos de tarea, integridad referencial).

**Entregables:**
- Controlador `ClienteController`.
- Requests de validación.
- Servicios de negocio para reglas complejas (incluyendo creación/sincronización con `USERS`).
- Tests unitarios y de integración.

---

### TK-004 – Endpoints de Gestión de Tipos de Cliente

**HU Relacionadas:** HU-014, HU-015, HU-016, HU-017

**Descripción:**
- `GET /api/v1/tipos-cliente` - Listado con filtros y paginación.
- `POST /api/v1/tipos-cliente` - Creación de tipo de cliente.
- `GET /api/v1/tipos-cliente/{id}` - Detalle de tipo de cliente.
- `PUT /api/v1/tipos-cliente/{id}` - Edición de tipo de cliente.
- `DELETE /api/v1/tipos-cliente/{id}` - Eliminación (con validación de referencias).

**Entregables:**
- Controlador `TipoClienteController`.
- Requests de validación.
- Tests unitarios y de integración.

---

### TK-005 – Endpoints de Gestión de Empleados

**HU Relacionadas:** HU-018, HU-019, HU-020, HU-021, HU-022

**Descripción:**
- `GET /api/v1/empleados` - Listado con filtros y paginación.
- `POST /api/v1/empleados` - Creación de empleado. Al crear un empleado, crear también registro en `USERS`. Validar que el `code` no exista en `USERS`. Sincronizar estados (`activo`, `inhabilitado`) entre `USERS` y `PQ_PARTES_USUARIOS`.
- `GET /api/v1/empleados/{id}` - Detalle de empleado.
- `PUT /api/v1/empleados/{id}` - Edición de empleado. Si se cambia contraseña, actualizar `password_hash` en `USERS` (no en `PQ_PARTES_USUARIOS`). Sincronizar estados (`activo`, `inhabilitado`) entre `USERS` y `PQ_PARTES_USUARIOS`.
- `DELETE /api/v1/empleados/{id}` - Eliminación (con validación de referencias). Al eliminar empleado, eliminar también registro en `USERS` (o marcar como inhabilitado según diseño).
- Validaciones de permisos (solo supervisores).

**Entregables:**
- Controlador `EmpleadoController` o `UsuarioController`.
- Requests de validación.
- Servicios de negocio para creación/sincronización con `USERS`.
- Middleware de permisos para supervisores.
- Tests unitarios y de integración.

---

### TK-006 – Endpoints de Gestión de Tipos de Tarea

**HU Relacionadas:** HU-023, HU-024, HU-025, HU-026, HU-027

**Descripción:**
- `GET /api/v1/tipos-tarea` - Listado con filtros y paginación.
- `POST /api/v1/tipos-tarea` - Creación de tipo de tarea.
- `GET /api/v1/tipos-tarea/{id}` - Detalle de tipo de tarea.
- `PUT /api/v1/tipos-tarea/{id}` - Edición de tipo de tarea.
- `DELETE /api/v1/tipos-tarea/{id}` - Eliminación (con validación de referencias).
- Validación de regla: solo un tipo por defecto.
- Validación de regla: si es por defecto, forzar genérico.

**Entregables:**
- Controlador `TipoTareaController`.
- Requests de validación.
- Servicios de negocio para reglas complejas.
- Tests unitarios y de integración.

---

### TK-007 – Endpoints de Registro de Tareas

**HU Relacionadas:** HU-028, HU-029, HU-030, HU-031, HU-032, HU-033, HU-034, HU-035, HU-036, HU-037, HU-038

**Descripción:**
- `GET /api/v1/tareas` - Listado con filtros y paginación (según rol: propias o todas).
- `POST /api/v1/tareas` - Creación de tarea.
- `GET /api/v1/tareas/{id}` - Detalle de tarea.
- `PUT /api/v1/tareas/{id}` - Edición de tarea (con validación de permisos y estado cerrado).
- `DELETE /api/v1/tareas/{id}` - Eliminación (con validación de permisos y estado cerrado).
- `GET /api/v1/tareas/tipos-disponibles?cliente_id={id}` - Tipos de tarea disponibles para un cliente.
- Validaciones: duración en tramos de 15 minutos, fecha futura (advertencia), observación obligatoria, tipos de tarea por cliente.
- Validación de permisos: empleado solo sus tareas, supervisor todas.

**Entregables:**
- Controlador `TareaController` o `RegistroTareaController`.
- Requests de validación.
- Servicios de negocio para validaciones complejas.
- Tests unitarios y de integración.

---

### TK-008 – Componentes UI de Autenticación

**HU Relacionadas:** HU-001, HU-002, HU-003, HU-004, HU-005

**Descripción:**
- Componente `LoginForm` para empleados.
- Componente `LoginClienteForm` para clientes (opcional).
- Componente de logout (botón/enlace).
- Componente `RecuperarPasswordForm` (opcional).
- Componente `CambiarPasswordForm` (opcional).
- Integración con i18n y data-testid.
- Manejo de estados (loading, error, success).
- Redirección post-login según rol.

**Entregables:**
- Componentes React en `frontend/src/features/auth/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-009 – Componentes UI de Configuración de Usuario

**HU Relacionadas:** HU-006, HU-007

**Descripción:**
- Componente `PerfilUsuario` para visualización.
- Componente `EditarPerfilForm` (opcional).
- Integración con i18n y data-testid.
- Manejo de estados.

**Entregables:**
- Componentes React en `frontend/src/features/user/`.
- Tests unitarios de componentes.

---

### TK-010 – Componentes UI de Gestión de Clientes

**HU Relacionadas:** HU-008, HU-009, HU-010, HU-011, HU-012, HU-013

**Descripción:**
- Componente `ClientesList` con tabla, filtros y paginación.
- Componente `ClienteForm` (crear/editar).
- Componente `ClienteDetail` (opcional).
- Componente `AsignarTiposTarea` para gestión de tipos asignados.
- Integración con i18n y data-testid.
- Validaciones en frontend (complementarias a backend).
- Manejo de estados y errores.

**Entregables:**
- Componentes React en `frontend/src/features/clientes/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-011 – Componentes UI de Gestión de Tipos de Cliente

**HU Relacionadas:** HU-014, HU-015, HU-016, HU-017

**Descripción:**
- Componente `TiposClienteList` con tabla, filtros y paginación.
- Componente `TipoClienteForm` (crear/editar).
- Integración con i18n y data-testid.
- Validaciones en frontend.

**Entregables:**
- Componentes React en `frontend/src/features/tipos-cliente/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-012 – Componentes UI de Gestión de Empleados

**HU Relacionadas:** HU-018, HU-019, HU-020, HU-021, HU-022

**Descripción:**
- Componente `EmpleadosList` con tabla, filtros y paginación.
- Componente `EmpleadoForm` (crear/editar).
- Componente `EmpleadoDetail` (opcional).
- Integración con i18n y data-testid.
- Validaciones en frontend.

**Entregables:**
- Componentes React en `frontend/src/features/empleados/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-013 – Componentes UI de Gestión de Tipos de Tarea

**HU Relacionadas:** HU-023, HU-024, HU-025, HU-026, HU-027

**Descripción:**
- Componente `TiposTareaList` con tabla, filtros y paginación.
- Componente `TipoTareaForm` (crear/editar) con lógica de "por defecto" y "genérico".
- Componente `TipoTareaDetail` (opcional).
- Integración con i18n y data-testid.
- Validaciones en frontend (especialmente regla de único por defecto).

**Entregables:**
- Componentes React en `frontend/src/features/tipos-tarea/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-014 – Componentes UI de Registro de Tareas

**HU Relacionadas:** HU-028, HU-029, HU-030, HU-031, HU-032, HU-033, HU-034, HU-035, HU-036, HU-037, HU-038

**Descripción:**
- Componente `TareaForm` (crear/editar) con:
  - Selector de fecha con advertencia de fecha futura.
  - Selector de cliente.
  - Selector dinámico de tipos de tarea (filtrado por cliente).
  - Input de duración con validación de tramos de 15 minutos.
  - Checkboxes de sin cargo y presencial.
  - Textarea de observación (obligatorio).
  - Selector de empleado (solo para supervisores).
- Componente `TareasList` con tabla, filtros y paginación (propias o todas según rol).
- Integración con i18n y data-testid.
- Validaciones en frontend (complementarias a backend).
- Manejo de estados y errores.
- Lógica de filtrado dinámico de tipos de tarea por cliente.

**Entregables:**
- Componentes React en `frontend/src/features/tareas/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-015 – Servicios API Frontend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Servicio `AuthService` para autenticación.
- Servicio `ClienteService` para gestión de clientes.
- Servicio `TipoClienteService` para gestión de tipos de cliente.
- Servicio `EmpleadoService` para gestión de empleados.
- Servicio `TipoTareaService` para gestión de tipos de tarea.
- Servicio `TareaService` para gestión de tareas.
- Servicio `BulkTaskProcessService` para proceso masivo.
- Servicio `InformeService` para consultas e informes.
- Servicio `DashboardService` para datos del dashboard.
- Interceptor para agregar token a requests.
- Manejo de errores centralizado.
- Formato de respuesta estándar (envelope).

**Entregables:**
- Servicios en `frontend/src/services/`.
- Configuración de axios/fetch.
- Tests unitarios de servicios.

---

### TK-016 – Middleware y Permisos Backend

**HU Relacionadas:** Todas las HU que requieren autenticación y roles

**Descripción:**
- Middleware de autenticación (Sanctum).
- Middleware para extraer valores de autenticación del token: `tipo_usuario`, `user_code`, `usuario_id`/`cliente_id`, `es_supervisor`.
- Middleware para hacer disponibles estos valores en el request (ej: `request()->user_type`, `request()->user_code`, etc.).
- Validar que el `User` asociado al token esté activo y no inhabilitado en `USERS`.
- Validar que el usuario/cliente asociado esté activo y no inhabilitado en su tabla correspondiente (`PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES`).
- Middleware de permisos para supervisores.
- Middleware de validación de roles.
- Políticas de autorización (Laravel Policies) para tareas (empleado solo sus tareas, supervisor todas).

**Entregables:**
- Middleware en `backend/app/Http/Middleware/`.
- Middleware para conservar valores de autenticación durante el ciclo del proceso.
- Policies en `backend/app/Policies/`.
- Tests de middleware y políticas.

---

### TK-017 – Validaciones y Reglas de Negocio Backend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Form Requests de validación para todos los endpoints.
- Servicios de negocio para reglas complejas:
  - Validación de único tipo por defecto.
  - Validación de tipos de tarea por cliente.
  - Validación de integridad referencial.
  - Validación de duración en tramos de 15 minutos.
  - Validación de fecha futura (advertencia).
- Códigos de error de dominio centralizados.

**Entregables:**
- Form Requests en `backend/app/Http/Requests/`.
- Servicios en `backend/app/Services/`.
- Excepciones de dominio personalizadas.
- Tests unitarios de validaciones y reglas.

---

### TK-018 – Tests Unitarios Backend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Tests unitarios de modelos (validaciones, relaciones).
- Tests unitarios de servicios de negocio.
- Tests unitarios de validaciones (Form Requests).
- Tests unitarios de políticas de autorización.

**Entregables:**
- Tests PHPUnit en `backend/tests/Unit/`.
- Cobertura mínima del 70% en lógica crítica.

---

### TK-019 – Tests de Integración Backend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Tests de integración de endpoints (API).
- Tests de integración de autenticación.
- Tests de integración de permisos y roles.
- Tests de integración de reglas de negocio complejas.
- Uso de base de datos de prueba y factories.

**Entregables:**
- Tests PHPUnit en `backend/tests/Feature/`.
- Factories para modelos.
- Seeders para datos de prueba.

---

### TK-020 – Tests E2E del Flujo Principal

**HU Relacionadas:** HU-001, HU-028, HU-033, HU-044, HU-046, HU-051 (flujo E2E prioritario)

**Estado:** ✅ **Playwright instalado y configurado**

**Descripción:**
- Test E2E con Playwright del flujo: Login → Carga de tarea → Visualización de lista.
- Validación de autenticación, creación de tarea, y listado.
- Uso de data-testid para selectores.
- Validación de mensajes de error y éxito.

**Entregables completados:**
- ✅ Playwright instalado en `frontend/` (versión 1.57.0)
- ✅ Configuración de Playwright en `frontend/playwright.config.ts`
- ✅ Estructura de tests en `frontend/tests/e2e/`
- ✅ Test de ejemplo en `frontend/tests/e2e/example.spec.ts`
- ✅ Documentación en `frontend/tests/e2e/README.md`
- ✅ Reglas de testing en `.cursor/rules/11-playwright-testing-rules.md`
- ✅ Scripts npm configurados (`test:e2e`, `test:e2e:ui`, `test:e2e:headed`)

**Pendiente:**
- ⏳ Implementar tests reales del flujo E2E principal según `specs/flows/e2e-core-flow.md`

---

### TK-021 – Tests Unitarios Frontend

**HU Relacionadas:** Todas las HU de UI

**Descripción:**
- Tests unitarios de componentes React.
- Tests de hooks personalizados.
- Tests de servicios API.
- Tests de utilidades y helpers.

**Entregables:**
- Tests con Jest/Vitest en `frontend/src/__tests__/` o `frontend/tests/`.
- Configuración de testing library.

---

### TK-022 – Configuración CI/CD Básica

**HU Relacionadas:** Todas las HU (infraestructura)

**Descripción:**
- Pipeline de CI para ejecutar tests (unit, integración, E2E).
- Pipeline de CD para deploy en ambiente de staging/producción.
- Configuración de secretos (tokens, credenciales de BD).
- Configuración de variables de entorno.

**Entregables:**
- Archivos de configuración CI/CD (GitHub Actions, GitLab CI, etc.).
- Documentación de despliegue.
- Archivo `.env.example` con placeholders.

---

### TK-023 – Logging y Auditoría Básica

**HU Relacionadas:** Todas las HU (trazabilidad)

**Descripción:**
- Logging de acciones críticas (creación, edición, eliminación de entidades).
- Logging de autenticaciones (login, logout).
- Logging de errores y excepciones.
- Auditoría básica de cambios (opcional: tabla de auditoría).

**Entregables:**
- Configuración de logging en Laravel.
- Middleware de auditoría (opcional).
- Tests de logging.

---

### TK-024 – Seeders y Datos de Demo

**HU Relacionadas:** Todas las HU (datos de prueba)

**Descripción:**
- Seeder de tipos de cliente básicos.
- Seeder de tipos de tarea (incluyendo uno por defecto y genéricos).
- Seeder de clientes de ejemplo.
- Seeder de empleados (incluyendo supervisores).
- Seeder de tareas de ejemplo.
- Comando `php artisan db:seed` para poblar datos de prueba.

**Entregables:**
- Seeders en `backend/database/seeders/`.
- Documentación de uso.

---

### TK-025 – Documentación de API

**HU Relacionadas:** Todas las HU (documentación)

**Descripción:**
- Documentación de endpoints (Swagger/OpenAPI o Markdown).
- Ejemplos de requests y responses.
- Códigos de error documentados.
- Autenticación documentada.

**Entregables:**
- Archivo OpenAPI/Swagger o documentación Markdown en `docs/api/`.
- Ejemplos de uso.

---

### TK-026 – Endpoints de Proceso Masivo de Tareas

**HU Relacionadas:** HU-039, HU-040, HU-041, HU-042, HU-043

**Descripción:**
- `GET /api/v1/tareas/proceso-masivo` - Listado de tareas con filtros para proceso masivo.
- `POST /api/v1/tareas/proceso-masivo` - Procesamiento masivo (cambiar estado cerrado/abierto).
- Validación de permisos (solo supervisores).
- Validación de selección (al menos una tarea).
- Procesamiento atómico (transaccional).

**Entregables:**
- Controlador `BulkTaskProcessController`.
- Requests de validación.
- Servicio de negocio para procesamiento masivo.
- Tests unitarios y de integración.

---

### TK-027 – Componentes UI de Proceso Masivo

**HU Relacionadas:** HU-039, HU-040, HU-041, HU-042, HU-043

**Descripción:**
- Componente `BulkTaskProcessPage` con filtros y tabla.
- Componente `BulkFilters` para filtros complejos.
- Componente `SelectableTaskTable` con checkboxes.
- Componente `ProcessButton` con validación de selección.
- Integración con i18n y data-testid.
- Manejo de estados (loading, error, success).
- Lógica de selección múltiple (seleccionar todos, deseleccionar todos).

**Entregables:**
- Componentes React en `frontend/src/features/proceso-masivo/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-028 – Endpoints de Informes y Consultas

**HU Relacionadas:** HU-044, HU-045, HU-046, HU-047, HU-048, HU-049, HU-050

**Descripción:**
- `GET /api/v1/informes/detalle` - Consulta detallada de tareas con filtros.
- `GET /api/v1/informes/por-empleado` - Consulta agrupada por empleado.
- `GET /api/v1/informes/por-cliente` - Consulta agrupada por cliente.
- `GET /api/v1/informes/por-tipo` - Consulta agrupada por tipo de tarea.
- `GET /api/v1/informes/por-fecha` - Consulta agrupada por fecha.
- `GET /api/v1/informes/exportar` - Exportación a Excel (query params para tipo de consulta y filtros).
- Validación de permisos según rol (filtros automáticos).
- Validación de período (`fecha_desde <= fecha_hasta`).
- Manejo de resultados vacíos.

**Entregables:**
- Controlador `InformeController` o controladores específicos por tipo.
- Requests de validación.
- Servicios de negocio para agrupación y agregación.
- Servicio de exportación a Excel (Laravel Excel o similar).
- Tests unitarios y de integración.

---

### TK-029 – Componentes UI de Informes y Consultas

**HU Relacionadas:** HU-044, HU-045, HU-046, HU-047, HU-048, HU-049, HU-050

**Descripción:**
- Componente `ConsultaDetallePage` para consulta detallada.
- Componente `ConsultaAgrupadaPage` genérico para consultas agrupadas (por empleado, cliente, tipo, fecha).
- Componente `FiltrosConsulta` común para todos los tipos de consulta.
- Componente `TablaResultados` con paginación y ordenamiento.
- Componente `GrupoExpandible` (accordion) para consultas agrupadas.
- Componente `ExportarExcelButton` con validación de resultados.
- Integración con i18n y data-testid.
- Manejo de estados vacíos.
- Lógica de expansión/colapso de grupos.

**Entregables:**
- Componentes React en `frontend/src/features/informes/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-030 – Servicio de Exportación a Excel

**HU Relacionadas:** HU-049

**Descripción:**
- Servicio backend para generar archivos Excel (Laravel Excel/Maatwebsite Excel).
- Formato de archivo XLSX.
- Estructura de datos según tipo de consulta (detallada o agrupada).
- Formato de horas en decimal.
- Formato de fechas correcto.
- Nombre de archivo descriptivo con período/filtros.
- Streaming de archivo para descarga.

**Entregables:**
- Servicio `ExcelExportService`.
- Export classes para cada tipo de consulta.
- Tests unitarios de exportación.

---

### TK-031 – Endpoints de Dashboard

**HU Relacionadas:** HU-051, HU-052, HU-053, HU-054, HU-055

**Descripción:**
- `GET /api/v1/dashboard/resumen` - Resumen ejecutivo del dashboard.
- `GET /api/v1/dashboard/por-cliente` - Resumen por cliente (top N).
- `GET /api/v1/dashboard/por-empleado` - Resumen por empleado (top N, solo supervisor).
- Query parameters: `fecha_desde`, `fecha_hasta`, `limit` (para top N).
- Validación de permisos según rol (filtros automáticos).
- Cálculo de KPIs (totales, promedios).
- Respuesta optimizada para dashboard (datos agregados, no detalle completo).

**Entregables:**
- Controlador `DashboardController`.
- Requests de validación.
- Servicios de negocio para cálculo de KPIs y agregaciones.
- Tests unitarios y de integración.

---

### TK-032 – Componentes UI de Dashboard

**HU Relacionadas:** HU-051, HU-052, HU-053, HU-054, HU-055

**Descripción:**
- Componente `DashboardPage` principal.
- Componente `KPICard` para indicadores clave (total horas, cantidad tareas, promedio).
- Componente `ResumenPorCliente` para lista de top clientes.
- Componente `ResumenPorEmpleado` para lista de top empleados (solo supervisor).
- Componente `SelectorPeriodo` para cambiar período.
- Componente `GraficoDistribucion` para gráficos (Chart.js, Recharts, etc.).
- Integración con i18n y data-testid.
- Actualización automática (polling o WebSockets opcional).
- Botón de actualización manual.
- Indicador de última actualización.

**Entregables:**
- Componentes React en `frontend/src/features/dashboard/`.
- Configuración de librería de gráficos.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-033 – Optimización de Consultas para Informes

**HU Relacionadas:** HU-044, HU-045, HU-046, HU-047, HU-048

**Descripción:**
- Optimización de queries SQL para consultas agrupadas.
- Uso de índices apropiados (`idx_registro_usuario_fecha`, `idx_registro_cliente_fecha`).
- Eager loading de relaciones para evitar N+1 queries.
- Caché de resultados (opcional, según performance).
- Paginación eficiente para grandes volúmenes de datos.
- Query builder optimizado para agregaciones.

**Entregables:**
- Optimización de queries en controladores y servicios.
- Índices en base de datos.
- Tests de performance (opcional).

---

**Última actualización:** 2025-01-20
