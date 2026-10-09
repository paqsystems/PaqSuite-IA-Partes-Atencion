# HU-012 – Readaptar consumo del SDK Framework a Cloudsmith

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-012 |
| Título | Readaptar el uso del SDK (`paqsuite/laravel-core`, `@paqsuite/react-core`) de Satis + Verdaccio a Cloudsmith |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE (ops; no UI de producto) |
| Estado | Finalizado |
| Última actualización | 2026-10-09 |
| SPEC origen | [SPEC-012-adopcion-gen-35-cloudsmith](../../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) |
| Anexo normativo | [adopcion-gen-35-cloudsmith.md](../../06-operacion/adopcion-gen-35-cloudsmith.md) |
| TR relacionada(s) | [TR-012-adopcion-gen-35-cloudsmith](../../04-tareas/100-SistemaPartes/TR-012-adopcion-gen-35-cloudsmith.md) |

---

## Trazabilidad SPEC

| Entregable / criterio SPEC-012 | Dónde en esta HU |
|--------------------------------|------------------|
| Origen install Cloudsmith, no Satis/Verdaccio/Funnel para paquetes | Narrativa; Alcance; R-CS-01/02; CA-01…04 |
| Pins oleada anexo `1.3.13-beta.1` / `2.4.24-beta.1` | Alcance; R-CS-05; CA-01/02 |
| Locks regenerados contra Cloudsmith | CA-01/02 |
| Secreto `CLOUDSMITH_READ_TOKEN`; no write key | Actores; R-CS-01/03; CA-08 |
| `.npmrc` + `vercel-install.sh` | CA-03/04 |
| Forge / Vercel smoke | CA-05/06 |
| Login + health sin Tailscale para paquetes | CA-07 |
| GHA solo si ya hay job de install | Fuera de alcance; CA-09 |
| Docs deploy dejan Satis/Verdaccio como legado | CA-10 |
| Rollback = restaurar commit previo hasta Fase 7 | Alcance; R-CS-07; supuestos |
| Sin UI/SP/menú/Tango/publicación | Fuera de alcance |
| Token faltante → install falla (no fallback silencioso) | R-CS-06; CA-04 |
| Auth Composer efímera (header y/o `auth.json`) | R-CS-01; supuestos |
| Criterios SPEC §5 | CA-01…10 |

---

## Narrativa

Como desarrollador u operador del host Partes  
quiero que `composer install` y `npm ci`/`npm install` resuelvan el SDK Framework desde Cloudsmith  
para dejar de depender de Satis y Verdaccio (Funnel/Tailscale) **solo para paquetes**, sin cambiar pantallas ni reglas de negocio.

---

## Contexto funcional

El producto ya consume `@paqsuite/react-core` y `paqsuite/laravel-core`. El cambio es de **registry y autenticación de install**, no de funcionalidad de usuario.

Hoy: Satis HTTP (`100.110.69.93`) + Verdaccio Funnel (`VERDACCIO_AUTH_TOKEN`).  
Objetivo: HTTPS Cloudsmith `paqsystems/paqsuite-sdk` + token **read** `CLOUDSMITH_READ_TOKEN`.

GEN-35 (publicar paquetes) vive en Framework. Esta HU **adopta el consumo** en el host.

### Precondiciones

- Framework publicó los prereleases de esta oleada en Cloudsmith (SPEC §3).
- Existe un token **read** distinto de `CLOUDSMITH_API_KEY`.

### Actores

| Actor | Responsabilidad |
|-------|-----------------|
| Desarrollador del host | Manifests, locks, token local, nada de secretos en git |
| Operación Forge | Env + auth Composer antes de `composer install` |
| Operación Vercel | Env; install FE sin Tailscale para el SDK |
| GitHub Actions | Solo si **ya** hay job de install: mismo secreto y registry |
| Cloudsmith | Repo SDK; no se publica desde Partes |

---

## Alcance incluido

- Cambiar origen de `laravel-core` y `react-core` a Cloudsmith.
- Pins de oleada: Composer `1.3.13-beta.1`, npm `2.4.24-beta.1` (salvo bump humano antes del merge).
- Regenerar `composer.lock` y `package-lock.json`.
- Cablear `CLOUDSMITH_READ_TOKEN` en Forge, Vercel y local (y GHA **si** hay install).
- Adaptar `.npmrc` y `vercel-install.sh`.
- Documentación ops de **deploy**: Cloudsmith vigente; Satis/Verdaccio legado.
- Procedimiento de rollback: restaurar locks + URLs del commit anterior (hasta Fase 7 Framework).

---

## Fuera de alcance

- Publicar paquetes (Framework).
- Pantallas, SP, menú, i18n, `data-testid` de un “GEN-35 de producto”.
- Corte global Satis/Verdaccio (Fase 7 Framework).
- Repo Tango.
- Crear workflows GitHub Actions nuevos.
- Obligar a dejar de usar modo lab `file:` / `PAQ_REPO_LAB` en local; el **default de repo/deploy** sí es Cloudsmith.

