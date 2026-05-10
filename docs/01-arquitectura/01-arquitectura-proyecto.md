# Arquitectura del Proyecto ERP

## 1. Propósito

Este documento describe la arquitectura técnica del backend del ERP.

Define:

- Modelo por capas
- Separación de responsabilidades
- Modelo multiempresa
- Separación Dictionary DB / Company DB
- Flujo conceptual de autorización

No contiene especificaciones de endpoints ni detalles de implementación concreta.

---

# 2. Modelo Arquitectónico General

El backend adopta una arquitectura por capas con separación estricta de responsabilidades.

Capas principales:

1. API / Controllers
2. Application Services
3. Domain
4. Infrastructure (Repositories)
5. Base de Datos

Cada capa tiene responsabilidades claramente delimitadas.

---

# 3. Separación de Responsabilidades

## 3.1 Controllers

Responsables de:

- Recibir requests HTTP.
- Extraer contexto (usuario autenticado, tenant).
- Validaciones estructurales básicas.
- Delegar a Application Services.
- Devolver respuesta estandarizada.

No deben contener reglas de negocio.

---

## 3.2 Application Services

Responsables de:

- Implementar casos de uso.
- Orquestar operaciones.
- Aplicar reglas funcionales.
- Invocar repositorios mediante interfaces.
- Controlar flujo transaccional si aplica.

No deben conocer detalles de infraestructura.

---

## 3.3 Domain

Responsable de:

- Representar entidades del negocio.
- Contener reglas invariantes del dominio.
- Centralizar comportamientos esenciales.

Debe permanecer desacoplado de infraestructura.

---

## 3.4 Infrastructure (Repositories)

Responsable de:

- Implementar acceso a datos.
- Traducir entidades a estructuras persistentes.
- Ejecutar consultas.

No debe contener lógica de negocio.

---

# 4. Modelo Multiempresa

El ERP opera bajo modelo:

- Multiusuario
- Multiempresa
- Multirroles

El usuario puede pertenecer a múltiples empresas.
Cada request de gestión debe especificar la empresa activa.

---

# 5. Separación de Bases de Datos

## 5.1 Dictionary DB

Contiene:

- Usuarios
- Empresas
- Roles
- Permisos
- Asignaciones
- Configuración global

No contiene datos operativos.

---

## 5.2 Company DB

Contiene:

- Datos comerciales
- Datos contables
- Movimientos
- Transacciones
- Entidades operativas

Cada empresa posee su propia base.

---

# 6. Resolución de Tenant

El tenant se define por:

`X-Company-Id` (header en cada request).

Reglas:

- Debe estar presente en operaciones de gestión.
- Debe validarse contra asignaciones del usuario.
- No se considera confiable sin validación.

---

# 7. Modelo de Autorización

Cada operación debe:

1. Validar autenticación.
2. Validar pertenencia a empresa.
3. Validar permiso específico.

La autorización se evalúa en cada operación relevante.

---

# 8. Testing Arquitectónico

El diseño debe permitir:

- Unit tests en Services y Domain.
- Integration tests sobre API + DB.
- Al menos un test E2E por flujo crítico.

La arquitectura debe facilitar testabilidad.

---

# 9. Principios Rectores

- Separación estricta de responsabilidades.
- Desacople entre capas.
- Seguridad validada en cada request.
- No mezclar configuración con operación.
- Escalabilidad estructural.

## 10. Evolución de la Arquitectura

Este documento define la arquitectura fundacional del ERP.

A medida que el sistema crezca en:

- Número de empresas
- Volumen de datos
- Complejidad de módulos
- Integraciones externas

Se incorporarán nuevas secciones que detallen:

- Estrategia multi-DB avanzada
- Optimización de performance
- Escalabilidad
- Observabilidad
- Gestión de cache
- Manejo de transacciones complejas
