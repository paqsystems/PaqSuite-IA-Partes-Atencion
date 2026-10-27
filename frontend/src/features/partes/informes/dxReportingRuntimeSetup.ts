/**
 * DevExpress Reporting usa `import * as ko from 'knockout'` y llama `ko.observable`.
 * El plugin de Vite reescribe ese import a default y envuelve el UMD; este setup
 * fija `globalThis.ko` / `ace` antes de montar el widget.
 */
// Subpath sin .d.ts en ace-builds; Reporting solo necesita el runtime en globalThis.
// @ts-expect-error ace-builds/src-noconflict/ace no declara tipos
import ace from 'ace-builds/src-noconflict/ace'
import * as ko from 'knockout'

if (typeof ko.observable !== 'function') {
  throw new Error('Knockout observable is not a function')
}

type ReportingGlobals = typeof globalThis & {
  ko?: typeof ko
  ace?: typeof ace
}

const g = globalThis as ReportingGlobals
g.ko = ko
g.ace = ace

export { ace, ko }
