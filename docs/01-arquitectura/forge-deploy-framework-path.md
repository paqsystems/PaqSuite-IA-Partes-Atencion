# Deploy Forge — backend Partes + Framework path

Fecha: 2026-08-04

> **Fuente canónica (todos los productos):** Framework  
> `PaqSuite-IA-FRAMEWORK/docs/06-operacion/adopcion-forge-framework-path.md`  
> Script: `docs/06-operacion/scripts/forge-ensure-framework.sh` (copiado en este host a `backend/scripts/`).

## Resumen Partes

`paqsuite/laravel-core` se resuelve por path a `../../PaqSuite-IA-FRAMEWORK/...`. En Forge hay que ejecutar **antes** de `composer install`:

```bash
bash backend/scripts/forge-ensure-framework.sh
```

Al **armar** el sitio Forge: dar acceso git al repo Framework + pegar ese fragmento en el Deploy Script (template estable; no es un paso manual por release).

Detalle, variables y checklist: doc Framework citado arriba.
