# Verdaccio + Vercel — conectividad `@paqsuite/react-core`

> **Legado (GEN-35).** El deploy vigente resuelve el SDK desde **Cloudsmith**, no desde Verdaccio/Funnel.  
> Instructivo: [`adopcion-gen-35-cloudsmith.md`](./adopcion-gen-35-cloudsmith.md) · SPEC-012.  
> Este archivo se conserva como diagnóstico histórico de Funnel/Verdaccio.

Fecha: 2026-10-06 · Producto: Partes-Atención

## Diagnóstico (oleada 2.4.15)

| Síntoma | Causa |
|--------|--------|
| Build Vercel OK en local, falla en CI con `MISSING_EXPORT productTitleI18nKey` | `vercel-install.sh` instalaba un **tarball vendored antiguo** (`2.4.14.tgz`), no el `@paqsuite/react-core@2.4.15` publicado en Verdaccio. |
| Código del host ya usa `appProductBranding` / branding SDK | Correcto; el fallo es **artefacto de install**, no regressión de features. |
| `npm view` / install desde esta PC a `100.110.69.93:4873` o Funnel timeout | **Red o Funnel**: Tailscale caído, Funnel no publicado, token Verdaccio ausente, o firewall en `srv-pq`. |
| Satis (backend) vs Verdaccio (frontend) | Son registries distintos: Satis sirve `paqsuite/laravel-core`; Verdaccio sirve `@paqsuite/react-core`. Ambos viven en `srv-pq` pero puertos/rutas diferentes. |

**Conclusión:** mantener pin **`@paqsuite/react-core@2.4.15`** y el código actual; **dejar de usar tarball vendor** en Vercel; el builder debe resolver el paquete desde **Verdaccio accesible por HTTPS** (Tailscale Funnel) con **`VERDACCIO_AUTH_TOKEN`**.

---

## Contrato en repo (post-rollback tarball)

| Archivo | Rol |
|---------|-----|
| `frontend/package.json` | `"@paqsuite/react-core": "2.4.15"` |
| `frontend/.npmrc` | npmjs + `@paqsuite:registry=https://srv-pq.tail6726a3.ts.net` + token `${VERDACCIO_AUTH_TOKEN}` |
| `frontend/.npmrc.local.example` | Lab con IP Tailscale `http://100.110.69.93:4873` (copiar a `.npmrc.local`, no commitear) |
| `frontend/scripts/vercel-install.sh` | Valida token → `npm install` (sin `file:vendor/…`) |
| `frontend/scripts/refresh-react-core-lock.ps1` | Regenera `package-lock.json` con `integrity` real desde Verdaccio |

---

## Pasos en `srv-pq` (PC / servidor donde están Satis y Verdaccio)

### 1) Verificar Verdaccio y versión publicada

```bash
# Desde una máquina con Tailscale activo
npm view @paqsuite/react-core@2.4.15 version --registry http://100.110.69.93:4873
npm view @paqsuite/react-core versions --registry http://100.110.69.93:4873
```

Si `2.4.15` no aparece, publicar desde el monorepo Framework (Verdaccio / pipeline acordado) y repetir.

### 2) Token de lectura para CI

- Crear o reutilizar usuario/token Verdaccio con permiso **read** sobre `@paqsuite/*`.
- Guardar el valor como secret **`VERDACCIO_AUTH_TOKEN`** en Vercel (Production + Preview).

### 3) Tailscale Funnel (HTTPS público para Vercel)

Vercel **no** alcanza `100.110.69.93` ni MagicDNS privado. Debe existir un hostname HTTPS público (histórico del repo: `https://srv-pq.tail6726a3.ts.net` → proxy a Verdaccio `:4873`).

En `srv-pq`:

- Confirmar Funnel activo: `tailscale funnel status` (o equivalente en tu instalación).
- Probar desde **fuera** de la LAN (4G o máquina sin Tailscale):

```bash
curl -sS -o /dev/null -w "%{http_code}\n" \
  -H "Authorization: Bearer $VERDACCIO_AUTH_TOKEN" \
  "https://srv-pq.tail6726a3.ts.net/@paqsuite/react-core"
```

Esperado: `200` o `404` en ruta concreta, **no** timeout.

Si el hostname cambió, actualizar `frontend/.npmrc` y la variable opcional **`PAQSUITE_NPM_REGISTRY`** en Vercel.

### 4) Satis (backend, referencia)

Forge/local backend sigue con Satis `http://100.110.69.93/satis` y `composer.lock` — no mezclar con el install FE. Tras bump FE, smoke backend por separado si también subiste `laravel-core`.

---

## Pasos en tu PC de desarrollo (Windows)

1. Tailscale **Connected**.
2. `$env:VERDACCIO_AUTH_TOKEN = '<secret>'` — el **valor real** del secret en Vercel (no el carácter `…` de los ejemplos). Solo el token; si copiaste `Bearer …` desde curl, quitá el prefijo `Bearer `.
3. Opcional lab sin Funnel: copiar `frontend/.npmrc.local.example` → `frontend/.npmrc.local` y usar IP `:4873`.
4. Regenerar lock con integridad correcta:

```powershell
cd frontend
.\scripts\refresh-react-core-lock.ps1
```

5. Verificar:

```powershell
npm list @paqsuite/react-core
npm run build
```

6. Commit `package-lock.json` si el script añadió `integrity`.

---

## Pasos en Vercel

1. **Settings → Environment Variables:** `VERDACCIO_AUTH_TOKEN` (todas las envs que despliegan).
2. **No** definir `NPM_CONFIG_REGISTRY` apuntando a Tailscale (rompe tarballs de npmjs).
3. Root Directory: `frontend`.
4. Install Command: `bash scripts/vercel-install.sh` (ya en `vercel.json`).
5. **Redeploy** del commit que quita vendor, **sin caché de build**.
6. En logs de Install debe verse: `vercel-install: npm install (@paqsuite → https://srv-pq…)` y **no** `file:vendor/…tgz`.

---

## Checklist rápido

- [ ] Verdaccio lista `@paqsuite/react-core@2.4.15`
- [ ] Funnel HTTPS responde desde internet
- [ ] `VERDACCIO_AUTH_TOKEN` en Vercel
- [ ] `refresh-react-core-lock` ejecutado y lock commiteado con `integrity`
- [ ] Redeploy Vercel sin caché
- [ ] Smoke: build OK + login + branding auth

Ver también: [`deploy-sdk-package-repos.md`](../01-arquitectura/deploy-sdk-package-repos.md) · [`ritual-bump-sdk.md`](../01-arquitectura/ritual-bump-sdk.md).
