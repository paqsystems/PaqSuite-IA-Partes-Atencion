# Post-deploy — Save As del diseñador filtrado por proceso (PR Partes #139 / Framework #22)

Alcance: preview/dev Forge + sidecar `paqsuite-dx-reporting` en `paq-2021` (`127.0.0.1:5055`).

Referencias: [TR-011 §19](../04-tareas/100-SistemaPartes/TR-011-reportes-emisiones.md) · Framework `services/dx-reporting/README.md` · [plan sidecar](../01-arquitectura/plan-dx-reporting-sidecar-aws-gateway.md) §7.

## 1. Merge Git (hecho)

| Repo | PR | Rama destino |
|------|-----|--------------|
| PaqSuite-IA-Partes-Atencion | [#139](https://github.com/paqsystems/PaqSuite-IA-Partes-Atencion/pull/139) | `develop` |
| PaqSuite-IA-FRAMEWORK | [#22](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/pull/22) | `develop` |

**Prod Forge** (`partesatencion-paqsystems.on-forge.com`) sigue rama `main`: promover con PR `develop` → `main` cuando corresponda.

**Dev Forge** (`partesatencion-paqsystems-dev.on-forge.com`, site id `3399763`, server `485400`) rastrea `v1.3.0-CLOUDSMITH` en el repo conectado; para este fix desplegar commit de `develop` vía deploy API o merge/cherry-pick a la rama del sitio.

## 2. Forge — backend Partes (dev)

### 2.1 Deploy

1. Forge → sitio **partesatencion-paqsystems-dev** → Deploy now.
2. Rama/commit: **`develop`** @ merge PR #139 (`31c0b5d…` o posterior).
3. Verificar deploy **finished** y post-deploy (`scripts/forge/post-deploy.sh`) OK.

### 2.2 Variables `.env` (sitio Forge)

| Variable | Valor |
|----------|--------|
| `EMISSION_DESIGNER_SIDECAR_TOKEN` | Secreto compartido con sidecar (`PaqSuite:SidecarSchemaToken`) |
| `PAQSUITE_EMISSION_DX_MODE` | `remote` |
| `PAQSUITE_EMISSION_DX_SERVICE_URL` | `http://127.0.0.1:5055/api/v1/dx-reporting` |

Tras editar: `php artisan optimize` (o redeploy con cache).

**Nota:** si el token no existía, generarlo una vez y replicarlo en sidecar (§3.2). No commitear el valor.

### 2.3 Smoke API (desde el servidor o PC con tenant)

```bash
curl -sS -H "X-Paq-Cliente: dev" \
  -H "X-Paq-Sidecar-Schema-Token: <token>" \
  "https://partesatencion-paqsystems-dev.on-forge.com/api/v1/emissions/design/report-storage-urls?processCode=partes.informes.paqueteHoras"
```

Esperado: envelope `error: 0`, `resultado.urls[]` solo reportes del proceso (no Consulta detallada).

## 3. Sidecar DX (`/home/forge/paqsuite-dx-reporting`)

No va a Cloudsmith; se publica desde Git Framework.

### 3.1 Actualizar binario (post PR #22)

En **`paq-2021` no hay clone del Framework** (git clone HTTPS falla sin deploy key). Opciones:

**A — Desde PC de desarrollo (recomendado MVP):**

```powershell
cd C:\Programacion\PaqSuite-IA-FRAMEWORK
git checkout develop && git pull
cd services\dx-reporting\src\PaqSuite.DxReporting
dotnet publish -c Release -r linux-x64 --self-contained false -o .\publish-linux
# rsync/scp publish-linux/ → forge@paq-2021:/home/forge/paqsuite-dx-reporting/publish/
```

**B — En servidor** (solo si existe clone o deploy key GitHub):

```bash
FW=/home/forge/PaqSuite-IA-FRAMEWORK
cd "$FW" && git fetch origin && git checkout develop && git pull origin develop
cd services/dx-reporting/src/PaqSuite.DxReporting
dotnet publish -c Release -r linux-x64 --self-contained false -o /home/forge/paqsuite-dx-reporting/publish
```

### 3.2 Config sidecar

En `appsettings.Production.json` o variables de entorno del proceso:

| Clave | Valor |
|-------|--------|
| `PaqSuite:BackendBaseUrl` | `https://partesatencion-paqsystems-dev.on-forge.com` |
| `PaqSuite:SidecarSchemaToken` | **Igual** que `EMISSION_DESIGNER_SIDECAR_TOKEN` del host |

### 3.3 Reinicio

```bash
/home/forge/paqsuite-dx-reporting/start.sh
curl -sS http://127.0.0.1:5055/health
```

Log: `/home/forge/paqsuite-dx-reporting/sidecar.log`.

### 3.4 Schemas (post-deploy Laravel)

El deploy Partes ejecuta `scripts/forge/sync-dx-reporting-schemas.sh` (copia JSON legacy a `data/schemas/`). No sustituye DIC/GEN-34; solo complemento Field List.

## 4. Frontend (Vercel preview)

El merge a `develop` dispara preview si el proyecto está cableado a GitHub. Smoke manual:

1. Usuario con `emission.design`.
2. **Paquete de horas** → confirmar diseño → **Save As**.
3. La lista no debe incluir layouts de **Consulta detallada** u otro `processCode`.

Ver [guia-bateria-manual-tr-015-ia-diseno.md](./guia-bateria-manual-tr-015-ia-diseno.md) §8.

## 5. Cloudsmith

Este fix **no** publica paquetes npm/Composer. No bump de `@paqsuite/react-core` / `laravel-core` requerido por Save As.

## 6. Checklist rápido

- [ ] PR #139 y #22 mergeados en `develop`
- [ ] Forge dev desplegado con código PR #139
- [ ] `EMISSION_DESIGNER_SIDECAR_TOKEN` en `.env` Forge dev
- [ ] Sidecar rebuild desde Framework `develop` (PR #22) + restart
- [ ] `SidecarSchemaToken` = token del host
- [ ] Smoke API `report-storage-urls` + Save As en diseñador
- [ ] (Opcional prod) PR `develop` → `main` + mismo checklist en sitio prod
