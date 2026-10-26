import fs from 'node:fs'
import { createRequire } from 'node:module'
import type { Plugin } from 'vite'

/**
 * Rolldown (Vite 8) convierte `import * as ko from 'knockout'` en named imports.
 * El UMD de knockout no es ESM: si se le agrega `export`, el archivo pasa a ESM,
 * `module.exports` deja de existir y `ko.observable` / `ko.bindingEvent` quedan
 * undefined → `(void 0) is not a function` en el diseñador DX.
 *
 * Este plugin:
 * 1. Sirve knockout como módulo ESM con **solo** `export default` (el objeto ko).
 * 2. Reescribe `import * as ko from 'knockout'` → `import ko from 'knockout'`
 *    para que el acceso a propiedades no se convierta en named import.
 */
export const KNOCKOUT_VIRTUAL_ID = '\0paq-knockout-esm'

export function isKnockoutModuleId(id: string): boolean {
  const file = id.split('?')[0].replaceAll('\\', '/')
  return (
    file === 'knockout' ||
    file === KNOCKOUT_VIRTUAL_ID ||
    file.endsWith('/knockout/build/output/knockout-latest.js')
  )
}

export function rewriteKnockoutNamespaceImport(code: string): string {
  return code.replace(
    /\bimport\s+\*\s+as\s+([A-Za-z_$][\w$]*)\s+from\s+(['"])knockout\2/g,
    'import $1 from $2knockout$2',
  )
}

export function wrapKnockoutUmdAsDefaultEsm(umdSource: string): string {
  return `'use strict';
const __module = { exports: {} };
const __exports = __module.exports;
(function (module, exports, define) {
${umdSource}
}).call(typeof globalThis !== 'undefined' ? globalThis : undefined, __module, __exports, undefined);
const __ko = (__module.exports && typeof __module.exports.observable === 'function')
  ? __module.exports
  : (typeof globalThis !== 'undefined' ? globalThis.ko : undefined);
if (typeof __ko === 'undefined' || typeof __ko.observable !== 'function') {
  throw new Error('Knockout observable is not a function');
}
if (typeof globalThis !== 'undefined') globalThis.ko = __ko;
export default __ko;
`
}

function knockoutUmdPath(): string {
  const require = createRequire(import.meta.url)
  return require.resolve('knockout/build/output/knockout-latest.js')
}

export function reportingCjsDefaultExportPlugin(): Plugin {
  return {
    name: 'reporting-knockout-default-esm',
    enforce: 'pre',
    resolveId(id) {
      if (isKnockoutModuleId(id) && id !== KNOCKOUT_VIRTUAL_ID) {
        return KNOCKOUT_VIRTUAL_ID
      }
      return null
    },
    load(id) {
      if (id !== KNOCKOUT_VIRTUAL_ID) {
        return null
      }
      return wrapKnockoutUmdAsDefaultEsm(fs.readFileSync(knockoutUmdPath(), 'utf8'))
    },
    transform(code, id) {
      if (id === KNOCKOUT_VIRTUAL_ID || !code.includes("from") || !code.includes('knockout')) {
        return null
      }
      const next = rewriteKnockoutNamespaceImport(code)
      if (next === code) {
        return null
      }
      return { code: next, map: null }
    },
  }
}
