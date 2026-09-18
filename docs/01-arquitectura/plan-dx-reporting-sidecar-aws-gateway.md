# Plan — sidecar DevExpress Reporting en AWS (VM Gateway compartida)

Fecha: 2026-09-18  
Alcance: **transversal** (cualquier producto PaqSuite que adopte GEN-15), no solo Partes de Atención.  
SoT sidecar: `PaqSuite-IA-FRAMEWORK/services/dx-reporting/` · runbook lab: `docs/06-operacion/runbook-dx-reporting-sidecar.md` (Framework).

---

## Objetivo

Exponer el **motor DevExpress Reporting** (sidecar ASP.NET Core, puerto **5055**) en AWS, **compartiendo la misma instancia que el Gateway** (.NET 8 ya instalado), para alivianar costos frente a una VM dedicada.

El sidecar cumple dos roles:

| Rol | Consumidor | Rutas |
|-----|------------|-------|
| **Render server-side** | Backend Laravel (Forge) | `POST /api/v1/dx-reporting/render` |
| **Diseñador / viewer** | Frontend (Vercel) | `/DXXRD`, `/DXXRDV`, `/DXXQB` |

El deploy del frontend **no incluye** el sidecar: empaqueta solo el cliente JS (`devexpress-reporting-react`). El servicio .NET debe estar **accesible en runtime** cuando se usa el diseñador o se emiten PDF reales.

---

## 1. Revisión de instancia y recursos

### 1.1 Estado observado en Forge (org `paqsuite`)

| Atributo | Valor |
|----------|--------|
| Servidor Forge | `paq-2021` |
| EC2 | `i-0ab40b2f17c7894c9` |
| Región AWS | Ohio (`us-east-2`) |
| IP pública | `13.59.42.169` |
| SO | Ubuntu **20.04** |
| Sitios Laravel | 17+ (Partes, PedidosWeb, Tango, legacy, etc.) |
| Size Forge (legacy) | `319262` (catálogo antiguo; no mapea directo al listado actual Ohio) |

**Nota:** si el Gateway .NET está en **otra** VM (Custom VPS fuera de Forge), validar tipo EC2 y RAM con consola AWS o `free -h` / `df -h` por SSH. Este plan aplica igual; cambia el sizing según si comparte host con muchos sitios PHP o es dedicado al Gateway.

### 1.2 Carga adicional del sidecar

| Proceso | RAM idle típica | Picos |
|---------|-----------------|-------|
| Gateway .NET | 200–500 MB | según tráfico SP |
| DX Reporting (Kestrel) | 300–800 MB | **1–2 GB** por sesión de diseñador |
| PHP-FPM (si comparte `paq-2021`) | ya consumido | muchos sitios activos |

DevExpress Reporting usa Skia + `UseAsyncEngine()`. El **diseñador** (`DXXRD/GetDesignerModel`) es el componente más pesado; `/render` para PDF es más liviano pero puede ser lento en datasets grandes.

### 1.3 Recomendaciones por escenario

#### A) Gateway + Reporting en la misma VM que `paq-2021` (17 sitios PHP)

No alcanza dejar recursos sin cambios.

| Recurso | Recomendación |
|---------|----------------|
| **RAM** | Mínimo **8 GB** (`t3.large` / `m5.large`); **16 GB** (`t3.xlarge`) si diseñador + Gateway + picos PHP coinciden |
| **Disco EBS** | +**10–20 GB** (publish, `layouts/`, logs, cache NuGet si se compila en servidor) |
| **Swap** | **4 GB** (evita OOM en picos del diseñador) |
| **CPU** | 2 vCPU MVP; 4 vCPU si hay emisiones concurrentes |

#### B) Gateway en VM dedicada (solo .NET) — decisión preferida para costo/riesgo

| Recurso | MVP compartido Gateway + Reporting | Si crece uso |
|---------|-------------------------------------|--------------|
| **RAM** | **4 GB** (`t3.medium`) | **8 GB** (`t3.large`) |
| **Disco** | **20 GB** root + volumen persistente layouts | +10 GB/año |
| **Swap** | 2 GB | 4 GB |