---

## Reglas de negocio

| ID | Regla |
|----|--------|
| R-CS-01 | El token de lectura no se commitea (`auth.json`, `_authToken=` con valor, ni `CLOUDSMITH_API_KEY`). Auth Composer es efímera en el install (`X-API-KEY` y/o `auth.json` bearer). |
| R-CS-02 | Tras el PR, el default de repo/deploy no usa Satis `100.110.69.93` ni Verdaccio Funnel para **paquetes SDK**. |
| R-CS-03 | Tailscale no es requisito para bajar el SDK (sí puede seguir para SQL/RDP). |
| R-CS-04 | `VERDACCIO_AUTH_TOKEN` no es la vía del código nuevo; puede quedar en Vercel solo hasta producción de este PR y luego se borra. |
| R-CS-05 | Pins de esta historia = anexo, salvo decisión humana de bump antes del merge. |
| R-CS-06 | Sin `CLOUDSMITH_READ_TOKEN`, el install **falla** de forma explícita; no hay fallback silencioso a Satis/Verdaccio. |
| R-CS-07 | Rollback hasta Fase 7 = restaurar lock + URLs del commit previo; no hay switch en la aplicación. |

---

## Criterios de aceptación

- [x] **CA-01** `backend/composer.json` usa `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/` (sin `/basic/`), `secure-http` acorde a HTTPS, pin `paqsuite/laravel-core` `1.3.13-beta.1` (o bump acordado); `composer.lock` regenerado contra ese origen.
- [x] **CA-02** `frontend/package.json` pin `@paqsuite/react-core` `2.4.24-beta.1` (o bump acordado); `package-lock.json` resuelve tarball en `npm.cloudsmith.io` (no Funnel).
- [x] **CA-03** `frontend/.npmrc`: npmjs para el resto; scope `@paqsuite` → Cloudsmith; `_authToken=${CLOUDSMITH_READ_TOKEN}` sin valor literal.
- [x] **CA-04** `vercel-install.sh` exige `CLOUDSMITH_READ_TOKEN`, no Verdaccio/Tailscale para el SDK; sin token el script sale con error explícito.
- [x] **CA-05** En Forge, `composer show paqsuite/laravel-core` muestra el pin de oleada y origen Cloudsmith.
- [x] **CA-06** El build Vercel resuelve `@paqsuite/react-core` desde `npm.cloudsmith.io`.
- [x] **CA-07** Login + health del producto siguen operativos **sin** Tailscale para paquetes.
- [x] **CA-08** No hay `CLOUDSMITH_API_KEY` en el repo ni como env de install del host.
- [x] **CA-09** No se añaden workflows GHA de install si hoy no existen; si existieran, usarían el mismo secreto y registry.
- [x] **CA-10** Docs de deploy del host marcan Satis/Verdaccio como legado y apuntan a Cloudsmith / este instructivo.

### Escenarios Gherkin

```gherkin
Feature: Consumo SDK desde Cloudsmith
  Como operador del host
  Quiero instalar laravel-core y react-core desde Cloudsmith
  Para no usar Satis ni Verdaccio para paquetes

  Scenario: Install frontend con token read
    Given CLOUDSMITH_READ_TOKEN está definido
    And .npmrc apunta el scope @paqsuite a npm.cloudsmith.io
    When corre el install de frontend (local o Vercel)
    Then se resuelve @paqsuite/react-core en el pin de oleada desde Cloudsmith
    And no se usa VERDACCIO_AUTH_TOKEN

  Scenario: Install sin token
    Given CLOUDSMITH_READ_TOKEN no está definido
    When corre vercel-install.sh
    Then el proceso falla con mensaje explícito
    And no instala el SDK desde Satis ni Verdaccio
```

---

## Supuestos explícitos

- Los prereleases del anexo ya están en Cloudsmith.
- `minimum-stability` / `prefer-stable` actuales del host permiten el pin beta **explícito**; la TR no abre beta a otros paquetes sin necesidad (SPEC §6).
- Header `X-API-KEY` y `auth.json` bearer son la misma regla de credencial en runtime; la TR elige el snippet Forge.
- No hay job GHA de `composer install` / `npm ci` en este repo hoy → CA-09 = no crear pipeline.

## Preguntas abiertas

- Ninguna bloqueante. Opcional: bump de pins si Cloudsmith publica otro prerelease antes del merge.

## Riesgos de ambigüedad

- Confundir “dejar Tailscale para SQL” con “seguir usándolo para el SDK”.
- Copiar `CLOUDSMITH_API_KEY` del Framework al host.
- Tratar el modo lab `file:` como default de deploy.

---

## Veredicto B1

Lista para TR: **Sí**

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-08 | B+B1 desde SPEC-012. |
| 2026-10-09 | F + I: CA marcados; Estado **Finalizado**. Sin HU-update. |
