/**
 * DevExpress Reporting usa `import * as ko from 'knockout'` y llama `ko.observable`.
 * El shim de Vite debe dejar named exports; este setup fija `globalThis.ko` / `ace`
 * antes de montar el widget (si no, `(void 0) is not a function`).
 */
// Subpath sin .d.ts en ace-builds; Reporting solo necesita el runtime en globalThis.
// @ts-expect-error ace-builds/src-noconflict/ace no declara tipos
import ace from 'ace-builds/src-noconflict/ace'
import koDefault from 'knockout'
import * as koNs from 'knockout'

type KnockoutRuntime = typeof koDefault & { observable?: unknown }

function resolveKnockout(): KnockoutRuntime {
  const fromDefault = koDefault as KnockoutRuntime | undefined
  const fromNs = koNs as KnockoutRuntime
  if (typeof fromDefault?.observable === 'function') {
    return fromDefault
  }
  if (typeof fromNs.observable === 'function') {
    return fromNs
  }
  const fromGlobal = (globalThis as { ko?: KnockoutRuntime }).ko
  if (typeof fromGlobal?.observable === 'function') {
    return fromGlobal
  }
  throw new Error('Knockout observable is not a function')
}

const ko = resolveKnockout()

type ReportingGlobals = typeof globalThis & {
  ko?: typeof ko
  ace?: typeof ace
}

const g = globalThis as ReportingGlobals
g.ko = ko
g.ace = ace

export { ace, ko }
