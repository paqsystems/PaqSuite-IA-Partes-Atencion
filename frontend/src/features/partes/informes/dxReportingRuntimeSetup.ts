/**
 * DevExpress Reporting usa `import * as ko from 'knockout'` y llama `ko.observable`.
 * El plugin de Vite reescribe ese import a default y envuelve el UMD; este setup
 * fija `globalThis.ko` / `ace` antes de montar el widget.
 */
import ace from 'ace-builds/src-noconflict/ace'
import config from 'devextreme/core/config'
import $ from 'jquery'
import * as ko from 'knockout'

if (typeof ko.observable !== 'function') {
  throw new Error('Knockout observable is not a function')
}

if (typeof $.Deferred !== 'function') {
  throw new Error('jQuery Deferred is not a function')
}

config({ useJQuery: true })

type ReportingGlobals = typeof globalThis & {
  ko?: typeof ko
  ace?: typeof ace
  jQuery?: typeof $
  $?: typeof $
}

const g = globalThis as ReportingGlobals
g.ko = ko
g.ace = ace
g.jQuery = $
g.$ = $

export { ace, ko, $ }
