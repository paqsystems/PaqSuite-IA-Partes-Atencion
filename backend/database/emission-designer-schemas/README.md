# Schemas diseñador emisiones (GEN-15 / GEN-34)

JSON **canónico v1** desplegado en DIC vía manifiesto `kind: emissionDesignerSchema`.

- Un archivo `{processCode}.json` por proceso emisible con diseñador DX.
- Opcional `{processCode}.port-names.json` para validación V-EM-04 contra el puerto PHP.

Regenerar manifiesto: `php tools/build-installation-manifest.php` (desde `backend/`).

SoT adopción: PaqSuite-IA-FRAMEWORK `docs/06-operacion/adopcion-gen-15-emission-designer-schema-host.md`.