**No recomendado:** `t2/t3.small` (2 GB) con Gateway + Reporting juntos.

### 1.4 Otros ajustes de la instancia

1. **Ubuntu 20.04** está EOL → planificar migración a **22.04** (Gateway y sidecar son `net8.0`).
2. **Puertos:** Gateway (ej. `:5000`) y Reporting `:5055` en **`127.0.0.1`**; exposición pública solo vía Nginx + TLS.
3. **URL interna vs pública:** backends Forge en la misma VM pueden usar `http://127.0.0.1:5055`; el FE en Vercel necesita URL HTTPS pública o rewrites.
4. **Monitoreo:** alerta si `GET /health` falla o RAM > 85%.

---

## 2. Arquitectura transversal (multi-producto)

Un **solo sidecar** sirve a todos los hosts desplegados. Cada producto aporta:

- Schemas JSON → `data/schemas/{reportCode}.json`
- Layouts REPX → `data/layouts/{reportCode}.repx`
- Seed GEN-15 (`pq_emission_*`) y puertos `resolveDataset` en Laravel

El sidecar identifica por **`processCode` / `reportUrl`** (ej. `partes.consultaDetallada.principal`), no por tenant ni ID numérico del catálogo Laravel.

```text
Vercel (cada producto FE)
    │
    ├── /api/v1/*  ──────────────────► Forge (Laravel BE del producto)
    │
    └── /DXXRD*    ──► Nginx TLS ──► sidecar :5055 (diseñador)

Forge (cada producto BE)
    ├── POST /render ──► sidecar :5055 (PDF/Excel/CSV server-side)
    └── Gateway jobs ──► Gateway :5000 (SP remoto; independiente de Reporting)
```

Referencia código FE (proxy local dev):

- `frontend/vite.config.ts` — proxea `/DXXRD`, `/DXXRDV`, `/DXXQB`
- `frontend/src/features/partes/informes/dxReportingConfig.ts` — resolución de host en runtime

Referencia Framework:

- Sidecar: `PaqSuite-IA-FRAMEWORK/services/dx-reporting/`
- Motor remoto PHP: `RemoteDxReportingEngine` en `paqsuite/laravel-core`
- Variables: `PAQSUITE_EMISSION_DX_MODE`, `PAQSUITE_EMISSION_DX_SERVICE_URL`, `PAQSUITE_EMISSION_DX_API_KEY`

---

## 3. Instalación en AWS (VM Gateway)

### 3.1 Prerrequisitos (build, una vez)

En PC con **DevExpress Unified Installer 26.1** (Reporting for ASP.NET Core):

```powershell
cd PaqSuite-IA-FRAMEWORK\services\dx-reporting\src\PaqSuite.DxReporting
dotnet restore
dotnet publish -c Release -o .\publish
```

En la VM de producción instalar solo runtime (no SDK salvo compilar en servidor):

```bash
# Ubuntu 22.04 (ajustar versión si aplica)
wget https://packages.microsoft.com/config/ubuntu/22.04/packages-microsoft-prod.deb
sudo dpkg -i packages-microsoft-prod.deb
sudo apt update
sudo apt install -y aspnetcore-runtime-8.0
dotnet --list-runtimes
```

Restore en Linux sin Windows: feed `https://nuget.devexpress.com/api/v3/index.json` + token de cuenta DevExpress.

### 3.2 Despliegue del artefacto

```bash
sudo mkdir -p /var/www/paqsuite-dx-reporting
sudo mkdir -p /var/lib/paqsuite/dx-reporting/{layouts,schemas}
sudo chown -R forge:forge /var/www/paqsuite-dx-reporting /var/lib/paqsuite/dx-reporting

# Copiar publish desde CI o PC (ejemplo)
# rsync -av publish/ forge@<gateway-host>:/var/www/paqsuite-dx-reporting/
```

**Config producción** (`appsettings.Production.json` o variables de entorno):

