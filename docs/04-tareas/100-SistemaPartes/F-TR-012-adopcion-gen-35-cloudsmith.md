# F — Verificación documental TR-012 (openspec-05)

Fecha: 2026-10-09 · Tras F1 y deploy Forge/Vercel

| Campo | Valor |
|-------|--------|
| **TR** | [TR-012-adopcion-gen-35-cloudsmith](./TR-012-adopcion-gen-35-cloudsmith.md) |
| **HU** | [HU-012-adopcion-gen-35-cloudsmith](../../03-historias-usuario/100-SistemaPartes/HU-012-adopcion-gen-35-cloudsmith.md) |
| **SPEC** | [SPEC-012-adopcion-gen-35-cloudsmith](../../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) |
| **F1** | [F1-TR-012](./F1-TR-012-adopcion-gen-35-cloudsmith.md) — Aprobado con observaciones (antes del deploy) |
| **Resultado F** | **Aprobado con observaciones** |

## Alcance contrastado

Consumo SDK Host: Satis + Verdaccio → Cloudsmith. Sin UI, sin GHA nuevos, sin publicación.

## Matriz CA (SPEC §5 / HU CA-01…10)

| CA | Veredicto | Evidencia |
|----|-----------|-----------|
| CA-01 Composer Cloudsmith + pin `1.3.13-beta.1` + lock | **OK** | `backend/composer.json` URL sin `/basic/`, `secure-http: true`; lock dist `dl.cloudsmith.io/basic/...` |
| CA-02 npm pin `2.4.24-beta.1` + tarball Cloudsmith | **OK** | `package.json` + lock `npm.cloudsmith.io/.../react-core-2.4.24-beta.1.tgz` |
| CA-03 `.npmrc` + `${CLOUDSMITH_READ_TOKEN}` | **OK** | Sin valor literal |
| CA-04 `vercel-install.sh` exige token | **OK** (código) | Script sale si falta env; no se ejecutó bash en Windows sin Git Bash |
| CA-05 Forge `composer show` pin + Cloudsmith | **OK** | Deploy **79758818** *finished*; `versions : * 1.3.13-beta.1`; dist zip Cloudsmith |
| CA-06 Vercel Preview `@paqsuite` Cloudsmith | **OK** | Check Vercel del PR #55 **SUCCESS** / Preview Ready (falla sin token) |
| CA-07 Health sin Tailscale para paquetes | **OK** (health) | Forge `/up` HTTP 200; Preview host 200. Login interactivo no caminado en esta F |
| CA-08 Sin write key / token en git | **OK** | Grep: `CLOUDSMITH_API_KEY` solo en docs de prohibición; `auth.json` gitignored |
| CA-09 Sin GHA nuevos | **OK** | No se añadieron workflows de install |
| CA-10 Docs legado Satis/Verdaccio | **OK** | Instructivo + `deploy-sdk-package-repos.md` + Verdaccio legado |

## Código vs TR

- `backend/scripts/forge-composer-install.sh`: token; lock Cloudsmith; `auth.json` bearer + http-basic `dl.cloudsmith.io`; **lee `.env`** si Forge no exporta la variable al bash.
- Deploy Script del sitio Forge (ops, no git): exporta el token desde `.env` del release.
- `frontend/scripts/vercel-install.sh` + refresh lock: Cloudsmith.

## Tests (Parte E, no repetidos en F)

| Qué | Resultado |
|-----|-----------|
| `php artisan test` | 104 passed, 7 skipped |
| Vitest | 142 passed; 2 timeouts DX en suite completa |
| Playwright | No (TR ops, sin UI nueva) |

## Observaciones no bloqueantes

1. Composer avisa 404 de `packages.json` en `composer.cloudsmith.io`; el zip del lock se descarga igual.
2. `VERDACCIO_AUTH_TOKEN` en Vercel: borrar **después** de producción (HU R-CS-04).
3. Rotar `CLOUDSMITH_READ_TOKEN` si quedó visible en capturas de Forge Environment.
4. El commit del fallback `.env` en el script va **después** del merge #55; Forge-dev ya quedó operativo por el Deploy Script.

## Parte I (esta corrida)

No hay `HU-012`/`TR-012`/`SPEC-012` *update* en `docs/.../updates/`. Nada que fusionar. Cierre de originales a **Finalizado** a pedido del usuario (Parte I + cierre F).

## Veredicto

Implementación alineada a SPEC/HU/TR. Puede cerrarse el ciclo A→F de SPEC-012. Residual ops: R-CS-04 (Verdaccio en Vercel) y rotación de token si hubo exposición.
