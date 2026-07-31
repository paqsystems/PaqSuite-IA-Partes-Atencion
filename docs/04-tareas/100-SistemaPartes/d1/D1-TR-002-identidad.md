# Plan de implementación - TR-002

## Alcance entendido

Gate post-login Partes vía SP `pq_sp_partes_identidad_resolver`; enriquecer login/`me` con `resultado.partes`; middleware revalidación; seed asistentes `admin`/`PQ` supervisores; UI perfil avatar solo lectura; i18n `partes.auth.*`. Sin ABM, sin carga, sin tocar Sanctum/forgot.

## Fuentes leídas
- SPEC: `SPEC-002-identidad-funcional-y-acceso.md`
- HU: `HU-002-identidad-funcional-y-acceso.md`
- TR: `TR-002-identidad-funcional-y-acceso.md` (C1 OK)
- Código: `LoginController`, `MeController`, `PostLoginBusinessGate*`, `User`, seeders rol/permiso, envelope auth Feature

## Impacto esperado
### Base de datos
- Migración SP `pq_sp_partes_identidad_resolver`
- Seeder `PqPartesUsuariosDominioSeeder` (admin/PQ → `PQ_PARTES_USUARIOS`, `supervisor=1`)

### Backend
- Reemplazar `NoOpPostLoginBusinessGate` por implementación Partes
- Fallar **antes** de `createToken` (403/`3003`/`partes.auth.*`)
- Middleware `EnsurePartesFunctionalProfile` en `/auth/me` (+ grupo futuro)
- Payload `resultado.partes` (tipoFuncional, ids, esSupervisor, code, nombre, email)

### Frontend
- Consumir `resultado.partes`; perfil avatar RO; i18n claves auth; no romper `firstLogin` → change-password

### Tests
- Feature: sin perfil / inconsistente / asistente / cliente / me revalida; seed admin
- Vitest/E2E mín. perfil si aplica

### Documentación
- OpenAPI auth login/me; nota TR-001 seed diferido cerrado

### DevOps
- migrate SP + seed dominio; smoke login admin

## Orden de trabajo
1. SP resolver + tests SQL
2. Gate + Login/Me + middleware
3. Seed admin/PQ
4. FE perfil + i18n
5. Feature tests + smoke DEMO

## Riesgos
- Gate después de token → AC-01 falla (mitigar: orden LoginController)
- `users.supervisor` legado → no leer
- CI sqlite: SP solo sqlsrv (mismo patrón TR-001)

## Tests a ejecutar
- `php artisan test --filter=PartesIdentidad` (grupo sqlsrv + Feature envelope)
- Login DEMO admin → `resultado.partes.esSupervisor=true`

## Dudas / bloqueos
- Ninguno bloqueante (TR-001 ya desplegado en DEMO)
- Seed **en el mismo PR** que el gate (si no, login `admin` Feature actual rompe)
- AC-04 en sqlsrv: fixtures cuidando triggers; en sqlite sin triggers se puede forzar doble vínculo para probar SP `codigo=2`

## Confirmación de alcance
- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**
- Clases previstas: `PartesPostLoginBusinessGate`, `PartesIdentidadRepository`, `EnsurePartesFunctionalProfile`; seeder `PqPartesUsuariosSeedSeeder`
- Test Must: gate fail ⇒ **sin** fila en `personal_access_tokens`