```json
{
  "PaqSuite": {
    "ApiKey": "<secreto-compartido-con-forge>",
    "LayoutStoragePath": "/var/lib/paqsuite/dx-reporting/layouts",
    "SchemaStoragePath": "/var/lib/paqsuite/dx-reporting/schemas"
  }
}
```

Variables adicionales:

```bash
ASPNETCORE_ENVIRONMENT=Production
ASPNETCORE_URLS=http://127.0.0.1:5055
# DevExpress_License=<si aplica en Linux>
```

### 3.3 Servicio systemd

Archivo: `/etc/systemd/system/paqsuite-dx-reporting.service`

```ini
[Unit]
Description=PaqSuite DevExpress Reporting sidecar (GEN-15)
After=network.target

[Service]
WorkingDirectory=/var/www/paqsuite-dx-reporting
ExecStart=/usr/bin/dotnet /var/www/paqsuite-dx-reporting/PaqSuite.DxReporting.dll
Restart=always
RestartSec=5
User=forge
Environment=ASPNETCORE_ENVIRONMENT=Production
Environment=ASPNETCORE_URLS=http://127.0.0.1:5055

[Install]
WantedBy=multi-user.target
```

Activación y smoke:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now paqsuite-dx-reporting
curl -s http://127.0.0.1:5055/health
# Esperado: {"status":"ok","service":"paqsuite-dx-reporting"}
```

### 3.4 Nginx (subdominio compartido)

Crear sitio con TLS, por ejemplo **`https://dx-reporting.paqsystems.com`**:

```nginx
server {
    listen 443 ssl http2;
    server_name dx-reporting.paqsystems.com;

    location / {
        proxy_pass http://127.0.0.1:5055;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 32m;
        proxy_read_timeout 120s;
    }
}
```

Rutas que debe servir:

| Ruta | Uso |
|------|-----|
| `GET /health` | Healthcheck |
| `POST /api/v1/dx-reporting/render` | Laravel → PDF/Excel/CSV |
| `POST /DXXRD/GetDesignerModel` | Browser → diseñador |
| `/DXXRDV`, `/DXXQB` | Viewer / Query Builder |

### 3.5 CORS (obligatorio para Vercel)

El sidecar en Framework hoy solo permite `localhost`. En producción extender orígenes de **cada FE** desplegado, por ejemplo en `Program.cs`:

```csharp
policy.WithOrigins(
    "https://partesatencionpaqsystems.vercel.app",
    "https://partesatencionpaqsystemsdev.vercel.app",
    "https://demo.partesatencion.paqsystems.com",
    "https://paq.partesatencion.paqsystems.com"
    // agregar dominios de cada producto
)
.AllowAnyHeader()
.AllowAnyMethod();
```

**Recomendación transversal:** lista en `appsettings.Production.json` → `PaqSuite:AllowedOrigins[]` para no recompilar al sumar un producto.

### 3.6 Catálogo multi-producto en el sidecar

Por cada host que adopte GEN-15:

1. Copiar schema:  
   `services/dx-reporting/.../data/schemas/{reportCode}.json` →  
   `/var/lib/paqsuite/dx-reporting/schemas/`
2. Layouts iniciales (opcional): `*.repx` → `/var/lib/paqsuite/dx-reporting/layouts/`
3. Usar **`reportUrl` estable** (código de reporte, no ID numérico del catálogo GEN)

Pipeline sugerido: script `deploy-dx-schemas.sh` en Framework, invocado al cerrar TR GEN-15 de cada producto.

### 3.7 Seguridad

| Endpoint | Autenticación |
|----------|----------------|
| `/render` | `PaqSuite:ApiKey` → header `Authorization: Bearer` (desde `PAQSUITE_EMISSION_DX_API_KEY` en Laravel) |
| `/DXXRD*` | Sin API key (browser); mitigar con TLS, rate-limit, no divulgar URL innecesariamente |
| Transporte | TLS obligatorio en URL pública |

---

## 4. Configuración Backend (Forge) — por producto

### 4.1 Variables `.env` (Environment Forge)

