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
export const JQUERY_VIRTUAL_ID = '\0paq-jquery-esm'

export function isJqueryModuleId(id: string): boolean {
  const file = id.split('?')[0].replaceAll('\\', '/')
  return (
    file === 'jquery' ||
    file === JQUERY_VIRTUAL_ID ||
    file.endsWith('/jquery/dist/jquery.js')
  )
}

export function rewriteJqueryNamespaceImport(code: string): string {
  return code.replace(
    /\bimport\s+\*\s+as\s+([A-Za-z_$][\w$]*)\s+from\s+(['"])jquery\2/g,
    'import $1 from $2jquery$2',
  )
}

export function wrapJqueryUmdAsDefaultEsm(umdSource: string): string {
  // El UMD de jQuery asigna `module.exports`. En Vitest/Node (módulo ESM) `module` puede
  // existir en el scope externo con `exports` de solo lectura → "Cannot set property default…".
  // Cargamos el UMD dentro de un IIFE con `module`/`exports` locales (mismo patrón que knockout).
  return `'use strict';
function __paqLoadJQuery() {
  var module = { exports: {} };
  var exports = module.exports;
${umdSource}
  var jq = module.exports;
  // jQuery es una función (factory); el branch CJS asigna esa función a module.exports.
  if (jq != null && typeof jq.Deferred === 'function') {
    return jq;
  }
  if (
    jq != null &&
    typeof jq === 'object' &&
    typeof jq.default === 'function' &&
    typeof jq.default.Deferred === 'function'
  ) {
    return jq.default;
  }
  var w = typeof window !== 'undefined' ? window : globalThis;
  return w.jQuery;
}
const __jquery = __paqLoadJQuery();
if (typeof __jquery === 'undefined' || typeof __jquery.Deferred !== 'function') {
  throw new Error('jQuery Deferred is not a function');
}
if (typeof globalThis !== 'undefined') {
  globalThis.jQuery = __jquery;
  globalThis.$ = __jquery;
}
export default __jquery;
`
}

function jqueryUmdPath(): string {
  const require = createRequire(import.meta.url)
  return require.resolve('jquery/dist/jquery.js')
}

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
    name: 'reporting-knockout-jquery-default-esm',
    enforce: 'pre',
    resolveId(id) {
      if (isKnockoutModuleId(id) && id !== KNOCKOUT_VIRTUAL_ID) {
        return KNOCKOUT_VIRTUAL_ID
      }
      if (isJqueryModuleId(id) && id !== JQUERY_VIRTUAL_ID) {
        return JQUERY_VIRTUAL_ID
      }
      return null
    },
    load(id) {
      if (id === KNOCKOUT_VIRTUAL_ID) {
        return wrapKnockoutUmdAsDefaultEsm(fs.readFileSync(knockoutUmdPath(), 'utf8'))
      }
      if (id === JQUERY_VIRTUAL_ID) {
        return wrapJqueryUmdAsDefaultEsm(fs.readFileSync(jqueryUmdPath(), 'utf8'))
      }
      return null
    },
    transform(code, id) {
      if (id === KNOCKOUT_VIRTUAL_ID || id === JQUERY_VIRTUAL_ID) {
        return null
      }
      let next = code
      if (code.includes('knockout')) {
        next = rewriteKnockoutNamespaceImport(next)
      }
      if (code.includes('jquery')) {
        next = rewriteJqueryNamespaceImport(next)
      }
      if (next === code) {
        return null
      }
      return { code: next, map: null }
    },
  }
}
