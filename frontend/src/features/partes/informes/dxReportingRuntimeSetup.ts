/**
 * DevExpress Reporting (diseñador) espera `knockout` y `ace` como módulos CJS con default.
 * En build de producción (Vite/Rolldown) el shim de vite.config puede dejar `export default undefined`
 * si no hay `globalThis.ko` / `globalThis.ace` aún — rompe con `(void 0) is not a function` y tumba React.
 * Este archivo debe importarse antes que `devexpress-reporting-react`.
 */
// Subpath sin .d.ts en ace-builds; Reporting solo necesita el runtime en globalThis.
// @ts-expect-error ace-builds/src-noconflict/ace no declara tipos
import ace from 'ace-builds/src-noconflict/ace'
import ko from 'knockout'

type ReportingGlobals = typeof globalThis & {
  ko?: typeof ko
  ace?: typeof ace
}

const g = globalThis as ReportingGlobals
g.ko = ko
g.ace = ace

export { ace, ko }
