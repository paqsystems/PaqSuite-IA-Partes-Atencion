# Ops — piloto IA diseño informes (TR-015 oleada 1)

Sidecar Framework + host Partes. SoT técnico IA/env: `PaqSuite-IA-FRAMEWORK/docs/06-operacion/runbook-dx-reporting-sidecar.md` §6.

## Laravel (sin cambios Must)

```env
PAQSUITE_EMISSION_DX_MODE=remote
PAQSUITE_EMISSION_DX_SERVICE_URL=https://<sidecar-host>/api/v1/dx-reporting
```

**No** poner `OPENAI_API_KEY` ni claves Azure en `backend/.env` para el piloto (BYOK sidecar, no GEN-16).

## Sidecar (Forge / lab)

1. Desplegar build de `PaqSuite-IA-FRAMEWORK/services/dx-reporting` (26.1.4 + NuGet IA).
2. En el **Environment del sitio/proceso sidecar** (no Laravel):

   OpenAI lab:

   ```env
   OPENAI_API_KEY=<secret>
   OPENAI_MODEL=gpt-4o-mini
   ```

   O Azure OpenAI: `AZURE_OPENAI_ENDPOINT`, `AZURE_OPENAI_API_KEY`, `AZURE_OPENAI_DEPLOYMENT_NAME`.

3. Puerto típico lab: `:5055`. Health: `GET /health`.
4. Sin credenciales LLM: sidecar OK, diseñador clásico; sin wizard Prompt-to-Report (log stderr).

## Frontend Partes

- Pin `devexpress-reporting*` **26.1.4** (`frontend/package.json`).
- `developmentMode` del widget DX solo en `import.meta.env.DEV` (`DxReportDesignerApp.tsx`).
- Proxy Vite `/DXXRD` → sidecar (igual TR-011).

## Smoke mínimo post-deploy

1. Usuario con permiso `emission.design`.
2. Abrir diseñador Consulta detallada → Field List con 14 campos.
3. Con LLM en sidecar: wizard IA visible; prompt ES; **Save** persiste REPX vía callbacks GEN.
4. Emitir PDF (regresión TR-011).

Verificación formal: batería TR-015 §4.4 → [`guia-bateria-manual-tr-015-ia-diseno.md`](./guia-bateria-manual-tr-015-ia-diseno.md) + [`D-VERIFICACION-TR-015-*.md`](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-015-2026-10-30.md).
