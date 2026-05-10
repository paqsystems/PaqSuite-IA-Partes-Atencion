# Mapa Visual – Arquitectura Backend (ERP)

## Propósito

Permitir comprender la arquitectura backend del ERP en menos de 5 minutos.

Este documento es visual y sintético.
Para explicación detallada, ver `01-arquitectura-proyecto.md`.

---

# 1️⃣ Arquitectura por Capas

```mermaid
flowchart TD
  A[Controller / API] --> B[Application Service]
  B --> C[Domain]
  B --> D[Repository Interface]
  D --> E[Infrastructure Repository]
  E --> F[(DB)]
```  
---

## 2️⃣ Flujo de Request (E2E backend)

```mermaid
sequenceDiagram
  autonumber
  participant FE as Frontend
  participant API as API Controller
  participant TEN as Tenant Resolver
  participant AUTH as Auth/Permissions
  participant SVC as Application Service
  participant REP as Repository
  participant DBD as Dictionary DB
  participant DBC as Company DB

  FE->>API: Request + Authorization + X-Company-Id
  API->>TEN: Resolver empresa (tenant)
  TEN-->>API: Contexto empresa resuelto
  API->>AUTH: Validar usuario + permisos en empresa
  AUTH->>DBD: Consultar asignaciones/roles/permisos
  DBD-->>AUTH: Permisos OK/NO
  AUTH-->>API: Autorizado / Rechazado

  alt Autorizado
    API->>SVC: Ejecutar caso de uso
    SVC->>REP: Operación de datos
    REP->>DBC: Persistir/leer datos empresa
    DBC-->>REP: Resultado
    REP-->>SVC: Resultado
    SVC-->>API: Resultado (DTO/response)
    API-->>FE: Response OK
  else Rechazado
    API-->>FE: Response 401/403
  end
```
## 3️⃣ Separación de Bases

```mermaid
flowchart LR
  A[Dictionary DB] -->|Identidad| B[Usuarios]
  A --> C[Roles]
  A --> D[Permisos]
  A --> E[Empresas]

  F[Company DB] --> G[Clientes]
  F --> H[Ventas]
  F --> I[Compras]
  F --> J[Stock]
```

## 4️⃣ Testing por Nivel

```mermaid
flowchart TD
  A[Unit Tests] --> B[Services / Domain]
  C[Integration Tests] --> D[API + DB]
  E[E2E Tests] --> F[Flujo Completo]
```  
