# Mapa Visual – Tenancy y Resolución de Base por Empresa
## Dictionary DB vs Company DB + Modelo de Seguridad

## Propósito

Este documento visualiza de forma integral:

- Modelo de seguridad (Roles → Permisos → Menú → Acciones)
- Tenancy multiempresa
- Separación Dictionary DB / Company DB
- Flujo completo de request
- Validaciones obligatorias
- Selección conceptual de base por empresa

Es un documento visual.
La explicación detallada se encuentra en `01-arquitectura-proyecto.md`.

---

# 1️⃣ Modelo Estructural de Seguridad

```mermaid
flowchart TD
  U[Usuario] --> AE[Asignación Usuario-Empresa]
  AE --> E[Empresa]
  AE --> R[Rol en esa Empresa]
  R --> P[Permisos]
```

# 2️⃣ Modelo Operativo (Permisos como Nodo Central)

```mermaid
flowchart TD
  U[Usuario] --> PE[Permisos Efectivos]
  PE --> EV[Empresas Visibles]
  PE --> ACT[Acciones Permitidas]
  ACT --> OM[Opciones de Menú]
  ACT --> API[Endpoints API]
```

# 3️⃣ Roles → Permisos → Menú → API

```mermaid
flowchart TD
  R[Rol] --> P[Permisos]
  P --> OM[Menú]
  P --> UI[Acciones UI]
  UI --> API[API Endpoint]
  API --> AUTH[Validación Backend]
  AUTH --> OK[Permitido]
  AUTH --> NO[Denegado]
```

# 4️⃣ Vista General Tenancy – Dos Contextos de Datos

```mermaid
flowchart LR
  REQ[Request + Token + X-Company-Id] --> DBD[(Dictionary DB)]
  REQ --> DBC[(Company DB)]
```

* Dictionary DB → identidad, roles, permisos, empresas.
* Company DB → datos operativos de una empresa específica.

# 5️⃣ Flujo Completo de Resolución Tenancy

```mermaid
sequenceDiagram
  autonumber
  participant FE as Frontend
  participant API as API Controller
  participant TEN as Tenant Resolver
  participant SEC as Security Validator
  participant SEL as Company DB Selector
  participant DBD as Dictionary DB
  participant DBC as Company DB

  FE->>API: Request + Token + X-Company-Id
  API->>TEN: Extraer CompanyId
  TEN-->>API: CompanyId
  API->>SEC: Validar acceso usuario→empresa + permiso
  SEC->>DBD: Consultar asignaciones/roles/permisos
  DBD-->>SEC: OK / Denegado

  alt Autorizado
    API->>SEL: Resolver Company DB
    SEL->>DBD: Obtener info de empresa
    SEL-->>API: Contexto DB
    API->>DBC: Operación (CRUD)
    DBC-->>API: Resultado
    API-->>FE: 200 OK
  else Denegado
    API-->>FE: 401 / 403
  end
```

# 6️⃣ Componentes Conceptuales del Backend

```mermaid
flowchart TD
  A[API Controller]
  B[Tenant Resolver]
  C[Security Validator]
  D[Company DB Selector]
  E[(Dictionary DB)]
  F[(Company DB)]

  A --> B
  A --> C
  C --> E
  A --> D
  D --> E
  A --> F
```

# 7️⃣ Validación Obligatoria en Cada Request

```mermaid
flowchart TD
  A[Request] --> B{Token válido?}
  B -->|No| F1[401 Unauthorized]
  B -->|Sí| C{X-Company-Id presente?}
  C -->|No| F2[400 Bad Request]
  C -->|Sí| D{Usuario tiene acceso a la empresa?}
  D -->|No| F3[403 Forbidden]
  D -->|Sí| E{Permiso requerido?}
  E -->|No| F4[403 Forbidden]
  E -->|Sí| OK[Operación Permitida]
```

# 8️⃣ Separación Dictionary DB vs Company DB

```mermaid
flowchart LR
  subgraph Dictionary DB
    U1[Usuarios]
    R1[Roles]
    P1[Permisos]
    E1[Empresas]
    A1[Asignaciones]
  end

  subgraph Company DB
    C1[Clientes]
    V1[Ventas]
    S1[Stock]
    T1[Tesorería]
    M1[Movimientos]
  end
```

# 9️⃣ Principios Arquitectónicos Clave

* El tenant siempre es explícito (X-Company-Id).
* El tenant nunca es confiable sin validación.
* La autorización se valida en Dictionary DB.
* Los datos operativos viven solo en Company DB.
* Nunca se accede a Company DB sin validación previa.
* El menú refleja permisos, pero no concede seguridad.
* La API valida permisos en cada request.
* Una empresa es visible solo si el usuario tiene permisos en ella.

# 🔟 Resumen Integral en 60 Segundos

* Usuario se autentica.
* Request incluye X-Company-Id.
* Se valida usuario + empresa + permisos en Dictionary DB.
* Si autorizado:
    Se selecciona Company DB correspondiente.
    Se ejecuta operación.
* Si no:
    Se rechaza antes de tocar datos operativos.  
