# Frontend — URL del backend y variables `.env`

Fecha: 2026-08-04  
Producto: Partes de Atención (MONO) · FE Vite + `@paqsuite/react-core`

**Contrato SDK (todos los productos):** Framework  
`PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-api-base-url.md`  
API: `bootstrapApiBaseUrl` · variable `VITE_API_BASE_URL`.

---

## Resumen

Solución canónica Framework (**opción B**): en builds remotos, `VITE_API_BASE_URL` absoluta al BE (Forge) + `bootstrapApiBaseUrl` en el arranque del FE.

En Partes, `frontend/src/main.tsx` ya llama:

```ts
await bootstrapApiBaseUrl({
  envBaseUrl: import.meta.env.VITE_API_BASE_URL,
  projectSlug: 'partesatencion',
  isNative: isNativeApp(),
})
```

- **Local:** no setear `VITE_API_BASE_URL` → same-origin + proxy Vite (`VITE_API_PROXY_TARGET`).
- **Vercel:** setear `VITE_API_BASE_URL` por ambiente → `apiRequest` pega a Forge.

---

## Topología de deploy

| Rol | Plataforma | Ambiente | Rama | URL canónica |
|-----|------------|----------|------|--------------|
| Frontend | Vercel | Operativo | `main` | `https://partesatencionpaqsystems.vercel.app` |
| Frontend | Vercel | Desarrollo | `develop` | `https://partesatencionpaqsystemsdev.vercel.app` |
| Backend | Laravel Forge | Operativo | `main` | `https://backend.partesatencion.paqsystems.com` |
| Backend | Laravel Forge | Desarrollo | `develop` | `https://backenddev.partesatencion.paqsystems.com` |

### Multidominio (clientes → FE)

| Custom domain | FE | Ambiente | `VITE_API_BASE_URL` esperado |
|---------------|-----|----------|------------------------------|
| `desarrollo.partesatencion.paqsystems.com` | `*dev.vercel.app` | develop | `https://backenddev.partesatencion.paqsystems.com` |
| `demo.partesatencion.paqsystems.com` | operativo Vercel | prod | `https://backend.partesatencion.paqsystems.com` |
| `paq.partesatencion.paqsystems.com` | operativo Vercel | prod | `https://backend.partesatencion.paqsystems.com` |

Si el panel hace **HTTP redirect** a `*.vercel.app` (no CNAME), el path debe conservar `?cliente={CODIGO}` (ej. `ESTUDIOGB`). El pie de shell muestra `pq_empresa.nombre` de la BD de ese código: hace falta fila `EMPRESAS_CONEXION` (`proyecto=partesatencion`, `cliente=ESTUDIOGB`) y que esa BD no sea la de DEMO.

---

## Variables

| Variable | Ámbito | Uso |
|----------|--------|-----|
| **`VITE_API_BASE_URL`** | Build Vercel / CI | URL absoluta Forge. Vacío en local. |
| **`VITE_API_PROXY_TARGET`** | Solo `npm run dev` | Proxy Vite `/api` → backend local. |
| `VITE_APP_VERSION` | UI | Versión |
| `VITE_DEVEXTREME_LICENSE` | Runtime | Licencia DX |
| `VITE_AUTH_*` | Build | Hero login — no API |

Ver `frontend/.env.example`.

### Vercel — Environment Variables

| Proyecto / env Vercel | Valor |
|-----------------------|--------|
| Production (`main`) | `VITE_API_BASE_URL=https://backend.partesatencion.paqsystems.com` |
| Preview / develop | `VITE_API_BASE_URL=https://backenddev.partesatencion.paqsystems.com` |

Tras cambiar la var: **redeploy** (Vite la embebe en build).

---

## Comportamiento del cliente

1. `bootstrapApiBaseUrl` (web): si env es `https://…` → cachea base; si no → cache null.
2. `apiRequest('/api/v1/...')` → `resolveRequestUrl` une path con la base si hay cache.
3. Native: Preferences override → env → fallback `backend.{projectSlug}`.

---

## CORS

Con base absoluta, el browser hace cross-origin FE→Forge. El `backend/config/cors.php` de Partes permite `*`; si se restringe, listar dominios Vercel + custom.

---

## Chequeo rápido

### Local

1. Sin `VITE_API_BASE_URL` en `.env`.
2. Backend en puerto de `VITE_API_PROXY_TARGET`.
3. Network: `http://127.0.0.1:3000/api/v1/...`.

### Deploy

1. Var seteada en Vercel del ambiente.
2. Abrir p. ej. `https://demo.partesatencion.paqsystems.com`.
3. Network: requests a `https://backend.partesatencion.paqsystems.com/api/v1/...`.

---

## Referencias

| Pieza | Ruta |
|-------|------|
| Bootstrap host | `frontend/src/main.tsx` |
| SDK | `@paqsuite/react-core` → `bootstrapApiBaseUrl` |
| Adopción Framework | `PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-api-base-url.md` |
| Proxy local | `frontend/vite.config.ts` |
