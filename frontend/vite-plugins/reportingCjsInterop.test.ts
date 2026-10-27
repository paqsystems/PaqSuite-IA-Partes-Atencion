import { describe, expect, it } from 'vitest'
import {
  isKnockoutModuleId,
  rewriteKnockoutNamespaceImport,
  wrapKnockoutUmdAsDefaultEsm,
} from './reportingCjsInterop'

describe('reportingCjsInterop', () => {
  it('detecta el entry UMD de knockout', () => {
    expect(isKnockoutModuleId('knockout')).toBe(true)
    expect(
      isKnockoutModuleId('/app/node_modules/knockout/build/output/knockout-latest.js'),
    ).toBe(true)
    expect(isKnockoutModuleId('/app/src/features/partes/informes/DxReportDesignerApp.tsx')).toBe(
      false,
    )
  })

  it('reescribe import * as ko a default import (evita named imports de Rolldown)', () => {
    expect(rewriteKnockoutNamespaceImport("import * as ko from 'knockout';")).toBe(
      "import ko from 'knockout';",
    )
    expect(rewriteKnockoutNamespaceImport('import * as ko from "knockout";')).toBe(
      'import ko from "knockout";',
    )
    expect(rewriteKnockoutNamespaceImport("import ko from 'knockout';")).toBe(
      "import ko from 'knockout';",
    )
  })

  it('envuelve el UMD con export default y sin named exports', () => {
    const wrapped = wrapKnockoutUmdAsDefaultEsm(
      '(function(p){typeof exports==="object"&&typeof module==="object"?p(module.exports):p((globalThis.ko={}))})(function(S){S.observable=function(){};});',
    )
    expect(wrapped).toContain('export default __ko')
    expect(wrapped).not.toContain('export const observable')
    expect(wrapped).toContain('Knockout observable is not a function')
  })

  it('ejecuta el UMD y deja observable como función', async () => {
    const umd =
      '(function(p){typeof exports==="object"&&typeof module==="object"?p(module.exports):p((globalThis.ko={}))})(function(S){S.observable=function(v){return function(){return v;}};S.bindingEvent={subscribe:function(){}};});'
    const wrapped = wrapKnockoutUmdAsDefaultEsm(umd).replace(
      'export default __ko;',
      'return __ko;',
    )
    const ko = new Function(wrapped)() as {
      observable: (value: number) => () => number
      bindingEvent: { subscribe: () => void }
    }
    expect(typeof ko.observable).toBe('function')
    expect(ko.observable(3)()).toBe(3)
    expect(typeof ko.bindingEvent.subscribe).toBe('function')
  })
})
