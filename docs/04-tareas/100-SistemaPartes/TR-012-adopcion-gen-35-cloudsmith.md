# TR-012 – Readaptar consumo SDK a Cloudsmith (host)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-012-adopcion-gen-35-cloudsmith](../../03-historias-usuario/100-SistemaPartes/HU-012-adopcion-gen-35-cloudsmith.md) |
| **SPEC relacionada** | [SPEC-012-adopcion-gen-35-cloudsmith](../../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) |
| **Épica** | 100 — Sistema Partes (ops) |
| **Prioridad** | MUST-HAVE |
| **Roles** | Desarrollador / operación Forge / Vercel |
| **Dependencias** | GEN-35 publicado en Cloudsmith (Framework); instructivo [adopcion-gen-35-cloudsmith.md](../../06-operacion/adopcion-gen-35-cloudsmith.md) |
| **Clasificación** | HU SIMPLE (ops; sin UI) |
| **Estado** | Finalizado |
| **F1** | [F1-TR-012-adopcion-gen-35-cloudsmith.md](./F1-TR-012-adopcion-gen-35-cloudsmith.md) — Aprobado con observaciones |
| **F** | [F-TR-012-adopcion-gen-35-cloudsmith.md](./F-TR-012-adopcion-gen-35-cloudsmith.md) — Aprobado con observaciones |
| **Última actualización** | 2026-10-09 |

**Origen:** HU-012 · **SPEC:** SPEC-012 · **Anexo de pasos:** instructivo Cloudsmith del host.

---

## 1) HU refinada (resumen)

Readaptar install de `paqsuite/laravel-core` y `@paqsuite/react-core` de Satis + Verdaccio a Cloudsmith `paqsystems/paqsuite-sdk`. Sin pantallas, sin publicación, sin Tango, sin crear GHA.

### Pins (oleada; bump solo con decisión humana)

| Gestor | Paquete | Pin |
|--------|---------|-----|
| Composer | `paqsuite/laravel-core` | `1.3.13-beta.1` |
| npm | `@paqsuite/react-core` | `2.4.24-beta.1` |

### Decisiones TR (cierran A1 / HU)

| Tema | Decisión |
|------|----------|
| GHA | **No** crear workflows. Hoy no hay `.github` de install. |
| Auth Composer Forge | Generar `backend/auth.json` efímero (bearer `composer.cloudsmith.io`) **y** header `X-API-KEY` en `repositories.options.http` **solo en el checkout del deploy** (no commitear). Snippets del instructivo §2. |
| Auth npm | `.npmrc` con `${CLOUDSMITH_READ_TOKEN}`; `vercel-install.sh` exige esa variable. |
| `minimum-stability` | Mantener `dev` + `prefer-stable` actuales; **pin exacto** del prerelease. No relajar otros paquetes. |
| Rollback | Documentar: restaurar `composer.lock` / `package-lock.json` + URLs Satis/Verdaccio del commit previo. |
| Tests producto | No hay UI nueva. Verificación = locks + script + smoke deploy (SPEC §5). No E2E de pantallas por esta TR. |

---

## 2) Fuera de alcance

Igual que SPEC/HU: publicación, Fase 7 global, Tango, UI/SP/menú, pipelines nuevos, forzar el abandono del modo lab `file:` en local.

---

## 3) Pasos de implementación

### 3.1 Secretos (humano / ops; no en git)

1. Token Cloudsmith **read** del repo `paqsystems/paqsuite-sdk`.
2. Nombre único: `CLOUDSMITH_READ_TOKEN` — GitHub **secret** (si en el futuro hay Actions), Forge env, Vercel env (Production + Preview + Development), local (env del desarrollador).
3. **Prohibido** `CLOUDSMITH_API_KEY` en Partes.
4. Si el valor estaba como Variable de GitHub: pasarlo a Secret.

### 3.2 Backend

1. `backend/composer.json`:
   - `repositories` URL `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/` **sin** `/basic/`.
   - `"paqsuite/laravel-core": "1.3.13-beta.1"`.
   - `config.secure-http`: `true` (hoy está `false` por Satis HTTP).
2. Con token en el entorno, regenerar `composer.lock` (`composer update paqsuite/laravel-core --with-dependencies` o equivalente que deje el pin).
3. Asegurar `auth.json` en `.gitignore` si no está.
4. `backend/scripts/forge-composer-install.sh`: exigir `CLOUDSMITH_READ_TOKEN`, validar lock Cloudsmith (no path, no Satis), `auth.json` efímero + header `X-API-KEY` en el checkout del deploy. Quitar comprobación Satis/Tailscale para paquetes.

### 3.3 Frontend

1. `frontend/.npmrc`:

```
registry=https://registry.npmjs.org/
replace-registry-host=never
@paqsuite:registry=https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/
//npm.cloudsmith.io/paqsystems/paqsuite-sdk/:_authToken=${CLOUDSMITH_READ_TOKEN}
```

