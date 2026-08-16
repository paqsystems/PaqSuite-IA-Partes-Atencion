# Gaps Framework pendientes — cutover `1.2.0-FINAL`

Fecha: 2026-08-14 · Actualizado: 2026-08-15  
Contexto: migración Partes a consumo Satis + Verdaccio (`laravel-core@^1.3.3`, `react-core@2.2.1`) según plan rediseño SDK paquetes.

Ítems que **no se pudieron transformar del todo** (o quedan como deuda) porque la **infra** de publicación / red aún no cubre el caso.

---

## Tabla de gaps

| Ítem / capacidad | Por qué no se transformó | Evidencia | Workaround temporal en Partes | Seguimiento |
|------------------|--------------------------|-----------|-------------------------------|-------------|
| ~~**Corpus GEN**~~ | **Cerrado en Framework** (`laravel-core` **1.3.3**): `resources/manual-usuario-gen/` + `GenManualDocsRoot`; sync `tools/sdk/sync-gen-manual-corpus.ps1` | Paquete + config Partes `chat_assistant_corpus.php` | Hasta publicar Satis 1.3.3: path local de lab o quedarse en 1.3.2 sin GEN | Tag `php-laravel-core-v1.3.3` + rebuild Satis; luego `composer update` en Partes |
| ~~**Templates Framework**~~ | **Cerrado**: `docs/06-operacion/templates/*` + template `create-app` → Satis/Verdaccio (`^1.3.3` / `^2.2.1`); CLI bump **0.1.8** | templates + `tools/template` | Publicar `@paqsuite/create-app@0.1.8` en Verdaccio | Publish Verdaccio |
| **Vercel → Verdaccio (Tailscale)** | `100.110.69.93` no es alcanzable desde builders Vercel públicos sin subnet router / CI con Tailscale | Red; plan prohíbe volver a `git+https` | Build local/CI con Tailscale que publique `dist`, o habilitar Verdaccio alcanzable desde Vercel | Infra: subnet router Tailscale en Vercel o pipeline prebuild |
| **Forge → Satis** | Mismo requisito de red a `srv-pq` en el server Forge | Deploy Script `composer install` | Forge en VPN/Tailscale, o CI que suba release con `vendor/` | Infra Forge + Tailscale / artefact CI |
| **SP SQL canónicos** desde monorepo vs vendor | Parte de la doc histórica apuntaba a `PaqSuite-IA-FRAMEWORK/packages/.../sp`; el paquete Satis **sí** incluye `vendor/paqsuite/laravel-core/database/sp/` | Verificado en install 1.3.2 | Usar scripts bajo `vendor/paqsuite/laravel-core/database/sp/` (doc Partes actualizada) | Ninguno bloqueante si se usa vendor |
| **Auth Verdaccio** (`authorization required` en metadata HTTP cruda) | Algunos endpoints Verdaccio piden auth; `npm view`/`npm install` anónimo funcionó para `@paqsuite/react-core@2.2.1` | `curl` a `/:package` → 401; `npm install` OK | Ninguno hoy | Framework/ops: documentar si publish/install requiere token en CI |
| **GEN-13 Tareas / GEN-23 Grupos** (guía actualización create-app) | Fuera de alcance MONO Partes en esta oleada (opt-in) | `GUIA_ACTUALIZACION_PROYECTO` pasos 2/6 multi | No adoptado | Producto: decidir adopción en épica aparte |

---

## Lo que sí quedó transformado

- `composer.json` → Satis + `^1.3.2` (acepta **1.3.3** cuando Satis lo publique; hoy lock **1.3.2** + overlay local de corpus en lab).
- `package.json` + `.npmrc` → Verdaccio + **2.2.1**.
- Sin contrato `git+https` / VCS GitHub / path monorepo en manifests.
- `forge-ensure-framework.sh` deprecado (exit 1).
- Corpus GEN: default vía `GenManualDocsRoot` cuando el paquete trae `resources/manual-usuario-gen` (sin sibling Framework).
- Templates SDK Framework alineados a Satis/Verdaccio (`create-app` **0.1.8** pendiente publish Verdaccio).
- Docs ops + TR-008/009/010 + D1-010 + SPEC-009 (supuesto distribución).

---

## Criterio para cerrar gaps restantes

Cuando la infra de build (Forge/Vercel) tenga ruta estable a Satis/Verdaccio y esté publicado `laravel-core@1.3.3` (+ create-app 0.1.8), tachar filas de red y retirar workarounds de deploy.
