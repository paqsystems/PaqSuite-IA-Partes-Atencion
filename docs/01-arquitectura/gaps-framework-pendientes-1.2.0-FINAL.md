# Gaps Framework pendientes — cutover `1.2.0-FINAL`

Fecha: 2026-08-14 · Actualizado: 2026-08-15  
Contexto: Partes consume Satis + Verdaccio (`laravel-core@^1.3.3` lock **1.3.3**, `react-core@2.2.1`; scaffold Framework `create-app@0.1.8`).

Ítems que **siguen abiertos** por **infra de red / builder**, no por falta de paquete publicado.

---

## Tabla de gaps

| Ítem / capacidad | Estado | Evidencia | Workaround temporal en Partes | Seguimiento |
|------------------|--------|-----------|-------------------------------|-------------|
| ~~**Corpus GEN**~~ | **Cerrado** — `laravel-core@1.3.3` incluye `resources/manual-usuario-gen/` + `GenManualDocsRoot`; Partes lo usa en `chat_assistant_corpus.php` | Satis zip 1.3.3; vendor con class + markdown | — | — |
| ~~**Templates / create-app**~~ | **Cerrado** — templates Satis/Verdaccio; `@paqsuite/create-app@0.1.8` en Verdaccio | `npm view` 0.1.8 | — | — |
| **Vercel → Verdaccio (Tailscale)** | Abierto | `100.110.69.93` no alcanzable desde builders Vercel públicos | Build local/CI con Tailscale que publique `dist` | Infra: subnet router o pipeline prebuild |
| **Forge → Satis** | Abierto | Deploy Script `composer install` necesita ruta a `srv-pq` | Forge en VPN/Tailscale, o CI con `vendor/` | Infra Forge + Tailscale / artefact CI |
| **SP SQL canónicos** | OK vía vendor | `vendor/paqsuite/laravel-core/database/sp/` | Usar scripts del vendor | — |
| **Auth Verdaccio** (metadata HTTP cruda 401) | Menor | `npm install` anónimo OK para `@paqsuite/*` | Ninguno hoy | Documentar token CI si hace falta |
| **GEN-13 Tareas / GEN-23 Grupos** | Fuera de alcance MONO | Guía actualización create-app | No adoptado | Épica aparte |

---

## Lo que quedó transformado

- `composer.json` / lock → Satis + **`^1.3.3`** (lock **1.3.3**, corpus GEN en vendor).
- `package.json` + `.npmrc` → Verdaccio + **2.2.1**.
- Sin `git+https` / VCS / path monorepo en manifests.
- `forge-ensure-framework.sh` deprecado.
- Corpus GEN por `GenManualDocsRoot` (override opcional `PAQSUITE_GEN_DOCS_ROOT`).

---

## Criterio para cerrar gaps restantes

Cuando Forge/Vercel tengan ruta estable a Satis/Verdaccio (o pipeline de artefacto), tachar las filas de red.
