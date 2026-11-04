/**
 * DevExpress Reporting importa `ace` como default ESM; el UMD de ace-builds no expone default en Vite 8.
 * Cargamos el script real vía alias interno y reexportamos `globalThis.ace`.
 */
import 'ace-builds-internal/ace.js'

type AceGlobal = typeof globalThis & { ace?: unknown }
const ace = (globalThis as AceGlobal).ace
if (!ace) {
  throw new Error('Ace editor runtime is not available on globalThis')
}

export default ace