```env
# Motor documental real (reemplaza stub / MinimalDx)
PAQSUITE_EMISSION_DX_MODE=remote

# Misma VM que el sidecar (recomendado si BE y sidecar comparten host):
PAQSUITE_EMISSION_DX_SERVICE_URL=http://127.0.0.1:5055/api/v1/dx-reporting

# BE en paq-2021 y sidecar en VM Gateway separada:
# PAQSUITE_EMISSION_DX_SERVICE_URL=https://dx-reporting.paqsystems.com/api/v1/dx-reporting

PAQSUITE_EMISSION_DX_API_KEY=<mismo-secreto-que-PaqSuite:ApiKey-del-sidecar>
PAQSUITE_EMISSION_DX_TIMEOUT=60
```

Tras cambiar `.env`:

```bash
php artisan config:cache
php artisan queue:restart
```

### 4.2 Binding PHP (`RemoteDxReportingEngine`)

Patrón de referencia: `PaqSuite-IA-FRAMEWORK/apps/smoke-backend/app/Providers/CapabilitiesServiceProvider.php`

- `PAQSUITE_EMISSION_DX_MODE=remote` → `RemoteDxReportingEngine`
- `fake` / ausente → stub local (CI y tests)

**Gap Partes (2026-09-18):** `AppServiceProvider` registra `MinimalDxReportingEngine` fijo; hay que alinear al patrón condicional del smoke antes de producción remota.

Config Framework (template): `config/paqsuiteCapabilities.php` → `emissions.dxReporting.*`

### 4.3 Adopción GEN-15 por producto (checklist)

| Ítem | Acción |
|------|--------|
| Parámetro `EmissionEnabled=S` | Seed / pantalla parámetros |
| Procesos emisibles | Seed `pq_emission_processes` |
| Puertos dataset | Registrar en `EmissionDatasetPortRegistry` |
| Permiso diseñador | `emission.design` en roles correspondientes |
| SP GEN-15 | Desplegar `pq_sp_emission_*` si aplica |
| Schema sidecar | Publicar JSON en VM Gateway |

### 4.4 Smoke post-deploy BE

```bash
curl -s https://backend.<proyecto>.paqsystems.com/api/v1/health

# API design debe indicar designer=dx (no stub) cuando mode=remote
curl -s -H "Authorization: Bearer <token>" \
  https://backend.<proyecto>.paqsystems.com/api/v1/emissions/design/processes/<processCode>/reports

# Emitir job PDF y descargar artefacto
```

---

## 5. Configuración Frontend (Vercel) — por producto

### 5.1 Variables de entorno Vercel

| Variable | Production | Preview / develop |
|----------|------------|-------------------|
| `VITE_API_BASE_URL` | URL Forge prod del BE | URL Forge dev |
| `VITE_DEVEXTREME_LICENSE_KEY` | LCP DevExtreme **26.1** | Igual |
| `VITE_DX_REPORTING_HOST` | URL del sidecar o estrategia proxy (ver §5.2) | Idem dev |

Ver también: [`frontend-api-base-url-y-env.md`](./frontend-api-base-url-y-env.md) · `frontend/.env.example`

**Importante:** cambios en variables Vite requieren **redeploy** (se embeben en build).

### 5.2 Conexión al diseñador — dos modos

#### Opción A (recomendada): rewrites same-origin en Vercel

El browser llama al origin del FE; Vercel reenvía al sidecar. Compatible con `readDxReportingConfig` (same-origin + proxy, como Vite en dev).

Agregar en `frontend/vercel.json` **antes** del rewrite SPA catch-all:

```json
{
  "rewrites": [
    { "source": "/DXXRD/:path*", "destination": "https://dx-reporting.paqsystems.com/DXXRD/:path*" },
    { "source": "/DXXRDV/:path*", "destination": "https://dx-reporting.paqsystems.com/DXXRDV/:path*" },
    { "source": "/DXXQB/:path*", "destination": "https://dx-reporting.paqsystems.com/DXXQB/:path*" },
    { "source": "/(.*)", "destination": "/index.html" }
  ]
}
```

Con rewrites activos, `VITE_DX_REPORTING_HOST` puede apuntar al sidecar o dejarse configurado para que el cliente use el origin del FE (comportamiento equivalente al proxy Vite).

