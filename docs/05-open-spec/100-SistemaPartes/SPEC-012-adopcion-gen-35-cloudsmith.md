# SPEC-012 — Readaptar consumo del SDK Framework a Cloudsmith

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-012 |
| **Título** | Readaptar el uso del SDK Framework (`paqsuite/laravel-core`, `@paqsuite/react-core`) de Satis + Verdaccio a Cloudsmith |
| **Producto** | Partes de Atención |
| **Épica / carpeta** | 100 — Sistema Partes (ops; no UI) |
| **Estado** | Finalizado |
| **Última actualización** | 2026-10-09 |
| **Tipo** | Adopción Framework GEN-35 en el **host**. **No** duplicar `SPEC-001-35` (publicación). |
| **SoT Framework** | `PaqSuite-IA-FRAMEWORK` SPEC-001-35 · `docs/06-operacion/adopcion-sdk-registry.md` |
| **Anexo normativo (pasos)** | [`docs/06-operacion/adopcion-gen-35-cloudsmith.md`](../../06-operacion/adopcion-gen-35-cloudsmith.md) |
| **HU relacionada(s)** | [HU-012-adopcion-gen-35-cloudsmith](../../03-historias-usuario/100-SistemaPartes/HU-012-adopcion-gen-35-cloudsmith.md) |
| **TR relacionada(s)** | [TR-012-adopcion-gen-35-cloudsmith](../../04-tareas/100-SistemaPartes/TR-012-adopcion-gen-35-cloudsmith.md) |
| **F** | [F-TR-012-adopcion-gen-35-cloudsmith](../../04-tareas/100-SistemaPartes/F-TR-012-adopcion-gen-35-cloudsmith.md) |
| **A1** | [`SPEC-012-adopcion-gen-35-cloudsmith-A1-ambiguity-review.md`](./SPEC-012-adopcion-gen-35-cloudsmith-A1-ambiguity-review.md) |

```text
[Proceso] Readaptación de consumo SDK en host Partes: adoptar GEN-35.
UI/motor = no aplica (ops/CI de install). No reimplementar publicación de paquetes.
SoT: Framework SPEC-001-35.
Host: composer.json, package.json, lockfiles, .npmrc, vercel-install, secretos
CLOUDSMITH_READ_TOKEN, Forge y Vercel; GitHub Actions solo si ya hay job de install.
```

## 1. Resumen ejecutivo

Hoy el host resuelve el SDK así:

- Backend: `paqsuite/laravel-core` **1.3.12** desde Satis (`http://100.110.69.93/satis`).
- Frontend: `@paqsuite/react-core` **2.4.23** desde Verdaccio Funnel (`VERDACCIO_AUTH_TOKEN`, Tailscale).

**Resultado esperado:** el mismo host instala **los mismos paquetes SDK** desde Cloudsmith HTTPS (`paqsystems/paqsuite-sdk`) con un token **read**. Satis, Verdaccio y Funnel **dejan de usarse para paquetes**. No hay cambio de pantallas ni de comportamiento de negocio.

## 2. Alcance

### 2.1 En alcance

- Cambiar **origen de install** de `laravel-core` y `react-core` a Cloudsmith.
- Actualizar pins de esta oleada (anexo): Composer `1.3.13-beta.1`, npm `2.4.24-beta.1`.
- Regenerar `composer.lock` y `package-lock.json` contra Cloudsmith.
- Cablear secreto `CLOUDSMITH_READ_TOKEN` (GitHub Actions **si hay** job de install, Forge, Vercel, local).
- Adaptar `frontend/.npmrc` y `frontend/scripts/vercel-install.sh` (dejar de exigir Verdaccio/Funnel).
- Ajustar documentación ops del host que todavía diga Satis/Verdaccio como vía de **deploy**.
- Conservar procedimiento de **rollback** a Satis/Verdaccio hasta Fase 7 del Framework (restaurar lock + URLs del commit anterior).

### 2.2 Fuera de alcance

- Publicar paquetes en Cloudsmith (Framework).
- Inventar un GEN-35 de producto (pantallas, SP, menú, i18n, `data-testid`).
- Corte global de Satis/Verdaccio en todos los hosts (Fase 7 Framework).
- Copiar este trabajo a Tango (otro repo; el anexo lo menciona como después).
- Crear pipelines GitHub Actions **nuevos** solo porque GEN-35 existe. Si **no** hay workflow de `composer install` / `npm ci`, no es entregable de este SPEC.
- Modo lab `file:` / `PAQ_REPO_LAB` (sigue siendo opcional local; el default de repo/deploy pasa a Cloudsmith).

## 3. Actores y contexto

| Actor | Qué hace |
|-------|----------|
| Desarrollador del host | Cambia manifests/locks, usa `CLOUDSMITH_READ_TOKEN` en local, no commitea tokens. |
| Operación Forge | Variable de entorno + deploy que autentica Composer **antes** de `composer install`. |
| Operación Vercel | Variable de entorno; el script de install resuelve `@paqsuite` en Cloudsmith **sin** Tailscale. |
| GitHub Actions | Solo si ya existe job de install: el mismo secreto y el mismo registry. |
| Cloudsmith | Repo `paqsystems/paqsuite-sdk`; token **read** distinto de `CLOUDSMITH_API_KEY` (write del Framework). |

