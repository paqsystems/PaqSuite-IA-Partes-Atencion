# Schemas diseñador emisiones (GEN-15 + GEN-37 / GEN-34)

JSON **canónico v1** desplegado en DIC vía manifiesto `kind: emissionDesignerSchema`.

- Un archivo `{processCode}.json` por proceso emisible con diseñador DX.
- Opcional `{processCode}.port-names.json` para validación V-EM-04 contra el puerto PHP.
- **GEN-37:** cada `fields[]` Must incluir `label` (ES fallback). Si el proceso usa IA diseño: `description` + `synonyms` Must. `captionKey` recomendado (i18n host; no mezclar con captions GEN-14 Excel).
- Prohibido inventar un catálogo semántico paralelo o hardcodear labels del producto en el sidecar.

Regenerar manifiesto: `php tools/build-installation-manifest.php` (desde `backend/`).

SoT:

- Framework `docs/02-producto/37-catalogo-semantico-campos-dataset.md` · SPEC-001-37
- Adopción Field List: `docs/06-operacion/adopcion-gen-15-emission-designer-schema-host.md`