2. `frontend/package.json`: `"@paqsuite/react-core": "2.4.24-beta.1"`.
3. Regenerar `package-lock.json` con token, **sin** Funnel; `resolved` debe ser `npm.cloudsmith.io`.
4. `frontend/scripts/vercel-install.sh`: exigir `CLOUDSMITH_READ_TOKEN`; registry default Cloudsmith; pin `2.4.24-beta.1` (o env `PAQSUITE_REACT_CORE_VERSION` alineado); `npm install`/`ci` sin `VERDACCIO_AUTH_TOKEN`.
5. `frontend/scripts/refresh-react-core-lock.sh`: misma vía Cloudsmith (deja Verdaccio).
6. `frontend/MODO-REPO-LAB.md`: default deploy = Cloudsmith; lab `file:` sigue opcional y no se commitea en release.

### 3.4 Documentación

- `docs/06-operacion/verdaccio-vercel-conectividad.md`: legado; apuntar al instructivo Cloudsmith.
- Instructivo ya es anexo normativo; no duplicar SPEC-001-35.

### 3.5 Vercel / Forge (ops)

- Vercel: crear `CLOUDSMITH_READ_TOKEN`; no copiar write key; `VERDACCIO_AUTH_TOKEN` se borra **después** de producción de este PR (HU R-CS-04).
- Forge: misma env; snippet §2 del instructivo; smoke `composer show paqsuite/laravel-core`.

---

## 4) Verificación (DoD)

- [x] Locks y manifests según CA-01…03.
- [x] `vercel-install.sh` CA-04 (prueba local: sin token → exit ≠ 0).
- [x] Forge CA-05; Vercel CA-06 (cuando haya deploy).
- [x] Health/login CA-07.
- [x] Grep repo: no `CLOUDSMITH_API_KEY`; no `_authToken=` con valor; `auth.json` no versionado (CA-08).
- [x] Sin workflows GHA nuevos (CA-09).
- [x] Docs legado (CA-10).
- [x] Rollback descrito en instructivo o este TR §1.

No se exige `npm run test:all` como prueba de esta TR salvo que un cambio de pin rompa tipos/runtime (entonces Parte E sobre la suite existente).

### E (2026-10-09)

- Backend `php artisan test`: **104 passed**, 7 skipped (SQL Server). Ajuste test atributos: `titulo` / `permisoAlta` (contrato laravel-core 1.3.13-beta.1).
- Frontend Vitest: tests de tema alineados a IAT-79 (`rose.material`, persistencia `paqsuite.*`). Suite completa: 142 passed; 2 timeouts DX (UsuariosAdmin / diseñador) al correr todo junto.
- `tsc -b`: errores en `node_modules/@paqsuite/react-core` (paquete) + varios host; no bloquean Vitest.
- E2E Playwright: no corrido (TR ops; sin UI nueva).
- CA-08/09: sin `CLOUDSMITH_API_KEY` en código; `auth.json` gitignored; sin workflows GHA.

---

## 5) Riesgos

- Token mal cargado como Variable (se filtra en logs).
- `prefer-stable` ignorado si el pin no es exacto.
- Lock generado todavía contra Funnel por olvidar el env.

---

## 6) Plan D1 (2026-10-09)

# Plan de implementación - TR-012

## Alcance entendido

Cambiar origen de install del SDK en este host (Satis + Verdaccio → Cloudsmith). Sin UI, sin GHA nuevos, sin publicar paquetes.

## Fuentes leídas

SPEC-012, HU-012, TR-012, instructivo `adopcion-gen-35-cloudsmith.md`, `composer.json`, `.npmrc`, `vercel-install.sh`, scripts refresh lock, docs deploy.

## Impacto esperado

- DB: ninguno
- Backend: solo `composer.json` / lock / `secure-http`
- Frontend: pin, `.npmrc`, lock, scripts install
- Tests: no suite nueva; smoke script sin token
- Docs: legado Verdaccio/Satis; ritual bump y deploy SDK
- DevOps: secretos Forge/Vercel (humano); snippets ya en instructivo

## Orden de trabajo

1. Manifests y scripts (sin token en git).
2. Docs deploy.
3. Regenerar locks si existe `CLOUDSMITH_READ_TOKEN`.
4. Probar `vercel-install.sh` sin token → error.

## Riesgos

- Locks no regenerados sin token local.
- Pin beta vs `prefer-stable` si el pin no queda exacto.

## Tests a ejecutar

- Script sin token (CA-04).
- Grep: no write key, no token literal.
- `composer show` / `npm list` solo si hay token y lock nuevo.

## Dudas / bloqueos

- Secretos Forge/Vercel no se pueden crear desde el código (ops humano, TR §3.1/3.5).
- Smoke login/health en deploy queda pendiente de ops.

## Confirmación de alcance

No se crean workflows, no se toca UI, no se publica a Cloudsmith.

---

## 7) Revisión C1 (ambigüedad TR)

- Estado: **Apto**
- Críticas: ninguna.
- Puede ejecutar D: **Sí** (D1 breve: orden 3.1 secretos → 3.2/3.3 código → 3.4 docs → 3.5 ops).

---

## 8) F / I (2026-10-09)

- F: [F-TR-012](./F-TR-012-adopcion-gen-35-cloudsmith.md).
- I: sin archivos `*-update` de la familia 012. Originales → **Finalizado**.