Precondición: el Framework ya publicó los prereleases de esta oleada en Cloudsmith (anexo).

## 4. Comportamiento funcional

No hay flujo de usuario de producto. Flujo de **install**:

1. El entorno expone `CLOUDSMITH_READ_TOKEN` (secret, no variable visible en logs).
2. Composer usa repo `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/` **sin** `/basic/`, `secure-http: true`, pin `paqsuite/laravel-core` de esta oleada.
3. Auth Composer: token **no** va al git. En el momento del install se inyecta `X-API-KEY` y/o se genera `auth.json` (bearer) **efímero**. Ambas son la misma regla: credencial solo en runtime. No usar `CLOUDSMITH_API_KEY`.
4. npm: registry público para el resto; scope `@paqsuite` → `https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/` con `_authToken=${CLOUDSMITH_READ_TOKEN}` en `.npmrc` commiteado **sin** valor literal.
5. Install / ci resuelve versiones pinneadas desde Cloudsmith.
6. Si falta el token: el install **falla** de forma explícita (no caer a Satis/Verdaccio en silencio).
7. Rollback hasta Fase 7: restaurar `composer.lock` / `package-lock.json` y URLs Satis/Verdaccio del commit previo; no es un switch en la aplicación.

### 4.1 Reglas

1. Prohibido commitear `auth.json`, `_authToken=` con valor, o el write key del Framework.
2. Prohibido dejar Satis (`100.110.69.93`) o Verdaccio Funnel como registry de **paquetes SDK** en el default de repo/deploy tras el PR de adopción.
3. Tailscale puede seguir para SQL/RDP; **no** es requisito para bajar el SDK.
4. `VERDACCIO_AUTH_TOKEN` puede quedar en Vercel solo hasta que este PR esté en producción; después se elimina. No es la vía de install del código nuevo.
5. Los pins de esta HU/TR son los del anexo (`1.3.13-beta.1` / `2.4.24-beta.1`) salvo decisión humana de bump **antes** del merge.

## 5. Criterios verificables

- [x] `backend/composer.json` apunta a Cloudsmith (sin `/basic/`) y pin `paqsuite/laravel-core` de esta oleada; `composer.lock` regenerado contra ese origen.
- [x] `frontend/package.json` pin `@paqsuite/react-core` de esta oleada; `package-lock.json` resuelve tarball `npm.cloudsmith.io` (no Funnel).
- [x] `.npmrc` usa Cloudsmith + `${CLOUDSMITH_READ_TOKEN}` sin secreto en git.
- [x] `vercel-install.sh` exige `CLOUDSMITH_READ_TOKEN` y no requiere Verdaccio/Tailscale para el SDK.
- [x] Forge: `composer show paqsuite/laravel-core` muestra el pin de oleada y origen Cloudsmith.
- [x] Build Vercel resuelve `@paqsuite/react-core` desde `npm.cloudsmith.io`.
- [x] Login + health del producto siguen funcionando; **sin** Tailscale para paquetes.
- [x] No hay `CLOUDSMITH_API_KEY` en este repo ni en env del host.
- [x] Si existe job GHA de install, usa el mismo secreto y registry; si no existe, no se inventa pipeline como alcance.
- [x] Documentación de deploy del host deja de presentar Satis/Verdaccio como vía vigente de SDK (legado marcado).

## 6. Impacto técnico (visión para TR)

| Archivo / superficie | Cambio |
|----------------------|--------|
| `backend/composer.json` / `composer.lock` | Registry Cloudsmith + pin + lock |
| `frontend/.npmrc` | Scope `@paqsuite` Cloudsmith |
| `frontend/package.json` / `package-lock.json` | Pin + lock |
| `frontend/scripts/vercel-install.sh` | Token y registry Cloudsmith |
| `frontend/scripts/refresh-react-core-lock.sh` | Alinear a Cloudsmith (deja Verdaccio) |
| Docs ops (`verdaccio-vercel-conectividad.md`, `MODO-REPO-LAB.md` deploy) | Legado / Cloudsmith |
| Forge / Vercel / GHA (si hay install) | Secret `CLOUDSMITH_READ_TOKEN`; quitar dependencia Funnel para SDK |

`minimum-stability` Composer: el anexo indica `beta` (o equivalente) para poder instalar el prerelease pinneado. La TR no debe abrir estabilidad beta a paquetes no pinneados sin necesidad.

## 7. Riesgos y supuestos

- Cloudsmith ya tiene los prereleases publicados (dependencia Framework).
- El anexo normativo detalla snippets de deploy; el SPEC fija **qué** debe quedar; la TR elige el snippet canónico sin cambiar el origen.
- Rollback a Satis/Verdaccio es temporal hasta Fase 7 Framework; no es feature de producto.
- No hay UI; no aplican reglas de grilla/i18n/mobile de pantallas.

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-08 | Borrador inicial (Must = “ver instructivo”). |
| 2026-10-08 | A1: no apto. Alcance reescrito: **readaptar consumo SDK** Satis+Verdaccio → Cloudsmith; instructivo = anexo de pasos. |
| 2026-10-08 | Partes B+B1+C: HU-012 y TR-012 enlazadas; Estado Especificado. |
| 2026-10-09 | F + I: deploy Forge/Vercel Cloudsmith; sin updates que fusionar; Estado **Finalizado**. |