#### Opción B: directo cross-origin

```env
VITE_DX_REPORTING_HOST=https://dx-reporting.paqsystems.com
VITE_DX_REPORTING_DIRECT=true
```

Requiere CORS correcto en el sidecar (§3.5).

### 5.3 Checklist FE por producto

| Paso | Detalle |
|------|---------|
| Montar diseñador | `EmissionReportDesignerPage` + `renderDesigner` → panel host (Partes: `DxReportDesignerPanel`) |
| i18n | Claves `emissions.designer.*` |
| Mobile | Diseñador excluido (`isNative`) |
| Redeploy | Tras cambiar env |
| Smoke | Ruta diseñador (ej. `/emisiones/disenador`) — no debe mostrar `hostMissing` |

---

## 6. Orden de implementación

| # | Tarea | Responsable |
|---|--------|-------------|
| 1 | Confirmar specs VM Gateway; upgrade RAM/disco si aplica | Infra |
| 2 | Publicar sidecar + systemd + Nginx + `/health` | Infra |
| 3 | Extender CORS + `PaqSuite:ApiKey` + paths layouts/schemas | Framework / sidecar |
| 4 | Desplegar schemas por producto | Cada host |
| 5 | Forge `.env` remote + binding `RemoteDxReportingEngine` | Cada BE |
| 6 | Vercel env + rewrites `DXXRD*` | Cada FE |
| 7 | Smoke E2E: diseñador + PDF real | QA |
| 8 | Actualizar runbook Framework § producción AWS | Docs |

---

## 7. Estado Partes de Atención (referencia)

| Pieza | Estado |
|-------|--------|
| UI diseñador (`DxReportDesignerPanel`, proxy Vite local) | Implementado |
| API emisiones `/api/v1/emissions/*` | Implementado |
| `MinimalDxReportingEngine` en prod | **Pendiente** → `remote` |
| Variables `VITE_DX_REPORTING_*` en Vercel | **Pendiente** |
| Sidecar en AWS | **Pendiente** |
| CORS producción en sidecar | **Pendiente** (solo localhost en código actual) |

---

## 8. Comparativa de costos

| Modelo | Costo infra adicional | Notas |
|--------|----------------------|-------|
| Sidecar en VM Gateway existente | ~$0 (+ posible upgrade RAM) | Decisión actual |
| Sidecar en `paq-2021` con 17 PHP | Upgrade EC2 ~$15–30/mes | Riesgo de contención |
| Sidecar dedicado `t3.medium` | ~$30/mes | Aislamiento |
| srv-pq (Tailscale) | N/A como runtime | Registry SDK Satis/Verdaccio, no sidecar Reporting |

---

## 9. Referencias

| Documento | Ubicación |
|-----------|-----------|
| Runbook lab sidecar | `PaqSuite-IA-FRAMEWORK/docs/06-operacion/runbook-dx-reporting-sidecar.md` |
| README sidecar | `PaqSuite-IA-FRAMEWORK/services/dx-reporting/README.md` |
| URL API FE | [`frontend-api-base-url-y-env.md`](./frontend-api-base-url-y-env.md) |
| Deploy SDK | [`deploy-sdk-package-repos.md`](./deploy-sdk-package-repos.md) |
| Producto GEN-15 Partes | `docs/02-producto/Sistema-Partes-IA/15-reportes-emisiones.md` |
| Config FE ejemplo | `frontend/.env.example` |
| Config DX runtime | `frontend/src/features/partes/informes/dxReportingConfig.ts` |

---

## 10. Smoke mínimo post-cutover

1. `GET https://dx-reporting.paqsystems.com/health` → `status: ok`
2. Login FE → abrir diseñador → Field List carga sin error de host
3. Guardar layout → archivo `.repx` en `/var/lib/paqsuite/dx-reporting/layouts/`
4. Emitir PDF desde consulta → BE llama `/render` → descarga artefacto válido
5. Repetir con segundo producto (mismo sidecar, distinto `processCode`/schema)
