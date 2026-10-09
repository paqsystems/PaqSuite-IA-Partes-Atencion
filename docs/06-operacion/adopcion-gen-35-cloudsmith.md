# Cableado Cloudsmith — Partes de Atención (piloto GEN-35)

**No es un GEN de producto.** GEN-35 vive en Framework (`SPEC-001-35`). Este archivo es el **instructivo de adopción** (anexo normativo de pasos) del host.

Contrato OpenSpec: [`docs/05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md`](../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) — **readaptar** el consumo del SDK (Satis + Verdaccio → Cloudsmith). No publicar paquetes.

PC nueva / token faltante / errores 401: regla BASE **`45-host-sdk-cloudsmith`** (`.cursor/rules/base/00-arquitectura/45-host-sdk-cloudsmith.mdc`). Variable de usuario `CLOUDSMITH_READ_TOKEN`; no usar `backend/.env`.

SoT Framework: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-sdk-registry.md`

Pins a adoptar (prerelease ya en Cloudsmith):

| Gestor | Paquete | Pin |
|--------|---------|-----|
| Composer | `paqsuite/laravel-core` | `1.3.13-beta.1` (hoy en repo: `1.3.12` vía Satis) |
| npm | `@paqsuite/react-core` | `2.4.24-beta.1` (hoy: `2.4.23` vía Verdaccio Funnel) |

Hoy el código apunta a Satis (`http://100.110.69.93/satis`) y Verdaccio Funnel (`VERDACCIO_AUTH_TOKEN`). Eso hay que **cambiar en el PR de adopción**.

---

## 0. Qué tenés que tener creado (vos)

En Cloudsmith: un token **read** del repo `paqsystems/paqsuite-sdk` **distinto** del write del Framework (`CLOUDSMITH_API_KEY`).

En GitHub del **Host** (este repo): el token debe ser **Secret** de Actions, **no** Variable de repositorio (las Variables se ven en logs y no se enmascaran).

Nombre único en todas las plataformas:

| Nombre | Dónde | Uso |
|--------|--------|-----|
| `CLOUDSMITH_READ_TOKEN` | GitHub **Actions secret**, Forge env, Vercel env | npm `_authToken` y Composer `X-API-KEY` |
| `CLOUDSMITH_API_KEY` | **Prohibido** en Partes | Es write del Framework |

Si ya cargaste el valor como *Variable*: borrala y creala como **Secret**.

---

## 1. GitHub Actions (este repo)

Partes **no tiene** workflows de install hoy. Cuando exista CI (`composer install` / `npm ci`):

1. Repo → **Settings → Secrets and variables → Actions → New repository secret**.
2. Name: `CLOUDSMITH_READ_TOKEN`. Value: token **read** Cloudsmith.
3. En el job, **antes** de Composer:

```yaml
env:
  CLOUDSMITH_READ_TOKEN: ${{ secrets.CLOUDSMITH_READ_TOKEN }}
- name: Composer auth Cloudsmith
  working-directory: backend
  run: |
    node -e "
      const fs=require('fs');
      const j=JSON.parse(fs.readFileSync('composer.json','utf8'));
      const r=j.repositories;
      const repo=Array.isArray(r)?r[0]:r[0];
      const list=Array.isArray(r)?r:Object.values(r);
      list[0].options={http:{header:['X-API-KEY: '+process.env.CLOUDSMITH_READ_TOKEN]}};
      fs.writeFileSync('composer.json', JSON.stringify(j,null,2));
    "
    composer install --no-interaction --prefer-dist
```

Mejor: no mutar `composer.json` en git; inyectar solo en el runner (el snippet de arriba es efímero en el checkout del job).

4. Frontend en el mismo job:

```yaml
- name: npm ci Cloudsmith
  working-directory: frontend
  env:
    CLOUDSMITH_READ_TOKEN: ${{ secrets.CLOUDSMITH_READ_TOKEN }}
  run: npm ci
```

El `.npmrc` commiteado usa `${CLOUDSMITH_READ_TOKEN}` (npm lo expande desde el entorno). **No** pongas el valor en el archivo.

5. El runner de GitHub **no** necesita Tailscale para el SDK.

---

## 2. Laravel Forge (backend)

Composer corre en el servidor. Vercel no instala PHP.

1. Sitio Partes → **Environment** (o *Environment Variables*).
2. Agregar `CLOUDSMITH_READ_TOKEN` = token read (mismo valor que GitHub). Forge **enlaza** ese valor al `.env` del release; **no** lo exporta al bash del Deploy Script. `backend/scripts/forge-composer-install.sh` lee la clave del `.env` si el proceso no la tiene.
3. En el **Deploy Script**, invocar el script del repo (`cd $FORGE_RELEASE_DIRECTORY/backend` + `bash scripts/forge-composer-install.sh`). Si hace falta un export explícito **antes** de `composer install` / `composer update`:

```bash
cd /home/forge/.../backend   # path real del sitio

export CLOUDSMITH_READ_TOKEN="${CLOUDSMITH_READ_TOKEN}"

# auth.json no se commitea; se genera en el deploy
php -r '
$k=getenv("CLOUDSMITH_READ_TOKEN");
if(!$k){fwrite(STDERR,"Falta CLOUDSMITH_READ_TOKEN\n"); exit(1);}
file_put_contents("auth.json", json_encode([
  "bearer" => ["composer.cloudsmith.io" => $k]
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
'

# El composer.json del PR ya debe tener repositories URL Cloudsmith (sin /basic/)
# y el header X-API-KEY. Si Composer no manda bearer, usar:
python3 - <<'PY'
import json, os
p="composer.json"
j=json.load(open(p))
repos=j["repositories"]
if isinstance(repos, dict):
    r=list(repos.values())[0]
else:
    r=repos[0]
r["options"]={"http":{"header":[f"X-API-KEY: {os.environ['CLOUDSMITH_READ_TOKEN']}"]}}
json.dump(j, open(p,"w"), indent=4)
print("composer.json header X-API-KEY inyectado")
PY

composer install --no-dev --no-interaction --prefer-dist
```

4. Quitar del deploy: Tailscale **solo para Satis**, `github-oauth` para el SDK, `forge-ensure-framework.sh`, path al monorepo Framework.
5. Tailscale puede seguir para SQL/RDP; **no** para bajar `laravel-core`.
6. Tras el primer deploy: `composer show paqsuite/laravel-core` debe mostrar `1.3.13-beta.1` y origen Cloudsmith.

Rollback hasta Fase 7: restaurar `composer.lock` + URL Satis del commit anterior.

---

## 3. Vercel (frontend)

Hoy `frontend/scripts/vercel-install.sh` exige `VERDACCIO_AUTH_TOKEN` y Funnel Tailscale. Hay que **dejar de usarlo**.

1. Proyecto Vercel Partes → **Settings → Environment Variables**.
2. Crear `CLOUDSMITH_READ_TOKEN` (Production + Preview + Development) = token **read**.
3. **No** copiar `CLOUDSMITH_API_KEY` del Framework.
4. Dejar `VERDACCIO_AUTH_TOKEN` solo hasta que el PR de adopción esté en producción; después borrarlo.
5. Opcional: `PAQSUITE_NPM_REGISTRY=https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/`
6. En el PR de código (este repo):

- `frontend/.npmrc`:

```
registry=https://registry.npmjs.org/
replace-registry-host=never
@paqsuite:registry=https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/
//npm.cloudsmith.io/paqsystems/paqsuite-sdk/:_authToken=${CLOUDSMITH_READ_TOKEN}
```

- `frontend/package.json`: `"@paqsuite/react-core": "2.4.24-beta.1"`
- `frontend/scripts/vercel-install.sh`: exigir `CLOUDSMITH_READ_TOKEN`; registry default Cloudsmith; `npm install @paqsuite/react-core@2.4.24-beta.1 --save-exact` + `npm install` (o `npm ci` si el lock ya apunta a Cloudsmith).
- Regenerar `package-lock.json` en una máquina **con** el token, **sin** Funnel.

7. Build Vercel: no Tailscale, no Funnel.

---

## 4. Archivos a tocar en el PR (código)

| Archivo | Qué hacer |
|---------|-----------|
| `backend/composer.json` | `repositories` → `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/` **sin** `/basic/`. Pin `1.3.13-beta.1`. `minimum-stability`: `beta` (o equivalente para prerelease). `secure-http`: `true`. |
| `backend/composer.lock` | Regenerar con token read. |
| `frontend/.npmrc` | Cloudsmith + `${CLOUDSMITH_READ_TOKEN}` |
| `frontend/package.json` | pin `2.4.24-beta.1` |
| `frontend/package-lock.json` | Regenerar |
| `frontend/scripts/vercel-install.sh` | Cloudsmith; secret `CLOUDSMITH_READ_TOKEN` |
| `docs/06-operacion/verdaccio-vercel-conectividad.md` | Marcar legado / apuntar a este archivo |

Prohibido commitear `auth.json`, `_authToken=` con valor, o el write key.

---

## 5. Smoke (cuando el PR esté desplegado)

1. Forge: `composer show paqsuite/laravel-core` → `1.3.13-beta.1`.
2. Vercel build log: resuelve `@paqsuite/react-core@2.4.24-beta.1` desde `npm.cloudsmith.io`.
3. Login + health **sin** Tailscale para paquetes.
4. Local: mismo `.npmrc` + env `CLOUDSMITH_READ_TOKEN`; Composer con header `X-API-KEY`.

---

## 6. Rollback (hasta Fase 7 Framework)

No hay switch en la aplicación. Restaurar del commit anterior a la adopción:

- `backend/composer.json` + `composer.lock` (URL Satis)
- `frontend/package.json` + `package-lock.json` + `.npmrc` Verdaccio
- `frontend/scripts/vercel-install.sh` (token Verdaccio)
- Secretos: volver a usar `VERDACCIO_AUTH_TOKEN` en Vercel solo si ese rollback es necesario

Luego `composer install` / `npm ci` con la vía anterior.

---

## 7. Tango (después)

Copiar este archivo al repo Tango. Cambiar solo nombres de sitio Forge, proyecto Vercel y pins si no van a la par. Mismos nombres de secretos.
